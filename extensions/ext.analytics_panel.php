<?php

if(!defined('EXT'))
{
	exit('Invalid file request');
}

class Analytics_panel
{
	var $settings		= array();
	var $name			= 'Google Analytics Panel';
	var $version		= '1.1.2';
	var $description	= 'Display your Google Analytics stats on the control panel home page.';
	var $settings_exist	= 'y';
	var $docs_url		= 'http://github.com/amphibian/ext.analytics_panel.ee_addon';
	
	
	function Analytics_panel($settings='')
	{
	    $this->settings = $settings;
	}
	
	
	function settings_form($current)
	{
		global $DB, $DSP, $FNS, $IN, $LANG, $PREFS, $REGX;
		$site = $PREFS->ini('site_id');
		$here = BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extension_settings'.AMP.'name=analytics_panel';
		
		// This removes the current username/password
		if(isset($_GET['analytics_reset']))
		{
			$settings = $this->get_settings();	
			$settings[$site]['user'] = '';
			$settings[$site]['password'] = '';
			$settings[$site]['profile'] = '';
			$settings[$site]['authenticated'] = '';

			$data = array('settings' => addslashes(serialize($settings)));
			$update = $DB->update_string('exp_extensions', $data, "class = 'Analytics_panel'");
			$DB->query($update);
			$FNS->redirect($here);		
		}
		
		// Only grab settings for the current site
		if(isset($current[$site]))
		{
			$current = $current[$site];
		}
		
		// Do we have valid credentials?
		$auth = ( isset($current['authenticated']) && $current['authenticated'] == 'y' ) ? TRUE : FALSE;
					
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
				'name'   => 'analytics_panel',
				'id'     => 'analytics_panel'
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
			
		// Only show the username and password if we don't have validated credentials
		if(!$auth)
		{
			$DSP->body .=   $DSP->input_text('user', '', null, null, null, '300px');
			$DSP->body .=   $DSP->td_c();
			$DSP->body .=   $DSP->tr_c();			
			
			// Create a settings row for the password			
			$DSP->body .=   $DSP->tr();
			$DSP->body .=   $DSP->td('tableCellOne', '45%');
			$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line('password'));
			$DSP->body .=   $DSP->td_c();
			
			$DSP->body .=   $DSP->td('tableCellOne');
			$DSP->body .=   $DSP->input_pass('password', '', null, null, null, '300px');
			$DSP->body .=   $DSP->td_c();
			$DSP->body .=   $DSP->tr_c();
		}
		else
		{
			// We're authenticated, so show our username and reset link
			$DSP->body .=   $LANG->line('authenticated_as').' <b>'.$current['user'].'</b> ';
			$DSP->body .=	$DSP->anchor($here.AMP.'analytics_reset=y', '('.$LANG->line('reset').')', 'style="margin-left:5px;"');
			$DSP->body .=   $DSP->td_c();
			$DSP->body .=   $DSP->tr_c();
		}
		
