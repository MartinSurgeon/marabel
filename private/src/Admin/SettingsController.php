<?php
/**
 * Admin Settings Controller
 * Uaddara Basic School — SBA Management System
 * 
 * Handles uploading the Headmaster's signature and the School stamp.
 */

require_once PRIVATE_PATH . '/src/Helpers/Session.php';

class SettingsController {

    private string $uploadDir;

    public function __construct() {
        $this->uploadDir = ROOT_PATH . '/assets/uploads/signatures';
    }

    public function handle(): void {
        Session::requireRole('admin');

        // Create directories if they don't exist
        $brandingDir = ROOT_PATH . '/assets/uploads/branding';
        foreach ([$this->uploadDir, $brandingDir] as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type'] ?? '';
            
            if ($type === 'branding') {
                $this->handleBrandingUpdate();
            } else {
                $this->handleUploads();
            }
            return;
        }

        $this->displaySettings();
    }

    private function handleBrandingUpdate(): void {
        $schoolName        = $_POST['school_name'] ?? '';
        $schoolBody        = $_POST['school_body'] ?? '';
        $schoolTagline     = $_POST['school_tagline'] ?? '';
        $brandAccentColor  = $_POST['brand_accent_color'] ?? '#c00000';

        if (empty($schoolName)) {
            Session::flash('error', 'School name cannot be empty.');
        } else {
            Config::set('school_name', $schoolName, 'branding');
            Config::set('school_body', $schoolBody, 'branding');
            Config::set('school_tagline', $schoolTagline, 'branding');
            Config::set('brand_accent_color', $brandAccentColor, 'branding');
            Session::flash('success', 'Branding settings updated successfully.');
        }

        header('Location: ' . APP_BASE . '/admin/settings');
        exit;
    }

    private function displaySettings(): void {
        global $signatureExists, $stampExists;

        $signatureExists = file_exists($this->uploadDir . '/headmaster_signature.png') || file_exists($this->uploadDir . '/headmaster_signature.jpg') || file_exists($this->uploadDir . '/headmaster_signature.jpeg');
        $stampExists =     file_exists($this->uploadDir . '/school_stamp.png')         || file_exists($this->uploadDir . '/school_stamp.jpg')         || file_exists($this->uploadDir . '/school_stamp.jpeg');
    }

    private function handleUploads(): void {
        $type = $_POST['type'] ?? '';
        $action = $_POST['action'] ?? 'upload';

        if (!in_array($type, ['signature', 'stamp', 'logo'])) {
            Session::flash('error', 'Invalid target type.');
            header('Location: ' . APP_BASE . '/admin/settings');
            exit;
        }

        $dir = ($type === 'logo') ? ROOT_PATH . '/assets/uploads/branding' : $this->uploadDir;
        $basename = ($type === 'logo') ? 'school_logo' : (($type === 'signature') ? 'headmaster_signature' : 'school_stamp');

        if ($action === 'delete') {
            @unlink($dir . '/' . $basename . '.png');
            @unlink($dir . '/' . $basename . '.jpg');
            @unlink($dir . '/' . $basename . '.jpeg');
            
            if ($type === 'logo') {
                Config::set('school_logo', '/assets/img/school-logo.png', 'branding');
            }
            
            Session::flash('success', ucfirst($type) . ' removed successfully.');
            header('Location: ' . APP_BASE . '/admin/settings');
            exit;
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Please select a valid image file to upload.');
            header('Location: ' . APP_BASE . '/admin/settings');
            exit;
        }

        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
            Session::flash('error', 'Only PNG and JPG images are allowed.');
            header('Location: ' . APP_BASE . '/admin/settings');
            exit;
        }

        // Limit size to 2MB
        if ($file['size'] > 2 * 1024 * 1024) {
             Session::flash('error', 'Image is too large. Must be under 2MB.');
             header('Location: ' . APP_BASE . '/admin/settings');
             exit;
        }

        // Remove existing variations Before saving the new one
        @unlink($dir . '/' . $basename . '.png');
        @unlink($dir . '/' . $basename . '.jpg');
        @unlink($dir . '/' . $basename . '.jpeg');

        $destination = $dir . '/' . $basename . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            if ($type === 'logo') {
                Config::set('school_logo', '/assets/uploads/branding/' . $basename . '.' . $ext, 'branding');
            }
            Session::flash('success', ucfirst($type) . ' uploaded successfully.');
        } else {
            Session::flash('error', 'Failed to save the uploaded image.');
        }

        header('Location: ' . APP_BASE . '/admin/settings');
        exit;
    }
}
