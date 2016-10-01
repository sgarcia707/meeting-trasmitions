<?php 
include_once 'vendor/autoload.php';
include_once "config/security.php";

class Brodcasting {

	private $client;

    function __construct() {
		$this->client = new Google_Client();

        if (!headers_sent()) {
        	session_start();
        }

	    if (!$oauth_credentials = getOAuthCredentialsFile()) {
	        echo missingOAuth2CredentialsWarning();
	        return;
	    }

	    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	    $this->client->setAuthConfig($oauth_credentials);
	    $this->client->setRedirectUri($redirect_uri);
	    $this->client->addScope("https://www.googleapis.com/auth/youtube");

	    if (isset($_REQUEST['logout'])) {
	        unset($_SESSION['multi-api-token']);
	    }

	    if (isset($_GET['code'])) {
	        $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
	        $this->client->setAccessToken($token);
	        $_SESSION['multi-api-token'] = $token;
	        header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
	    }

	    if (!empty($_SESSION['multi-api-token'])) {
	        $this->client->setAccessToken($_SESSION['multi-api-token']);
	        if ($this->client->isAccessTokenExpired()) {
	            unset($_SESSION['multi-api-token']);
	        }
	    } else {
	        $authUrl = $this->client->createAuthUrl();
	    }
   }

   function createBrodcasting(){
   		$title = "";
		$init_timestamp = "";
		$finish_timestamp = "";


		if(isset($_GET['title']) &&  isset($_GET['init_timestamp']) &&  isset($_GET['finish_timestamp'])){
			        $title = $_GET['title'];
			        $init_timestamp = $_GET['init_timestamp'];
			        $finish_timestamp = $_GET['finish_timestamp'];
	    }

	    if ($this->client->getAccessToken()) {


		    $youtube = new Google_Service_YouTube($this->client);

		    try {
		        $broadcastSnippet = new Google_Service_YouTube_LiveBroadcastSnippet();
		        $broadcastSnippet->setTitle($title);
		        //$broadcastSnippet->setScheduledStartTime('2016-10-15T14:00:00.000Z');
		        $broadcastSnippet->setScheduledStartTime($init_timestamp);
		        //$broadcastSnippet->setScheduledEndTime('2016-10-15T14:05:00.000Z');
		        $broadcastSnippet->setScheduledEndTime($finish_timestamp);

		        $status = new Google_Service_YouTube_LiveBroadcastStatus();
		        $status->setPrivacyStatus('unlisted');
		        $status->setLifeCycleStatus("live");

		        $broadcastInsert = new Google_Service_YouTube_LiveBroadcast();
		        $broadcastInsert->setSnippet($broadcastSnippet);
		        $broadcastInsert->setStatus($status);
		        $broadcastInsert->setKind('youtube#liveBroadcast');

		        $broadcastsResponse = $youtube->liveBroadcasts->insert('snippet,status',
		            														$broadcastInsert, array());

		        $streamSnippet = new Google_Service_YouTube_LiveStreamSnippet();
		        $streamSnippet->setTitle($title);

		        $cdn = new Google_Service_YouTube_CdnSettings();
		        $cdn->setFormat("240p");
		        $cdn->setIngestionType('rtmp');

		        $streamInsert = new Google_Service_YouTube_LiveStream();
		        $streamInsert->setSnippet($streamSnippet);
		        $streamInsert->setCdn($cdn);
		        $streamInsert->setKind('youtube#liveStream');

		        $streamsResponse = $youtube->liveStreams->insert('snippet,cdn',
		            $streamInsert, array());

		        $bindBroadcastResponse = $youtube->liveBroadcasts->bind(
		            $broadcastsResponse['id'],'id,contentDetails',
		            array(
		                'streamId' => $streamsResponse['id'],
		            ));

		        $streamsResponse = $youtube->liveStreams->listLiveStreams('id,snippet,cdn', array(
		            'mine' => 'true',
		        ));

		        $streaming_name = "";
		        $broadcast_id = "";

		        foreach ($streamsResponse['items'] as $streamItem) {
		                if($bindBroadcastResponse['contentDetails']['boundStreamId'] == $streamItem['id']){
		                    $streaming_name = $streamItem["cdn"]["ingestionInfo"]["streamName"];
		                    $broadcast_id = $streamItem["id"];
		                    break;
		                }
		        }


		        $array = array("id_stream"=>$broadcastsResponse['id'], "title"=>$broadcastsResponse['snippet']['title'], "published"=> $broadcastsResponse['snippet']['publishedAt'],
		            "url"=>"https://www.youtube.com/my_live_events?camera_tab=0&event_id=". $bindBroadcastResponse['id'] ."&action_edit_live_event_stream=1", "streaming_name"=>$streaming_name, "broadcast_id"=>$broadcast_id );


		    } catch (Google_Service_Exception $e) {
		      $gServiceError = array("message"=> $e.getMessage(), "code"=>"500");
		        header('Content-Type: application/json');
		        http_response_code(500);
		        echo json_encode($gServiceError);
		    } catch (Google_Exception $e) {
		        $eServiceError = array("message"=> $e.getMessage(), "code"=>"500");
		        header('Content-Type: application/json');
		        http_response_code(500);
		        echo json_encode($eServiceError);
		    }

		    $_SESSION['token'] = $this->client->getAccessToken();

		    header('Content-Type: application/json');
		    http_response_code(200);

		    echo json_encode($array);
		} else {
		    $exceptionError = array("message"=> "Token not fund: " . $this->client->createAuthUrl(), "code"=>"500");
		    header('Content-Type: application/json');
		    http_response_code(500);
		    echo json_encode($exceptionError);
		}
   }

