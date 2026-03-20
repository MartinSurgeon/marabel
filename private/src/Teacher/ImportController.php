<?php
/**
 * Teacher Score Import Controller
 */

require_once PRIVATE_PATH . '/src/Helpers/Session.php';

class ImportController {

    public function handle(): void {
        Session::requireRole('teacher', 'admin');
        
        // This will handle file uploads in the next phase
    }
}
