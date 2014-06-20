<?php /* Smarty version Smarty-3.1.7, created on 2014-06-09 19:58:32
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\uitypes\StringDetailView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:11962537dfd648c52e5-04782457%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0c5be998aa2db9dbbf12f36311fa91971daa5176' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\uitypes\\StringDetailView.tpl',
      1 => 1401997456,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11962537dfd648c52e5-04782457',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537dfd648d0e6',
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'RECORD' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537dfd648d0e6')) {function content_537dfd648d0e6($_smarty_tpl) {?>



<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'),$_smarty_tpl->tpl_vars['RECORD']->value->getId(),$_smarty_tpl->tpl_vars['RECORD']->value);?>
<?php }} ?>