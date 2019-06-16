<?php


/**
 * Test the restapi plugin
 *
 * @group plugin_restapi
 * @group plugins
 * @uses \PHPUnit\Framework\TestCase
 */
include_once (__DIR__ . '/utils.php');

class dokuwiki_plugin_restapi_pages_test extends DokuWikiTest
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
     * Test the pages function
     */
    public function test_plugin_pages()
    {

        // $conf must not be in a static method
        global $conf;
        // Use heading as title
        $conf['useheading']=1;

        /**
         * Set things up
         */
        // Create a page
        $homePageId = "home";
        $summaryDefault = 'Summary';
        saveWikiText($homePageId, 'Home Page', $summaryDefault);
        idx_addPage($homePageId);

        // A second page with a heading to test the titles
        $secondPage = "PageToHome";
        $secondPageTitle = 'Backlink Page Heading 1';
        saveWikiText($secondPage, '====== '.$secondPageTitle.'======'.DOKU_LF.
            'Whatever', $summaryDefault);
        idx_addPage($secondPage);


        /**
         * Query
         */
        $queryParameters = array(
            'fn'=>'pages'
        );
        $response = dokuwiki_plugin_restapi_util::getRequest($queryParameters);
        $data = self::$JSON->decode($response->getContent());

        /**
         * Test
         */
        // Two pages
        $this->assertEquals(2, sizeof($data),"The number of page is correct");

        // Same Id
        $actualPageId=$data[0]['id'];
        $this->assertEquals($homePageId, $actualPageId, "The page id must be the same");

        // Same Title
        $actualPageTitle=$data[0]['title'];
        $expectedTitle = $homePageId;
        $this->assertEquals($expectedTitle, $actualPageTitle, "A page title without header must be the page id");
        $actualPageTitle=$data[1]['title'];
        $expectedTitle = $secondPageTitle;
        $this->assertEquals($expectedTitle, $actualPageTitle, "A page title with header must be the first heading");



    }






}
