<?php 


require_once("guiconfig.inc");
require_once("/usr/local/pkg/snort/snort_new.inc");

// unset crsf checks
if(isset($_POST['__csrf_magic'])) 
{
  unset($_POST['__csrf_magic']);
}

// return codes
$snortJsonReturnCode_success = '{"snortgeneralsettings":"success"}';

$snortJsonReturnCode_fail = '{"snortgeneralsettings":"fail"}';

function snortJsonReturnCode($returnStatus)
{		
	if ($returnStatus == true)
	{
		echo '{"snortgeneralsettings":"success","snortUnhideTabs":"true"}';
	}else{
		echo '{"snortgeneralsettings":"fail"}';
	}		
}

// row from db by uuid
if ($_POST['snortSidRuleEdit'] == 1)
{
	
	unset($_POST['snortSidRuleEdit']);
	
	snortSidStringRuleEditGUI();
	
}

	
// row from db by uuid
if ($_POST['snortSaveRuleSets'] == 1)
{	

	if ($_POST['ifaceTab'] == 'snort_rulesets')
	{	
		// unset POSTs that are markers not in db
		unset($_POST['snortSaveRuleSets']);
		unset($_POST['ifaceTab']);		
		
		snortJsonReturnCode(snortSql_updateRuleSetList());

	}
	
	
	if ($_POST['ifaceTab'] == 'snort_rules')
	{	
		// unset POSTs that are markers not in db
		unset($_POST['snortSaveRuleSets']);
		unset($_POST['ifaceTab']);
		
		snortJsonReturnCode(snortSql_updateRuleSigList());
	}	
	
	
} // END of rulesSets	

// row from db by uuid
if ($_POST['RMlistDelRow'] == 1)
{
	
	//conf_mount_rw();
	
	if ($_POST['RMlistTable'] == 'Snortrules' || $_POST['RMlistTable'] == 'SnortSuppress')
	{
	  	if (snortSql_updatelistDelete($_POST['RMlistDB'], $_POST['RMlistTable'], 'uuid', $_POST['RMlistUuid']))
	  	{
	  		echo $snortJsonReturnCode_success;
			return true; 		
	  	}else{
			echo $snortJsonReturnCode_fail;
			return false; 		
	  	}
	}
	
	if ($_POST['RMlistTable'] == 'SnortWhitelist')
	{
		$fetchExtraWhitelistEntries = snortSql_fetchAllSettings($_POST['RMlistDB'], $_POST['RMlistTable'], 'uuid', $_POST['RMlistUuid']);
		
		if (snortSql_updatelistDelete($_POST['RMlistDB'], 'SnortWhitelistips', 'filename', $fetchExtraWhitelistEntries['filename']))
		{	
			snortSql_updatelistDelete($_POST['RMlistDB'], $_POST['RMlistTable'], 'uuid', $_POST['RMlistUuid']);
			
	  		echo $snortJsonReturnCode_success;
			return true; 		
	  		}else{
			echo $snortJsonReturnCode_fail;
			return false; 			
		}
	}	
	
	//conf_mount_ro();
	
}


