# ==============================================================
#  PillMate - Script di installazione automatica
#  Esegui da PowerShell come amministratore:
#    cd C:\xampp\htdocs\gestionefarmaci
#    .\pillmate_setup.ps1
# ==============================================================

$projectRoot = "C:\xampp\htdocs\gestionefarmaci"
$backupDir   = "$projectRoot\_backup_pillmate_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "  PillMate - Setup automatico Laravel" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Verifica che siamo nella cartella giusta
if (-not (Test-Path "$projectRoot\artisan")) {
    Write-Host "ERRORE: artisan non trovato in $projectRoot" -ForegroundColor Red
    Write-Host "Controlla il percorso del progetto." -ForegroundColor Red
    exit 1
}

# Crea cartella backup per i file che verranno modificati
New-Item -ItemType Directory -Force -Path $backupDir | Out-Null
Write-Host "Backup salvato in: $backupDir" -ForegroundColor Yellow
Write-Host ""

function Write-File($path, $content) {
    $fullPath = "$projectRoot\$path"
    $dir = Split-Path $fullPath
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Force -Path $dir | Out-Null
    }
    # Backup se il file esiste già
    if (Test-Path $fullPath) {
        $backupPath = "$backupDir\$($path -replace '\\','_')"
        Copy-Item $fullPath $backupPath -Force
    }
    Set-Content -Path $fullPath -Value $content -Encoding UTF8
    Write-Host "  [OK] $path" -ForegroundColor Green
}

function Append-IfMissing($path, $marker, $content) {
    $fullPath = "$projectRoot\$path"
    if (-not (Test-Path $fullPath)) {
        Write-Host "  [WARN] $path non trovato, salto." -ForegroundColor Yellow
        return
    }
    $existing = Get-Content $fullPath -Raw
    if ($existing -match [regex]::Escape($marker)) {
        Write-Host "  [SKIP] $path (già modificato)" -ForegroundColor DarkGray
    } else {
        # Backup
        $backupPath = "$backupDir\$($path -replace '\\','_')"
        Copy-Item $fullPath $backupPath -Force
        Add-Content -Path $fullPath -Value $content -Encoding UTF8
        Write-Host "  [OK] $path (aggiornato)" -ForegroundColor Green
    }
}

# ==============================================================
Write-Host "1) Migration - scomparti_dispositivo" -ForegroundColor Cyan
# ==============================================================

Write-File "database\migrations\2026_04_20_000001_create_scomparti_dispositivo_table.php" @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mappa scomparto fisico -> farmaco per ogni dispositivo PillMate.
 * Hardware: servo MG90S (180 max) -> 10 scomparti x 20 gradi = 180
 * Angoli: [0, 20, 40, 60, 80, 100, 120, 140, 160, 180]
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scomparti_dispositivo', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_dispositivo');
            $table->foreign('id_dispositivo')
                  ->references('id')->on('dispositivi')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->unsignedTinyInteger('numero_scomparto'); // 1-10

            // (numero_scomparto - 1) * 20  ->  0, 20, 40 ... 180
            $table->unsignedSmallInteger('angolo')->default(0);

            // null = scomparto non ancora configurato
            $table->unsignedBigInteger('id_farmaco')->nullable();
            $table->foreign('id_farmaco')
                  ->references('id')->on('farmaci')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            // true = pillole fisicamente presenti
            $table->boolean('pieno')->default(false);

            $table->timestamps();

            $table->unique(
                ['id_dispositivo', 'numero_scomparto'],
                'uq_dispositivo_scomparto'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scomparti_dispositivo');
    }
};
'@

# ==============================================================
Write-Host "2) Model - ScompartoDispositivo" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Models\ScompartoDispositivo.php" @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Singolo scomparto fisico del carosello PillMate.
 *
 * @property int      $id
 * @property int      $id_dispositivo
 * @property int      $numero_scomparto  (1-10)
 * @property int      $angolo            (0,20,40...180)
 * @property int|null $id_farmaco
 * @property bool     $pieno
 */
class ScompartoDispositivo extends Model
{
    protected $table = 'scomparti_dispositivo';

    protected $fillable = [
        'id_dispositivo',
        'numero_scomparto',
        'angolo',
        'id_farmaco',
        'pieno',
    ];

    protected $casts = [
        'pieno'            => 'boolean',
        'numero_scomparto' => 'integer',
        'angolo'           => 'integer',
    ];

    // Speculare ad angoliFissi[] nel firmware C++
    public const NUM_SCOMPARTI = 10;
    public const ANGOLI_FISSI  = [0, 20, 40, 60, 80, 100, 120, 140, 160, 180];

