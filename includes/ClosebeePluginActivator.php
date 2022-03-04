<?php

    class ClosebeePluginActivator {
        
    	public static function activate($surl) {
    	    add_option('my_plugin_do_activation_redirect', true);
    	}
    	
    }

?>