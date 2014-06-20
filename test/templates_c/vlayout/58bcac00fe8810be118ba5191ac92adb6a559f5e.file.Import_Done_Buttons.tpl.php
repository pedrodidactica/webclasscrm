<?php /* Smarty version Smarty-3.1.7, created on 2014-06-17 15:57:36
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Import\Import_Done_Buttons.tpl" */ ?>
<?php /*%%SmartyHeaderCode:3007453a0657099fed1-93035800%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '58bcac00fe8810be118ba5191ac92adb6a559f5e' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Import\\Import_Done_Buttons.tpl',
      1 => 1401997404,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3007453a0657099fed1-93035800',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'FOR_MODULE' => 0,
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_53a065709cece',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_53a065709cece')) {function content_53a065709cece($_smarty_tpl) {?>

<button class="btn btn-success" name="ok"
		onclick="location.href='index.php?module=<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
&view=Import'"><strong><?php echo vtranslate('LBL_OK_BUTTON_LABEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button><?php }} ?>