<?php
/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

require_once('modules/EMAILMaker/EMAILMakerUtils.php');
 
class EmailMaker extends CRMEntity {
  private $version_type;
  private $license_key;
  
  private $profilesActions;
  private $profilesPermissions;
 
  var $SelectModuleFields = array();
  var $ModuleFields = array();
  var $All_Related_Modules = array();
  var $Module_Related_Fields = array();
  var $Convert_RelatedModuleFields = array();
  var $Convert_ModuleFields = array();
  var $Convert_ModuleBlocks = array();
  
  var $log;
	var $db;	
	
//constructor of EMAILMaker class 	
	function __construct() {
		$this->log =LoggerManager::getLogger('account');
		$this->db = PearDatabase::getInstance();
		
		$this->setLicenseInfo();
		//$this->setPermissions();
        
        $this->profilesActions = array("EDIT"=>"EditView",        // Create/Edit
                                       "DETAIL"=>"DetailView",    // View
                                       "DELETE"=>"Delete",        // Delete                                      
                                       );
        $this->profilesPermissions = array(); 
  }

  public function actualizeSmarty($smarty)
  {
      global $current_user;
      
      $tool_buttons = Button_Check("EMAILMaker");
      $smarty->assign('CHECK', $tool_buttons);
      
      $is_drip_active = $this->controlActiveDelay();
      $smarty->assign("IS_DRIP_ACTIVE", $is_drip_active);  
      
      include_once("version.php");
      $version_type = $this->GetVersionType();
      $smarty->assign("VERSION_TYPE",$version_type);
      
      if (strtolower($version_type) != "professional")
         $smarty->assign("VERSION",ucfirst($version_type)." ".$version);
      else
         $smarty->assign("VERSION",$version);
         
      $license_key = $this->GetLicenseKey();
      $smarty->assign("LICENSE_KEY",$license_key);
      
      $category = getParentTab();
      $smarty->assign("CATEGORY",$category);
      
      if(is_admin($current_user)){
          $smarty->assign('IS_ADMIN','1');
      }
      
      return $smarty; 
  }

  public function controlActiveDelay()
  {
      $sql = "SELECT delay_active FROM vtiger_emakertemplates_delay";           
      $result = $this->db->query($sql);
    
      $drip_active = $this->db->query_result($result,0,"delay_active");  
      
      if ($drip_active == "1")
         return true;
      else
         return false;  
  }
  
	
//Getters and Setters
  public function GetVersionType() {
      return $this->version_type;
  } 	
	
  public function GetLicenseKey() {
      return $this->license_key;
  }

    
  public function GetProfilesActions() {
      return $this->profilesActions;
  } 
//PUBLIC METHODS SECTION
//ListView data 
  public function GetListviewData($show_module,$orderby = "templateid", $dir = "asc")
  {  
	global $current_user, $mod_strings, $app_strings;


    $status_sql="SELECT * FROM vtiger_emakertemplates_userstatus  
		             INNER JOIN vtiger_emakertemplates USING(templateid) 
		             WHERE userid=?"; 
	$status_res=$this->db->pquery($status_sql,array($current_user->id));
	$status_arr = array();
	while($status_row = $this->db->fetchByAssoc($status_res))
	{
		$status_arr[$status_row["templateid"]]["is_active"] = $status_row["is_active"];
		$status_arr[$status_row["templateid"]]["is_default"] = $status_row["is_default"];
        $status_arr[$status_row["templateid"]]["sequence"] = $status_row["sequence"];
	}

    $sql = "SELECT templateid, templatename, description, module, category FROM vtiger_emakertemplates "; 
    
    $where_array = array();
    if ($show_module != "all" && $show_module != "none")       
    {
        $sql .= "WHERE module = ?";
        $where_array = array($show_module);
    }  
    elseif ($show_module == "none") 
    {
        $sql .= "WHERE module = '' OR module IS NULL ";
    }  
        
    $sql .= "ORDER BY ".$orderby." ".$dir;
    $result = $this->db->pquery($sql, $where_array);

    $del="Del  ";
    $bar="  | ";
    $cnt=1;
    
    $return_data = Array();
    $num_rows = $this->db->num_rows($result);
    
    for($i=0;$i < $num_rows; $i++)
    {	
        $templateid = $this->db->query_result($result,$i,'templateid');
        $currModule = $this->db->query_result($result,$i,'module');
        
        if($this->CheckTemplatePermissions($currModule, $templateid, false) === false)
                continue;
        
        $emailtemplatearray=array();
        $suffix="";
			
        if(isset($status_arr[$templateid]))
        {
        	if($status_arr[$templateid]["is_active"]=="0")
        		$emailtemplatearray['status']=0;
        	else
        	{
        		$emailtemplatearray['status']=1;
                switch($status_arr[$templateid]["is_default"])
                {
                    case "1":
                        $suffix=" (".$mod_strings["LBL_DEFAULT_NOPAR"]." ".$mod_strings["LBL_FOR_DV"].")";
                        break;
                        
                    case "2":
                        $suffix=" (".$mod_strings["LBL_DEFAULT_NOPAR"]." ".$mod_strings["LBL_FOR_LV"].")";
                        break;
                        
                    case "3":
                        $suffix=" (".$mod_strings["LBL_DEFAULT_NOPAR"].")";
                        break;
                }
        	}
        	
        	$emailtemplatearray['order'] = $status_arr[$templateid]["sequence"];
        }
        else
        {
            $emailtemplatearray['status']=1;
            $emailtemplatearray['order']=1;
        }
        
        $emailtemplatearray['status_lbl'] = ($emailtemplatearray['status']==1 ? $app_strings["Active"] : $app_strings["Inactive"]);  
			
        
        $emailtemplatearray['templateid'] = $templateid;
        $emailtemplatearray['description'] = $this->db->query_result($result,$i,'description');
        $emailtemplatearray['category'] = $this->db->query_result($result,$i,'category');
        $emailtemplatearray['module'] = getTranslatedString($this->db->query_result($result,$i,'module'));
        $emailtemplatearray['templatename'] = "<a href=\"index.php?action=DetailViewEmailTemplate&module=EMAILMaker&templateid=".$templateid."&parenttab=Tools\">".$this->db->query_result($result,$i,'templatename').$suffix."</a>";
        if($this->CheckPermissions("EDIT"))
        {
          $emailtemplatearray['edit'] = "<a href=\"index.php?action=EditEmailTemplate&module=EMAILMaker&templateid=".$templateid."&parenttab=Tools\">".$app_strings["LBL_EDIT_BUTTON"]."</a> | "
                                       ."<a href=\"index.php?action=EditEmailTemplate&module=EMAILMaker&templateid=".$templateid."&isDuplicate=true&parenttab=Tools\">".$app_strings["LBL_DUPLICATE_BUTTON"]."</a>";
        }
        $return_data []= $emailtemplatearray;	
    }
    
    return $return_data;
  }
  
