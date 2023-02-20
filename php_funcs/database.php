<?php

function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows") {
        pclose(popen("start /B ". $cmd, "r"));
    } else {
        exec($cmd . " > /dev/null &");
    }
}

function openConn(): PDO {
    require_once('../config.inc.php');


    $name = "2022_comp10120_x3";
    try
    {
        include '../config.inc.php';
        $pdo = new PDO("mysql:host=$database_host;dbname=$name", $database_user, $database_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,
            PDO::ERRMODE_WARNING);
    }
    catch (PDOException $pe)
    {
        die("Could not connect to $database_host :" . $pe->getMessage());
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

function addTimetable($id, $timetable_url){
    $pdo = openConn();

    $sql = "UPDATE users 
    SET timetable_url = :timetable_url
    WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
            'timetable_url' => $timetable_url,
            'id' => $id
        ]);


    echo("Added timetable to db");

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
function authenticateUsername($username, $password): int
// This function will return the id, can accept email or username.
// Returns either the user id, or -1 if errored.
{
    $pdo = openConn();

    $sql = "SELECT password_hash, id
            FROM users
            WHERE username= :username OR email= :email";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'username' => $username,
        'email' => $username
    ]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $row = $stmt->fetch();
    if (isset($row['password_hash'])) {
        if (password_verify($password, $row['password_hash'])) {
            echo("authentication successful");
            closeConn($pdo);
            return ($row['id']);
        }
        else {
            echo("incorrect username or password");
            closeConn($pdo);
            return (-1);
        }
    }
    else{
        echo("no");
        closeConn($pdo);
        return(-1);

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

function getTimetable($url) {
    $fileContent = file_get_contents($url);
    if ($fileContent == false) {
        echo("error in file get");
        return(false);
    }
    if(str_starts_with($fileContent, "BEGIN:VCALENDAR")) {
        $events = parseTimetable($fileContent);
        return($events);
    }
    else {
        echo("bad .ics file");
        return(false);
    }
}

function parseTimetable($fileContent) :array {
    /**
     * returns all events found in $fileContent, in a 2d array.
     * output: 2d array, where array[n] = childArray[summary of event n, start date+time of event n, end date+time of event n]
     * its messy i know
     */
    $begins = [];
    $ends = [];
    $dt_starts = [];
    $dt_ends = [];
    $summary = [];
    $AllEvents = [];


    $offset=0;
    $finish = '';
    $find="BEGIN:VEVENT"; //finds index of all event starts in string
    $find_length=  strlen($find);
    while ($string_position = strpos($fileContent, $find, $offset)) {
        $begins[] = $string_position;
        $offset=$string_position+$find_length;
    }

    $find="END:VEVENT"; //finds index of all event ends in string
    $find_length=  strlen($find);
    $offset=0;
    while ($string_position = strpos($fileContent, $find, $offset)) {
        $ends[] = $string_position;
        $offset=$string_position+$find_length;
    }

    for($i=0;$i<count($begins);$i++){
        $current = substr($fileContent, $begins[$i], $ends[$i]);

        $pos = strpos($current, "SUMMARY:") + 8;
        $line = substr($current, $pos);
        $summary[] = substr($current, $pos, $pos + strpos($line, PHP_EOL) - (strpos($current, "SUMMARY:") + 8));

        $pos = strpos(substr($current, strpos($current, "DTSTART") + 7), ":") + 1 + strpos($current, "DTSTART") + 7;
        $line = substr($current, $pos);
        $eventStartTime = substr($current, $pos, $pos + strpos($line, PHP_EOL) - $pos);
        $dt_starts[] = substr($eventStartTime, 0, 4) . '-' . substr($eventStartTime, 4, 2) . '-' . substr($eventStartTime, 6, 2) . ' ' . substr($eventStartTime, 9, 2) . ":" . substr($eventStartTime, 11, 2) . ":00";

        $pos = strpos(substr($current, strpos($current, "DTEND") + 5), ":") + 1 + strpos($current, "DTEND") + 5;
        $line = substr($current, $pos);
        $eventStartTime = substr($current, $pos, $pos + strpos($line, PHP_EOL) - $pos);
        $dt_ends[] = substr($eventStartTime, 0, 4) . '-' . substr($eventStartTime, 4, 2) . '-' . substr($eventStartTime, 6, 2) . ' ' . substr($eventStartTime, 9, 2) . ":" . substr($eventStartTime, 11, 2) . ":00";

        $AllEvents[] = [$summary[$i],$dt_starts[$i], $dt_ends[$i]]; //adds all current event information (summary,start,end) to AllEvents array.
    }
    //echo(var_dump($AllEvents));
    return($AllEvents);
}

function saveTimetable($user_id, $events) {
    execInBackground("php background.php " . $user_id . " " . base64_encode(serialize($events))); // CHANGED PHP PATH FOR SERVER, WILL NOT WORK ON LOCAL MACHINES ANY LONGER
}