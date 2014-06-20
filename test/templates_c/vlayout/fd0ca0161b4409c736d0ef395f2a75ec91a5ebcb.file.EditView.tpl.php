<?php /* Smarty version Smarty-3.1.7, created on 2014-06-13 16:05:45
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Inventory\EditView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:142539b2159c48927-48106677%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fd0ca0161b4409c736d0ef395f2a75ec91a5ebcb' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Inventory\\EditView.tpl',
      1 => 1401997374,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '142539b2159c48927-48106677',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_539b2159c738a',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_539b2159c738a')) {function content_539b2159c738a($_smarty_tpl) {?>

<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("EditViewBlocks.tpl",'Inventory'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("LineItemsEdit.tpl",'Inventory'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("EditViewActions.tpl",'Vtiger'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>