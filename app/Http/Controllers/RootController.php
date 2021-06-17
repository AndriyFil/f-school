<?php


	namespace App\Http\Controllers;


	use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    class RootController
	{
        public function index()
        {
            $myclass = "0";
            $classes = "";
            $subjects = "";
            $subjects_class = "";
            $schoolboys = "";
            $teach_id = "";
            if(!empty(Auth::user()) && Auth::user()->role == "teacher") {
                $this->school_id = Auth::user()->school_id;
                $this->teach_id = Auth::user()->id;
                $this->role = Auth::user()->role;
                $base_obj = new BaseController(Auth::user(), $this->teach_id);
                $subjects = $base_obj->getSubjects();
                $classes = $base_obj->getClasses();
                $myclass = $base_obj->getMyClass();
                $base_obj->setClassNumber($myclass[0]->class[0]);
                $subjects_class = $base_obj->getSubjectByClass();


            }
            if(!empty(Auth::user()) && Auth::user()->role == "parent") {
                $this->school_id = Auth::user()->school_id;
                $this->parent_id = Auth::user()->id;
                $this->role = Auth::user()->role;
//                $base_obj = new BaseController(Auth::user(), $this->parent_id);
//                $classes = $base_obj->getClasses();
//                $base_obj->setClassNumber($myclass[0]->class[0]);
//                $subjects_class = $base_obj->getSubjectByClass();

                $schoolboys = DB::select("
                    SELECT * FROM schoolboys LEFT JOIN schoolboys_parents ON schboy_id = schoolboys_parents.schoolboy_id
                    WHERE schoolboys_parents.parent_id = '$this->parent_id'
                ");
            }



            return view('layouts.app'
                , [
                    'schoolboys' => json_encode($schoolboys)
                    , 'subjects' => json_encode($subjects)
                    , 'classes' => json_encode($classes)
                    , 'myclass' => json_encode($myclass[0])
                    , 'subjects_class' => json_encode($subjects_class)
                ]
            );
        }
	}
