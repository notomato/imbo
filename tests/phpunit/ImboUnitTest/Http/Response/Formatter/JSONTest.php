<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboUnitTest\Http\Response\Formatter;

use Imbo\Http\Response\Formatter\JSON,
    DateTime;

/**
 * @covers Imbo\Http\Response\Formatter\JSON
 * @group unit
 * @group http
 * @group formatters
 */
class JSONTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var JSON
     */
    private $formatter;

    private $dateFormatter;

    /**
     * Set up the formatter
     *
     * @covers Imbo\Http\Response\Formatter\Formatter::__construct
     */
    public function setUp() {
        $this->dateFormatter = $this->getMock('Imbo\Helpers\DateFormatter');
        $this->formatter = new JSON($this->dateFormatter);
    }

    /**
     * Tear down the formatter
     */
    public function tearDown() {
        $this->dateFormatter;
        $this->formatter = null;
    }

    /**
     * @covers Imbo\Http\Response\Formatter\JSON::getContentType
     */
    public function testReturnsCurrectContentType() {
        $this->assertSame('application/json', $this->formatter->getContentType());
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatError
     * @covers Imbo\Http\Response\Formatter\JSON::encode
     */
    public function testCanFormatAnErrorModel() {
        $formattedDate = 'Wed, 30 Jan 2013 10:53:11 GMT';
        $date = new DateTime($formattedDate);

        $model = $this->getMock('Imbo\Model\Error');
        $model->expects($this->once())->method('getHttpCode')->will($this->returnValue(404));
        $model->expects($this->once())->method('getErrorMessage')->will($this->returnValue('Public key not found'));
        $model->expects($this->once())->method('getDate')->will($this->returnValue($date));
        $model->expects($this->once())->method('getImboErrorCode')->will($this->returnValue(100));
        $model->expects($this->once())->method('getImageIdentifier')->will($this->returnValue('identifier'));

        $this->dateFormatter->expects($this->once())->method('formatDate')->with($date)->will($this->returnValue($formattedDate));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertSame($formattedDate, $data['error']['date']);
        $this->assertSame('Public key not found', $data['error']['message']);
        $this->assertSame(404, $data['error']['code']);
        $this->assertSame(100, $data['error']['imboErrorCode']);
        $this->assertSame('identifier', $data['imageIdentifier']);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\JSON::formatError
     */
    public function testCanFormatAnErrorModelWhenNoImageIdentifierExists() {
        $date = new DateTime();

        $model = $this->getMock('Imbo\Model\Error');
        $model->expects($this->once())->method('getDate')->will($this->returnValue($date));
        $model->expects($this->once())->method('getImageIdentifier')->will($this->returnValue(null));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertArrayNotHasKey('imageIdentifier', $data);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatStatus
     */
    public function testCanFormatAStatusModel() {
        $formattedDate = 'Wed, 30 Jan 2013 10:53:11 GMT';
        $date = new DateTime($formattedDate);

        $model = $this->getMock('Imbo\Model\Status');
        $model->expects($this->once())->method('getDate')->will($this->returnValue($date));
        $model->expects($this->once())->method('getDatabaseStatus')->will($this->returnValue(true));
        $model->expects($this->once())->method('getStorageStatus')->will($this->returnValue(false));

        $this->dateFormatter->expects($this->once())->method('formatDate')->with($date)->will($this->returnValue($formattedDate));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertSame($formattedDate, $data['date']);
        $this->assertTrue($data['database']);
        $this->assertFalse($data['storage']);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatUser
     */
    public function testCanFormatAUserModel() {
        $formattedDate = 'Wed, 30 Jan 2013 10:53:11 GMT';
        $date = new DateTime($formattedDate);

        $model = $this->getMock('Imbo\Model\User');
        $model->expects($this->once())->method('getLastModified')->will($this->returnValue($date));
        $model->expects($this->once())->method('getNumImages')->will($this->returnValue(123));
        $model->expects($this->once())->method('getPublicKey')->will($this->returnValue('christer'));

        $this->dateFormatter->expects($this->once())->method('formatDate')->with($date)->will($this->returnValue($formattedDate));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertSame($formattedDate, $data['lastModified']);
        $this->assertSame('christer', $data['publicKey']);
        $this->assertSame(123, $data['numImages']);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatImages
     */
    public function testCanFormatAnImagesModel() {
        $formattedDate = 'Wed, 30 Jan 2013 10:53:11 GMT';

        $date = new DateTime();

        $addedDate = $date;
        $updatedDate = $date;
        $publicKey = 'christer';
        $imageIdentifier = 'identifier';
        $checksum = 'checksum';
        $extension = 'png';
        $mimeType = 'image/png';
        $filesize = 123123;
        $width = 800;
        $height = 600;
        $metadata = array(
            'some key' => 'some value',
            'some other key' => 'some other value',
        );

        $image = $this->getMock('Imbo\Model\Image');
        $image->expects($this->once())->method('getPublicKey')->will($this->returnValue($publicKey));
        $image->expects($this->once())->method('getImageIdentifier')->will($this->returnValue($imageIdentifier));
        $image->expects($this->once())->method('getChecksum')->will($this->returnValue($checksum));
        $image->expects($this->once())->method('getExtension')->will($this->returnValue($extension));
        $image->expects($this->once())->method('getMimeType')->will($this->returnValue($mimeType));
        $image->expects($this->once())->method('getAddedDate')->will($this->returnValue($addedDate));
        $image->expects($this->once())->method('getUpdatedDate')->will($this->returnValue($updatedDate));
        $image->expects($this->once())->method('getFilesize')->will($this->returnValue($filesize));
        $image->expects($this->once())->method('getWidth')->will($this->returnValue($width));
        $image->expects($this->once())->method('getHeight')->will($this->returnValue($height));
        $image->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));

        $images = array($image);
        $model = $this->getMock('Imbo\Model\Images');
        $model->expects($this->once())->method('getImages')->will($this->returnValue($images));
        $model->expects($this->once())->method('getHits')->will($this->returnValue(100));
        $model->expects($this->once())->method('getPage')->will($this->returnValue(2));
        $model->expects($this->once())->method('getLimit')->will($this->returnValue(20));
        $model->expects($this->once())->method('getCount')->will($this->returnValue(1));

        $this->dateFormatter->expects($this->any())->method('formatDate')->with($this->isInstanceOf('DateTime'))->will($this->returnValue($formattedDate));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertSame(array('hits' => 100, 'page' => 2, 'limit' => 20, 'count' => 1), $data['search']);
        $this->assertCount(1, $data['images']);
        $image = $data['images'][0];

        $this->assertSame($formattedDate, $image['added']);
        $this->assertSame($formattedDate, $image['updated']);
        $this->assertSame($publicKey, $image['publicKey']);
        $this->assertSame($filesize, $image['size']);
        $this->assertSame($width, $image['width']);
        $this->assertSame($height, $image['height']);
        $this->assertSame($imageIdentifier, $image['imageIdentifier']);
        $this->assertSame($checksum, $image['checksum']);
        $this->assertSame($extension, $image['extension']);
        $this->assertSame($mimeType, $image['mime']);
        $this->assertSame($metadata, $image['metadata']);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatImages
     */
    public function testCanFormatAnImagesModelWithNoMetadataSet() {
        $image = $this->getMock('Imbo\Model\Image');
        $image->expects($this->once())->method('getMetadata')->will($this->returnValue(null));
        $image->expects($this->once())->method('getAddedDate')->will($this->returnValue(new DateTime()));
        $image->expects($this->once())->method('getUpdatedDate')->will($this->returnValue(new DateTime()));

        $images = array($image);
        $model = $this->getMock('Imbo\Model\Images');
        $model->expects($this->once())->method('getImages')->will($this->returnValue($images));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertCount(1, $data['images']);
        $image = $data['images'][0];

        $this->assertArrayNotHasKey('metadata', $image);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatImages
     */
    public function testCanFormatAnImagesModelWithNoMetadata() {
        $image = $this->getMock('Imbo\Model\Image');
        $image->expects($this->once())->method('getMetadata')->will($this->returnValue(array()));
        $image->expects($this->once())->method('getAddedDate')->will($this->returnValue(new DateTime()));
        $image->expects($this->once())->method('getUpdatedDate')->will($this->returnValue(new DateTime()));

        $images = array($image);
        $model = $this->getMock('Imbo\Model\Images');
        $model->expects($this->once())->method('getImages')->will($this->returnValue($images));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertCount(1, $data['images']);
        $image = $data['images'][0];

        $this->assertEmpty($image['metadata']);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatImages
     */
    public function testCanFormatAnImagesModelWithNoImages() {
        $model = $this->getMock('Imbo\Model\Images');
        $model->expects($this->once())->method('getImages')->will($this->returnValue(array()));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertCount(0, $data['images']);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatMetadata
     */
    public function testCanFormatAMetadataModel() {
        $metadata = array(
            'some key' => 'some value',
            'some other key' => 'some other value',
        );
        $model = $this->getMock('Imbo\Model\Metadata');
        $model->expects($this->once())->method('getData')->will($this->returnValue($metadata));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertSame($data, $metadata);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatMetadata
     */
    public function testCanFormatAMetadataModelWithNoMetadata() {
        $model = $this->getMock('Imbo\Model\Metadata');
        $model->expects($this->once())->method('getData')->will($this->returnValue(array()));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertSame(array(), $data);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatArrayModel
     */
    public function testCanFormatAnArrayModel() {
        $data = array(
            'some key' => 'some value',
            'some other key' => 'some other value',
            'nested' => array(
                'subkey' => array(
                    'subsubkey' => 'some value',
                ),
            ),
        );
        $model = $this->getMock('Imbo\Model\ArrayModel');
        $model->expects($this->once())->method('getData')->will($this->returnValue($data));

        $json = $this->formatter->format($model);

        $this->assertSame(json_decode($json, true), $data);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatArrayModel
     */
    public function testCanFormatAnEmptyArrayModel() {
        $model = $this->getMock('Imbo\Model\ArrayModel');
        $model->expects($this->once())->method('getData')->will($this->returnValue(array()));

        $json = $this->formatter->format($model);

        $data = json_decode($json, true);
        $this->assertSame(array(), $data);
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatListModel
     */
    public function testCanFormatAListModel() {
        $list = array(1, 2, 3);
        $container = 'foo';
        $model = $this->getMock('Imbo\Model\ListModel');
        $model->expects($this->once())->method('getList')->will($this->returnValue($list));
        $model->expects($this->once())->method('getContainer')->will($this->returnValue($container));

        $this->assertSame('{"foo":[1,2,3]}', $this->formatter->format($model));
    }

    /**
     * @covers Imbo\Http\Response\Formatter\Formatter::format
     * @covers Imbo\Http\Response\Formatter\JSON::formatListModel
     */
    public function testCanFormatAnEmptyListModel() {
        $list = array();
        $container = 'foo';
        $model = $this->getMock('Imbo\Model\ListModel');
        $model->expects($this->once())->method('getList')->will($this->returnValue($list));
        $model->expects($this->once())->method('getContainer')->will($this->returnValue($container));

        $this->assertSame('{"foo":[]}', $this->formatter->format($model));
    }
}