    // ── Relazioni ────────────────────────────────────────────────────

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class, 'id_dispositivo');
    }

    public function farmaco(): BelongsTo
    {
        return $this->belongsTo(Farmaco::class, 'id_farmaco');
    }

    // ── Helper statici ───────────────────────────────────────────────

    /**
     * Calcola l'angolo fisico per il numero scomparto dato (1-based).
     * Speculare a angoloScomparto() nel firmware.
     */
    public static function calcolaAngolo(int $numeroScomparto): int
    {
        $idx = $numeroScomparto - 1;
        return self::ANGOLI_FISSI[$idx] ?? 0;
    }

    /**
     * Crea i 10 scomparti vuoti per un dispositivo appena registrato.
     */
    public static function inizializzaPerDispositivo(int $idDispositivo): void
    {
        for ($n = 1; $n <= self::NUM_SCOMPARTI; $n++) {
            self::updateOrCreate(
                ['id_dispositivo' => $idDispositivo, 'numero_scomparto' => $n],
                ['angolo' => self::calcolaAngolo($n), 'id_farmaco' => null, 'pieno' => false]
            );
        }
    }

    /**
     * Costruisce l'array JSON per il comando "configura_scomparti" dell'ESP32.
     */
    public static function buildPayloadPerDispositivo(int $idDispositivo): array
    {
        return self::with('farmaco')
            ->where('id_dispositivo', $idDispositivo)
            ->orderBy('numero_scomparto')
            ->get()
            ->map(fn($s) => [
                'numero'       => $s->numero_scomparto,
                'nome_farmaco' => $s->farmaco?->nome ?? 'Non configurato',
                'id_farmaco'   => $s->id_farmaco ?? 0,
                'pieno'        => $s->pieno,
            ])
            ->toArray();
    }
}
'@

# ==============================================================
Write-Host "3) Model - Dispositivo" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Models\Dispositivo.php" @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispositivo extends Model
{
    protected $table  = 'dispositivi';
    public $timestamps = false;

    protected $fillable = [
        'codice_seriale',
        'id_paziente',
        'nome_dispositivo',
        'stato',
        'ultima_connessione',
        'batteria',
        'temperatura',
        'umidita',
    ];

    protected $casts = [
        'ultima_connessione' => 'datetime',
        'temperatura'        => 'float',
        'umidita'            => 'float',
    ];

    // ── Relazioni ────────────────────────────────────────────────────

    public function paziente(): BelongsTo
    {
        return $this->belongsTo(Paziente::class, 'id_paziente');
    }

    public function scomparti(): HasMany
    {
        return $this->hasMany(ScompartoDispositivo::class, 'id_dispositivo')
                    ->orderBy('numero_scomparto');
    }

    // ── Topic MQTT ───────────────────────────────────────────────────

    public function topicComandi(): string
    {
        return "pillmate/{$this->codice_seriale}/comandi";
    }

    public function topicEventi(): string
    {
        return "pillmate/{$this->codice_seriale}/eventi";
    }

    public function topicTelemetria(): string
    {
        return "pillmate/{$this->codice_seriale}/telemetria";
    }

    public function topicStato(): string
    {
        return "pillmate/{$this->codice_seriale}/stato";
    }
}
'@

# ==============================================================
Write-Host "4) Model - Farmaco" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Models\Farmaco.php" @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmaco extends Model
{
    protected $table   = 'farmaci';
    public $timestamps = false;

    protected $fillable = ['nome', 'descrizione', 'dose', 'note'];

    public function terapie(): HasMany
    {
        return $this->hasMany(Terapia::class, 'id_farmaco');
    }

    public function scomparti(): HasMany
    {
        return $this->hasMany(ScompartoDispositivo::class, 'id_farmaco');
    }
}
'@

# ==============================================================
Write-Host "5) Model - Paziente" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Models\Paziente.php" @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paziente extends Model
{
    protected $table   = 'pazienti';
    public $timestamps = false;

    protected $fillable = ['id_utente', 'data_nascita', 'indirizzo', 'note_mediche'];

    public function utente(): BelongsTo
    {
        return $this->belongsTo(Utente::class, 'id_utente');
    }

    public function dispositivi(): HasMany
    {
        return $this->hasMany(Dispositivo::class, 'id_paziente');
    }

    public function terapie(): HasMany
    {
        return $this->hasMany(Terapia::class, 'id_paziente');
    }
}
'@

# ==============================================================
Write-Host "6) Model - Assunzione" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Models\Assunzione.php" @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assunzione extends Model
{
    protected $table   = 'assunzioni';
    public $timestamps = false;

    protected $fillable = [
        'id_somministrazione',
        'data_prevista',
        'data_erogazione',
        'data_conferma',
        'stato',
        'confermata_da',
    ];

    protected $casts = [
        'data_prevista'  => 'datetime',
        'data_erogazione'=> 'datetime',
        'data_conferma'  => 'datetime',
    ];

    public function somministrazione(): BelongsTo
    {
        return $this->belongsTo(Somministrazione::class, 'id_somministrazione');
    }
}
'@

