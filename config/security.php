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
	if (!headers_sent()) {
	    session_start();
	}

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
	if (isset($_REQUEST['logout'])) {
	    unset($_SESSION['multi-api-token']);
	}

	
	if(isset($_GET['title']) &&  isset($_GET['init_timestamp']) &&  isset($_GET['finish_timestamp'])){
	    $title = $_GET['title'];
	    $init_timestamp = $_GET['init_timestamp'];
	    $finish_timestamp = $_GET['finish_timestamp'];

	}

	if (isset($_GET['code'])) {
	    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
	    $client->setAccessToken($token);

	    // store in the session also
	    $_SESSION['multi-api-token'] = $token;

	    // redirect back to the example
	    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
	}

	// set the access token as part of the client
	if (!empty($_SESSION['multi-api-token'])) {
	    $client->setAccessToken($_SESSION['multi-api-token']);
	    if ($client->isAccessTokenExpired()) {
	        unset($_SESSION['multi-api-token']);
	    }
	} else {
	    $authUrl = $client->createAuthUrl();
	}

	return $client;
}

?>