<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\core\event\course_restored',
        'callback' => 'mod_edusharing_observer::course_restored',
    ),
);