# ==============================================================
Write-Host "7) Model - Somministrazione" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Models\Somministrazione.php" @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Somministrazione extends Model
{
    protected $table   = 'somministrazioni';
    public $timestamps = false;

    protected $fillable = ['id_terapia', 'ora', 'giorno_settimana'];

    public function terapia(): BelongsTo
    {
        return $this->belongsTo(Terapia::class, 'id_terapia');
    }

    public function assunzioni(): HasMany
    {
        return $this->hasMany(Assunzione::class, 'id_somministrazione');
    }
}
'@

# ==============================================================
Write-Host "8) Model - Terapia" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Models\Terapia.php" @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Terapia extends Model
{
    protected $table   = 'terapie';
    public $timestamps = false;

    protected $fillable = [
        'id_paziente','id_medico','id_farmaco',
        'data_inizio','data_fine','frequenza',
        'quantita','istruzioni','attiva',
    ];

    protected $casts = [
        'attiva'      => 'boolean',
        'data_inizio' => 'date',
        'data_fine'   => 'date',
    ];

    public function paziente(): BelongsTo
    {
        return $this->belongsTo(Paziente::class, 'id_paziente');
    }

    public function farmaco(): BelongsTo
    {
        return $this->belongsTo(Farmaco::class, 'id_farmaco');
    }

    public function somministrazioni(): HasMany
    {
        return $this->hasMany(Somministrazione::class, 'id_terapia');
    }
}
'@

# ==============================================================
Write-Host "9) Model - Notifica" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Models\Notifica.php" @'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifica extends Model
{
    protected $table   = 'notifiche';
    public $timestamps = false;

    protected $fillable = [
        'id_utente',
        'titolo',
        'messaggio',
        'tipo',
        'letta',
        'data_invio',
    ];

    protected $casts = ['letta' => 'boolean'];

    public function utente(): BelongsTo
    {
        return $this->belongsTo(Utente::class, 'id_utente');
    }
}
'@

# ==============================================================
Write-Host "10) Controller - MqttController" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Http\Controllers\MqttController.php" @'
<?php

namespace App\Http\Controllers;

use App\Models\Dispositivo;
use App\Models\ScompartoDispositivo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use PhpMqtt\Client\Facades\MQTT;

/**
 * Gestisce i comandi che Laravel pubblica verso l ESP32.
 *
 * Comandi supportati dall ESP32 (vedere main.cpp -> callback()):
 *   configura_scomparti, attiva_allarme, eroga_farmaco,
 *   set_sveglia, get_mappa_scomparti, buzzer_test
 */
class MqttController extends Controller
{
    // ── Invia la mappa completa scomparti->farmaci all ESP32 ─────────

    public function configuraScomparti(Request $request, int $idDispositivo): JsonResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        $payload = json_encode([
            'comando'   => 'configura_scomparti',
            'scomparti' => ScompartoDispositivo::buildPayloadPerDispositivo($idDispositivo),
        ]);

        MQTT::publish($dispositivo->topicComandi(), $payload);

        return response()->json([
            'ok'        => true,
            'messaggio' => 'Configurazione inviata al dispositivo.',
            'payload'   => json_decode($payload),
        ]);
    }

    // ── Attiva allarme (buzzer + OLED), il paziente conferma con PIR/tasto ──

    public function attivaAllarme(Request $request, int $idDispositivo): JsonResponse
    {
        $request->validate(['id_farmaco' => 'required|integer|exists:farmaci,id']);

        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        MQTT::publish($dispositivo->topicComandi(), json_encode([
            'comando'    => 'attiva_allarme',
            'id_farmaco' => (int) $request->id_farmaco,
        ]));

        return response()->json(['ok' => true, 'messaggio' => 'Allarme attivato.']);
    }

    // ── Eroga subito (remoto, senza conferma paziente) ────────────────

    public function erogaFarmaco(Request $request, int $idDispositivo): JsonResponse
    {
        $request->validate(['id_farmaco' => 'required|integer|exists:farmaci,id']);

        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        $scomparto = ScompartoDispositivo::where('id_dispositivo', $idDispositivo)
            ->where('id_farmaco', $request->id_farmaco)
            ->where('pieno', true)
            ->first();

        if (! $scomparto) {
            return response()->json([
                'ok'        => false,
                'messaggio' => 'Farmaco non trovato in nessuno scomparto pieno.',
            ], 422);
        }

        MQTT::publish($dispositivo->topicComandi(), json_encode([
            'comando'    => 'eroga_farmaco',
            'id_farmaco' => (int) $request->id_farmaco,
        ]));

        return response()->json(['ok' => true, 'messaggio' => 'Comando erogazione inviato.']);
    }

    // ── Aggiorna sveglia nella flash dell ESP32 ───────────────────────

    public function setSveglia(Request $request, int $idDispositivo): JsonResponse
    {
        $request->validate([
            'ora'    => 'required|integer|between:0,23',
            'minuto' => 'required|integer|between:0,59',
        ]);

        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        MQTT::publish($dispositivo->topicComandi(), json_encode([
            'comando' => 'set_sveglia',
            'ora'     => (int) $request->ora,
            'minuto'  => (int) $request->minuto,
        ]));

        return response()->json([
            'ok'        => true,
            'messaggio' => "Sveglia impostata alle {$request->ora}:{$request->minuto}.",
        ]);
    }

    // ── Richiede all ESP32 di pubblicare la sua mappa attuale ─────────

    public function richiediMappa(int $idDispositivo): JsonResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        MQTT::publish(
            $dispositivo->topicComandi(),
            json_encode(['comando' => 'get_mappa_scomparti'])
        );

        return response()->json(['ok' => true, 'messaggio' => 'Richiesta mappa inviata.']);
    }

    // ── Test buzzer ────────────────────────────────────────────────────

    public function testBuzzer(int $idDispositivo): JsonResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);

        MQTT::publish(
            $dispositivo->topicComandi(),
            json_encode(['comando' => 'buzzer_test'])
        );

        return response()->json(['ok' => true, 'messaggio' => 'Buzzer test inviato.']);
    }
}
'@

