<?php
include 'db.php';

if (isset($_GET['id'])) {
    $resumeId = $_GET['id'];
    
    // Fetch the resume file path from the database
    $sql = "SELECT file_path FROM resumes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $resumeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $filePath = $row['file_path'];
        
        // Set headers for file download
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . basename($filePath) . "\"");
        
        // Output file contents
        readfile($filePath);
    } else {
        echo "Resume not found.";
    }
} else {
    echo "Invalid request.";
}