  public function GetListviewModules($return_data)
  {  
	  global $current_user, $mod_strings, $app_strings;

    $sql = "SELECT templateid, templatename, description, module 
            FROM vtiger_emakertemplates 
            WHERE module IS NOT NULL and module != '' 
            ORDER BY module ASC";
    $result = $this->db->pquery($sql, array());
    $num_rows = $this->db->num_rows($result);
    
    for($i=0;$i < $num_rows; $i++)
    {	
      $templateid = $this->db->query_result($result,$i,'templateid');
      $currModule = $this->db->query_result($result,$i,'module');
      
      if($this->CheckTemplatePermissions($currModule, $templateid, false) === false)
                continue;
      
      if (!isset($return_data[$currModule])) $return_data[$currModule] = getTranslatedString($this->db->query_result($result,$i,'module'));
    }
    
    return $return_data;
  }
  
  public function DeleteAllRefLinks() {
    require_once('vtlib/Vtiger/Link.php');            
    $link_res = $this->db->pquery("SELECT tabid FROM vtiger_tab", array());
    while($link_row = $this->db->fetchByAssoc($link_res)) {
      Vtiger_Link::deleteLink($link_row["tabid"], "DETAILVIEWWIDGET", "EMAILMaker");
      Vtiger_Link::deleteLink($link_row["tabid"], "LISTVIEWBASIC", "Send Email", 'getEMAILListViewPopup(this,\'$MODULE$\',\'1\');');   
      Vtiger_Link::deleteLink($link_row["tabid"], "LISTVIEWBASIC", "Send Email", 'getEMAILListViewPopup(this,\'$MODULE$\',\'2\');');        
    }
  }
  
  public function AddLinks($tabid,$modulename) {
    require_once('vtlib/Vtiger/Module.php');

    $link_module = Vtiger_Module::getInstance($modulename);
    
    $link_module->addLink('DETAILVIEWWIDGET','EMAILMaker','module=EMAILMaker&action=EMAILMakerAjax&file=getEMAILActions&record=$RECORD$','themes/images/actionGenerateInvoice.gif');
    
    if ($tabid == "4" || $tabid == "6" || $tabid == "7" || $tabid == "18")
    {
        $link_module->addLink('LISTVIEWBASIC','Send Email','getEMAILListViewPopup(this,\'$MODULE$\',\'1\');');
    }
    else
    {
        $link_module->addLink('LISTVIEWBASIC','Send Email','getEMAILListViewPopup(this,\'$MODULE$\',\'2\');');
    }
  }

  public function AddHeaderLink() {

    $sql = "SELECT * FROM vtiger_links WHERE linktype = 'HEADERSCRIPT' AND linkurl = 'modules/EMAILMaker/EMAILMakerActions.js'";
    $result = $this->db->query($sql);
    $num_rows = $this->db->num_rows($result); 
    
    if ($num_rows == 0)
    {
        require_once('vtlib/Vtiger/Module.php');
        $link_module = Vtiger_Module::getInstance("EmailMaker");        
        $link_module->addLink('HEADERSCRIPT','EMAILMakerJS','modules/EMAILMaker/EMAILMakerActions.js', "", "1");
    }
  }

  public function actualizeLinks() 
  { 
    $sql = "SELECT * FROM vtiger_links WHERE ((linktype = 'LISTVIEWBASIC' AND linkurl LIKE 'getEMAILListViewPopup(this,%') OR (linktype = 'DETAILVIEWWIDGET' AND linkurl LIKE 'module=EMAILMaker&action=EMAILMakerAjax&file=getEMAILActions&record=%'))";
    $result = $this->db->query($sql);
    $num_rows = $this->db->num_rows($result); 
    
    if ($num_rows == 0)
    {
        $sql2 = "SELECT * FROM vtiger_tab WHERE isentitytype=1 AND tabid NOT IN (9, 10, 16, 28) AND name != 'EMAILMaker'";
        $result2 = $this->db->query($sql2);
        while($row2 = $this->db->fetchByAssoc($result2))
        {
            $this->AddLinks($row2['tabid'],$row2['name']);
        }
    } 
    
    $this->AddHeaderLink();
  }
  
  
  public function convertOldTemplates()
  {
    $this->actualizeSeqTables();
    
    $sql = "SELECT * FROM vtiger_emailtemplates where deleted = ?";
    $res = $this->db->pquery($sql,array("0"));
    $permissions = array();
    while($row = $this->db->fetchByAssoc($res))
    {
        if (isset($row["module"]) && $row["module"] != "")
        {
            $templateid = $this->db->getUniqueID('vtiger_emakertemplates');
            $template_description = html_entity_decode($row["description"],ENT_COMPAT,'UTF-8');
            $template_subject = html_entity_decode($row["subject"],ENT_COMPAT,'UTF-8');
            $template_body = html_entity_decode($row["body"],ENT_COMPAT,'UTF-8');
            
        	$sql2 = "insert into vtiger_emakertemplates (templatename,module,description,subject,body,deleted,templateid) values (?,?,?,?,?,?,?)";
	        $params2 = array($row["templatename"], $row["module"], $template_description, $template_subject, $template_body, 0, $templateid);
	        $this->db->pquery($sql2, $params2);
            
            $sql3 = "update vtiger_emailtemplates set deleted =? where templateid =?";
        	$params3 = array("1", $row["templateid"]);
        	$this->db->pquery($sql3, $params3);
        }
    }
  }
  
  public function removeLinks() {
	require_once('vtlib/Vtiger/Link.php');

	$tabid = getTabId("EMAILMaker");		
    Vtiger_Link::deleteAll($tabid);
	$this->DeleteAllRefLinks();
  }
      
  
//PRIVATE METHODS SECTION   
  private function setLicenseInfo()
  {    
    $sql = "SELECT version_type, license_key FROM vtiger_emakertemplates_license";
    $result = $this->db->query($sql);
    if($this->db->num_rows($result) > 0) {
      $this->version_type = $this->db->query_result($result,0,"version_type");
      $this->license_key = $this->db->query_result($result,0,"license_key");
    }
    else {
      $this->version_type = "";
      $this->license_key = "";
    } 
  
  }
  /*
  private function setPermissions()
  {
    global $currentModule;  
    if(isPermitted($currentModule,"EditView") == 'yes' && $this->version_type != "deactivate") {    	
      $this->isEditable = true;      
    }
    else {
      $this->isEditable = false;
    }
    
    if(isPermitted($currentModule,"Delete") == 'yes' && $this->version_type != "deactivate") {    	
      $this->isDeletable = true;      
    }
    else {
      $this->isDeletable = false;
    }
  }
  */
    function vtlib_handler($modulename, $event_type) {
        global $dbconfig;
        if($event_type == 'module.postinstall') {
        		$this->executeSql();
        	} 
        else if($event_type == 'module.preupdate') 
        {     
            //$result = $this->db->query("SHOW TABLES FROM ".$dbconfig['db_name']);
            $result = $this->db->query("SHOW TABLES FROM `".$dbconfig['db_name']."`");
            while($row = $this->db->fetchByAssoc($result))
            {
                 $table = $row["tables_in_".$dbconfig['db_name']];
            
                 if ($table == "vtiger_emailtemplates_ignorepicklistvalues")
                 {
                     $this->db->query("RENAME TABLE vtiger_emailtemplates_ignorepicklistvalues TO vtiger_emakertemplates_ignorepicklistvalues");
                 }
                 elseif ($table == "vtiger_emailtemplates_settings")
                 {
                     $this->db->query("RENAME TABLE vtiger_emailtemplates_settings TO vtiger_emakertemplates_settings");
                 }
            }
        }    
        else if($event_type == 'module.postupdate') { 
    		$this->convertOldTemplates();  
            
            $this->actualizeLinks();    
    	} 
        else if($event_type == 'module.preuninstall') {
          $this->removeLinks();
        }
        else if($event_type == 'module.disabled') {
           // TODO Handle actions when this module is disabled.
           $this->removeLinks();
        } 
        else if($event_type == 'module.enabled') {
           // TODO Handle actions when this module is enabled.
           $this->actualizeLinks();
        }
    }
	
    
    
