<?php /* Smarty version Smarty-3.1.7, created on 2014-06-03 21:09:41
         compiled from "/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Potentials/dashboards/TotalRevenuePerSalesPerson.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1722299573538e3995beb5b6-37769229%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e6dac7f5575180dc9219754e9e3b2e201e15c4c9' => 
    array (
      0 => '/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Potentials/dashboards/TotalRevenuePerSalesPerson.tpl',
      1 => 1401295355,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1722299573538e3995beb5b6-37769229',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538e3995c58d1',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538e3995c58d1')) {function content_538e3995c58d1($_smarty_tpl) {?>
<script type="text/javascript">
	Vtiger_Pie_Widget_Js('Vtiger_Totalrevenuepersalesperson_Widget_Js',{},{});
</script>
<div class="dashboardWidgetHeader">
	<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("dashboards/WidgetHeader.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('SETTING_EXIST'=>true), 0);?>

	<div class="row-fluid filterContainer hide" style="position:absolute;z-index:100001">
		<div class="row-fluid">
			<span class="span5">
				<span class="pull-right">
					<?php echo vtranslate('Created Time',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
 &nbsp; <?php echo vtranslate('LBL_BETWEEN',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>

				</span>
			</span>
			<span class="span4">
				<input type="text" name="createdtime" class="dateRange widgetFilter" />
			</span>
		</div>
	</div>			
</div>
<div class="dashboardWidgetContent">
	<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("dashboards/DashBoardWidgetContents.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

</div><?php }} ?>