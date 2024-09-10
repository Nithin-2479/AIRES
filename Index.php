<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
   header("Location: login.php");
   exit;
}

// Function to call Python script dynamically and get matching resumes
function getMatchingResumes($jobDescription, $matchPercentage) {
    // Escape shell arguments to prevent injection attacks
    $escapedJobDescription = escapeshellarg($jobDescription);
    $escapedMatchPercentage = escapeshellarg($matchPercentage);

    // Construct the command to run the Python script
    $command = "python3 main.py $escapedJobDescription $escapedMatchPercentage";
    $output = shell_exec($command);  // Execute the Python script

    // Check if there was an error executing the Python script
    if ($output === null) {
        die('Error executing Python script.');
    }

    // Decode the JSON response from the Python script
    $result = json_decode($output, true);

    // Return the decoded result
    return $result;
}

// Fetch recent job descriptions from the database
function recentJobDescriptions($db) {
    $query = "SELECT id, title FROM job_descriptions ORDER BY created_at DESC LIMIT 10";
    $result = $db->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$results = null;
//$recentJobDescriptions = recentJobDescriptions($db); // Correct function name

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jobDescription = $_POST['job_description'];
    $matchPercentage = $_POST['match_percentage'];
    $results = getMatchingResumes($jobDescription, $matchPercentage);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Matcher</title>
    <link rel="stylesheet" href="index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <img src="logo.png" alt="Company Logo" class="logo">
        <h1>Resume Matcher</h1>
        <form id="resumeMatchForm" enctype="multipart/form-data" method="POST">
            <div>
                <input type="radio" id="enter_jd" name="job_description_option" value="enter" checked>
                <label for="enter_jd">Enter Job Description</label>
                <input type="radio" id="upload_jd" name="job_description_option" value="upload">
                <label for="upload_jd">Upload Document</label>
                <input type="radio" id="select_jd" name="job_description_option" value="select">
                <label for="select_jd">Select Recent Job Description</label>
            </div>

            <div id="enter_jd_section">
                <textarea id="job_description" name="job_description" rows="4" cols="50"></textarea>
            </div>

            <div id="upload_jd_section" style="display: none;">
                <input type="file" id="jd_file" name="jd_file">
            </div>

            <div id="select_jd_section" style="display: none;">
                <select name="recent_job_description" id="recent_job_description">
                    <!-- Dynamically populated options -->
                </select>
            </div>

            <label for="match_percentage">Match Percentage:</label>
            <input type="number" id="match_percentage" name="match_percentage" min="0" max="100" required><br>

            <input type="submit" value="Match Resumes">
        </form>

        <div id="results">
            <!-- Matching Resumes Results -->
            <h2>Matching Resumes</h2>
            <label for="resume_count">Show:</label>
            <select id="resume_count">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <table>
                <tr>
                    <th>Reference Number</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
                <!-- Dynamically populated rows -->
            </table>
            <div class="pagination">
                <button id="prev_page">Previous</button>
                <button id="next_page">Next</button>
            </div>
        </div>

        <div>
            <form action="logout.php" method="post">
                <input type="submit" name="logout" value="Logout" class="logout-btn">
            </form>
        </div>
    </div>
    <footer>
        &copy; 2024 KenexOft Technologies. All rights reserved.
    </footer>
    <script>
        $(document).ready(function () {
            $('input[name="job_description_option"]').change(function () {
                $('#enter_jd_section, #upload_jd_section, #select_jd_section').hide();
                $('#' + $(this).val() + '_jd_section').show();
            });

            $('#resumeMatchForm').submit(function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: 'resume_matcher.php', // Pointing to the same PHP file
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $('#results').html(response);
                    }
                });
            });

            let currentPage = 1;
            let itemsPerPage = 10;

            function updateTable() {
                $('table tr').hide();
                $('table tr:first').show();
                $('table tr').slice(1 + (currentPage - 1) * itemsPerPage, 1 + currentPage * itemsPerPage).show();
            }

            $('#prev_page').click(function () {
                if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                }
            });

            $('#next_page').click(function () {
                if ($('table tr').length > currentPage * itemsPerPage) {
                    currentPage++;
                    updateTable();
                }
            });

            $('#resume_count').change(function () {
                itemsPerPage = parseInt($(this).val());
                currentPage = 1;
                updateTable();
            });

            updateTable();
        });
    </script>
</body>
</html>
