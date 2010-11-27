<?php

if ( ! defined('EXT')) exit('Invalid file request');


/**
 * VZ Members Class
 *
 * @package   FieldFrame
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2009-2010 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 *            Some small bits of code here and there were borrowed from the
 *            Checkbox Group fieldtype included with FieldFrame.
 */
 
class Vz_members extends Fieldframe_Fieldtype {

    /**
     * Fieldtype Info
     * @var array
     */
    var $info = array(
        'name'             => 'VZ Members',
        'version'          => '0.99',
        'desc'             => 'Select members from one or more member groups',
        'docs_url'         => 'http://elivz.com/blog/single/vz_members/',
        'versions_xml_url' => 'http://elivz.com/files/versions.xml'
    );
    
    var $requires = array(
        'ff'        => '1.4',
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
    function _get_member_groups($selected_groups)
    {
        global $DB, $SESS;
        $SD = new Fieldframe_SettingsDisplay();
        //var_dump($selected_groups);die();
        
        // Get the available member groups
        if (!isset( $SESS->cache['vz_members']['groups']['all'] ))
        {
            $member_groups = array();
            $result = $DB->query("SELECT group_title, group_id FROM exp_member_groups WHERE site_id = 1")->result;
            foreach ($result as $item)
            {
                $member_groups[array_pop($item)] = array_pop($item);
            }
            $SESS->cache['vz_members']['groups']['all'] = $member_groups;
        }
        
        return $SESS->cache['vz_members']['groups']['all'];
    }
  
  
	/**
	 * Display Field Settings
	 */
	function display_field_settings($field_settings)
	{
        // Initialize a new instance of SettingsDisplay
        $SD = new Fieldframe_SettingsDisplay();
        
        $row1 = array(
            $SD->label('mode_label'),
            $SD->select('mode', $field_settings['mode'], $this->modes)
        );
    
		$row2 = array(
            $SD->label('member_groups_label'),
            $SD->multiselect('member_groups[]', $field_settings['member_groups'], $this->_get_member_groups())
        );
		
		return array('rows' => array( $row1, $row2 ));
	}
	
    
	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($cell_settings)
	{
        global $LANG;
        $SD = new Fieldframe_SettingsDisplay();
        
        $row1 = array(
            $LANG->line('mode_label_cell'),
            $SD->select('mode', $cell_settings['mode'], $this->modes)
        );
    
		$row2 = array(
            $LANG->line('member_groups_label_cell'),
            $SD->multiselect('member_groups[]', $cell_settings['member_groups'], $this->_get_member_groups())
        );
		
		return array( $row1, $row2 );
	}
	
	
	/**
	 * Create the user checkboxes or select list
	 */
    function _create_user_list($field_name, $selected_members, $member_groups, $mode)
    {
        global $DB, $DSP, $LANG, $SESS;
        $SD = new Fieldframe_SettingsDisplay();
        
        // If there are no member groups selected, don't bother
        if (empty($member_groups))
        {
            $LANG->fetch_language_file('vz_members');
            return $DSP->qdiv('highlight', $LANG->line('no_member_groups'));
        }
        
        // Flatten the list of member groups csv
        if (is_array($member_groups))
        {
            $member_groups = implode(',', $member_groups);
        }
	    
        // Get the members in the selected member groups
        if (!isset( $SESS->cache['vz_members']['in_groups'][$member_groups] ))
        {
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
                    exp_member_groups.group_id IN ($member_groups) AND exp_member_groups.site_id = 1
                ORDER BY 
                    exp_member_groups.group_id ASC, exp_members.screen_name ASC
            ");
            $SESS->cache['vz_members']['in_groups'][$member_groups] = $query->result;
        }
        $members = $SESS->cache['vz_members']['in_groups'][$member_groups];
    
        $r = '';
        $current_group = 0;
        
        if ($mode == 'single')
        {
            // Get the first selected member if there are more than one
            if (is_array($selected_members))
            {
                $selected_members = array_shift($selected_members);
            }
            
            $r = $DSP->input_select_header($field_name);
            $selected = (!$selected_members) ? 1 : 0;
            $r .= $DSP->input_select_option('', '&mdash;', $selected) . NL;
            foreach ($members as $member)
            {
                // If we are moving on to a new group
                if ($current_group != $member['group_id'])
                {
                    // Output the group header
                    if ($current_group) $r .= '</optgroup>' . NL;
                    $r .= '<optgroup label="'.$member['group_title'].'">' . NL;
                    
                    // Set the new current group
                    $current_group = $member['group_id'];
                }
            
                // Output the checkbox
                $selected = ($member['member_id'] == $selected_members) ? 1 : 0;
                $r .= $DSP->input_select_option($member['member_id'], $member['screen_name'], $selected) . NL;
            }
            $r .= '</optgroup>';
            $r .= $DSP->input_select_footer();
        }
        else // Multi-select mode
        {
            foreach ($members as $member)
            {
            	// If we are moving on to a new group
            	if ($current_group != $member['group_id'])
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
                    . NBS . $member['screen_name']
                    . '</label>';
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
        global $DB, $SESS;
        
        // Prepare parameters for SQL query
        $member_list = (is_array($members)) ? implode(',', $members) : $members;
        if (!$member_list) $member_list = -1;
        $sort = (strtolower($sort) == 'desc') ? 'DESC' : 'ASC';
        $orderby = ($orderby == 'username' || $orderby == 'screen_name' || $orderby == 'group_id') ? $orderby : 'member_id';
        
        // Only hit the database once per pageload
        if (!isset( $SESS->cache['vz_members']['members'][$member_list][$orderby][$sort] ))
        {
            // Get the names of the members
            $query = $DB->query("
                SELECT member_id, group_id, username, screen_name
                FROM exp_members 
                WHERE member_id IN ($member_list)
                ORDER BY $orderby $sort
            ");
            $SESS->cache['vz_members']['members'][$member_list][$orderby][$sort] = $query->result;
        }
        
        return $SESS->cache['vz_members']['members'][$member_list][$orderby][$sort];
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
                $separator = isset($params['separator']) ? $params['separator'] : '|';
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
            if (isset($params['backspace']))
            {
                $r = substr($r, 0, -$params['backspace']);
            }
            
            return $r;
    	}
    }


    /**
     * Names
     */
    function names($params, $tagdata, $field_data, $field_settings)
    {
        // Get the member info
        $members = $this->_get_member_names($field_data, $params['orderby'], $params['sort']);
        
        // Put the names in an array
        foreach ($members as $member)
        {
            $member_names[] = $member['screen_name'];
        }
        
        // Output the list
        $separator = isset($params['separator']) ? $params['separator'] : ', ';
        return implode($separator, $member_names);
    }
  
  
    /**
    * Checks the intersection between the selected members and a
    * member or list of members 
    */
    function is_allowed($params, $tagdata, $field_data, $field_settings)
    {
        global $DB, $SESS;
        
        $allowed = is_array($field_data) ? $field_data : array($field_data);
        $candidates = explode('|', $params['members']);
        
        if ( isset($params['groups']) )
        {
            // Get all the users in those groups
            if ( !isset($SESS->cache['vz_members']['groups'][$params['groups']]) )
            {
                $SESS->cache['vz_members']['groups'][$params['groups']] = $DB->query("
                	SELECT member_id
                	FROM exp_members 
                	WHERE group_id IN (".$params['groups'].")
                ")->result;
            }
            $supers = $SESS->cache['vz_members']['groups'][$params['groups']];
            
            // Separate out the member_ids
            foreach ($supers as $super)
            {
                $candidates[] = $super['member_id'];
            }
        }
        
        // Are there any matches between the two?
        $isAllowed = count(array_intersect($candidates, $allowed));
        
        if (!$tagdata) // Single tag
        {
            return $isAllowed ? TRUE : FALSE;
        }
        else // Tag pair
        {
            return $isAllowed ? $tagdata : '';
        }
    }
  
}

/* End of file ft.vz_members.php */