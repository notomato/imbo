<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

// Default config for testing
$testConfig = array(
    'auth' => array(
        'publickey' => 'privatekey',
        'user' => 'key',
    ),

    'database' => function() {
        return new Imbo\Database\MongoDB(array(
            'databaseName' => 'imbo_testing',
        ));
    },

    'storage' => function() {
        return new Imbo\Storage\GridFS(array(
            'databaseName' => 'imbo_testing',
        ));
    },
);

// Default Imbo config
$defaultConfig = require __DIR__ . '/../../../config/config.default.php';

// Custom test config, if any, specified in the X-Imbo-Test-Config HTTP request header
if (isset($_SERVER['HTTP_X_IMBO_TEST_CONFIG'])) {
    $customConfig = require __DIR__ . '/' . basename($_SERVER['HTTP_X_IMBO_TEST_CONFIG']);
} else {
    $customConfig = array();
}

// Return the merged configuration, having the custom config overwrite the default testing config,
// which in turn overwrites the default config
return array_replace_recursive($defaultConfig, $testConfig, $customConfig);