   function changeStatus(){
	   if(isset($_GET['id']) && isset($_GET['status'])){
		   $id = $_GET['id'];
		   $status = $_GET['status'];

	   }

   		if ($this->client->getAccessToken()) {
		    $youtube = new Google_Service_YouTube($this->client);
		    try{
		        $broadcastsResponse = $youtube->liveBroadcasts->transition($status, $id, "id, contentDetails, status");
		        header('Content-Type: application/json');
		        http_response_code(200);
		        $responseOk = array("status"=>$status, "id"=>$id);
		        echo(json_encode($responseOk));
		    }catch (Google_Service_Exception $e) {
		        $gServiceError = array("message"=> $e->getErrors()[0]["message"], "code"=>"500");
		        header('Content-Type: application/json');
		        http_response_code(500);
		        echo(json_encode($gServiceError));
		    } catch (Google_Exception $e) {
		        $gServiceError = array("message"=> "Error: transactions is inactive", "code"=>"500");
		        header('Content-Type: application/json');
		        http_response_code(500);
		        echo(json_encode($gServiceError));
		    }catch(Exception $e){
		        $gServiceError = array("message"=> "Error: transactions is inactive", "code"=>"500");
		        http_response_code(500);
		        echo(json_encode($gServiceError));
		    }

		} else {
		    $exceptionError = array("message"=> "Token not fund: " . $this->client->createAuthUrl(), "code"=>"500");
		    header('Content-Type: application/json');
		    http_response_code(500);
		    echo json_encode($exceptionError);
		}
   }

   function listBroadcast(){
   	
   	$youtube = new Google_Service_YouTube($this->client);

   	if ($this->client->getAccessToken()) {

		  try {
			   $brodcastResponse = $youtube->liveBroadcasts->listLiveBroadcasts('contentDetails, status, snippet, id', array(
					  'mine' => 'true'
				));

			   $list = array();

			   foreach ($brodcastResponse as $broadcastItem) {
				   	$streamsResponse = $youtube->liveStreams->listLiveStreams('id,cdn', array(
			            'mine'=> 'true', 'id' => $brodcastResponse['contentDetails']['boundStreamId'],
			        ))[0];

		        $streaming_name = $streamsResponse["cdn"]["ingestionInfo"]["streamName"];
		        $broadcast_id = $streamsResponse["id"];
			    	
			      $row = array("id_stream"=>$broadcastItem["id"], "title"=>$broadcastItem['snippet']['title'], "published"=> $broadcastItem['snippet']['publishedAt'],
				            "url"=>"https://www.youtube.com/my_live_events?camera_tab=0&event_id=". $broadcastItem["id"] ."&action_edit_live_event_stream=1", "streaming_name"=>$streaming_name, "broadcast_id"=>$broadcastItem['contentDetails']['boundStreamId'], "status"=> $broadcastItem['status']['lifeCycleStatus']);
			      array_push($list, $row);
			   }

		    http_response_code(200);
	        echo(json_encode($list));

		  } catch (Google_Service_Exception $e) {
	    	$gServiceError = array("message"=> $e->getErrors()[0]["message"], "code"=>"500");
	        header('Content-Type: application/json');
	        http_response_code(500);
	        echo(json_encode($gServiceError));
		  } catch (Google_Exception $e) {
	    	$gServiceError = array("message"=> "Error: get transactions error", "code"=>"500");
	        header('Content-Type: application/json');
	        http_response_code(500);
	        echo(json_encode($gServiceError));
		  }

		  $_SESSION['token'] = $this->client->getAccessToken();
		} else {
			$exceptionError = array("message"=> "Token not fund: " . $this->client->createAuthUrl(), "code"=>"500");
		    header('Content-Type: application/json');
		    http_response_code(500);
		    echo json_encode($exceptionError);
		}
   }

