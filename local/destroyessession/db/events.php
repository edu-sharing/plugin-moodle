<?php

$handlers = array (
    'user_logout' => array (
        'handlerfile'      => '/local/destroyessession/lib.php',
        'handlerfunction'  => 'destroy',
        'schedule'         => 'instant',
        'internal'         => 1,
    )
);