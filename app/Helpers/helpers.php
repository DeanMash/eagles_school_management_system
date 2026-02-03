<?php

if (!function_exists('getEventColor')) {
    function getEventColor($type)
    {
        $colors = [
            'academic' => '#007bff',
            'exam' => '#dc3545',
            'sports' => '#28a745',
            'cultural' => '#ffc107',
            'holiday' => '#17a2b8',
            'meeting' => '#6c757d',
            'other' => '#6f42c1'
        ];

        return $colors[$type] ?? '#6c757d';
    }
}