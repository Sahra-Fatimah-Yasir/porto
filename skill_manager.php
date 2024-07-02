<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "zahraporto";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute query
    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            // Redirect to admin dashboard
            header("Location: admin.php");
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "No user found with that username.";
    }

    $stmt->close();
}

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = array();
    session_destroy();
    header("Location: admin.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit Skills</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <style>
        body {
            padding-top: 20px;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
            <h1 class="mt-5 mb-4">Edit Skills</h1>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Skill Name</th>
                        <th scope="col">Skill Icon</th>
                        <th scope="col">Skill Percentage</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody id="skillsTableBody">
                    <!-- Data skill akan dimuat di sini -->
                </tbody>
            </table>
            <a href="admin.php?logout" class="btn btn-danger logout-btn">Logout</a>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card mt-5">
                        <div class="card-body">
                            <h2 class="text-center mb-4">Admin Login</h2>
                            <?php if (isset($error_message)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            <form action="admin.php" method="post">
                                <div class="form-group">
                                    <label for="username">Username:</label>
                                    <input type="text" id="username" name="username" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password:</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block" name="login">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                fetch('admin.php?action=get_skills')
                    .then(response => response.json())
                    .then(data => {
                        const tableBody = document.getElementById('skillsTableBody');
                        data.forEach(skill => {
                            const row = document.createElement('tr');

                            const skillNameCell = document.createElement('td');
                            skillNameCell.innerText = skill.skill_name;
                            row.appendChild(skillNameCell);

                            const skillIconCell = document.createElement('td');
                            skillIconCell.innerHTML = `<img src="${skill.skill_icon}" alt="${skill.skill_name}" style="max-width: 50px; max-height: 50px;">`;
                            row.appendChild(skillIconCell);

                            const skillPercentageCell = document.createElement('td');
                            const input = document.createElement('input');
                            input.type = 'number';
                            input.value = skill.skill_percentage;
                            input.classList.add('form-control');
                            input.dataset.skillId = skill.id;
                            skillPercentageCell.appendChild(input);
                            row.appendChild(skillPercentageCell);

                            const actionsCell = document.createElement('td');
                            const button = document.createElement('button');
                            button.innerText = 'Save';
                            button.classList.add('btn', 'btn-primary', 'btn-sm', 'mr-1');
                            button.onclick = function () {
                                const newPercentage = input.value;
                                const skillId = input.dataset.skillId;
                                updateSkillPercentage(skillId, newPercentage);
                            };
                            actionsCell.appendChild(button);
                            row.appendChild(actionsCell);

                            tableBody.appendChild(row);
                        });
                    });
            });

            function updateSkillPercentage(skillId, newPercentage) {
                fetch('admin.php?action=update_skill', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: skillId,
                        skill_percentage: newPercentage
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Skill updated successfully!');
                        } else {
                            alert('Failed to update skill: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error updating skill:', error);
                        alert('Failed to update skill due to an error.');
                    });
            }
        </script>
    <?php endif; ?>
</body>
</html>

<?php
if (isset($_GET['action'])) {
    // Reconnect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_GET['action'] == 'get_skills') {
        // Retrieve skills from the database
        $sql = "SELECT id, skill_name, skill_icon, skill_percentage FROM skills";
        $result = $conn->query($sql);

        if ($result === false) {
            echo json_encode(array("success" => false, "message" => "Error executing query: " . $conn->error));
            $conn->close();
            exit();
        }

        $skills = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $skills[] = $row;
            }
        }

        echo json_encode($skills);
        $conn->close();
        exit();
    } elseif ($_GET['action'] == 'update_skill' && $_SERVER["REQUEST_METHOD"] == "POST") {
        // Decode JSON data from request body
        $data = json_decode(file_get_contents("php://input"));

        // Validate JSON data
        if (isset($data->id) && isset($data->skill_percentage)) {
            $id = $data->id;
            $percentage = $data->skill_percentage;

            // Update skill percentage in the database
            $sql = "UPDATE skills SET skill_percentage = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ii", $percentage, $id);

                if ($stmt->execute()) {
                    echo json_encode(array("success" => true, "message" => "Skill percentage updated successfully."));
                } else {
                    echo json_encode(array("success" => false, "message" => "Failed to update skill percentage: " . $stmt->error));
                }

                $stmt->close();
            } else {
                echo json_encode(array("success" => false, "message" => "Failed to prepare statement: " . $conn->error));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Invalid data received."));
        }

        $conn->close();
        exit();
    }

    $conn->close();
}
?>
