<?php /* Smarty version Smarty-3.1.7, created on 2014-06-04 16:15:11
         compiled from "/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Reports/EditHeader.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2083355472538f460fe43078-95151929%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c0ce966967900b445eeeaa1466ccbb04a98ded2e' => 
    array (
      0 => '/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Reports/EditHeader.tpl',
      1 => 1401295281,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2083355472538f460fe43078-95151929',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538f46100d904',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538f46100d904')) {function content_538f46100d904($_smarty_tpl) {?>
<ul id="reportBreadCrumbs" class="breadcrumb"><li class="step1"><?php echo vtranslate('LBL_STEP_1',$_smarty_tpl->tpl_vars['MODULE']->value);?>
: <?php echo vtranslate('LBL_REPORT_DETAILS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<span class="divider">></span></li><li class="step2"><?php echo vtranslate('LBL_STEP_2',$_smarty_tpl->tpl_vars['MODULE']->value);?>
: <?php echo vtranslate('LBL_SELECT_COLUMNS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<span class="divider">></span></li><li class="step3"><?php echo vtranslate('LBL_STEP_3',$_smarty_tpl->tpl_vars['MODULE']->value);?>
: <?php echo vtranslate('LBL_FILTERS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</li></ul>	<?php }} ?>