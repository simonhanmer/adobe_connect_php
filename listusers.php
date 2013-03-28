<?php
/******************************************************************************
 * File: listusers.php
 * Description:
 * Retrieve list of all users within Adobe Connect server and show info. If
 * PGi audio profiles are used, also retrieve profile info for users.
 *
 * Syntax: listusers -h host -u username -p password [-v]
 * Arguments
 *	-h hostname     required
 *	-p password     required
 *	-u username     required
 *	-v              optional
 * 
 * Distributed: Under GPL - See COPYING for info
 * Copyright: 2009-2013 Simon Hanmer (simon.hanmer@gmail.com)
 *
 *****************************************************************************/

require_once("connect_lib.php");

// Parse command line
$options = getopt("h:p:u:v");
$user_email = $argv[$argc-1];


if (!isset($options[h]) || !isset($options[p]) || !isset($options[p])) {
	fprintf(STDERR, $argv[0].": -h host -u username -p password [-v] email_address\n");
	return false;
}

$host = $options[h];
$user = $options[u];
$pass = $options[p];

$debug = isset($option[v]);


// OK, have arguments so let's go

$adobe = new Connect($host, $user, $pass);

$request->action = "common-info";
if (!$adobe->request($request)) return false;

$login->action   = "login";
if (!$adobe->request($login)) return false;


// OK, we're logged in , find the user account for the specified email


$info->action = "principal-list";
$info->{'filter-type'} = "user";
if (!$adobe->request($info)) return false;

foreach ($xml_response->{'principal-list'}->principal as $account) {
	unset($user_profile);

	$profile_list->action = "telephony-profile-list";
	$profile_list->{'principal-id'} = $account->attributes()->{'principal-id'}+0;

	if ($adobe->request($profile_list)) {
		foreach ($profile_list->response->{'telephony-profiles'}->profile as $profile) {
			if (preg_match('/pgi/i', $profile->name) && $profile->attributes()->profile-status == 'enabled') {
				$profile_info->action = 'telephony-profile-info';
				$profile_info->{'profile-id'} = $profile->attributes()->{'profile-id'};

				$user_profile->name	 = $account->name.'';
				$user_profile->email	= $account->email.'';
				$user_profile->provider = $profile->name.'';

				if ($adobe->request($profile_info)) {
					$user_profile->profile 	= $profile_info->response->{'telephony-profile-fields'}->{'profile-name'}.'';
					if (preg_match('/pgi emea/i',$user_profile->provider)) {
						$user_profile->moderator 	= $profile_info->response->{'telephony-profile-fields'}->{'x-tel-premiere-emea-moderator-code'}.'';
						$user_profile->participant 	= $profile_info->response->{'telephony-profile-fields'}->{'x-tel-premiere-emea-participant-code'}.'';
						$user_profile->id			= $profile_info->response->{'telephony-profile-fields'}->{'x-tel-premiere-emea-user-id'}.'';
					} else {
						$user_profile->moderator 	= $profile_info->response->{'telephony-profile-fields'}->{'x-tel-premiere-moderator-code'}.'';
						$user_profile->participant 	= $profile_info->response->{'telephony-profile-fields'}->{'x-tel-premiere-participant-code'}.'';
						$user_profile->id			= $profile_info->response->{'telephony-profile-fields'}->{'x-tel-premiere-user-id'}.'';
					}
				}

				printf("%s,%s,%s,\"%s\",%s,%s,%s\n", $user_profile->name, $user_profile->email, $user_profile->provider, $user_profile->profile,
							$user_profile->id,$user_profile->moderator, $user_profile->participant);


			}
		}
	}

}

return;

?>
