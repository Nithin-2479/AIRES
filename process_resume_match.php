<?php
include 'db.php';

// Process the form data
$jobDescription = $_POST['job_description'];
$matchPercentage = $_POST['match_percentage'];

$results = getMatchingResumes($jobDescription, $matchPercentage);

// Generate the HTML for the results table
if ($results) {
    echo "<h2>Matching Resumes</h2>";
    echo "<table>";
    echo "<tr><th>Name</th><th>Match Percentage</th><th>Action</th></tr>";
    foreach ($results['resumes'] as $resume) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($resume['name']) . "</td>";
        echo "<td>" . htmlspecialchars($resume['match']) . "%</td>";
        echo "<td><a href='download_resume.php?id=" . $resume['id'] . "'>Download</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No matching resumes found.</p>";
}