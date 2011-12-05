<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Caches Facebook queries and invalidates 
 * on inserts/updates/deletes.
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
class Kohana_Fcache {
	
	/**
	 * @var  Memcache  Memcache instance
	 */
	private $cache;
	
	/**
	 * @var  array  Configuration details pulled from config file
	 */
	private $config;
	
	/**
	 * @var  string  Unique ID used to cache on user to users basis
	 */
	private $id;
	
	/**
	 * Creates new Fcache object, adds servers from config
	 *
	 * @return  void
	 */
	public function __construct($id = NULL, Memcache &$cache = NULL)
	{
		$this->config = Kohana::$config->load('fcache');
		$this->id = $id;
		
		if($cache)
		{
			$this->cache = $cache;
		}
		else
		{
			$this->cache = new Memcache();
		}
		
		foreach ($this->config['servers'] as $server)
		{
			$this->cache->addServer($server['host'], $server['port']);
		}
	}
	
	/**
	 * Sets query into the cache
	 *
	 * @return  void
	 */
	public function set(array $tables, $args, $result, $lifetime)
	{
		$hash = $this->get_query_key($args);
		
		$result = array(
			"result" => $result,
			"lifetime" => $lifetime,
			"time_set" => time()
		);
		
		$this->cache->set($hash, $this->condense($result), $lifetime);

		foreach($tables as $table)
		{
			$this->add_history($table, $hash);
		}
	}
	
	/**
	 * Returns query from the cache
	 *
	 * @return  array
	 */
	public function get($args)
	{
		$hash = $this->get_query_key($args);
		if( ($result = $this->cache->get($hash)) )
		{
			$result = $this->uncondense($result);

			if( ($result['time_set'] + $result['lifetime']) >= time() AND isset($result['result']) )
			{
				return $result['result'];
			}
		}
		
		return NULL;
	}
	
	/**
	 * Invalidates all queries for a given table
	 *
	 * @return  void
	 */
	public function invalidate(array $tables)
	{
		foreach($tables as $table)
		{
			$history = $this->get_history($table);
			foreach($history as $hash)
			{
				$this->cache->delete($hash);
			}
		
			$this->set_history($table, array());
		}
	}
	
	/**
	 * Creates a hash key for API Arguments
	 *
	 * @return  string
	 */
	private function get_query_key($args)
	{
		$hash = md5($this->id.serialize($args));
		return $hash;
	}
	
	/**
	 * Creates a key for storing cached queries by table
	 *
	 * @return  string
	 */
	private function get_table_key($table)
	{
		$key = $this->id.'FB'.$table;
		return $key;
	}
	
	/**
	 * Condenses API array result into string
	 *
	 * @return  string
	 */
	private function condense($data)
	{
		$result = serialize($data);
		return $result;
	}
	
	/**
	 * Un-condenses condensed string into array
	 *
	 * @return  array
	 */
	private function uncondense($data)
	{
		$result = unserialize($data);
		return $result;
	}
	
	/**
	 * Adds a cached query into history
	 * for later invalidation
	 *
	 * @return  void
	 */
	private function add_history($table, $hash)
	{
		$history = $this->get_history($table);
		$history[] = $hash;
		$this->set_history($table, $history);
	}
	
	/**
	 * Returns the cached history for a given table
	 *
	 * @return  array
	 */
	private function get_history($table)
	{
		$key = $this->get_table_key($table);
		if( ($result = $this->cache->get($key)) )
		{
			return $this->uncondense($result);
		}
		
		return array();
	}
	
	/**
	 * Sets the cached history for a given table
	 *
	 * @return  void
	 */
	private function set_history($table, $history)
	{
		$key = $this->get_table_key($table);
		$this->cache->set($key, $this->condense($history));
	}

} // End Mcache