# ==============================================================
Write-Host "11) Controller - DispositivoController" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Http\Controllers\DispositivoController.php" @'
<?php

namespace App\Http\Controllers;

use App\Models\Dispositivo;
use App\Models\Farmaco;
use App\Models\ScompartoDispositivo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DispositivoController extends Controller
{
    /**
     * Pagina configurazione scomparti.
     */
    public function scomparti(int $idDispositivo): View
    {
        $dispositivo = Dispositivo::with(['scomparti.farmaco', 'paziente.utente'])
                                  ->findOrFail($idDispositivo);
        $farmaci     = Farmaco::orderBy('nome')->get();

        if ($dispositivo->scomparti->count() < ScompartoDispositivo::NUM_SCOMPARTI) {
            ScompartoDispositivo::inizializzaPerDispositivo($idDispositivo);
            $dispositivo->load('scomparti.farmaco');
        }

        return view('dispositivi.scomparti', compact('dispositivo', 'farmaci'));
    }

    /**
     * Salva la configurazione e la invia all ESP32 via MQTT.
     */
    public function salvaScomparti(Request $request, int $idDispositivo): RedirectResponse
    {
        $dispositivo = Dispositivo::findOrFail($idDispositivo);
        $datiForm    = $request->input('scomparti', []);

        foreach ($datiForm as $numero => $valori) {
            $numero = (int) $numero;
            if ($numero < 1 || $numero > ScompartoDispositivo::NUM_SCOMPARTI) continue;

            $idFarmaco = isset($valori['id_farmaco']) && (int)$valori['id_farmaco'] > 0
                ? (int) $valori['id_farmaco']
                : null;

            ScompartoDispositivo::updateOrCreate(
                ['id_dispositivo' => $idDispositivo, 'numero_scomparto' => $numero],
                [
                    'angolo'     => ScompartoDispositivo::calcolaAngolo($numero),
                    'id_farmaco' => $idFarmaco,
                    'pieno'      => isset($valori['pieno']) && $valori['pieno'],
                ]
            );
        }

        app(MqttController::class)->configuraScomparti(new Request(), $idDispositivo);

        return redirect()
            ->route('dispositivi.scomparti', $idDispositivo)
            ->with('success', 'Configurazione salvata e inviata al dispositivo.');
    }

    /**
     * Aggiorna solo pieno/vuoto di un singolo scomparto (AJAX).
     */
    public function aggiornaStato(Request $request, int $idDispositivo, int $numeroScomparto)
    {
        $request->validate(['pieno' => 'required|boolean']);

        $scomparto = ScompartoDispositivo::where('id_dispositivo', $idDispositivo)
            ->where('numero_scomparto', $numeroScomparto)
            ->firstOrFail();

        $scomparto->update(['pieno' => $request->boolean('pieno')]);

        app(MqttController::class)->configuraScomparti(new Request(), $idDispositivo);

        return response()->json(['ok' => true, 'pieno' => $scomparto->pieno]);
    }
}
'@

# ==============================================================
Write-Host "12) Command - MqttListen" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Console\Commands\MqttListen.php" @'
<?php

namespace App\Console\Commands;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Notifica;
use App\Models\ScompartoDispositivo;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

/**
 * Processo persistente: ascolta MQTT e aggiorna il DB.
 * Avvio: php artisan mqtt:listen
 * In produzione: tenerlo vivo con Supervisor.
 */
class MqttListen extends Command
{
    protected $signature   = 'mqtt:listen';
    protected $description = 'Ascolta i topic MQTT dei dispositivi PillMate e aggiorna il DB';

