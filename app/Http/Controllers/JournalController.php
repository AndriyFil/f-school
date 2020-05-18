<?php


	namespace App\Http\Controllers;
    use App\Journal;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Http\Request;
    use App\Administration;
    use App\User;
    use App\School;
    use Carbon\Carbon;
    use Validator;
    use Exception;
    use GuzzleHttp\Client;
    use Laravel\Passport\Client as OClient;
    use App\Http\Controllers\EmailController as Email;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Str;

    class JournalController
	{
	    public $profile = [];
	    public function __construct()
        {
            $this->profile = new AuthController;
            $this->profile->profile();
//            dd($this->profile->profile());
        }

        public function getSubjectsClasses($id, $school_id, $role) {
            $subjects = DB::select('
                SELECT
                    sub_id  \'id\'
                    , sub_name \'name\'
                FROM
                    subject_teachers subtea
                LEFT JOIN subjects sub ON  subtea.subtea_subject_id = sub.sub_id
                WHERE subtea_teacher_id = (SELECT tea_id FROM teachers WHERE tea_user_id = "'.$id.'")
            ');
            $teacher_classes = DB::select('
                SELECT
                    tea_class_from \'class_from\'
                    , tea_class_to \'class_to\'
                    , tea_class_to \'class_to\'
                FROM
                    teachers
                WHERE tea_user_id = "'.$id.'"
            ');

            $classes = DB::select('
                SELECT
                    CONCAT(schboy_class_number, \'-\',  schboy_class_letter) \'class\'
                FROM
                    schoolboys
                WHERE schboy_class_number >= "'.$teacher_classes[0]->class_from.'"
                and schboy_class_number <= "'.$teacher_classes[0]->class_to.'"
                and schboy_school_id <= "'.$school_id.'"
                GROUP BY class
            ');

            return response()->json(['subjects' => $subjects, 'classes' => $classes]);
        }

        public function getThemes($subject_id, $class_number, $school_id) {

            $themes = DB::select('
                SELECT
                    cur_id \'id\'
                    , cur_lesson_theme \'name\'
                FROM
                    curricula
                WHERE cur_school_id = "'.$school_id.'"
                    AND cur_class_number = "'.$class_number.'"
                    AND cur_subject_id = "'.$subject_id.'"
            ');

            return response()->json($themes);
        }

        public function getSchoolboys(Request $request) {

	        if(empty($request->class) || empty($request->subject) || empty($request->section) || empty($request->date)) {
	            return response()->json(['message' => 'Всі поля мають бути заповнені!']);
            }

            if(empty($request->school_id)) {
                return response()->json(['message' => 'Системна помилка!']);
            }

            $class = explode('-', $request->class); //[0] -> number, [1] -> letter
            //dd($request->date['start']);
            $date_start = $request->date['start'];
            $date_end = $request->date['end'];
            $date_arr[0] = $date_start;

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
                    AND jou_subject_id = $request->subject
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
                    , IFNULL((SELECT ROUND(AVG(journals.jou_rating), 1)
                                FROM journals
                                WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id = '3' AND journals.jou_class_number = '$class[0]' AND journals.jou_subject_id = $request->subject
                            ), '') AS 'rating_semester'
                    , (SELECT IFNULL( ROUND((AVG(journals.jou_rating)
                                        + (SELECT journals.jou_rating FROM journals WHERE journals.jou_rating_type_id = '4' AND journals.jou_schoolboy_id = sch.schboy_id $where) / 2),1)
                                , ROUND(AVG(journals.jou_rating), 1))
                                    FROM journals
                                    WHERE journals.jou_schoolboy_id = sch.schboy_id AND journals.jou_rating_type_id IN (1, 2)
                                    $where
                    ) AS 'rating_theme'
                FROM journals j
                RIGHT OUTER JOIN (SELECT * FROM schoolboys WHERE schboy_class_number = '$class[0]' AND schboy_class_letter = '$class[1]' AND schboy_school_id = '$request->school_id') sch on j.jou_schoolboy_id = sch.schboy_id
                    AND (j.jou_created >= '$date_start' AND j.jou_created <= '$date_end')
                    $where
                    AND jou_rating_type_id IN (1, 2)

                ORDER BY  sch.schboy_lastname, jou_created
            ");
            $journal_data = [];
            foreach ($schoolboys as $sch) {

                $journal_data[$sch->schoolboy_id]['name'] = $sch->name;
                $journal_data[$sch->schoolboy_id]['rating_theme'] = $sch->rating_theme;
                $journal_data[$sch->schoolboy_id]['rating_semester'] = $sch->rating_semester;
                $journal_data[$sch->schoolboy_id]['photo'] = $sch->photo;

                foreach ($date_arr as $date) {
                    if($date == $sch->date) {
                        $journal_ratings[$sch->schoolboy_id][$date][$sch->record] = $sch->rating;
                    } else {
                        $journal_ratings[$sch->schoolboy_id][$date][$sch->record] = "";
                    }
                }
                foreach ($date_arr as $date) {
                    if($date == $sch->date) {
                        if ($sch->rating >= 10) {
                            $status = "success-color";
                        } elseif ($sch->rating < 10 && $sch->rating >= 7) {
                            $status = "warning-color";
                        } else {
                            $status = "danger-color";
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

//            $table_body = "";
//            $table_headers = "
//                <tr>
//                        <td>Учень</td>
//            ";
//            foreach($date_arr as $date) {
//                $table_headers .= "
//                    <td data-date='{$date}' class='journal_date' data-app>$date</td>
//                ";
//            }
//            $table_headers .= "
//                <td>Оцінка за тему</td>
//                <td>Оцінка за семестр</td>
//            </tr>";
//            foreach ($journal_data as $sch_id => $info) {
//                $photo = $info['photo'];
//                if(empty($info['photo'])) {
//                    $photo = "/img/user/profile2.png";
//                }
//                $table_body .= " <tr>
//                                        <td class='schoolboy'>
//                                            <img src=\"{$photo}\" class='avatar'>
//                                            <div class='name'>{$info['name']}</div>
//                                        </td>
//
//                ";
//                foreach($date_arr as $date) {
//                    $table_body .= "<td class='text-center'>";
//                    foreach ($journal_data[$sch_id]["$date"] as $record => $rating) {
//                        if($rating) {
//                            if($rating >= '10') {
//                                $color = 'success-color';
//                            } elseif($rating <= '9' and $rating >= '7'){
//                                $color = 'warning-color';
//                            } else {
//                                $color = 'danger-color';
//                            }
//                            $table_body .= "<input type='text' class='input-rating $color' @change='editRating(\"{$record}\")' value='{$rating}'>";
//                        }
//                    }
//                    $table_body .= "</td>";
//                }
//                if($info['rating_theme']) {
//                    if($rating >= '10') {
//                        $color_theme = 'success-color';
//                    } elseif($rating <= '9' and $rating >= '7'){
//                        $color_theme = 'warning-color';
//                    } else {
//                        $color_theme = 'danger-color';
//                    }
//                }
//                $color_theme = "";
//                $color_semester = "";
//
//                if($info['rating_theme'] >= '10') {
//                    $color_theme = 'success-color';
//                } elseif($info['rating_theme'] <= '9' and $info['rating_theme'] >= '7'){
//                    $color_theme = 'warning-color';
//                } elseif($info['rating_theme'] >= 1) {
//                    $color_theme = 'danger-color';
//                } else {
//                    $color_theme = "";
//                }
//
//                if($info['rating_semester'] >= '10') {
//                    $color_semester = 'success-color';
//                } elseif($info['rating_semester'] <= '9' and $info['rating_semester'] >= '7'){
//                    $color_semester = 'warning-color';
//                } elseif($info['rating_semester'] >= 1) {
//                    $color_semester = 'danger-color';
//                } else {
//                    $color_semester = "";
//                }
//
//                $table_body .= "<td class='text-center'>
//                                        <input type='text' class='input-rating $color_theme' value=\"{$info['rating_theme']}\">
//                                </td>
//                                <td class='text-center'>
//                                        <input type='text' class='input-rating $color_semester' value=\"{$info['rating_semester']}\">
//                                </td>
//                            </tr>
//                ";
//
//            }
            return response()->json(['body' => $journal_data, 'dates' => $date_arr]);
        }

        public function setRating(Request $request) {
            $request->validate([
                'rating' => 'required'
                , 'rating_type' => 'required'
                , 'teacher' => 'required'
                , 'subject' => 'required'
                , 'theme' => 'required'
                , 'schoolboy' => 'required'
                , 'school_id' => 'required'
                , 'record'
                , 'class_number'
            ]);
//dd($request);
            $journal = new Journal;

            if (empty($request->record)) {
                try {
                    DB::insert("INSERT INTO journals SET
                            jou_schoolboy_id = '$request->schoolboy'
                            , jou_teacher_id = '$request->teacher'
                            , jou_subject_id = '$request->subject'
                            , jou_rating = '$request->rating'
                            , jou_rating_type_id = '$request->rating_type'
                            , jou_school_id = '$request->school_id'
                            , jou_theme_id = '$request->theme'
                            , jou_class_number = '$request->class_number'
                         ");
                    return response()->json(['status' => true], 200);
                } catch (Exception $e) {
                    return response()->json(['status' => false], 500);
                }
            } else {
                try {
                    DB::update("UPDATE journals SET
                            jou_schoolboy_id = '$request->schoolboy'
                            , jou_teacher_id = '$request->teacher'
                            , jou_subject_id = '$request->subject'
                            , jou_rating = '$request->rating'
                            , jou_rating_type_id = '$request->rating_type'
                            , jou_school_id = '$request->school_id'
                            , jou_theme_id = '$request->theme'
                            , jou_class_number = '$request->class_number'
                        WHERE jou_record = '$request->record'

                     ");
                    return response()->json(['status' => true], 200);
                } catch (Exception $e) {
                    return response()->json(['status' => false], 500);
                }
            }
        }
	}


