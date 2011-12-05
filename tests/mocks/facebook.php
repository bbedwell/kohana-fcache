<?php
/**
 * Mock Facebook class
 *
 * @package    Kohana_Fcache
 * @author     Bryce Bedwell <bryce@familylink.com>
 * @copyright  (c) 2011 FamilyLink.com
 **/
class Facebook {
	
	private $_data;
		
	private $_change_count = 0;
	
	public function __construct()
	{
		$this->reset();
	}
	
	public function api()
	{
		return $this->_data;
	}
	
	public function getUser()
	{
		return 123456;
	}
	
	public function reset()
	{
		$this->_data = array(
			array(
				'name' => 'John',
				'id' => 6789
			)
		);
		
		$this->_change_count = 0;
	}
	
	public function changeData()
	{
		if($this->_change_count % 2 == 0)
		{
			$this->_data = array(
				array(
					'name' => 'Ben',
					'id' => 1357
				)
			);
		}
		else
		{
			$this->_data = array(
				array(
					'name' => 'Sam',
					'id' => 2468
				)
			);
		}
		
		$this->_change_count++;
	}
	
} // End Facebook