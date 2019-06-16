<?php


/**
 * Test the restapi plugin
 *
 * @group plugin_restapi
 * @group plugins
 * @uses \PHPUnit\Framework\TestCase
 */
class dokuwiki_plugin_restapi_test extends DokuWikiTest
{

    protected $pluginsEnabled = array(action_plugin_restapi::PLUGIN_NAME);
    /**
     * @var JSON
     */
    private static $JSON;
    private static $PLUGIN_INFO;

    static function setUpBeforeClass()
    {
        self::$JSON = new JSON(JSON_LOOSE_TYPE);
        $file = __DIR__ . '/../plugin.info.txt';
        self::$PLUGIN_INFO = confToHash($file);

    }

    /**
     * Control the plugin.info.txt
     */
    public function test_pluginInfoTxt()
    {
        $file = __DIR__ . '/../plugin.info.txt';
        $this->assertFileExists($file);

        $info = confToHash($file);

        $this->assertArrayHasKey('base', $info);
        $this->assertEquals(action_plugin_restapi::PLUGIN_NAME, $info['base']);

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
                action_plugin_restapi::PLUGIN_NAME,
                $plugin_controller->getList()),
            action_plugin_restapi::PLUGIN_NAME . " plugin is loaded"
        );
    }

    /**
     * Test a call without any parameters
     *
     */
    public function test_plugin_base_no_function()
    {

        $expected = array(
            "api" => action_plugin_restapi::PLUGIN_NAME,
            "version" => self::$PLUGIN_INFO['date']
        );
        $this->assertContent($expected, self::getRequest());

    }

    /**
     * Test the pages function
     */
    public function test_plugin_base_pages()
    {

        // Create a page
        $pageId = "home";
        $summaryDefault = 'Summary';
        saveWikiText($pageId, 'Home Page', $summaryDefault);
        idx_addPage($pageId);

        $queryParameters = array(
            'fn'=>'pages'
        );
        $response = self::getRequest($queryParameters);
        $data = self::$JSON->decode($response->getContent());

        $this->assertEquals(1, sizeof($data));
        $expectedId=$data[0]['id'];
        $this->assertEquals($expectedId, $pageId);

    }

    /**
     * Utility functions are below
     */
    /**
     * Will check the content of a response against a php array
     * @param $expected - an array of data
     * @param $response - the response
     */
    private function assertContent($expected, $response)
    {
        $this->assertEquals(self::$JSON->encode($expected), $response->getContent());
    }

    /**
     * A wrapper around a call to the rest api
     * @param $queryParams - An array representing the query string
     * @return TestResponse - A response
     */
    private static function getRequest($queryParams=array()):TestResponse {
        $request = new TestRequest();
        $queryParams['call'] = action_plugin_restapi::PLUGIN_NAME;
        $testResponse = $request->get($queryParams, '/lib/exe/ajax.php');
        return $testResponse;

    }


}
