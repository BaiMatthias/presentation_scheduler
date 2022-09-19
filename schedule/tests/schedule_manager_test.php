<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/schedule/schedule_manager.php');

class schedule_manager_test extends advanced_testcase{
    /**
     * Testet, ob eine Prasentation ordnungsgemaess in die Datenbank geschrieben wurde
     * @return void
     * @throws dml_exception
     */
    public function test_create_presentation_date(){

        $this->resetAfterTest();
        $this->setUser(2); // Admin
        $manager = new schedule_manager();

        $schedule = $manager->get_presentations_from_user(2);
        $this->assertEmpty($schedule);



        $date = new DateTime('now');
        $starttime = new DateTime();
        $starttime->setTime(12,00);
        $endtime = new DateTime();
        $endtime->setTime(13,00);
        $course_id = 191000;
        $description = 'testbeschreibung';
        $password = 'pass';

        $results = $manager->create_presentation_date($date->format('U'), $starttime, $endtime, $course_id, $description, $password, 2);
        $this->assertTrue($results);




        $dateTimestamp = $date->getTimestamp();
        $startTimestamp = $starttime->getTimestamp();
        $endTimestamp = $endtime->getTimestamp();

        $result = $manager->get_presentations_from_user(2);

       // print_r($result);
        $this->assertNotEmpty($result);
        $id = "";
        foreach($result as $i => $i_value) {
            $id = $i_value->id;
        }

        $this->assertEquals($dateTimestamp, $result[$id]->date);
        $this->assertEquals($startTimestamp, $result[$id]->start);
        $this->assertEquals($endTimestamp, $result[$id]->end);
        $this->assertEquals($course_id, $result[$id]->course_id);
        $this->assertEquals($description, $result[$id]->description);

        $this->assertTrue(password_verify($password, $result[$id]->password));
        $this->assertEquals(2, $result[$id]->created_by);


    }

    /**
     * Testet, ob ein die Anmeldung des Studenten fuer einen Termin korrekt in der Datenbank erfasst wurde
     * @return void
     * @throws dml_exception
     */
    public function test_sign_in_student(){
        global $DB;
        $this->resetAfterTest();
        $this->setUser(2); // Admin
        $manager = new schedule_manager();

        $this->assertEmpty($DB->get_records('presentation_user'));


        $presentation_id = 1;
        $student_id = 3;

        $record_for_insert = new stdClass();
        $record_for_insert->presentation_id = $presentation_id;
        $record_for_insert->user_id = $student_id;
        $record_for_insert->signed_in = 'false';
        $return_id = $DB->insert_record("presentation_user", $record_for_insert, true);

        $this->assertIsInt($return_id);
        $result = $DB->get_record('presentation_user', ['id' => $return_id]);
        $this->assertNotEmpty($result);

        $this->assertEquals($presentation_id, $result->presentation_id);
        $this->assertEquals($student_id, $result->user_id);
        $this->assertEquals('false', $result->signed_in);

        $this->assertTrue($manager->sign_in_student($return_id, $presentation_id, $student_id));

        $result = $DB->get_record('presentation_user', ['id' => $return_id]);
        $this->assertNotEmpty($result);

        $this->assertEquals($presentation_id, $result->presentation_id);
        $this->assertEquals($student_id, $result->user_id);
        $this->assertEquals('true', $result->signed_in);
    }

    /**
     * Testet, ob das Passwort richtig geprueft wird. Zuerst wird ein falsches Passwort eingegeben, dann ein richtiges
     * @return void
     * @throws dml_exception
     */
    public function test_password_check(){

        $this->resetAfterTest();
        $this->setUser(2); // Admin
        $manager = new schedule_manager();

        $schedule = $manager->get_presentations_from_user(2);
        $this->assertEmpty($schedule);



        $date = new DateTime('now');
        $starttime = new DateTime();
        $starttime->setTime(12,00);
        $endtime = new DateTime();
        $endtime->setTime(13,00);
        $course_id = 191000;
        $description = 'testbeschreibung';
        $password = 'pass';

        $results = $manager->create_presentation_date($date->format('U'), $starttime, $endtime, $course_id, $description, $password, 2);
        $this->assertTrue($results);

        $result = $manager->get_presentations_from_user(2);

        $this->assertNotEmpty($result);
        $id = "";
        foreach($result as $i => $i_value) {
            $id = $i_value->id;
        }

        $this->assertFalse(password_verify('wrongpw', $result[$id]->password));
        $this->assertTrue(password_verify('pass', $result[$id]->password));


    }
}