Certo. Te lo spiego in modo pratico.

## Che cos’è MQTT

MQTT è un protocollo di messaggistica **leggero** basato su **publish/subscribe**: un client pubblica un messaggio su un **topic**, il **broker** lo riceve e lo inoltra a tutti i client iscritti a quel topic. Il pacchetto `php-mqtt/laravel-client` è un wrapper Laravel del client `php-mqtt/client`, e serve proprio per collegare Laravel a un broker MQTT, così puoi **pubblicare** e **sottoscrivere** topic dal tuo progetto. ([GitHub][1])

## In parole povere

Pensa così:

* **Broker MQTT** = il “centralino” dei messaggi
* **Topic** = il canale, tipo `pillmate/paziente/1/farmaco`
* **Publisher** = chi manda il messaggio
* **Subscriber** = chi ascolta quel topic

Esempio tuo:

* un dispositivo o app invia: `pillmate/paziente/12/farmaco`
* Laravel ascolta quel topic
* quando arriva il messaggio, Laravel lo salva nel database o aggiorna lo stato

## Devi creare un listener tra database e broker?

**Non proprio “tra database e broker”** nel senso classico.
La soluzione giusta è questa:

* **Laravel pubblica** verso MQTT quando il tuo sito deve inviare qualcosa al broker
* **Laravel ascolta** MQTT con un **subscriber/listener** quando il broker manda eventi che devono finire nel database

Quindi sì, ti serve un **listener/subscriber**, ma non come “ponte fisso in mezzo”; ti serve come **processo Laravel in ascolto** che riceve i messaggi MQTT e poi li scrive nel DB.

La parte importante è questa: la sottoscrizione MQTT richiede un **event loop** continuo. La documentazione del client lo dice chiaramente: per `subscribe()` bisogna eseguire `loop(true)`, cioè un processo che resta vivo ad ascoltare i messaggi. ([GitHub][1])

---

# Come si usa in Laravel

## 1. Installa il pacchetto

```bash
composer require php-mqtt/laravel-client
php artisan vendor:publish --provider="PhpMqtt\Client\MqttClientServiceProvider" --tag="config"
```

Il pacchetto si registra con auto-discovery e aggiunge anche la facade `MQTT`. ([GitHub][1])

---

## 2. Configura `.env`

Nel file pubblicato `config/mqtt-client.php` puoi definire una o più connessioni. La documentazione consiglia di usare variabili d’ambiente. ([GitHub][1])

Esempio `.env`:

```env
MQTT_HOST=127.0.0.1
MQTT_PORT=1883
MQTT_CLIENT_ID=laravel-pillmate
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_USE_TLS=false
```

Poi nel config usi quei valori.

---

## 3. Pubblicare un messaggio da Laravel

Per inviare un messaggio al broker:

```php
use PhpMqtt\Client\Facades\MQTT;

MQTT::publish('pillmate/test', 'ciao dal sito');
```

La facade supporta direttamente `publish(topic, message)` per QoS 0. ([GitHub][1])

Per esempio dentro un controller:

```php
<?php

namespace App\Http\Controllers;

use PhpMqtt\Client\Facades\MQTT;

class FarmacoController extends Controller
{
    public function inviaPromemoria()
    {
        MQTT::publish('pillmate/paziente/12/promemoria', json_encode([
            'farmaco' => 'Tachipirina',
            'orario' => '08:00'
        ]));

        return back()->with('success', 'Messaggio MQTT inviato.');
    }
}
```

---

## 4. Ascoltare un topic con un subscriber Laravel

Questa è la parte che ti serve davvero.

La cosa giusta in Laravel è creare un **comando Artisan** che resta in ascolto.

### Crea il comando

```bash
php artisan make:command MqttListen
```

