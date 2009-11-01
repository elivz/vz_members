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
	 * Create the user checkboxes
	 */
  function _create_user_checkboxes($field_name, $selected_members, $member_groups)
  {
    global $DB, $DSP, $LANG;
    
    // If there are no member groups selected, don't bother
    if (!$member_groups)
    {
			return $DSP->qdiv('highlight_alt', $LANG->line('no_member_groups'));
    }
    else
    {
      $member_groups = implode(',', $member_groups);
    }
    
    // Initialize a new instance of SettingsDisplay
    $SD = new Fieldframe_SettingsDisplay();
	    
		// Get the members in the selected member groups
		$query = $DB->query("
					SELECT
						exp_members.member_id AS member_id,
						exp_members.screen_name AS screen_name,
						exp_member_groups.group_title AS group_title, 
						exp_member_groups.group_id AS group_id
					FROM
						exp_members
					INNER JOIN
						exp_member_groups
					ON
						exp_members.group_id = exp_member_groups.group_id
					WHERE 
						exp_member_groups.group_id IN ($member_groups)
					ORDER BY 
						exp_member_groups.group_id ASC, exp_members.screen_name ASC ");
    
    // Convert the list of selected members into an array
    $selected_members = explode(',', $selected_members);
    
    $r = '';
    $current_group = -1;
    
    foreach($query->result AS $member)
		{
			// If we are moving on to a new group
			if($current_group != $member['group_id'])
			{
				// Set the current group
				$current_group = $member['group_id'];

				// Output the group header
        $r .= '<div style="clear:left"></div>';
				$r .= $SD->label('<strong>'.$member['group_title'].'</strong>');
			}
      
      // Output the checkbox
			$checked = in_array($member['member_id'], $selected_members) ? 1 : 0;
			$r .= '<label style="display:block; float:left; margin:3px 15px 7px 0; white-space:nowrap;">'
			    . $DSP->input_checkbox($field_name.'[]', $member['member_id'], $checked)
			    . NBS.$member['screen_name']
			    . '</label> ';
		}
    
    // Clear the floats
    $r .= '<div style="clear:left"></div>';
    
    return $r;
  }
  
  
	/**
	 * Display Field
	 */
	function display_field($field_name, $field_data, $field_settings)
	{
    return $this->_create_user_checkboxes($field_name, $field_data, $field_settings['member_groups']);
	}
	
    
	/**
	 * Display Cell
	 */
	function display_cell($cell_name, $cell_data, $cell_settings)
	{
    return $this->_create_user_checkboxes($cell_name, $cell_data, $cell_settings['member_groups']);
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