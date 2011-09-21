<?php

	include("include/init.php");

	loadlib("http");
	# loadlib("foursquare_users");

	# Some basic sanity checking like are you already logged in?

	if ($GLOBALS['cfg']['user']['id']){
		header("location: {$GLOBALS['cfg']['abs_root_url']}");
		exit();
	}


	if (! $GLOBALS['cfg']['enable_feature_signin']){
		$GLOBALS['smarty']->display("page_signin_disabled.txt");
		exit();
	}

	$code = get_str("code");

	if (! $code){
		error_404();
	}

	$args = array(
		'client_id' => $GLOBALS['cfg']['foursquare_oauth_key'],
		'client_secret' => $GLOBALS['cfg']['foursquare_oauth_secret'],
		'grant_type' => 'authorization_code',
		'redirect_uri' => $GLOBALS['cfg']['foursquare_oauth_callback'],
		'code' => $code,
	);

	$url = 'https://foursquare.com/oauth2/access_token?' . http_build_query($args);

	$rsp = http_get($url);

	if (! $rsp['ok']){

		# do something
		exit();
	}

	$data = json_decode($rsp['body'], 'as hash');

	if (! $data['access_token']){

		# do something
		exit();
	}

	$token = $data['access_token'];

	$foursquare_user = foursquare_users_get_by_access_token($token);

	if ($user_id = $foursquare_user['user_id']){
		$user = users_get_by_id($user_id);
	}

	# If we don't ensure that new users are allowed to create
	# an account (locally).

	else if (! $GLOBALS['cfg']['enable_feature_signup']){
		$GLOBALS['smarty']->display("page_signup_disabled.txt");
		exit();
	}

	# Hello, new user! This part will create entries in two separate
	# databases: Users and TwitterUsers that are joined by the primary
	# key on the Users table.

	else {

		$username = 'FIX ME';
		$foursqure_id = 'FIX ME';

		$password = random_string(32);

		$user = users_create_user(array(
			"username" => $username,
			"email" => "{$username}@donotsend-foursquare.com",
			"password" => $password,
		));

		if (! $user){
			$GLOBALS['error']['dberr_user'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}

		$foursquare_user = foursquare_users_create_user(array(
			'user_id' => $user['id'],
			'access_token' => $token,
			'foursquare_id' => $foursquare_id,
		));

		if (! $foursquare_user){
			$GLOBALS['error']['dberr_foursquareuser'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}
	}

	# Okay, now finish logging the user in (setting cookies, etc.) and
	# redirecting them to some specific page if necessary.

	$redir = (isset($extra['redir'])) ? $extra['redir'] : '';

	login_do_login($user, $redir);
	exit();
?>
