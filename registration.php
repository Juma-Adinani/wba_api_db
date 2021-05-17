<?php
/**
 * Registration API
 * This is an API for confirmation of user's registration
 * It verifies user's registration number and passwords and returns them if they are valid.
 * 
 */

include_once 'config/connect.php';

$response = [];

// echo $_SERVER['REQUEST_METHOD'];
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(!empty($_POST)){
    
        $reg_no = mysqli_real_escape_string($con, $_POST['reg_no']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        
        $current_year = date('Y');
        $current_date = date('Y-m-d');

        // STEP 1: Check if the user's registration number exists
        $user_reg_sql = "SELECT id FROM users WHERE reg_no = '" . $reg_no . "'";
        $user_reg_res = mysqli_query($con, $user_reg_sql);

        if(!mysqli_error($con)){
            if(mysqli_num_rows($user_reg_res) == 1){

                // STEP 2: If the registration number exists, check if the password is correct
                $user_pass_sql = "SELECT users.id, firstname, lastname, roles.name FROM users, roles 
                    WHERE users.role_id = roles.id
                    AND reg_no = '".$reg_no."' AND password = sha('".$password."')";
                $user_pass_res = mysqli_query($con, $user_pass_sql);

                if(!mysqli_error($con)){
                    if(mysqli_num_rows($user_pass_res) == 1){

                        // STEP 3: The password & registration exist
                        // Get user's details
                        $user = mysqli_fetch_assoc($user_pass_res);
                        $user_id = $user['id'];
                        $name = $user['firstname'] . ' ' . $user['lastname'];
                        $role = $user['name'];
                        $year_of_study = '';
                        $semester = '';
                        $programme = '';

                        // STEP 4: Get the users programme, year_of_study and programme if he/she is a student
                        if($role == 'student'){
                            $user_detals_sql = "SELECT year_of_study, programmes.code as programme, semesters.name as semester FROM students, programmes, semesters 
                                WHERE students.prog_id = programmes.id
                                AND students.semester_id = semesters.id
                                AND students.user_id = '".$user_id."'
                                AND students.current_year = '".$current_year."'";
                            $user_details_res = mysqli_query($con, $user_detals_sql);

                            if(!mysqli_error($con)){

                                if(mysqli_num_rows($user_details_res) == 1){
                                
                                    $details = mysqli_fetch_assoc($user_details_res);

                                    $year_of_study = $details['year_of_study'];
                                    $semester = $details['semester'];
                                    $programme = $details['programme'];

                                    // STEP 5: Registration validation completed, return response
                                    $response['status'] = 'Ok';
                                    $response['message'] = '';
                                    $response['data'] = ['reg_no'=>$reg_no, 'name'=>$name, 'role'=>$role, 'year_of_study'=>$year_of_study, 'semester'=>$semester, 'programme'=>$programme];
                                }else{
                                    $response['status'] = 'Error';
                                    $response['message'] = 'Registration incomplete. Please contact administration';
                                    $response['data'] = '';
                                }

                            }else{
                                $response['status'] = 'Error';
                                $response['message'] = 'Sorry!, An error has occured.'. mysqli_error($con);
                                $response['data'] = '';
                            }
                        }else if($role == 'teacher'){

                            // STEP 5: Registration validation completed, return response
                            $response['status'] = 'Ok';
                            $response['message'] = '';
                            $response['data'] = ['reg_no'=>$reg_no, 'name'=>$name, 'role'=>$role];

                        }

                    }else{
                        $response['status'] = 'Error';
                        $response['message'] = 'Incorrect password!';
                        $response['data'] = '';
                    }
                }else{
                    $response['status'] = 'Error';
                    $response['message'] = 'Sorry!, An error has occured.' . mysqli_error($con);
                    $response['data'] = '';
                }
            }else{
                $response['status'] = 'Error';
                $response['message'] = 'Registration number not found!';
                $response['data'] = '';
            }
        }else{
            $response['status'] = 'Error';
            $response['message'] = 'Sorry!, An error has occured.'. mysqli_error($con);
            $response['data'] = '';
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