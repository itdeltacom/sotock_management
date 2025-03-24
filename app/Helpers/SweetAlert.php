<?php

namespace App\Helpers;

/**
 * SweetAlert2 Helper for BATI Car Rental Admin Panel
 * 
 * This helper provides methods to generate SweetAlert2 notifications
 * with consistent styling and behavior across the application.
 */
class SweetAlert
{
    /**
     * Generate a success notification
     * 
     * @param string $title The title of the notification
     * @param string $message The message to display
     * @param string $position The position of the notification (default: 'top-end')
     * @param int $timer The auto-close timer in milliseconds (default: 3000)
     * @return string JavaScript code for SweetAlert2
     */
    public static function success($title, $message = '', $position = 'top-end', $timer = 3000)
    {
        return self::alert('success', $title, $message, $position, $timer);
    }

    /**
     * Generate an error notification
     * 
     * @param string $title The title of the notification
     * @param string $message The message to display
     * @param string $position The position of the notification (default: 'top-end')
     * @param int $timer The auto-close timer in milliseconds (default: 5000)
     * @return string JavaScript code for SweetAlert2
     */
    public static function error($title, $message = '', $position = 'top-end', $timer = 5000)
    {
        return self::alert('error', $title, $message, $position, $timer);
    }

    /**
     * Generate a warning notification
     * 
     * @param string $title The title of the notification
     * @param string $message The message to display
     * @param string $position The position of the notification (default: 'top-end')
     * @param int $timer The auto-close timer in milliseconds (default: 4000)
     * @return string JavaScript code for SweetAlert2
     */
    public static function warning($title, $message = '', $position = 'top-end', $timer = 4000)
    {
        return self::alert('warning', $title, $message, $position, $timer);
    }

    /**
     * Generate an info notification
     * 
     * @param string $title The title of the notification
     * @param string $message The message to display
     * @param string $position The position of the notification (default: 'top-end')
     * @param int $timer The auto-close timer in milliseconds (default: 3000)
     * @return string JavaScript code for SweetAlert2
     */
    public static function info($title, $message = '', $position = 'top-end', $timer = 3000)
    {
        return self::alert('info', $title, $message, $position, $timer);
    }

    /**
     * Generate a confirmation dialog
     * 
     * @param string $title The title of the dialog
     * @param string $message The message to display
     * @param string $confirmButtonText The text for the confirm button
     * @param string $cancelButtonText The text for the cancel button
     * @param string $confirmCallback JavaScript callback function when confirmed
     * @return string JavaScript code for SweetAlert2
     */
    public static function confirm($title, $message, $confirmButtonText = 'Yes', $cancelButtonText = 'Cancel', $confirmCallback = '')
    {
        return "
            Swal.fire({
                title: '{$title}',
                text: '{$message}',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2D3FE0',
                cancelButtonColor: '#EF4444',
                confirmButtonText: '{$confirmButtonText}',
                cancelButtonText: '{$cancelButtonText}',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: true
            }).then((result) => {
                if (result.isConfirmed) {
                    {$confirmCallback}
                }
            });
        ";
    }

    /**
     * Generate a generic alert notification
     * 
     * @param string $icon The icon type (success, error, warning, info)
     * @param string $title The title of the notification
     * @param string $message The message to display
     * @param string $position The position of the notification
     * @param int $timer The auto-close timer in milliseconds
     * @return string JavaScript code for SweetAlert2
     */
    private static function alert($icon, $title, $message = '', $position = 'top-end', $timer = 3000)
    {
        // Set icon color based on type
        $iconColor = self::getIconColor($icon);
        
        return "
            Swal.fire({
                icon: '{$icon}',
                title: '{$title}',
                text: '{$message}',
                position: '{$position}',
                timer: {$timer},
                timerProgressBar: true,
                toast: true,
                showConfirmButton: false,
                iconColor: '{$iconColor}',
                customClass: {
                    popup: 'swal2-toast colored-toast',
                    title: 'swal2-title-{$icon}'
                }
            });
        ";
    }

    /**
     * Get the color for the icon based on the alert type
     * 
     * @param string $icon The icon type
     * @return string The color hex code
     */
    private static function getIconColor($icon)
    {
        switch ($icon) {
            case 'success':
                return '#34D399'; 
            case 'error':
                return '#EF4444'; 
            case 'warning':
                return '#FBBF24'; 
            case 'info':
                return '#2D3FE0'; 
            default:
                return '#2D3FE0'; 
        }
    }
}