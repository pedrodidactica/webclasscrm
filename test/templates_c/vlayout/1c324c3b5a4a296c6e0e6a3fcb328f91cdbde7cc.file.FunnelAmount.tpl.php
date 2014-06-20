<?php /* Smarty version Smarty-3.1.7, created on 2014-06-06 15:30:05
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Potentials\dashboards\FunnelAmount.tpl" */ ?>
<?php /*%%SmartyHeaderCode:25582538349f9d36f60-02425897%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1c324c3b5a4a296c6e0e6a3fcb328f91cdbde7cc' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Potentials\\dashboards\\FunnelAmount.tpl',
      1 => 1401997437,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '25582538349f9d36f60-02425897',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_538349f9d90d0',
  'variables' => 
  array (
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538349f9d90d0')) {function content_538349f9d90d0($_smarty_tpl) {?>

<div class="dashboardWidgetHeader">
	<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("dashboards/WidgetHeader.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

</div>
<div class="dashboardWidgetContent">
	<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("dashboards/DashBoardWidgetContents.tpl",$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

</div>



<script type="text/javascript">
	Vtiger_Barchat_Widget_Js('Vtiger_Funnelamount_Widget_Js',{},{});
</script>
<?php }} ?>