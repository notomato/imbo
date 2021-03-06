<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboUnitTest\Http\Request;

use Imbo\Http\Request\Request,
    Imbo\Router\Route;

/**
 * @covers Imbo\Http\Request\Request
 * @group unit
 * @group http
 */
class RequestTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Request
     */
    private $request;

    /**
     * Set up the request
     */
    public function setUp() {
        $this->request = new Request();
    }

    /**
     * Tear down the request
     */
    public function tearDown() {
        $this->request = null;
    }

    /**
     * @covers Imbo\Http\Request\Request::getTransformations
     */
    public function testGetTransformationsWithNoTransformationsPresent() {
        $this->assertEquals(array(), $this->request->getTransformations());
    }

    /**
     * @covers Imbo\Http\Request\Request::getTransformations
     */
    public function testGetTransformationsWithCorrectOrder() {
        $query = array(
            't' => array(
                'flipHorizontally',
                'flipVertically',
            ),
        );

        $request = new Request($query);
        $transformations = $request->getTransformations();
        $this->assertEquals('flipHorizontally', $transformations[0]['name']);
        $this->assertEquals('flipVertically', $transformations[1]['name']);
    }

    /**
     * @covers Imbo\Http\Request\Request::getTransformations
     */
    public function testGetTransformations() {
        $query = array(
            't' => array(
                // Valid transformations with all options
                'border:color=fff,width=2,height=2',
                'compress:level=90',
                'crop:x=1,y=2,width=3,height=4',
                'resize:width=100,height=100',

                // Transformations with no options
                'flipHorizontally',
                'flipVertically',

                // The same transformation can be applied multiple times
                'resize:width=50,height=75',
            ),
        );

        $request = new Request($query);
        $transformations = $request->getTransformations();
        $this->assertInternalType('array', $transformations);
        $this->assertSame(7, count($transformations));

        $this->assertEquals(array('color' => 'fff', 'width' => 2, 'height' => 2), $transformations[0]['params']);
        $this->assertEquals(array('level' => '90'), $transformations[1]['params']);
        $this->assertEquals(array('x' => 1, 'y' => 2, 'width' => 3, 'height' => 4), $transformations[2]['params']);
        $this->assertEquals(array('width' => 100, 'height' => 100), $transformations[3]['params']);
        $this->assertEquals(array(), $transformations[4]['params']);
        $this->assertEquals(array(), $transformations[5]['params']);
        $this->assertEquals(array('width' => 50, 'height' => 75), $transformations[6]['params']);
    }

    /**
     * @covers Imbo\Http\Request\Request::getImageIdentifier
     */
    public function testSetGetImageIdentifier() {
        $identifier = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $this->assertNull($this->request->getImageIdentifier());

        $route = new Route();
        $this->request->setRoute($route);

        $this->assertNull($this->request->getImageIdentifier());
        $route->set('imageIdentifier', $identifier);
        $this->assertSame($identifier, $this->request->getImageIdentifier());
    }

    /**
     * @covers Imbo\Http\Request\Request::getExtension
     */
    public function testSetGetExtension() {
        $extension = 'jpg';
        $this->assertNull($this->request->getExtension());

        $route = new Route();
        $this->request->setRoute($route);

        $this->assertNull($this->request->getExtension());
        $route->set('extension', $extension);
        $this->assertSame($extension, $this->request->getExtension());
    }

    /**
     * @covers Imbo\Http\Request\Request::getPublicKey
     */
    public function testSetGetPublicKey() {
        $publicKey = 'christer';
        $this->assertNull($this->request->getPublicKey());

        $route = new Route();
        $this->request->setRoute($route);

        $this->assertNull($this->request->getPublicKey());
        $route->set('publicKey', $publicKey);
        $this->assertSame($publicKey, $this->request->getPublicKey());
    }

    /**
     * @covers Imbo\Http\Request\Request::getImage
     * @covers Imbo\Http\Request\Request::setImage
     */
    public function testCanSetAndGetAnImage() {
        $image = $this->getMock('Imbo\Model\Image');
        $this->assertSame($this->request, $this->request->setImage($image));
        $this->assertSame($image, $this->request->getImage());
    }

    /**
     * @covers Imbo\Http\Request\Request::getRoute
     * @covers Imbo\Http\Request\Request::setRoute
     */
    public function testCanSetAndGetARoute() {
        $this->assertNull($this->request->getRoute());
        $route = $this->getMockBuilder('Imbo\Router\Route')->disableOriginalConstructor()->getMock();
        $this->assertSame($this->request, $this->request->setRoute($route));
        $this->assertSame($route, $this->request->getRoute());
    }

    /**
     * @expectedException Imbo\Exception\InvalidArgumentException
     * @expectedExceptionMessage Transformations must be specifed as an array
     * @expectedExceptionCode 400
     * @covers Imbo\Http\Request\Request::getTransformations
     */
    public function testRequiresTransformationsToBeSpecifiedAsAnArray() {
        $request = new Request(array(
            't' => 'desaturate',
        ));
        $request->getTransformations();
    }

    /**
     * @expectedException Imbo\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid transformation
     * @expectedExceptionCode 400
     * @covers Imbo\Http\Request\Request::getTransformations
     */
    public function testDoesNotGenerateWarningWhenTransformationIsNotAString() {
        $query = array(
            't' => array(
                array(
                    'flipHorizontally',
                    'flipVertically',
                ),
            ),
        );

        $request = new Request($query);
        $request->getTransformations();
    }

    public function getQueryStrings() {
        return array(
            'transformation with params' => array(
                't[]=thumbnail:width=100',
                't[]=thumbnail:width=100',
            ),
            'transformation with params, encoded' => array(
                't%5B0%5D%3Dthumbnail%3Awidth%3D100',
                't[0]=thumbnail:width=100',
            ),
        );
    }

    /**
     * @dataProvider getQueryStrings
     */
    public function testGetRawUriDecodesUri($queryString, $expectedQueryString) {
        $request = new Request(array(), array(), array(), array(), array(), array(
            'SERVER_NAME' => 'imbo',
            'SERVER_PORT' => 80,
            'QUERY_STRING' => $queryString,
        ));

        $uri = $request->getRawUri();
        $this->assertSame($expectedQueryString, substr($uri, strpos($uri, '?') + 1));
    }
}
