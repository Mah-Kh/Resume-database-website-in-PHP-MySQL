<?php
require_once "pdo.php";
require_once "util.php";

session_start();

if ( ! isset($_SESSION['user_id']) ) {
  die('ACCESS DENIED');
  return;
}
if(isset($_POST['cancel'])){
  header("Location: index.php");
  return;
}
// Make sure the REQUEST parametr is present
if( ! isset($_REQUEST['profile_id']) ){
  $_SESSION['error'] = "Missing profile_id";
  header("Location: index.php");
  return;
}
// Load up the profile
$stmt = $pdo->prepare('SELECT * FROM profile WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array(':prof' => $_REQUEST['profile_id'], ':uid' => $_SESSION['user_id']));

$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if( $profile === false ){
  $_SESSION['error'] = "Could not load profile";
  header("Location: index.php");
  return;
}

// Handle the incomining data
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) 
    && isset($_POST['headline']) && isset($_POST['summary']) ){
    // Data validation
    $msg = validateProfile();
      if( is_string($msg) ){
          $_SESSION['error'] = $msg;
          header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
          return;
    }
    // Validate position entry if present 
    $msg = validatePos();
    if( is_string($msg) ){
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
        return;
    }
    // Validate education
    $msg = validateEdu();
    if( is_string($msg) ){
      $_SESSION['error'] = $msg;
      header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
      return;
    }
    // #######################################
    // Begin to update the data
    $stmt = $pdo->prepare('UPDATE profile SET first_name = :fn, last_name = :ln,
        email = :em, headline = :he, summary = :su
        WHERE profile_id = :pid AND user_id = :uid');
    $stmt->execute(array(
      ':pid' => $_REQUEST['profile_id'],
      ':uid' => $_SESSION['user_id'],
      ':fn' => $_POST['first_name'],
      ':ln' => $_POST['last_name'],
      ':em' => $_POST['email'],
      ':he' => $_POST['headline'],
      ':su' => $_POST['summary']
    ));
    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position
    WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
    // Insert the position entries
    insertPositions($pdo, $_REQUEST['profile_id']);   
    // Clear out the old education entries
    $stmt = $pdo->prepare('DELETE FROM Education
    WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
    // Insert the educations entries
    insertEducation($pdo, $_REQUEST['profile_id']);

    $_SESSION['success'] = 'Profile updated';
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
    <h1>Edit <span class="name"><?= htmlentities($_SESSION['name']); ?></span>&nbsp;profile </h1>
    </div>
    <div class="body">

<?php flashMessage(); ?>
<form method="post" action="edit.php">
<input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']); ?>">
<div class="form-group row">
    <label class="col-sm-2 col-form-label">First name:</label>
    <div class="col-sm-10">
    <input type="text" name="first_name" size="80" value="<?= htmlentities($row['first_name']); ?>">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Last name:</label>
    <div class="col-sm-10">
    <input type="text" name="last_name" size="80" value="<?= htmlentities($row['last_name']); ?>">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Email:</label>
    <div class="col-sm-10">
    <input type="text" name="email" size="80" value="<?= htmlentities($row['email']); ?>">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Headline:</label>
    <div class="col-sm-10">
    <input type="text" name="headline" size="80" value="<?= htmlentities($row['headline']); ?>">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Summary:</label>
    <div class="col-sm-10">
    <textarea name="summary" rows="8" cols="80">
<?= htmlentities($row['summary']); ?>
</textarea>
    </div>
</div>

<!-- Edit up to nine empty educations and position entries -->
<?php
// educations
$countEdu = 0;
// echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo('<div class="form-group row">');
echo('<label class="col-sm-2 col-form-label">Education:</label>');
echo('<div class="col-sm-10">');
echo('<input type="submit" id="addEdu" value="+">');
echo('</div>');
echo('<div id="edu-fields">');
if( count($schools) > 0 ){
  foreach( $schools as $school ){
    $countEdu++;
    echo('<div id="edu'.$countEdu.'">');
    echo('<div class="form-group row">');
    echo('<label class="col-sm-2 col-form-label">Year:</label>');
    echo('<div class="col-sm-10">');
    echo('<input type=text" name="edu_year'.$countEdu.'" value="'.$school['year'].'" />');
    echo('<input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false;">');
    echo('</div>');
    echo('</div>');
    echo('<div class="form-group row">');
    echo('<label class="col-sm-2 col-form-label">School:</label>');
    echo('<div class="col-sm-10">');
    echo('<input type="text" size="80" name="edu_school'.$countEdu.'" class="school"
    value="'.htmlentities($school['name']).'"/>');
    echo('</div>');
    echo('</div>');
    echo('</div>');
  }
}
echo('</div>');
echo('</div>');

// Positions
$pos = 0;
echo('<div class="form-group row">');
echo('<label class="col-sm-2 col-form-label">Position:</label>');
echo('<div class="col-sm-10">');
echo('<input type="submit" id="addPos" value="+">');
echo('</div>');

echo('<div id="position-fields">');
  foreach( $positions as $position ){
    $pos++;
    echo('<div id="position'.$pos.'">');
    echo('<div class="form-group row">');
    echo('<label class="col-sm-2 col-form-label">Year:</label>');
    echo('<div class="col-sm-10">');
    echo('<input type="text" name="year'.$pos.'"');
    echo(' value="'.$position['year'].'"/>');
    echo('<input type="button" value="-" ');
    echo(' onclick="$(\'#position'.$pos.'\').remove();return false;">');
    echo('<textarea name="desc'.$pos.'" row="8" cols="80">'."\n");
    echo(htmlentities($position['description'])."\n");
    echo("\n</textarea>");
    echo('</div>');

    echo('</div>');
    echo('</div>'); 
  }
echo('</div>');
echo('</div>');

?>
<p><input type="submit" name="edit" value="Save" class="btn btn-primary mybtn btn-1"/><input type="submit" name="cancel" value="Cancle" class="btn btn-primary mybtn btn-2 btn-c"/></p>
</form>
</div>
</div>
</div>
<script type="text/javascript">
countPos = <?= $pos ?>;
countEdu = <?= $countEdu ?>;

$(document).ready(function(){
    $('#addPos').click(function(event){
        event.preventDefault();
        if(countPos >= 9){
            alert('Maximum of nine position entries exceeded');
            return;
        }
        countPos++;

        $('#position-fields').append(
  '<div id="position'+countPos+'"> \
  <div class="form-group row"><label class="col-sm-2 col-form-label">Year:</label>\
  <div class="col-sm-10"><input type=text" name="year'+countPos+'" value="">\
  <input type="button" value="-"onclick="$(\'#position'+countPos+'\').remove();return false;">\
  <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea></div>\
  <div>');
    });

    $('#addEdu').click(function(event){
      event.preventDefault();
      if(countEdu >= 9){
          alert('Maximum of nine education entries exceeded');
          return;
      }
      countEdu++;
      // Grab some HTML with hot spots and insert into the DOM
      var source = $("#edu-template").html();
      $('#edu-fields').append(source.replace(/@COUNT@/g, countEdu));

      // Add the event handler to the new ones
      $('.school').autocomplete({
        source: "school.php"
      });
    });

    $('.school').autocomplete({
        source: "school.php"
    });
});
</script>
<!-- HTML with subsitution hot spots -->
<script id="edu-template" type="text">
<div id="edu@COUNT@">
    <div class="form-group row">
        <label class="col-sm-2 col-form-label">Year:</label>
        <div class="col-sm-10">
        <input type="text" name="edu_year@COUNT@" value="" />
        <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;">
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label">School: </label>
        <div class="col-sm-10">
        <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
        </div>
    </div>
  </div>

</script>
</body>
</html>


