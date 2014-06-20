<?php /* Smarty version Smarty-3.1.7, created on 2014-06-13 16:05:47
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\Popup.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2423539b215bc41341-04476643%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c1d8b85d9728a79c5e92e935f2de74a04bc394e9' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\Popup.tpl',
      1 => 1401997445,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2423539b215bc41341-04476643',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_539b215bc9b0d',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_539b215bc9b0d')) {function content_539b215bc9b0d($_smarty_tpl) {?>
<div id="popupPageContainer" class="popupBackgroundColor"><div class="paddingLeftRight10px"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('PopupSearch.tpl',$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div><div id="popupContents" class="paddingLeftRight10px"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('PopupContents.tpl',$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div><input type="hidden" class="triggerEventName" value="<?php echo $_REQUEST['triggerEventName'];?>
"/></div></div><?php }} ?>