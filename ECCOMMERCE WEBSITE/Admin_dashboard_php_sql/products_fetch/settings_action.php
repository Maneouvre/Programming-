<?php
// Enforce strict JSON object validation output maps
header('Content-Type: application/json');

$host   = "localhost";
$dbname = "ecommerce_db";
$user   = "root";
$pass   = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Handle Form Operations intercept routines
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $adminName = trim($_POST['admin_name'] ?? '');
        $darkMode  = isset($_POST['dark_mode']) ? 1 : 0;
        $savedPath = null;

        if (empty($adminName)) {
            echo json_encode(['success' => false, 'message' => 'Username property value parameters cannot pass blank.']);
            exit;
        }

        // ===================================================
        // PROFILE FILE FILE EXTRACTION AND SAVE LOGIC
        // ===================================================
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            
            $fileTmpPath = $_FILES['avatar_file']['tmp_name'];
            $fileName    = $_FILES['avatar_file']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Whitelist safe execution extensions arrays to insulate against shell injections
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file signature context. Only JPG, PNG, WEBP and GIF images accepted.']);
                exit;
            }

            // Create target directory if it does not physically exist on your storage drive
            $uploadTargetDir = "../product_upload_dashboard/uploads/";
            if (!is_dir($uploadTargetDir)) {
                mkdir($uploadTargetDir, 0775, true);
            }

            // Create unique hashed name structure to prevent file system naming collusions
            $newFileName = "avatar_" . uniqid() . "." . $fileExtension;
            $destinationFileRoute = $uploadTargetDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destinationFileRoute)) {
                // Compile matching route string to map straight into table columns
                $savedPath = "../product_upload_dashboard/uploads/" . $newFileName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to write uploaded image stream asset to disk storage paths folders.']);
                exit;
            }
        }

        // ===================================================
        // PARAMETERIZED DATABASE SAVE CONTROLLER STATE
        // ===================================================
        // OPTIONAL PROACTIVE EXTENSION: Update your admin metadata profile rows table fields matching patterns
        // If you haven't created a table named admin_profile yet, you can uncomment this when ready:
        /*
        if ($savedPath) {
            $stmt = $pdo->prepare("UPDATE `admin_profile` SET `username` = :name, `dark_mode` = :dm, `avatar_path` = :avatar WHERE `id` = 1");
            $stmt->execute(['name' => $adminName, 'dm' => $darkMode, 'avatar' => $savedPath]);
        } else {
            $stmt = $pdo->prepare("UPDATE `admin_profile` SET `username` = :name, `dark_mode` = :dm WHERE `id` = 1");
            $stmt->execute(['name' => $adminName, 'dm' => $darkMode]);
        }
        */

        echo json_encode([
            'success'    => true, 
            'message'    => 'Account layout parameter settings adjusted successfully!',
            'new_avatar' => $savedPath // Return new path so frontend interface nodes update instantly
        ]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database update error exception triggered: ' . $e->getMessage()]);
    exit;
}
