<?php

require_once "pdo.php";
// session_start();
$pdo = new PDO('mysql:host=localhost;port=3306;dbname=interviewall','priyal','badminton');
// See the "errors" folder for details...
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


function flashMessages(){
  if( isset($_SESSION['error']) ){
    echo('<p style="color: red; background-color: white; font-size: 20px;">'.htmlentities($_SESSION['error'])."</p>\n" );
    unset($_SESSION['error']);
  }
  if( isset($_SESSION['success']) ){
    echo('<p style="color: green; background-color: white; font-size: 20px;">'.htmlentities($_SESSION['success'])."</p>\n" );
    unset($_SESSION['success']);
  }
}

//a bit of utility code
function validateProfile(){

  if(strlen($_POST['headline']) < 1
  || strlen($_POST['summary']) < 1){
    return "All values are required";
  }
//   if( strpos($_POST['email'],'@') === false  ){
//     return 'Email address must contain @';
//   }
  return true;
}

//LOOK THOUGH THE POST DATA AND RETURN TRUE OR ERROR msg
//CONSTRAINT#2 - No of participants in a meeting is less than 2
function validateCount(){
    $count = 0;
    for($i=1 ; $i<=10 ;$i++){
      if (  isset($_POST['name'.$i]) ){
          $count = $count + 1;
      }else{
          continue;
      }
    }

    if($count < 2){
        return "Minimum number of participants in the meet must be 2. Please add more participants.";
    }
    
    return true;
  }

//CONSTRAINT#1 - Any of the participants is not available during the scheduled time (i.e, has another interview scheduled)
function validatePer(){
    for($i=1; $i<=10; $i++) {
      if( ! isset($_POST['name'.$i]) ) continue;
      $name = $_POST['name'.$i];

      $pdo = new PDO('mysql:host=localhost;port=3306;dbname=interviewall','priyal','badminton');
      // See the "errors" folder for details...
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $stmt = $pdo->query("SELECT interview.profile_id, interview.start, interview.end, interview.date, 
                    interview.user_id, meeting.profile_id, meeting.interview_id, interview.interview_id
                    FROM interview JOIN meeting ON meeting.profile_id = interview.profile_id");
                                    
      while ( $meeting = $stmt->fetch(PDO::FETCH_ASSOC) ) {

              echo("<h1>checking time clash</h1>");

              $st =  $_POST['start_time'];
              $se =  $_POST['end_time'];
              $id = $_POST['int_date'];
              
              if($meeting['date'] == $id and $meeting['start'] >= $st and $meeting['start'] < $se){
                  return ("1) This {$name} person has another interview scheduled, please select some other time.");
              }
              elseif($meeting['date'] == $id and $meeting['end'] > $st and $meeting['end'] <= $se){
                  return ("2) This {$name} person has another interview scheduled, please select some other time.");
              }
      }
                                        
    }
    return true;
  }

function loadEdu($pdo , $interview_id){
  $stmt = $pdo->prepare('SELECT name FROM Profile
    JOIN Interview
        ON Profile.profile_id = Interview.profile_id  
    WHERE interview_id = :prof');
    $stmt->execute(array(':prof' => $interview_id)) ;
    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $profiles;
}

function insertInterviews($pdo, $interview_id){
  $rank = 1;
  for($i=1; $i<=10; $i++){
    if ( !isset($_POST['name'.$i]) ) continue;

    $name = $_POST['name'.$i];

     //lookup the profile if it is there.
     $profile_id = false;
     $stmt = $pdo->prepare('SELECT profile_id FROM
           Profile WHERE name = :name');
     $stmt->execute(array(':name' => $name));
     $row = $stmt->fetch(PDO::FETCH_ASSOC);
     if ( $row !== false ) $profile_id = $row['profile_id'];

     //if there was no profile. insert it
     if ( $profile_id === false ) {
       $stmt = $pdo->prepare('INSERT INTO Profile
             (name) VALUES (:name)');
       $stmt->execute(array(':name' => $name));
       $profile_id = $pdo->lastInsertId();
     }

     $stmt = $pdo->prepare('INSERT INTO Interview
          (user_id , profile_id, rank,  headline, summary, start, end, date)
          VALUES ( :uid, :pid, :rank, :he, :su, :st, :en, :dt)');

          $stmt->execute(array(
          ':uid' => $_SESSION['user_id'],
          ':pid' => $profile_id, 
          ':rank' => $rank,
          ':he' => $_POST['headline'],
          ':su' => $_POST['summary'],
          ':st' => $_POST['start_time'],
          ':en' => $_POST['end_time'],
          ':dt' => $_POST['int_date'] )
          );

          //to give the id for which we just added the details:
          $interview_id = $pdo-> lastInsertId();


      $stmt = $pdo->prepare('INSERT INTO Meeting
      (profile_id, rank, interview_id)
      VALUES (:pid, :rank, :iid)');
      $stmt->execute(array(
          ':pid' => $profile_id,
          ':rank' => $rank,
          ':iid' => $interview_id)
      );

    $rank++;

  }
}

?>
