<?php /* Smarty version Smarty-3.1.7, created on 2014-06-12 14:10:03
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\ModuleSummaryView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:200975384ae8df26295-05249441%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4034c1be7c0ab0c9cda936dc0afed53a1e3f0e07' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\ModuleSummaryView.tpl',
      1 => 1401997445,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '200975384ae8df26295-05249441',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5384ae8e064d3',
  'variables' => 
  array (
    'MODULE_NAME' => 0,
    'SUMMARY_RECORD_STRUCTURE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5384ae8e064d3')) {function content_5384ae8e064d3($_smarty_tpl) {?>
<div class="recordDetails"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('DetailViewBlockView.tpl',$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('RECORD_STRUCTURE'=>$_smarty_tpl->tpl_vars['SUMMARY_RECORD_STRUCTURE']->value,'MODULE_NAME'=>$_smarty_tpl->tpl_vars['MODULE_NAME']->value), 0);?>
</div>
<?php }} ?>