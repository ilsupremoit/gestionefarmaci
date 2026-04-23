<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComuneController extends Controller
{
    public function cerca(Request $request)
    {
        $nomeRichiesto = mb_strtolower(trim($request->query('nome', '')));
        $path = storage_path('app/comuni.csv');

        if (!file_exists($path)) {
            return response()->json([
                'found' => false,
                'error' => 'File comuni.csv non trovato'
            ], 500);
        }

        $handle = fopen($path, 'r');

        if (!$handle) {
            return response()->json([
                'found' => false,
                'error' => 'Impossibile aprire il file CSV'
            ], 500);
        }

        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            fclose($handle);
            return response()->json([
                'found' => false,
                'error' => 'CSV vuoto o intestazione non valida'
            ], 500);
        }

        $header = array_map(fn($h) => mb_strtolower(trim($h)), $header);

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }

            $record = array_combine($header, $row);

            $nomeComune = mb_strtolower(trim($record['denominazione (italiana e straniera)'] ?? ''));
            $codice = trim($record['codice catastale del comune'] ?? '');

            if ($nomeComune === $nomeRichiesto) {
                fclose($handle);

                return response()->json([
                    'found' => true,
                    'nome' => $record['denominazione (italiana e straniera)'],
                    'codice' => $codice,
                ]);
            }
        }

        fclose($handle);

        return response()->json([
            'found' => false
        ]);
    }
}
