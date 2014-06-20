<?php /* Smarty version Smarty-3.1.7, created on 2014-06-16 19:03:55
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Import\Import_Step1.tpl" */ ?>
<?php /*%%SmartyHeaderCode:28557539f3f9b04a7c0-14833889%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f9e9db400a6e28ddf7d564acc9550680f1b1b3e8' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Import\\Import_Step1.tpl',
      1 => 1401997405,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '28557539f3f9b04a7c0-14833889',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'IMPORT_UPLOAD_SIZE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_539f3f9b05a1c',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_539f3f9b05a1c')) {function content_539f3f9b05a1c($_smarty_tpl) {?>
<table width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td><strong><?php echo vtranslate('LBL_IMPORT_STEP_1',$_smarty_tpl->tpl_vars['MODULE']->value);?>
:</strong></td>
		<td class="big"><?php echo vtranslate('LBL_IMPORT_STEP_1_DESCRIPTION',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td data-import-upload-size="<?php echo $_smarty_tpl->tpl_vars['IMPORT_UPLOAD_SIZE']->value;?>
">
			<input type="hidden" name="type" value="csv" />
			<input type="hidden" name="is_scheduled" value="1" />
			<input type="file" name="import_file" id="import_file" onchange="ImportJs.checkFileType()"/>
			<!-- input type="hidden" name="userfile_hidden" value=""/ -->
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><?php echo vtranslate('LBL_IMPORT_SUPPORTED_FILE_TYPES',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td>
	</tr>
</table><?php }} ?>