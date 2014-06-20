<?php /* Smarty version Smarty-3.1.7, created on 2014-06-17 21:56:10
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\Google\map.tpl" */ ?>
<?php /*%%SmartyHeaderCode:523553a0b97a1ccb33-32531045%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6829f989367630bfcd991ebca7892be186eeffad' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\Google\\map.tpl',
      1 => 1401997380,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '523553a0b97a1ccb33-32531045',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'RECORD' => 0,
    'SOURCE_MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_53a0b97a21ad4',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_53a0b97a21ad4')) {function content_53a0b97a21ad4($_smarty_tpl) {?>
<script type="text/javascript" src="layouts/vlayout/modules/Google/resources/map.js"></script>

<span id="map_record" class="hide"><?php echo $_smarty_tpl->tpl_vars['RECORD']->value;?>
</span>
<span id="map_module" class="hide"><?php echo $_smarty_tpl->tpl_vars['SOURCE_MODULE']->value;?>
</span>
<div id="map_canvas">
    <span id="map_address" class="hide"></span>
    <img id="map_link" class="pull-right icon-share cursorPointer"></img>
</div>

<?php }} ?>