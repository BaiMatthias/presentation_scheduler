<?php
/**
 * Version details
 *
 * @package    local_schedule
 * @author     Matthias Baidinger
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/schedule/forms/signinform.php');
require_once($CFG->dirroot . '/local/schedule/schedule_manager.php');
global $DB;
global $USER;

$PAGE->set_url(new moodle_url('/local/schedule/signin.php?'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_pagelayout('base');
$PAGE->set_title("Für Präsentation anmelden");
$PAGE->set_heading("Für Präsentation anmelden");


global $DB;
global $USER;
$urlid = optional_param('id', null, PARAM_INT);
$to_form = array('id' => $urlid);
$mform =  new signinform(null, $to_form);
$homeurl = new moodle_url('/');
$message = 'Passwort ist nicht korrekt';
if ($mform->is_cancelled()) {
    redirect($homeurl, 'Der Vorgang wurde abgebrochen.');
} else if ($fromform = $mform->get_data()) {

    $pageurl = new moodle_url('/local/schedule/signin.php?id='.$urlid);


    $result = $DB->get_record('presentation_date', ['id' => $urlid]);
    $update = $DB->get_record('presentation_user', ['presentation_id' => $urlid, 'user_id' => $USER->id]);
    if(password_verify($fromform->password, $result->password)) {
        // Erst wenn Passwort richtig, wird fortgefahren
        $manager = new schedule_manager();
        $ergebnis = $manager->sign_in_student($update->id, $urlid, $USER->id);

        if($ergebnis){
            redirect($homeurl, 'Sie haben sich erfolgreich für den Termin eingetragen.', null, \core\output\notification::NOTIFY_SUCCESS);
        }
        else{
            redirect($pageurl, 'Fehler beim Anmelden aufgetreten.', null, \core\output\notification::NOTIFY_ERROR);
        }
    }   else {
        redirect($pageurl, 'Das Passwort war nicht korrekt. Bitte versuchen Sie es erneut.', null, \core\output\notification::NOTIFY_ERROR);
    }


    }



echo $OUTPUT->header();
$mform->display();

echo $OUTPUT->footer();