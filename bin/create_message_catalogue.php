<?php

$ds = DIRECTORY_SEPARATOR;
$dir = dirname(__dir__);

require $dir. $ds. 'vendor'. $ds. 'autoload.php';

$tplDir = $dir. $ds. 'share'. $ds. 'templates';

$tmpDir = $ds. 'tmp'. $ds. 'tkmon-templates'. $ds;

$loader = new Twig_Loader_Filesystem($tplDir);

// force auto-reload to always have the latest version of the template
$twig = new Twig_Environment($loader, array(
    'cache' => $tmpDir,
    'auto_reload' => true
));

$twig->addExtension(new Twig_Extensions_Extension_I18n());

$c = new \Pimple();

$twig->addExtension(new \TKMON\Twig\Extension($c));

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tplDir), RecursiveIteratorIterator::LEAVES_ONLY);
foreach ($iterator as $file) {
    // force compilation
    if ($file->isFile()) {
        $twig->loadTemplate(str_replace($tplDir. $ds, '', $file));
    }
}

exec('/usr/bin/find '. $tmpDir. ' -name \*php -exec mv {} '. $tmpDir. ' \\;');

exec('/usr/bin/xgettext --default-domain=messages -p '. $dir. '/share/locales -o messages.pot --from-code=UTF-8 -n --omit-header -L PHP '. $tmpDir. '/*php');

exec('/bin/rm -rf '. $tmpDir);