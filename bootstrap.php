<?php

require('./vendor/autoload.php');

use ElephantWrench\ApplicationAspectKernel;


// Initialize an application aspect container
$applicationAspectKernel = ApplicationAspectKernel::getInstance();
$applicationAspectKernel->init(array(
        'debug' => True, // Use 'false' for production mode
        // Cache directory
        'cacheDir' => __DIR__ . '/cache/', // Adjust this path if needed
        // Include paths restricts the directories where aspects should be applied, or empty for all source files
        'includePaths' => array(__DIR__ . '/src/',
                                __DIR__ . '/tests/')
));
