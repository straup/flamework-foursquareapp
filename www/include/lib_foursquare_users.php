<?php

	#################################################################

	function foursquare_users_get_by_oauth_token($token){

		$enc_token = AddSlashes($token);

		$sql = "SELECT * FROM FoursquareUsers WHERE oauth_token='{$enc_token}'";
		return db_single(db_fetch($sql));
	}

	#################################################################

	function foursquare_users_get_by_user_id($user_id){

		$enc_id = AddSlashes($user_id);

		$sql = "SELECT * FROM FoursquareUsers WHERE user_id='{$enc_id}'";
		return db_single(db_fetch($sql));
	}

	#################################################################

	function foursquare_users_create_user($user){

		$hash = array();

		foreach ($user as $k => $v){
			$hash[$k] = AddSlashes($v);
		}

		$rsp = db_insert('FoursquareUsers', $hash);

		if (! $rsp['ok']){
			return null;
		}

		# $cache_key = "foursquare_user_{$user['foursquare_id']}";
		# cache_set($cache_key, $user, "cache locally");

		$cache_key = "foursquare_user_{$user['id']}";
		cache_set($cache_key, $user, "cache locally");

		return $user;
	}

	#################################################################

	function foursquare_users_update_user(&$foursquare_user, $update){

		$hash = array();
		
		foreach ($update as $k => $v){
			$hash[$k] = AddSlashes($v);
		}

		$enc_id = AddSlashes($foursquare_user['user_id']);
		$where = "user_id='{$enc_id}'";

		$rsp = db_update('FoursquareUsers', $hash, $where);

		if ($rsp['ok']){

			$foursquare_user = array_merge($foursquare_user, $update);

			# $cache_key = "foursquare_user_{$foursquare_user['foursquare_id']}";
			# cache_unset($cache_key);

			$cache_key = "foursquare_user_{$foursquare_user['user_id']}";
			cache_unset($cache_key);
		}

		return $rsp;
	}

	#################################################################

?>
