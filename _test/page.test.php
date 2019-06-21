<?php


/**
 * Test the endpoint page of the api plugin
 *
 * @group plugin_api
 * @group plugins
 * @uses \PHPUnit\Framework\TestCase
 */
include_once(__DIR__ . '/utils.php');

class dokuwiki_plugin_api_page_test extends DokuWikiTest
{

    const ENDPOINT_NAME = 'page';

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
    public function test_plugin_page()
    {

        // $conf must not be in a static method
        global $conf;
        // Use heading as title
        $conf['useheading'] = 1;

        /**
         * Set things up
         */
        // Create a page, the endpoint namespace is important to avoid race condition with other test
        $homePageId = self::ENDPOINT_NAME."home";
        $summaryDefault = 'Summary';
        saveWikiText($homePageId, 'Home Page', $summaryDefault);
        idx_addPage($homePageId);

        // Backlinks
        // The endpoint namespace is important to avoid race condition with other test
        $backlinkHomePageId = self::ENDPOINT_NAME."PageWithLinkToHome";
        $externalLink = 'https://gerardnico.com';
        $backlinkHomePageTitle = 'Backlink Page Heading 1';
        saveWikiText($backlinkHomePageId, '====== ' . $backlinkHomePageTitle . '======' . DOKU_LF .
            '[['.$homePageId.']] - [[' . $externalLink . ']]', $summaryDefault);
        idx_addPage($backlinkHomePageId);


        /**
         * Query Home page
         */
        $queryParameters = array(
            'fn' => self::ENDPOINT_NAME,
            'id' => $homePageId
        );
        $response = dokuwiki_plugin_api_util::getRequest($queryParameters);
        $data = self::$JSON->decode($response->getContent());

        /**
         * Test
         */


        // Same Title
        $actualPageTitle = $data['title'];
        $expectedTitle = $homePageId;
        $this->assertEquals($expectedTitle, $actualPageTitle, "A page title without header must be the page id");

        // Html
        $expectedHtml = DOKU_LF . '<p>' . DOKU_LF . 'Home Page' . DOKU_LF . '</p>' . DOKU_LF;
        $actualHtml = $data['html'];
        $this->assertEquals($expectedHtml, $actualHtml, "The Html must be the same");

        // backlinks
        $actualBacklinks = $data['backlinks'];
        $expectedBacklinks = array(
            0 => $backlinkHomePageId
        );
        $this->assertEquals($expectedBacklinks, $actualBacklinks, "The Backlinks must be the same");

        /**
         * Query Second page
         */
        $queryParameters = array(
            'fn' => 'page',
            'id' => $backlinkHomePageId
        );
        $response = dokuwiki_plugin_api_util::getRequest($queryParameters);
        $data = self::$JSON->decode($response->getContent());

        // Title
        $actualPageTitle = $data['title'];
        $expectedTitle = $backlinkHomePageTitle;
        $this->assertEquals($expectedTitle, $actualPageTitle, "A page title with header must be the first heading");

        // internal links
        $actualLinks = $data['links'];
        $expectedLinks = array(
            0 => $homePageId
        );
        $this->assertEquals($expectedLinks, $actualLinks, "The links must be the same");

        // external links
        $actualExternalLinks = $data['external_links'];
        $expectedExternalLinks = array(
            0 => $externalLink
        );
        $this->assertEquals($expectedExternalLinks, $actualExternalLinks, "The externals links must be the same");


    }
    
}
