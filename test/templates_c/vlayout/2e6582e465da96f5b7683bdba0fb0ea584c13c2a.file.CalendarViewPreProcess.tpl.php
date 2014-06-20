<?php /* Smarty version Smarty-3.1.7, created on 2014-06-09 19:35:08
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Calendar\CalendarViewPreProcess.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8880537bcd07dfef75-69280389%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2e6582e465da96f5b7683bdba0fb0ea584c13c2a' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Calendar\\CalendarViewPreProcess.tpl',
      1 => 1401997381,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8880537bcd07dfef75-69280389',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537bcd07e29ef',
  'variables' => 
  array (
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537bcd07e29ef')) {function content_537bcd07e29ef($_smarty_tpl) {?>
<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("Header.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("BasicHeader.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<div class="bodyContents"><div class="mainContainer row-fluid"><div class="span2 row-fluid"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("SideBar.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div><div class="contentsDiv span10 marginLeftZero"><?php }} ?>