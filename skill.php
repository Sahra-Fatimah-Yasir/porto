<?php
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

// Fetch skills from database
$sql = "SELECT * FROM skills";
$result = $conn->query($sql);

// Close MySQL connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sahra's Portfolio</title>
    <link rel="icon" href="Image/engineer.ico">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"
        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="skill.css">
    <script src="https://code.iconify.design/1/1.0.7/iconify.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Arizonia&family=Montserrat:wght@400;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous">
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            <?php if ($result && $result->num_rows > 0): ?>
            const skills = <?php echo json_encode($result->fetch_all(MYSQLI_ASSOC)); ?>;
            const skillBox = document.getElementById('SkillBox');
            skills.forEach(skill => {
                const skillBar = document.createElement('div');
                skillBar.classList.add('SkillBar');

                const skillDiv = document.createElement('div');
                skillDiv.id = `Skill-${skill.skill_name}`;

                const skillIcon = document.createElement('span');
                skillIcon.classList.add('Skill-Area', 'iconify');
                skillIcon.dataset.icon = skill.skill_icon;
                skillIcon.dataset.inline = "false";
                skillIcon.dataset.width = "35";
                skillIcon.dataset.height = "35";

                const skillPercentage = document.createElement('span');
                skillPercentage.classList.add('percentage');
                skillPercentage.innerText = `${skill.skill_percentage}%`;

                skillDiv.appendChild(skillIcon);
                skillDiv.appendChild(skillPercentage);
                skillBar.appendChild(skillDiv);
                skillBox.appendChild(skillBar);

                // Update skill bar width based on percentage
                const skillBarElement = document.getElementById(`Skill-${skill.skill_name}`);
                const percentage = skill.skill_percentage;
                skillBarElement.style.width = `${percentage}%`;
            });
            <?php else: ?>
            console.error('Failed to fetch skills.');
            <?php endif; ?>
        });
    </script>
</head>

<body class="container-fluid body_section">
    <header>
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="navbar-brand brand-name" href="index.html">Sahra Fatimah Yasir</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link headerachor" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link headerachor" href="Education.html">Education</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link headerachor" href="skill.php">Skills</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link headerachor" href="Project.html">Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link headerachor" href="contact.html">Contact Me</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <section class="Section1">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div id="SkillBox">
                    <center class="Skill-heading">Proficiency</center>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">Made with ❤️ by Sahra Fatimah Yasir</footer>
</body>

</html>
