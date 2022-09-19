<?php

require_once("$CFG->libdir/formslib.php");


class manageform extends moodleform {
    public function definition() {
        global $CFG;
        global $DB;
        global $USER;
        $mform = $this->_form;
        $courses = enrol_get_all_users_courses($USER->id); // Frage die Kurse ab, in denen der Dozent unterrichtet
        //$courses = $DB->get_records('course');
        $courses_select = array();
        $i = 0;
        // Befuelle das Dropdown mit Kursen
        foreach ($courses as $course){
            if($course->category !=0){
                $courses_select[$course->id] = $course->fullname;
                $i++;
            }

        }
        // Seite zusammenbauen
        $mform->addElement('date_selector', 'date', 'Datum:');

        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] =  sprintf("%02d", $i) ;
        }
        for ($i = 0; $i < 60; $i++) {
            $minutes[$i] ="   " .  sprintf("%02d", $i);
        }

        $starttimearray=array();
        $starttimearray[]=& $mform->createElement('select', 'starthours', '', $hours);
        $starttimearray[]=& $mform->createElement('select', 'startminutes', '', $minutes);

        $endtimearray=array();
        $endtimearray[]=& $mform->createElement('select', 'endhours', '', $hours);
        $endtimearray[]=& $mform->createElement('select', 'endminutes', '', $minutes);


        $mform->addGroup($starttimearray,'timearr',' Beginn des Termins:' ,' ',false);
        $mform->addGroup($endtimearray,'timearr',' Ende des Termins:' ,' ',false);

        $mform->addElement('select', 'course', 'Kurs auswählen:', $courses_select);
        $attributes='size="50"';
        $mform->addElement('text', 'description', 'Beschreibung:', $attributes);
        $mform->setType('description', PARAM_TEXT);
        $mform->addElement('passwordunmask', 'password', 'Passwort:');

        $this->add_action_buttons(true, 'Termin anlegen');


///////////  Tabelle erstellen
            $userid = $USER->id;

            $results = $DB->get_records('presentation_date', ['created_by' => $userid]);

            if(empty($results)){
                $mform->addElement('html', '<h2> Keine Termine angelegt </h2>');
            }
            else{

                foreach ($results as $result){
                    $course = $DB->get_record('course', ['id' => $result->course_id]);
                    $starttime = date('H:i', $result->start);
                    $endtime = date('H:i', $result->end);
                    $date = userdate($result->date, get_string('strftimedaydate', 'core_langconfig'));
                    $mform->addElement('html', '<h2> Angelegte Termine </h2>');

                    $mform->addElement('html', '<details> <summary>' . $course->fullname . '  '. $date. '   ' . $starttime . ' Uhr - ' . $endtime . ' Uhr </summary><p>');
                    $mform->addElement('static', 'description_date', 'Beschreibung:', $result->description);
                    $mform->addElement('html', '</p><p>');

                    /*$table = new html_table();
                    $table->head = array('Name', 'Bereitschaft zur Präsentation'); */
                    $mform->addElement('html', '<table><tr><th>Name</th><th>Bereitschaft zur Präsentation</th></tr>');

                    $signed_students = $DB->get_records('presentation_user', ['presentation_id' => $result->id]);
                    // Fuer jeden eingeladenen Studenten, erstelle eine Tabellenzeile im HTML
                    foreach ($signed_students as $signed_student){
                        $student = $DB->get_record('user', ['id' => $signed_student->user_id]);
                        $fullname = $student->firstname. ' ' .$student->lastname;
                        $signed_in = $signed_student->signed_in;

                        if(strpos($signed_in, 'true') !== false){
                            $signed_in = 'Ja';
                        }
                        else{
                            $signed_in = 'Nein';
                        }
                        $mform->addElement('html', '<tr><td>'.$fullname.'</td><td>'.$signed_in.'</td></tr>');

                    }


                    $mform->addElement('html','</table></p></details>');
                    $mform->addElement('html','<style> table, th, td {border: 1px solid black;border-collapse: collapse;}th, td {padding: 15px;}</style>');

                }
            }









    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}