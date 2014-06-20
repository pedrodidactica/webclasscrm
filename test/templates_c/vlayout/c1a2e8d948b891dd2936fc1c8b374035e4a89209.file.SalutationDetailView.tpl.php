<?php /* Smarty version Smarty-3.1.7, created on 2014-06-13 15:10:53
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\uitypes\SalutationDetailView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:9014538399c6a855c9-85990025%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c1a2e8d948b891dd2936fc1c8b374035e4a89209' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\uitypes\\SalutationDetailView.tpl',
      1 => 1401997450,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '9014538399c6a855c9-85990025',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538399c6a9114',
  'variables' => 
  array (
    'RECORD' => 0,
    'FIELD_MODEL' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538399c6a9114')) {function content_538399c6a9114($_smarty_tpl) {?>


<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('salutationtype');?>


<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'),$_smarty_tpl->tpl_vars['RECORD']->value->getId(),$_smarty_tpl->tpl_vars['RECORD']->value);?>
<?php }} ?>