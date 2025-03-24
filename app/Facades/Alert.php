<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string success(string $title, string $message = '', string $position = 'top-end', int $timer = 3000)
 * @method static string error(string $title, string $message = '', string $position = 'top-end', int $timer = 5000)
 * @method static string warning(string $title, string $message = '', string $position = 'top-end', int $timer = 4000)
 * @method static string info(string $title, string $message = '', string $position = 'top-end', int $timer = 3000)
 * @method static string confirm(string $title, string $message, string $confirmButtonText = 'Yes', string $cancelButtonText = 'Cancel', string $confirmCallback = '')
 * 
 * @see \App\Helpers\SweetAlert
 */
class Alert extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sweet-alert';
    }
}