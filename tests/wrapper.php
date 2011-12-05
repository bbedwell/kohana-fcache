<?php

require_once(dirname(__FILE__).'/mocks/facebook.php');
require_once(dirname(__FILE__).'/mocks/mockmemcache.php');

/**
 * Test case to ensure stability in the Fcache_Facebook wrapper
 *
 * @package    Kohana_Fcache
 * @author     Bryce Bedwell <bryce@familylink.com>
 * @copyright  (c) 2011 FamilyLink.com
 **/
class Tests_Wrapper extends Unittest_TestCase {
	
	private $_facebook;
	
	public function setUp()
	{
		$this->_facebook = new Fcache_Facebook( array('cache' => 'MockMemcache') );
	}
	
	public function tearDown()
	{
		
	}
	
	public function testDoQueryMethodsWork()
	{
		$data = $this->_facebook;
		
		// Test query method
		
		$this->assertTrue( method_exists($data,'query') );
		
		$data = $data->query("/123456?fields=name");
		
		$dump = $data->dump();
		
		$this->assertEquals( $dump['args'][0], '/123456?fields=name' );
		
		// Test invalidate method
		
		$this->assertTrue( method_exists($data,'invalidate') );
		
		$data = $data->invalidate(TRUE);
		
		$dump = $data->dump();
		
		$this->assertEquals( $dump['invalidate'], TRUE );
		
		// Test set_table method

		$this->assertTrue( method_exists($data,'set_table') );
		
		$data = $data->set_table('users');
		
		$dump = $data->dump();
		
		$this->assertEquals( $dump['tables'], array('users') );
		
		$data = $data->set_table('another_table');
		
		$dump = $data->dump();
		
		$this->assertEquals( $dump['tables'], array('users','another_table') );
		
		// Test set_lifetime method
		
		$this->assertTrue( method_exists($data,'set_lifetime') );
		
		$data = $data->set_lifetime(30);
		
		$dump = $data->dump();
		
		$this->assertEquals( $dump['lifetime'], 30 );
		
		// Test execute method
		
		$this->assertTrue( method_exists($data,'execute') );
		
		$return = $data->execute();
		
		$this->assertTrue( isset($return[0]['name']) );
		
		$this->assertTrue( isset($return[0]['id']) );
		
		// Test that it clears
		
		$dump = $data->dump();
		
		$this->assertEquals( $dump['args'], array() );
		
		$this->assertEquals( $dump['tables'], array() );
		
		$this->assertEquals( $dump['lifetime'], 3600 );
		
		$this->assertEquals( $dump['invalidate'], FALSE );
	}
	
	public function testDoesItOnlyInvalidateOnInvalidate()
	{
		$data = $this->_facebook->query("/123456?fields=friends")->set_table('user')->execute();
		
		$this->assertTrue( is_array($data) );
		
		$this->_facebook->changeData();
		
		$data2 = $this->_facebook->query("/123456?fields=friends")->set_table('user')->execute();
		
		$this->assertEquals( $data, $data2 );
		
		$data3 = $this->_facebook->query("/123456?fields=friends")->set_table('user')->invalidate()->execute();
		
		$this->assertNotEquals( $data, $data3 );
	}
	
	public function testCanItExecuteWithoutCaching()
	{
		$data = $this->_facebook->query("/123456?fields=friends")->set_table('user')->execute();
		
		$this->assertTrue( is_array($data) );
		
		$this->_facebook->changeData();
		
		$data2 = $this->_facebook->query("/123456?fields=friends")->set_table('user')->execute();
		
		$this->assertEquals( $data, $data2 );
		
		$data3 = $this->_facebook->query("/123456?fields=friends")->set_table('user')->execute(FALSE);
		
		$this->assertNotEquals( $data, $data3 );
	}
	
	public function testItDoesntInvalidateAll()
	{
		$this->_facebook->reset();
		
		$data = $this->_facebook->query("/123456?fields=friends")->set_table('user1')->execute();
		
		$this->assertTrue( is_array($data) );
		
		$this->_facebook->changeData();
		
		$data2 = $this->_facebook->query("/123456?fields=friends")->set_table('user2')->execute(FALSE);
		
		$this->assertTrue( is_array($data) );
		
		$this->assertNotEquals( $data, $data2 );
		
		$this->_facebook->changeData();
		
		$data3 = $this->_facebook->query("/123456?fields=friends")->set_table('user2')->invalidate()->execute();
		
		$this->assertTrue( is_array($data) );
		
		$this->assertNotEquals( $data2, $data3 );
		
		$data4 = $this->_facebook->query("/123456?fields=friends")->set_table('user')->execute();
		
		$this->assertTrue( is_array($data) );
		
		$this->assertNotEquals( $data, $data4 );
	}
	
}