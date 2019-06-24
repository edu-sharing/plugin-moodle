<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\core\event\course_module_deleted',
        'callback' => 'mod_edusharing_observer::course_module_deleted',
        'priority ' => 5000,
    ),
    array(
        'eventname' => '\core\event\course_module_created',
        'callback' => 'mod_edusharing_observer::course_module_created',
    ),
    array(
        'eventname' => '\core\event\course_deleted',
        'callback' => 'mod_edusharing_observer::course_deleted',
    ),
);
