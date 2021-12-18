
<?php
require_once "pdo.php";
session_start();

if ( ! isset($_SESSION['name']) ) {
    die('Not logged in');
}


if ( isset($_POST['delete']) && isset($_POST['interview_id']) 
) {
    $sql = 'DELETE FROM Interview WHERE interview_id = :zip';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['interview_id']));
    $_SESSION['success'] = 'Record deleted';
    header( 'Location: index.php' ) ;
    return;
}
if (isset($_POST['cancel'])){
  header( 'Location: index.php' ) ;
  return;
}

// Guardian: Make sure that user_id is present
if ( ! isset($_GET['interview_id']) ) {
  $_SESSION['error'] = "Missing interview_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT headline FROM interview where interview_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['interview_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}

?>
<html>
<head>
  <head> <title>Interview Creation Portal</title>
  <?php require_once "head.php"; ?>
</head>
<body>
  <div class="container deletec">
<p>Confirm: Deleting Interview </p>
<p>Headline: <?= htmlentities($row['headline']) ?></p>


<form method="post">
<input type="hidden" name="interview_id" value="<?= $row['interview_id'] ?>">
<input type="submit" value="Delete" class="addbutton" name="delete">
<input type="submit" value="Cancel" name="cancel" class="cancelbutton">
</form>

</div>
</body>
<html>
