<?php

require dirname(__DIR__).'/vendor/autoload.php';

set_error_handler(function ($severity, $message, $file, $line) {
    if ((error_reporting() & $severity) && strpos($file, dirname(__DIR__).'/src/') === 0) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    return false;
});