   function getBroadcast(){
   	$youtube = new Google_Service_YouTube($this->client);

   	if(isset($_GET['id'])){
		   $id = $_GET['id'];
	 }
   	if ($this->client->getAccessToken()) {

		  try {
		    /*$streamsResponse = $youtube->liveStreams->listLiveStreams('id,snippet,cdn,status', array(
		        'id' => $id
		    ))[0];*/

				$brodcastResponse = $youtube->liveBroadcasts->listLiveBroadcasts('contentDetails, status, snippet, id', array(
					  'id' => $id
				))[0];

			  	$streamsResponse = $youtube->liveStreams->listLiveStreams('id,cdn', array(
		            'id' => $brodcastResponse['contentDetails']['boundStreamId'],
		        ))[0];

		        $streaming_name = $streamsResponse["cdn"]["ingestionInfo"]["streamName"];
		        $broadcast_id = $streamsResponse["id"];
			  
			    $broadcast = array("id_stream"=>$brodcastResponse["id"], "title"=>$brodcastResponse['snippet']['title'], "published"=> $brodcastResponse['snippet']['publishedAt'],
			            "url"=>"https://www.youtube.com/my_live_events?camera_tab=0&event_id=". $brodcastResponse["id"] ."&action_edit_live_event_stream=1", "streaming_name"=>$streaming_name, "broadcast_id"=>$brodcastResponse['contentDetails']['boundStreamId'], "status"=> $brodcastResponse['status']['lifeCycleStatus']);

			    http_response_code(200);
		        echo(json_encode($broadcast));

		  } catch (Google_Service_Exception $e) {
	    	$gServiceError = array("message"=> $e->getErrors()[0]["message"], "code"=>"500");
	        header('Content-Type: application/json');
	        http_response_code(500);
	        echo(json_encode($gServiceError));
		  } catch (Google_Exception $e) {
	    	$gServiceError = array("message"=> "Error: get transactions error", "code"=>"500");
	        header('Content-Type: application/json');
	        http_response_code(500);
	        echo(json_encode($gServiceError));
		  }

		  $_SESSION['token'] = $this->client->getAccessToken();
		} else {
			$exceptionError = array("message"=> "Token not fund: " . $this->client->createAuthUrl(), "code"=>"500");
		    header('Content-Type: application/json');
		    http_response_code(500);
		    echo json_encode($exceptionError);
		}
   }

   function getStratus(){

   	 	$youtube = new Google_Service_YouTube($this->client);

   	 	if(isset($_GET['id'])){
			$id = $_GET['id'];
	 	}

	 	
	 	if ($this->client->getAccessToken()) {
		  try {

		  	$streamsResponse = $youtube->liveStreams->listLiveStreams('status', array('id' => $id ))[0];

		  	$streaming = array('id_stream' =>$id , "status"=> $streamsResponse['status']['healthStatus']['status']);

			http_response_code(200);
		    echo(json_encode($streaming));

		  }catch (Google_Service_Exception $e) {
	    	$gServiceError = array("message"=> $e->getErrors()[0]["message"], "code"=>"500");
	        header('Content-Type: application/json');
	        http_response_code(500);
	        echo(json_encode($gServiceError));
		  } catch (Google_Exception $e) {
	    	$gServiceError = array("message"=> "Error: get transactions error", "code"=>"500");
	        header('Content-Type: application/json');
	        http_response_code(500);
	        echo(json_encode($gServiceError));
		  }

	   } else {
				$exceptionError = array("message"=> "Token not fund: " . $this->client->createAuthUrl(), "code"=>"500");
			    header('Content-Type: application/json');
			    http_response_code(500);
			    echo json_encode($exceptionError);
		}
	} 
}
?> 