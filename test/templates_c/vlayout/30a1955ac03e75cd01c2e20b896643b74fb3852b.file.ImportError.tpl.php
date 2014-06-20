<?php /* Smarty version Smarty-3.1.7, created on 2014-06-16 20:35:28
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Import\ImportError.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1860539f5510c88301-63072249%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '30a1955ac03e75cd01c2e20b896643b74fb3852b' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Import\\ImportError.tpl',
      1 => 1401997405,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1860539f5510c88301-63072249',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'FOR_MODULE' => 0,
    'MODULE' => 0,
    'ERROR_MESSAGE' => 0,
    'ERROR_DETAILS' => 0,
    '_TITLE' => 0,
    '_VALUE' => 0,
    'CUSTOM_ACTIONS' => 0,
    '_LABEL' => 0,
    '_ACTION' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_539f5510d10ea',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_539f5510d10ea')) {function content_539f5510d10ea($_smarty_tpl) {?>
<div class="contentsDiv span10 marginLeftZero"><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
" /><table style="width:80%;margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" cellspacing="10" class="searchUIBasic well"><tr><td class="font-x-large" align="left"><strong><?php echo vtranslate('LBL_IMPORT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 - <?php echo vtranslate('LBL_ERROR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></td></tr><tr><td valign="top"><table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder importContents redColor"><tr><td class="style1" align="left" colspan="2"><?php echo $_smarty_tpl->tpl_vars['ERROR_MESSAGE']->value;?>
</td></tr><?php if ($_smarty_tpl->tpl_vars['ERROR_DETAILS']->value!=''){?><tr><td class="errorMessage" align="left" colspan="2"><?php echo vtranslate('ERR_DETAILS_BELOW',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<table cellpadding="5" cellspacing="0"><?php  $_smarty_tpl->tpl_vars['_VALUE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['_VALUE']->_loop = false;
 $_smarty_tpl->tpl_vars['_TITLE'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['ERROR_DETAILS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['_VALUE']->key => $_smarty_tpl->tpl_vars['_VALUE']->value){
$_smarty_tpl->tpl_vars['_VALUE']->_loop = true;
 $_smarty_tpl->tpl_vars['_TITLE']->value = $_smarty_tpl->tpl_vars['_VALUE']->key;
?><tr><td><?php echo $_smarty_tpl->tpl_vars['_TITLE']->value;?>
</td><td>-</td><td><?php echo $_smarty_tpl->tpl_vars['_VALUE']->value;?>
</td></tr><?php } ?></table></td></tr><?php }?></table></td></tr><tr><td align="right"><?php if ($_smarty_tpl->tpl_vars['CUSTOM_ACTIONS']->value!=''){?><?php  $_smarty_tpl->tpl_vars['_ACTION'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['_ACTION']->_loop = false;
 $_smarty_tpl->tpl_vars['_LABEL'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['CUSTOM_ACTIONS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['_ACTION']->key => $_smarty_tpl->tpl_vars['_ACTION']->value){
$_smarty_tpl->tpl_vars['_ACTION']->_loop = true;
 $_smarty_tpl->tpl_vars['_LABEL']->value = $_smarty_tpl->tpl_vars['_ACTION']->key;
?><button name="<?php echo $_smarty_tpl->tpl_vars['_LABEL']->value;?>
" onclick="<?php echo $_smarty_tpl->tpl_vars['_ACTION']->value;?>
" class="create btn "><strong><?php echo vtranslate($_smarty_tpl->tpl_vars['_LABEL']->value,$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button><?php } ?><?php }?><button name="goback" onclick="window.history.back()" class="edit btn btn-danger"><strong><?php echo vtranslate('LBL_GO_BACK',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></td></tr></table></div><?php }} ?>