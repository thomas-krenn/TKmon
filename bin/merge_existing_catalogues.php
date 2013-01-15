<?php

$ds = DIRECTORY_SEPARATOR;
$dir = dirname(__dir__);

$localesDir = $dir. $ds. 'share'. $ds. 'locales';

$baseCatalogue = $localesDir. $ds. 'messages.pot';

exec('find '. $localesDir. ' -mindepth 1 -name *.po -exec msgmerge -v -U {} '. $baseCatalogue. ' \\;');
exec('find '. $localesDir. ' -mindepth 1 -name *.po~ -exec rm -v {} \\;');