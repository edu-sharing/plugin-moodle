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
        'eventname' => '\core\event\course_category_created',
        'callback' => 'mod_edusharing_observer::course_category_created',
    ),
    array(
        'eventname' => '\core\event\course_category_updated',
        'callback' => 'mod_edusharing_observer::course_category_updated',
    ),
    array(
        'eventname' => '\core\event\course_created',
        'callback' => 'mod_edusharing_observer::course_created',
    ),
    array(
        'eventname' => '\core\event\user_created',
        'callback' => 'mod_edusharing_observer::user_signup',
    ),
);
