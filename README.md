# PillMate - Progetto completo

PillMate e' un sistema per la gestione delle terapie farmacologiche. Il progetto e' composto da:

- un backend Laravel, che gestisce sito web, API, autenticazione e database MySQL;
- un'app Android, pensata per il paziente/familiare, che comunica con Laravel tramite API;
- un database MySQL chiamato `pillmate`;
- ngrok, usato per testare il progetto da un telefono vero tramite un URL pubblico temporaneo.

## Struttura del progetto

```text
C:\xampp\htdocs\gestionefarmaci
```

Contiene il backend Laravel:

- pagine web Blade per admin, medico e paziente;
- API usate dall'app Android;
- collegamento al database MySQL;
- autenticazione utenti;
- gestione terapie, storico assunzioni, notifiche e dispositivi.

 L'app Android:

- schermata login;
- dashboard paziente;
- caricamento dati tramite API Laravel;
- configurazione `BASE_URL` per collegarsi al backend.

## Tecnologie usate

- Laravel 13
- PHP 8.5
- MySQL tramite XAMPP
- Blade
- Vite
- Kotlin
- Android
- Retrofit
- ngrok

## Come funziona la comunicazione

L'app Android non si collega direttamente al database. Il flusso corretto e':

```text
Telefono Android
    -> HTTPS tramite ngrok
Laravel API
    -> query al database MySQL
Database pillmate
```

Esempio:

```text
App Android
    -> POST https://...ngrok-free.dev/api/login
Laravel
    -> controlla email/password nel database
MySQL
    -> restituisce l'utente
Laravel
    -> restituisce token e dati in JSON
App Android
    -> mostra la dashboard paziente
```

## Requisiti

Prima di avviare il progetto servono:

- XAMPP installato;
- MySQL attivo da XAMPP;
- PHP disponibile da terminale;
- Composer installato;
- Node.js e npm installati;
- Android Studio installato;
- ngrok installato;
- telefono Android collegato o APK installato sul telefono.

## Avvio quotidiano del progetto

Questi sono i passaggi da fare ogni volta che vuoi usare il progetto con telefono vero e ngrok.

## 1. Avviare MySQL

Aprire XAMPP e avviare:

```text
MySQL
```

Il database usato dal progetto e':

```text
pillmate
```

Se vuoi controllare da terminale che MySQL sia acceso:

```powershell
C:\xampp\mysql\bin\mysqladmin.exe -u root ping
```

Se funziona, deve comparire:

```text
mysqld is alive
```

## 2. Avviare Laravel

Aprire PowerShell e andare nella cartella Laravel:

```powershell
cd C:\xampp\htdocs\gestionefarmaci
```

Pulire cache e configurazioni:

```powershell
php artisan optimize:clear
```

Avviare il server Laravel:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Il sito locale sara' disponibile su:

```text
http://127.0.0.1:8000
```

Le API locali saranno disponibili su:

```text
http://127.0.0.1:8000/api
```

## 3. Avviare ngrok

In un secondo terminale eseguire:

```powershell
ngrok http 8000
```

Ngrok generera' un URL pubblico simile a:

```text
https://bobble-synopsis-recreate.ngrok-free.dev
```

Questo URL permette al telefono di raggiungere Laravel anche se Laravel sta girando sul PC.

## 4. Aggiornare Laravel con l'URL ngrok

Nel file:

```text
C:\xampp\htdocs\gestionefarmaci\.env
```

impostare:

```env
APP_URL=https://bobble-synopsis-recreate.ngrok-free.dev
```

Se ngrok cambia URL, bisogna aggiornare questa riga.

Dopo aver modificato `.env`, pulire la cache:

```powershell
cd C:\xampp\htdocs\gestionefarmaci
php artisan optimize:clear
```

## 5. Aggiornare l'app Android con l'URL ngrok

Nel file:

