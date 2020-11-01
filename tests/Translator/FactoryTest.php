<?php
namespace CLSystems\IBMWatson\Translator\Translator\Tests;

use CLSystems\IBMWatson\Translator\Translator\Factory;
use CLSystems\IBMWatson\Translator\Translator\Client as TranslatorClient;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsTranslator()
    {
        $this->assertInstanceOf(
            TranslatorClient::class,
            Factory::getTranslator('username', 'password')
        );
    }
}
