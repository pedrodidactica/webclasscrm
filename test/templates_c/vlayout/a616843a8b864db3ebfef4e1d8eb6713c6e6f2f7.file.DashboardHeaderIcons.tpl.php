<?php /* Smarty version Smarty-3.1.7, created on 2014-05-28 17:00:07
         compiled from "/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Vtiger/dashboards/DashboardHeaderIcons.tpl" */ ?>
<?php /*%%SmartyHeaderCode:387973448538616170db6a7-51914979%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a616843a8b864db3ebfef4e1d8eb6713c6e6f2f7' => 
    array (
      0 => '/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Vtiger/dashboards/DashboardHeaderIcons.tpl',
      1 => 1401295358,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '387973448538616170db6a7-51914979',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SETTING_EXIST' => 0,
    'WIDGET' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5386161710151',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5386161710151')) {function content_5386161710151($_smarty_tpl) {?>
<?php if ($_smarty_tpl->tpl_vars['SETTING_EXIST']->value){?>
<a name="dfilter">
	<i class='icon-cog' border='0' align="absmiddle" title="<?php echo vtranslate('LBL_FILTER');?>
" alt="<?php echo vtranslate('LBL_FILTER');?>
"/>
</a>
<?php }?>
<a href="javascript:void(0);" name="drefresh" data-url="<?php echo $_smarty_tpl->tpl_vars['WIDGET']->value->getUrl();?>
&linkid=<?php echo $_smarty_tpl->tpl_vars['WIDGET']->value->get('linkid');?>
&content=data">
	<i class="icon-refresh" hspace="2" border="0" align="absmiddle" title="<?php echo vtranslate('LBL_REFRESH');?>
" alt="<?php echo vtranslate('LBL_REFRESH');?>
"></i>
</a>
<?php if (!$_smarty_tpl->tpl_vars['WIDGET']->value->isDefault()){?>
	<a name="dclose" class="widget" data-url="<?php echo $_smarty_tpl->tpl_vars['WIDGET']->value->getDeleteUrl();?>
">
		<i class="icon-remove" hspace="2" border="0" align="absmiddle" title="<?php echo vtranslate('LBL_REMOVE');?>
" alt="<?php echo vtranslate('LBL_REMOVE');?>
"></i>
	</a>
<?php }?><?php }} ?>