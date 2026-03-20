<?php
/**
 * Admin Dashboard Controller
 * Uaddara Basic School — SBA Management System
 */

class DashboardController {

    public function handle(): void {
        // Just prepare some basic stats for the dashboard view
        global $stats;
        
        $stats = [
            'students' => DB::queryOne("SELECT COUNT(*) as c FROM students WHERE status = 'active'")['c'] ?? 0,
            'teachers' => DB::queryOne("SELECT COUNT(*) as c FROM users WHERE role = 'teacher' AND is_active = 1")['c'] ?? 0,
            'classes'  => DB::queryOne("SELECT COUNT(*) as c FROM classes WHERE academic_year_id = (SELECT id FROM academic_years WHERE is_active = 1 LIMIT 1)")['c'] ?? 0,
        ];
    }
}