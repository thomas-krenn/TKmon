<?php
/**
 * This file is part of TKMON
 *
 * TKMON is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TKMON is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TKMON.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Marius Hein <marius.hein@netways.de>
 * @copyright 2012-2013 NETWAYS GmbH <info@netways.de>
 */

// ----------------------------------------------------------------------------
// This binary create the default message catalogue
// Based on Twig PHP templates and PHP source code
// ----------------------------------------------------------------------------

// Display errors outside any logs
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

$ds = DIRECTORY_SEPARATOR;
$dir = dirname(__dir__);

require implode($ds, array($dir, 'lib', 'tkmon', 'vendor', 'autoload.php'));

$tplDir = $dir. $ds. 'share'. $ds. 'tkmon'. $ds. 'templates';

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

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($tplDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    // force compilation
    if ($file->isFile()) {
        $twig->loadTemplate(str_replace($tplDir. $ds, '', $file));
    }
}

exec('/usr/bin/find '. $tmpDir. ' -name \*php -exec mv {} '. $tmpDir. ' \\;');

exec(
    '/usr/bin/xgettext'
    . ' --default-domain=messages -p '. $dir. '/share/tkmon/locales -o messages.pot --from-code=UTF-8'
    . ' -n --omit-header -L PHP '. $tmpDir. '/*php'
);

exec(
    '/usr/bin/xgettext'
    . ' --join-existing --default-domain=messages -p '. $dir. '/share/tkmon/locales -o messages.pot'
    . ' --from-code=UTF-8 -n --omit-header -L PHP $(find '. $dir. '/lib/tkmon -name *php)'
);

exec('/bin/rm -rf '. $tmpDir);
