<?php
/**
 * Version details
 *
 * @package    local_schedule
 * @author     Matthias Baidinger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/schedule/forms/manageform.php');
require_once($CFG->dirroot . '/local/schedule/schedule_manager.php');
global $DB;
global $USER;

$PAGE->set_url(new moodle_url('/local/schedule/manage.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('base');
$PAGE->set_title("Termin erstellen");
$PAGE->set_heading("Termin erstellen");



$mform =  new manageform();
$homeurl = new moodle_url('/');
if ($mform->is_cancelled()) {

    redirect($homeurl, 'Der Vorgang wurde abgebrochen.');
} else if ($fromform = $mform->get_data()) {

    $manager = new schedule_manager();
    $date = $fromform->date;

    $starttime = new DateTime();
    $starttime->setTime($fromform->starthours, $fromform->startminutes);

    $endtime = new DateTime();
    $endtime->setTime($fromform->endhours, $fromform->endminutes);


    $course_id = $fromform->course;
    $description = $fromform->description;
    $password = $fromform->password;

    $createresult = $manager->create_presentation_date($date, $starttime, $endtime, $course_id, $description, $password, $USER->id);

  /*  $context = context_course::instance($course_id);
    $students = get_enrolled_users($context);

    foreach($students as $i => $student) {
        $record_for_insert = new stdClass();
        $record_for_insert->presentation_id = $returnid;
        $record_for_insert->user_id = $student->id;
        $record_for_insert->signed_in = 'false';
        $DB->insert_record("presentation_user", $record_for_insert);

        // Studenten benachrichtigen

        $eventdata = new \core\message\message();
        $eventdata->component         = 'local_schedule';    // the component sending the message. Along with name this must exist in the table message_providers
        $eventdata->name              = 'datenotification';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
        $eventdata->userfrom          = core_user::get_noreply_user();      // user object
        $eventdata->userto            = $student;        // user object
        $eventdata->subject           = 'Einladung zur Präsentation einer Übungsaufgabe';   // very short one-line subject
        $eventdata->fullmessage       = 'Hallo, sie wurden für die Präsentation einer Übungsaufgabe eingeladen. Weitere Informationen finden Sie unter folgendem Link: http://127.0.0.1/local/schedule/signin.php?id='.$returnid;      // raw text
        $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
        $eventdata->fullmessagehtml   = '<p>Hallo,</p><p>sie wurden für die Präsentation einer Übungsaufgabe eingeladen.</p><p>Weitere Informationen finden Sie unter folgendem Link:</p>';      // html rendered version
        $eventdata->smallmessage      = '';             // useful for plugins like sms or twitter
        $eventdata->courseid = $course_id; // This is required in recent versions, use it from 3.2 on https://tracker.moodle.org/browse/MDL-47162
        $eventdata->contexturl = (new \moodle_url('/local/schedule/signin.php',array('id' => $returnid))); // A relevant URL for the notification
        $eventdata->contexturlname = 'Anmeldung zur Präsentation'; // Link title explaining where users get to for the contexturl
// <p>http://127.0.0.1/local/schedule/signin.php?id='.$returnid.'</p>
        $result = message_send($eventdata);



    } */
    if($createresult){
        redirect($homeurl, 'Der Termin wurde erfolgreich erstellt.', null, \core\output\notification::NOTIFY_SUCCESS);
    }
    else{
        redirect($homeurl, 'Bei der Erstellung des Termins sind Fehler aufgetreten.', null, \core\output\notification::NOTIFY_ERROR);
    }

}
echo $OUTPUT->header();
$mform->display();

echo $OUTPUT->footer();