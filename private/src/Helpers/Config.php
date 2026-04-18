<?php
/**
 * System Settings Configuration Helper
 * Marabel SBA — Persistent Config System
 */

class Config {
    private static ?array $settings = null;

    /**
     * Get a setting value by key.
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed {
        self::load();
        return self::$settings[$key] ?? $default;
    }

    /**
     * Set a setting value.
     * 
     * @param string $key
     * @param string $value
     * @param string $category
     * @return bool
     */
    public static function set(string $key, string $value, string $category = 'general'): bool {
        $sql = "INSERT INTO system_settings (setting_key, setting_value, category) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP";
        
        $success = DB::execute($sql, [$key, $value, $category, $value]) > 0;
        
        if ($success) {
            if (self::$settings === null) self::$settings = [];
            self::$settings[$key] = $value;
        }
        
        return $success;
    }

    /**
     * Load all settings into memory.
     */
    private static function load(): void {
        if (self::$settings !== null) return;

        $rows = DB::query("SELECT setting_key, setting_value FROM system_settings");
        self::$settings = [];
        foreach ($rows as $row) {
            self::$settings[$row['setting_key']] = $row['setting_value'];
        }
    }

    /**
     * Get all loaded settings.
     * 
     * @return array
     */
    public static function all(): array {
        self::load();
        return self::$settings;
    }
}
