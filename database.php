<?php

function openConn(): PDO {
    $user = "h14965lf";
    $host = "dbhost.cs.man.ac.uk";
    $pass = "_UL37f734XT6NKJs8Cc9MMyBey7+wz";
    $name = "2022_comp10120_x3";
    try
    {
        $pdo = new PDO("mysql:host=$host;dbname=$name", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,
            PDO::ERRMODE_WARNING);
    }
    catch (PDOException $pe)
    {
        die("Could not connect to $host :" . $pe->getMessage());
    }
    echo (" CONN ");
    return $pdo;
}

function closeConn($obj): void
{
    $obj = null;
    echo " DISCONN ";
}

function showDB(): void
{
    $pdo = openConn();
    $sql = "SELECT * FROM users";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    while ($row = $stmt->fetch())
    {
        print("<h3>" . "id: " . $row['id'] . "</h3>");
        print("<h3>" . "Name: " . $row['name'] . "</h3>");
        print("<h3>" . "Username: " . $row['username'] . "</h3>");
        print("<h3>" . "passHash: " . $row['password_hash'] . "</h3>");
        print("<h3>" . "email: " . $row['email'] . "</h3>");
        print("<h3>" . '<img alt="Profile Picture" src="data:image/png;base64,'.base64_encode($row['profile_picture']).'"/>' . "</h3>");
    }
    closeConn($pdo);
}

function createUser($name, $profile_picture, $username, $email, $password) {
    if(checkIfUsernameExists($username)){
        echo("username already exists, please choose another. ");
        return;
    }
    if(checkIfEmailExists($email)){
        echo("email has already been used, please choose another. ");
        return;
    }
    $pdo = openConn();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, profile_picture, username, email, password_hash)
 VALUES (:name, :profile_picture, :username, :email, :password_hash)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'profile_picture' => $profile_picture,
        'username' => $username,
        'email' => $email,
        'password_hash' => $password_hash
    ]);
    echo("account: '" . $username . "' created.");
    closeConn($pdo);
}

function authenticateUser($email, $password): bool
{
    $pdo = openConn();

    $sql = "SELECT password_hash
            FROM users
            WHERE email= :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    if (password_verify($password, $row['password_hash'])) {
        echo("authentication successful");
        closeConn($pdo);
        return true;
    }
    echo("incorrect email or password");
    return false;


}

function checkIfEmailExists($email): bool
{
    $pdo = openConn();
    $sql = "SELECT email
            FROM users
            WHERE email = :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    if (isset($row['email'])) {
        closeConn($pdo);
        return(true);
    }
    else {
        closeConn($pdo);
        return(false);
    }
}

function checkIfUsernameExists($username): bool
{
    $pdo = openConn();
    $sql = "SELECT username
            FROM users
            WHERE username = :username";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'username' => $username
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    if (isset($row['username'])) {
        closeConn($pdo);
        return(true);
    }
    else {
        closeConn($pdo);
        return(false);
    }
}
function authenticateUsername($username, $password): bool
{
    $pdo = openConn();

    $sql = "SELECT password_hash
            FROM users
            WHERE username= :username";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'username' => $username
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();

    if (password_verify($password, $row['password_hash'])) {
        echo("authentication successful");
        closeConn($pdo);
        return true;
    }
    else {
        echo("incorrect username or password");
        return false;
    }
}

function createGroup($name, $group_picture, $userIDS) {
    $pdo = openConn();

    $sql = "INSERT INTO groups (name, group_picture)
 VALUES (:name, :group_picture)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'group_picture' => $group_picture,
    ]);

    $group_id = $pdo->lastInsertId();
    $sql = "INSERT INTO user_group_link (group_id, user_id)
 VALUES (:group_id, :user_id)";
    $stmt = $pdo->prepare($sql);

    foreach ($userIDS as $user_id) {
        $stmt->execute([
            'group_id' => $group_id,
            'user_id' => $user_id,
        ]);
    }

    closeConn($pdo);
    return(true);
}

function getTimetable($id, $url) {
    $fileContent = file_get_contents($url);
    if ($fileContent == false) {
        echo("error in file get");
        return(false);
    }
    if(str_starts_with($fileContent, "BEGIN:VCALENDAR")) {
        echo("yay");
        parseTimetable($id, $fileContent);
        return(true);
    }
    else {
        echo("bad");
        return(false);
    }
}

function parseTimetable($id, $fileContent) {
    echo(preg_match_all("BEGIN:VEVENT", $fileContent));
}

//createUser("Aran", file_get_contents("https://assets.manchester.ac.uk/corporate/images/design/logo-university-of-manchester.png" ), "a2trizzy", "aran@2trizzy.com", "test");
//showDB();
//echo(checkIfEmailExists("aran@2trizzy.com"));
//echo(checkIfUsernameExists("a2trizzy"));
//echo(checkIfUsernameExists("fakeUsername"));
getTimetable(1, "https://scientia-eu-v4-api-d3-02.azurewebsites.net//api/ical/b5098763-4476-40a6-8d60-5a08e9c52964/54df08df-70ec-869d-162a-1230db79bf15/timetable.ics");