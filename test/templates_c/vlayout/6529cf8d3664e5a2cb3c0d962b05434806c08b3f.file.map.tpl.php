<?php /* Smarty version Smarty-3.1.7, created on 2014-06-02 13:12:47
         compiled from "/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Google/map.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1214576018538c784f0b6935-25684975%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6529cf8d3664e5a2cb3c0d962b05434806c08b3f' => 
    array (
      0 => '/home/crmweb/public_html/crmwebclass/includes/runtime/../../layouts/vlayout/modules/Google/map.tpl',
      1 => 1401295278,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1214576018538c784f0b6935-25684975',
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
  'unifunc' => 'content_538c784f16db5',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_538c784f16db5')) {function content_538c784f16db5($_smarty_tpl) {?>
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