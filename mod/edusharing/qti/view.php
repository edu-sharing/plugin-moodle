<?php
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/mod/edusharing/qti/renderer.php');


$PAGE->set_url('/mod/edusharing/qti/view.php', array('id' => 1));
$title = 'olihasljkh';//$course->shortname . ': ' . format_string($quiz->name);
$PAGE->set_title($title);
$PAGE->set_heading('asdasdasd');
//$PAGE->set_context(context_module::instance(25));
$output = $PAGE->get_renderer('mod_edusharing_qti');




echo $OUTPUT->header();
/*
if (isguestuser()) {
    // Guests can't do a quiz, so offer them a choice of logging in or going back.
    echo $output->view_page_guest($course, $quiz, $cm, $context, $viewobj->infomessages);
} else if (!isguestuser() && !($canattempt || $canpreview
          || $viewobj->canreviewmine)) {
    // If they are not enrolled in this course in a good enough role, tell them to enrol.
    echo $output->view_page_notenrolled($course, $quiz, $cm, $context, $viewobj->infomessages);
} else {*/
    echo $output->renderswitch();
/*}*/

echo $OUTPUT->footer();

