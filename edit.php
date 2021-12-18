<?php
//make the database connection and leave it in the variable $pdo
require_once 'pdo.php';
require_once 'util.php';

session_start();

// if the user is not logged in redirect back to index.php
//with an error
if ( ! isset($_SESSION['user_id']) ){
    die('ACCESS DENIED');
    return;
}

//if the user requested cancel go back to index.php
if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php
    header('Location: index.php');
    return;
}

//make sure the REQUEST parameter is present
if ( ! isset($_REQUEST['interview_id']) ) {
  $_SESSION['error'] = "Missing interview_id";
  header('Location: index.php');
  return;
}

//load up the profile in question
$stmt = $pdo->prepare('SELECT * FROM Interview
  WHERE interview_id = :iid AND user_id = :uid');
$stmt->execute(array(":iid" => $_REQUEST['interview_id'],
       ':uid' => $_SESSION['user_id'] ));                       
       //user id check to see if no one else can alter interview, just by providing interview idea

$interview = $stmt->fetch(PDO::FETCH_ASSOC);
if ($interview === false){
    $_SESSION['error'] = 'Could not load interview';
    header( 'Location: index.php' ) ;
    return;
}

//handle the incoming data
if ( isset($_POST['headline']) && isset($_POST['summary']) 
        && isset($_POST['int_date']) && isset($_POST['start_time']) && isset($_POST['end_time']) ) {

    //  Data validation
    $msg = validateProfile();
        if ( is_string($msg) ){
          $_SESSION['error'] = $msg;
          header('Location: edit.php?interview_id='.$_REQUEST["interview_id"]);
          return;
      }

    $msg3 = validateCount();
    if ( is_string($msg3) ){
      $_SESSION['error'] = $msg3;
      header('Location: edit.php?interview_id='.$_REQUEST["interview_id"]);
      return;
    }

    $msg2 = validatePer();
    if ( is_string($msg2) ){
      $_SESSION['error'] = $msg2;
      header('Location: edit.php?interview_id='.$_REQUEST["interview_id"]);
      return;
    }


    //begin the update the date
    $sql = 'UPDATE Interview SET headline = :hd, interview_id = :iid,
            summary = :sm, date = :dt, start = :st, end = :et
            WHERE interview_id = :iid';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
      ':iid' => $_REQUEST['interview_id'],
      ':hd' => $_POST['headline'],
      ':sm' => $_POST['summary'],
      ':dt' => $_POST['int_date'],
      ':st' => $_POST['start_time'],
      ':et' => $_POST['end_time'])
      );

    //clear out the old profile enteries
    $stmt = $pdo->prepare('DELETE FROM Interview
          WHERE interview_id= :iid');
    $stmt ->execute(array(':iid' => $_REQUEST['interview_id']));

    //insert the profile enteries
    insertInterviews($pdo, $_REQUEST['interview_id']);

    $_SESSION['success'] = 'Interview updated';
    header("Location: index.php");
    return;
}
// //load up the profiles

$persons = loadEdu($pdo, $_REQUEST['interview_id']);

?>
<!DOCTYPE html>
<html>
<head> <title>Interview Creation Portal</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container editc">
  <h1>Editing Interviews for <?php echo(htmlentities($_SESSION['name'])); ?> </h1>
  <?php flashMessages(); ?>
  <?php
    $hl = htmlentities($interview['headline']);
    $sm = htmlentities($interview['summary']);
    $dt = htmlentities($interview['date']);
    $st = htmlentities($interview['start']);
    $et = htmlentities($interview['end']);
  ?>

  <form method="post" action="edit.php">
    <input type="hidden" name="interview_id"
    value="<?= htmlentities($_REQUEST['interview_id']); ?>"/>
    
    <p>Headline:<br/>
      <input type="text" name="headline" class="he" size="80" value="<?= $hl ?>"/></p>
    <p>Summary:<br/>
      <textarea name="summary" rows="8" class="sm" cols="80" value="<?= $sm ?>"><?= $sm ?></textarea></p>

    <p>Date: <br/>
      <input type="date" class="dt" name="int_date" value="<?= $dt ?>"/>
    <p>Start Time: <br/>
      <input type="time" class="tm" name="start_time" value="<?= $st ?>" />
    <p>End Time: <br/>
      <input type="time" class="tm" name="end_time" value="<?= $et ?>" />
 
  <?php
    $countPer = 0;

    echo('<p>Persons: <input type="submit" id="addPer" value="+">'."\n");
    echo('<div id="person_fields">'."\n");
    if ( count($persons) >0 ){
      foreach ($persons as $person ) {
        $countPer++;
        echo('<div id="per'.$countPer.'">');
        echo('
          <p>Name: <input type="text" size="80" name="name'.$countPer.'" value="'.$person['name'].'" />
          <input type="button" value="-" class="minus" onclick="$(\'#per'.$countPer.'\').remove(); return false;"></p>
          ');
        echo("\n</div>\n");
      }
    }
    echo("</div></p>\n");
  ?>

    <p>
      <input type="submit" class="addbutton" value="Save">
      <input type="submit" class="cancelbutton" name="cancel" value="Cancel">
    </p>
  </form>


  <script>

    countPer = <?= $countPer ?>;

    //https://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
    $(document).ready(function(){
      // window.console &&
      console.log('Document ready called');

      $('#addPer').click(function(event){
          event.preventDefault();
          if( countPer >= 10 ){
            alert("Maximum of ten person enteries exceeded");
            return;
          }
          countPer++;
          window.console && console.log("adding persons "+countPer);

          //grab some html with hot spots and insert into DOM
          var source = $("#person-template").html();
          $('#person_fields').append(source.replace(/@COUNT@/g,countPer));

          //add the evem handler to the new ones
          $('.person').autocomplete({
            source: "person.php"
          });
        });

        $('.person').autocomplete({
          source: "person.php"
        });

    });

  </script>
  <!-- HTML with substitution hot spots -->
  <script id="edu-template" type="text">

      <div id="per@COUNT@">      
        <p>Name: <input type="text" size="80" name="name@COUNT@" class="person" value="" />
        <input type="button" class="minus" value="-" onclick="$('#per@COUNT@').remove(); return false;"><br>    
        </p>
      <div>

  </script>
    
 </div>
 </body>
 </html>
