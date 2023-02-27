<?php

include "../php_funcs/database.php";
include "../php_funcs/user-session.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["invite_id"])) {
        $invite_id = htmlspecialchars($_POST["invite_id"]);
        redirectIfNotLoggedIn("./login.php?redirect=invite.php?id=" . $invite_id);
    } else {
        echo "This invite link does not exist or is no longer valid.";
        die();
    }

} else {
    if (isset($_GET["id"])) {
        $invite_id = htmlspecialchars($_GET["id"]);
        redirectIfNotLoggedIn("./login.php?redirect=pages/invite.php?id=" . $invite_id);
    } else {
        echo "This invite link does not exist or is no longer valid.";
        die();
    }
}
$info = getGroupInfoFromInviteLink($invite_id);

if (!$info) {
    echo "This invite link does not exist or is no longer valid.";
    die();
}

$group_id = $info["id"];
$group_name = $info["name"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    addUserToGroup($user_id, $group_id);
    redirectIfLoggedIn("./profile.html");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/invite.css">
</head>
<body>

<header>
    <div class = "logo">
        <button class="mainlogo" onClick="window.location.href = '../index.html' " id="btn" type="button"><img class="main-img" src="../images/logo_white.png"></button>
    </div>

</header>

<div class = "modal">
    <div class = "firstline">
        <p>JOIN GROUP</p>
    </div>
    <div class = "inviteinfo">
        <p>You were invited to join the group <?php echo htmlspecialchars($group_name)?><br><br></p>
        <p>Would you like to join this group?<br><p>
    </div>

    <div class = "inputbox">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

            <input type="hidden" name="invite_id" value="<?php echo $invite_id;?>">

            <div class = "buttonbox">
                <button class="buttondesign" id = "post" type = "submit" value = "">Join Group</button>
            </div>

        </form>
    </div>
</div>

<footer id ="footer">
    <a href="#privacypolicy">Privacy Policy</a>
    <a href="#t&c">Terms & Conditions</a>
    <a href="#contact">Contact Us</a>
</footer>


</body>
</html>
