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
        'eventname' => '\core\event\course_module_updated',
        'callback' => 'mod_edusharing_observer::course_module_updated',
    ),
    array(
        'eventname' => '\core\event\course_section_created',
        'callback' => 'mod_edusharing_observer::course_section_created',
    ),
    array(
        'eventname' => '\core\event\course_section_updated',
        'callback' => 'mod_edusharing_observer::course_section_updated',
    ),
    array(
        'eventname' => 'core\event\course_deleted',
        'callback' => 'mod_edusharing_observer::course_deleted',
    ),
    array(
        'eventname' => '\core\event\course_restored',
        'callback' => 'mod_edusharing_observer::course_restored',
    )
);

