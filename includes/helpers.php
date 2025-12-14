<?php
/**
 * Common Helper Functions
 * Utility functions used across the application
 */

/**
 * Format time range from database TIME format to readable format
 * @param string $start_time Start time (HH:MM:SS)
 * @param string $end_time End time (HH:MM:SS)
 * @return string Formatted time range (HH:MM - HH:MM)
 */
function formatTimeRange($start_time, $end_time) {
    $start = substr($start_time, 0, 5);
    $end = substr($end_time, 0, 5);
    return $start . ' - ' . $end;
}

/**
 * Format date to Indonesian format
 * @param string $date Date in Y-m-d format
 * @return string Date in Indonesian format
 */
function formatDateIndo($date) {
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $parts = explode('-', $date);
    if (count($parts) != 3) return $date;
    
    return $parts[2] . ' ' . $months[(int)$parts[1]] . ' ' . $parts[0];
}

/**
 * Get day name in Indonesian
 * @param string $day Day name in English
 * @return string Day name in Indonesian
 */
function getDayIndo($day) {
    $days = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    
    return $days[$day] ?? $day;
}

/**
 * Sanitize output for HTML display
 * @param string $text Text to sanitize
 * @return string Sanitized text
 */
function sanitizeOutput($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if a date is weekend
 * @param string $date Date in Y-m-d format
 * @return bool True if weekend
 */
function isWeekend($date) {
    $day = date('N', strtotime($date));
    return ($day == 6 || $day == 7); // 6 = Saturday, 7 = Sunday
}
