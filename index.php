<?php
require_once 'lib/bones.php';
require_once 'lib/Misc.php';

function fnc_detailed_err($errno, $errstr, $errfile, $errline, $errcontext) {
    $var = array('errno' => $errno, 'errstr' => $errstr, 'errfile' => $errfile,
        'errline' => $errline, 'errcontext' => $errcontext);
    // envoie systématique dans la log
    ob_start();
    var_dump($var);
    $dump = ob_get_clean();
    error_log($dump);

    die();
}

set_error_handler('fnc_detailed_err', E_ERROR | E_USER_ERROR);

// stockage du Path standard, peut être pratique dans certains cas
define('APP_PATH_STD', realpath(dirname(__FILE__)));

// directory setup and class loading
set_include_path('.'
        . PATH_SEPARATOR . APP_PATH_STD
        . PATH_SEPARATOR . APP_PATH_STD . DIRECTORY_SEPARATOR . 'lib' .
        DIRECTORY_SEPARATOR
        . PATH_SEPARATOR . APP_PATH_STD . DIRECTORY_SEPARATOR . 'lib' .
        DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
        . PATH_SEPARATOR . APP_PATH_STD . DIRECTORY_SEPARATOR . 'lib' .
        DIRECTORY_SEPARATOR . 'macaronDB' . DIRECTORY_SEPARATOR
        . PATH_SEPARATOR . get_include_path());

require_once 'context/config.php';
require_once 'context/configdb.php';
require_once 'routes.php';

