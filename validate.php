<?php

include_once('db_config.php');

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = test_input($_POST["username"]);
    $password = test_input($_POST["password"]);

    // Prepare query
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user with username exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password using password_verify function
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header("Location: admin.php");
            exit();
        } else {
            echo "<script>alert('Wrong password.');</script>";
        }
    } else {
        echo "<script>alert('Username not found.');</script>";
    }

    $stmt->close();
}

?>
