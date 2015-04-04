<?php
// Leave this value as is
$archivesh = './scripts/archive.sh';

// A list of folders that will be skipped.
// NOTE - If a parent folder is listed, all children folders are skipped too
$folders_skip = array(
    'Protected',
    'Calendar',
    'Contacts',
    'Outbox',
    'Drafts',
    'Notes',
    'Journal',
    'Tasks',
    'Public Folders',
    'Deleted Items',
    'Trash',
    'INBOX.Trash'
);