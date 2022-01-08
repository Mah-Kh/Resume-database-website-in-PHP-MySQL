<?php
require_once "pdo.php";
require_once "util.php";

session_start();

// Make sure the REQUEST parametr is present
if( ! isset($_REQUEST['profile_id']) ){
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}
// Load up the profile
$stmt = $pdo->prepare('SELECT * FROM profile WHERE profile_id = :prof');
$stmt->execute(array(':prof' => $_REQUEST['profile_id']));

$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if( $profile === false ){
  $_SESSION['error'] = "Could not load profile";
  header("Location: index.php");
  return;
}
// Load up the position and educations rows
$positions = loadPos($pdo,$_REQUEST['profile_id']);
$schools = loadEdu($pdo,$_REQUEST['profile_id']);

// Guardian: Make sure that profile_id is present
$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}
?>
<!DOCTYPE html>
<html>
<?php require_once "head.php"; ?>
<body>
<header>
<h1 class="logo"><a href="./index.php">Resume Registry</a></h1>
</header>
<div class="container">
  <div class="profile">
    <div class="title">
      <h1>Profile <?= htmlentities($_REQUEST['profile_id']); ?></h1>
    </div>
    <div class="body">
<h4>First name:</h4>
<p><?= htmlentities($row['first_name']); ?></p>
<h4>Last name:</h4>
<p><?= htmlentities($row['last_name']); ?></p>
<h4>Email:</h4>
<p><?= htmlentities($row['email']); ?></p>
<h4>Summary:</h4>
<p><?= htmlentities($row['summary']); ?></p>
<h4>Educations:</h4>
<ul>
<?php
$edu = 0;
foreach( $schools as $school ){
  $edu++; 
  echo('<li>');
  echo( htmlentities($school['year']) );
  echo("\n");
  echo( htmlentities($school['name']) );
  echo('</li>'); 
}
?>
</ul>
<h4>Positions:</h4>
<ul>
<?php
$pos = 0;
foreach( $positions as $position ){
  $pos++; 
  echo('<li>');
  echo( htmlentities($position['year']) );
  echo("\n");
  echo( htmlentities($position['description']) );
  echo('</li>'); 
}
?>
</ul>
<?php
if ( isset($_SESSION['name']) ){
    if($row['user_id'] == $_SESSION['user_id']){
        echo('<p class="view-btns"><a href="edit.php?profile_id='.$row['profile_id'].'" class="btn btn-primary mybtn btn-1">Edit</a></p>');
        echo('<p class="view-btns btn-c"><a href="delete.php?profile_id='.$row['profile_id'].'" class="btn btn-primary mybtn btn-d">Delete</a></p>');
    }
}
?>
<a href="index.php" class="btn btn-primary mybtn btn-2 btn-c">Back</a>
</div>
</div>
</div>
</body>

