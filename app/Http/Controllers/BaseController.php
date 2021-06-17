<?php


	namespace App\Http\Controllers;


	use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    class BaseController
	{
	    public $user_id = 0;
	    public $school_id = 0;
	    public $teach_id = 0;
	    public $schoolboy_id = 0;
	    public $class_number = 0;
	    public $role = "";
	    public $user_data = [];

        public function __construct($user = [],$teach_id = 0, $schoolboy_id = 0)
        {
//            $this->profile = new AuthController;
//            $this->profile->profile();
//            dd($this->profile->profile());

            $this->teach_id = $teach_id;
            $this->schoolboy_id = $schoolboy_id;
            $this->user_data = $user;
        }

        public function setClassNumber($class_number) {
            $this->class_number = $class_number;
        }

        public function index()
        {
            return view('welcome');
        }


        public function getSubjects() {
            $subjects = DB::select('
                SELECT
                    sub_id  \'id\'
                    , sub_name \'name\'
                FROM
                    subject_teachers subtea
                LEFT JOIN subjects sub ON  subtea.subtea_subject_id = sub.sub_id
                WHERE subtea_teacher_id = (SELECT tea_id FROM teachers WHERE tea_user_id = "'.$this->teach_id.'")
            ');
            return $subjects;
        }

        public function getClasses() {
            $teacher_classes = DB::select('
                SELECT
                    tea_class_from \'class_from\'
                    , tea_class_to \'class_to\'
                FROM
                    teachers
                WHERE tea_user_id = "'.$this->teach_id.'"
                LIMIT 1
            ');

            $classes = DB::select('
                SELECT
                    CONCAT(schboy_class_number, \'-\',  schboy_class_letter) \'class\'
                FROM
                    schoolboys
                WHERE schboy_class_number >= "'.$teacher_classes[0]->class_from.'"
                and schboy_class_number <= "'.$teacher_classes[0]->class_to.'"
                and schboy_school_id <= "'.$this->user_data->school_id.'"
                GROUP BY class
            ');
            return $classes;
        }

        public function getMyClass() {
            $teacher_class = DB::select('
                SELECT
                    CONCAT(tea_class_number, "-", tea_class_letter) class
                FROM
                    teachers
                WHERE tea_user_id = "'.$this->teach_id.'"
                LIMIT 1
            ');
            return $teacher_class;
        }

        public function getSubjectByClass() {
            $subjects = DB::select("
                SELECT
                    sub_name 'name'
                    , sub_id 'id'
                FROM
                    subjects
                LEFT JOIN subjects_classes ON subjects.sub_id = subjects_classes.subject_id
                WHERE subjects_classes.class_number = '$this->class_number'
            ");
            return $subjects;
        }
	}
