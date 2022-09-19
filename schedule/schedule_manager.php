<?php

class schedule_manager{

    /**
     * Erstellt einen Termin mit den uebergebenen Parametern und schreibt diesen in die Datenbank
     * @param $date - das Datum des Termins
     * @param $starttime - Der Startzeitpunkt des Termins
     * @param $endtime - Der Endzeitpunkt des Termins
     * @param $course_id - Die ID des Kurses fuer den Termin
     * @param $description - Eine Beschreibung fuer den Termin
     * @param $password - Das Passwort, um sich fuer den Termin anzumelden
     * @param $user_id - Der User, der den Termin angelegt hat
     * @return bool - true, wenn der Termin erfolgreich angelegt werden konnte, false wenn nicht
     * @throws dml_exception
     */
    public function create_presentation_date($date, $starttime, $endtime, $course_id, $description, $password, $user_id):bool{
        global $DB;

        // Erstellen der Praesentation und Eintragen in die Datenbank
        $record_for_insert = new stdClass();
        $record_for_insert->created_by = $user_id;
        $record_for_insert->course_id = $course_id;
        $record_for_insert->date = $date;
        $record_for_insert->start = $starttime->getTimestamp();
        $record_for_insert->end = $endtime->getTimestamp();
        $record_for_insert->description = $description;
        $record_for_insert->password = password_hash($password, 1);
        $returnid = $DB->insert_record("presentation_date", $record_for_insert, true);

        // Frage alle Studenten des Kurses ab
        $students = $DB->get_records_sql("

        SELECT c.id, u.id

        FROM {course} c
        JOIN {context} ct ON c.id = ct.instanceid
        JOIN {role_assignments} ra ON ra.contextid = ct.id
        JOIN {user} u ON u.id = ra.userid
        JOIN {role} r ON r.id = ra.roleid

        where c.id = ".$course_id);

        // Fuer jeden eingeladenen Studenten, erstelle einen Datenbankeintrag und verschicke eine Benachrichtigung
        foreach($students as $i => $student) {
            $record_for_insert = new stdClass();
            $record_for_insert->presentation_id = $returnid;
            $record_for_insert->user_id = $student->id;
            $record_for_insert->signed_in = 'false';
            $DB->insert_record("presentation_user", $record_for_insert);

            // Studenten benachrichtigen

            $this->notify_student($student, $course_id, $returnid);

        }
        return true;
    }

    /**
     * Aktualisiert die Anmeldung eines Studenten in der Datenbank
     * @param $update_id - Die ID der Praesentation des Primary Keys, die aktualisiert werden soll
     * @param $url_id - Die ID der Praesentation
     * @param $user_id - Die ID des Studenten, fuer den der Eintrag aktualisiert werden soll
     * @return bool - true, wenn der Eintrag erfolgreich aktualisiert werden konnte, false, wenn nicht
     * @throws dml_exception
     */
    public function sign_in_student($update_id, $url_id, $user_id):bool{
        global $DB;

        $update = new stdClass();
        $update->id = $update_id;
        $update->presentation_id = $url_id;
        $update->user_id = $user_id;
        $update->signed_in = 'true';

        return $DB->update_record('presentation_user', $update);
    }

    /**
     * Gibt eine Liste aller Praesentationen zurueck, die der User erstellt hat
     * @param $user_id - die ID des Users
     * @return array - Eine Liste mit den erstellten Prasentationen
     * @throws dml_exception
     */
    public function get_presentations_from_user($user_id):array{
        global $DB;
        return $DB->get_records('presentation_date', ['created_by' => $user_id]);
    }

    /**
     * Benachrichtigt die eingeladenen Studenten, indem diese eine Nachricht erhalten
     * @param $student Der Student, der eine Nachricht erhalten soll
     * @param $course_id Der Kurs fuer den diese Einladung ist
     * @param $url_id - Die ID der Praesentation fuer die der Student eingeladen wurde
     * @return bool
     * @throws coding_exception
     * @throws moodle_exception
     */
    private function notify_student($student, $course_id, $url_id):bool{
        $eventdata = new \core\message\message();
        $eventdata->component         = 'local_schedule';    // the component sending the message. Along with name this must exist in the table message_providers
        $eventdata->name              = 'datenotification';        // type of message from that module (as module defines it). Along with component this must exist in the table message_providers
        $eventdata->userfrom          = core_user::get_noreply_user();      // user object
        $eventdata->userto            = $student;        // user object
        $eventdata->subject           = 'Einladung zur Präsentation einer Übungsaufgabe';   // very short one-line subject
        $eventdata->fullmessage       = 'Hallo, sie wurden für die Präsentation einer Übungsaufgabe eingeladen. Weitere Informationen finden Sie unter folgendem Link: http://127.0.0.1/local/schedule/signin.php?id='.$url_id;      // raw text
        $eventdata->fullmessageformat = FORMAT_PLAIN;   // text format
        $eventdata->fullmessagehtml   = '<p>Hallo,</p><p>Sie wurden für die Präsentation einer Übungsaufgabe eingeladen.</p><p>Weitere Informationen finden Sie unter folgendem Link:</p>';      // html rendered version
        $eventdata->smallmessage      = '';             // useful for plugins like sms or twitter
        $eventdata->courseid = $course_id; // This is required in recent versions, use it from 3.2 on https://tracker.moodle.org/browse/MDL-47162
        $eventdata->contexturl = (new \moodle_url('/local/schedule/signin.php',array('id' => $url_id))); // A relevant URL for the notification
        $eventdata->contexturlname = 'Anmeldung zur Präsentation'; // Link title explaining where users get to for the contexturl
        $result = message_send($eventdata);
        return true;

    }

}