  public function actualizeSeqTables() {
		
        if($this->db->num_rows($this->db->query("SELECT id FROM vtiger_emakertemplates_drips_seq"))<1) {
		  $this->db->query("INSERT INTO vtiger_emakertemplates_drips_seq VALUES ('0')");
		}
        
        if($this->db->num_rows($this->db->query("SELECT id FROM vtiger_emakertemplates_drip_groups_seq"))<1) {
		  $this->db->query("INSERT INTO vtiger_emakertemplates_drip_groups_seq VALUES ('0')");
		}
        
        if($this->db->num_rows($this->db->query("SELECT id FROM vtiger_emakertemplates_drip_tpls_seq"))<1) {
		  $this->db->query("INSERT INTO vtiger_emakertemplates_drip_tpls_seq VALUES ('0')");
		}
        
        if($this->db->num_rows($this->db->query("SELECT id FROM vtiger_emakertemplates_seq"))<1) {
		  $this->db->query("INSERT INTO vtiger_emakertemplates_seq VALUES ('0')");
		}
        
        if($this->db->num_rows($this->db->query("SELECT delay_active FROM vtiger_emakertemplates_delay"))<1) {
		  $this->db->query("INSERT INTO vtiger_emakertemplates_delay VALUES ('0')");
		}
        
        if($this->db->num_rows($this->db->query("SELECT id FROM vtiger_emakertemplates_relblocks_seq"))<1) {
		  $this->db->query("INSERT INTO vtiger_emakertemplates_relblocks_seq VALUES ('0')");
		}
  }
  
  public function executeSql() {
  
        $this->actualizeSeqTables();
   		
		$productblocData="INSERT INTO `vtiger_emakertemplates_productbloc_tpl` (`id`, `name`, `body`) VALUES
		              (1, 'product block for individual tax', 0x3c7461626c6520626f726465723d2231222063656c6c70616464696e673d2233222063656c6c73706163696e673d223022207374796c653d22666f6e742d73697a653a313070783b222077696474683d2231303025223e0d0a093c74626f64793e0d0a09093c7472206267636f6c6f723d2223633063306330223e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e506f733c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c746420636f6c7370616e3d223222207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f517479253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e546578743c2f7370616e3e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4c4953545f5052494345253c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f5355425f544f54414c253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f446973636f756e74253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4e45545f5052494345253c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f54617825202825293c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f546178253c2f7374726f6e673e20283c7374726f6e673e2443555252454e4359434f4445243c2f7374726f6e673e293c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e254d5f546f74616c253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f5354415254233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b20766572746963616c2d616c69676e3a20746f703b223e0d0a090909092450524f44554354504f534954494f4e243c2f74643e0d0a0909093c746420616c69676e3d227269676874222076616c69676e3d22746f70223e0d0a090909092450524f445543545155414e54495459243c2f74643e0d0a0909093c746420616c69676e3d226c65667422207374796c653d22544558542d414c49474e3a2063656e746572222076616c69676e3d22746f70223e0d0a090909092450524f445543545553414745554e4954243c2f74643e0d0a0909093c746420616c69676e3d226c656674222076616c69676e3d22746f70223e0d0a090909092450524f445543544e414d45243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f445543544c4953545052494345243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435453544f54414c4146544552444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415450455243454e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435456415453554d243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c53554d243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223131223e0d0a090909092350524f44554354424c4f435f454e44233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f544f54414c253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c574954484f5554564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f446973636f756e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c444953434f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f4e45545f544f54414c253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c4146544552444953434f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a0909090925475f54617825202456415450455243454e542420252025475f4c424c5f4c4953545f4f46252024544f54414c4146544552444953434f554e54243c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a0909090924564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a09090909546f74616c2077697468205441583c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a0909090924544f54414c57495448564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a0909090925475f4c424c5f5348495050494e475f414e445f48414e444c494e475f43484152474553253c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a09090909245348544158414d4f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f5441585f464f525f5348495050494e475f414e445f48414e444c494e47253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909245348544158544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f41646a7573746d656e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909092441444a5553544d454e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d22313022207374796c653d22544558542d414c49474e3a206c656674223e0d0a090909093c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e25475f4c424c5f4752414e445f544f54414c25203c2f7370616e3e3c7374726f6e673e282443555252454e4359434f444524293c2f7374726f6e673e3c2f74643e0d0a0909093c7464206e6f777261703d226e6f7772617022207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7374726f6e673e24544f54414c243c2f7374726f6e673e3c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e),
		              (2, 'product block for group tax', 0x3c7461626c6520626f726465723d2231222063656c6c70616464696e673d2233222063656c6c73706163696e673d223022207374796c653d22666f6e742d73697a653a313070783b222077696474683d2231303025223e0d0a093c74626f64793e0d0a09093c7472206267636f6c6f723d2223633063306330223e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e506f733c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c746420636f6c7370616e3d223222207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f517479253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e546578743c2f7370616e3e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4c4953545f5052494345253c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f5355425f544f54414c253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f446973636f756e74253c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a2063656e746572223e0d0a090909093c7370616e3e3c7374726f6e673e25475f4c424c5f4e45545f5052494345253c6272202f3e0d0a090909093c2f7374726f6e673e3c2f7370616e3e3c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d2238223e0d0a090909092350524f44554354424c4f435f5354415254233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2063656e7465723b20766572746963616c2d616c69676e3a20746f703b223e0d0a090909092450524f44554354504f534954494f4e243c2f74643e0d0a0909093c746420616c69676e3d227269676874222076616c69676e3d22746f70223e0d0a090909092450524f445543545155414e54495459243c2f74643e0d0a0909093c746420616c69676e3d226c65667422207374796c653d22544558542d414c49474e3a2063656e746572222076616c69676e3d22746f70223e0d0a090909092450524f445543545553414745554e4954243c2f74643e0d0a0909093c746420616c69676e3d226c656674222076616c69676e3d22746f70223e0d0a090909092450524f445543544e414d45243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f445543544c4953545052494345243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354544f54414c243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22544558542d414c49474e3a207269676874222076616c69676e3d22746f70223e0d0a090909092450524f44554354444953434f554e54243c2f74643e0d0a0909093c746420616c69676e3d22726967687422207374796c653d22746578742d616c69676e3a2072696768743b222076616c69676e3d22746f70223e0d0a090909092450524f4455435453544f54414c4146544552444953434f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d2238223e0d0a090909092350524f44554354424c4f435f454e44233c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f544f54414c253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c574954484f5554564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f446973636f756e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c444953434f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f4e45545f544f54414c253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a0909090924544f54414c4146544552444953434f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a0909090925475f54617825202456415450455243454e542420252025475f4c424c5f4c4953545f4f46252024544f54414c4146544552444953434f554e54243c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a0909090924564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a09090909546f74616c2077697468205441583c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a0909090924544f54414c57495448564154243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22746578742d616c69676e3a206c6566743b223e0d0a0909090925475f4c424c5f5348495050494e475f414e445f48414e444c494e475f43484152474553253c2f74643e0d0a0909093c7464207374796c653d22746578742d616c69676e3a2072696768743b223e0d0a09090909245348544158414d4f554e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f4c424c5f5441585f464f525f5348495050494e475f414e445f48414e444c494e47253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a09090909245348544158544f54414c243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a0909090925475f41646a7573746d656e74253c2f74643e0d0a0909093c7464207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909092441444a5553544d454e54243c2f74643e0d0a09093c2f74723e0d0a09093c74723e0d0a0909093c746420636f6c7370616e3d223722207374796c653d22544558542d414c49474e3a206c656674223e0d0a090909093c7370616e207374796c653d22666f6e742d7765696768743a20626f6c643b223e25475f4c424c5f4752414e445f544f54414c25203c2f7370616e3e3c7374726f6e673e282443555252454e4359434f444524293c2f7374726f6e673e3c2f74643e0d0a0909093c7464206e6f777261703d226e6f7772617022207374796c653d22544558542d414c49474e3a207269676874223e0d0a090909093c7374726f6e673e24544f54414c243c2f7374726f6e673e3c2f74643e0d0a09093c2f74723e0d0a093c2f74626f64793e0d0a3c2f7461626c653e)";
		
		$this->db->query($productblocData);

        //$this->actualizeLinks();   
	}
    
