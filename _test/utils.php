<?php


/**
 * Static Test Utility Function
 */
class dokuwiki_plugin_api_util
{

    /**
     * @var JSON
     */
    public static $JSON;
    public static $PLUGIN_INFO;

    static function init(){
        self::$JSON = new JSON(JSON_LOOSE_TYPE);
        $file = __DIR__ . '/../plugin.info.txt';
        self::$PLUGIN_INFO = confToHash($file);
    }

    /**
     * A wrapper around a call to the rest api
     * @param $queryParams - An array representing the query string
     * @return TestResponse - A response
     */
    public static function getRequest($queryParams=array()):TestResponse {
        $request = new TestRequest();
        $queryParams['call'] = action_plugin_api::PLUGIN_NAME;
        $testResponse = $request->get($queryParams, '/lib/exe/ajax.php');
        return $testResponse;

    }




}
