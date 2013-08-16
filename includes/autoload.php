<?php

/**
 * Autoload
 * 
 * @param string $class_name The name of the class
 */
function AutoLoad($class_name)
{
    if (file_exists('includes/' . strtolower($class_name) . '.php')) {
        require_once 'includes/'. strtolower($class_name) . '.php';
    }
}

// Start autoload
spl_autoload_register('AutoLoad');
