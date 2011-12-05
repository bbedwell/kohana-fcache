<?php
/**
 * Mock Memcache module
 *
 * @package    Kohana_Fcache
 * @author     Bryce Bedwell <bryce@familylink.com>
 * @copyright  (c) 2011 FamilyLink.com
 **/
if( ! class_exists('Memcache') )
{
	class Memcache {}
}

class MockMemcache extends Memcache {
	
	private $environment;
	
	public function construct() 
	{
		$this->environment = array();
	}
	
	public function get($key)
	{
		$value = @$this->environment[$key];
		
		return $value;
	}
	
	public function set($key,$value)
	{
		$this->environment[$key] = $value;
	}
	
	public function delete($key)
	{
		unset($this->environment[$key]);
	}
	
	public function flush()
	{
		$this->environment = array();
	}
	
	public function addServer($host, $port)
	{
		
	}
	
}