// general settings save
if ($_POST['snortSaveSettings'] == 1) 
{
	
	// Save general settings
	if ($_POST['dbTable'] == 'SnortSettings')
	{
		 
		if ($_POST['ifaceTab'] == 'snort_interfaces_global')
		{    
			// checkboxes when set to off never get included in POST thus this code      
			$_POST['forcekeepsettings'] = ($_POST['forcekeepsettings'] == '' ? off : $_POST['forcekeepsettings']);		
		}
		      
		if ($_POST['ifaceTab'] == 'snort_alerts') 
		{
		        
			if (!isset($_POST['arefresh']))
				$_POST['arefresh'] = ($_POST['arefresh'] == '' ? off : $_POST['arefresh']);
			       		          
		}
		      
		if ($_POST['ifaceTab'] == 'snort_blocked') 
		{
		          
			if (!isset($_POST['brefresh']))
				$_POST['brefresh'] = ($_POST['brefresh'] == '' ? off : $_POST['brefresh']);          
		          
		}
		
		// unset POSTs that are markers not in db
		unset($_POST['snortSaveSettings']);
		unset($_POST['ifaceTab']);
		      
		// update date on every save
		$_POST['date'] = date(U);              
		          
		//print_r($_POST);
		//return true;
		    
		conf_mount_rw();
		snortSql_updateSettings($_POST['dbName'], $_POST, 'id', '1');
		conf_mount_ro();
		
		echo '
		{
		"snortgeneralsettings": "success"	
		}
		';
		return true;		
	      
	} // end of dbTable SnortSettings

    // Save rules settings
	if ($_POST['dbTable'] == 'Snortrules')
    {
    	
	    // snort interface edit
		if ($_POST['ifaceTab'] == 'snort_interfaces_edit') 
		{
		        
			if (!isset($_POST['enable']))
			 	$_POST['enable'] = ($_POST['enable'] == '' ? off : $_POST['enable']);
			          
			if (!isset($_POST['blockoffenders7']))
				$_POST['blockoffenders7'] = ($_POST['blockoffenders7'] == '' ? off : $_POST['blockoffenders7']);
	
			if (!isset($_POST['alertsystemlog']))
				$_POST['alertsystemlog'] = ($_POST['alertsystemlog'] == '' ? off : $_POST['alertsystemlog']);  
	
			if (!isset($_POST['tcpdumplog']))
				$_POST['tcpdumplog'] = ($_POST['tcpdumplog'] == '' ? off : $_POST['tcpdumplog']); 
	
			if (!isset($_POST['snortunifiedlog']))
				$_POST['snortunifiedlog'] = ($_POST['snortunifiedlog'] == '' ? off : $_POST['snortunifiedlog']);
				
			// convert textbox to base64
			$_POST['configpassthru'] = base64_encode($_POST['configpassthru']);
			
			/*
			 * make dir for the new iface
			 * may need to move this as a func to new_snort,inc
			 */			
			if (!is_dir('/usr/local/etc/snort/sn_' . $_POST['uuid'] . '_' . $_POST['interface']))
			{
				$newSnortDirCraete = 'mkdir -p /usr/local/etc/snort/sn_' . $_POST['uuid'] . '_' . $_POST['interface'];
				exec($newSnortDirCraete);
				// NOTE: code only works on php5
				$listRulesDir = snortScanDirFilter('/usr/local/etc/snort/rules', '.rules');
				if (!empty($listRulesDir) && file_exists('/usr/local/etc/snort/base_rules.tar.gz'))
				{	
					$newSnortDir = 'sn_' . $_POST['uuid'] . '_' . $_POST['interface'];					
					exec('/usr/bin/tar xvfz /usr/local/etc/snort/base_rules.tar.gz ' . '-C /usr/local/etc/snort/' . $newSnortDir);					
				}
			}
		          
		}		
		
		// snort preprocessor edit
		if ($_POST['ifaceTab'] == 'snort_preprocessors') 
		{

			if (!isset($_POST['dce_rpc_2']))
			 	$_POST['dce_rpc_2'] = ($_POST['dce_rpc_2'] == '' ? off : $_POST['dce_rpc_2']);
			 	
			if (!isset($_POST['dns_preprocessor']))
			 	$_POST['dns_preprocessor'] = ($_POST['dns_preprocessor'] == '' ? off : $_POST['dns_preprocessor']);
			 	
			if (!isset($_POST['ftp_preprocessor']))
			 	$_POST['ftp_preprocessor'] = ($_POST['ftp_preprocessor'] == '' ? off : $_POST['ftp_preprocessor']);
			 	
			if (!isset($_POST['http_inspect']))
			 	$_POST['http_inspect'] = ($_POST['http_inspect'] == '' ? off : $_POST['http_inspect']);
			 	
			if (!isset($_POST['other_preprocs']))
			 	$_POST['other_preprocs'] = ($_POST['other_preprocs'] == '' ? off : $_POST['other_preprocs']);
			 	
			if (!isset($_POST['perform_stat']))
			 	$_POST['perform_stat'] = ($_POST['perform_stat'] == '' ? off : $_POST['perform_stat']);
			 	
			if (!isset($_POST['sf_portscan']))
			 	$_POST['sf_portscan'] = ($_POST['sf_portscan'] == '' ? off : $_POST['sf_portscan']);
			 	
			if (!isset($_POST['smtp_preprocessor']))
			 	$_POST['smtp_preprocessor'] = ($_POST['smtp_preprocessor'] == '' ? off : $_POST['smtp_preprocessor']);			
			
		}

		// snort barnyard edit
		if ($_POST['ifaceTab'] == 'snort_barnyard') 
		{
			// make shure iface is lower case
			$_POST['interface'] = strtolower($_POST['interface']);
			
			if (!isset($_POST['barnyard_enable']))
			 	$_POST['barnyard_enable'] = ($_POST['barnyard_enable'] == '' ? off : $_POST['barnyard_enable']);	
			
		}
		
		
	      // unset POSTs that are markers not in db
	      unset($_POST['snortSaveSettings']);
	      unset($_POST['ifaceTab']);
	      
	      // update date on every save
	      $_POST['date'] = date(U);    
	          
	          
	      //print_r($_POST);
	      //return true;
	      
	      snortJsonReturnCode(snortSql_updateSettings($_POST['dbName'], $_POST, 'uuid', $_POST['uuid']));	      
      
    } // end of dbTable Snortrules
    		
			
} // STOP General Settings Save

