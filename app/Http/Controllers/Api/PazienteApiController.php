<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assunzione;
use App\Models\Dispositivo;
use App\Models\Notifica;
use App\Models\Paziente;
use App\Models\Terapia;
use Illuminate\Http\Request;

class PazienteApiController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $paziente = $this->resolvePaziente($request);

        if (!$paziente) {
            return response()->json([
                'message' => 'Nessun paziente collegato a questo account.',
            ], 404);
        }

        $paziente->load('utente');

        return response()->json([
            'ruolo' => $user->ruolo,
            'titolo' => 'Dashboard paziente',
            'messaggio' => 'Dati caricati dal backend Laravel',
            'paziente' => [
                'id' => $paziente->id,
                'nome' => $paziente->utente->nome ?? '',
                'cognome' => $paziente->utente->cognome ?? '',
                'email' => $paziente->utente->email ?? '',
                'telefono' => $paziente->utente->telefono ?? '',
                'dataNascita' => optional($paziente->data_nascita)?->format('Y-m-d'),
                'indirizzo' => $paziente->indirizzo,
                'codiceFiscale' => $paziente->codice_fiscale,
                'noteMediche' => $paziente->note_mediche,
            ],
            'statistiche' => [
                ['label' => 'Terapie attive', 'value' => (string) Terapia::where('id_paziente', $paziente->id)->where('attiva', true)->count()],
                ['label' => 'Assunzioni oggi', 'value' => (string) Assunzione::whereHas('somministrazione.terapia', function ($query) use ($paziente) {
                    $query->where('id_paziente', $paziente->id);
                })->whereDate('data_prevista', now()->toDateString())->count()],
                ['label' => 'Notifiche', 'value' => (string) Notifica::where('id_utente', $user->id)->count()],
                ['label' => 'Dispositivi', 'value' => (string) Dispositivo::where('id_paziente', $paziente->id)->count()],
            ],
        ]);
    }

    public function terapie(Request $request)
    {
        $paziente = $this->resolvePaziente($request);
        if (!$paziente) {
            return response()->json([]);
        }

        return Terapia::with('farmaco')
            ->where('id_paziente', $paziente->id)
            ->get()
            ->map(function (Terapia $terapia) {
                return [
                    'id' => $terapia->id,
                    'farmaco' => $terapia->farmaco->nome ?? 'Farmaco',
                    'dosaggio' => $terapia->quantita ?? '-',
                    'frequenza' => $terapia->frequenza ?? '-',
                    'dataInizio' => optional($terapia->data_inizio)?->format('Y-m-d'),
                    'dataFine' => optional($terapia->data_fine)?->format('Y-m-d'),
                    'attiva' => (bool) $terapia->attiva,
                ];
            })
            ->values();
    }

    public function storico(Request $request)
    {
        $paziente = $this->resolvePaziente($request);
        if (!$paziente) {
            return response()->json([]);
        }

        return Assunzione::with('somministrazione.terapia.farmaco')
            ->whereHas('somministrazione.terapia', function ($query) use ($paziente) {
                $query->where('id_paziente', $paziente->id);
            })
            ->latest('data_prevista')
            ->limit(50)
            ->get()
            ->map(function (Assunzione $assunzione) {
                return [
                    'id' => $assunzione->id,
                    'farmaco' => optional(optional(optional($assunzione->somministrazione)->terapia)->farmaco)->nome ?? 'Farmaco',
                    'dataPrevista' => optional($assunzione->data_prevista)?->format('Y-m-d H:i'),
                    'stato' => $assunzione->stato,
                    'scompartoNumero' => $assunzione->scomparto_numero,
                    'noteEvento' => $assunzione->note_evento,
                ];
            })
            ->values();
    }

    public function dispositivi(Request $request)
    {
        $paziente = $this->resolvePaziente($request);
        if (!$paziente) {
            return response()->json([]);
        }

        return Dispositivo::where('id_paziente', $paziente->id)
            ->get()
            ->map(function (Dispositivo $dispositivo) {
                return [
                    'id' => $dispositivo->id,
                    'codiceSeriale' => $dispositivo->codice_seriale,
                    'nomeDispositivo' => $dispositivo->nome_dispositivo,
                    'stato' => $dispositivo->stato,
                    'batteria' => $dispositivo->batteria,
                    'temperatura' => $dispositivo->temperatura,
                    'umidita' => $dispositivo->umidita,
                    'allarmeAttivo' => (bool) $dispositivo->allarme_attivo,
                ];
            })
            ->values();
    }

    public function notifiche(Request $request)
    {
        return Notifica::where('id_utente', $request->user()->id)
            ->latest('data_invio')
            ->limit(50)
            ->get()
            ->map(function (Notifica $notifica) {
                return [
                    'id' => $notifica->id,
                    'titolo' => $notifica->titolo ?? 'Notifica',
                    'messaggio' => $notifica->messaggio,
                    'dataInvio' => optional($notifica->data_invio)?->format('Y-m-d H:i'),
                    'letta' => (bool) $notifica->letta,
                ];
            })
            ->values();
    }

    private function resolvePaziente(Request $request): ?Paziente
    {
        $user = $request->user();

        if ($user->ruolo === 'paziente') {
            return $user->paziente;
        }

        if ($user->ruolo === 'familiare') {
            return $user->pazientiFamiliari()->with('utente')->first();
        }

        return null;
    }
}
