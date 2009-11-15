<?php

if(!defined('EXT'))
{
	exit('Invalid file request');
}

class Analytics_panel
{
	var $settings        = array();
	var $name            = 'Google Analytics Panel';
	var $version         = '1.0.1';
	var $description     = 'Display your Google Analytics stats on the control panel home page.';
	var $settings_exist  = 'y';
	var $docs_url        = 'http://github.com/amphibian/ext.analytics_panel.ee_addon';

	
	// -------------------------------
	//   Constructor - Extensions use this for settings
	// -------------------------------
	
	function Analytics_panel($settings='')
	{
	    $this->settings = $settings;
	}
	// END
	
	
	function settings_form($current)
	{
		global $DB, $DSP, $IN, $LANG, $PREFS;
						
		// Start building the page
		$DSP->crumbline = TRUE;
		
		$DSP->title  = $LANG->line('extension_settings');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities')).
		$DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')));
		$DSP->crumb .= $DSP->crumb_item($this->name);
		
		$DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));
		
		$DSP->body = '';
		$DSP->body .= $DSP->form_open(
			array(
				'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings',
				'name'   => 'tag_sync',
				'id'     => 'tag_sync'
			),
			array('name' => get_class($this))
		);
		
		// Open the table
		$DSP->body .=   $DSP->table('tableBorder', '0', '', '100%');
		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableHeadingAlt', '', '2');
		$DSP->body .=   $this->name;
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();	
			
		// Create a settings row for the user			
		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableCellTwo', '45%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line('username'));
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->td('tableCellTwo');
		$DSP->body .=   $DSP->input_text('user', (isset($current['user'])) ? $current['user'] : '', null, null, null, '300px');
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();			
		
		// Create a settings row for the password			
		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableCellOne', '45%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line('password'));
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->td('tableCellOne');
		$DSP->body .=   $DSP->input_pass('password', (isset($current['password'])) ? $current['password'] : '', null, null, null, '300px');
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();
		
		// Create a settings row for the profile chooser			
		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td('tableCellTwo', '45%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line('profile'));
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->td('tableCellTwo');
		// If we have a username and password, try and authenticate and fetch our profile list
		if( !empty($current['user']) && !empty($current['password']) )
		{
			require_once(PATH_LIB.'analytics_panel/gapi.class.php');				
			$ga_email = $current['user'];
			$ga_password = $current['password'];
			
			$ga = new gapi($ga_email, $ga_password);
			$ga->requestAccountData(1,100);
			
			if($ga->getResults())
			{
				$DSP->body .= $DSP->input_select_header('profile');
				foreach($ga->getResults() as $result)
				{
				  $DSP->body .= $DSP->input_select_option($result->getProfileId(), $result, (!empty($current['profile']) && $current['profile'] == $result->getProfileId()) ? 1 : 0);
				}
			}
			else
			{
				// There was an authentication error, or no accounts to list
				$DSP->body .= $LANG->line('no_accounts');
			}
		}
		else
		{
			// No username or password set
			$DSP->body .= $LANG->line('need_credentials');
		}
		$DSP->body .=   $DSP->input_select_footer();
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();
					
		// Wrap it up
		$DSP->body .=   $DSP->table_c();
		$DSP->body .=   $DSP->qdiv('itemWrapperTop', $DSP->input_submit());
		$DSP->body .=   $DSP->form_c();	   			
	}
	
	
	function save_settings()
	{
		global $DB;	
		$data = array('settings' => addslashes(serialize($_POST)));
		$update = $DB->update_string('exp_extensions', $data, "class = 'Analytics_panel'");
		$DB->query($update);
	}

		
	function get_user_id()
	{
		global $IN, $SESS;
		return ( ! $IN->GBL('id', 'GP')) ? $SESS->userdata('member_id') : $IN->GBL('id', 'GP');
	}
	// END
	

	function is_setup()
	{
		if(!empty($this->settings['user']) && !empty($this->settings['password']) && !empty($this->settings['profile']))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	    
	function myaccount_homepage_builder($i)
	{
		global $DSP, $DB, $EXT;
		$r = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';

		if($this->is_setup() !== FALSE)
		{
			$id = $this->get_user_id();
			$prefs = array();

			// This is all basically lifted from the function 'homepage_builder'
			// Located in /system/cp/cp.myaccount.php  
					
			$DB->fetch_fields = TRUE;
			$query = $DB->query("
				SELECT analytics_panel
				FROM exp_member_homepage 
				WHERE member_id = '".$DB->escape_str($id)."'
			");
			if ($query->num_rows == 0)
	        {        
	            foreach ($query->fields as $f)
	            {
					$prefs[$f] = 'n';
	            }
	        }
	        else
	        {  
	        	unset($query->row['member_id']);
	              
	            foreach ($query->row as $key => $val)
	            {
					$prefs[$key] = $val;
	            }
	        }
	        
		  	foreach ($prefs as $key => $val)
			{
				$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
				
				$r .= $DSP->tr();
				$r .= $DSP->table_qcell($style, $DSP->qspan('defaultBold', 'Google Analytics'));
				$r .= $DSP->table_qcell($style, $DSP->input_radio($key, 'l', ($val == 'l') ? 1 : ''));
				$r .= $DSP->table_qcell($style, $DSP->input_radio($key, 'r', ($val == 'r') ? 1 : ''));
				$r .= $DSP->table_qcell($style, $DSP->input_radio($key, 'n', ($val != 'l' && $val != 'r') ? 1 : ''));
				$r .= $DSP->tr_c();
	        }
		}	
        return $r;
	}   
	// END 


	function myaccount_set_homepage_order($i)
	{
		global $DB, $DSP, $EXT;
		$r = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';

		if($this->is_setup() !== FALSE)
		{	
			$id = $this->get_user_id();
			$panels = array('analytics_panel');
			$prefs = array();		
					
			// This is all basically lifted from the function 'set_homepage_order'
			// Located in /system/cp/cp.myaccount.php
			      
	        $query = $DB->query("
	        	SELECT	* FROM exp_member_homepage 
	        	WHERE member_id = '".$DB->escape_str($id)."'
	        ");
						  
			foreach ($query->row as $key => $val)
			{
				if (in_array($key, $panels))
				{
					if ($val && $val != 'n')
					{
						$prefs[$key] = $val;
					}
				}
			}
			
			foreach ($prefs as $key => $val)
			{
				if (in_array($key, $panels))
				{
					$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
					
					$r .= $DSP->tr();
					$r .= $DSP->table_qcell($style, $DSP->qspan('defaultBold', 'Google Analytics'));
									
					if ($val == 'l')
					{
						$r .= $DSP->table_qcell($style, $DSP->input_text($key.'_order', $query->row[$key.'_order'], '10', '3', 'input', '50px'));
						$r .= $DSP->table_qcell($style, NBS);
					}
					elseif ($val == 'r')
					{
						$r .= $DSP->table_qcell($style, NBS);
						$r .= $DSP->table_qcell($style, $DSP->input_text($key.'_order', $query->row[$key.'_order'], '10', '3', 'input', '50px'));
					}
					
					$r .= $DSP->tr_c();
				}
	        }
		}
        return $r;		
	}   
	// END
	

	function show_full_control_panel_end($out)
	{
		global $EXT, $IN;
		$out = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';
		
		// Only add our styles on the CP home screen
		if( $IN->GBL('C', 'GET') === FALSE && $IN->GBL('M', 'GET') === FALSE )
		{					
			$find= '</head>';
			$replace = '
			<style type="text/css">
			td.analytics-container-cell { border-bottom: 1px solid #CAD0D5 !important; }
			.analytics-panel { width: 100%; }
			.analytics-panel th { background: #EEF4F9; border-bottom: 1px solid #DDD; padding: 5px; text-align: left; }
			.analytics-panel td { padding: 5px; width: 25%; }
			.analytics-panel .border-right-dotted { border-right: 1px dotted #DDD; }
			.analytics-panel .border-right-solid { border-right: 1px solid #DDD; }
			td.analytics-stat-col { border-bottom: 1px solid #DDD; border-right: 1px dotted #DDD; color: #999; text-align: center; }
			td.end { border-right: 0px; }
			.analytics-stat-col .analytics-stat { color: #000; display: block; font: bold 24px/24px Arial, Helvetica, sans; }
			td.analytics-stat-row, td.analytics-sparkline { border-bottom: 1px dotted #DDD; color: #999; }
			td.bottom { border-bottom-style: solid; }
			.analytics-stat-row .analytics-stat { color: #000; font-size: 14px; font-weight: bold; margin-right: 3px; }
			td.analytics-sparkline { text-align: center; }
			td.analytics-inset-container { padding: 0; vertical-align: top; }
			.analytics-inset { width: 100%; }
			th.analytics-count-type { color: #999; font-weight: normal; text-align: right; }
			td.analytics-count { text-align: right; }
			.analytics-inset td { border-bottom: 1px dotted #DDD; width: auto; }
			td.analytics-top-content-row, td.analytics-top-referrer-row { word-wrap: break-word; }
			td.analytics-report-link { border-bottom: 0px; font-weight: bold; }
			td.analytics-footer { background: #EEF4F9; border-top: 1px solid #DDD; }
			</style>		
			</head>
			';
			return str_replace($find, $replace, $out);
		}
		else
		{
			return $out;
		}
	}   
	// END
	
		
	function build_analytics_panel($method)
	{						
		global $DSP, $EXT, $LANG;
		$r = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';
	
		// With this crazy hook we need to make sure that it's our method that's being called,
		// as other methods using this hook will *also* call this function.
		if( $method == 'analytics_panel' && $this->is_setup() !== FALSE )
		{	
			$LANG->fetch_language_file('analytics_panel');
	
			$r .=
			$DSP->table('tableBorder', '0', '0', '100%').
			$DSP->tr().
			$DSP->table_qcell('tableHeading', 'Google Analytics').
			$DSP->tr_c().
			$DSP->tr();
			
			$ga_email = $this->settings['user'];
			$ga_password = $this->settings['password'];
			$ga_profile_id = $this->settings['profile'];
			
			require_once(PATH_LIB.'analytics_panel/gapi.class.php');
			
			$today = new gapi($ga_email,$ga_password);
			if($today->getAuthToken() != FALSE)
			{
				// This is the first call made, so save the auth token for use in subsequent calls
				// (No need to re-authorize multiple times in one session.)
				$ga_auth_token = $today->getAuthToken();
				
				// Get account data so we can store the profile info
				$today->requestAccountData(1,100);
				$profile = array();
				foreach($today->getResults() as $result)
				{
					if($result->getProfileId() == $this->settings['profile'])
					{
						$profile['id'] = $result->getProfileId();
						$profile['title'] = $result;
						$profile['webid'] = $result->getWebPropertyId();
					}
				}
				
				$today->requestReportData(
					$ga_profile_id,
					'date',
					array('pageviews', 'visits', 'timeOnSite'),
					'', '',
					date('Y-m-d'),
					date('Y-m-d')
				);
				
				$yesterday = new gapi($ga_email,$ga_password,$ga_auth_token);
				$yesterday->requestReportData(
					$ga_profile_id,
					array('date'),
					array('pageviews','visits', 'timeOnSite'),
					'','',
					date('Y-m-d', strtotime('yesterday')),
					date('Y-m-d', strtotime('yesterday'))
				);
				
				$lastmonth = new gapi($ga_email,$ga_password,$ga_auth_token);
				$lastmonth->requestReportData($ga_profile_id,
					array('date'),
					array('pageviews','visits', 'newVisits', 'timeOnSite', 'bounces', 'entrances'),
					'date', '',
					date('Y-m-d', strtotime('31 days ago')),
					date('Y-m-d', strtotime('yesterday'))
				);
				
				$topcontent = new gapi($ga_email,$ga_password,$ga_auth_token);
				$topcontent->requestReportData($ga_profile_id,
					array('hostname', 'pagePath'),
					array('pageviews'),
					'-pageviews', '',
					date('Y-m-d', strtotime('31 days ago')),
					date('Y-m-d', strtotime('yesterday')),
					null, 10
				);		
			
				$referrers = new gapi($ga_email,$ga_password,$ga_auth_token);
				$referrers->requestReportData($ga_profile_id,
					array('source', 'referralPath', 'medium'),
					array('visits'),
					'-visits', '',
					date('Y-m-d', strtotime('31 days ago')),
					date('Y-m-d', strtotime('yesterday')),
					null, 10
				);			
			
				// No way I'm using the Display Class to build this whole thing
				ob_start();
				include PATH_LIB.'analytics_panel/analytics_panel_display.php';
				$template = ob_get_clean();
				
				$r .=
				$DSP->td('default analytics-container-cell').
				$template;
			}
			else
			{
				// We couldn't fetch our account data for some reason
				$r .= 
				$DSP->td('tableCellTwo').
				$LANG->line('trouble_connecting');
			}
				
			$r .=
			$DSP->td_c().
			$DSP->tr_c().
			$DSP->table_c();
			
			// The'control_panel_home_page_left/right_option' hook doesn't return data,
			// so we have to manually save our output in the last_call variable.
			// Otherwise subsequent calls to this hook with other functions
			// will overwrite what we just created.
			$EXT->last_call = $r;			
		}
		
		// It's not our method, and/or we're not setup, so just return.
		return $r;
	}   

	
	function analytics_avg_pages($pageviews, $visits)
	{
		echo ($pageviews && $visits) ? round($pageviews / $visits, 2) : 0;
	}
	

	function analytics_avg_visit($seconds, $visits)
	{
		if($seconds && $visits)
		{
			$avg_secs = $seconds / $visits;
			// This little snippet by Carson McDonald, from his Analytics Dashboard WP plugin
			$hours = floor($avg_secs / (60 * 60));
			$minutes = floor(($avg_secs - ($hours * 60 * 60)) / 60);
			$seconds = $avg_secs - ($minutes * 60) - ($hours * 60 * 60);
			printf('%02d:%02d:%02d', $hours, $minutes, $seconds);
		}
		else
		{
			echo '00:00:00';
		}
	}
	
	
	function analytics_sparkline($data_array, $metric)
	{
		$max = 0; $stats = '';
		
		foreach($data_array as $result)
		{
			switch($metric) {
				case "pageviews":
					$datapoint = $result->getPageviews();
					break;
				case "visits":	
					$datapoint = $result->getVisits();
					break;
				case "time":
					$datapoint = $result->getTimeOnSite();
					break;
				case "avgpages":
					$datapoint = ($result->getVisits() > 0 && $result->getPageViews() > 0) ? $result->getPageviews() / $result->getVisits() : 0;
					break;
				case "bouncerate":
					$datapoint = ($result->getEntrances() > 0 && $result->getBounces() > 0) ? $result->getBounces() / $result->getEntrances() : 0;
					break;
				case "newvisits":
					$datapoint =  ($result->getNewVisits() > 0 && $result->getVisits() > 0) ? $result->getNewVisits() / $result->getVisits() : 0;
					break;
			}		

			if($max < $datapoint)
			{
				$max = $datapoint;
			}
			$stats .= $datapoint . ',';
		}
		$stats = rtrim($stats, ',');
		
		echo '<img src="http://chart.apis.google.com/chart?cht=ls&amp;chs=100x20&amp;chm=B,e6f2fa,0,0.0,0.0&amp;chco=0077cc&amp;chd=t:'.$stats.'&amp;chds=0,'.$max.'" alt="" />';
	}	
			
   
	// --------------------------------
	//  Activate Extension
	// --------------------------------
	
	function activate_extension()
	{
	    global $DB;
	    
	    $defaults = array(
	    	'username' => '',
	    	'password' => '',
	    	'profile' => ''
	    );  
	    
	    $hooks = array(
	    	'myaccount_homepage_builder' => 'myaccount_homepage_builder',
	    	'myaccount_set_homepage_order' => 'myaccount_set_homepage_order',
	    	'control_panel_home_page_left_option' => 'build_analytics_panel',
	    	'control_panel_home_page_right_option' => 'build_analytics_panel',
	    	'show_full_control_panel_end' => 'show_full_control_panel_end'
	    );
	    
	    foreach($hooks as $hook => $method)
	    {
		    $DB->query($DB->insert_string('exp_extensions',
		    	array(
					'extension_id' => '',
			        'class'        => "Analytics_panel",
			        'method'       => $method,
			        'hook'         => $hook,
			        'settings'     => serialize($defaults),
			        'priority'     => 10,
			        'version'      => $this->version,
			        'enabled'      => "y"
					)
				)
			);	    
	    }
		
	    $DB->query("ALTER TABLE exp_member_homepage 
	    	ADD `analytics_panel` char(1), 
	    	ADD `analytics_panel_order` int(3) unsigned");
	}
	// END


	// --------------------------------
	//  Update Extension
	// --------------------------------  
	
	function update_extension($current='')
	{
	    global $DB;
	    
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }
	    
	    $DB->query("UPDATE exp_extensions 
	    	SET version = '".$DB->escape_str($this->version)."' 
	    	WHERE class = 'Analytics_panel'");
	}
	// END
	
	
	// --------------------------------
	//  Disable Extension
	// --------------------------------
	
	function disable_extension()
	{
	    global $DB;
	    
	    $DB->query("DELETE FROM exp_extensions WHERE class = 'Analytics_panel'");
	    $DB->query("ALTER TABLE exp_member_homepage DROP `analytics_panel`, DROP `analytics_panel_order`");
	    
	}
	// END


}
// END CLASS