    //Method for getting the array of profiles permissions to PDFMaker actions.
    public function GetProfilesPermissions()
    {
        if(count($this->profilesPermissions) == 0)
        {
            $profiles = getAllProfileInfo();
            $sql = "SELECT * FROM vtiger_emakertemplates_profilespermissions";
            $res = $this->db->query($sql);
            $permissions = array();
            while($row = $this->db->fetchByAssoc($res))
            {
            //      in case that profile has been deleted we need to set permission only for active profiles
                if(isset($profiles[$row["profileid"]]))
                    $permissions[$row["profileid"]][$row["operation"]] = $row["permissions"];
            }

            foreach($profiles as $profileid=>$profilename)
            {
                foreach($this->profilesActions as $actionName)
                {
                    $actionId = getActionid($actionName);
                    if(!isset($permissions[$profileid][$actionId]))
                    {
                        $permissions[$profileid][$actionId] = "0";
                    }
                }
            }

            ksort($permissions);
            $this->profilesPermissions = $permissions;
        }

        return $this->profilesPermissions;
    }

    //Method for checking the permissions, whether the user has privilegies to perform specific action on PDF Maker.
    public function CheckPermissions($actionKey)
    {
        global $current_user;
        $profileid = fetchUserProfileId($current_user->id);
        $result = false;

        if(isset($this->profilesActions[$actionKey]))
        {
            $actionid = getActionid($this->profilesActions[$actionKey]);
            $permissions = $this->GetProfilesPermissions();

            if( isset($permissions[$profileid][$actionid]) && $permissions[$profileid][$actionid] == "0" )
                $result = true;
        }

        return $result;
    }
    
    public function DieDuePermission()
	{
        global $current_user, $app_strings;
        if(isset($_SESSION['vtiger_authenticated_user_theme']) && $_SESSION['vtiger_authenticated_user_theme'] != '')
        	$theme = $_SESSION['vtiger_authenticated_user_theme'];
        else
        {
        	if(!empty($current_user->theme)) {
        		$theme = $current_user->theme;
        	} else {
        		$theme = $default_theme;
        	}
        }

        $output = "<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>";
      	$output .= "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
      	$output .= "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>
      		<table border='0' cellpadding='5' cellspacing='0' width='98%'>
      		<tbody><tr>
      		<td rowspan='2' width='11%'><img src='". vtiger_imageurl('denied.gif', $theme) . "' ></td>
      		<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>$app_strings[LBL_PERMISSION]</span></td>
      		</tr>
      		<tr>
      		<td class='small' align='right' nowrap='nowrap'>
      		<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br></td>
      		</tr>
      		</tbody></table>
      		</div>";
      	$output .= "</td></tr></table>";
      	die($output);
    }
    
    public function CheckSharing($templateid,$type = "template")
    {
        global $current_user;

        //  if this template belongs to current user
        if ($type == "template")
        {
            $sql = "SELECT owner, sharingtype FROM vtiger_emakertemplates WHERE templateid = ?";
        }
        else
        {
            $sql = "SELECT owner, sharingtype FROM vtiger_emakertemplates_drips WHERE dripid = ?";
        }
        $result = $this->db->pquery($sql, array($templateid));
        $row = $this->db->fetchByAssoc($result);

        $owner = $row["owner"];
        $sharingtype = $row["sharingtype"];

        $result = false;
        if($owner == $current_user->id)
        {
            $result = true;
        }
        else
        {
            if ($sharingtype == "") $sharingtype = "public"; 
            
            switch($sharingtype)
            {
                //available for all
                case "public":
                    $result = true;
                    break;
                //available only for superordinate users of template owner, so we get list of all subordinate users of the current user and if template
                //owner is one of them then template is available for current user
                case "private":
                    $subordinateUsers = $this->getSubRoleUserIds($current_user->roleid);
                    if(!empty($subordinateUsers) && count($subordinateUsers) > 0)
                        $result = in_array($owner, $subordinateUsers);
                    else
                        $result = false;
                    break;
                //available only for those that are in share list
                case "share":
                    $subordinateUsers = $this->getSubRoleUserIds($current_user->roleid);
                    if(!empty($subordinateUsers) && count($subordinateUsers) > 0 && in_array($owner, $subordinateUsers))
                        $result = true;
                    else
                    {
                        $member_array = $this->GetSharingMemberArray($templateid,$type);
                        
                        if(isset($member_array["users"]) && in_array($current_user->id, $member_array["users"]))
                            $result = true;
                        elseif(isset($member_array["roles"]) && in_array($current_user->roleid, $member_array["roles"]))
                            $result = true;
                        else
                        {
                            if(isset($member_array["rs"]))
                            {
                                foreach($member_array["rs"] as $roleid)
                                {
                                    $roleAndsubordinateRoles = getRoleAndSubordinatesRoleIds($roleid);
                                    if(in_array($current_user->roleid, $roleAndsubordinateRoles))
                                    {
                                        $result = true;
                                        break;
                                    }
                                }
                            }

                            if($result == false && isset($member_array["groups"]))
                            {
                                $current_user_groups = explode(",", fetchUserGroupids($current_user->id));
                                $res_array = array_intersect($member_array["groups"], $current_user_groups);
                                if(!empty($res_array) && count($res_array) > 0)
                                    $result = true;
                                else
                                    $result = false;
                            }
                        }
                    }
                    break;
            }
        }

        return $result;
    }