### `app/Console/Commands/MqttListen.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Models\Notifica;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Ascolta i messaggi MQTT e li salva nel database';

    public function handle()
    {
        $mqtt = MQTT::connection();

        $mqtt->subscribe('pillmate/paziente/+/farmaco', function (string $topic, string $message) {
            $this->info("Messaggio ricevuto su {$topic}: {$message}");

            $payload = json_decode($message, true);

            if (!$payload) {
                return;
            }

            Notifica::create([
                'topic' => $topic,
                'messaggio' => $message,
            ]);
        }, 1);

        $mqtt->loop(true);
    }
}
```

Questo schema segue esattamente il modo d’uso previsto dalla libreria: `MQTT::connection()`, `subscribe(...)`, poi `loop(true)`. ([GitHub][1])

Poi lo avvii con:

```bash
php artisan mqtt:listen
```

---

# Architettura giusta per il tuo caso

Per il tuo sito Laravel, la struttura sensata è questa:

## Caso A: Laravel deve solo inviare ordini/comandi

Allora usi MQTT solo come **publisher**:

`Laravel -> MQTT broker -> dispositivo/app`

Esempio:

* utente clicca “ricorda farmaco”
* Laravel pubblica su `pillmate/device/12/reminder`

## Caso B: Laravel deve ricevere eventi dal broker e salvarli

Allora ti serve il **subscriber**:

`dispositivo -> MQTT broker -> Laravel listener -> database`

Esempio:

* dispenser invia “farmaco preso”
* broker inoltra il messaggio
* il comando `mqtt:listen` lo riceve
* Laravel salva l’evento nel DB

## Caso C: bidirezionale

Molto comune:

* Laravel pubblica comandi
* Laravel ascolta conferme/eventi
* il database resta la fonte storica dei dati

---

# Quindi serve un listener “in mezzo”?

La risposta corretta è:

**Ti serve un processo subscriber Laravel che ascolta il broker e poi aggiorna il DB.**
Non ti serve un “middleware separato” tra DB e broker, a meno che tu non voglia un microservizio dedicato.

Per un progetto come il tuo, la soluzione normale è:

* **Controller/Job Laravel** per pubblicare
* **Command Laravel long-running** per ascoltare e salvare

---

# Una cosa importante: non farlo dentro una route web

Non fare una cosa del genere dentro un controller HTTP:

```php
$mqtt->subscribe(...);
$mqtt->loop(true);
```

perché il loop è pensato per restare attivo continuamente, non per una richiesta web che deve finire in pochi secondi. La documentazione del client spiega che la subscribe richiede un event loop attivo. ([GitHub][1])

Quindi il subscriber va lanciato come:

* comando artisan
* processo gestito da Supervisor, systemd, Docker, ecc.

---

# Esempio pratico per PillMate

Mettiamo che tu voglia ricevere conferme da un dispenser.

## Topic

```text
pillmate/pazienti/12/eventi
```

## Messaggio JSON inviato dal dispositivo

```json
{
  "tipo": "farmaco_assunto",
  "farmaco": "Aspirina",
  "timestamp": "2026-04-11 10:30:00"
}
```

## Subscriber Laravel

Quando lo riceve:

* trova il paziente 12 dal topic
* salva nel database
* eventualmente crea una notifica nel sito

---

# Esempio migliore con parsing topic

```php
$mqtt->subscribe('pillmate/pazienti/+/eventi', function (string $topic, string $message) {
    preg_match('#pillmate/pazienti/(\d+)/eventi#', $topic, $matches);

    $pazienteId = $matches[1] ?? null;
    $payload = json_decode($message, true);

    if (!$pazienteId || !$payload) {
        return;
    }

    \App\Models\Notifica::create([
        'id_utente' => $pazienteId,
        'tipo' => $payload['tipo'] ?? 'evento',
        'messaggio' => $message,
    ]);
}, 1);

$mqtt->loop(true);
```

---

# QoS spiegato semplice

La libreria supporta i vari livelli QoS; per QoS 1 e 2 bisogna far girare il loop per gestire gli acknowledgment, mentre per la sottoscrizione serve comunque il loop continuo. ([GitHub][1])

Per te, pratica:

* **QoS 0** = veloce, senza garanzie forti
* **QoS 1** = almeno una volta
* **QoS 2** = esattamente una volta, più pesante

Per notifiche e stati sanitari leggeri, spesso si usa **QoS 1**.

---

# In sintesi

## Cos’è

MQTT è un sistema leggero di messaggi publish/subscribe tramite broker. ([GitHub][1])

## Come lo usi in Laravel

Con `php-mqtt/laravel-client` puoi:

* pubblicare messaggi con `MQTT::publish(...)`
* ottenere una connessione con `MQTT::connection()`
* fare `subscribe(...)`
* avviare `loop(true)` per ascoltare i messaggi ([GitHub][1])

## Ti serve un listener?

**Sì**, ma come **comando Laravel sempre acceso**, non come pagina web e non come ponte separato obbligatorio.

## Architettura consigliata

* sito Laravel → pubblica su MQTT
* comando Laravel `mqtt:listen` → ascolta MQTT
* callback del subscriber → salva nel database

Se vuoi, nel prossimo messaggio ti scrivo direttamente il codice completo per il tuo progetto `gestionefarmaci` con:

* config `.env`
* comando `php artisan mqtt:listen`
* salvataggio su tabella `notifiche`
* esempio controller che pubblica su un topic reale.

[1]: https://github.com/php-mqtt/laravel-client/blob/master/README.md "laravel-client/README.md at master · php-mqtt/laravel-client · GitHub"
