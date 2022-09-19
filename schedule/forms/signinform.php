<?php

require_once("$CFG->libdir/formslib.php");



class signinform extends moodleform {

    public function definition() {
        global $CFG;
        global $DB;
        global $USER;
        $mform = $this->_form;
        $urlid = $this->_customdata['id'];
        $message = 'Kein Termin gefunden';

        $mform->addElement('hidden', 'id', $urlid);
        $mform->setType('id', PARAM_INT);
        // Es muss eine Id in der URL vorhanden sein, sonst Fehlermeldung
        if(is_null($urlid)){
            $mform->addElement('html', '<h3>'.\core\notification::error($message).'</h3>');
        }
        // Wenn der Student nicht fuer diesen Termin eingeladen ist und trotzdem die Anmeldeseite besucht
        elseif (!$DB->get_record('presentation_user', ['presentation_id' => $urlid, 'user_id' => $USER->id])){
            $mform->addElement('html', '<h3>'.\core\notification::error($message).'</h3>');
        }
        else{
            // Frage die Prasentation und den Kursnamen mit der ID einer Praesentation ab
            $result = $DB->get_record_sql('SELECT mdl_presentation_date.id, mdl_presentation_date.course_id, mdl_presentation_date.date,
            mdl_presentation_date.start, mdl_presentation_date.end, mdl_presentation_date.description, mdl_presentation_date.password, mdl_course.fullname FROM mdl_presentation_date
            JOIN mdl_course ON mdl_presentation_date.course_id=mdl_course.id WHERE mdl_presentation_date.id='.$urlid);

            $mform->addElement('static', 'course_name', 'Kurs:', $result->fullname);
            $date = userdate($result->date, get_string('strftimedaydate', 'core_langconfig'));
            $mform->addElement('static', 'date', 'Datum:', $date);
            $starttime = date('H:i', $result->start);
            $endtime = date('H:i', $result->end);
            $mform->addElement('static', 'time_period', 'Zeitraum:', $starttime . ' Uhr - '.$endtime.' Uhr');
            $mform->addElement('static', 'description', 'Beschreibung:', $result->description);

            $mform->addElement('passwordunmask', 'password', 'Passwort');

            $this->add_action_buttons(true, 'Eintragen');
        }














    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}