    private function getSubRoleUserIds($roleid)
    {
        $subRoleUserIds = array();
        $subordinateUsers = getRoleAndSubordinateUserIds($roleid);
        if(!empty($subordinateUsers) && count($subordinateUsers) > 0) {
            $currRoleUserIds = getRoleUserIds($roleid);
            $subRoleUserIds = array_diff($subordinateUsers, $currRoleUserIds);
        }
        
        return $subRoleUserIds;
    }
    
    public function GetSharingMemberArray($templateid,$type = "template")
    {
        if($type == "template")
            $sql = "SELECT shareid, setype FROM vtiger_emakertemplates_sharing WHERE templateid = ? ORDER BY setype ASC";
        else
            $sql = "SELECT shareid, setype FROM vtiger_emakertemplates_sharing_drip WHERE dripid = ? ORDER BY setype ASC";
        
        $result = $this->db->pquery($sql, array($templateid));
        $memberArray = array();
        while($row = $this->db->fetchByAssoc($result))
        {
            $memberArray[$row["setype"]][] = $row["shareid"];
        }

        return $memberArray;
    }
    
    public function CheckTemplatePermissions($selected_module, $templateid, $die = true)
    {
        $result = $this->CheckRecordPermissions($selected_module, $templateid, "template", $die);
                     
        return $result;        
    }
    
    public function CheckDripPermissions($selected_module, $templateid, $die = true)
    {
        $result = $this->CheckRecordPermissions($selected_module, $templateid, "drip", $die); 
                       
        return $result;        
    }
    
    public function CheckRecordPermissions($selected_module, $recordid, $type, $die)
    {
        $result = true;
        if($selected_module != "" && isPermitted($selected_module, '') != "yes") {
            $result = false;
        }
        elseif($recordid != "" && $this->CheckSharing($recordid,$type) === false) {
            $result = false;
            
        }
        
        if($die === true && $result === false) {
            $this->DieDuePermission();
        }

        return $result;
    }
    
    public function GetAvailableTemplates($currModule,$show_none = false)
    {
        global $current_user,$app_strings;
 
        if ($show_none)
            $return_array = array(""=>$app_strings["LBL_NONE_NO_LINE"]); 
        else
            $return_array = array();
 
        $params = array($currModule);
        
        $sql1 = "SELECT templateid, templatename, description, category,
        CASE WHEN is_active IS NULL THEN '1' ELSE is_active END,
        CASE WHEN is_default IS NULL THEN '0' ELSE is_default END,
        CASE WHEN sequence IS NULL THEN '1' ELSE sequence END
        FROM vtiger_emakertemplates  
        LEFT JOIN vtiger_emakertemplates_userstatus USING ( templateid )
        WHERE module = ? AND category != '' AND (is_active=1 OR is_active IS NULL) ORDER BY category ASC, templatename ASC";
        $result1 = $this->db->pquery($sql1, $params);
        $num_rows1 = $this->db->num_rows($result1);
        
        if ($num_rows1 > 0)
        {
            while($row = $this->db->fetchByAssoc($result1))
            {
                if($this->CheckTemplatePermissions($currModule, $row["templateid"], false) == false)
                    continue;
                
                $return_array[$row['category']][$row['templateid']] = $row['templatename'];    
            }
        }
        
        $sql2 = "SELECT templateid, templatename, description, category, 
        CASE WHEN is_active IS NULL THEN '1' ELSE is_active END,
        CASE WHEN is_default IS NULL THEN '0' ELSE is_default END,
        CASE WHEN sequence IS NULL THEN '1' ELSE sequence END
        FROM vtiger_emakertemplates  
        LEFT JOIN vtiger_emakertemplates_userstatus USING ( templateid )
        WHERE module = ? AND (category = '' OR category IS NULL) AND (is_active=1 OR is_active IS NULL) ORDER BY templatename ASC";
        $result2 = $this->db->pquery($sql2, $params);
        $num_rows2 = $this->db->num_rows($result2);
        
        if ($num_rows2 > 0)
        {
            while($row = $this->db->fetchByAssoc($result2))
            {
                if($this->CheckTemplatePermissions($currModule, $row["templateid"], false) == false)
                    continue;
                
                $return_array[$row['templateid']] = $row['templatename'];    
            }
        }
        
        $is_drip_active = $this->controlActiveDelay();
        if ($is_drip_active)
        {
            $drip_category = "Drip";
            
            $sql3 = "SELECT dripid, dripname FROM vtiger_emakertemplates_drips WHERE deleted = '0' AND (module IS NULL OR module = '' OR module = ?) ORDER BY dripid ASC";
            $result3 = $this->db->pquery($sql3, array($currModule));
            $num_rows3 = $this->db->num_rows($result3);
            
            if ($num_rows3 > 0)
            {
                while($row = $this->db->fetchByAssoc($result3))
                {
                    if($this->CheckDripPermissions($currModule, $row['dripid'], false) === false)
                            continue;
                            
                    $return_array[$drip_category]["drip_".$row['dripid']] = $row['dripname'];        
                }
            }
        }
        return $return_array;
    }
    
