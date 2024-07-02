<?php
session_start();

include_once('db_config.php');

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
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

        // Verify password directly from database (plaintext comparison)
        if ($password === $user['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header("Location: admin.php");
            exit();
        } else {
            $error_message = "Wrong password.";
        }
    } else {
        $error_message = "Username not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Integrasi Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Arizonia&family=Montserrat:wght@400;800&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">
    <style>
        body {
            background-color: #19123B;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: #212042;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            text-align: center;
            color: #fff;
        }
        .login-box h1 {
            margin-bottom: 30px;
            color: #F2CEFF;
            font-family: 'Arizonia', cursive;
        }
        .textbox {
            position: relative;
            margin-bottom: 20px;
        }
        .textbox i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #57557A;
        }
        .textbox input {
            width: calc(100% - 40px);
            padding: 10px 10px 10px 40px;
            border: 1px solid #57557A;
            border-radius: 5px;
            background: #19123B;
            color: #fff;
            outline: none;
            letter-spacing: 1px;
        }
        .textbox input::placeholder {
            color: #57557A;
        }
        .textbox input:focus {
            border: 1px solid #29cc61;
        }
        .button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 50px;
            background: linear-gradient(135deg, rgba(176, 106, 252, 1) 39%, rgba(116, 17, 255, 1) 101%);
            color: #fff;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: linear-gradient(135deg, rgba(176, 106, 252, 0.8) 39%, rgba(116, 17, 255, 0.8) 101%);
        }
        .fab {
            display: flex;
            justify-content: center;
            align-items: center;
            border: none;
            background: #2A284D;
            height: 40px;
            width: 90px;
            margin: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .fab i {
            font-size: 20px;
        }
        .fab i.fa-twitter {
            color: #56ABEC;
        }
        .fab i.fa-facebook {
            color: #1775F1;
        }
        .fab i.fa-google {
            color: #CB5048;
        }
        .division {
            text-align: center;
            margin: 30px auto 20px;
            position: relative;
        }
        .division .line {
            border-top: 1.5px solid #57557A;
            position: absolute;
            top: 13px;
            width: 85%;
        }
        .line.l {
            left: 52px;
        }
        .line.r {
            right: 45px;
        }
        .division span {
            font-weight: 600;
            font-size: 14px;
            background: #212042;
            padding: 0 10px;
            color: #57557A;
        }
        @media(max-width: 450px) {
            .fab {
                width: 100%;
                height: 100%;
            }
            .division .line {
                width: 50%;
            }
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h1>Login</h1>
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="textbox">
                <i class="fa fa-user" aria-hidden="true"></i>
                <input type="text" placeholder="Username" name="username" required>
            </div>
            <div class="textbox">
                <i class="fa fa-lock" aria-hidden="true"></i>
                <input type="password" placeholder="Password" name="password" required>
            </div>
            <input class="button" type="submit" name="login" value="Sign In">
        </form>
    </div>

    <!-- Script Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous">
    </script>
</body>

</html>
