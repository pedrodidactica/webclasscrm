<?php /* Smarty version Smarty-3.1.7, created on 2014-05-23 22:11:11
         compiled from "C:\xampp\htdocs\webclasscrm\includes\runtime/../../layouts/vlayout\modules\MailManager\Settings.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2467537bcd3b519644-94116228%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '47f1a1c5771f24dc5fc71e07eba5684c2312862a' => 
    array (
      0 => 'C:\\xampp\\htdocs\\webclasscrm\\includes\\runtime/../../layouts/vlayout\\modules\\MailManager\\Settings.tpl',
      1 => 1400778760,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2467537bcd3b519644-94116228',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_537bcd3b5f03f',
  'variables' => 
  array (
    'MODULE' => 0,
    'SERVERNAME' => 0,
    'MAILBOX' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_537bcd3b5f03f')) {function content_537bcd3b5f03f($_smarty_tpl) {?>
<div class="editViewContainer">
	<form action="javascript:void(0);" method="POST" id="EditView">
		<div class="widget_header row-fluid">
			<h3><?php echo vtranslate('JSLBL_Settings',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h3>
		</div>
		<hr>
		<table class="table table-bordered blockContainer showInlineTable">
			<tbody>
				<tr>
					<th class="blockHeader" colspan="4">
						<span class="font-x-large"><?php echo vtranslate('LBL_CREATE_MAILBOX',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span>
					</th>
				</tr>
				<tr>
					<td class="fieldLabel">
						<label class="muted pull-right marginRight10px"><?php echo vtranslate('LBL_SELECT_ACCOUNT_TYPE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label>
					</td>
					<td class="fieldValue narrowWidthType" style="width: 70%;">
						<select id="_mbox_helper" class="small" onchange="MailManager.handle_settings_confighelper(this);">
							<option value=''><?php echo vtranslate('JSLBL_Choose_Server_Type',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</option>
							<option value='gmail' <?php if ($_smarty_tpl->tpl_vars['SERVERNAME']->value=='gmail'){?> selected <?php }?>><?php echo vtranslate('JSLBL_Gmail',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</option>
							<option value='yahoo' <?php if ($_smarty_tpl->tpl_vars['SERVERNAME']->value=='yahoo'){?> selected <?php }?>><?php echo vtranslate('JSLBL_Yahoo',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</option>
							<option value='fastmail' <?php if ($_smarty_tpl->tpl_vars['SERVERNAME']->value=='fastmail'){?> selected <?php }?>><?php echo vtranslate('JSLBL_Fastmail',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</option>
							<option value='other' <?php if ($_smarty_tpl->tpl_vars['SERVERNAME']->value=='other'){?> selected <?php }?>><?php echo vtranslate('JSLBL_Other',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>

		<div id="settings_details" <?php if ($_smarty_tpl->tpl_vars['SERVERNAME']->value!=''){?> style="display:block;" <?php }else{ ?> style="display:none;"<?php }?>>
		<table class="table table-bordered blockContainer showInlineTable">
			<tbody>
            <tr>
                <td class="fieldLabel " nowrap>
					<label class="muted pull-right marginRight10px"><font color="red">*</font><?php echo vtranslate('LBL_Mail_Server',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label>
				</td>
                <td class="fieldValue narrowWidthType" nowrap style="width: 70%;">
                    <input name="_mbox_server" value="<?php echo $_smarty_tpl->tpl_vars['MAILBOX']->value->server();?>
" data-validation-engine="validate[required]]" type="text" style="width: 60%" class="detailedViewTextBox" onblur="this.className='detailedViewTextBox'" onfocus="this.className='detailedViewTextBoxOn'">
                    <span class="mm_blur"><?php echo vtranslate('LBL_Like',$_smarty_tpl->tpl_vars['MODULE']->value);?>
, mail.company.com or 192.168.10.20</span>
                </td>
            </tr>

            <tr>
                <td class="fieldLabel " nowrap>
					<label class="muted pull-right marginRight10px"><font color="red">*</font><?php echo vtranslate('LBL_Username',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label>
				</td>
                <td class="fieldValue narrowWidthType" nowrap>
                    <input name="_mbox_user" id="_mbox_user" value="<?php echo $_smarty_tpl->tpl_vars['MAILBOX']->value->username();?>
" type="text" style="width: 60%" class="detailedViewTextBox" onblur="this.className='detailedViewTextBox'" onfocus="this.className='detailedViewTextBoxOn'">
                    <span class="mm_blur"><?php echo vtranslate('LBL_Your_Mailbox_Account',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span>
                </td>
            </tr>

            <tr>
                <td class="fieldLabel " nowrap>
					<label class="muted pull-right marginRight10px"><font color="red">*</font><?php echo vtranslate('LBL_Password',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label>
				</td>
                <td class="fieldValue narrowWidthType" nowrap>
                    <input name="_mbox_pwd" id="_mbox_pwd" value="<?php echo $_smarty_tpl->tpl_vars['MAILBOX']->value->password();?>
" type="password" style="width: 60%" class="detailedViewTextBox" onblur="this.className='detailedViewTextBox'" onfocus="this.className='detailedViewTextBoxOn'">
                    <span class="mm_blur"><?php echo vtranslate('LBL_Account_Password',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span>
                </td>
            </tr>
			</tbody>
        </table>

        <div id="additional_settings" <?php if ($_smarty_tpl->tpl_vars['SERVERNAME']->value=='other'){?> style="display:block;"<?php }else{ ?> style="display:none;" <?php }?>>
		<table class="table table-bordered blockContainer showInlineTable">
			<tr>
				<td class="fieldLabel " nowrap>
					<label class="muted pull-right marginRight10px"><?php echo vtranslate('LBL_Protocol',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label>
				</td>
				<td class="fieldValue narrowWidthType" nowrap style="width: 70%;"><?php echo $_smarty_tpl->tpl_vars['MAILBOX']->value->protocol();?>

					<input type="radio" name="_mbox_protocol" value="IMAP2" <?php if (strcasecmp($_smarty_tpl->tpl_vars['MAILBOX']->value->protocol(),'imap2')===0){?>checked=true<?php }?>> <?php echo vtranslate('LBL_Imap2',$_smarty_tpl->tpl_vars['MODULE']->value);?>

					<input type="radio" name="_mbox_protocol" value="IMAP4" <?php if (strcasecmp($_smarty_tpl->tpl_vars['MAILBOX']->value->protocol(),'imap4')===0){?>checked=true<?php }?>> <?php echo vtranslate('LBL_Imap4',$_smarty_tpl->tpl_vars['MODULE']->value);?>

				</td>
			</tr>

			<tr>
				<td class="fieldLabel " nowrap>
					<label class="muted pull-right marginRight10px"><?php echo vtranslate('LBL_SSL_Options',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label>
				</td>
				<td class="fieldValue narrowWidthType" nowrap>
					<input type="radio" name="_mbox_ssltype" value="notls" <?php if (strcasecmp($_smarty_tpl->tpl_vars['MAILBOX']->value->ssltype(),'notls')===0){?>checked=true<?php }?>> <?php echo vtranslate('LBL_No_TLS',$_smarty_tpl->tpl_vars['MODULE']->value);?>

					<input type="radio" name="_mbox_ssltype" value="tls" <?php if (strcasecmp($_smarty_tpl->tpl_vars['MAILBOX']->value->ssltype(),'tls')===0){?>checked=true<?php }?>> <?php echo vtranslate('LBL_TLS',$_smarty_tpl->tpl_vars['MODULE']->value);?>

					<input type="radio" name="_mbox_ssltype" value="ssl" <?php if (strcasecmp($_smarty_tpl->tpl_vars['MAILBOX']->value->ssltype(),'ssl')===0){?>checked=true<?php }?>> <?php echo vtranslate('LBL_SSL',$_smarty_tpl->tpl_vars['MODULE']->value);?>

				</td>
			</tr>

			<tr>
				<td class="fieldLabel " nowrap>
					<label class="muted pull-right marginRight10px"><?php echo vtranslate('LBL_Certificate_Validations',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label>
				</td>
				<td class="fieldValue narrowWidthType" nowrap>
					<input type="radio" name="_mbox_certvalidate" value="validate-cert" <?php if (strcasecmp($_smarty_tpl->tpl_vars['MAILBOX']->value->certvalidate(),'validate-cert')===0){?>checked=true<?php }?> > <?php echo vtranslate('LBL_Validate_Cert',$_smarty_tpl->tpl_vars['MODULE']->value);?>

					<input type="radio" name="_mbox_certvalidate" value="novalidate-cert" <?php if (strcasecmp($_smarty_tpl->tpl_vars['MAILBOX']->value->certvalidate(),'novalidate-cert')===0){?>checked=true<?php }?>> <?php echo vtranslate('LBL_Do_Not_Validate_Cert',$_smarty_tpl->tpl_vars['MODULE']->value);?>

				</td>
			</tr>
		</table>
        </div>

        <table class="table table-bordered blockContainer showInlineTable">
            <tr>
                <td class="fieldLabel " nowrap>
					<label class="muted pull-right marginRight10px"><?php echo vtranslate('LBL_REFRESH_TIME',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label>
				</td>
                <td class="fieldValue narrowWidthType" nowrap  style="width: 70%;">
                    <select name="_mbox_refresh_timeout">
                        <option value="" <?php if ($_smarty_tpl->tpl_vars['MAILBOX']->value->refreshTimeOut()==''){?>selected<?php }?>><?php echo vtranslate('LBL_NONE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</option>
                        <option value="300000" <?php if (strcasecmp($_smarty_tpl->tpl_vars['MAILBOX']->value->refreshTimeOut(),'300000')==0){?>selected<?php }?>><?php echo vtranslate('LBL_5_MIN',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</option>
                        <option value="600000" <?php if (strcasecmp($_smarty_tpl->tpl_vars['MAILBOX']->value->refreshTimeOut(),'600000')==0){?>selected<?php }?>><?php echo vtranslate('LBL_10_MIN',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="15%">&nbsp;</td>
                <td colspan=3>
                    <button class="btn btn-success" onclick="MailManager.save_settings(this.form);"><?php echo vtranslate('LBL_SAVE_BUTTON_LABEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</button>
                    <?php if ($_smarty_tpl->tpl_vars['MAILBOX']->value&&$_smarty_tpl->tpl_vars['MAILBOX']->value->exists()){?>
                        <button class="btn btn-danger" onclick="MailManager.remove_settings(this.form);"><?php echo vtranslate('LBL_Remove',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</button>
                        <a href="javascript:;" class="cancelLink" onclick="MailManager.close_settings();"><?php echo vtranslate('LBL_CANCEL_BUTTON_LABEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</button>
                    <?php }?>
                </td>
            </tr>
        </table>
	</form>
</div>
<?php }} ?>