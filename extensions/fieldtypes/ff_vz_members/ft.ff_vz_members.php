<?php

if ( ! defined('EXT')) exit('Invalid file request');


/**
 * VZ Members Class
 *
 * @package   FieldFrame
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2009 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 *            Some small bits of code here and there were borrowed from the
 *            Checkbox Group fieldtype included with FieldFrame.
 */
 
class Ff_vz_members extends Fieldframe_Fieldtype {

	/**
	 * Fieldtype Info
	 * @var array
	 */
	var $info = array(
		'name'             => 'VZ Members',
		'version'          => '0.91',
		'desc'             => 'Select members from one or more member groups',
		'docs_url'         => 'http://elivz.com/blog/single/vz_members/',
		'versions_xml_url' => 'http://elivz.com/files/version.xml'
	);
	
	var $requires = array(
		'ff'        => '1.3.0',
		'cp_jquery' => '1.1.1',
	);
    
	var $default_site_settings = array();
	
	var $default_field_settings = array(
		'member_groups' => array(),
		'mode'          => 'multiple'
	);

	var $default_cell_settings = array(
		'member_groups' => array(),
		'mode'          => 'single'
	);

	var $modes = array(
		'single'    => 'mode_single',
		'multiple'  => 'mode_multiple'
	);
	
  
  /**
   * Member Groups Select
   */
  function _member_groups_select($selected_groups)
  {
		global $DB, $DSP;
		
    // Get the available member groups
		$member_groups = $DB->query("SELECT group_title, group_id FROM exp_member_groups");
    
    // Construct the select list of member groups
		$r = $DSP->input_select_header('member_groups[]', 'y', ($member_groups->num_rows < 10 ? $member_groups->num_rows : 10));
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
  	// Initialize a new instance of SettingsDisplay
  	$SD = new Fieldframe_SettingsDisplay();
	
    $cell1 = $SD->label('mode_label')
           . $SD->select('mode', $field_settings['mode'], $this->modes);
    
		$cell2 = $r = $SD->label('member_groups_label')
		       . $this->_member_groups_select($field_settings['member_groups']);
		
		return array('cell1' => $cell1, 'cell2' => $cell2);
	}
	
    
	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($cell_settings)
	{
    global $DSP, $LANG;
  	$SD = new Fieldframe_SettingsDisplay();
    
		return '<label class="itemWrapper">'
		   . $LANG->line('mode_label')
		   . $SD->select('mode', $cell_settings['mode'], $this->modes)
		   . '</label>'
		   . '<label class="itemWrapper">'
		   . $LANG->line('member_groups_label')
		   . $this->_member_groups_select($cell_settings['member_groups'])
		   . '</label>';
	}
	
	
	/**
	 * Create the user checkboxes
	 */
  function _create_user_list($field_name, $selected_members, $member_groups, $mode)
  {
    global $DB, $DSP, $LANG;
    
    // If there are no member groups selected, don't bother
    if (!$member_groups)
    {
			return $DSP->qdiv('highlight_alt', $LANG->line('no_member_groups'));
    }
    
    // Flatten the list of members to csv
    if (is_array($member_groups))
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
						exp_member_groups.group_id ASC, exp_members.screen_name ASC
				");
    
    $r = '';
    $current_group = -1;
    
    if ($mode == 'single')
    {
      // Get the first selected member if there are more than one
      if (is_array($selected_members)) $selected_members = array_shift($selected_members);
      
      $r = $DSP->input_select_header($field_name, 0, 4);
      foreach($query->result AS $member)
  		{
  			// If we are moving on to a new group
  			if($current_group != $member['group_id'])
  			{
  				// Output the group header
  				if ($current_group) $r .= '</optgroup>' . NL;
					$r .= '<optgroup label="'.$member['group_title'].'">' . NL;
					
  				// Set the current group
  				$current_group = $member['group_id'];
  			}
        
        // Output the checkbox
  			$selected = ($member['member_id'] == $selected_members) ? 1 : 0;
  			$r .= $DSP->input_select_option($member['member_id'], $member['screen_name'], $selected) . NL;
  		}
  		$r .= $DSP->input_select_footer();
    }
    else
    {
      foreach($query->result AS $member)
  		{
  			// If we are moving on to a new group
  			if($current_group != $member['group_id'])
  			{
  				// Set the current group
  				$current_group = $member['group_id'];
  
  				// Output the group header
          $r .= '<div style="clear:left"></div>';
  				$r .= $SD->label('<strong>'.$member['group_title'].':</strong>');
  			}
        
        // Is it selected?
  			if (is_array($selected_members))
  			{
  		    $checked = (in_array($member['member_id'], $selected_members)) ? 1 : 0;
  		  }
  		  else
  		  {
  		    $checked = ($member['member_id'] == $selected_members) ? 1 : 0;
  		  }
  		  
        // Output the checkbox
  			$r .= '<label style="display:block; float:left; margin:3px 15px 7px 0; white-space:nowrap;">'
  			    . $DSP->input_checkbox($field_name.'[]', $member['member_id'], $checked)
  			    . NBS.$member['screen_name']
  			    . '</label> ';
  		}
  		
  		// Fool the form into working
  		$r .= $DSP->input_hidden($field_name.'[]', 'temp');
      
      // Clear the floats
      $r .= '<div style="clear:left"></div>';
    }
        
    return $r;
  }
  
  
	/**
	 * Display Field
	 */
	function display_field($field_name, $field_data, $field_settings)
	{
    return $this->_create_user_list($field_name, $field_data, $field_settings['member_groups'], $field_settings['mode']);
	}
	
    
	/**
	 * Display Cell
	 */
	function display_cell($cell_name, $cell_data, $cell_settings)
	{
    return $this->_create_user_list($cell_name, $cell_data, $cell_settings['member_groups'], $cell_settings['mode']);
	}


