<?php /* Smarty version Smarty-3.1.7, created on 2014-06-04 22:07:24
         compiled from "/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Accounts/AccountHierarchy.tpl" */ ?>
<?php /*%%SmartyHeaderCode:690682069538f989cdbd076-61556179%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bf9b9b4aaba635830e9df7815accdc3fc1d3d89a' => 
    array (
      0 => '/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Accounts/AccountHierarchy.tpl',
      1 => 1401295277,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '690682069538f989cdbd076-61556179',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'ACCOUNT_HIERARCHY' => 0,
    'HEADERNAME' => 0,
    'ENTRIES' => 0,
    'LISTFIELDS' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538f989ce3608',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538f989ce3608')) {function content_538f989ce3608($_smarty_tpl) {?>
<div id="accountHierarchyContainer" class="modelContainer" style='min-width:750px'><div class="modal-header"><button class="close" data-dismiss="modal" title="<?php echo vtranslate('LBL_CLOSE');?>
">x</button><h3><?php echo vtranslate('LBL_SHOW_ACCOUNT_HIERARCHY',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h3></div><div class="modal-body"><div id ="hierarchyScroll" style="margin-right: 8px;"><table class="table table-bordered"><thead><tr class="blockHeader"><?php  $_smarty_tpl->tpl_vars['HEADERNAME'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['HEADERNAME']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ACCOUNT_HIERARCHY']->value['header']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['HEADERNAME']->key => $_smarty_tpl->tpl_vars['HEADERNAME']->value){
$_smarty_tpl->tpl_vars['HEADERNAME']->_loop = true;
?><th><?php echo vtranslate($_smarty_tpl->tpl_vars['HEADERNAME']->value,$_smarty_tpl->tpl_vars['MODULE']->value);?>
</th><?php } ?></tr></thead><?php  $_smarty_tpl->tpl_vars['ENTRIES'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ENTRIES']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ACCOUNT_HIERARCHY']->value['entries']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['ENTRIES']->key => $_smarty_tpl->tpl_vars['ENTRIES']->value){
$_smarty_tpl->tpl_vars['ENTRIES']->_loop = true;
?><tbody><tr><?php  $_smarty_tpl->tpl_vars['LISTFIELDS'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['LISTFIELDS']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ENTRIES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['LISTFIELDS']->key => $_smarty_tpl->tpl_vars['LISTFIELDS']->value){
$_smarty_tpl->tpl_vars['LISTFIELDS']->_loop = true;
?><td><?php echo $_smarty_tpl->tpl_vars['LISTFIELDS']->value;?>
</td><?php } ?></tr></tbody><?php } ?></table></div></div><div class="modal-footer"><div class=" pull-right cancelLinkContainer"><button class="btn btn-primary" type="reset" data-dismiss="modal"><strong><?php echo vtranslate('LBL_CLOSE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div></div><?php }} ?>