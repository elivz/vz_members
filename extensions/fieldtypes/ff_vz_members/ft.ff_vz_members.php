<?php

if ( ! defined('EXT')) exit('Invalid file request');


/**
 * VZ Members Class
 *
 * @package   FieldFrame
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2009 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
 
class Ff_vz_members extends Fieldframe_Fieldtype {

	/**
	 * Fieldtype Info
	 * @var array
	 */
	var $info = array(
		'name'             => 'VZ Members',
		'version'          => '0.9',
		'desc'             => 'Multi-select list of site members',
		'docs_url'         => 'http://elivz.com/blog/single/vz_members/',
		'versions_xml_url' => 'http://elivz.com/files/version.xml'
	);
	
	var $requires = array(
		'ff'        => '1.3.0',
		'cp_jquery' => '1.1.1',
	);
    
	var $default_site_settings = array();
	
	var $default_field_settings = array(
		'member_groups'   => array()
	);

	var $default_cell_settings = array(
		'member_groups'   => array()
	);
  
  /**
   * Member Groups Select
   */
  function _member_groups_select($selected_groups)
  {
		global $DB, $DSP;
		
   	// Initialize a new instance of SettingsDisplay
    $SD = new Fieldframe_SettingsDisplay();
		$r = $SD->label('member_groups_label');
		
    // Get the available member groups
		$member_groups = $DB->query("SELECT group_title, group_id FROM exp_member_groups");
    
    // Construct the select list of member groups
		$r .= $DSP->input_select_header('member_groups[]', 'y', ($member_groups->num_rows < 10 ? $member_groups->num_rows : 10));
		foreach($member_groups->result as $member_group)
		{
			$selected = in_array($member_group['group_id'], $selected_groups) ? 1 : 0;
			$r .= $DSP->input_select_option($member_group['group_id'], $member_group['group_title'], $selected);
		}
		$r .= $DSP->input_select_footer();
		
		return $r;
  }
  
  
	/**
	 * Display Field Settings
	 */
	function display_field_settings($field_settings)
	{
		return array('cell2' => $this->_member_groups_select($field_settings['member_groups']));
	}
	
    
	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($cell_settings)
	{
		return $this->_member_groups_select($cell_settings['member_groups']);
	}
	
    
	/**
	 * Display Field
	 */
	function display_field($field_name, $field_data, $field_settings)
	{
    
	}
	
    
	/**
	 * Display Cell
	 */
	function display_cell($cell_name, $cell_data, $cell_settings)
	{

	}


	/**
	 * Save Field
	 */
	function save_field($field_data, $field_settings)
	{

	}


	/**
	 * Save Cell
	 */
	function save_cell($cell_data, $cell_settings)
	{

	}

}


/* End of file ft.ff_vz_members.php */
/* Location: ./system/fieldtypes/ff_vz_members/ft.ff_vz_members.php */