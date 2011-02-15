<?php
class TweetStream
{
	private $_username;
	private $_password;
	private $_keywords = array();
	private $_apiVersion = 1;
	private $_callback;
	
	public function __construct()
	{
		
	}
	
	public function setCredentials($username, $password)
	{
		Enforce::NotEmptyString($username, 'Username cannot be null or empty');
		Enforce::NotEmptyString($password, 'Password cannot be null or empty');

		$this->_username = $username;
		$this->_password = $password;
	}
	
	public function setCallback($callback)
	{
		Enforce::NotNull($callback, 'Callback cannot be null');
		
		$this->_callback = $callback;
	}
	
	public function track($keywords = null))
	{
		// Users are able to provide keywords as single arguments
		// track(keyword1, keyword2) instead of sending an array
		if($keywords === null)
			$keywords = func_get_args();
			
		Enforce::NotEmptyArray($keywords, 'Keywords cannot be null');
		
		$this->_keywords = $keywords;
	
		$url = $this->getUrl();
		$handle = fopen($url, 'r');
		$callback = $this->_callback;
		
		while($data = fgets($handle))
		{
			$tweet = $this->recieveMessage($data);
			$callback($tweet);
		}
		
		// dispose our handle
		fclose($handle);
	}
	
	protected function getUrl()
	{
		$keywords = implode(',', $this->_keywords);
		return sprintf('http://%s:%s@stream.twitter.com/%d/statuses/filter.json?track=%s',
			$this->_username,
			$this->_password,
			$this->_apiVersion,
			$keywords);
	}
	
	protected function recieveMessage($data)
	{
		return json_decode($data);
	}
}

class Enforce
{
	public static function NotNull($property, $message)
	{
		if($property === null)
			throw new ArgumentNullException($message);
			
		return true;
	}
	
	public static function NotEmptyString($property, $message)
	{
		if(Enforce::NotNull($property, $message) || strlen(trim($property)) === 0)
			throw new ArgumentNullException($message);
			
		return true;
	}
	
	public static function NotEmptyArray($property, $message)
	{
		if(Enforce::NotNull($property, $message) || count($property) === 0)
			throw new ArgumentNullException($message);
			
		return true;
	}
}

class ArgumentNullException extends Exception { }

$streamer = new TweetStream();
$streamer->setCredentials('alexnyquist', 'password');
$streamer->setCallback(function($message) {
	$message = sprintf('%s says: %s', $message->user->screen_name, $message->text);
	file_put_contents('log.txt', $message . PHP_EOL, FILE_APPEND); // Write tweets to file
});
$streamer->track(array('twitter'));
