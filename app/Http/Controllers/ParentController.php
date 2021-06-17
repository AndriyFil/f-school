<?php


	namespace App\Http\Controllers;


    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Http\Request;
    class ParentController extends RootController
	{
        public $school_id = '26';
        public $teach_id = '51';
        public $role = 'teacher';
        public $parent_id = '65';

        public function getFilters(Request $request) {
//            $base_obj = new BaseController();
//            $base_obj->setClassNumber($request->class);
//            $subjects_class = $base_obj->getSubjectByClass();

            $class = $request->class_number;

            $types = DB::select("
                SELECT
                    rating_types.rattyp_name 'name'
                    , rating_types.rattyp_id 'id'
                FROM
                    rating_types
                WHERE rattyp_id NOT IN (6, 7, 8)
            ");
            return response()->json(['types' => $types]);
        }

        public function getSchoolboys(Request $request) {

            if(empty($this->school_id)) {
                return response()->json(['message' => 'Системна помилка!']);
            }

            $class = explode('-', $request->class); //[0] -> number, [1] -> letter
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

            $join = "";
            //TODO: change hardcoded type_id, school_id. Change photo query -> (SELECT photo FROM users WHERE users.id = sch.schboy_user_id)

            $where_type = "";
            if(gettype($request->type_rating) == 'string') {
                $rating_type = $request->type_rating;
            } else {
                $rating_type = $request->type_rating['id'];
            }

            if($rating_type != '-1') {
                $where_type = " AND j.jou_rating_type_id = '$rating_type' ";
            }

            $where_semester = "
                AND journals.jou_class_number = '$class[0]' AND journals.jou_subject_id = sub.sub_id
            ";
            $where = "
                    AND jou_schoolboy_id = '$request->schoolboy_id'
                    AND jou_subject_id = sub.sub_id
            ";

            $schoolboys = DB::select("
                SELECT
                    j.jou_rating 'rating'
                    , sub.sub_id 'subject_id'
                    , jou_created 'date'
                    , jou_theme_id 'theme_id'
                    , sub.sub_name 'subject_name'
                    , jou_rating_type_id 'rating_type_id'
                    , (SELECT rattyp_name FROM rating_types WHERE  rattyp_id = jou_rating_type_id) 'rating_type_name'
                    , jou_record 'record'
                    , IFNULL((SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = '$request->schoolboy_id' $where_semester AND journals.jou_rating_type_id = 6 ), (SELECT ROUND(AVG(journals.jou_rating), 1)
                                FROM journals
                                WHERE journals.jou_schoolboy_id = '$request->schoolboy_id' AND journals.jou_rating_type_id = '3' $where_semester
                                AND journals.jou_created BETWEEN '$first_semester_date_from' AND '$first_semester_date_to'
                            )) AS 'rating_first_semester'
                    , IFNULL((SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = '$request->schoolboy_id' $where_semester AND journals.jou_rating_type_id = 7 ), (SELECT ROUND(AVG(journals.jou_rating), 1)
                                FROM journals
                                WHERE journals.jou_schoolboy_id = '$request->schoolboy_id' AND journals.jou_rating_type_id = '3' $where_semester
                                AND journals.jou_created BETWEEN '$second_semester_date_from' AND '$second_semester_date_to'
                            )) AS 'rating_second_semester'
                    , (SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = '$request->schoolboy_id' AND journals.jou_rating_type_id = 8 $where_semester) AS 'rating_year'
                    , (SELECT journals.jou_rating FROM journals WHERE  journals.jou_schoolboy_id = '$request->schoolboy_id' AND journals.jou_rating_type_id = 4 AND journals.jou_subject_id = sub.sub_id $where) AS 'rating_control'
                    , (SELECT GROUP_CONCAT(CONCAT(rt.rattyp_name, ': ', journals.jou_rating) SEPARATOR '<br>') FROM journals
                    LEFT JOIN rating_types rt ON journals.jou_rating_type_id = rt.rattyp_id
                    WHERE journals.jou_schoolboy_id = '$request->schoolboy_id' AND (rt.rattyp_group = '1' OR rattyp_id = '4') $where
                    GROUP BY journals.jou_schoolboy_id
                    ) 'rating_theme_description'
                    , (SELECT GROUP_CONCAT(CONCAT(c.cur_lesson_theme, ': ', journals.jou_rating), '<br>') FROM journals
                    LEFT JOIN curricula c ON journals.jou_theme_id = c.cur_id
                    WHERE journals.jou_schoolboy_id = '$request->schoolboy_id' AND journals.jou_rating_type_id = '3' $where_semester
                    AND journals.jou_created BETWEEN '$first_semester_date_from' AND '$first_semester_date_to'
                    GROUP BY journals.jou_schoolboy_id
                    ) 'rating_first_semester_description'
                    , (SELECT GROUP_CONCAT(CONCAT(c.cur_lesson_theme, ': ', journals.jou_rating), '<br>') FROM journals
                    LEFT JOIN curricula c ON journals.jou_theme_id = c.cur_id
                    WHERE journals.jou_schoolboy_id = '$request->schoolboy_id' AND journals.jou_rating_type_id = '3' $where_semester
                    AND journals.jou_created BETWEEN '$second_semester_date_from' AND '$second_semester_date_to'
                    GROUP BY journals.jou_schoolboy_id
                    ) 'rating_second_semester_description'
                    FROM journals j
                    RIGHT OUTER JOIN (SELECT * FROM subjects LEFT JOIN subjects_classes ON sub_id = subjects_classes.subject_id
                                WHERE subjects_classes.class_number = '$class[0]' AND (subjects.school_id = '$this->school_id' OR subjects.school_id IS NULL)
                    ) sub ON j.jou_subject_id = sub.sub_id
                    AND (j.jou_created >= '$date_start' AND j.jou_created <= '$date_end')
                    $where
                    $where_type
                ORDER BY jou_created
            ");

            $date_arr = DB::select("
                SELECT
                    jou_created 'date'
                    FROM journals j
                RIGHT OUTER JOIN (SELECT * FROM subjects LEFT JOIN subjects_classes ON sub_id = subjects_classes.subject_id
                                WHERE subjects_classes.class_number = '$class[0]' AND (subjects.school_id = '$this->school_id' OR subjects.school_id IS NULL)
                    ) sub ON j.jou_subject_id = sub.sub_id
                    AND (j.jou_created >= '$date_start' AND j.jou_created <= '$date_end')
                    $where
                    $where_type
                GROUP BY jou_created
                ORDER BY jou_created
            ");
            $journal_data = [];

            foreach ($schoolboys as $sch) {

                $journal_data[$sch->subject_id]['name'] = $sch->subject_name;
                $journal_data[$sch->subject_id]['rating_first_semester'] = $sch->rating_first_semester;
                $journal_data[$sch->subject_id]['rating_second_semester'] = $sch->rating_second_semester;
                $journal_data[$sch->subject_id]['rating_year'] = $sch->rating_year;
                $journal_data[$sch->subject_id]['rating_theme_description'] = $sch->rating_theme_description;
                $journal_data[$sch->subject_id]['rating_first_semester_description'] = $sch->rating_first_semester_description;
                $journal_data[$sch->subject_id]['rating_second_semester_description'] = $sch->rating_second_semester_description;

                if(!empty($date_arr)) {
                    foreach ($date_arr as $date) {
                        if(!empty($date->date)) {
                            if ($date->date == $sch->date) {
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
                                $journal_data[$sch->subject_id]['rating'][$date->date][$sch->record]['rating'] = $sch->rating;
                                $journal_data[$sch->subject_id]['rating'][$date->date][$sch->record]['status'] = $status;
                                $journal_data[$sch->subject_id]['rating'][$date->date][$sch->record]['rating_type_id'] = $sch->rating_type_id;
                                $journal_data[$sch->subject_id]['rating'][$date->date][$sch->record]['rating_type_name'] = $sch->rating_type_name;
                                $journal_data[$sch->subject_id]['rating'][$date->date][$sch->record]['input_id'] = "rating_$sch->record";
                            } else {
                                $journal_data[$sch->subject_id]['rating'][$date->date][$sch->record]['rating'] = "";
                            }
                        }
                    }
                }
            }
            $dates_header = [];

            $i = 0;

            foreach ($date_arr as $date) {
                if(!empty($date->date)) {
                    $dates_header[$i]['date'] = $date->date;
                    $d = date('d.m',strtotime($date->date));
                    $dates_header[$i]['date_formatted'] = $d;
                    $i++;
                }
            }

            return response()->json(['body' => $journal_data, 'dates' => $dates_header]);
        }
	}