    function createModuleFields($module,$tabid)
    {
        global $adb; 
        
        $sql1 = "SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid=".$tabid." ORDER BY sequence ASC";
    	$res1 = $adb->query($sql1);
    	$block_info_arr = array();
    	while($row = $adb->fetch_array($res1))
    	{
     		$sql2 = "SELECT vtiger_tab.name AS module, fieldid, uitype, fieldlabel, columnname FROM vtiger_field 
                     INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE block=".$row['blockid']." and (displaytype != 3 OR uitype = 55) and fieldid != '195' ORDER BY sequence ASC";
     	    $res2 = $adb->query($sql2);
     	    $num_rows2 = $adb->num_rows($res2);  
     	    
     	    if ($num_rows2 > 0)
     	    {
        	    $field_id_array = array();
              
            	while($row2 = $adb->fetch_array($res2))
            	{
                	$F_Related_Modules = array();
                    $field_id_array[] = $row2['fieldid'];
    
                	switch ($row2['uitype'])
                	{
                    	case "51": $F_Related_Modules[] = "Accounts"; break;
                    	case "57": $F_Related_Modules[] = "Contacts"; break;
                    	case "58": $F_Related_Modules[] = "Campaigns"; break;
                    	case "59": $F_Related_Modules[] = "Products"; break;
                    	case "73": $F_Related_Modules[] = "Accounts"; break;
                    	case "75": $F_Related_Modules[] = "Vendors"; break;
                    	case "81": $F_Related_Modules[] = "Vendors"; break;
                    	case "76": $F_Related_Modules[] = "Potentials"; break;
                    	case "78": $F_Related_Modules[] = "Quotes"; break;
                    	case "80": $F_Related_Modules[] = "SalesOrder"; break;
                    	case "68": $F_Related_Modules[] = "Accounts"; $F_Related_Modules[] = "Contacts"; break;
                    	case "10": $fmrs=$adb->query('select relmodule from vtiger_fieldmodulerel where fieldid='.$row2['fieldid']);
                                while ($rm=$adb->fetch_array($fmrs)) { 
                                    $F_Related_Modules[] = $rm['relmodule'];
                                }                  
                    		break;
                	} 
                    
                    if (count($F_Related_Modules) > 0)
                    {
                        $fieldlabel = getTranslatedString($row2['fieldlabel'],$row2['module']);
                        if($fieldlabel == "") $fieldlabel = $row2['fieldlabel'];
                        
                        $fieldname = $row2['columnname'];
    
                        foreach ($F_Related_Modules AS $r_module)
                        {
                            $r_module_label = getTranslatedString($r_module);
                            if ($r_module_label == "") $r_module_label = $r_module;      
                           
                            $this->All_Related_Modules[$module][] = array("module"=>$r_module,"modulelabel"=>$r_module_label,"fieldlabel"=>$fieldlabel,"fieldname"=>$fieldname);
                        
                            if (!isset($this->Module_Related_Fields[$module][$fieldname])) 
                            {                      
                                $fieldlabel = str_replace("'", "\'", $fieldlabel); // ITS4YOU-UP SlOl 
                                $this->Module_Related_Fields[$module][$fieldname]["name"] = $fieldlabel; 
                            }  
                            $this->Module_Related_Fields[$module][$fieldname]["modules"][$r_module] = $r_module_label; 
                        }
                    }
                    
            	}
              
            	$block_info_arr[$row['blocklabel']] = $field_id_array;
        	}
    	}
      
      
    	if ($module == "Quotes" || $module == "Invoice" || $module == "SalesOrder" || $module=="PurchaseOrder" || $module=="Issuecards" || $module=="Receiptcards" || $module=="Creditnote" || $module=="StornoInvoice")
        	$block_info_arr["LBL_DETAILS_BLOCK"] = array();
      
    	$this->ModuleFields[$module] = $block_info_arr;
    }
    
    function convertModuleFields()
    {
        global $default_language,$current_language,$adb,$mod_strings;
        
        $More_Fields = array("CURRENCYNAME"=>$mod_strings["LBL_CURRENCY_NAME"],
                         "CURRENCYSYMBOL"=>$mod_strings["LBL_CURRENCY_SYMBOL"],
                         "CURRENCYCODE"=>$mod_strings["LBL_CURRENCY_CODE"],
                         "TOTALWITHOUTVAT"=>$mod_strings["LBL_VARIABLE_SUMWITHOUTVAT"],
                         "TOTALDISCOUNT"=>$mod_strings["LBL_VARIABLE_TOTALDISCOUNT"],
                         "TOTALDISCOUNTPERCENT"=>$mod_strings["LBL_VARIABLE_TOTALDISCOUNT_PERCENT"],
                         "TOTALAFTERDISCOUNT"=>$mod_strings["LBL_VARIABLE_TOTALAFTERDISCOUNT"],
                         "VAT"=>$mod_strings["LBL_VARIABLE_VAT"],
                         "VATPERCENT"=>$mod_strings["LBL_VARIABLE_VAT_PERCENT"],
                         "VATBLOCK"=>$mod_strings["LBL_VARIABLE_VAT_BLOCK"],
                         "TOTALWITHVAT"=>$mod_strings["LBL_VARIABLE_SUMWITHVAT"],
                         "SHTAXTOTAL"=>$mod_strings["LBL_SHTAXTOTAL"],
                         "SHTAXAMOUNT"=>$mod_strings["LBL_SHTAXAMOUNT"],
                         "ADJUSTMENT"=>$mod_strings["LBL_ADJUSTMENT"],
                         "TOTAL"=>$mod_strings["LBL_VARIABLE_TOTALSUM"]);
        
        foreach ($this->ModuleFields AS $module => $Blocks)
        {
            $Optgroupts = array();
            
            if(file_exists("modules/$module/language/$default_language.lang.php"))  //kontrola na $default_language pretoze vo funkcii return_specified_module_language sa kontroluje $current_language a ak neexistuje tak sa pouzije $default_language  
            	$current_mod_strings = return_specified_module_language($current_language, $module);    
            else 
            	$current_mod_strings = return_specified_module_language("en_us", $module);
            
        	$b = 0;
        	foreach ($Blocks AS $block_label => $block_fields)
        	{
            	$b++;
                
                $Options = array();
                
        
                if (isset($current_mod_strings[$block_label]) AND $current_mod_strings[$block_label] != "")
                	$optgroup_value = $current_mod_strings[$block_label];
                elseif (isset($app_strings[$block_label]) AND $app_strings[$block_label] != "")
                    $optgroup_value = $app_strings[$block_label];  
                elseif(isset($mod_strings[$block_label]) AND $mod_strings[$block_label]!="")
                    $optgroup_value = $mod_strings[$block_label];
                else
                    $optgroup_value = $block_label;  
                    
                $Optgroupts[] = '"'.$optgroup_value.'","'.$b.'"';
                
                if (count($block_fields) > 0)
                {
                	$field_ids = implode(",",$block_fields);
                    
                	$sql1 = "SELECT * FROM vtiger_field WHERE fieldid IN (".$field_ids.")";
                	$result1 = $adb->query($sql1); 
                    
                	while($row1 = $adb->fetchByAssoc($result1))
                	{
                    	$fieldname = $row1['fieldname'];
                    	$fieldlabel = $row1['fieldlabel'];
                    	
                 	    $option_key = strtolower($module."-".$fieldname);
                    	   
                        if (isset($current_mod_strings[$fieldlabel]) AND $current_mod_strings[$fieldlabel] != "")
                        	$option_value = $current_mod_strings[$fieldlabel];
                        elseif (isset($app_strings[$fieldlabel]) AND $app_strings[$fieldlabel] != "")
                            $option_value = $app_strings[$fieldlabel];  
                        else
                            $option_value = $fieldlabel;  
                             
                    	$Options[] = '"'.$option_value.'","'.$option_key.'"';
                    	$this->SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
                	}
        		    }
                
                //variable RECORD ID added
                if($b==1)
                {
            			$option_value = "Record ID";
            			$option_key = strtolower($module."-crmid");
            			$Options[] = '"'.$option_value.'","'.$option_key.'"';
            			$this->SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
                }        
               //end
               
               if($block_label == "LBL_TERMS_INFORMATION" && isset($tacModules[$module]))
               {
                  $option_value = $mod_strings["LBL_TAC4YOU"];
                  $option_key = strtolower($module."-TAC4YOU");
                  $Options[] = '"'.$option_value.'","'.$option_key.'"';
                  $this->SelectModuleFields[$module][$optgroup_value][$option_key] = $option_value;
               }
        
                if ($block_label == "LBL_DETAILS_BLOCK" && ($module == "Quotes" || $module == "Invoice" || $module == "SalesOrder" || $module == "PurchaseOrder" || $module == "Issuecards" || $module == "Receiptcards" || $module == "Creditnote" || $module == "StornoInvoice"))
                {
                	foreach ($More_Fields AS $variable => $variable_name)
                    {
                    	$variable_key = strtolower($variable);
                        
                    	$Options[] = '"'.$variable_name.'","'.$variable_key.'"';
                    	$this->SelectModuleFields[$module][$optgroup_value][$variable_key] = $variable_name;
                    }
                }
                
                $this->Convert_RelatedModuleFields[$module."|".$b] = implode(",",$Options);
                $this->Convert_ModuleFields[$module."|".$b] = implode(",",$Options);
            }
            
            $this->Convert_ModuleBlocks[$module] = implode(",",$Optgroupts);
            
        }
    
    }
    
