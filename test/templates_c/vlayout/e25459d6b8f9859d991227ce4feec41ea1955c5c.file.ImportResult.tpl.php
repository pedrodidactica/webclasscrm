<?php /* Smarty version Smarty-3.1.7, created on 2014-06-16 20:06:57
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Import\ImportResult.tpl" */ ?>
<?php /*%%SmartyHeaderCode:31956539f4e6157fd69-34043964%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e25459d6b8f9859d991227ce4feec41ea1955c5c' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Import\\ImportResult.tpl',
      1 => 1401997404,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '31956539f4e6157fd69-34043964',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'FOR_MODULE' => 0,
    'MODULE' => 0,
    'ERROR_MESSAGE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_539f4e6162bb8',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_539f4e6162bb8')) {function content_539f4e6162bb8($_smarty_tpl) {?>
<div class="contentsDiv span10 marginLeftZero"><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
" /><table style="width:80%;margin-left:auto;margin-right:auto;margin-top: 10px" cellpadding="5" class="searchUIBasic well"><tr><td class="font-x-large" align="left" colspan="2"><strong><?php echo vtranslate('LBL_IMPORT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 <?php echo vtranslate($_smarty_tpl->tpl_vars['FOR_MODULE']->value,$_smarty_tpl->tpl_vars['FOR_MODULE']->value);?>
 - <?php echo vtranslate('LBL_RESULT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></td></tr><?php if ($_smarty_tpl->tpl_vars['ERROR_MESSAGE']->value!=''){?><tr><td class="style1" align="left" colspan="2"><?php echo $_smarty_tpl->tpl_vars['ERROR_MESSAGE']->value;?>
</td></tr><?php }?><tr><td valign="top"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("Import_Result_Details.tpl",'Import'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</td></tr><tr><td align="right" colspan="2"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('Import_Finish_Buttons.tpl','Import'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</td></tr></table></div><?php }} ?>