		// Create a settings row for the profile chooser			
		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td(($auth) ? 'tableCellOne' : 'tableCellTwo', '45%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line('profile'));
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->td(($auth) ? 'tableCellOne' : 'tableCellTwo');
		
		// If we have a username and password, try and authenticate and fetch our profile list
		if(isset($current['authenticated']) && $current['authenticated'] == 'y')
		{
			require_once(PATH_LIB.'analytics_panel/gapi.class.php');				
			$ga_user = $current['user'];
			$ga_password = base64_decode($current['password']);
			
			$ga = new gapi($ga_user, $ga_password);
			$ga->requestAccountData(1,100);
			
			if($ga->getResults())
			{
				$DSP->body .= $DSP->input_select_header('profile');
				$DSP->body .= $DSP->input_select_option('', '--');
				foreach($ga->getResults() as $result)
				{
				  $DSP->body .= $DSP->input_select_option($result->getProfileId(), $result->getTitle(), (isset($current['profile']) && !empty($current['profile']) && $current['profile'] == $result->getProfileId()) ? 1 : 0);
				}
				$DSP->body .=   $DSP->input_select_footer();
			}
			else
			{
				// There are no accounts to list
				$DSP->body .= $LANG->line('no_accounts');
			}
		}
		elseif(isset($current['authenticated']) && $current['authenticated'] == 'n')
		{
			// Bad credentials provided
			$DSP->body .= '<span class="highlight">'.$LANG->line('bad_credentials').'</span>';
		}
		else
		{
			// No username or password set
			$DSP->body .= $LANG->line('need_credentials');		
		}
		
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();
		
		// Get a list of Member Groups for this site
		$groups = $DB->query("SELECT group_id, group_title FROM exp_member_groups WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' ORDER BY group_id ASC");

		// Create a settings row for member groups						
		$DSP->body .=   $DSP->tr();
		$DSP->body .=   $DSP->td(($auth) ? 'tableCellTwo' : 'tableCellOne', '45%');
		$DSP->body .=   $DSP->qdiv('defaultBold', $LANG->line('member_groups'));
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->td(($auth) ? 'tableCellTwo' : 'tableCellOne');
		$DSP->body .= $DSP->input_select_header('member_groups[]', 'y', 5, '300px');
		foreach($groups->result as $group)
		{
			$DSP->body .= $DSP->input_select_option($group['group_id'], $group['group_title'], (isset($current['member_groups']) && !empty($current['member_groups']) && in_array($group['group_id'], $current['member_groups'])) ? 1 : 0);
		}
		$DSP->body .= $DSP->input_select_footer();		
		$DSP->body .=   $DSP->td_c();
		$DSP->body .=   $DSP->tr_c();		
					
		// Wrap it up
		$DSP->body .=   $DSP->table_c();
		$DSP->body .=   $DSP->qdiv('itemWrapperTop', $DSP->input_submit());
		$DSP->body .=   $DSP->form_c();	   			
	}
	
	
	function save_settings()
	{
		global $DB, $PREFS, $REGX;
		$site = $PREFS->ini('site_id');
		
		// $this->settings doesn't work here, so we use our won function
		$settings = $this->get_settings();
        
		// If we're posting a username and password,
		// check if they authenticate, and store them if they do.
		// If not, discard and throw the authentication error flag
		
		if(isset($_POST['user']) && isset($_POST['password']))
		{
			require_once(PATH_LIB.'analytics_panel/gapi.class.php');				
			$ga_user = $_POST['user'];
			$ga_password = $_POST['password'];
			$ga = new gapi($ga_user, $ga_password);
			if($ga->getAuthToken() != FALSE)
			{
				$settings[$site]['user'] = $_POST['user'];
				$settings[$site]['password'] = base64_encode($_POST['password']);
				$settings[$site]['authenticated'] = 'y';
			}
			else
			{
				// The credentials don't authenticate, so zero us out
				$settings[$site]['user'] = '';
				$settings[$site]['password'] = '';
				$settings[$site]['profile'] = '';
				$settings[$site]['authenticated'] = 'n';
			}
		}
		
		if(isset($_POST['profile']))
		{
			$settings[$site]['profile'] = $_POST['profile'];
			
			// Fetch and cache new data with this profile ID
			$settings[$site]['cache'] = $this->fetch_stats(	
				$settings[$site]['user'], 
				base64_decode($settings[$site]['password']), 
				$_POST['profile']
			);
		}

		$settings[$site]['member_groups'] = (isset($_POST['member_groups'])) ? $_POST['member_groups'] : array('1');;
		
		$data = array('settings' => addslashes(serialize($settings)));
		$update = $DB->update_string('exp_extensions', $data, "class = 'Analytics_panel'");
		$DB->query($update);
	}
	
	
	function get_settings()
	{
		global $DB, $REGX;

		$get_settings = $DB->query("SELECT settings FROM exp_extensions WHERE class = 'Analytics_panel' LIMIT 1");
		if ($get_settings->num_rows > 0 && $get_settings->row['settings'] != '')
        {
        	$settings = $REGX->array_stripslashes(unserialize($get_settings->row['settings']));
        }
        else
        {
        	$settings = array();
        }
        return $settings;		
	}

		
	function get_user_id()
	{
		global $IN, $SESS;
		return ( ! $IN->GBL('id', 'GP')) ? $SESS->userdata('member_id') : $IN->GBL('id', 'GP');
	}
	

