<?php

$ds = DIRECTORY_SEPARATOR;
$dir = dirname(__dir__);

$localesDir = $dir. $ds. 'share'. $ds. 'locales';

$baseCatalogue = $localesDir. $ds. 'messages.pot';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($localesDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if (strpos($file, '.po') === strlen($file)-3) {
        $moFile = substr($file, 0, strlen($file)-3). '.mo';

        $short = basename($file);

        if (file_exists($moFile)) {
            echo "Binary exists, unlink\n";
            unlink($moFile);
        }

        echo "Compile $short to binary ... ";

        exec(
            '/usr/bin/msgfmt'
            . ' -o '. $moFile
            . ' '. $file
        );

        echo "done\n";
    }
}