<?php

$handlers = array (
    'user_created' => array (
        'handlerfile'      => '/local/autoassignrole/lib.php',
        'handlerfunction'  => 'assign',
        'schedule'         => 'instant',
        'internal'         => 1,
    )
);