	function is_setup()
	{
		global $PREFS;
		$site = $PREFS->ini('site_id');
		
		if(isset($this->settings[$site]) && !empty($this->settings[$site]['user']) && !empty($this->settings[$site]['password']) && !empty($this->settings[$site]['profile']))
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
		global $DB, $DSP, $EXT, $PREFS, $SESS;
		$r = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';

		$site = $PREFS->ini('site_id');

		if($this->is_setup() !== FALSE && in_array($SESS->userdata['group_id'], $this->settings[$site]['member_groups']))
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
		global $DB, $DSP, $EXT, $PREFS, $SESS;
		$r = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';

		$site = $PREFS->ini('site_id');

		if($this->is_setup() !== FALSE && in_array($SESS->userdata['group_id'], $this->settings[$site]['member_groups']))
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
		$out = ($EXT->last_call !== FALSE) ? $EXT->last_call : $out;
		
		// Only add our styles on the CP home screen
		if( $IN->GBL('C', 'GET') === FALSE && $IN->GBL('M', 'GET') === FALSE )
		{					
			$find= '</head>';
			$replace = '
			<style type="text/css">
			td.analytics-container-cell { border-bottom: 1px solid #CAD0D5 !important; }
			.analytics-panel { table-layout: fixed; width: 100%; }
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
			td.analytics-inset-container { padding: 0; vertical-align: top; width: 50%; }
			.analytics-inset { table-layout: fixed; width: 100%; }
			th.analytics-count-type { color: #999; font-weight: normal; text-align: right; width: 25%; }
			td.analytics-count { text-align: right; }
			.analytics-inset td { border-bottom: 1px dotted #DDD; width: auto; }
			td.analytics-top-content-row, td.analytics-top-referrer-row { word-wrap: break-word; }
			td.analytics-top-content-row div { overflow:hidden; }
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
		global $DB, $DSP, $EXT, $LANG, $PREFS, $SESS;
		$r = ($EXT->last_call !== FALSE) ? $EXT->last_call : '';
		
		$site = $PREFS->ini('site_id');
	
		// With this crazy hook we need to make sure that it's our method that's being called,
		// as other methods using this hook will *also* call this function.
		if( $method == 'analytics_panel' && $this->is_setup() !== FALSE && in_array($SESS->userdata['group_id'], $this->settings[$site]['member_groups']) ) 
		{
			$LANG->fetch_language_file('analytics_panel');
			
			$r .=
			$DSP->table('tableBorder', '0', '0', '100%').
			$DSP->tr().
			$DSP->table_qcell('tableHeading', 'Google Analytics').
			$DSP->tr_c().
			$DSP->tr();
			
			$ga_user = $this->settings[$site]['user'];
			$ga_password = base64_decode($this->settings[$site]['password']);
			$ga_profile_id = $this->settings[$site]['profile'];
			
			require_once(PATH_LIB.'analytics_panel/gapi.class.php');
			
			$today = new gapi($ga_user,$ga_password);
			if($today->getAuthToken() != FALSE)
			{
				// This is the first call made, so save the auth token for use in subsequent calls
				// (No need to re-authorize multiple times in one session.)
				$ga_auth_token = $today->getAuthToken();
				
				$today->requestReportData(
					$ga_profile_id,
					'date',
					array('pageviews', 'visits', 'timeOnSite'),
					'', '',
					date('Y-m-d'),
					date('Y-m-d')
				);
				
				// Check to see if we have a cache, and if it's still valid (refresh daily)
				if(isset($this->settings[$site]['cache']) && $this->settings[$site]['cache']['cache_date'] == date('Y-m-d'))
				{
					$data = $this->settings[$site]['cache'];
				}
				else
				{
					// We need to fetch our data and recreate the cache
					$data = $this->fetch_stats($ga_user, $ga_password, $ga_profile_id);
					$settings = $this->get_settings();
					$settings[$site]['cache'] = $data;
					
					$new_data = array('settings' => addslashes(serialize($settings)));
					$update = $DB->update_string('exp_extensions', $new_data, "class = 'Analytics_panel'");
					$DB->query($update);
				}
							
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


	function fetch_stats($ga_user, $ga_password, $ga_profile_id)
	{
		$data = array();
		$data['cache_date'] = date('Y-m-d');					

		require_once(PATH_LIB.'analytics_panel/gapi.class.php');
		
		// Compile yesterday's stats
		$yesterday = new gapi($ga_user,$ga_password);
		$ga_auth_token = $yesterday->getAuthToken();
		$yesterday->requestReportData(
			$ga_profile_id,
			array('date'),
			array('pageviews','visits', 'timeOnSite'),
			'','',
			date('Y-m-d', strtotime('yesterday')),
			date('Y-m-d', strtotime('yesterday'))
		);
		
		// Get account data so we can store the profile info
		$data['profile'] = array();
		$yesterday->requestAccountData(1,100);
		foreach($yesterday->getResults() as $result)
		{
			if($result->getProfileId() == $ga_profile_id)
			{
				$data['profile']['id'] = $result->getProfileId();
				$data['profile']['title'] = $result->getTitle();
			}
		}					
		
		$data['yesterday']['visits'] = 
		number_format($yesterday->getVisits());
		
		$data['yesterday']['pageviews'] = 
		number_format($yesterday->getPageviews());
		
		$data['yesterday']['pages_per_visit'] = 
		$this->analytics_avg_pages($yesterday->getPageviews(), $yesterday->getVisits());
		
		$data['yesterday']['avg_visit'] = 
		$this->analytics_avg_visit($yesterday->getTimeOnSite(), $yesterday->getVisits());
		
		// Compile last month's stats
		$lastmonth = new gapi($ga_user,$ga_password,$ga_auth_token);
		$lastmonth->requestReportData($ga_profile_id,
			array('date'),
			array('pageviews','visits', 'newVisits', 'timeOnSite', 'bounces', 'entrances'),
			'date', '',
			date('Y-m-d', strtotime('31 days ago')),
			date('Y-m-d', strtotime('yesterday'))
		);
		
		$data['lastmonth']['date_span'] = 
		date('F jS Y', strtotime('31 days ago')).' &ndash; '.date('F jS Y', strtotime('yesterday'));
		
		$data['lastmonth']['visits'] = 
		number_format($lastmonth->getVisits());
		$data['lastmonth']['visits_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'visits');
		
		$data['lastmonth']['pageviews'] = 
		number_format($lastmonth->getPageviews());
		$data['lastmonth']['pageviews_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'pageviews');
		
		$data['lastmonth']['pages_per_visit'] = 
		$this->analytics_avg_pages($lastmonth->getPageviews(), $lastmonth->getVisits());
		$data['lastmonth']['pages_per_visit_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'avgpages');
		
		$data['lastmonth']['avg_visit'] = 
		$this->analytics_avg_visit($lastmonth->getTimeOnSite(), $lastmonth->getVisits());
		$data['lastmonth']['avg_visit_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'time');
		
		$data['lastmonth']['bounce_rate'] = 
		($lastmonth->getBounces() > 0 && $lastmonth->getBounces() > 0) ? 
		round( ($lastmonth->getBounces() / $lastmonth->getEntrances()) * 100, 2 ).'%' : '0%';
		$data['lastmonth']['bounce_rate_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'bouncerate');
		
		$data['lastmonth']['new_visits'] = 
		($lastmonth->getNewVisits() > 0 && $lastmonth->getVisits() > 0) ? 
		round( ($lastmonth->getNewVisits() / $lastmonth->getVisits()) * 100, 2).'%' : '0%';					
		$data['lastmonth']['new_visits_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'newvisits');

		// Compile last month's top content
		$topcontent = new gapi($ga_user,$ga_password,$ga_auth_token);
		$topcontent->requestReportData($ga_profile_id,
			array('hostname', 'pagePath'),
			array('pageviews'),
			'-pageviews', '',
			date('Y-m-d', strtotime('31 days ago')),
			date('Y-m-d', strtotime('yesterday')),
			null, 10
		);
		
		$data['lastmonth']['content'] = array();
		$i = 0;
		foreach($topcontent->getResults() as $result)
		{
			$data['lastmonth']['content'][$i]['title'] = 
			'<a href="http://'.$result->getHostname() . $result->getPagePath().'" target="_blank">'
			.$result->getPagePath().
			'</a>';
			$data['lastmonth']['content'][$i]['count'] = number_format($result->getPageviews());
			$i++;
		}
		
		// Compile last month's top referrers
		$referrers = new gapi($ga_user,$ga_password,$ga_auth_token);
		$referrers->requestReportData($ga_profile_id,
			array('source', 'referralPath', 'medium'),
			array('visits'),
			'-visits', '',
			date('Y-m-d', strtotime('31 days ago')),
			date('Y-m-d', strtotime('yesterday')),
			null, 10
		);
		
		$data['lastmonth']['referrers'] = array();
		$i = 0;
		foreach($referrers->getResults() as $result)
		{
			$data['lastmonth']['referrers'][$i]['title'] = 
			($result->getMedium() == 'referral') ?
			'<a href="http://'.$result->getSource() . $result->getReferralPath().'" target="_blank">'.$result->getSource().'</a>' : $result->getSource();
			$data['lastmonth']['referrers'][$i]['count'] = number_format($result->getVisits());
			$i++;
		}

		return $data;
	}
	
		
	function analytics_avg_pages($pageviews, $visits)
	{
		return ($pageviews > 0 && $visits > 0) ? round($pageviews / $visits, 2) : 0;
	}
	

	function analytics_avg_visit($seconds, $visits)
	{
		if($seconds > 0 && $visits > 0)
		{
			$avg_secs = $seconds / $visits;
			// This little snippet by Carson McDonald, from his Analytics Dashboard WP plugin
			$hours = floor($avg_secs / (60 * 60));
			$minutes = floor(($avg_secs - ($hours * 60 * 60)) / 60);
			$seconds = $avg_secs - ($minutes * 60) - ($hours * 60 * 60);
			return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
		}
		else
		{
			return '00:00:00';
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
		
		return '<img src="http://chart.apis.google.com/chart?cht=ls&amp;chs=100x20&amp;chm=B,e6f2fa,0,0.0,0.0&amp;chco=0077cc&amp;chd=t:'.$stats.'&amp;chds=0,'.$max.'" alt="" />';
	}		
			
   
	// --------------------------------
	//  Activate Extension
	// --------------------------------
	
	function activate_extension()
	{
	    global $DB;
	    
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
			        'settings'     => serialize($this->settings),
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

	    if ($current < '1.1')
	    {
			// Reset previous settings due to format change
			$settings = array('settings' => serialize(array()));
			$update = $DB->update_string('exp_extensions', $settings, "class = 'Analytics_panel'");
			$DB->query($update);
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