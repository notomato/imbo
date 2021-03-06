<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\EventListener\ImageVariations\Storage;

use Imbo\Exception\StorageException;

/**
 * Filesystem storage driver for the image variations
 *
 * Configuration options supported by this driver:
 *
 * - <pre>(string) dataDir</pre> Absolute path to the base directory the image variations should
 *                               be stored in. Must be specified.
 *
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @package Storage
 */
class Filesystem implements StorageInterface {
    /**
     * Parameters for the driver
     *
     * @var array
     */
    private $params = array(
        'dataDir' => null,
    );

    /**
     * Class constructor
     *
     * @param array $params Parameters for the driver
     */
    public function __construct(array $params = null) {
        if ($params !== null) {
            $this->params = array_replace_recursive($this->params, $params);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function storeImageVariation($publicKey, $imageIdentifier, $blob, $width) {
        if (!is_writable($this->params['dataDir'])) {
            throw new StorageException('Could not store image variation (directory not writable)', 500);
        }

        $variationsDir = $this->getImagePath($publicKey, $imageIdentifier, $width, false);
        $oldUmask = umask(0);

        if (!is_dir($variationsDir)) {
            mkdir($variationsDir, 0775, true);
        }

        umask($oldUmask);

        $variationsPath = $this->getImagePath($publicKey, $imageIdentifier, $width);

        return (bool) file_put_contents($variationsPath, $blob);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageVariation($publicKey, $imageIdentifier, $width) {
        $variationPath = $this->getImagePath($publicKey, $imageIdentifier, $width);

        if (file_exists($variationPath)) {
            return file_get_contents($variationPath);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteImageVariations($publicKey, $imageIdentifier, $width = null) {
        // If width is specified, delete only the specific image
        if ($width !== null) {
            return unlink($this->getImagePath($publicKey, $imageIdentifier, $width, true));
        }

        // If width is not specified, delete every variation, then the folder
        $variationsPath = $this->getImagePath($publicKey, $imageIdentifier);
        if (!is_dir($variationsPath)) {
            return false;
        }

        array_map('unlink', glob($variationsPath . '/*'));
        return rmdir($variationsPath);
    }

    /**
     * Get the path to an image
     *
     * @param string $publicKey The key
     * @param string $imageIdentifier Image identifier
     * @param int $width Width of the image, in pixels
     * @param boolean $includeFilename Whether or not to include the last part of the path
     *                                 (the filename itself)
     * @return string
     */
    private function getImagePath($publicKey, $imageIdentifier, $width = null, $includeFilename = true) {
        $parts = array(
            $this->params['dataDir'],
            $publicKey[0],
            $publicKey[1],
            $publicKey[2],
            $publicKey,
            $imageIdentifier[0],
            $imageIdentifier[1],
            $imageIdentifier[2],
            $imageIdentifier,
        );

        if ($includeFilename) {
            $parts[] = $width;
        }

        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}