	/**
	 * Save Field
	 */
	function save_field($field_data, $field_settings)
	{
		// Remove the temporary element
		@array_pop($field_data);
		return $field_data;
	}


	/**
	 * Save Cell
	 */
	function save_cell($cell_data, $cell_settings)
	{
    return $this->save_field($cell_data, $cell_settings);
	}


  /**
   * Get names of a list of members
   */
  function _get_member_names($members, $orderby, $sort)
  {
    global $DB;
    
    // Prepare parameters for SQL query
    $member_list = (is_array($members)) ? implode(',', $members) : $members;
    if (!$member_list) $member_list = -1;
    $sort = (strtolower($sort) == 'desc') ? 'DESC' : 'ASC';
    $orderby = ($orderby == 'username' || $orderby == 'screen_name' || $orderby == 'group_id') ? $orderby : 'member_id';
    
    // Get the names of the members
		$query = $DB->query("
					SELECT member_id, group_id, username, screen_name
					FROM exp_members 
					WHERE member_id IN ($member_list)
					ORDER BY $orderby $sort
				");
		
		return $query->result;
  }

	/**
	 * Display Tag
	 */
	function display_tag($params, $tagdata, $field_data, $field_settings)
	{
    if (!$tagdata) // Single tag
    {
      if (is_array($field_data))
      {
        // Multiple members are selected
        $separator = ($params['separator']) ? $params['separator'] : '|';
	   	  return implode($separator, $field_data);
      }
      else
      {
        // Only one member selected
        return $field_data;
      }
		}
		else // Tag pair
		{
		  global $TMPL;
		  
		  // Get the member info
		  $members = $this->_get_member_names($field_data, $params['orderby'], $params['sort']);
		  
			// Prepare for {switch} and {count} tags
			$this->prep_iterators($tagdata);
		  
		  $r = '';
		  
			foreach($members as $member)
			{
				// Make a copy of the tagdata
				$member_tag_data = $tagdata;

				// Replace the variables
				$member_tag_data = $TMPL->swap_var_single('id', $member['member_id'], $member_tag_data);
				$member_tag_data = $TMPL->swap_var_single('group', $member['group_id'], $member_tag_data);
				$member_tag_data = $TMPL->swap_var_single('username', $member['username'], $member_tag_data);
				$member_tag_data = $TMPL->swap_var_single('screen_name', $member['screen_name'], $member_tag_data);
				$member_tag_data = $TMPL->swap_var_single('total', count($members), $member_tag_data);

				// Parse {switch} and {count} tags
				$this->parse_iterators($member_tag_data);

				$r .= $member_tag_data;
			}
		  
		  // Backsapce parameter
		  if ($params['backspace'])
			{
				$r = substr($r, 0, -$params['backspace']);
			}
			
			return $r;
		}
	}
	
}


/* End of file ft.ff_vz_members.php */
/* Location: ./system/fieldtypes/ff_vz_members/ft.ff_vz_members.php */