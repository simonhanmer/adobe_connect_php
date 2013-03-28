<?php
/* ---------------------------------------------------------------------
 Script: room_list.php
 Author: simon.hanmer@gmail.com

 Description:
 Retrieve list of all rooms in Adobe Connect and associated details.

 Arguments
    -h hostname     required
    -p password     required
    -u username     required
    -v              optional

   ------------------------------------------------------------------ */

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
$info->{'filter-login'} = $user_email;
if (!$adobe->request($info)) {
    fprintf(stderr, "Cannot find user with email %s\n", $user_email);
    return false;
} else {
    // OK, we have a user profile - fild the audio profiles
    printf("User %s (%s)\n", $info->response->{'principal-list'}->principal->name,
                            $info->response->{'principal-list'}->principal->attributes()->{'principal-id'});

    $profile_list->action = "telephony-profile-list";
    $profile_list->{'principal-id'} = $info->response->{'principal-list'}->principal->attributes()->{'principal-id'}+0;
    if ($adobe->request($profile_list)) {
        foreach ($profile_list->response->{'telephony-profiles'}->profile as $profile) {
        
            $profile_info->action = 'telephony-profile-info';
            $profile_info->{'profile-id'} = $profile->attributes()->{'profile-id'};
            if ($adobe->request($profile_info)) {
                printf("\t%s\t%s\n", 
                    $profile_info->response->{'telephony-profile-fields'}->{'x-tel-premiere-user-id'},
                    $profile_info->response->{'telephony-profile-fields'}->{'x-tel-premiere-moderator-code'});
            }
        }
    } else {
        printf("No audio profiles allocated for %s (%s)", $info->response->{'principal-list'}->principal->name,
                            $info->response->{'principal-list'}->principal->attributes()->{'principal-id'});
    }
}

return;

?>