    public function handle(): void
    {
        $this->info('[MQTT] Listener avviato. In ascolto su pillmate/+/...');

        $mqtt = MQTT::connection();

        // ── EVENTI ───────────────────────────────────────────────────
        $mqtt->subscribe('pillmate/+/eventi', function (string $topic, string $raw) {
            $serial  = $this->estraiSeriale($topic);
            $payload = json_decode($raw, true);
            if (! $payload || ! isset($payload['azione'])) return;

            $dispositivo = Dispositivo::where('codice_seriale', $serial)->first();
            if (! $dispositivo) { $this->warn("[MQTT] Dispositivo sconosciuto: $serial"); return; }

            match ($payload['azione']) {
                'pillola_erogata'            => $this->gestisciErogazione($dispositivo, $payload),
                'mappa_scomparti'            => $this->sincronizzaMappa($dispositivo, $payload),
                'errore_farmaco_non_trovato' => $this->gestisciErroreFarmaco($dispositivo, $payload),
                'tentativo_fuori_orario'     => $this->gestisciTentativoFuoriOrario($dispositivo, $payload),
                default                      => $this->line("[MQTT] Azione ignota: {$payload['azione']}"),
            };
        }, 1);

        // ── TELEMETRIA ────────────────────────────────────────────────
        $mqtt->subscribe('pillmate/+/telemetria', function (string $topic, string $raw) {
            $serial  = $this->estraiSeriale($topic);
            $payload = json_decode($raw, true);
            if (! $payload) return;

            $dispositivo = Dispositivo::where('codice_seriale', $serial)->first();
            if (! $dispositivo) return;

            $update = ['ultima_connessione' => now()];
            if (isset($payload['temperatura'])) $update['temperatura'] = $payload['temperatura'];
            if (isset($payload['umidita']))      $update['umidita']     = $payload['umidita'];
            $dispositivo->update($update);

            if (isset($payload['scomparti']) && is_array($payload['scomparti'])) {
                foreach ($payload['scomparti'] as $s) {
                    if (! isset($s['numero'])) continue;
                    ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
                        ->where('numero_scomparto', $s['numero'])
                        ->update(['pieno' => $s['pieno'] ?? false]);
                }
            }
            $this->line("[TELEM] {$serial} | T:{$payload['temperatura']}C H:{$payload['umidita']}%");
        }, 0);

        // ── STATO (Last Will + online) ────────────────────────────────
        $mqtt->subscribe('pillmate/+/stato', function (string $topic, string $raw) {
            $serial  = $this->estraiSeriale($topic);
            $payload = json_decode($raw, true);
            if (! $payload) return;

            $dispositivo = Dispositivo::where('codice_seriale', $serial)->first();
            if (! $dispositivo) return;

            $nuovoStato = match ($payload['status'] ?? '') {
                'online'  => 'attivo',
                'offline' => 'offline',
                default   => null,
            };

            if ($nuovoStato) {
                $dispositivo->update(['stato' => $nuovoStato, 'ultima_connessione' => now()]);
                $this->info("[STATO] {$serial} -> {$nuovoStato}");
            }

            // Quando torna online, manda subito la configurazione scomparti
            if ($nuovoStato === 'attivo') {
                $this->inviaConfigurazione($dispositivo);
            }
        }, 1);

        $mqtt->loop(true);
    }

    // ── Handler ──────────────────────────────────────────────────────

    private function gestisciErogazione(Dispositivo $d, array $p): void
    {
        $idFarmaco    = $p['id_farmaco']      ?? null;
        $numScomparto = $p['scomparto_usato'] ?? null;
        $metodo       = $p['metodo_attivazione'] ?? 'sconosciuto';
        $timestamp    = $p['timestamp']       ?? now();

        $this->info("[EVENTO] Erogazione | {$d->codice_seriale} | Farmaco:{$idFarmaco} | Scmp:{$numScomparto} | {$metodo}");

        // Segna scomparto vuoto
        if ($numScomparto) {
            ScompartoDispositivo::where('id_dispositivo', $d->id)
                ->where('numero_scomparto', $numScomparto)
                ->update(['pieno' => false]);
        }

        // Aggiorna assunzione in_attesa
        if ($idFarmaco && $d->id_paziente) {
            $assunzione = Assunzione::whereHas('somministrazione.terapia', function ($q) use ($d, $idFarmaco) {
                    $q->where('id_paziente', $d->id_paziente)
                      ->where('id_farmaco', $idFarmaco)
                      ->where('attiva', true);
                })
                ->where('stato', 'in_attesa')
                ->orderBy('data_prevista')
                ->first();

            if ($assunzione) {
                $assunzione->update([
                    'stato'           => 'erogata',
                    'data_erogazione' => $timestamp,
                    'confermata_da'   => $this->mappaMetodo($metodo),
                ]);
            }
        }

        $nome = $p['nome_farmaco'] ?? "Farmaco #{$idFarmaco}";
        $this->creaNotifica($d->id_paziente, 'Pillola erogata',
            "La pillola \"{$nome}\" e stata erogata (metodo: {$metodo}).", 'info');
    }

