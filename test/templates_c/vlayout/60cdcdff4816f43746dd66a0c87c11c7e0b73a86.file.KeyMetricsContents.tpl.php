<?php /* Smarty version Smarty-3.1.7, created on 2014-06-06 15:30:05
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\dashboards\KeyMetricsContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:8957538349eb4e2ce6-51647864%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '60cdcdff4816f43746dd66a0c87c11c7e0b73a86' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\dashboards\\KeyMetricsContents.tpl',
      1 => 1401997459,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '8957538349eb4e2ce6-51647864',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538349eb4f657',
  'variables' => 
  array (
    'KEYMETRICS' => 0,
    'KEYMETRIC' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538349eb4f657')) {function content_538349eb4f657($_smarty_tpl) {?>
<div style='padding:5px'><?php  $_smarty_tpl->tpl_vars['KEYMETRIC'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['KEYMETRIC']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['KEYMETRICS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['KEYMETRIC']->key => $_smarty_tpl->tpl_vars['KEYMETRIC']->value){
$_smarty_tpl->tpl_vars['KEYMETRIC']->_loop = true;
?><div style='padding:5px'><span class="pull-right"><?php echo $_smarty_tpl->tpl_vars['KEYMETRIC']->value['count'];?>
</span><a href="?module=<?php echo $_smarty_tpl->tpl_vars['KEYMETRIC']->value['module'];?>
&view=List&viewname=<?php echo $_smarty_tpl->tpl_vars['KEYMETRIC']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['KEYMETRIC']->value['name'];?>
</a></div><?php } ?></div>
<?php }} ?>