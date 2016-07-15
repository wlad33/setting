<?php

namespace Larapacks\Setting\Tests;

use Illuminate\Database\Eloquent\Model;
use Larapacks\Setting\Contracts\Setting as SettingContract;
use Larapacks\Setting\Facades\Setting;

class SettingTest extends TestCase
{
    public function test_contract_resolution()
    {
        $this->assertInstanceOf(SettingContract::class, app(SettingContract::class));
    }

    public function test_set()
    {
        Setting::set('key', 'value');

        $this->seeInDatabase('settings', [
            'key'   => 'key',
            'value' => serialize('value'),
        ]);
    }

    public function test_set_update()
    {
        Setting::set('key', 'value');

        Setting::set('key', 'updated');

        $this->seeInDatabase('settings', [
            'key'   => 'key',
            'value' => serialize('updated'),
        ]);

        $this->dontSeeInDatabase('settings', [
            'key'   => 'key',
            'value' => serialize('value'),
        ]);
    }

    public function test_get()
    {
        $this->test_set();

        $this->assertEquals('value', Setting::get('key'));
    }

    public function test_get_default()
    {
        $this->assertEquals('default', Setting::get('non-existent-key', 'default'));
    }

    public function test_find()
    {
        $this->test_set();

        $this->assertInstanceOf(Model::class, Setting::find('key'));
        $this->assertNull(Setting::find('test'));
    }

    public function test_has()
    {
        $this->test_set();

        $this->assertTrue(Setting::has('key'));
    }

    public function test_all()
    {
        $settings = [
            'key.1' => 'value',
            'key.2' => 'value',
            'key.3' => 'value',
        ];

        Setting::set($settings);

        $this->assertEquals($settings, Setting::all()->toArray());
    }

    public function test_helper()
    {
        setting()->set('key', 'value');

        $this->assertEquals('value', setting()->get('key'));
    }

    public function test_inject()
    {
        $this->assertInstanceOf(SettingContract::class, app(SettingContract::class));
    }

    public function test_serialization()
    {
        setting()->set('boolean', true);

        $this->assertTrue(setting()->get('boolean'));
    }

    public function test_flip()
    {
        setting()->set('boolean', true);

        setting()->flip('boolean');

        $this->assertFalse(setting()->get('boolean'));

        setting()->flip('boolean');

        $this->assertTrue(setting()->get('boolean'));

        setting()->flip('new-key');

        $this->assertTrue(setting()->get('new-key'));
    }

    public function test_enable()
    {
        setting()->enable('new-key');

        $this->assertTrue(setting()->get('new-key'));
    }

    public function test_disable()
    {
        setting()->disable('new-key');

        $this->assertFalse(setting()->get('new-key'));
    }
}
