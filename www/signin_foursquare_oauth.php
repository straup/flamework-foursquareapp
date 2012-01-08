<?php

	include("include/init.php");

	$redir = (get_str('redir')) ? get_str('redir') : '/';

	# Some basic sanity checking like are you already logged in?

	if ($GLOBALS['cfg']['user']['id']){
		header("location: {$redir}");
		exit();
	}

	if (! $GLOBALS['cfg']['enable_feature_signin']){
		$GLOBALS['smarty']->display("page_signin_disabled.txt");
		exit();
	}

	$callback = $GLOBALS['cfg']['abs_root_url'] . $GLOBALS['cfg']['foursquare_oauth_callback'];

	$oauth_key = $GLOBALS['cfg']['foursquare_oauth_key'];
        $oauth_redir = urlencode($callback);

	$url = "https://foursquare.com/oauth2/authenticate?client_id={$oauth_key}&response_type=code&redirect_uri=$oauth_redir";

	header("location: {$url}");
	exit();
?>
