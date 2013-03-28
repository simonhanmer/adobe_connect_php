<?php
/******************************************************************************
 * File: connect_lib.php
 * Notes:
 *  This files provides the main code used to send requests to an Adobe Connect
 *  Service
 * 
 * Distributed: Under GPL - See COPYING for info
 * Copyright: 2009-2013 Simon Hanmer (simon.hanmer@gmail.com)
 *
 *****************************************************************************/
?>

<?php
class Connect {
function Connect($host, $user, $password) {

	$this->host = $host;
	$this->user = $user;
	$this->password = $password;

}


// ----------------------------------------------------------------------------
//  Function: request
//  Arguments: 
//		$server - initialised instance of class Connect
//		$debug - set to true to show debug info (optional)
//  Return: true or false to reflect success of request.
//
// This function sends the request to the adobe connect service defined in the 
// server instance.
// ----------------------------------------------------------------------------

function request($server, $debug = false) {
	global $xml_response;
    
	if (empty($server->action) || $server->action == '') {
		return false;
	}
        
	$url = $this->host.'/api/xml?action='.$server->action;
        
    if (!empty($this->cookie)) {
        $server->session = $this->cookie;
    }

    if ($server->action == 'login') {
        $server->login = $this->user;
        $server->password = $this->password;
    }

	foreach ($server as $arg => $value) {
	if ($arg == 'action') continue;
		$url .= '&'.$arg.'='.$value;
	}

	if (!empty($this->domain) && $this->domain != $this->host && $server->action == "login") {
		$url .= "&domain=".$this->domain;
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);

	$xml=simplexml_load_string($response);		/* Convert XML tree */
	$xml_response = $xml;

    if ($debug) {
        print "Debug - url: $url \n";
        print_r($server);
        print "----\n";
        print_r($xml_response);
        print "----\n";
        print_r($xml);
        print "====\n\n";
    }

	$status = $xml->status->attributes()->code;

    if ($server->action == "common-info" && status) {
        $this->cookie = (string)$xml_response->common->cookie;
        }

    if ($server->action == "login" && $status) {
        $folder->action = "sco-shortcuts";

        if ($this->request($folder) != "ok" ) {
            return false;
            }

        $this->folders = array();

        foreach ($xml_response->shortcuts->sco as $shortcut) {
            $sco_id = (integer)$shortcut->attributes()->{'sco-id'};
            $type = (string)$shortcut->attributes()->{'type'};
            $this->folders[$type] = $sco_id;
            }
        }

    $server->response = $xml_response;
	return($status = "ok");

}
    

}

// ----------------------------------------------------------------------------
//  Function: room_url
//  Arguments: 
//		$link		initialised instance of class Connect
//		$sco_id		sco_id of Adobe Connect meeting room
//  Return: path to room or false if room cannot be found
//
// Find the path to a room with a specific sco_id
// ----------------------------------------------------------------------------
function room_url($link, $sco_id) {
$folder->action = "sco-nav";
$folder->{'sco-id'} = $sco_id;
if (!$link->request($folder)) return false;

$path = "";
foreach ($folder->response->{'sco-nav'}->sco as $subfolder) {
    $path .= "/".$subfolder->name;
}

return $path;
}

?>

