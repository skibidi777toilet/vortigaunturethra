<?php
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Predefined subdirectory selected from dropdown
    $baseSubdirectory = isset($_POST['baseSubdirectory']) ? $_POST['baseSubdirectory'] : '';
    
    // Extra subdirectory specified by the user (if any)
    $extraSubdirectory = isset($_POST['extraSubdirectory']) ? trim($_POST['extraSubdirectory']) : '';
    
    // Construct full path using base subdirectory and extra subdirectory
    $subdirectory = $baseSubdirectory;
    if (!empty($extraSubdirectory)) {
        $subdirectory .= '/' . $extraSubdirectory;
    }

    // Create the full path
    $targetDirectory = __DIR__ . '/' . $subdirectory;

    // Ensure the directory exists
    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0755, true);
    }

    // Handle file upload or ZIP extraction
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $fileType = $file['type'];
        $tmpFilePath = $file['tmp_name'];
        $fileName = basename($file['name']);
        $targetFile = $targetDirectory . '/' . $fileName;

        // Check if the file is a ZIP (handle both common MIME types)
        if ($fileType === 'application/zip' || $fileType === 'application/x-zip-compressed') {
            $zip = new ZipArchive;

            // Check if the ZIP file exists and can be opened
            if ($zip->open($tmpFilePath) === TRUE) {
                // Extract the ZIP file to the target directory
                $zip->extractTo($targetDirectory);
                $zip->close();
                echo "ZIP file extracted successfully into: " . htmlspecialchars($subdirectory);
            } else {
                echo "Error: Could not open ZIP file.<br>";
            }
        } else {
            // Handle normal file upload
            if (move_uploaded_file($tmpFilePath, $targetFile)) {
                echo "File uploaded successfully into: " . htmlspecialchars($subdirectory);
            } else {
                echo "Error uploading file.<br>";
            }
        }
    }

    // Handle file or folder deletion
    if (isset($_POST['deletePath']) && !empty($_POST['deletePath'])) {
        $deletePath = trim($_POST['deletePath']);
        $fullDeletePath = __DIR__ . '/' . $deletePath;

        if (file_exists($fullDeletePath)) {
            // If it's a file, delete it
            if (is_file($fullDeletePath)) {
                unlink($fullDeletePath);
                echo "File deleted successfully: " . htmlspecialchars($deletePath);
            }
            // If it's a directory, delete it (and its contents)
            elseif (is_dir($fullDeletePath)) {
                deleteDirectory($fullDeletePath); // Call the recursive delete function
                echo "Directory deleted successfully: " . htmlspecialchars($deletePath);
            }
        } else {
            echo "Error: Path does not exist: " . htmlspecialchars($deletePath);
        }
    }
}

// Function to delete a directory and its contents recursively
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }

    // Loop through all the files and directories in the specified directory
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $fullPath = "$dir/$file";
        if (is_dir($fullPath)) {
            // If it's a directory, call deleteDirectory recursively
            deleteDirectory($fullPath);
        } else {
            // If it's a file, delete it
            unlink($fullPath);
        }
    }
    // Finally, remove the now-empty directory
    rmdir($dir);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reltonic FastDL</title>
</head>
<body>
    <h1>Reltonic FastDL</h1>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <label for="baseSubdirectory">Select upload folder:</label>
        <select name="baseSubdirectory" id="baseSubdirectory">
            <option value="">(root)</option>
            <option value="maps">maps</option>
            <option value="models">models</option>
            <option value="materials">materials</option>
            <option value="sound">sound</option>
            <option value="scripts">scripts</option>
            <option value="resource">resource</option>
        </select>
        <label for="extraSubdirectory">/</label>
        <input type="text" name="extraSubdirectory" id="extraSubdirectory" placeholder="">
        <br>
        <label for="file">Select file or ZIP:</label>
        <input type="file" name="file" id="file" required>
        <br>
        <label for="uplBtn">Overwrite and</label>
        <input name="uplBtn" type="submit" value="Upload">
        <label for="uplBtn">(ZIP files will be extracted)</label>
    </form>

    <h2>Delete</h2>
    <form action="upload.php" method="post">
        <label for="deletePath">Path:</label>
        <input type="text" name="deletePath" id="deletePath" required>
        <input type="submit" value="Delete">
    </form>
<p>
<b>WARNING: The max upload size is ~100MB!</b>
</body>
</html>