    private function sincronizzaMappa(Dispositivo $d, array $p): void
    {
        foreach ($p['scomparti'] ?? [] as $s) {
            if (! isset($s['numero'])) continue;
            $idFarmaco = isset($s['id_farmaco']) && $s['id_farmaco'] > 0 ? $s['id_farmaco'] : null;
            ScompartoDispositivo::updateOrCreate(
                ['id_dispositivo' => $d->id, 'numero_scomparto' => $s['numero']],
                [
                    'angolo'     => $s['angolo'] ?? ScompartoDispositivo::calcolaAngolo($s['numero']),
                    'id_farmaco' => $idFarmaco,
                    'pieno'      => $s['pieno'] ?? false,
                ]
            );
        }
        $this->line("[MAPPA] Sincronizzata per {$d->codice_seriale}.");
    }

    private function gestisciErroreFarmaco(Dispositivo $d, array $p): void
    {
        $nome = $p['nome_farmaco'] ?? ("Farmaco ID " . ($p['id_farmaco'] ?? '?'));
        $this->warn("[ERRORE] Farmaco non trovato | {$d->codice_seriale}");
        $this->creaNotifica($d->id_paziente, 'Farmaco non disponibile',
            "Il farmaco \"{$nome}\" non e presente in nessuno scomparto carico.", 'allarme');
    }

    private function gestisciTentativoFuoriOrario(Dispositivo $d, array $p): void
    {
        $metodo = $p['metodo_attivazione'] ?? 'sconosciuto';
        $this->warn("[FUORI ORARIO] {$d->codice_seriale} | {$metodo}");
        $this->creaNotifica($d->id_paziente, 'Tentativo fuori orario',
            "Tentativo di erogazione fuori orario (metodo: {$metodo}).", 'allarme');
    }

    private function inviaConfigurazione(Dispositivo $d): void
    {
        $payload = json_encode([
            'comando'   => 'configura_scomparti',
            'scomparti' => ScompartoDispositivo::buildPayloadPerDispositivo($d->id),
        ]);
        MQTT::publish($d->topicComandi(), $payload);
        $this->info("[AUTO-CONFIG] Configurazione inviata a {$d->codice_seriale}.");
    }

    // ── Utility ──────────────────────────────────────────────────────

    private function estraiSeriale(string $topic): string
    {
        return explode('/', $topic)[1] ?? '';
    }

    private function mappaMetodo(string $metodo): string
    {
        return match (strtoupper($metodo)) {
            'SENSORE_PIR'    => 'sensore',
            'BOTTONE_FISICO' => 'paziente',
            'COMANDO_REMOTO' => 'sistema',
            default          => 'sistema',
        };
    }

    private function creaNotifica(int $idUtente, string $titolo, string $messaggio, string $tipo): void
    {
        Notifica::create([
            'id_utente' => $idUtente,
            'titolo'    => $titolo,
            'messaggio' => $messaggio,
            'tipo'      => $tipo,
        ]);
    }
}
'@

# ==============================================================
Write-Host "13) Command - SomministrazioneScheduler" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Console\Commands\SomministrazioneScheduler.php" @'
<?php

namespace App\Console\Commands;

use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Notifica;
use App\Models\ScompartoDispositivo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

/**
 * Controlla ogni minuto le somministrazioni in scadenza
 * e pubblica attiva_allarme + set_sveglia verso l ESP32.
 *
 * Schedulato in Kernel.php con ->everyMinute()
 */
class SomministrazioneScheduler extends Command
{
    protected $signature   = 'pillmate:scheduler';
    protected $description = 'Controlla le somministrazioni previste e attiva gli allarmi';

    public function handle(): void
    {
        $adesso = Carbon::now();

        $assunzioni = Assunzione::with([
                'somministrazione.terapia.farmaco',
                'somministrazione.terapia.paziente',
            ])
            ->where('stato', 'in_attesa')
            ->whereBetween('data_prevista', [
                $adesso->copy()->subSeconds(30),
                $adesso->copy()->addSeconds(30),
            ])
            ->get();

        foreach ($assunzioni as $assunzione) {
            $terapia  = $assunzione->somministrazione->terapia;
            $farmaco  = $terapia->farmaco;
            $paziente = $terapia->paziente;
            if (! $paziente) continue;

            $dispositivo = Dispositivo::where('id_paziente', $paziente->id)
                ->where('stato', 'attivo')
                ->first();

            if (! $dispositivo) {
                $this->warn("[SCHEDULER] Nessun dispositivo attivo per paziente {$paziente->id}");
                continue;
            }

            $scomparto = ScompartoDispositivo::where('id_dispositivo', $dispositivo->id)
                ->where('id_farmaco', $farmaco->id)
                ->where('pieno', true)
                ->first();

            if (! $scomparto) {
                $this->warn("[SCHEDULER] Scomparto vuoto per {$farmaco->nome}");
                Notifica::create([
                    'id_utente' => $paziente->id_utente,
                    'titolo'    => 'Scomparto vuoto',
                    'messaggio' => "Lo scomparto per \"{$farmaco->nome}\" e vuoto. Ricaricare il dispositivo.",
                    'tipo'      => 'allarme',
                ]);
                continue;
            }

            // Attiva allarme sul dispositivo
            MQTT::publish($dispositivo->topicComandi(), json_encode([
                'comando'    => 'attiva_allarme',
                'id_farmaco' => $farmaco->id,
            ]));

            // Aggiorna anche la sveglia nella flash come backup
            MQTT::publish($dispositivo->topicComandi(), json_encode([
                'comando' => 'set_sveglia',
                'ora'     => (int) $adesso->format('H'),
                'minuto'  => (int) $adesso->format('i'),
            ]));

            $this->info("[SCHEDULER] Allarme -> {$dispositivo->codice_seriale} | {$farmaco->nome}");
        }
    }
}
'@

