<?php

namespace PHPCI\Plugin\Tests\Util;

require_once __DIR__ . "/ExamplePlugins.php";

use PHPCI\Plugin\Util\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \PHPCI\Plugin\Util\Factory
     */
    protected $testedFactory;

    protected $expectedResource;

    protected $resourceLoader;

    protected function setUp()
    {
        $this->testedFactory = new Factory();

        // Setup a resource that can be returned and asserted against
        $this->expectedResource = new \stdClass();
        $resourceLink = $this->expectedResource;
        $this->resourceLoader = function() use (&$resourceLink) {
            return $resourceLink;
        };
    }

    protected function tearDown()
    {
        // Nothing to do.
    }


    public function testRegisterResourceThrowsExceptionWithoutTypeAndName()
    {
        $this->setExpectedException("InvalidArgumentException");
        $this->testedFactory->registerResource($this->resourceLoader, null, null);
    }

    public function testBuildPluginWorksWithConstructorlessPlugins()
    {
        $namespace = '\\PHPCI\\Plugin\\Tests\\Util\\';
        $expectedPluginClass = $namespace  .'ExamplePluginWithNoConstructorArgs';
        $plugin = $this->testedFactory->buildPlugin($expectedPluginClass);
        $this->assertInstanceOf($expectedPluginClass, $plugin);
    }

    public function testBuildPluginWorksWithSingleOptionalArgConstructor()
    {
        $namespace = '\\PHPCI\\Plugin\\Tests\\Util\\';
        $expectedPluginClass = $namespace  . 'ExamplePluginWithSingleOptionalArg';
        $plugin = $this->testedFactory->buildPlugin($expectedPluginClass);
        $this->assertInstanceOf($expectedPluginClass, $plugin);
    }

    public function testBuildPluginThrowsExceptionIfMissingResourcesForRequiredArg()
    {
        $this->setExpectedException(
            'DomainException',
            'Unsatisfied dependency: requiredArgument'
        );

        $namespace = '\\PHPCI\\Plugin\\Tests\\Util\\';
        $expectedPluginClass = $namespace  . 'ExamplePluginWithSingleRequiredArg';
        $plugin = $this->testedFactory->buildPlugin($expectedPluginClass);
    }

    public function testBuildPluginLoadsArgumentsBasedOnName()
    {
        $namespace = '\\PHPCI\\Plugin\\Tests\\Util\\';
        $expectedPluginClass = $namespace  . 'ExamplePluginWithSingleRequiredArg';

        $this->testedFactory->registerResource(
            $this->resourceLoader,
            "requiredArgument"
        );

        /** @var ExamplePluginWithSingleRequiredArg $plugin */
        $plugin = $this->testedFactory->buildPlugin($expectedPluginClass);

        $this->assertEquals($this->expectedResource, $plugin->RequiredArgument);
    }

    public function testBuildPluginLoadsArgumentsBasedOnType()
    {
        $namespace = '\\PHPCI\\Plugin\\Tests\\Util\\';
        $expectedPluginClass = $namespace  . 'ExamplePluginWithSingleTypedRequiredArg';

        $this->testedFactory->registerResource(
            $this->resourceLoader,
            null,
            "stdClass"
        );

        /** @var ExamplePluginWithSingleTypedRequiredArg $plugin */
        $plugin = $this->testedFactory->buildPlugin($expectedPluginClass);

        $this->assertEquals($this->expectedResource, $plugin->RequiredArgument);
    }

    public function testBuildPluginLoadsFullExample()
    {
        $namespace = '\\PHPCI\\Plugin\\Tests\\Util\\';
        $expectedPluginClass = $namespace  . 'ExamplePluginFull';

        $this->registerBuildAndBuilder();

        /** @var ExamplePluginFull $plugin */
        $plugin = $this->testedFactory->buildPlugin($expectedPluginClass);

        $this->assertInstanceOf($expectedPluginClass, $plugin);
    }

    public function testBuildPluginLoadsFullExampleWithOptions()
    {
        $namespace = '\\PHPCI\\Plugin\\Tests\\Util\\';
        $expectedPluginClass = $namespace  . 'ExamplePluginFull';

        $expectedArgs = array(
            'thing' => "stuff"
        );

        $this->registerBuildAndBuilder();

        /** @var ExamplePluginFull $plugin */
        $plugin = $this->testedFactory->buildPlugin(
            $expectedPluginClass,
            $expectedArgs
        );

        $this->assertInternalType('array', $plugin->Options);
        $this->assertArrayHasKey('thing', $plugin->Options);
    }

    /**
     * Registers mocked Builder and Build classes so that realistic plugins
     * can be tested.
     */
    private function registerBuildAndBuilder()
    {
        $this->testedFactory->registerResource(
            function () {
                return $this->getMock(
                    'PHPCI\Builder',
                    array(),
                    array(),
                    '',
                    false
                );
            },
            null,
            'PHPCI\\Builder'
        );

        $this->testedFactory->registerResource(
            function () {
                return $this->getMock(
                    'PHPCI\Model\Build',
                    array(),
                    array(),
                    '',
                    false
                );
            },
            null,
            'PHPCI\\Model\Build'
        );
    }
}
 