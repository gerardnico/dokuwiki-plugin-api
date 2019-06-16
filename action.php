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
class  action_plugin_restapi extends DokuWiki_Action_Plugin{



    function register( Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE',  $this, '_ajax_call');
    }

    /**
     * handle ajax requests
     */
    function _ajax_call(&$event, $param)
    {
        if ($event->data !== 'restapi') {
            return;
        }
        //no other ajax call handlers needed
        $event->stopPropagation();
        $event->preventDefault();

        global $conf;
        $conf['remote']=true;
        $conf['remoteuser']='@ALL';
        $remote = new RemoteAPI();
        $wikiVersion = $remote->call('dokuwiki.getVersion');

        $wikiTitle = $remote->call('dokuwiki.getTitle');


        $allPages = $remote->call('wiki.getAllPages');

        global $INPUT;
        $pageId =$INPUT->str('pageid');
        $data=array(
            'wikiVersion'=>$wikiVersion,
            'wikiTitle'=>$wikiTitle,

        );
        $data = $allPages;

        require_once DOKU_INC . 'inc/JSON.php';
        $json = new JSON();
        header('Content-Type: application/json');
        if($_GET["callback"]){
            echo $_GET["callback"]."(".$json->encode($data).")";
        }else {
            echo $json->encode($data);
        }
    }


}