<?php
// Make the database connection and leave it in the variable $pdo
require_once "pdo.php";
require_once "util.php";

session_start();

// Retrive the profiles from database
$stmt = $pdo->query('SELECT * FROM Profile');
$profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<?php require_once "head.php"; ?>
<body>
<header>
<h1 class="logo"><a href="./index.php">Resume Registry</a></h1>
</header>
<div class="container">
<?php
flashMessage();

if ( isset($_SESSION['user_id']) ){
    echo('<p class="user-view-top"><a href="add.php" class="btn btn-primary mybtn btn-1">Add New Entry</a></p>'."\n");
    echo('<p class="user-view-top"><a href="logout.php" class="btn btn-primary mybtn btn-2 btn-c">Logout</a></p>'."\n");
} else {
    echo('<h2><a href="login.php">Login to add your profile or edit your profile</a></h2>'."\n");
}
?>

<?php
    $selectsql = "SELECT * FROM Profile";
    $stmt = $pdo->query($selectsql);
    if($stmt->rowCount() > 0){
        echo('<table class="view" border="1"><thead><td>First Name</td>');
            echo("</td><td>");
            echo("Last Name");
            echo("</td><td>");
            echo("Profile");
            echo("</td>");
            echo("</thead>");
            echo("</td></thead>");
        while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
            echo "<tbody><tr><td>";
            echo(htmlentities($row['first_name']));
            echo("</td><td>");
            echo(htmlentities($row['last_name']));
            echo("</td><td>");
            echo('<p><a href="view.php?profile_id='.$row['profile_id'].'">View Details</a></p>');
            if ( isset($_SESSION['name']) ){
                if($row['user_id'] == $_SESSION['user_id']){
                    echo('<p><a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a></p>');
                    echo('<p><a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a></p>');
                }
            }
            echo("</td></tr></tbody>\n");
        }
        echo("</table>");
    } else {
        echo("<p>No rows found</p>");
    }
?>

</div>
</body>

