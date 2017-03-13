<?php
function getOAuthCredentialsFile()
{
  // oauth2 creds
  $oauth_creds = 'config/oauth-credentials.json';

  if (file_exists($oauth_creds)) {
    return $oauth_creds;
  }

  return false;
}

function init(){


    //$mng = new MongoDB\Driver\Manager("mongodb://localhost:27017");
	//$bulk = new MongoDB\Driver\BulkWrite();
	//$json = array('_id' => 1, 'token'=>"4/hD3vJEd735bcBsEo7eqpR9ZN6Afu7Q0GGq_lQ_EFMcU",  'description'=>'Youtube Services');
	//$bulk->insert($json);
	//$mng->executeBulkWrite('streaming.tokens', $bulk);

	if (!$oauth_credentials = getOAuthCredentialsFile()) {
	    echo missingOAuth2CredentialsWarning();
	    return;
	}

	$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

	$client = new Google_Client();

	$client->setAuthConfig($oauth_credentials);
	$client->setRedirectUri($redirect_uri);
	$client->addScope("https://www.googleapis.com/auth/youtube");

	// add "?logout" to the URL to remove a token from the session
	/*if (isset($_REQUEST['logout'])) {
	    unset($_SESSION['multi-api-token']);
	}*/


	if (isset($_GET['code'])) {
		var_dump($_GET['code']);
	    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
	    $client->setAccessToken($token);

	    // store in the session also
	    //$_SESSION['multi-api-token'] = $token;
	    $mng = new MongoDB\Driver\Manager("mongodb://localhost:27017");
  		$bulk = new MongoDB\Driver\BulkWrite();
		$bulk->update(['_id'=>1], ['token'=>$token]);
		$mng->executeBulkWrite('streaming.tokens', $bulk);

		//var_dump($token);

	    // redirect back to the example
	    //header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
	}

	$mng = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $query = new MongoDB\Driver\Query([]); 
     
    $rows = $mng->executeQuery("streaming.tokens", $query);
    
    //var_dump($rows);
    $t = "";
    foreach ($rows as $row) {
    	//var_dump($row);
    	$t = $row->token->access_token;
    }

	//var_dump($t);
	if (!empty($t)) {
	    $client->setAccessToken($t);
	    if ($client->isAccessTokenExpired()) {
	    	$client->refreshToken($t);
	    }
	} else {
		//var_dump($tokens);
	    $authUrl = $client->createAuthUrl();
	    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
	    //var_dump($authUrl);
	}

	return $client;
}

?>