// Suppress settings save
if ($_POST['snortSaveSuppresslist'] == 1) 
{

	// post for supress_edit	
	if ($_POST['ifaceTab'] == 'snort_interfaces_suppress_edit') 
	{
		
	    // make sure filename is valid  
		if (!is_validFileName($_POST['filename']))
		{
			echo 'Error: FileName';
			return false;
		}
		
		// unset POSTs that are markers not in db
		unset($_POST['snortSaveSuppresslist']);
		unset($_POST['ifaceTab']);
		
		// convert textbox to base64
		$_POST['suppresspassthru'] = base64_encode($_POST['suppresspassthru']);
		
		//conf_mount_rw();
		snortSql_updateSettings($_POST['dbName'], $_POST, 'uuid', $_POST['uuid']);
		//conf_mount_ro();		
		
		echo '
		{
		"snortgeneralsettings": "success" 
		}
		';
		return true;	  
	  
	}


	
}

// Whitelist settings save
if ($_POST['snortSaveWhitelist'] == 1) 
{

  if ($_POST['ifaceTab'] == 'snort_interfaces_whitelist_edit') {
        
		if (!is_validFileName($_POST['filename']))
		{
			echo 'Error: FileName';
			return false;
		}
        
          $_POST['wanips'] = ($_POST['wanips'] == '' ? off : $_POST['wanips']); 
          $_POST['wangateips'] = ($_POST['wangateips'] == '' ? off : $_POST['wangateips']);
          $_POST['wandnsips'] = ($_POST['wandnsips'] == '' ? off : $_POST['wandnsips']);
          $_POST['vips'] = ($_POST['vips'] == '' ? off : $_POST['vips']);
          $_POST['vpnips'] = ($_POST['vpnips'] == '' ? off : $_POST['vpnips']);  
 
  }
  
  // unset POSTs that are markers not in db
  unset($_POST['snortSaveWhitelist']);
  unset($_POST['ifaceTab']);

  $genSettings = $_POST;
  unset($genSettings['list']);
  
  $genSettings['date'] = date(U);
  
    //print_r($_POST);
    //return true;
  
  //conf_mount_rw();
  snortSql_updateSettings($_POST['dbName'], $genSettings, 'uuid', $genSettings['uuid']);
  if ($_POST['list'] != '')
  {
    snortSql_updateWhitelistIps($_POST['dbTable'], $_POST['list'], $genSettings['filename']);
  }
  //conf_mount_ro();
  
    echo '
    {
    "snortgeneralsettings": "success" 
    }
    ';
    return true;

}

// download code for alerts page
if ($_POST['snortlogsdownload'] == 1)
{
	conf_mount_rw();
	snort_downloadAllLogs();
	conf_mount_ro();

}

// download code for alerts page
if ($_POST['snortblockedlogsdownload'] == 1)
{
	conf_mount_rw();
	snort_downloadBlockedIPs();
	conf_mount_ro();

}


// code neeed to be worked on when finnished rules code
if ($_POST['snortlogsdelete'] == 1)
{
	
	conf_mount_rw();
	snortDeleteLogs();
	conf_mount_ro();
}

// flushes snort2c table
if ($_POST['snortflushpftable'] == 1)
{
	
	conf_mount_rw();
	snortRemoveBlockedIPs();
	conf_mount_ro();
}

// reset db reset_snortgeneralsettings
if ($_POST['reset_snortgeneralsettings'] == 1)
{

	conf_mount_rw();
	reset_snortgeneralsettings();
	conf_mount_ro();	
	
}


?>










