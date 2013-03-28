<?php
/******************************************************************************
 * File: roominfo.php
 * Notes:
 * Description:
 * Retrieve info about an Adobe Connect Room
 *
 * Syntax: roominfo -h host -u username -p password [-v] room_url
 * Arguments
 *	-h hostname     required
 *	-p password     required
 *	-u username     required
 *	-v              optional
 * room_url         required - just the final part of the URL
 * 
 * Distributed: Under GPL - See COPYING for info
 * Copyright: 2009-2013 Simon Hanmer (simon.hanmer@gmail.com)
 *
 *****************************************************************************/

require_once("connect_lib.php");

// Parse command line
$options = getopt("h:p:u:v");

if (!isset($options[h]) || !isset($options[p]) || !isset($options[p])) {
	fprintf(STDERR, $argv[0].": -h host -u username -p password [-v]\n");
	return false;
}

$host = $options[h];
$user = $options[u];
$pass = $options[p];

$debug = isset($option[v]);

$url = $argv[$argc-1];


// OK, have arguments so let's go

$adobe = new Connect($host, $user, $pass);

$request->action = "common-info";
if (!$adobe->request($request)) return false;

$login->action   = "login";
if (!$adobe->request($login)) return false;

$room->action = "sco-by-url";
$room->{'url-path'} = $url;
if (!$adobe->request($room)) return false;

print_r($room->response);

if (!($path = room_url($adobe, $room->response->sco->attributes()->{'folder-id'}))) return false;

print "$path\n";


return;

