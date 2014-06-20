<?php /* Smarty version Smarty-3.1.7, created on 2014-06-13 15:10:55
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\NoComments.tpl" */ ?>
<?php /*%%SmartyHeaderCode:25304537f6effeb30d0-71786088%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'faf0fbe4634bb3ee45cba5a71507581f5739d22e' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\NoComments.tpl',
      1 => 1401997441,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '25304537f6effeb30d0-71786088',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537f6effebadd',
  'variables' => 
  array (
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537f6effebadd')) {function content_537f6effebadd($_smarty_tpl) {?>
<div class="summaryWidgetContainer noCommentsMsgContainer"><p class="textAlignCenter"> <?php echo vtranslate('LBL_NO_COMMENTS',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</p></div><?php }} ?>