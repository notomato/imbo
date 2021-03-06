<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

/**
 * Enable the image transformation metadata cache listener, and store the cached images to a
 * directory in /tmp
 */
return array(
    'eventListeners' => array(
        'imageTransformationCache' => array(
            'listener' => 'Imbo\EventListener\ImageTransformationCache',
            'params' => array(
                'path' => '/tmp/imbo-behat-image-transformation-cache',
            ),
        ),
    ),
);
