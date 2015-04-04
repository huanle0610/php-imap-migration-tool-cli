<?php
require 'ImapUtf7.php';

$imap_utf7 = '[Gmail]/&lj9fJZZAT1s-';
$utf8 =  ImapUtf7::decode($imap_utf7);
$imap_utf7_2 =  ImapUtf7::encode($utf8);

echo $utf8;
echo "\n";
echo $imap_utf7_2;
echo "\n";
echo utf8_decode($utf8);
echo "\n";
echo ImapUtf7::encode('阿弥陀佛');