<?php /* Smarty version Smarty-3.1.7, created on 2014-05-23 19:22:02
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Reports\EditHeader.tpl" */ ?>
<?php /*%%SmartyHeaderCode:22587537df865a85d21-71222461%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'de22d179deccd66c08a70326b9ae17340a8aa96f' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Reports\\EditHeader.tpl',
      1 => 1400778760,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '22587537df865a85d21-71222461',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537df865aa12a',
  'variables' => 
  array (
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537df865aa12a')) {function content_537df865aa12a($_smarty_tpl) {?>
<ul id="reportBreadCrumbs" class="breadcrumb"><li class="step1"><?php echo vtranslate('LBL_STEP_1',$_smarty_tpl->tpl_vars['MODULE']->value);?>
: <?php echo vtranslate('LBL_REPORT_DETAILS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<span class="divider">></span></li><li class="step2"><?php echo vtranslate('LBL_STEP_2',$_smarty_tpl->tpl_vars['MODULE']->value);?>
: <?php echo vtranslate('LBL_SELECT_COLUMNS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<span class="divider">></span></li><li class="step3"><?php echo vtranslate('LBL_STEP_3',$_smarty_tpl->tpl_vars['MODULE']->value);?>
: <?php echo vtranslate('LBL_FILTERS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</li></ul>	<?php }} ?>