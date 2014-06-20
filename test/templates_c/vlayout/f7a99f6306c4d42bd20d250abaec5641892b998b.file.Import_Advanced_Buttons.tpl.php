<?php /* Smarty version Smarty-3.1.7, created on 2014-06-16 19:52:15
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Import\Import_Advanced_Buttons.tpl" */ ?>
<?php /*%%SmartyHeaderCode:18195539f4aef5d4079-37149895%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f7a99f6306c4d42bd20d250abaec5641892b998b' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Import\\Import_Advanced_Buttons.tpl',
      1 => 1401997404,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '18195539f4aef5d4079-37149895',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_539f4aef5dfc0',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_539f4aef5dfc0')) {function content_539f4aef5dfc0($_smarty_tpl) {?>

<button type="submit" name="import" id="importButton" class="crmButton big edit btn btn-success"
		><strong><?php echo vtranslate('LBL_IMPORT_BUTTON_LABEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button>
&nbsp;&nbsp;
<a type="button" name="cancel" value="<?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" class="cursorPointer cancelLink" onclick="window.history.back()">
	<?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>

</a><?php }} ?>