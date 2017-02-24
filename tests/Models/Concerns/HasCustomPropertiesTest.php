<?php

namespace Spatie\ServerMonitor\Test\Models\Concerns;

use Spatie\ServerMonitor\Test\TestCase;

class HasCustomPropertiesTest extends TestCase
{
    /** @var \Spatie\ServerMonitor\Models\Check */
    protected $check;

    public function setUp()
    {
        parent::setUp();

        $host = $this->createHost('localhost', 65000, ['diskspace']);

        $this->check = $host->checks->first();

        $this->check->custom_properties = [
            'customName' => 'customValue',
            'nested' => [
                'customName' => 'nested customValue',
            ],
        ];

        $this->check->save();
    }

    /** @test */
    public function it_can_determine_if_a_media_item_has_a_custom_property()
    {
        $this->assertTrue($this->check->hasCustomProperty('customName'));
        $this->assertTrue($this->check->hasCustomProperty('nested.customName'));

        $this->assertFalse($this->check->hasCustomProperty('nonExisting'));
        $this->assertFalse($this->check->hasCustomProperty('nested.nonExisting'));
    }

    /** @test */
    public function it_can_get_a_custom_property()
    {
        $this->assertEquals('customValue', $this->check->getCustomProperty('customName'));
        $this->assertEquals('nested customValue', $this->check->getCustomProperty('nested.customName'));

        $this->assertNull($this->check->getCustomProperty('nonExisting'));
        $this->assertNull($this->check->getCustomProperty('nested.nonExisting'));
    }

    /** @test */
    public function it_can_set_a_custom_property()
    {
        $this->check->setCustomProperty('anotherName', 'anotherValue');

        $this->assertEquals('anotherValue', $this->check->getCustomProperty('anotherName'));
        $this->assertEquals('customValue', $this->check->getCustomProperty('customName'));

        $this->check->setCustomProperty('nested.anotherName', 'anotherValue');
        $this->assertEquals('anotherValue', $this->check->getCustomProperty('nested.anotherName'));
    }

    /** @test */
    public function it_can_forget_a_custom_property()
    {
        $this->assertTrue($this->check->hasCustomProperty('customName'));
        $this->assertTrue($this->check->hasCustomProperty('nested.customName'));

        $this->check->forgetCustomProperty('customName');
        $this->check->forgetCustomProperty('nested.customName');

        $this->assertFalse($this->check->hasCustomProperty('customName'));
        $this->assertFalse($this->check->hasCustomProperty('nested.customName'));
    }

    /** @test */
    public function it_returns_a_fallback_if_a_custom_property_isnt_set()
    {
        $this->assertEquals('foo', $this->check->getCustomProperty('imNotHere', 'foo'));
    }
}
