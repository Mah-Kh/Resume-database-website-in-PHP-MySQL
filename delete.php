<?php
require_once "pdo.php";

session_start();

if ( ! isset($_SESSION['user_id']) ) {
  die('ACCESS DENIED');
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM profile WHERE profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
    $_SESSION['success'] = 'Profile deleted';
    header( 'Location: index.php' ) ;
    return;
}

// Guardian: Make sure that profile_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT profile_id FROM profile where profile_id = :xyz");
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
<h2>Confirm: Deleting Profile <?= htmlentities($row['profile_id']) ?></h2>
<form method="post">
<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
<input type="submit" value="Delete" name="delete" class="btn btn-primary mybtn btn-d">
<a href="index.php" class="btn btn-primary mybtn btn-2 btn-c">Cancel</a>
</form>
</div>
</body>
</html>