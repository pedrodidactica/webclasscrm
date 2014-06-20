<?php /* Smarty version Smarty-3.1.7, created on 2014-06-10 15:17:15
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Vtiger\EditViewActions.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1790537f56cda77e18-56085090%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8d4afa8ea859a127b50f7a629342eabea008d5bc' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Vtiger\\EditViewActions.tpl',
      1 => 1401997445,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1790537f56cda77e18-56085090',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537f56cda8f51',
  'variables' => 
  array (
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537f56cda8f51')) {function content_537f56cda8f51($_smarty_tpl) {?>

<br><div class="row-fluid"><div class="pull-right"><button class="btn btn-success" type="submit"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button><a class="cancelLink" type="reset" onclick="javascript:window.history.back();"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><div class="clearfix"></div></div></form></div><?php }} ?>