```text
C:\Users\Utente\Downloads\pillmate-android-conversion (1)\pillmate-android-conversion\app\build.gradle.kts
```

controllare questa riga:

```kotlin
buildConfigField("String", "BASE_URL", "\"https://bobble-synopsis-recreate.ngrok-free.dev/api/\"")
```

L'URL deve:

- iniziare con `https://`;
- contenere il dominio ngrok attuale;
- finire con `/api/`.

Esempio corretto:

```text
https://bobble-synopsis-recreate.ngrok-free.dev/api/
```

Esempio sbagliato:

```text
http://bobble-synopsis-recreate.ngrok-free.dev
```

## 6. Compilare l'app Android

Aprire PowerShell nella cartella Android:

```powershell
cd "C:\Users\Utente\Downloads\pillmate-android-conversion (1)\pillmate-android-conversion"
```

Compilare:

```powershell
.\gradlew.bat --no-daemon assembleDebug --console plain
```

L'APK debug viene generato in:

```text
app\build\outputs\apk\debug\app-debug.apk
```

Questo APK puo' essere installato sul telefono.

## 7. Installare l'app sul telefono con ADB

Se il telefono e' collegato al PC con debug USB attivo:

```powershell
C:\Users\Utente\AppData\Local\Android\Sdk\platform-tools\adb.exe devices
```

Poi installare l'APK:

```powershell
C:\Users\Utente\AppData\Local\Android\Sdk\platform-tools\adb.exe install -r "C:\Users\Utente\Downloads\pillmate-android-conversion (1)\pillmate-android-conversion\app\build\outputs\apk\debug\app-debug.apk"
```

## Avvio rapido
Su php su due terminali diversi:
```
npm run dev
php artisan serve --host=0.0.0.0 --port=8000
```
Per ngrok
```
ngrok http 8000
```
Avvia xampp
## Login

Il login dell'app Android usa credenziali reali presenti nel database MySQL.

La chiamata parte dall'app:

```text
POST /api/login
```

Laravel controlla la tabella utenti e restituisce un token.

Il token viene poi usato per chiamare le API protette:

```text
GET /api/dashboard
GET /api/paziente/terapie
GET /api/paziente/storico
GET /api/paziente/dispositivi
GET /api/paziente/notifiche
```

## API principali

Le route API si trovano in:

```text
C:\xampp\htdocs\gestionefarmaci\routes\api.php
```

Endpoint principali:

```text
POST /api/login
GET  /api/me
GET  /api/dashboard
GET  /api/paziente/terapie
GET  /api/paziente/storico
GET  /api/paziente/dispositivi
GET  /api/paziente/notifiche
```

Il controller API paziente si trova in:

```text
C:\xampp\htdocs\gestionefarmaci\app\Http\Controllers\Api\PazienteApiController.php
```

Il controller API login si trova in:

```text
C:\xampp\htdocs\gestionefarmaci\app\Http\Controllers\Api\AuthApiController.php
```

## File Android importanti

Schermata principale, login e dashboard:

```text
app\src\main\java\com\pillmate\app\MainActivity.kt
```

Repository che chiama Laravel:

```text
app\src\main\java\com\pillmate\app\data\AppRepository.kt
```

Definizione delle API Retrofit:

```text
app\src\main\java\com\pillmate\app\network\ApiService.kt
```

Modelli dei dati:

```text
app\src\main\java\com\pillmate\app\model\Models.kt
```

Configurazione URL backend:

```text
app\build.gradle.kts
```

## File Laravel importanti

Configurazione ambiente:

```text
.env
```

Route web:

```text
routes\web.php
```

Route API:

```text
routes\api.php
```

View paziente:

```text
resources\views\paziente
```

View medico:

```text
resources\views\medico
```

View admin:

```text
resources\views\admin
```

CSS e asset Vite:

```text
resources\css
resources\js
public\build
```

## Build CSS e icone Laravel

Il sito usa Vite per compilare CSS e JavaScript. Le icone Lucide vengono caricate da:

