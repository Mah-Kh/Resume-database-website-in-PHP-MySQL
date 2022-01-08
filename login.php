<?php
require_once "pdo.php";
require_once "util.php";

session_start();
unset($_SESSION['name']); // to log the user out
unset($_SESSION['user_id']); // to log the user out

if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
//$stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // Pw is php123


//login.php 
// user1: foo@test.com and password: php123
// user2: bar@test.com and password: php123

if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    if ( strlen($_POST['email']) == 0 || strlen($_POST['pass']) == 0 ) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: login.php");
        return;
    }
    $check = hash('md5', $salt.$_POST['pass']);
    $stmt = $pdo->prepare('SELECT user_id, name FROM users 
            WHERE email = :em AND password = :pw');
    $stmt->execute(array(':em' => $_POST['email'],':pw' => $check)); 
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ( $row !== false ) {
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];
        header("Location: index.php");
        return;
    } else {
        $_SESSION['error'] = "Incorrect password";
        header("Location: login.php");
        return;
    }
}

?>
<!DOCTYPE html>
<html>
<?php require_once "head.php"; ?>
<body>
<header>
<h1 class="logo"><a href="./index.php">Resume Registry</a></h1>
</header>
<div class="my-form">
    <h1>Login</h1>
<?php flashMessage(); ?>
    <form method="POST" action="login.php">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="email">Email</label>
            <div class="col-sm-10">
                <input type="text" name="email" id="email" class="form-control-plaintext">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="id_1723">Password</label>
            <div class="col-sm-10">
                <input type="password" name="pass" id="id_1723" class="form-control-plaintext">
            </div>
        </div>
        <div class="submit">
            <input type="submit" onclick="return doValidate();" value="Log In" class="btn btn-primary mybtn btn-1">
            <input type="submit" name="cancel" value="Cancel" class="btn btn-primary mybtn btn-2 btn-c">
        </div>
    </form>
    </div>
<script type="text/javascript">
    function doValidate() {
        console.log('Validating...');
        try {
            mail = document.getElementById('email').value;
            pw = document.getElementById('id_1723').value;
            console.log("Validating email= "+mail+" pw= "+pw);
            if (mail == null || mail == "" || pw == null || pw == "") {
                alert("Both fields must be filled out");
                return false;
            }
            if ( mail.indexOf('@') == -1 ) {
                alert("Email must have an at-sign (@)");
                return false;
            }
            return true;
        } catch(e) {
            return false;
        }
        return false;
    }
</script>
</body>
