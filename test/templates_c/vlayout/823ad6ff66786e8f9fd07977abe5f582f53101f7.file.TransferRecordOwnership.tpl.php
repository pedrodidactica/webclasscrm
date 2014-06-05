<?php /* Smarty version Smarty-3.1.7, created on 2014-06-04 22:10:56
         compiled from "/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Vtiger/TransferRecordOwnership.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1481305591538f9970975890-35853127%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '823ad6ff66786e8f9fd07977abe5f582f53101f7' => 
    array (
      0 => '/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Vtiger/TransferRecordOwnership.tpl',
      1 => 1401295283,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1481305591538f9970975890-35853127',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'RELATED_MODULES' => 0,
    'RELATED_MODULE' => 0,
    'SKIP_MODULES' => 0,
    'USER_MODEL' => 0,
    'ALL_ACTIVEUSER_LIST' => 0,
    'OWNER_ID' => 0,
    'OWNER_NAME' => 0,
    'FIELD_VALUE' => 0,
    'ACCESSIBLE_USER_LIST' => 0,
    'CURRENT_USER_ID' => 0,
    'ALL_ACTIVEGROUP_LIST' => 0,
    'ACCESSIBLE_GROUP_LIST' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538f9970a6920',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538f9970a6920')) {function content_538f9970a6920($_smarty_tpl) {?>
<div id="transferOwnershipContainer" class='modelContainer'><div class="modal-header contentsBackground"><button data-dismiss="modal" class="close" title="<?php echo vtranslate('LBL_CLOSE');?>
">&times;</button><h3 id="massEditHeader"><?php echo vtranslate('LBL_TRANSFER_OWNERSHIP',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h3></div><form class="form-horizontal" id="changeOwner" name="changeOwner" method="post" action="index.php"><div class="modal-body tabbable"><div class="control-group"><div class="control-label" style="width: 50;"><?php echo vtranslate('LBL_SELECT_RELATED_MODULES',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</div><div class="controls"><select class="select2-container columnsSelect" id="related_modules" data-validation-engine="validate[required]" data-placeholder="<?php echo vtranslate('LBL_SELECT_RELATED_MODULES',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" multiple="" name="related_modules[]" style="width: 350px;"><?php  $_smarty_tpl->tpl_vars['RELATED_MODULE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RELATED_MODULE']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_MODULES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_MODULE']->key => $_smarty_tpl->tpl_vars['RELATED_MODULE']->value){
$_smarty_tpl->tpl_vars['RELATED_MODULE']->_loop = true;
?><?php if (!in_array($_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('relatedModuleName'),$_smarty_tpl->tpl_vars['SKIP_MODULES']->value)){?><option value="<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('relatedModuleName');?>
"><?php echo vtranslate($_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('relatedModuleName'),$_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('relatedModuleName'));?>
</option><?php }?><?php } ?></select></div></br></div><div class="control-group"><div class="control-label"><?php echo vtranslate('LBL_ASSIGNED_TO',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</div><div class="controls"><?php $_smarty_tpl->tpl_vars['ALL_ACTIVEUSER_LIST'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->getAccessibleUsers(), null, 0);?><?php $_smarty_tpl->tpl_vars['ALL_ACTIVEGROUP_LIST'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->getAccessibleGroups(), null, 0);?><?php $_smarty_tpl->tpl_vars['CURRENT_USER_ID'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->get('id'), null, 0);?><?php $_smarty_tpl->tpl_vars['ACCESSIBLE_USER_LIST'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->getAccessibleUsersForModule($_smarty_tpl->tpl_vars['MODULE']->value), null, 0);?><?php $_smarty_tpl->tpl_vars['ACCESSIBLE_GROUP_LIST'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->getAccessibleGroupForModule($_smarty_tpl->tpl_vars['MODULE']->value), null, 0);?><select class="chzn-select" data-validation-engine="validate[ required]" name="transferOwnerId" id="transferOwnerId"><optgroup label="<?php echo vtranslate('LBL_USERS');?>
"><?php  $_smarty_tpl->tpl_vars['OWNER_NAME'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['OWNER_NAME']->_loop = false;
 $_smarty_tpl->tpl_vars['OWNER_ID'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['ALL_ACTIVEUSER_LIST']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['OWNER_NAME']->key => $_smarty_tpl->tpl_vars['OWNER_NAME']->value){
$_smarty_tpl->tpl_vars['OWNER_NAME']->_loop = true;
 $_smarty_tpl->tpl_vars['OWNER_ID']->value = $_smarty_tpl->tpl_vars['OWNER_NAME']->key;
?><option value="<?php echo $_smarty_tpl->tpl_vars['OWNER_ID']->value;?>
" data-picklistvalue= '<?php echo $_smarty_tpl->tpl_vars['OWNER_NAME']->value;?>
' <?php if ($_smarty_tpl->tpl_vars['FIELD_VALUE']->value==$_smarty_tpl->tpl_vars['OWNER_ID']->value){?> selected <?php }?><?php if (array_key_exists($_smarty_tpl->tpl_vars['OWNER_ID']->value,$_smarty_tpl->tpl_vars['ACCESSIBLE_USER_LIST']->value)){?> data-recordaccess=true <?php }else{ ?> data-recordaccess=false <?php }?>data-userId="<?php echo $_smarty_tpl->tpl_vars['CURRENT_USER_ID']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['OWNER_NAME']->value;?>
</option><?php } ?></optgroup><optgroup label="<?php echo vtranslate('LBL_GROUPS');?>
"><?php  $_smarty_tpl->tpl_vars['OWNER_NAME'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['OWNER_NAME']->_loop = false;
 $_smarty_tpl->tpl_vars['OWNER_ID'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['ALL_ACTIVEGROUP_LIST']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['OWNER_NAME']->key => $_smarty_tpl->tpl_vars['OWNER_NAME']->value){
$_smarty_tpl->tpl_vars['OWNER_NAME']->_loop = true;
 $_smarty_tpl->tpl_vars['OWNER_ID']->value = $_smarty_tpl->tpl_vars['OWNER_NAME']->key;
?><option value="<?php echo $_smarty_tpl->tpl_vars['OWNER_ID']->value;?>
" data-picklistvalue= '<?php echo $_smarty_tpl->tpl_vars['OWNER_NAME']->value;?>
'<?php if (array_key_exists($_smarty_tpl->tpl_vars['OWNER_ID']->value,$_smarty_tpl->tpl_vars['ACCESSIBLE_GROUP_LIST']->value)){?> data-recordaccess=true <?php }else{ ?> data-recordaccess=false <?php }?> ><?php echo $_smarty_tpl->tpl_vars['OWNER_NAME']->value;?>
</option><?php } ?></optgroup></select></div></div></div><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('ModalFooter.tpl',$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</form></div>
<?php }} ?>