<?php

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/local/schedule/schedule_manager.php');
class simpleform extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        global $DB;
        global $USER;
        $mform = $this->_form;

        $manager = new schedule_manager();

        $presentationstring='';
        $manageurl = new moodle_url('/local/schedule/manage.php');
        $results = $manager->get_presentations_from_user($USER->id);
        foreach ($results as $result){ // Zeige die Praesentationen des Users im Block an
            $course = $DB->get_record('course', ['id' => $result->course_id]);
            $starttime = date('H:i', $result->start);
            $endtime = date('H:i', $result->end);
            $date = userdate($result->date, get_string('strftimedaydate', 'core_langconfig'));
            $presentationstring .= $course->fullname . '  '. $date. '   ' . $starttime . ' Uhr - ' . $endtime . ' Uhr <br>';
        }

        if($presentationstring !== ''){ // Wenn keine Praesentationen vorhanden, Standardtext ausgeben
            $mform->addElement('html', '<p style=”text-align: left;">'.$presentationstring.'</p>');
        }
        else{
            $mform->addElement('html', '<p style=”text-align: left;">Keine Termine angelegt</p>');
        }
        $mform->addElement('html', '<input type="button" class="btn btn-primary" value="Verwalten" onclick=location.href="'.$manageurl.'">');
                                                            //\"window.location.href='www.example.com/page.html?id=".$id."'\">

    }
    function validation($data, $files) {
        return array();
    }
}