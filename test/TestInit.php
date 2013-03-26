<?php
// Composer snippet
require dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . 'lib'
    . DIRECTORY_SEPARATOR
    . 'tkmon'
    . DIRECTORY_SEPARATOR
    . 'vendor'
    . DIRECTORY_SEPARATOR
    . 'autoload.php';

// Test mocks

define(
    'TEST_LIB_DIR',
    'lib'. DIRECTORY_SEPARATOR. 'TKMON'. DIRECTORY_SEPARATOR. 'Test'
);

require TEST_LIB_DIR. DIRECTORY_SEPARATOR. 'Container.php';