```text
resources\js\app.js
```

Se da telefono spariscono CSS o icone, rigenerare gli asset:

```powershell
cd C:\xampp\htdocs\gestionefarmaci
npm run build
```

Poi eliminare `public/hot`, se presente:

```powershell
Remove-Item C:\xampp\htdocs\gestionefarmaci\public\hot
```

Pulire la cache:

```powershell
php artisan optimize:clear
```

Nota importante:

```text
public/hot
```

fa usare a Laravel il server Vite locale, ad esempio:

```text
http://[::1]:5173
```

Da telefono questo indirizzo non funziona. Per usare ngrok e telefono vero, e' meglio usare gli asset compilati in `public/build`.

## Problemi comuni

### Errore: SQLSTATE[HY000] [2002]

Significa che Laravel non riesce a collegarsi a MySQL.

Controllare che MySQL sia avviato da XAMPP.

Comando di verifica:

```powershell
C:\xampp\mysql\bin\mysqladmin.exe -u root ping
```

### CSS sparito da telefono

Probabile causa:

```text
public/hot
```

Soluzione:

```powershell
cd C:\xampp\htdocs\gestionefarmaci
npm run build
Remove-Item C:\xampp\htdocs\gestionefarmaci\public\hot
php artisan optimize:clear
```

### Icone non visibili

Le icone dipendono da `resources/js/app.js` e dalla build Vite.

Soluzione:

```powershell
cd C:\xampp\htdocs\gestionefarmaci
npm run build
php artisan optimize:clear
```

### Il browser avvisa che i dati non sono protetti

Succede quando si usa `http://` invece di `https://`.

Usare sempre:

```text
https://...
```

non:

```text
http://...
```

### L'app non riesce a fare login

Controllare:

- MySQL acceso;
- Laravel acceso;
- ngrok acceso;
- `APP_URL` nel `.env` aggiornato;
- `BASE_URL` Android aggiornato;
- URL Android con `/api/` finale;
- telefono con connessione internet.

## Comandi rapidi

Avvio Laravel:

```powershell
cd C:\xampp\htdocs\gestionefarmaci
php artisan optimize:clear
php artisan serve --host=0.0.0.0 --port=8000
```

Avvio ngrok:

```powershell
ngrok http 8000
```

Build Laravel:

```powershell
cd C:\xampp\htdocs\gestionefarmaci
npm run build
php artisan optimize:clear
```

Build Android:

```powershell
cd "C:\Users\Utente\Downloads\pillmate-android-conversion (1)\pillmate-android-conversion"
.\gradlew.bat --no-daemon assembleDebug --console plain
```

Installazione Android con ADB:

```powershell
C:\Users\Utente\AppData\Local\Android\Sdk\platform-tools\adb.exe install -r "C:\Users\Utente\Downloads\pillmate-android-conversion (1)\pillmate-android-conversion\app\build\outputs\apk\debug\app-debug.apk"
```

## Ordine corretto di avvio

```text
1. Avviare MySQL da XAMPP
2. Avviare Laravel con php artisan serve
3. Avviare ngrok con ngrok http 8000
4. Copiare l'URL https di ngrok
5. Aggiornare APP_URL nel file .env Laravel
6. Aggiornare BASE_URL nel file app/build.gradle.kts Android
7. Eseguire php artisan optimize:clear
8. Ricompilare l'app Android
9. Installare/aprire l'app sul telefono
```

## Nota per la presentazione

Il punto principale del progetto e' che l'app Android non contiene i dati e non accede direttamente a MySQL. Tutti i dati passano dal backend Laravel, che fa da livello intermedio sicuro tra app e database.

Questo permette di:

- centralizzare login e permessi;
- usare lo stesso database del sito;
- mantenere sincronizzati sito web e app;
- mostrare al paziente solo i suoi dati;
- esporre API riutilizzabili anche da altri dispositivi.
