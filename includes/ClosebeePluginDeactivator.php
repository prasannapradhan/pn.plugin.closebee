<?php

    class ClosebeePluginDeactivator {
    
    	public static function deactivate($surl) {
    	    $post_args = array(
    	        'timeout' => '5',
    	        'redirection' => '5',
    	        'httpversion' => '1.0',
    	        'blocking' => true,
    	        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
    	        'cookies' => array(),
    	        'method'  => 'POST',
    	        'data_format' => 'body'
    	    );
    	    $rdata = array('oc' => $org->code, 'pc' => $profile->code, 'surl' => $surl);
    	    $post_args['body'] = json_encode($rdata);
    	    wp_remote_post('https://api.pearnode.com/closebee/site/plugin/deactivate.php', $post_args);
    	}
    }

?>