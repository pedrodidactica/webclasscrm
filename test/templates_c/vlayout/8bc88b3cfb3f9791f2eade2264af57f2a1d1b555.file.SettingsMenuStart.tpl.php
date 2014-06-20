<?php /* Smarty version Smarty-3.1.7, created on 2014-06-06 15:30:12
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Settings\Vtiger\SettingsMenuStart.tpl" */ ?>
<?php /*%%SmartyHeaderCode:17929537e0285514332-76642031%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8bc88b3cfb3f9791f2eade2264af57f2a1d1b555' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Settings\\Vtiger\\SettingsMenuStart.tpl',
      1 => 1401997433,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17929537e0285514332-76642031',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537e02855663c',
  'variables' => 
  array (
    'MODULE' => 0,
    'LOAD_OLD' => 0,
    'UI5_URL' => 0,
    'QUALIFIED_MODULE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537e02855663c')) {function content_537e02855663c($_smarty_tpl) {?>
<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("Header.tpl",$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("BasicHeader.tpl",$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>


<?php if ($_smarty_tpl->tpl_vars['LOAD_OLD']->value){?><div class="bodyContents"><div class="mainContainer row-fluid"><div class="span12"><iframe id="ui5frame" src="<?php echo $_smarty_tpl->tpl_vars['UI5_URL']->value;?>
" width="100%" height="650px" style="border:0;"></iframe></div><?php }else{ ?><div class="bodyContents"><div class="mainContainer row-fluid"><div class="span2 row-fluid"><div><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('Sidebar.tpl',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div></div><div class="contentsDiv span10 marginLeftZero"><?php }?><?php }} ?>