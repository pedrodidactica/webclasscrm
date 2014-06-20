<?php /* Smarty version Smarty-3.1.7, created on 2014-06-06 15:30:04
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Potentials\dashboards\GroupBySalesPerson.tpl" */ ?>
<?php /*%%SmartyHeaderCode:22736538349c7cfcea9-94793133%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'cf411db8f52a226ce4f019a7e044c49d91a8ef0d' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Potentials\\dashboards\\GroupBySalesPerson.tpl',
      1 => 1401997437,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '22736538349c7cfcea9-94793133',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538349c7d6e33',
  'variables' => 
  array (
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538349c7d6e33')) {function content_538349c7d6e33($_smarty_tpl) {?>

<div class="dashboardWidgetHeader">
	<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("dashboards/WidgetHeader.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

</div>
<div class="dashboardWidgetContent">
	<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("dashboards/DashBoardWidgetContents.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

</div>


<script type="text/javascript">

	Vtiger_MultiBarchat_Widget_Js('Vtiger_Groupedbysalesperson_Widget_Js',{},{
			getCharRelatedData : function() {
				var container = this.getContainer();
				var data = container.find('.widgetData').val();
				data = JSON.parse(data);
				var users = new Array();
				var stages = new Array();
				var count = new Array();
				for(var i=0; i<data.length ;i++) {
					if($.inArray(data[i].last_name, users) == -1) {
						users.push(data[i].last_name);
					}
					if($.inArray(data[i].sales_stage, stages) == -1) {
						stages.push(data[i].sales_stage);
					}
				}
				
				for(j in stages) {
					var salesStageCount = new Array();
					for(i in users) {
						var salesCount = 0;
						for(var k in data) {
							var userData = data[k];
							if(userData.sales_stage == stages[j] && userData.last_name == users[i]) {
								salesCount = parseInt(userData.count);
								break;
							}
						}
						salesStageCount.push(salesCount);
					}
					count.push(salesStageCount);
				}
				return {
					'data' : count,
					'ticks' : users,
					'labels' : stages
				}
			}
		});
</script>
<?php }} ?>