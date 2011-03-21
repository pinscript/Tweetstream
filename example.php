<?php
require_once 'tweetstream.php';

$conn = mysql_connect('localhost', 'root', '');
        mysql_select_db('tweetstream');

$streamer = new TweetStream();
$streamer->setCredentials('alexnyquist', 'password');
$streamer->setCallback(function($message) use($conn) {
    if($message != null && strlen($message->text) > 0)
        mysql_query(sprintf('INSERT INTO tweets(user, message) VALUES("%s", "%s")', $message->user->screen_name, $message->text));
});
$streamer->track('kaffe', 'obama');
