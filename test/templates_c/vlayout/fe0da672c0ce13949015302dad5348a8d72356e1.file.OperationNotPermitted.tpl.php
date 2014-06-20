<?php /* Smarty version Smarty-3.1.7, created on 2014-06-09 19:34:46
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\OperationNotPermitted.tpl" */ ?>
<?php /*%%SmartyHeaderCode:17003537dfd315b0658-99622033%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fe0da672c0ce13949015302dad5348a8d72356e1' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\OperationNotPermitted.tpl',
      1 => 1401997441,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17003537dfd315b0658-99622033',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537dfd3160a3e',
  'variables' => 
  array (
    'MESSAGE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537dfd3160a3e')) {function content_537dfd3160a3e($_smarty_tpl) {?>
<div style="margin:0 auto;width: 50em;">
	<table border='0' cellpadding='5' cellspacing='0' height='600px' width="700px">
	<tr><td align='center'>
		<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 100000020;'>

		<table border='0' cellpadding='5' cellspacing='0' width='98%'>
		<tr>
			<td rowspan='2' width='11%'><img src="<?php echo vimage_path('denied.gif');?>
" ></td>
			<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'>
				<span class='genHeaderSmall'><?php echo vtranslate($_smarty_tpl->tpl_vars['MESSAGE']->value);?>
</span></td>
		</tr>
		<tr>
			<td class='small' align='right' nowrap='nowrap'>
				<a href='javascript:window.history.back();'><?php echo vtranslate('LBL_GO_BACK');?>
</a><br>
			</td>
		</tr>
		</table>
		</div>
	</td></tr>
	</table>
</div><?php }} ?>