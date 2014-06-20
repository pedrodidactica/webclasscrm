<?php /* Smarty version Smarty-3.1.7, created on 2014-06-06 15:30:07
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\HelpDesk\dashboards\OpenTickets.tpl" */ ?>
<?php /*%%SmartyHeaderCode:18364538349dc633515-21598765%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd3168471dfd0ed00cef3270b943cc917e7a6e19e' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\HelpDesk\\dashboards\\OpenTickets.tpl',
      1 => 1401997395,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '18364538349dc633515-21598765',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538349dc68d2b',
  'variables' => 
  array (
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538349dc68d2b')) {function content_538349dc68d2b($_smarty_tpl) {?>
<div class="dashboardWidgetHeader">
	<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("dashboards/WidgetHeader.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

</div>
<div class="dashboardWidgetContent">
	<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("dashboards/DashBoardWidgetContents.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

</div>

<script type="text/javascript">
	Vtiger_Pie_Widget_Js('Vtiger_Opentickets_Widget_Js',{},{
		/**
		 * Function which will give chart related Data
		 */
		getChartRelatedData : function() {
			var container = this.getContainer();
			var jData = container.find('.widgetData').val();
			var data = JSON.parse(jData);
			var chartData = [];
			for(var index in data) {
				var row = data[index];
				var rowData = [row.name, parseInt(row.count), row.id];
				chartData.push(rowData);
			}
			return chartData;
		}
	});
</script><?php }} ?>