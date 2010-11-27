<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * VZ Members Class
 *
 * @author    Eli Van Zoeren <eli@elivz.com>
 * @copyright Copyright (c) 2009-2010 Eli Van Zoeren
 * @license   http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported
 */
 
class Vz_members_ft extends EE_Fieldtype {

    public $info = array(
        'name'             => 'VZ Members',
        'version'          => '1.0.0',
    );
    
    /**
     * Fieldtype Constructor
     *
     */
    function Vz_members_ft()
    {
        parent::EE_Fieldtype();
        
        // Initialize the cache
        if (!isset($this->EE->session->cache['vz_members']))
        {
        	$this->EE->session->cache['vz_members'] = array();
        }
        $this->cache =& $this->EE->session->cache['vz_members'];
    }
	
	
	/**
	 * Install Fieldtype
	 *
	 */
    function install()
    {
        // Default field settings
		return array(
            'member_groups' => array(),
            'mode'          => 'multiple'
        );
    }
    
    var $has_array_data = TRUE;
	    
    public $default_cell_settings = array(
        'member_groups' => array(),
        'mode'          => 'single'
    );
    
    protected function modes()
    {
		$this->EE->lang->loadfile('vz_members');
		
        return array(
            'single'    => $this->EE->lang->line('mode_single'),
            'multiple'  => $this->EE->lang->line('mode_multiple')
        );
    }
	
	
	/**
	 * Include the JS and CSS files,
	 * but only the first time
	 *
	 */
	private function _include_jscss()
	{
		if ( !isset($this->cache['jscss']) )
		{
            $this->EE->cp->add_to_head('<style type="text/css">
                div.vz_members_group { float:left; height:14px; line-height:14px !important; margin:3px 10px 7px 0; font-size:12px; }
                label.vz_member { float:left; height:14px; line-height:14px !important; margin:3px 10px 7px 0; padding: 2px 10px; border:1px solid #B6C0C2; -moz-border-radius:9px; border-radius:9px; text-shadow:0 1px #fff; background:#ebf1f7; -webkit-box-shadow:inset 0 2px 3px rgba(255,255,255,0.8); -moz-box-shadow:inset 0 2px 3px rgba(255,255,255,0.8); box-shadow:inset 0 2px 3px rgba(255,255,255,0.8); cursor:pointer; white-space:nowrap; }
                label.vz_member:hover, label.vz_member:focus { background:#f7fafc; -webkit-box-shadow: 0 0 5px #abd9f4; -moz-box-shadow: 0 0 5px #abd9f4; box-shadow: 0 0 5px #abd9f4; }
                label.vz_member.checked { background:#b6babf; color:#fff; text-shadow:0 -1px rgba(0,0,0,0.2); background: -webkit-gradient(linear, 0 0, 0 100%, from(#aaaeb3), to(#b6babf)); background: -moz-linear-gradient(top, #aaaeb3, #b6babf); border-color:#a7b4c2; -webkit-box-shadow:inset 0 1px rgba(0,0,0,0.1); -moz-box-shadow:inset 0 1px 3px rgba(0,0,0,0.1); box-shadow:inset 0 1px 3px rgba(0,0,0,0.1); }
                label.vz_member input { display:none }
            </style>');
            $this->EE->cp->add_to_foot('<script type="text/javascript">
                jQuery(document).ready(function($) {
                    $(".vz_member input").live("click", function() {
                        $(this).parent().toggleClass("checked");
                    });
                });
            </script>');
			
			$this->cache['jscss'] = TRUE;
		}
	}
	
	
    /**
    * Member Groups Select
    */
    private function _get_member_groups()
    {
        // Get the available member groups
        if (!isset( $this->cache['groups']['all'] ))
        {
            $member_groups = array();
            $result = $this->EE->db->query("
                SELECT group_title, group_id
                FROM exp_member_groups
                WHERE site_id = 1
                ")->result_array();
            
            // We need it in key-value form for the select helper functions
            foreach ($result as $item)
            {
                $member_groups[array_pop($item)] = array_pop($item);
            }
            $this->cache['groups']['all'] = $member_groups;
        }
        
        return $this->cache['groups']['all'];
    }
  
    
    /**
     * Create the settings ui
     */
    private function _get_settings($settings)
    {
		$this->EE->lang->loadfile('vz_members');
		$this->EE->load->helper('form');
        
        $mode = isset($settings['mode']) ? $settings['mode'] : 0;
        $row1 = array(
            $this->EE->lang->line('mode_label_cell'),
            form_dropdown('mode', $this->modes(), $mode)
        );
        
        $member_groups = isset($settings['member_groups']) ? $settings['member_groups'] : 0;
		$row2 = array(
            $this->EE->lang->line('member_groups_label_cell'),
            form_multiselect('member_groups[]', $this->_get_member_groups(), $member_groups)
        );
        
        return array( $row1, $row2 );
    }
    
    
    /**
     * Display Field Settings
     */
    function display_settings($field_settings)
    {
        $settings_array = $this->_get_settings($field_settings);
        
        $this->EE->table->add_row($settings_array[0]);
        $this->EE->table->add_row($settings_array[1]);
    }


    function save_settings($data)
    {
    	return array(
    		'mode'	=> $this->EE->input->post('mode'),
    		'member_groups'		=> $this->EE->input->post('member_groups')
    	);
    }
	
    
	/**
	 * Display Cell Settings
	 */
	function display_cell_settings($cell_settings)
	{
		return $this->_get_settings($cell_settings);
	}
	
	
	/**
	 * Create the user checkboxes or select list
	 */
    function _create_user_list($field_name, $selected_members, $member_groups, $mode)
    {
		$this->EE->load->helper('form');
        
        // If there are no member groups selected, don't bother
        if (empty($member_groups))
        {
            $this->EE->lang->loadfile('vz_members');
            return '<div class="highlight">' . $this->EE->lang->line('no_member_groups') . '</div>';
        }
        
        // Flatten the list of member groups csv
        if (is_array($member_groups))
        {
            $member_groups = implode(',', $member_groups);
        }
	    
        // Get the members in the selected member groups
        if ( !isset($this->cache['in_groups'][$member_groups]) )
        {
            $this->cache['in_groups'][$member_groups] = $this->EE->db->query("
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
            ")->result_array();
        }
        $members = $this->cache['in_groups'][$member_groups];
    
        $r = '';
        $current_group = 0;
        
        // We want the selected members as an array
        if (!is_array($selected_members))
        {
            $selected_members = explode('|', $selected_members);
        }
        
        if ($mode == 'single')
        {
            // Get the first selected member if there are more than one
            $selected_members = array_shift($selected_members);
            
            // Construct the select box markup
            $r = '<select name="' . $field_name . '">';
            $r .= '<option value=""' . (!$selected_members ? ' selected="selected"' : '') . '>&mdash;</option>' . NL;
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
            
                // Output the option
                $r .= '<option value="' . $member['member_id'] . '"'
                    . ($member['member_id'] == $selected_members ? ' selected="selected"' : '') . '>' 
                    . $member['screen_name'] . '</option>' . NL;
            }
            $r .= '</optgroup>';
            $r .= '</select>';
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
                    $r .= '<div class="defaultBold vz_members_group">'.$member['group_title'].':</div>';
            	}
            
                // Is it selected?
            	$checked = (in_array($member['member_id'], $selected_members)) ? 1 : 0;
        	  
                // Output the checkbox
                $r .= '<label class="vz_member' . ($checked ? ' checked' : '') . '">'
                    . form_checkbox($field_name.'[]', $member['member_id'], $checked)
                    . $member['screen_name']
                    . '</label>';
        	}
        	
            // Fool the form into working
            $r .= form_hidden($field_name.'[]', 'temp');
            
            // Make it pretty
            $this->_include_jscss();
            
            // Clear the floats
            $r .= '<div style="clear:left"></div>';
        }
        
        return $r;
    }
  
  
    /**
     * Display Field
     */
    function display_field($field_data)
    {
        return $this->_create_user_list($this->field_name, $field_data, $this->settings['member_groups'], $this->settings['mode']);
    }
	
    
    /**
     * Display Cell
     */
    function display_cell($cell_data)
    {
        return $this->_create_user_list($this->cell_name, $cell_data, $this->settings['member_groups'], $this->settings['mode']);
    }

    
    /**
     * Save Field
     */
    function save($field_data)
    {
    	// Remove the temporary element
    	if (is_array($field_data))
    	{
    	   @array_pop($field_data);
    	   $field_data = implode('|', $field_data);
    	}
    	return $field_data;
    }
    
    
    /**
     * Save Cell
     */
    function save_cell($cell_data)
    {
        return $this->save($cell_data);
    }


    /**
    * Get names of a list of members
    */
    function _get_member_names($members, $orderby, $sort)
    {
        // Prepare parameters for SQL query
        $member_list = str_replace('|', ',', $members);
        if (!$member_list) $member_list = -1;
        $sort = (strtolower($sort) == 'desc') ? 'DESC' : 'ASC';
        $orderby = ($orderby == 'username' || $orderby == 'screen_name' || $orderby == 'group_id') ? $orderby : 'member_id';
        
        // Only hit the database once per pageload
        if ( !isset($this->cache['members'][$member_list][$orderby][$sort]) )
        {
            // Get the names of the members
            $this->cache['members'][$member_list][$orderby][$sort] = $this->EE->db->query("
                SELECT member_id, group_id, username, screen_name
                FROM exp_members 
                WHERE member_id IN ($member_list)
                ORDER BY $orderby $sort
                ")->result_array();
        }
        
        return $this->cache['members'][$member_list][$orderby][$sort];
    }

    /**
     * Display Tag
     */
    function replace_tag($field_data, $params=array(), $tagdata=FALSE)
    {
        if (!$tagdata) // Single tag
        {
            return $field_data;
    	}
    	else // Tag pair
    	{
            // Get the member info
            $members = $this->_get_member_names($field_data, $params['orderby'], $params['sort']);
            
            $variables = array();
            foreach ($members as $member)
            {
                // Prepare the variables for replacement
                $variables[] = array(
                    'id' => $member['member_id'],
                    'group' => $member['group_id'],
                    'username' => $member['username'],
                    'screen_name' => $member['screen_name']
                );
            }
            
            $output = $this->EE->TMPL->parse_variables($tagdata, $variables);
            
            // Backsapce parameter
            if (isset($params['backspace']))
            {
                $output = substr($output, 0, -$params['backspace']);
            }
            
            return $output;
    	}
    }


    /**
     * Names
     */
    function replace_names($field_data, $params=array(), $tagdata=FALSE)
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
    function replace_is_allowed($field_data, $params=array(), $tagdata=FALSE)
    {
        $allowed = explode('|', $field_data);
        $candidates = explode('|', $params['members']);
        
        if ( isset($params['groups']) )
        {
            // Get all the users in those groups
            if ( !isset($this->cache['groups'][$params['groups']]) )
            {
                $this->cache['groups'][$params['groups']] = $this->EE->db->query("
                	SELECT member_id
                	FROM exp_members 
                	WHERE group_id IN (".$params['groups'].")
                    ")->result_array();
            }
            $supers = $this->cache['groups'][$params['groups']];
            
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