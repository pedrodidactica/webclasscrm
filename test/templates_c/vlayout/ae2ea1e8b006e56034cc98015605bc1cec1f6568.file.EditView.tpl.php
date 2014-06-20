<?php /* Smarty version Smarty-3.1.7, created on 2014-06-10 15:17:14
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\EditView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:27909537f56cd42e695-43105421%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ae2ea1e8b006e56034cc98015605bc1cec1f6568' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\EditView.tpl',
      1 => 1401997440,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '27909537f56cd42e695-43105421',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537f56cd45962',
  'variables' => 
  array (
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537f56cd45962')) {function content_537f56cd45962($_smarty_tpl) {?>
<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("EditViewBlocks.tpl",$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("EditViewActions.tpl",$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>