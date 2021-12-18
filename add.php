<?php 
  //make the database connection and leave it in the variable $pdo
  require_once "pdo.php";
  require_once "util.php";
  session_start();

  //if the user is not loggin in redirect back to index.php
  //with an error
  if ( ! isset($_SESSION['user_id']) ) {
      die('ACCESS DENIED');
      return;
  }

  //if the user requested cancel go back to index.php
  if ( isset($_POST['cancel'] ) ) {
      // Redirect the browser to index.php
      header("Location: index.php");
      return;
  }

  //handle the incoming data
  if ( isset($_POST['headline']) && isset($_POST['summary']) 
      && isset($_POST['int_date']) && isset($_POST['start_time']) && isset($_POST['end_time']) ) {

        $msg = validateProfile();
        if ( is_string($msg) ){
          $_SESSION['error'] = $msg;
          header("Location: add.php");
          return;
        }

        $msg3 = validateCount();
        if ( is_string($msg3) ){
          $_SESSION['error'] = $msg3;
          header('Location: add.php');
          return;
        }

        $msg2 = validatePer();
        if ( is_string($msg2) ){
          $_SESSION['error'] = $msg2;
          header('Location: add.php');
          return;
        }

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

        $_SESSION['success'] = "Profile added";
        header("Location: index.php");
        return;
  }

?>

<!DOCTYPE html>
<html>
<head> <title>Interview Creation Portal</title>
<?php require_once "head.php"; ?>
</head>
<body>

  <div class="container addc">
    <h1>Schedule a meet by <?php echo(htmlentities($_SESSION['name'])); ?> </h1>
    
    <?php flashMessages(); ?>


    <form method="post">
      <p>Headline: <br/>
        <input type="text" name="headline" class="he" size="80"/>
      </p>
      <p>Summary: <br/>
        <textarea name="summary" rows="8" class="sm" cols="80"></textarea>
      </p>
      <p>Date: <br/>
        <input type="date" class="dt" name="int_date" value="date" />
      <p>Start Time: <br/>
        <input type="time" class="tm" name="start_time" value="time" />
      <p>End Time: <br/>
        <input type="time" class="tm" name="end_time" value="time" />

      <p>Persons:
        <input type="submit" id="addPer" class="per" value="+">

      <div id="person_fields"> </div>

      <p>
      <input type="submit" class="addbutton" value="Add">
      <input type="submit" name="cancel" class="cancelbutton" value="Cancel">
      </p>
    </form>

    <script>

      //global funct. in js
      countPer = 0;

      //https://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript

      $(document).ready(function(){
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

    <script id="person-template" type="text">
        <div id="per@COUNT@">
          <p>Name: 
            <input type="text" size="80" name="name@COUNT@" class="person" value="" />
            <input type="button" class="minus" value="-" onclick="$('#per@COUNT@').remove(); return false;"><br>
          </p>
        <div>
    </script>

  </div>
  
</body>
</html>
