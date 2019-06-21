<?php


/**
 * Test the api plugin
 *
 * @group plugin_api
 * @group plugins
 * @uses \PHPUnit\Framework\TestCase
 */
include_once (__DIR__.'/utils.php');
class dokuwiki_plugin_api_test extends DokuWikiTest
{

    protected $pluginsEnabled = array(action_plugin_api::PLUGIN_NAME);

    static function setUpBeforeClass()
    {

        dokuwiki_plugin_api_util::init();

    }

    /**
     * Control the plugin.info.txt
     */
    public function test_pluginInfoTxt()
    {

        $info = dokuwiki_plugin_api_util::$PLUGIN_INFO;

        $this->assertArrayHasKey('base', $info);
        $this->assertEquals(action_plugin_api::PLUGIN_NAME, $info['base']);

        $this->assertArrayHasKey('author', $info);
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('desc', $info);

        $this->assertArrayHasKey('date', $info);
        $this->assertRegExp('/^\d\d\d\d-\d\d-\d\d$/', $info['date']);
        $this->assertTrue(false !== strtotime($info['date']));


        $this->assertArrayHasKey('url', $info);
        $this->assertRegExp('/^https?:\/\//', $info['url']);

        $this->assertArrayHasKey('email', $info);
        $this->assertTrue(mail_isvalid($info['email']));


    }

    /**
     * test if the plugin is loaded.
     */
    public function test_plugin_isLoaded()
    {
        global $plugin_controller;
        $this->assertTrue(
            in_array(
                action_plugin_api::PLUGIN_NAME,
                $plugin_controller->getList()),
            action_plugin_api::PLUGIN_NAME . " plugin is loaded"
        );
    }

    /**
     * Test a call without any parameters
     *
     */
    public function test_plugin_base_no_function()
    {

        $expected = array(
            "api" => action_plugin_api::PLUGIN_NAME,
            "version" => dokuwiki_plugin_api_util::$PLUGIN_INFO['date']
        );

        $testResponse = dokuwiki_plugin_api_util::getRequest();
        $actualData = dokuwiki_plugin_api_util::$JSON->decode($testResponse->getContent());
        $this->assertEquals($expected,$actualData,"Information about the API is given");

    }


}
