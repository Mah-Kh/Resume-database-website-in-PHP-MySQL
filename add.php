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

// Handle the incomining data
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) 
    && isset($_POST['headline']) && isset($_POST['summary'])){
        // Data Validation
        $msg = validateProfile();
        if( is_string($msg) ){
            $_SESSION['error'] = $msg;
            header("Location: add.php");
            return;
        }
        // Validate position entry if present 
        $msg = validatePos();
        if( is_string($msg) ){
            $_SESSION['error'] = $msg;
            header("Location: add.php");
            return;
        }
        // Validate education
        $msg = validateEdu();
        if( is_string($msg) ){
          $_SESSION['error'] = $msg;
          header("Location: edit.php?profile_id=".$_REQUEST["profile_id"]);
          return;
        }
        // Data is valid - time to insert
        $sql = 'INSERT INTO profile (user_id, first_name, last_name, email, headline, summary) 
            VALUES (:uid, :fn, :ln, :em, :he, :su)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary']));
        $profile_id = $pdo->lastInsertId();

        // insert the position entries
        insertPositions($pdo, $profile_id); 
        // Insert the educations entries
        insertEducation($pdo, $profile_id);

        $_SESSION['success'] = 'Profile added';
        header("Location: index.php");
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
    <h1>Add <span class="name"><?= htmlentities($_SESSION['name']); ?></span>&nbsp;profile</h1>
    </div>
    <div class="body">


<?php flashMessage(); ?>
<form method="post">
<div class="form-group row">
    <label class="col-sm-2 col-form-label">First name:</label>
    <div class="col-sm-10">
        <input type="text" name="first_name" size="80">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Last name:</label>
    <div class="col-sm-10">
        <input type="text" name="last_name" size="80">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Email:</label>
    <div class="col-sm-10">
        <input type="text" name="email" size="80">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Headline:</label>
    <div class="col-sm-10">
        <input type="text" name="headline" size="80">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Summary:</label>
    <div class="col-sm-10">
        <textarea name="summary" rows="8" cols="80"></textarea>
    </div>
</div>
<!-- add up to nine empty education entries -->
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Education:</label>
    <div class="col-sm-10">
        <input type="submit" id="addEdu" value="+">
    </div>
    <div id="edu-fields"></div>
</div>
<!-- add up to nine empty position entries -->
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Position:</label>
    <div class="col-sm-10">
        <input type="submit" id="addPos" value="+">
    </div>
    <div id="position-fields"></div>
</div>
<div class="submit">
    <input type="submit" name="add" value="Add" class="btn btn-primary mybtn btn-1"/>
    <input type="submit" name="cancel" value="Cancle" class="btn btn-primary mybtn btn-2 btn-c"/>
</div>

</form>
</div>
</div>
</div>
<script type="text/javascript">
countPos = 0;
countEdu = 0;
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

    // add educations
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