    function getRelatedData()
    {
        $module_related_fields_data = "";
        $all_related_modules_data = "";
        foreach ($this->Module_Related_Fields AS $for_module => $Related_Fields)
        {
             $Rel_Fields_Array = array();
             foreach ($Related_Fields AS $rel_field => $Rel_Data)
             {
                 $Rel_Modules_Array = array();
                 $Rel_Fields_Array[] = '\''.$rel_field.'\',\''.$Rel_Data["name"].'\'';
                 
                 foreach($Rel_Data["modules"] AS $rel_module => $rel_module_label)
                 {
                     $Rel_Modules_Array[] = '\''.$rel_module.'\',\''.$rel_module_label.'\'';   
                 } 
                 $rel_modules_data = implode(",",$Rel_Modules_Array);
        $all_related_modules_data .= 'all_related_modules["'.$for_module.'--'.$rel_field.'"] = new Array('.$rel_modules_data.'); 
        ';
             }
             
             $rel_fields_data = implode(",",$Rel_Fields_Array);
        $module_related_fields_data .= 'module_related_fields["'.$for_module.'"] = new Array('.$rel_fields_data.'); 
        ';
        }
        
        return array($all_related_modules_data,$module_related_fields_data);
    }
    
    function getSubjectFields()
    {
        global $mod_strings;
        
        $subjectFields = array("##DD.MM.YYYY##"=>$mod_strings["LBL_CURDATE_DD.MM.YYYY"],
                           "##DD-MM-YYYY##"=>$mod_strings["LBL_CURDATE_DD-MM-YYYY"],
                           "##DD/MM/YYYY##"=>$mod_strings["LBL_CURDATE_DD/MM/YYYY"],
                           "##MM-DD-YYYY##"=>$mod_strings["LBL_CURDATE_MM-DD-YYYY"],
                           "##MM/DD/YYYY##"=>$mod_strings["LBL_CURDATE_MM/DD/YYYY"],
                           "##YYYY-MM-DD##"=>$mod_strings["LBL_CURDATE_YYYY-MM-DD"]);  
        return $subjectFields;
    }
    
    public function GetDripsListviewData($show_module,$orderby = "dripid", $dir = "asc")
    {  
    	global $current_user, $mod_strings, $app_strings;
        
        $where_array = array();
        $return_data = Array();
        
        $sql = "SELECT dripid, dripname, description, module FROM vtiger_emakertemplates_drips WHERE vtiger_emakertemplates_drips.deleted = '0'"; 
        $sql .= "ORDER BY ".$orderby." ".$dir;
        $result = $this->db->pquery($sql, $where_array);
        $num_rows = $this->db->num_rows($result);
        
        for($i=0;$i < $num_rows; $i++)
        {	
            $dripemailsarray=array();
            $dripid = $this->db->query_result($result,$i,'dripid');
            $currModule = $this->db->query_result($result,$i,'module');
            
            if($this->CheckDripPermissions($currModule, $dripid, false) === false)
                    continue;
            
            $dripemailsarray['dripid'] = $dripid;
            $dripemailsarray['description'] = $this->db->query_result($result,$i,'description');
            $dripemailsarray['module'] = getTranslatedString($this->db->query_result($result,$i,'module'));
            $dripemailsarray['dripname'] = "<a href=\"index.php?action=DetailViewDripEmails&module=EMAILMaker&dripid=".$dripid."&parenttab=Tools\">".$this->db->query_result($result,$i,'dripname')."</a>";
            if($this->CheckPermissions("EDIT"))
            {
                $dripemailsarray['edit'] = "<a href=\"index.php?action=EditDripEmails&module=EMAILMaker&dripid=".$dripid."&parenttab=Tools\">".$app_strings["LBL_EDIT_BUTTON"]."</a>";
                //." | <a href=\"index.php?action=EditDripEmails&module=EMAILMaker&dripid=".$dripid."&isDuplicate=true&parenttab=Tools\">".$app_strings["LBL_DUPLICATE_BUTTON"]."</a>";
            }
            $return_data []= $dripemailsarray;	
        }
        
        return $return_data;
    }
    
    public function getDripEmailTemplates($dripid,$show_drip_text = true)
    {
        global $app_strings,$mod_strings,$Calendar_Mod_Strings; 
               
        $DL = array("days"=>$Calendar_Mod_Strings["LBL_REMAINDER_DAY"],"hours"=>$Calendar_Mod_Strings["LBL_HOURS"],"minutes"=>$Calendar_Mod_Strings["LBL_MINUTES"]);
        
        $Email_Templates = array();
        $sql2 = "SELECT vtiger_emakertemplates.templateid,
                        vtiger_emakertemplates.module,
                       vtiger_emakertemplates.templatename,
                       vtiger_emakertemplates.subject,
                       vtiger_emakertemplates.deleted, 
                       vtiger_emakertemplates_drip_tpls.* 
                FROM vtiger_emakertemplates_drip_tpls
                INNER JOIN vtiger_emakertemplates 
                    ON vtiger_emakertemplates.templateid = vtiger_emakertemplates_drip_tpls.templateid 
                WHERE vtiger_emakertemplates_drip_tpls.deleted = '0' AND vtiger_emakertemplates_drip_tpls.dripid = ? ORDER BY vtiger_emakertemplates_drip_tpls.delay ASC";
        
        $result2 = $this->db->pquery($sql2, array($dripid));
        while($row2 = $this->db->fetchByAssoc($result2))
        {
        	$delay_text = "";
            $status = $app_strings["Active"];
            $delay = $row2['delay'];
            
            $DA = array("days"=>"0","hours"=>"0","minutes"=>"0");
            
            if ($delay > 0)
            {
                $DA["days"] = floor($delay / 86400);
                $DA["hours"] = floor(($delay - ($DA["days"] * 86400))/ 3600);
                $DA["minutes"] = ($delay - (($DA["days"] * 86400) + ($DA["hours"] * 3600)))/ 60;

                if ($show_drip_text)
                {
                    if ($DA["days"] > 0) 
                    {
                        $delay_text .= $DA["days"]."&nbsp;";
                        
                        if ($DA["days"] == "1")
                            $delay_text .= strtolower($Calendar_Mod_Strings["LBL_DAY"])."&nbsp;";
                        else
                            $delay_text .= $DL["days"]."&nbsp;";
                    }
                    
                    if ($DA["hours"] > 0) $delay_text .= $DA["hours"]."&nbsp;".$DL["hours"]."&nbsp;";
                    if ($DA["minutes"] > 0) $delay_text .= $DA["minutes"]."&nbsp;".$DL["minutes"];
                }
            }
            else
            {
                $delay_text = $mod_strings["LBL_IN_NO_TIME"];
            }
            
            if ($row2['deleted'] == "1")
            {
                $status = $mod_strings["LBL_DELETED"];
            }
            
            $Email_Templates[] = array("id" =>$row2['driptplid'],
                                       "template_id" => $row2['templateid'],
                                       "template_name" => $row2['templatename'],
                                       "template_subject" => $row2['subject'],
                                       "status" => $status, 
                                       "delay" => $delay_text,
                                       "delay_array" => $DA,
                                       "delay_lang_array" => $DL);
        }
        
        return $Email_Templates;
    }
    
