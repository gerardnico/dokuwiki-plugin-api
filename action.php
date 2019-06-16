<?php

if (!defined('DOKU_INC')) die();

/**
 * Class action_plugin_restapi
 * Implements a rest api wrapper around XML rpc
 *
 * https://www.dokuwiki.org/devel:xmlrpc
 *
 * Test:
 * http://localhost:81/lib/exe/ajax.php?call=restapi
 *
 * @see RemoteAPI for the entry point
 * @see RemoteAPICore for the implementation of each functions
 */
class  action_plugin_restapi extends DokuWiki_Action_Plugin
{

    const PLUGIN_NAME = 'restapi';

    function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, '_ajax_call');
    }

    /**
     * handle ajax requests
     * @param $event Doku_Event
     */
    function _ajax_call(&$event)
    {
        $info = confToHash(__DIR__ . '/plugin.info.txt');

        if ($event->data !== self::getPluginName()) {
            return;
        }
        //no other ajax call handlers needed
        $event->stopPropagation();
        $event->preventDefault();

        global $conf;
        $conf['remote'] = true;
        $conf['remoteuser'] = '@ALL';
        $response_code = 200;

        global $INPUT;
        $fn = $INPUT->str('fn');

        $remote = new RemoteAPI();
        switch ($fn) {
            case '':
                $data = array(
                    "api" => self::PLUGIN_NAME,
                    "version" => $info['date']
                );
                break;
            case 'version':
                $wikiVersion = $remote->call('dokuwiki.getVersion');
                $rpcVersion = $remote->call('wiki.getRPCVersionSupported');
                $restApiVersion = $info['date'];
                $data = array(
                    'wiki' => $wikiVersion,
                    'restapi' => $restApiVersion,
                    'rpc' => $rpcVersion,
                );
                break;
            case 'wiki':
                $wikiTitle = $remote->call('dokuwiki.getTitle');
                $wikiVersion = $remote->call('dokuwiki.getVersion');
                $data = array(
                    'version' => $wikiVersion,
                    'title' => $wikiTitle,
                );
                break;
            case 'pages':
                $allPages = $remote->call('wiki.getAllPages');
                $data = array();
                foreach ($allPages as $pages) {
                    $pageData = array();
                    $pageData['id'] = $pages['id'];
                    $pageData['title']=tpl_pagetitle($pages['id'], true);
                    $pageData['html'] = $remote->call('wiki.getPageHTML', array("pagename" => $pages['id']));
                    $pageData['backlinks'] = $remote->call('wiki.getBackLinks', array("pagename" => $pages['id']));
                    $allLinks = $remote->call('wiki.listLinks', array("pagename" => $pages['id']));
                    $links=array();
                    $externalLinks=array();
                    foreach ($allLinks as $link){
                        if ($link['type']=='local'){
                            $links[]=$link['page'];
                        } else {
                            $externalLinks[]=$link['href'];
                        }
                    }
                    $pageData['links']=$links;
                    $pageData['external_links']=$externalLinks;
                    $data[] = $pageData;
                }
                break;
            default:
                $data = 'Function (' . $fn . ') was not found';
                $response_code = 404;
        }


        // Return
        require_once DOKU_INC . 'inc/JSON.php';
        $json = new JSON();
        header('Content-Type: application/json');
        http_response_code($response_code);
        if ($_GET["callback"]) {
            echo $_GET["callback"] . "(" . $json->encode($data) . ")";
        } else {
            echo $json->encode($data);
        }
    }


}