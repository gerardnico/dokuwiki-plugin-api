<?php


/**
 * Test the endpoint pages of the api plugin
 *
 * @group plugin_api
 * @group plugins
 * @uses \PHPUnit\Framework\TestCase
 */
include_once(__DIR__ . '/utils.php');

class dokuwiki_plugin_api_pages_test extends DokuWikiTest
{

    const ENDPOINT_NAME = 'pages';

    protected $pluginsEnabled = array(action_plugin_api::PLUGIN_NAME);
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
        $conf['useheading'] = 1;

        /**
         * Set things up
         */
        // Create a page
        // The endpoint namespace is important to avoid race condition with other test
        $homePageId = self::ENDPOINT_NAME . "home";
        $summaryDefault = 'Summary';
        saveWikiText($homePageId, 'Home Page', $summaryDefault);
        idx_addPage($homePageId);

        // A second page with a heading to test the titles
        // The endpoint namespace is important to avoid race condition with other test
        $secondPage = self::ENDPOINT_NAME . "PageToHome";
        $secondPageTitle = 'Backlink Page Heading 1';
        saveWikiText($secondPage, '====== ' . $secondPageTitle . '======' . DOKU_LF .
            'Whatever', $summaryDefault);
        idx_addPage($secondPage);


        /**
         * Query
         */
        $queryParameters = array(
            'fn' => self::ENDPOINT_NAME
        );
        $response = dokuwiki_plugin_api_util::getRequest($queryParameters);
        $data = self::$JSON->decode($response->getContent());

        /**
         * Test
         */
        // Two pages
        // Due to concurrent test, we can have more than this two pages ...
        // Wee check that they are in the index
        $pagesFoundCounter = 0;
        $pagesToFound = array($homePageId, $secondPage);
        foreach ($data as $page) {


            // Same Id

            if (in_array($page['id'], $pagesToFound)) {
                $pagesFoundCounter++;
            }

            if ($page['id'] == $homePageId) {
                // Same Title
                $actualPageTitle = $page['title'];
                $expectedTitle = $homePageId;
                $this->assertEquals($expectedTitle, $actualPageTitle, "A page title without header must be the page id");
            }

            if ($page['id'] ==$secondPage) {
                $actualPageTitle = $page['title'];
                $expectedTitle = $secondPageTitle;
                $this->assertEquals($expectedTitle, $actualPageTitle, "A page title with header must be the first heading");
            }

        }
        $this->assertEquals(sizeof($pagesToFound), $pagesFoundCounter, "The two pages were found");


    }

    /**
     * Test the max pages parameters
     */
    public function test_plugin_pages_limit()
    {


        /**
         * Set things up
         */
        // Create 10 pages
        for ($i = 1; $i <= 10; $i++) {
            saveWikiText($i, 'Text for the page ' . $i, "summary for page " . $i);
            idx_addPage($i);
        }

        /**
         * Query
         */
        $limit = 3;
        $queryParameters = array(
            'fn' => 'pages',
            'limit' => $limit
        );
        $response = dokuwiki_plugin_api_util::getRequest($queryParameters);
        $data = self::$JSON->decode($response->getContent());

        /**
         * Test
         */
        // Max pages
        $this->assertEquals($limit, sizeof($data), "The number of page is equal t max");


    }


}
