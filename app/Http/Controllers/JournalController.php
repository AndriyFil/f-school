<?php


	namespace App\Http\Controllers;
    use App\Journal;
    use App\RatingType;
    use App\StudyPeriodsSchools;
    use App\StudyPeriods;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Http\Request;
    use App\Administration;
    use App\User;
    use App\School;
    use Carbon\Carbon;
    use Validator;
    use Exception;

    class JournalController
	{
        private $photo_path =  "img/user/elon-musk.png";
        public $school_id = '26';
        public $teach_id = '51';
        public $role = 'teacher';
	    public $profile = [];
	    public function __construct()
        {
//            $this->profile = new AuthController;
//            $this->profile->profile();
//            dd($this->profile->profile());
        }

        public function index()
        {
            $subjects = "";
            $classes = "";
            if(!empty(Auth::user())) {
                $this->school_id = Auth::user()->school_id;
                $this->teach_id = Auth::user()->id;
                $this->role = Auth::user()->role;
                $base_obj = new BaseController(Auth::user(), $this->teach_id);
                $subjects = $base_obj->getSubjects();
                $classes = $base_obj->getClasses();
            }
            return view('layouts.app', ['subjects' => json_encode($subjects), 'classes' => json_encode($classes)]);
        }

//        public function getSubjectsClasses() {
//
//            $subjects = DB::select('
//                SELECT
//                    sub_id  \'id\'
//                    , sub_name \'name\'
//                FROM
//                    subject_teachers subtea
//                LEFT JOIN subjects sub ON  subtea.subtea_subject_id = sub.sub_id
//                WHERE subtea_teacher_id = (SELECT tea_id FROM teachers WHERE tea_user_id = "'.$this->teach_id.'")
//            ');
//            $teacher_classes = DB::select('
//                SELECT
//                    tea_class_from \'class_from\'
//                    , tea_class_to \'class_to\'
//                    , tea_class_to \'class_to\'
//                FROM
//                    teachers
//                WHERE tea_user_id = "'.$this->teach_id.'"
//            ');
//
//            $classes = DB::select('
//                SELECT
//                    CONCAT(schboy_class_number, \'-\',  schboy_class_letter) \'class\'
//                FROM
//                    schoolboys
//                WHERE schboy_class_number >= "'.$teacher_classes[0]->class_from.'"
//                and schboy_class_number <= "'.$teacher_classes[0]->class_to.'"
//                and schboy_school_id <= "'.$this->school_id.'"
//                GROUP BY class
//            ');
//
//            return response()->json(['subjects' => $subjects, 'classes' => $classes]);
//        }

        public function getThemes($subject_id, $class_number) {

            $themes = DB::select('
                SELECT
                    cur_id \'id\'
                    , cur_lesson_theme \'name\'
                FROM
                    curricula
                WHERE cur_school_id = "'.$this->school_id.'"
                    AND cur_class_number = "'.$class_number.'"
                    AND cur_subject_id = "'.$subject_id.'"
            ');

            return response()->json($themes);
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
            $date_arr[0] = $date_start;

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

            while(end($date_arr) != $date_end) {
                $date = new \DateTime(end($date_arr));
                $date = $date->modify('+1 day');
                $date = date_format($date,'Y-m-d');
                $date_arr[] = $date;
            }

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


            $dates_main_ratings = DB::select("
                SELECT
                    jou_created 'date'
                    , rt.rattyp_name 'name'
                    , jou_rating_type_id 'rating_type'
                    , CASE
                        WHEN jou_rating_type_id = 3 then 'red'
                        WHEN jou_rating_type_id = 6 then 'purple'
                        WHEN jou_rating_type_id = 7 then 'blue'
                        WHEN jou_rating_type_id = 8 then 'yellow'
                        WHEN jou_rating_type_id = 4 then 'orange'
                        END 'color'
                FROM
                    journals
                LEFT JOIN rating_types rt ON journals.jou_rating_type_id = rt.rattyp_id
                WHERE jou_rating_type_id IN (3, 4, 6, 7, 8) AND jou_subject_id = '$request->subject'
                GROUP BY jou_created, jou_rating_type_id, rt.rattyp_name
            ");

            $dates_main_ratings_filtered_arr = [];
            foreach ($dates_main_ratings as $date) {
                $dates_main_ratings_filtered_arr[$date->rating_type]['dates'][] = $date->date;
                $dates_main_ratings_filtered_arr[$date->rating_type]['name'] = $date->name;
                $dates_main_ratings_filtered_arr[$date->rating_type]['color'] = $date->color;
            }
//dd($dates_main_ratings_filtered_arr);
            $journal_data = [];
            foreach ($schoolboys as $sch) {

                $journal_data[$sch->schoolboy_id]['name'] = $sch->name;
                $journal_data[$sch->schoolboy_id]['rating_control'] = $sch->rating_control;
                $journal_data[$sch->schoolboy_id]['rating_theme'] = $sch->rating_theme;
                $journal_data[$sch->schoolboy_id]['rating_first_semester'] = $sch->rating_first_semester;
                $journal_data[$sch->schoolboy_id]['rating_second_semester'] = $sch->rating_second_semester;
                $journal_data[$sch->schoolboy_id]['rating_year'] = $sch->rating_year;
                $journal_data[$sch->schoolboy_id]['photo'] = $sch->photo;
                $journal_data[$sch->schoolboy_id]['rating_control_record'] = $sch->rating_control_record;
                $journal_data[$sch->schoolboy_id]['rating_theme_record'] = $sch->rating_theme_record;
                $journal_data[$sch->schoolboy_id]['rating_first_semester_record'] = $sch->rating_first_semester_record;
                $journal_data[$sch->schoolboy_id]['rating_second_semester_record'] = $sch->rating_second_semester_record;
                $journal_data[$sch->schoolboy_id]['rating_year_record'] = $sch->rating_year_record;
                $journal_data[$sch->schoolboy_id]['rating_theme_description'] = $sch->rating_theme_description;
                $journal_data[$sch->schoolboy_id]['rating_first_semester_description'] = $sch->rating_first_semester_description;
                $journal_data[$sch->schoolboy_id]['rating_second_semester_description'] = $sch->rating_second_semester_description;
                if(empty($sch->rating_count)) {
                    $journal_data[$sch->schoolboy_id]['disable_theme'] = true;
                    $journal_data[$sch->schoolboy_id]['disable_first_semester'] = true;
                    $journal_data[$sch->schoolboy_id]['disable_second_semester'] = true;
                    $journal_data[$sch->schoolboy_id]['disable_year'] = true;
                } elseif(!empty($sch->rating_theme_count)) {
                    $journal_data[$sch->schoolboy_id]['disable_rating'] = true;
                }
//                foreach ($date_arr as $date) {
//                    if($date == $sch->date) {
//                        $journal_ratings[$sch->schoolboy_id][$date][$sch->record] = $sch->rating;
//                    } else {
//                        $journal_ratings[$sch->schoolboy_id][$date][$sch->record] = "";
//                    }
//                }
                foreach ($date_arr as $date) {
                    if($date == $sch->date) {
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
                        $journal_data[$sch->schoolboy_id]['rating'][$date][$sch->record]['rating'] = $sch->rating;
                        $journal_data[$sch->schoolboy_id]['rating'][$date][$sch->record]['status'] = $status;
                        $journal_data[$sch->schoolboy_id]['rating'][$date][$sch->record]['rating_type_id'] = $sch->rating_type_id;
                        $journal_data[$sch->schoolboy_id]['rating'][$date][$sch->record]['rating_type_name'] = $sch->rating_type_name;
                        $journal_data[$sch->schoolboy_id]['rating'][$date][$sch->record]['input_id'] = "rating_$sch->record";
                    } else {
                        $journal_data[$sch->schoolboy_id]['rating'][$date][$sch->record]['rating'] = "";
                    }
                }
            }
            $dates_header = [];
            $i = 0;
            foreach ($date_arr as $date) {
                $dates_header[$i]['date'] = $date;
                $month = substr($date, 5, 2);
                $day = substr($date, 9, 2);
                $d = date('d.m',strtotime($date));
                $dates_header[$i]['date_formatted'] = $d;
                $i++;
            }


            return response()->json(['body' => $journal_data, 'dates' => $dates_header, 'date_rating_type' => $dates_main_ratings_filtered_arr]);
        }

        public function setRating(Request $request) {
            $request->validate([
                'rating' => 'required'
                , 'rating_type' => 'required'
                , 'subject' => 'required'
                , 'theme' => 'required'
                , 'schoolboy' => 'required'
                , 'record'
                , 'class_number'
                , 'date'
            ]);
            $date = new \DateTime($request->date);
            $date = date_format($date,'Y-m-d');
            $journal = new Journal;

            if($request->rating <= 0 or $request->rating > 12) {
                return response()->json(['status' => false, 'message' => 'Некоректне значення'], 500);
            }



            if (empty($request->record)) {
                    try {
                        DB::insert("INSERT INTO journals SET
                                jou_schoolboy_id = '$request->schoolboy'
                                , jou_teacher_id = '$this->teach_id'
                                , jou_subject_id = '$request->subject'
                                , jou_rating = '$request->rating'
                                , jou_rating_type_id = '$request->rating_type'
                                , jou_school_id = '$this->school_id'
                                , jou_theme_id = '$request->theme'
                                , jou_class_number = '$request->class_number'
                                , jou_created = '$date'
                             ");
                        return response()->json(['status' => true, 'message' => 'inserted'], 200);
                    } catch (Exception $e) {
                        return response()->json(['status' => false, 'message' => 'no inserted'], 500);
                    }
            } else {
                try {
                    DB::update("UPDATE journals SET
                                jou_schoolboy_id = '$request->schoolboy'
                                , jou_teacher_id = '$this->teach_id'
                                , jou_subject_id = '$request->subject'
                                , jou_rating = '$request->rating'
                                , jou_rating_type_id = '$request->rating_type'
                                , jou_school_id = '$this->school_id'
                                , jou_theme_id = '$request->theme'
                                , jou_class_number = '$request->class_number'
                            WHERE jou_record = '$request->record'
                            LIMIT 1

                         ");
                    return response()->json(['status' => true, 'message' => 'updated'], 200);
                } catch (Exception $e) {
                    return response()->json(['status' => false, 'message' => 'not updated'], 500);
                }
            }
        }

        function getLessonTable(Request $request) {

            $request->validate([
                'date_post' => 'required'
                , 'subject' => 'required'
                , 'theme' => 'required'
                , 'class_name' => 'required'
            ]);
            $class = explode('-', $request->class_name); //[0] -> number, [1] -> letter
            $date = new \DateTime($request->date_post);
            $date = date_format($date,'Y-m-d');
            $date_year = substr($date, 0, 4);

            $year_second_semester = $date_year;
            $year_first_semester = $date_year;
            if($date >= $date_year.'-09-01' && $date <= $date_year.'-12-31') {
                $year_second_semester += 1;
            } elseif($date >= $date_year.'-01-01' && $date <= $date_year.'-07-30') {
                $year_first_semester -= 1;
            }

            $first_semester_date_from = $year_first_semester.'-09-01';
            $first_semester_date_to = $year_first_semester.'-12-31';
            $second_semester_date_from = $year_second_semester.'-01-01';
            $second_semester_date_to = $year_second_semester.'-07-01';

            $where = "
                    AND jou_class_number = '$class[0]'
                    AND jou_theme_id = '$request->theme'
                    AND jou_subject_id = '$request->subject'
            ";

            $where_semester = "
                    AND journals.jou_class_number = '$class[0]' AND journals.jou_subject_id = '$request->subject'
            ";

            $lesson_data = DB::select("
                SELECT
                    CONCAT(sch.schboy_lastname, ' ', sch.schboy_firstname) 'name'
                    , j.jou_rating 'rating'
                    , jou_subject_id 'subject_id'
                    , jou_created 'date'
                    , jou_theme_id 'theme_id'
                    , jou_rating_type_id 'rating_type_id'
                    , (SELECT rattyp_name FROM rating_types WHERE  rattyp_id = jou_rating_type_id) 'rating_type_name'
                    , (SELECT rattyp_group FROM rating_types WHERE  rattyp_id = jou_rating_type_id) 'rating_type_group'
                    , schboy_id 'schoolboy_id'
                    , schboy_photo 'photo'
                    , jou_record 'record'
                    , (SELECT COUNT(journals.jou_rating) FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 6 $where_semester ) AS 'rating_first_semester'
                    , (SELECT COUNT(journals.jou_rating) FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 7 $where_semester) AS 'rating_second_semester'
                    , (SELECT COUNT(journals.jou_rating) FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 8 $where_semester) AS 'rating_year'
                    ,(SELECT COUNT(journals.jou_rating) FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 3 $where) AS 'rating_theme'
                    ,(SELECT COUNT(journals.jou_rating) FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 4 $where) AS 'rating_control'
                FROM journals j
                RIGHT OUTER JOIN (SELECT * FROM schoolboys WHERE schboy_class_number = '$class[0]' AND schboy_class_letter = '$class[1]' AND schboy_school_id = '$this->school_id') sch on j.jou_schoolboy_id = sch.schboy_id
                AND j.jou_created LIKE '%$date%'
                $where
                ORDER BY  sch.schboy_lastname, jou_rating_type_id
            ");

            $rating_types = RatingType::all()->sortBy('rattyp_id');

            $schoolboy_info = [];
            $rating_types_new = [];
            $rating_types_header = [];
//            $rating_types_hidden = [];
            foreach ($lesson_data as $data) {

                $schoolboy_info[$data->schoolboy_id]['name'] = $data->name;
                $schoolboy_info[$data->schoolboy_id]['photo'] = $data->photo;
                if(!empty($data->rating_first_semester)) {
                    $rating_types_hidden[] = 6;
                }
                if(!empty($data->rating_second_semester)) {
                    $rating_types_hidden[] = 7;
                }
                if(!empty($data->rating_year)) {
                    $rating_types_hidden[] = 8;
                }
                if(!empty($data->rating_theme)) {
                    $rating_types_hidden[] = 3;
                }
                if(!empty($data->rating_control)) {
                    $rating_types_hidden[] = 4;
                }
                foreach ($rating_types as $type) {
                    if(empty($schoolboy_info[$data->schoolboy_id]['rating'][$type->rattyp_id]['rating'])) {
                        if ($type->rattyp_id == $data->rating_type_id) {
                            if ($data->rating >= 10) {
                                $status = "success-color";
                            } elseif ($data->rating < 10 && $data->rating >= 7) {
                                $status = "warning-color";
                            } else {
                                $status = "danger-color";
                            }
                            $schoolboy_info[$data->schoolboy_id]['rating'][$data->rating_type_id]['rating'] = $data->rating;
                            $schoolboy_info[$data->schoolboy_id]['rating'][$data->rating_type_id]['record'] = $data->record;
                            $schoolboy_info[$data->schoolboy_id]['rating'][$data->rating_type_id]['status'] = $status;
                            $rating_types_header[$type->rattyp_id]['name'] = $type->rattyp_name;
                            $rating_types_new[$type->rattyp_id]['name'] = $type->rattyp_name;
                            $rating_types_new[$type->rattyp_id]['status'] = true;
                            $rating_types_hidden[] = $type->rattyp_id;

                        } else {
                            if ($type->rattyp_group == 1) {
                                $schoolboy_info[$data->schoolboy_id]['rating'][$type->rattyp_id]['rating'] = '';
                                $schoolboy_info[$data->schoolboy_id]['rating'][$type->rattyp_id]['record'] = '';
                                $schoolboy_info[$data->schoolboy_id]['rating'][$type->rattyp_id]['status'] = '';
                                $rating_types_header[$type->rattyp_id]['name'] = $type->rattyp_name;
                                $rating_types_new[$type->rattyp_id]['name'] = $type->rattyp_name;
                                $rating_types_new[$type->rattyp_id]['status'] = true;
                                $rating_types_hidden[] = $type->rattyp_id;
                            } else {
                                $schoolboy_info[$data->schoolboy_id]['rating'][$type->rattyp_id]['rating'] = '';
                                $schoolboy_info[$data->schoolboy_id]['rating'][$type->rattyp_id]['record'] = '';
                                $schoolboy_info[$data->schoolboy_id]['rating'][$type->rattyp_id]['status'] = '';
                                $rating_types_header[$type->rattyp_id]['name'] = $type->rattyp_name;
                                $rating_types_new[$type->rattyp_id]['name'] = $type->rattyp_name;
                                $rating_types_new[$type->rattyp_id]['status'] = false;
                            }
                        }
                    }
                }
            }

            $rating_types_hidden = array_unique($rating_types_hidden);
            $add_rating_type_arr = [];
            $options_rating_type_arr = [];
            $i = 0;
            foreach ($rating_types_new as $key => $data) {
                $add_rating_type_arr[$i]['id'] = $key;
                $add_rating_type_arr[$i]['name'] = $data['name'];
                $add_rating_type_arr[$i]['status'] = $data['status'];
                $i++;
            }
            $i = 0;

            foreach ($rating_types_new as $key => $data) {
                if(!in_array($key, $rating_types_hidden)) {
                    $options_rating_type_arr[$i]['id'] = $key;
                    $options_rating_type_arr[$i]['name'] = $data['name'];
                    $options_rating_type_arr[$i]['status'] = $data['status'];
                    $i++;
                }
            }
            return response()->json(['schoolboy_info' => $schoolboy_info, 'header' => $rating_types_header, 'options' => $options_rating_type_arr, 'add_rating_type' => $add_rating_type_arr, 'status' => true]);
        }

//        function getControlTable(Request $request) {
//
//            $request->validate([
//                'subject' => 'required'
//                , 'theme' => 'required'
//                , 'class_name' => 'required'
//            ]);
//            $class = explode('-', $request->class_name); //[0] -> number, [1] -> letter
//            $date = date('Y-m-d');
//            $date_year = substr($date, 0, 4);
//
//            $year_second_semester = $date_year;
//            $year_first_semester = $date_year;
//            if($date >= $date_year.'-09-01' && $date <= $date_year.'-12-31') {
//                $year_second_semester += 1;
//            } elseif($date >= $date_year.'-01-01' && $date <= $date_year.'-07-30') {
//                $year_first_semester -= 1;
//            }
//
//            $first_semester_date_from = $year_first_semester.'-09-01';
//            $first_semester_date_to = $year_first_semester.'-12-31';
//            $second_semester_date_from = $year_second_semester.'-01-01';
//            $second_semester_date_to = $year_second_semester.'-07-01';
//
//            $where = "
//                    AND jou_class_number = '$class[0]'
//                    AND jou_theme_id = '$request->theme'
//                    AND jou_subject_id = '$request->subject'
//            ";
//
//            $where_semester = "
//                    AND journals.jou_class_number = '$class[0]' AND journals.jou_subject_id = '$request->subject'
//            ";
//
//            $control_data = DB::select("
//                SELECT
//                    CONCAT(sch.schboy_lastname, ' ', sch.schboy_firstname) 'name'
//                    , j.jou_rating 'rating'
//                    , jou_subject_id 'subject_id'
//                    , jou_created 'date'
//                    , jou_theme_id 'theme_id'
//                    , jou_rating_type_id 'rating_type_id'
//                    , (SELECT rattyp_name FROM rating_types WHERE  rattyp_id = jou_rating_type_id) 'rating_type_name'
//                    , schboy_id 'schoolboy_id'
//                    , schboy_photo 'photo'
//                    , IFNULL((SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 6 ), (SELECT ROUND(AVG(journals.jou_rating), 1)
//                                FROM journals
//                                WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = '3' $where_semester
//                                AND journals.jou_created BETWEEN '$first_semester_date_from' AND '$first_semester_date_to'
//                            )) AS 'rating_first_semester'
//                    , IFNULL((SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 7 ), (SELECT ROUND(AVG(journals.jou_rating), 1)
//                                FROM journals
//                                WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = '3' $where_semester
//                                AND journals.jou_created BETWEEN '$second_semester_date_from' AND '$second_semester_date_to'
//                            )) AS 'rating_second_semester'
//                    , (SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 8 $where_semester) AS 'rating_year'
//                    , (SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 4 $where) AS 'rating_control'
//                    , IFNULL((SELECT journals.jou_rating FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = 3 $where),  (SELECT IFNULL( ROUND(((AVG(journals.jou_rating)
//                                        + (SELECT journals.jou_rating FROM journals WHERE journals.jou_rating_type_id = '4' AND journals.jou_schoolboy_id = sch.schboy_id $where)) / 2),1)
//                                , ROUND(AVG(journals.jou_rating), 1))
//                                    FROM journals
//                                    LEFT JOIN rating_types ON journals.jou_rating_type_id = rating_types.rattyp_id
//                                    WHERE journals.jou_schoolboy_id = sch.schboy_id AND rating_types.rattyp_group = 1
//                                    $where
//                     )) AS 'rating_theme'
//                    , (SELECT jou_record FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where AND journals.jou_rating_type_id = 3) 'rating_theme_record'
//                    , (SELECT jou_record FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where_semester AND journals.jou_rating_type_id = 6) 'rating_first_semester_record'
//                    , (SELECT jou_record FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where_semester AND journals.jou_rating_type_id = 7) 'rating_second_semester_record'
//                    , (SELECT jou_record FROM journals WHERE journals.jou_schoolboy_id = sch.schboy_id $where_semester AND journals.jou_rating_type_id = 8) 'rating_year_record'
//                FROM journals j
//                RIGHT OUTER JOIN (SELECT * FROM schoolboys WHERE schboy_class_number = '$class[0]' AND schboy_class_letter = '$class[1]' AND schboy_school_id = '$this->school_id') sch on j.jou_schoolboy_id = sch.schboy_id
//                AND j.jou_created LIKE '%$date%'
//                ORDER BY  sch.schboy_lastname, jou_rating_type_id
//            ");
//
//
//            $rating_types = RatingType::all()->sortBy('rattyp_id');
//
//            $schoolboy_info = [];
//            $rating_types_new = [];
//            $rating_types_header = [];
//            $rating_types_hidden = [];
////            foreach ($lesson_data as $data) {
////
////                $schoolboy_info[$data->schoolboy_id]['name'] = $data->name;
////                $schoolboy_info[$data->schoolboy_id]['photo'] = $data->photo;
////                if(!empty($data->rating_first_semester)) {
////                    $rating_types_hidden[] = 6;
////                }
////                if(!empty($data->rating_second_semester)) {
////                    $rating_types_hidden[] = 7;
////                }
////                if(!empty($data->rating_year)) {
////                    $rating_types_hidden[] = 8;
////                }
////                if(!empty($data->rating_theme)) {
////                    $rating_types_hidden[] = 3;
////                }
////                if(!empty($data->rating_control)) {
////                    $rating_types_hidden[] = 4;
////                }
////            }
//
//            $rating_types_hidden = array_unique($rating_types_hidden);
//            $add_rating_type_arr = [];
//            $options_rating_type_arr = [];
//            $i = 0;
//            foreach ($rating_types_new as $key => $data) {
//                $add_rating_type_arr[$i]['id'] = $key;
//                $add_rating_type_arr[$i]['name'] = $data['name'];
//                $add_rating_type_arr[$i]['status'] = $data['status'];
//                $i++;
//            }
//            $i = 0;
//
//            foreach ($rating_types_new as $key => $data) {
//                if(!in_array($key, $rating_types_hidden)) {
//                    $options_rating_type_arr[$i]['id'] = $key;
//                    $options_rating_type_arr[$i]['name'] = $data['name'];
//                    $options_rating_type_arr[$i]['status'] = $data['status'];
//                    $i++;
//                }
//            }
//            return response()->json(['schoolboy_info' => $schoolboy_info, 'header' => $rating_types_header, 'options' => $options_rating_type_arr, 'add_rating_type' => $add_rating_type_arr, 'status' => true]);
//        }
	}


