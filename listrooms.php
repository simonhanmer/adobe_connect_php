<?php
/******************************************************************************
 * File: listrooms.php
 * Description:
 * Retrieve list of all rooms within Adobe Connect server and show info. If
 * PGi audio profiles are used, also retrieve profile info for users.
 *
 * Syntax: listrooms -h host -u username -p password [-v]
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

if (!isset($options[h]) || !isset($options[p]) || !isset($options[p])) {
    fprintf(STDERR, $argv[0].": -h host -u username -p password [-v]\n");
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


// OK, we're logged in , so grab a list of all the meetings (this includes templates)
$object->action = "report-bulk-objects";
$object->{'filter-type'} = "meeting";
if (!$adobe->request($object)) return false;

$meeting_list = array();

foreach ($xml_response->{'report-bulk-objects'}[0] as $meeting) {
    unset($room, $profile,$request);

    $room->sco = $meeting->attributes()->{'sco-id'}[0]+0;
    $room->path = room_url($adobe, $room->sco);
    $room->type = $meeting->attributes()->type.'';
    $room->url = $meeting->url.'';
    $room->name = $meeting->name.'';

    $profile->action="acl-field-info";
    $profile->{'acl-id'} = $room->sco;
    $profile->{'field-id'} = 'telephony-profile';
    $profile->{'filter-field-id'} = 'telephony-profile';

    if ($adobe->request($profile)) {
        $room->profileid = $xml_response->{'acl-fields'}->field->value[0] + 0;

        $phone->action = "telephony-profile-info";
        $phone->{'profile-id'} = $room->profileid;
        if ($room->profileid > 0 && $adobe->request($phone)) {
            $room->{'audio-profile-name'}       = $xml_response->{'telephony-profile-fields'}->{'profile-name'};
            if (isset( $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-emea-user-id'}) )
                $room->{'audio-profile-id'}         = $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-emea-user-id'};
            if (isset( $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-user-id'}) )
                $room->{'audio-profile-id'}         = $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-user-id'};
            if (isset( $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-emea-user-id'}) )
                $room->{'audio-profile-moderator'}  = $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-emea-moderator-code'};
            if (isset( $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-user-id'}) )
                $room->{'audio-profile-moderator'}  = $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-moderator-code'};
            if (isset( $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-emea-user-id'}) )
                $room->{'audio-profile-participant'}  = $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-emea-participant-code'};
            if (isset( $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-user-id'}) )
                $room->{'audio-profile-participant'}  = $xml_response->{'telephony-profile-fields'}->{'x-tel-premiere-participant-code'};
        }


    printf('"%s",%s,"%s",%s,%s,%s'."\n", 
            $room->path, $room->profileid, $room->{'audio-profile-name'}, $room->{'audio-profile-id'}, $room->{'audio-profile-moderator'}, $room->{'audio-profile-participant'});

    array_push($meeting_list, $room);
    }

}

return;

?>
