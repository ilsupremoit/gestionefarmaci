<?php
// Questo script cerca e distrugge gli spazi vuoti e i BOM invisibili
// all'inizio di tutti i tuoi file PHP nella cartella "app".

$directory = new RecursiveDirectoryIterator(__DIR__ . '/app');
$iterator = new RecursiveIteratorIterator($directory);
$files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$contatore = 0;

foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);

    // 1. Rimuove il BOM invisibile (UTF-8)
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
        file_put_contents($path, $content);
        echo "BOM Rimosso da: $path\n";
        $contatore++;
    }

    // 2. Rimuove spazi vuoti prima di <?php
    $trimmed = ltrim($content);
    if ($trimmed !== $content && str_starts_with($trimmed, '<?php')) {
        file_put_contents($path, $trimmed);
        echo "Spazi rimossi da: $path\n";
        $contatore++;
    }
}

echo "\nPulizia completata! File sistemati: $contatore\n";