# ==============================================================
Write-Host "14) Kernel.php" -ForegroundColor Cyan
# ==============================================================

Write-File "app\Console\Kernel.php" @'
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\MqttListen::class,
        \App\Console\Commands\SomministrazioneScheduler::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Controlla ogni minuto le somministrazioni in scadenza
        $schedule->command('pillmate:scheduler')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
'@

# ==============================================================
Write-Host "15) View - dispositivi/scomparti.blade.php" -ForegroundColor Cyan
# ==============================================================

New-Item -ItemType Directory -Force -Path "$projectRoot\resources\views\dispositivi" | Out-Null

Write-File "resources\views\dispositivi\scomparti.blade.php" @'
{{-- resources/views/dispositivi/scomparti.blade.php --}}
@extends('layouts.app')

@section('title', 'Scomparti - ' . $dispositivo->nome_dispositivo)

@section('content')
<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-0">Configurazione Scomparti</h2>
            <small class="text-muted">
                {{ $dispositivo->nome_dispositivo }} ({{ $dispositivo->codice_seriale }})
                &mdash; Paziente: {{ $dispositivo->paziente?->utente?->nome ?? 'N/D' }}
                {{ $dispositivo->paziente?->utente?->cognome ?? '' }}
            </small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="richiediMappa(this)">
                Sync da dispositivo
            </button>
            <button class="btn btn-outline-warning btn-sm" onclick="testBuzzer(this)">
                Test Buzzer
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="alert alert-{{ $dispositivo->stato === 'attivo' ? 'success' : 'danger' }} py-2 mb-4">
        <strong>Dispositivo:</strong>
        {{ $dispositivo->stato === 'attivo' ? 'Online' : 'Offline' }}
        @if($dispositivo->ultima_connessione)
            &mdash; ultima connessione: {{ \Carbon\Carbon::parse($dispositivo->ultima_connessione)->diffForHumans() }}
        @endif
        @if($dispositivo->temperatura)
            | {{ $dispositivo->temperatura }}°C
        @endif
        @if($dispositivo->umidita)
            {{ $dispositivo->umidita }}%
        @endif
    </div>

    <form method="POST" action="{{ route('dispositivi.scomparti.salva', $dispositivo->id) }}">
        @csrf

        <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
            @foreach($dispositivo->scomparti as $scomparto)
            <div class="col">
                <div class="card h-100 border-{{ $scomparto->pieno ? 'success' : 'secondary' }}">
                    <div class="card-header d-flex justify-content-between align-items-center
                        {{ $scomparto->pieno ? 'bg-success bg-opacity-10' : '' }}">
                        <strong>Scomparto {{ $scomparto->numero_scomparto }}</strong>
                        <span class="badge bg-secondary">{{ $scomparto->angolo }}&deg;</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Farmaco assegnato</label>
                            <select name="scomparti[{{ $scomparto->numero_scomparto }}][id_farmaco]"
                                    class="form-select form-select-sm">
                                <option value="0">— Nessun farmaco —</option>
                                @foreach($farmaci as $farmaco)
                                    <option value="{{ $farmaco->id }}"
                                        {{ $scomparto->id_farmaco == $farmaco->id ? 'selected' : '' }}>
                                        {{ $farmaco->nome }}
                                        @if($farmaco->dose)({{ $farmaco->dose }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   name="scomparti[{{ $scomparto->numero_scomparto }}][pieno]"
                                   id="pieno_{{ $scomparto->numero_scomparto }}"
                                   value="1"
                                   {{ $scomparto->pieno ? 'checked' : '' }}>
                            <label class="form-check-label" for="pieno_{{ $scomparto->numero_scomparto }}">
                                Scomparto carico
                            </label>
                        </div>
                    </div>
                    @if($scomparto->farmaco)
                    <div class="card-footer bg-transparent small text-muted">
                        {{ $scomparto->farmaco->descrizione ?? '' }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Salva e invia al dispositivo</button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Annulla</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const dispositivoId = {{ $dispositivo->id }};
const csrfToken     = '{{ csrf_token() }}';

async function richiediMappa(btn) {
    btn.disabled = true; btn.textContent = 'In attesa...';
    try {
        const res  = await fetch(`/mqtt/${dispositivoId}/mappa-scomparti`);
        const json = await res.json();
        alert(json.messaggio);
    } catch(e) { alert('Errore di comunicazione.'); }
    finally { btn.disabled = false; btn.textContent = 'Sync da dispositivo'; }
}

async function testBuzzer(btn) {
    btn.disabled = true;
    try {
        const res  = await fetch(`/mqtt/${dispositivoId}/buzzer-test`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
        });
        const json = await res.json();
        alert(json.messaggio);
    } finally { btn.disabled = false; }
}
</script>
@endpush
'@

# ==============================================================
Write-Host "16) Routes - aggiunta a web.php" -ForegroundColor Cyan
# ==============================================================

Append-IfMissing "routes\web.php" "pillmate:routes" @'


// ============================================================
// PillMate - Route aggiunte automaticamente (pillmate:routes)
// ============================================================
use App\Http\Controllers\DispositivoController;
use App\Http\Controllers\MqttController;

Route::get('/dispositivi/{idDispositivo}/scomparti',      [DispositivoController::class, 'scomparti'])->name('dispositivi.scomparti');
Route::post('/dispositivi/{idDispositivo}/scomparti',     [DispositivoController::class, 'salvaScomparti'])->name('dispositivi.scomparti.salva');
Route::patch('/dispositivi/{idDispositivo}/scomparti/{numeroScomparto}/stato', [DispositivoController::class, 'aggiornaStato'])->name('dispositivi.scomparti.stato');

Route::post('/mqtt/{idDispositivo}/configura-scomparti',  [MqttController::class, 'configuraScomparti'])->name('mqtt.configuraScomparti');
Route::post('/mqtt/{idDispositivo}/attiva-allarme',       [MqttController::class, 'attivaAllarme'])->name('mqtt.attivaAllarme');
Route::post('/mqtt/{idDispositivo}/eroga-farmaco',        [MqttController::class, 'erogaFarmaco'])->name('mqtt.erogaFarmaco');
Route::post('/mqtt/{idDispositivo}/set-sveglia',          [MqttController::class, 'setSveglia'])->name('mqtt.setSveglia');
Route::get('/mqtt/{idDispositivo}/mappa-scomparti',       [MqttController::class, 'richiediMappa'])->name('mqtt.richiediMappa');
Route::post('/mqtt/{idDispositivo}/buzzer-test',          [MqttController::class, 'testBuzzer'])->name('mqtt.testBuzzer');
'@

# ==============================================================
Write-Host ""
Write-Host "17) Esecuzione migration..." -ForegroundColor Cyan
# ==============================================================

$phpPath = "C:\xampp\php\php.exe"
if (Test-Path $phpPath) {
    Set-Location $projectRoot
    & $phpPath artisan migrate --force
    Write-Host ""
} else {
    Write-Host "  [WARN] PHP non trovato in $phpPath" -ForegroundColor Yellow
    Write-Host "  Esegui manualmente: php artisan migrate" -ForegroundColor Yellow
}

# ==============================================================
Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host "  Setup completato!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green
Write-Host ""
Write-Host "Prossimi passi:" -ForegroundColor White
Write-Host ""
Write-Host "  1. Verifica che php-mqtt/laravel-client sia installato:" -ForegroundColor White
Write-Host "     composer require php-mqtt/laravel-client" -ForegroundColor Yellow
Write-Host ""
Write-Host "  2. Pubblica la config MQTT (se non l'hai gia fatto):" -ForegroundColor White
Write-Host "     php artisan vendor:publish --provider=""PhpMqtt\Client\MqttClientServiceProvider"" --tag=config" -ForegroundColor Yellow
Write-Host ""
Write-Host "  3. Aggiungi nel .env le credenziali HiveMQ:" -ForegroundColor White
Write-Host "     MQTT_HOST=a143f9a321874b76a9ba2c00413148ec.s1.eu.hivemq.cloud" -ForegroundColor Yellow
Write-Host "     MQTT_PORT=8883" -ForegroundColor Yellow
Write-Host "     MQTT_USERNAME=esp32" -ForegroundColor Yellow
Write-Host "     MQTT_PASSWORD=Password123!" -ForegroundColor Yellow
Write-Host "     MQTT_USE_TLS=true" -ForegroundColor Yellow
Write-Host ""
Write-Host "  4. Avvia il listener MQTT (tenere aperto):" -ForegroundColor White
Write-Host "     php artisan mqtt:listen" -ForegroundColor Yellow
Write-Host ""
Write-Host "  5. Avvia lo scheduler (in un secondo terminale):" -ForegroundColor White
Write-Host "     php artisan schedule:work" -ForegroundColor Yellow
Write-Host ""
Write-Host "  Backup dei file originali in:" -ForegroundColor DarkGray
Write-Host "  $backupDir" -ForegroundColor DarkGray
Write-Host ""
