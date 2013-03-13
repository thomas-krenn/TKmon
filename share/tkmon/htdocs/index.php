<?php
// Composer snippet
require dirname(dirname(dirname(__DIR__))). DIRECTORY_SEPARATOR. 'vendor'. DIRECTORY_SEPARATOR. 'autoload.php';

// Perform a scoped run!
\TKMON\Binary\Web::run();