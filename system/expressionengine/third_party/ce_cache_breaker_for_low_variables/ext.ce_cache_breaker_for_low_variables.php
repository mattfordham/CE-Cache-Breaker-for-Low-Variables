<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache Breaker for Low Variables Extension
 *
 * @package		ce_cache_breaker_for_low_variables
 * @category	Extension
 * @author		Matt Fordham
 * @link		http://www.revolvercreative.com
 * @copyright Copyright (c) 2012, Matt Fordham
 */

class Ce_cache_breaker_for_low_variables_ext {
	
	public $settings 			= array();
	public $default_settings 	= array(
  		'refresh' => 0, 
		'refresh_time' => 0
	);
	public $description			= 'Allows for cache breaking in CE Cache when Low Variables are updated';
	public $docs_url			= 'https://github.com/mattfordham/CE-Cache-Breaker-for-Low-Variables';
	public $name				= 'CE Cache Breaker for Low Variables';
	public $settings_exist		= 'y';
	public $version				= '1.1';
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}// ----------------------------------------------------------------------

	/**
	 * Settings
	 *
	 * Settings fields for the extension
	 *
	 * @return void
	 */
	public function settings()
	{
	    return array(
	  		'refresh' => array('r', array(
				'0' => 'No', 
				'1' => 'Yes'
			)), 
			'refresh_time' => array('s', array(
				'0' => '0',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4', 
				'5' => '5'
			))
		);
	}
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @return void
	 */
	public function activate_extension()
	{		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'low_variables_post_save',
			'hook'		=> 'low_variables_post_save',
			'settings'	=> serialize($this->default_settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);			
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * low_variables_post_save
	 *
	 * @param array of variable ids of the variables that were saved
	 * @return void
	 */
	public function low_variables_post_save($var_ids)
	{
    // print_r($var_ids);
	  
	  //check to see if CE Cache is an installed add-on
    $this->EE->db->select( 'module_id' );
    $this->EE->db->where( 'module_name', 'Ce_cache' );
    $q = $this->EE->db->get( 'modules' );
    $ce_cache_installed = ( $q->num_rows() > 0 );
    $q->free_result();

    if ( $ce_cache_installed )
    {
      //include the cache_break class
      if ( ! class_exists( 'Ce_cache_break' ) )
      {
        include PATH_THIRD . 'ce_cache/libraries/Ce_cache_break.php';
      }

      //instantiate the class
      $cache_break = new Ce_cache_break();

      //the cache items to remove or refresh
      $items = array();

      //item tag names that you would like to remove or refresh
      $tags = array('low_variables');

      //whether or not to refresh the local items after they are cleared
      $refresh = $this->settings['refresh'];

      //the number of seconds to wait between refreshing (and deleting)
      //items. Only applicable if refreshing.
      $refresh_time = $this->settings['refresh_time'];

      $cache_break->break_cache( $items, $tags, $refresh, $refresh_time );
    }
	}

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		$data = array(
			'settings'	=> serialize($this->default_settings),
			'version'	=> $this->version
		);

		$this->EE->db->where('class', __CLASS__)->update('extensions', $data);
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.ce_cache_breaker_for_low_variables.php */