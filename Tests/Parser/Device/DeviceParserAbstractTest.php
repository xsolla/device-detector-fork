<?php

/**
 * Device Detector - The Universal Device Detection library for parsing User Agents
 *
 * @link https://matomo.org
 *
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */

namespace DeviceDetector\Tests\Parser\Device;

use DeviceDetector\Parser\Device\AbstractDeviceParser;
use PHPUnit\Framework\TestCase;

class DeviceParserAbstractTest extends TestCase
{
    public function testGetAvailableDeviceTypes()
    {
        $available = AbstractDeviceParser::getAvailableDeviceTypes();
        $this->assertGreaterThan(5, \count($available));
        $this->assertContains('desktop', \array_keys($available));
    }

    public function testGetAvailableDeviceTypeNames()
    {
        $available = AbstractDeviceParser::getAvailableDeviceTypeNames();
        $this->assertGreaterThan(5, \count($available));
        $this->assertContains('desktop', $available);
    }

    public function testGetFullName()
    {
        $this->assertEquals('', AbstractDeviceParser::getFullName('Invalid'));
        $this->assertEquals('Asus', AbstractDeviceParser::getFullName('AU'));
        $this->assertEquals('Google', AbstractDeviceParser::getFullName('GO'));
    }
}
