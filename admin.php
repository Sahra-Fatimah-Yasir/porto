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

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = array();
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

if (isset($_GET['action'])) {
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
    } elseif ($_GET['action'] == 'add_skill' && $_SERVER["REQUEST_METHOD"] == "POST") {
        // Decode JSON data from request body
        $data = json_decode(file_get_contents("php://input"));

        // Validate JSON data
        if (isset($data->skill_name) && isset($data->skill_icon) && isset($data->skill_percentage)) {
            $skill_name = $data->skill_name;
            $skill_icon = $data->skill_icon;
            $skill_percentage = $data->skill_percentage;

            // Insert new skill into the database
            $sql = "INSERT INTO skills (skill_name, skill_icon, skill_percentage) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssi", $skill_name, $skill_icon, $skill_percentage);

                if ($stmt->execute()) {
                    echo json_encode(array("success" => true, "message" => "Skill added successfully."));
                } else {
                    echo json_encode(array("success" => false, "message" => "Failed to add skill: " . $stmt->error));
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
    } elseif ($_GET['action'] == 'delete_skill' && $_SERVER["REQUEST_METHOD"] == "POST") {
        // Decode JSON data from request body
        $data = json_decode(file_get_contents("php://input"));

        // Validate JSON data
        if (isset($data->id)) {
            $id = $data->id;

            // Delete skill from the database
            $sql = "DELETE FROM skills WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    echo json_encode(array("success" => true, "message" => "Skill deleted successfully."));
                } else {
                    echo json_encode(array("success" => false, "message" => "Failed to delete skill: " . $stmt->error));
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Edit Skills</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.2.3/css/bootstrap.min.css"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <style>s
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
        <button class="btn btn-success" onclick="showAddSkillModal()">Add Skill</button>
        <a href="admin.php?logout" class="btn btn-danger logout-btn">Logout</a>
    </div>

    <!-- Modal for Adding Skill -->
    <div class="modal" id="addSkillModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add Skill</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newSkillName">Skill Name</label>
                        <input type="text" class="form-control" id="newSkillName">
                    </div>
                    <div class="form-group">
                        <label for="newSkillIcon">Skill Icon</label>
                        <input type="text" class="form-control" id="newSkillIcon">
                    </div>
                    <div class="form-group">
                        <label for="newSkillPercentage">Skill Percentage</label>
                        <input type="number" class="form-control" id="newSkillPercentage">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="addSkill()">Add Skill</button>
                </div>
            </div>
        </div>
    </div>

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
                        skillIconCell.innerText = skill.skill_icon;
                        row.appendChild(skillIconCell);

                        const skillPercentageCell = document.createElement('td');
                        const input = document.createElement('input');
                        input.type = 'number';
                        input.value = skill.skill_percentage;
                        input.classList.add('form-control');
                        input.addEventListener('change', () => updateSkill(skill.id, input.value));
                        skillPercentageCell.appendChild(input);
                        row.appendChild(skillPercentageCell);

                        const actionsCell = document.createElement('td');
                        const deleteButton = document.createElement('button');
                        deleteButton.innerText = 'Delete';
                        deleteButton.classList.add('btn', 'btn-danger');
                        deleteButton.addEventListener('click', () => deleteSkill(skill.id));
                        actionsCell.appendChild(deleteButton);
                        row.appendChild(actionsCell);

                        tableBody.appendChild(row);
                    });
                });
        });

        function updateSkill(id, percentage) {
            fetch('admin.php?action=update_skill', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id, skill_percentage: percentage })
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert('Failed to update skill percentage: ' + data.message);
                    }
                });
        }

        function deleteSkill(id) {
            fetch('admin.php?action=delete_skill', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete skill: ' + data.message);
                    }
                });
        }

        function showAddSkillModal() {
            $('#addSkillModal').modal('show');
        }

        function addSkill() {
            const skillName = document.getElementById('newSkillName').value;
            const skillIcon = document.getElementById('newSkillIcon').value;
            const skillPercentage = document.getElementById('newSkillPercentage').value;

            fetch('admin.php?action=add_skill', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    skill_name: skillName,
                    skill_icon: skillIcon,
                    skill_percentage: skillPercentage
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to add skill: ' + data.message);
                    }
                });
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"
        integrity="sha384-Ph/xMyJ1whVQtnKfB1mcprEu+WoqSjlMNE5UraTY7ENu6Uq1TA4XJH1+PQ5MQQsz" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.2.3/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOUVNKGjtO4RO0lUYDFtwWl9i3txI1KtxMAf65V+FHU26A" crossorigin="anonymous">
    </script>
</body>
</html>
