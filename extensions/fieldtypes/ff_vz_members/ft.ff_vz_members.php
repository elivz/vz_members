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


	/**
	 * Display Site Settings
	 */
	function display_site_settings()
	{

	}
	
    
	/**
	 * Display Field
	 * 
	 * @param  string  $field_name      The field's name
	 * @param  mixed   $field_data      The field's current value
	 * @param  array   $field_settings  The field's settings
	 * @return string  The field's HTML
	 */
	function display_field($field_name, $field_data, $field_settings)
	{
		
	}
	
    
	/**
	 * Display Cell
	 * 
	 * @param  string  $cell_name      The cell's name
	 * @param  mixed   $cell_data      The cell's current value
	 * @param  array   $cell_settings  The cell's settings
	 * @return string  The field's HTML
	 */
	function display_cell($cell_name, $cell_data, $cell_settings)
	{

	}


	/**
	 * Save Field
	 * 
	 * @param  string  $field_data		The field's post data
	 * @param  array  $field_settings	The field settings
	 */
	function save_field($field_data, $field_settings)
	{

	}


	/**
	 * Save Cell
	 * 
	 * @param  string  $cell_data		The field's post data
	 * @param  array  $fcell_settings	The field settings
	 */
	function save_cell($cell_data, $cell_settings)
	{

	}

}


/* End of file ft.ff_vz_members.php */
/* Location: ./system/fieldtypes/ff_vz_members/ft.ff_vz_members.php */