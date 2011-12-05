<?php defined('SYSPATH') or die('No direct script access.');

require_once(dirname(__FILE__).'/mocks/mockmemcache.php');

/**
 * Test case to ensure stability in the Fcache module
 *
 * @package    Kohana_Fcache
 * @author     Bryce Bedwell <bryce@familylink.com>
 * @copyright  (c) 2011 FamilyLink.com
 **/
class Tests_Fcache extends Unittest_TestCase {
	
	private $memcache;
	private $fcache;
	private $id;
	private $read_query;
	private $read_result;
	private $baseline_query;
	private $baseline_result;
	
	public function setUp()
	{
		$this->id = 123456;
		
		$this->memcache = new MockMemcache();
		$this->memcache->addServer('localhost',11211);
		
		$this->fcache = new Fcache($this->id,$this->memcache);
		
		$this->memcache->flush();
		
		$this->read_query = "/".$this->id."/?fields=friends";
		
		$this->read_result = array(
			'data' => array(
				array(
					'name' => 'Johnny Appleseed',
					'id' => 78910
				),
				array(
					'name' => 'Buck Bingham',
					'id' => 13579
				)
			)
		);
		
		$this->baseline_query = "/".$this->id."/?fields=work";
		$this->baseline_result = array(
			array(
				'title' => 'Software Engineer',
				'company' => 'FamilyLink.com'
			)
		);
		
		$this->fcache->set( array('another_table'), $this->baseline_query, $this->baseline_result, 3600 );
	}
	
	public function tearDown()
	{
		$this->memcache->flush();
	}
	
	public function testDoesItSetAndTimeout()
	{
		$this->assertNull( $this->fcache->get($this->read_query) );
		
		$this->fcache->set( array('users'), $this->read_query, $this->read_result, 1 );
		
		$this->assertTrue( is_array($this->fcache->get($this->read_query)) );
		
		sleep(2);
		
		$this->assertNull( $this->fcache->get($this->read_query) );
	}
	
	public function testDoesItInvalidate()
	{
		$this->assertNull( $this->fcache->get($this->read_query) );
		
		$this->fcache->set( array('users'), $this->read_query, $this->read_result, 10 );
		
		$this->assertTrue( is_array($this->fcache->get($this->read_query)) );
		
		$this->fcache->invalidate( array('users') );
		
		$this->assertNull( $this->fcache->get($this->read_query) );
	}
	
	/**
     * @depends testDoesItInvalidate
     */
	public function testDoesItSafelyInvalidate()
	{
		$this->assertTrue( is_array($this->fcache->get($this->baseline_query)) );
	}

} // End McacheTest