    public function getEmailTemplatesToDrip($selected_module)
    {
        $Email_Templates = array();
        
        $sql = "SELECT * FROM vtiger_emakertemplates WHERE deleted='0' AND (module IS NULL OR module = ''";
        if ($selected_module != "") 
            $sql .= " OR module = '".$selected_module."'";
        $sql .= ")";
        
        $result = $this->db->pquery($sql, array());
        
        while($row = $this->db->fetchByAssoc($result))
        {
        	if($this->CheckTemplatePermissions($row['module'], $row['templateid'], false) === true || $row['templateid'] == $templateid)
            {
                $template_name = $row['templatename']; 
                if (trim($row['description']) != "") $template_name .= " (".$row['description'].")"; 
                
                $Email_Templates[$row['templateid']] = $template_name;
            }
        }
        
        return $Email_Templates;
    }
   
    //DetailView data
	public function GetDetailViewData($templateid)
	{
		global $mod_strings, $app_strings;
		
        $sql = "SELECT * FROM vtiger_emakertemplates WHERE vtiger_emakertemplates.templateid=?";
  	    $result = $this->db->pquery($sql, array($templateid));
  	    $emailtemplateResult = $this->db->fetch_array($result);
		
		$this->CheckTemplatePermissions($emailtemplateResult["module"], $templateid);
		
		$data = $this->getUserStatusData($templateid);    
		if(count($data) > 0)
		{            
			if($data["is_active"]=="1")
			{
				$is_active = $app_strings["Active"];
				$activateButton = $mod_strings["LBL_SETASINACTIVE"];  
			}
			else
			{
				$is_active = $app_strings["Inactive"];
				$activateButton = $mod_strings["LBL_SETASACTIVE"];
			}
			
			switch($data["is_default"])
			{
                case "0":
                    $is_default = $mod_strings["LBL_FOR_DV"].'&nbsp;<img src="themes/images/no.gif" alt="no" />&nbsp;&nbsp;';
                    $is_default .= $mod_strings["LBL_FOR_LV"].'&nbsp;<img src="themes/images/no.gif" alt="no" />';
                    $defaultButton = $mod_strings["LBL_SETASDEFAULT"];
                    break;

                case "1":
                    $is_default = $mod_strings["LBL_FOR_DV"].'&nbsp;<img src="themes/images/yes.gif" alt="yes" />&nbsp;&nbsp;';
                    $is_default .= $mod_strings["LBL_FOR_LV"].'&nbsp;<img src="themes/images/no.gif" alt="no" />';
                    $defaultButton = $mod_strings["LBL_UNSETASDEFAULT"];
                    break;
                    
                case "2":
                    $is_default = $mod_strings["LBL_FOR_DV"].'&nbsp;<img src="themes/images/no.gif" alt="no" />&nbsp;&nbsp;';
                    $is_default .= $mod_strings["LBL_FOR_LV"].'&nbsp;<img src="themes/images/yes.gif" alt="yes" />';
                    $defaultButton = $mod_strings["LBL_UNSETASDEFAULT"];
                    break;
                    
                case "3":
                    $is_default = $mod_strings["LBL_FOR_DV"].'&nbsp;<img src="themes/images/yes.gif" alt="yes" />&nbsp;&nbsp;';
                    $is_default .= $mod_strings["LBL_FOR_LV"].'&nbsp;<img src="themes/images/yes.gif" alt="yes" />';
                    $defaultButton = $mod_strings["LBL_UNSETASDEFAULT"];
                    break;
            }
		}
		else
		{
			$is_active = $app_strings["Active"];
			$is_default = '<img src="themes/images/no.gif" alt="no" />';
			$activateButton = $mod_strings["LBL_SETASINACTIVE"];
			$defaultButton = $mod_strings["LBL_SETASDEFAULT"];
		}
		
		$emailtemplateResult["is_active"] = $is_active;
		$emailtemplateResult["is_default"] = $is_default;
		$emailtemplateResult["activateButton"] = $activateButton;
		$emailtemplateResult["defaultButton"] = $defaultButton;
		$emailtemplateResult["templateid"] = $templateid;   // fix of empty templateid in case of NULL templateid in DB
		
		return $emailtemplateResult;
	}
	
	//EditView data
	public function GetEditViewData($templateid)
	{    
		$sql = "SELECT * FROM vtiger_emakertemplates WHERE vtiger_emakertemplates.templateid=?";
		$result = $this->db->pquery($sql, array($templateid));
		$emailtemplateResult = $this->db->fetch_array($result);
		
		$data = $this->getUserStatusData($templateid); 	
		
		if(count($data) > 0)
		{      
			$emailtemplateResult["is_active"] = $data["is_active"];
			$emailtemplateResult["is_default"] = $data["is_default"];
			$emailtemplateResult["order"] = $data["order"];
		}
		else
		{      
			$emailtemplateResult["is_active"] = "1";
			$emailtemplateResult["is_default"] = "0";
			$emailtemplateResult["order"] = "1";
		}  	
		
		return $emailtemplateResult;
	}
     
    private function getUserStatusData($templateid)
	{
		global $current_user;
		
		$sql = "SELECT is_active, is_default, sequence FROM vtiger_emakertemplates_userstatus WHERE templateid=? AND userid=?";
		$result = $this->db->pquery($sql,array($templateid,$current_user->id));
		
		$data = array();
		if($this->db->num_rows($result) > 0) {
			$data["is_active"] = $this->db->query_result($result, 0, "is_active");
			$data["is_default"] = $this->db->query_result($result, 0, "is_default");
			$data["order"] = $this->db->query_result($result, 0, "sequence");
		}    
		
		return $data;
	} 
}

  
?>