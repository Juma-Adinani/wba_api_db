<?php

/**
 * VENUE API
 * This is an API to check a provided venue if it exists
 * If the venue exists, the API returns the current timetable for the provided venue
 * 
 */
include_once 'config/connect.php';

$response = [];

// echo $_SERVER['REQUEST_METHOD'];
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(!empty($_POST)){
    
        $reg_no = mysqli_real_escape_string($con, $_POST['reg_no']);
        $venue = mysqli_real_escape_string($con, $_POST['venue']);
        $imeis = mysqli_real_escape_string($con, $_POST['imeis']);

        $current_year = date('Y');
        $current_time = date('H:i');
        $current_day = date('l');

        // STEP 1: Check if the venue exists
        $venue_sql = "SELECT * FROM venues WHERE name = '" . $venue . "'";
        $venue_res = mysqli_query($con, $venue_sql);

        if(mysqli_error($con)){
            $response['status'] = 'Error';
            $response['message'] = 'Sorry!, An error has occured.' . mysqli_error($con);
        }else{

            if(mysqli_num_rows($venue_res) == 0){
                // This venue does not exist
                $response['status'] = 'Error';
                $response['message'] = 'This venue is not registered.';
            }else{

                // This venue exists

                // STEP 2: Fetch the timetable for the venue
                $venue_tt_sql = "SELECT timetable.id, subject_id, start_time, end_time FROM timetable, venues 
                    WHERE venues.name = '" . $venue . "' 
                    AND day_of_week = '" . $current_day . "' 
                    AND (start_time <= '" . $current_time . "' AND end_time >= '" . $current_time . "')" ;

                $venue_tt_res = mysqli_query($con, $venue_tt_sql);

                if(mysqli_error($con)){
                    $response['status'] = 'Error';
                    $response['message'] = 'Sorry!, An error has occured.' . mysqli_error($con);
                }else{
                    $num_rows = mysqli_num_rows($venue_tt_res);

                    // If num_rows == 1, then a session exists at this venue
                    if($num_rows == 1){

                        $venue_tt_row = mysqli_fetch_assoc($venue_tt_res);
                        $timetable_id = $venue_tt_row['id'];
                        $subject_id = $venue_tt_row['subject_id'];
                        $start_time = $venue_tt_row['start_time'];
                        $end_time = $venue_tt_row['end_time'];
                        $subject_name = "";
                        $programmes_list = [];
                        $student_programe = "";
						$is_present = false;

                        $prog_subj_sql = "SELECT code FROM subjects WHERE id = $subject_id";
                        $prog_subj_res = mysqli_query($con, $prog_subj_sql);
                        if(!mysqli_error($con)){
                            $prog_subj_row = mysqli_fetch_assoc($prog_subj_res);
                            $subject_name = $prog_subj_row['code'];
                        }

                        $prog_list_sql = "SELECT code FROM programmes, programmes_subjects 
                            WHERE programmes.id = programmes_subjects.prog_id
                            AND programmes_subjects.subject_id = $subject_id";
                        $prog_list_res = mysqli_query($con, $prog_list_sql);

                        if(!mysqli_error($con)){
                            while($row = mysqli_fetch_assoc($prog_list_res)){
                                array_push($programmes_list, $row['code']);
                            }
                        }

                        $student_prog_sql = "SELECT programmes.code FROM students, programmes_subjects, users, programmes 
                            WHERE users.id = students.user_id
                            AND students.prog_id = programmes.id
                            AND programmes.id = programmes_subjects.prog_id 
                            AND students.year_of_study = programmes_subjects.year_of_study
                            AND students.current_year = '" . $current_year . "'
                            AND reg_no = '" . $reg_no . "'";

                        $student_prog_res = mysqli_query($con, $student_prog_sql);

                        if(!mysqli_error($con)){
                            if(mysqli_num_rows($student_prog_res) > 0){
                                $student_prog_row = mysqli_fetch_assoc($student_prog_res);
                                $student_programe = $student_prog_row['code'];
                            }
                        }
						
						// Check if the student is present..
						if(in_array($student_programe, $programmes_list)){
							$is_present_sql = "SELECT * FROM attendance WHERE timetable_id = '" . $timetable_id . "'
								AND imei = '" . $imeis . "'
								AND student_id = (SELECT id FROM users WHERE reg_no = '" . $reg_no . "')";
							$is_present_res = mysqli_query($con, $is_present_sql);
							
							if(!mysqli_error($con)){
								if(mysqli_num_rows($is_present_res) == 1){
									$is_present = true;
								}else{
									$is_present = false;
								}
							}
						}

                        $response['status'] = 'Ok';
                        $response['message'] = 'A session exists at this venue!';
                        $response['data'] = [
                            'timetable_id'=>$timetable_id,
                            'subject'=>$subject_name, 
                            'start'=>$start_time, 
                            'end'=>$end_time, 
                            'venue'=>$venue,
                            'programmes'=>$programmes_list,
                            'is_belong'=> in_array($student_programe, $programmes_list),
							'is_present'=>$is_present,
                        ];
                    }else { // Else, no session exists at this venue at this time..

                        $response['status'] = 'Error';
                        $response['message'] = 'No sessions currently exists at this venue!';

                    }

                }

            }


        }

    }else{
        
        $response['status'] = 'Error';
        $response['message'] = 'Your request was empty!';

    }

}else{
    
    $response['status'] = 'Error';
    $response['message'] = 'Incorrect request method!';

}

echo json_encode($response);

?>