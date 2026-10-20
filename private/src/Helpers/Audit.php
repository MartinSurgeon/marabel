<?php
/**
 * Audit Helper — records who changed student data, and from what to what
 * Uaddara Basic School — SBA Management System
 */

class Audit {

    /**
     * Write one audit_log row. Never throws: a logging failure must not block a save.
     *
     * @param string     $action    e.g. 'score_update', 'remark_update', 'year_transition'
     * @param string     $table     table that changed
     * @param int|null   $recordId  usually the student id
     * @param mixed      $old       previous value (scalar or array, stored as JSON)
     * @param mixed      $new       new value (scalar or array, stored as JSON)
     */
    public static function log(string $action, string $table, ?int $recordId, mixed $old, mixed $new): void {
        try {
            DB::execute(
                "INSERT INTO audit_log (user_id, action, table_name, record_id, old_value, new_value, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    Session::userId(),
                    $action,
                    $table,
                    $recordId,
                    $old === null ? null : json_encode($old),
                    $new === null ? null : json_encode($new),
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]
            );
        } catch (\Throwable $e) {
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }
}
