<?php
require("./scan_config.php");
require("./ImapUtf7.php");

// need be config
//------------------------------
$src_server = 'mail.abccc.com';
$dest_server = 'mail.abccc.cn';

$src_username = '123@abccc.com';
$src_password = 'abd';

$dest_username = '124@abccc.cn';
$dest_password = 'addd';
//------------------------------

$delete_src_msg = 'false';  // Do you want to remove the messages from the source server?
$inbox_date = 'false';
$folder_date = 'false';

if (empty($dest_username) || empty($dest_password) || empty($src_username) ||
    empty($src_password) || empty($inbox_date) || empty($folder_date) || empty($delete_src_msg)
) {
    echo "src_username = $src_username<BR>\n";
    echo "src_password = $src_password<BR>\n";
    echo "src_server = $src_server<BR>\n";
    echo "dest_username = $dest_username<BR>\n";
    echo "dest_password = $dest_password<BR>\n";
    echo "dest_server = $dest_server<BR>\n";
    echo "folder_date = $folder_date<BR>\n";
    echo "inbox_date = $inbox_date<BR>\n";
    echo "delete_src_msg = $delete_src_msg<BR>\n";
    exit("Please fill out all information.");
}


//  Archive Inbox messages older than  $inbox_date_weeks  weeks
if ($inbox_date == "true" && empty($inbox_date_weeks)) {
    echo "You must enter a number of weeks for moving messages in INBOX<BR>\n";
    exit("Please fill out all information.");
}

// Archive messages in your IMAP folders older than $folder_date_weeks weeks
if ($folder_date == "true" && empty($folder_date_weeks)) {
    echo "You must enter a number of weeks for moving messages in IMAP folders<BR>\n";
    exit("Please fill out all information.");
}

// Set up some vars
if (empty($time)) $time = "2am";

$script_header = "#!/bin/sh\n\n";

if ($inbox_date == "false") $inbox_date_weeks = -1;
if ($folder_date == "false") $folder_date_weeks = -1;

// This sets up the call to the work horse script with all the required parameters
function getScriptCall($folder_name, $folder_date_weeks, $delete_src_msg)
{
    global $src_server, $src_username, $src_password;
    global $dest_server, $dest_username, $dest_password;
    $script_call = "./archive.php $src_server $src_username \"$src_password\" $dest_server $dest_username \"$dest_password\" \"$folder_name\" $folder_date_weeks \"$delete_src_msg\"\n";
    return $script_call;
}

/*
    Script logic begins here
*/

// Prep the shell script
if (empty($archivesh)) {
    $archivesh = "./scripts/archive.sh";
}
$there = file_exists($archivesh);
$empty = (filesize($archivesh) == 0) ? true : false;
$fp = fopen($archivesh, "a-");
if (!$there || $empty) {
    fwrite($fp, $script_header);
}

$src_imap_string = sprintf('{%s%s}', $src_server, ':993/imap/ssl/novalidate-cert');

// Get list of mailboxes from src_server for $username
$src_mbox = imap_open($src_imap_string, "$src_username", "$src_password")
or
die("can't connect: " . imap_last_error());
// TODO - provide the user with a checkbox for only archiving subscribed folders
//         array imap_listsubscribed(int imap_stream, string ref, string pattern)
//                                       $src_mbox     \{$src_server}   *
//         (Maybe imap_lsub() or imap_getsubscribed())

//$new_folder_name =  ImapUtf7::encode($src_imap_string . 'INBOX.阿弥陀佛-阿弥陀佛');

$list = imap_list($src_mbox, $src_imap_string, "*");
if (is_array($list)) {
    reset($list);
} else {
    echo "imap_listmailbox failed: " . imap_last_error() . "\n";
}

while (list($key, $val) = each($list)) {
    $skipthis = false;
    $mailbox = ImapUtf7::decode($val);
    $fullmailbox = $mailbox;
    $mailbox = str_replace($src_imap_string, '', $mailbox);

    // Skip UNIX hidden files
    if (trim($mailbox) === '.') {
        continue;
    }

    // mailboxes to be skipped skipped here.
    foreach ($folders_skip as $skip) {
        if ($mailbox == $skip){
            $skipthis = true;
        }
        if (preg_match("/^$skip/", $mailbox)){
            $skipthis = true;
        }
    }

    // Set up $script_call and script it to the archive.sh shell script
    if (!$skipthis) {
        $script_call = getScriptCall($mailbox, ($mailbox == "INBOX") ? $inbox_date_weeks : $folder_date_weeks, $delete_src_msg);
        fwrite($fp, $script_call);
    }
    $skipthis = false;
}

fwrite($fp, "\n");
fclose($fp);
if (!chmod($archivesh, 0755)) {
    echo "\n\n<BR><BR>WARNING: $archivesh not chmoded!!<BR>";
}

//End of script