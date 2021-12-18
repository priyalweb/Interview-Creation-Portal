<?php
require_once "pdo.php";
require_once "util.php";
session_start();

?>

<html>
<head>
<title>Interview Creation Portal</title>


  <?php require_once "head.php"; ?>
  <?php require_once "header.php"; ?>
  <!-- <link rel="stylesheet" type="text/css" href="css/style.css"> -->
  <link href="css/bootstrap.min.css" rel="stylesheet">

  <style>

  </style>
  <!-- Custom styles for this template -->
  <link href="css/index.css" rel="stylesheet">
</head>

<body id="backg">
  <div class="container indexc">
      <h1>Schedule Interviews and Meetings</h1>
      <?php
          if( !isset($_SESSION['name']) ){
              $link_address3 = 'login.php';
              echo "<a id=".'loginbutton'." href='".$link_address3."'>Please log in</a> <br>";
              echo("<br>");
          }  
          else{
              flashMessages();

              $link_address2 = 'logout.php';
              echo "<br><a id=".'logoutbutton'." href='".$link_address2."'>Logout</a><br/>";

              if ( isset($_SESSION['name']) && isset($_SESSION['user_id']) ) {
                  $link_address = 'add.php';
                  echo "<br><a class=".'addbutton'." href='".$link_address."'>Create Interview</a>";
              }
              echo("<br><br>");

              echo('<table >'."\n");
              echo("<tr>
                    <th>Headline</th>
                    <th>Summary</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Name</th>
                    <th>Action</th>
                  <tr>"."\n");

              $stmt = $pdo->query("SELECT interview.headline, interview.interview_id, interview.rank, interview.profile_id, interview.summary,
              interview.start, interview.end, interview.date, interview.user_id, profile.name
              FROM interview JOIN profile ON interview.profile_id = profile.profile_id");
                                  
                  while ( $interview = $stmt->fetch(PDO::FETCH_ASSOC) ) {

                      echo "<tr><td>";
                      echo('<a href="view.php?interview_id='.$interview['interview_id'].'">');
                      echo(htmlentities($interview['headline']));
                      echo(" ");
                      echo("</td><td>");
                      echo(htmlentities($interview['summary']));
                      echo("</td><td>");
                      echo(htmlentities($interview['date']));
                      echo("</td><td>");
                      echo(htmlentities($interview['start']));
                      echo("</td><td>");
                      echo(htmlentities($interview['end']));
                      echo("</td><td>");
                      echo(htmlentities($interview['name']));
                      echo("</td><td>");

                      echo('<a href="edit.php?interview_id='.$interview['interview_id'].'">Edit</a> / ');
                      echo('<a href="delete.php?interview_id='.$interview['interview_id'].'">Delete</a>');
                      echo("</td></tr>\n");
                  }

              echo("</table>"."\n");
          }
      ?>
    <div>
</body>
</html>

<!-- <?php //require_once 'footer.php'; ?> -->
