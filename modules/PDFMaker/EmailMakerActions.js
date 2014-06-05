/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

function sendEMakerMail(module, idstrings)
{
    var smodule = document.DetailView.module.value;
    var record = document.DetailView.record.value;

    $("vtbusy_info").style.display = "inline";
    new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: "module=PDFMaker&return_module=" + module + "&action=PDFMakerAjax&file=mailSelect&idlist=" + idstrings,
                onComplete: function(response)
                {
                    if (response.responseText == "Mail Ids not permitted" || response.responseText == "No Mail Ids")
                    {
                        emailhref = 'module=PDFMaker&action=PDFMakerAjax&file=SendPDFMail&language=' + document.getElementById('template_language').value + '&record=' + record + '&relmodule=' + module + '&commontemplateid=' + getSelectedTemplates();

                        new Ajax.Request(
                                'index.php',
                                {queue: {position: 'end', scope: 'command'},
                                    method: 'post',
                                    postBody: emailhref,
                                    onComplete: function(response2)
                                    {
                                        openPopUp('xComposeEmail', this, 'index.php?module=Emails&action=EmailsAjax&file=EditView&pmodule=' + module + '&pid=' + idstrings + '&language=' + document.getElementById('template_language').value + '&sendmail=true&attachment=' + response2.responseText + '.pdf', 'createemailWin', 820, 689, 'menubar=no,toolbar=no,location=no,status=no,resizable=no');
                                    }
                                });
                    }
                    else {
                        getObj('sendpdfmail_cont').innerHTML = response.responseText;
                        var PDFMail = document.getElementById('sendpdfmail_cont');
                        var PDFMailHandle = document.getElementById('sendpdfmail_cont_handle');
                        Drag.init(PDFMailHandle, PDFMail);
                    }
                    $("vtbusy_info").style.display = "none";
                }
            }
    );
}

function validate_sendPDFmail(idlist, module)
{
    var smodule = document.DetailView.module.value;
    var record = document.DetailView.record.value;
    var j = 0;
    var chk_emails = document.SendPDFMail.elements.length;
    var oFsendmail = document.SendPDFMail.elements;
    email_type = new Array();
    for (var i = 0; i < chk_emails; i++)
    {
        if (oFsendmail[i].type != 'button')
        {
            if (oFsendmail[i].checked != false)
            {
                email_type [j++] = oFsendmail[i].value;
            }
        }
    }
    if (email_type != '')
    {
        $("vtbusy_info").style.display = "inline";
        var field_lists = email_type.join(':');

        emailhref = 'module=PDFMaker&action=PDFMakerAjax&file=SendPDFMail&language=' + document.getElementById('template_language').value + '&record=' + record + '&relmodule=' + smodule + '&commontemplateid=' + getSelectedTemplates();

        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    postBody: emailhref,
                    onComplete: function(response2)
                    {
                        openPopUp('xComposeEmail', this, 'index.php?module=Emails&action=EmailsAjax&file=EditView&pmodule=' + module + '&idlist=' + idlist + '&field_lists=' + field_lists + '&language=' + document.getElementById('template_language').value + '&sendmail=true&attachment=' + response2.responseText + '.pdf', 'createemailWin', 820, 689, 'menubar=no,toolbar=no,location=no,status=no,resizable=no');
                        $("vtbusy_info").style.display = "none";
                    }
                });

        fninvsh('roleLay2');
        return true;
    }
    else
    {
        alert(alert_arr.SELECT_MAILID);
    }
}
