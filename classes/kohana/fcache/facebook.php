<?php defined('SYSPATH') or die('No direct script access.');

if( ! class_exists('Facebook') )
{
	require_once Kohana::find_file('vendor','facebook/src/facebook');
}

/**
 * Wrapper to handle Facebook API calls
 *
 * @package    Kohana_Fcache
 * @author     Bryce Bedwell <bryce@familylink.com>
 * @copyright  FamilyLink.com
 *
 * Copyright (c) 2011 FamilyLink.com
 * 
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 **/
class Kohana_Fcache_Facebook extends Facebook {
	
	/**
	 * @var  array  Array of arguments passed to api query
	 */
	private $args;
	
	/**
	 * @var  array  Array of tables for caching
	 */
	private $tables;
	
	/**
	 * @var  Fcache  Object of Fcache
	 */
	private $cache;
	
	/**
	 * @var  int  Seconds to keep cached item alive
	 */
	private $lifetime;
	
	/**
	 * @var  boolean  To invalidate the cache or not
	 */
	private $invalidate;
	
	/**
	 * Calls parent constructor, instantiates new Fcache object
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->clear();
		
		$args = func_get_args();
		
		if( isset($args[0]) && isset($args[0]['cache']) )
		{
			$cache = new $args[0]['cache'];
			unset($args[0]['cache']);
		}
		else
		{
			$cache = NULL;
		}

		call_user_func_array( array("parent", "__construct"), $args );
		
		$this->cache = new Fcache($this->getUser(), $cache);
	}
	
	/**
	 * 'Resets' the object for a new query
	 *
	 * @return  void
	 */
	private function clear()
	{
		$this->args = array();
		$this->tables = array();
		$this->lifetime = 3600;
		$this->invalidate = FALSE;
	}
	
	/**
	 * Dumps out all setting member variables
	 *
	 * @return  array
	 */
	public function dump()
	{
		$data = array(
			'args' => $this->args,
			'tables' => $this->tables,
			'lifetime' => $this->lifetime,
			'invalidate' => $this->invalidate
		);
		
		return $data;
	}
	
	/**
	 * Load arguments into array
	 *
	 * @return  self
	 */
	public function query()
	{
		$this->args = func_get_args();
		
		return $this;
	}
	
	/**
	 * Executes API call using parent api method. Caches and invalidates calls
	 *
	 * @return  array
	 */
	public function execute($use_cache = TRUE)
	{
		if( $this->args )
		{
			if( !$this->tables )
			{
				$this->tables[] = 'user';
			}
		
			if($use_cache AND !$this->invalidate AND ($result = $this->cache->get($this->args)) !== NULL)
			{
				$this->clear();
				return $result;
			}
		
			$result = call_user_func_array( array("parent", "api"), $this->args );
			
			$this->cache->set($this->tables, $this->args, $result, $this->lifetime);
			
			if($this->invalidate)
			{
				$this->cache->invalidate($this->tables);
			}
		
			$this->clear();
		
			return $result;
		}
	}
	
	/**
	 * Adds table to $this->tables array
	 *
	 * @return  self
	 */
	public function set_table($table)
	{
		if( !in_array($table, $this->tables) )
		{
			$this->tables[] = $table;
		}
		
		return $this;
	}
	
	/**
	 * Sets the lifetime of the query
	 *
	 * @return  self
	 */
	public function set_lifetime($lifetime)
	{
		$this->lifetime = $lifetime;
		
		return $this;
	}
	
	/**
	 * Sets the invalidation status of the query
	 *
	 * @return  self
	 */
	public function invalidate($invalidate = TRUE)
	{
		$this->invalidate = $invalidate;
		
		return $this;
	}
	
} // End Kohana_Fcache_Facebook