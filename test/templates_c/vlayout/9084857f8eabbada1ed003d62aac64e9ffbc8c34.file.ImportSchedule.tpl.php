<?php /* Smarty version Smarty-3.1.7, created on 2014-06-17 15:57:36
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Import\ImportSchedule.tpl" */ ?>
<?php /*%%SmartyHeaderCode:3216553a0657084ff88-12445889%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9084857f8eabbada1ed003d62aac64e9ffbc8c34' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Import\\ImportSchedule.tpl',
      1 => 1401997403,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3216553a0657084ff88-12445889',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'ERROR_MESSAGE' => 0,
    'FOR_MODULE' => 0,
    'IMPORT_ID' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_53a06570980ad',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_53a06570980ad')) {function content_53a06570980ad($_smarty_tpl) {?>
<div class="contentsDiv span10 marginLeftZero"><table style="width:80%;margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="searchUIBasic well"><tr><td class="font-x-large" align="left" colspan="2"><strong><?php echo vtranslate('LBL_IMPORT_SCHEDULED',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></td></tr><?php if ($_smarty_tpl->tpl_vars['ERROR_MESSAGE']->value!=''){?><tr><td class="style1" align="left" colspan="2"><?php echo $_smarty_tpl->tpl_vars['ERROR_MESSAGE']->value;?>
</td></tr><?php }?><tr><td colspan="2" valign="top"><table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder importContents"><tr><td><?php echo vtranslate('LBL_SCHEDULED_IMPORT_DETAILS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td></tr></table></td></tr><tr><td align="right" colspan="2"><a type="button" name="cancel" value="<?php echo vtranslate('LBL_CANCEL_IMPORT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" class="crmButton small delete"onclick="location.href='index.php?module=<?php echo $_smarty_tpl->tpl_vars['FOR_MODULE']->value;?>
&view=Import&mode=cancelImport&import_id=<?php echo $_smarty_tpl->tpl_vars['IMPORT_ID']->value;?>
'"><?php echo vtranslate('LBL_CANCEL_IMPORT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('Import_Done_Buttons.tpl','Import'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</td></tr></table></div><?php }} ?>