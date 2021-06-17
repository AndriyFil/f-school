<?php


	namespace App\Http\Controllers;


	use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;

    class MyClassController
	{
        public $school_id = '26';
        public $teach_id = '51';
        public $role = 'teacher';
        public function index()
        {
            $myclass = "0";
            $classes = "";
            $subjects = "";
            $subjects_class = "";
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

            return view('layouts.app', ['subjects' => json_encode($subjects)
                , 'classes' => json_encode($classes)
                , 'myclass' => json_encode($myclass[0])
                , 'subjects_class' => json_encode($subjects_class)
            ]);
        }

        public function getSchoolboys(Request $request) {

            if(empty($request->class) || empty($request->subject) || empty($request->section) || empty($request->date)) {
                return response()->json(['message' => 'Всі поля мають бути заповнені!']);
            }

            if(empty($this->school_id)) {
                return response()->json(['message' => 'Системна помилка!']);
            }

//            $study_periods = DB::select("
//                SELECT stupersch_date_from 'from', stupersch_date_to 'to', stupersch_active 'active' FROM study_periods_schools
//                WHERE  stupersch_id = '$request->period' ");
            $class = explode('-', $request->class); //[0] -> number, [1] -> letter
            //dd($request->date['start']);
            $date_start = $request->date['start'];
            $date_end = $request->date['end'];

            $date_year = substr($date_start, 0, 4);

            $year_second_semester = $date_year;
            $year_first_semester = $date_year;
            if($date_start >= $date_year.'-09-01' && $date_start <= $date_year.'-12-31') {
                $year_second_semester += 1;
            } elseif($date_start >= $date_year.'-01-01' && $date_start <= $date_year.'-07-30') {
                $year_first_semester -= 1;
            }

            $first_semester_date_from = $year_first_semester.'-09-01';
            $first_semester_date_to = $year_first_semester.'-12-31';
            $second_semester_date_from = $year_second_semester.'-01-01';
            $second_semester_date_to = $year_second_semester.'-07-01';


            //TODO: change hardcoded type_id, school_id. Change photo query -> (SELECT photo FROM users WHERE users.id = sch.schboy_user_id)

            $where = "
                    AND jou_class_number = '$class[0]'
                    AND jou_theme_id = '$request->section'
                    AND jou_subject_id = '$request->subject'
            ";

            $where_semester = "
                    AND journals.jou_class_number = '$class[0]' AND journals.jou_subject_id = '$request->subject'
            ";

            $schoolboys = DB::select("
                SELECT
                    CONCAT(sch.schboy_lastname, ' ', sch.schboy_firstname) 'name'
                    , j.jou_rating 'rating'
                    , jou_subject_id 'subject_id'
                    , jou_created 'date'
                    , jou_theme_id 'theme_id'
                    , jou_rating_type_id 'rating_type_id'
                    , (SELECT rattyp_name FROM rating_types WHERE  rattyp_id = jou_rating_type_id) 'rating_type_name'
                    , schboy_id 'schoolboy_id'
                    , schboy_photo 'photo'
                    , jou_record 'record'
                    , IFNULL((SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 6 ), (SELECT ROUND(AVG(journals.jou_rating), 1)
                                FROM journals
                                WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = '3' $where_semester
                                AND journals.jou_created BETWEEN '$first_semester_date_from' AND '$first_semester_date_to'
                            )) AS 'rating_first_semester'
                    , IFNULL((SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 7 ), (SELECT ROUND(AVG(journals.jou_rating), 1)
                                FROM journals
                                WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = '3' $where_semester
                                AND journals.jou_created BETWEEN '$second_semester_date_from' AND '$second_semester_date_to'
                            )) AS 'rating_second_semester'
                    , (SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 8 $where_semester) AS 'rating_year'
                    , (SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 4 $where) AS 'rating_control'
                    , IFNULL((SELECT journals.jou_rating FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 3 $where),  (SELECT IFNULL( ROUND(((AVG(journals.jou_rating)
                                        + (SELECT journals.jou_rating FROM journals WHERE journals.jou_rating_type_id = '4' AND journals.jou_schoolboy_id = sch.schboy_id $where)) / 2),1)
                                , ROUND(AVG(journals.jou_rating), 1))
                                    FROM journals
                                    LEFT JOIN rating_types ON journals.jou_rating_type_id = rating_types.rattyp_id
                                    WHERE journals.jou_schoolboy_id = sch.schboy_id AND rating_types.rattyp_group = 1
                                    $where
                     )) AS 'rating_theme'
                    , (SELECT COUNT(journals.jou_rating) FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where) 'rating_count'
                    , (SELECT COUNT(journals.jou_rating) FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where AND journals.jou_rating_type_id = 3) 'rating_theme_count'
                    , (SELECT jou_record FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where AND journals.jou_rating_type_id = 3) 'rating_theme_record'
                    , (SELECT jou_record FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where AND journals.jou_rating_type_id = 4) 'rating_control_record'
                    , (SELECT jou_record FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where_semester AND journals.jou_rating_type_id = 6) 'rating_first_semester_record'
                    , (SELECT jou_record FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where_semester AND journals.jou_rating_type_id = 7) 'rating_second_semester_record'
                    , (SELECT jou_record FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where_semester AND journals.jou_rating_type_id = 8) 'rating_year_record'
                    , (SELECT GROUP_CONCAT(CONCAT(rt.rattyp_name, ': ', journals.jou_rating) SEPARATOR '<br>') FROM journals
                    LEFT JOIN rating_types rt ON journals.jou_rating_type_id = rt.rattyp_id
                    WHERE journals.jou_schoolboy_id = sch.schboy_id AND (rt.rattyp_group = '1' OR rattyp_id = '4') $where
                    GROUP BY journals.jou_schoolboy_id
                    ) 'rating_theme_description'
                    , (SELECT GROUP_CONCAT(CONCAT(c.cur_lesson_theme, ': ', journals.jou_rating), '<br>') FROM journals
                    LEFT JOIN curricula c ON journals.jou_theme_id = c.cur_id
                    WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = '3' $where_semester
                    AND journals.jou_created BETWEEN '$first_semester_date_from' AND '$first_semester_date_to'
                    GROUP BY journals.jou_schoolboy_id
                    ) 'rating_first_semester_description'
                    , (SELECT GROUP_CONCAT(CONCAT(c.cur_lesson_theme, ': ', journals.jou_rating), '<br>') FROM journals
                    LEFT JOIN curricula c ON journals.jou_theme_id = c.cur_id
                    WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = '3' $where_semester
                    AND journals.jou_created BETWEEN '$second_semester_date_from' AND '$second_semester_date_to'
                    GROUP BY journals.jou_schoolboy_id
                    ) 'rating_second_semester_description'
                    FROM journals j
                RIGHT OUTER JOIN (SELECT * FROM schoolboys WHERE schboy_class_number = '$class[0]' AND schboy_class_letter = '$class[1]' AND schboy_school_id = '$this->school_id') sch on j.jou_schoolboy_id = sch.schboy_id
                    AND (j.jou_created >= '$date_start' AND j.jou_created <= '$date_end')
                    $where
                ORDER BY  sch.schboy_lastname, jou_created
            ");

            $date_arr = DB::select("
                SELECT
                    jou_created 'date'
                    FROM journals j
                RIGHT OUTER JOIN (SELECT * FROM schoolboys WHERE schboy_class_number = '$class[0]' AND schboy_class_letter = '$class[1]' AND schboy_school_id = '$this->school_id') sch on j.jou_schoolboy_id = sch.schboy_id
                    AND (j.jou_created >= '$date_start' AND j.jou_created <= '$date_end')
                    $where
                GROUP BY jou_created
                ORDER BY  sch.schboy_lastname, jou_created
            ");

            $journal_data = [];
            foreach ($schoolboys as $sch) {

                $journal_data[$sch->schoolboy_id]['name'] = $sch->name;
                $journal_data[$sch->schoolboy_id]['rating_control'] = $sch->rating_control;
                $journal_data[$sch->schoolboy_id]['rating_theme'] = $sch->rating_theme;
                $journal_data[$sch->schoolboy_id]['rating_first_semester'] = $sch->rating_first_semester;
                $journal_data[$sch->schoolboy_id]['rating_second_semester'] = $sch->rating_second_semester;
                $journal_data[$sch->schoolboy_id]['rating_year'] = $sch->rating_year;
                $journal_data[$sch->schoolboy_id]['photo'] = $sch->photo;
                $journal_data[$sch->schoolboy_id]['rating_theme_description'] = $sch->rating_theme_description;
                $journal_data[$sch->schoolboy_id]['rating_first_semester_description'] = $sch->rating_first_semester_description;
                $journal_data[$sch->schoolboy_id]['rating_second_semester_description'] = $sch->rating_second_semester_description;

                if(!empty($date_arr)) {
                    foreach ($date_arr as $date) {
                        if($date->date == $sch->date) {
                            if ($sch->rating_type_id == 3) {
                                $status = "success-color";
                            } elseif ($sch->rating_type_id == 4) {
                                $status = "warning-color";
                            } elseif ($sch->rating_type_id == 6) {
                                $status = "cyan-color";
                            } elseif ($sch->rating_type_id == 7) {
                                $status = "teal-color";
                            } elseif ($sch->rating_type_id == 8) {
                                $status = "purple-color";
                            } else {
                                $status = "";
                            }
                            $journal_data[$sch->schoolboy_id]['rating'][$date->date][$sch->record]['rating'] = $sch->rating;
                            $journal_data[$sch->schoolboy_id]['rating'][$date->date][$sch->record]['status'] = $status;
                            $journal_data[$sch->schoolboy_id]['rating'][$date->date][$sch->record]['rating_type_id'] = $sch->rating_type_id;
                            $journal_data[$sch->schoolboy_id]['rating'][$date->date][$sch->record]['rating_type_name'] = $sch->rating_type_name;
                            $journal_data[$sch->schoolboy_id]['rating'][$date->date][$sch->record]['input_id'] = "rating_$sch->record";
                        } else {
                            $journal_data[$sch->schoolboy_id]['rating'][$date->date][$sch->record]['rating'] = "";
                        }
                    }
                }
            }
            $dates_header = [];

            $i = 0;
            if(!empty($date_arr)) {
                foreach ($date_arr as $date) {
                    if(!empty($date->date)) {
                        $dates_header[$i]['date'] = $date->date;
                        $d = date('d.m',strtotime($date->date));
                        $dates_header[$i]['date_formatted'] = $d;
                        $i++;
                    }
                }
            }

            return response()->json(['body' => $journal_data, 'dates' => $dates_header]);
        }
	}
