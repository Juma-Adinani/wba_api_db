<?php
include_once 'config/connect.php';
date_default_timezone_set('Africa/Nairobi');
$response = [];

// echo $_SERVER['REQUEST_METHOD'];
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(!empty($_POST)){
    
        $reg_no = mysqli_real_escape_string($con, $_POST['reg_no']);
        $timetable_id = mysqli_real_escape_string($con, $_POST['timetable_id']);
        $imeis = mysqli_real_escape_string($con, $_POST['imeis']);

        $current_year = date('Y');
        $current_date = date('Y-m-d');
        $current_time = date('H:i');
        $current_day = date('l');

        // STEP 1: Check if the user is already present in the session
                                        
        $present_check_sql = "SELECT * FROM attendance 
            WHERE date = '" . $current_date . "' 
            AND imei = '" . $imeis . "'
            AND timetable_id = '" . $timetable_id . "' 
            AND student_id = ( SELECT id FROM users WHERE reg_no = '" . $reg_no . "')";
        
        $present_check_res = mysqli_query($con, $present_check_sql);

        if(mysqli_error($con)){
            $response['status'] = 'Error';
            $response['message'] = 'Sorry!, An error has occured.' . mysqli_error($con);
        }else{

            if(mysqli_num_rows($present_check_res) > 0){
                $response['status'] = 'Ok';
                $response['message'] = 'You are already present for this session!';
            }else{

                $present_sql = "INSERT INTO attendance 
                    (timetable_id, student_id, imei, status, date) 
                    VALUES ('".$timetable_id."', (SELECT id FROM users WHERE reg_no = '".$reg_no."'), '".$imeis."', 'PRESENT', curdate())";
                
                $present_res = mysqli_query($con, $present_sql);

                if(mysqli_error($con)){
                    $response['status'] = 'Error';
                    $response['message'] = 'Sorry!, An error has occured.' . mysqli_error($con);
                }else{
                    $response['status'] = 'Ok';
                    $response['message'] = 'You are PRESENT for this session.';
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