/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

function getSelectedTemplates()
{
    var selectedColumnsObj = getObj("use_common_template");
    var selectedColStr = "";
    for (i = 0; i < selectedColumnsObj.options.length; i++)
    {
        if (selectedColumnsObj.options[i].selected)
        {
            selectedColStr += selectedColumnsObj.options[i].value + ";";
        }
    }

    return selectedColStr;
}

function getPDFDocDivContent(rootElm, module, id)
{
    $("vtbusy_info").style.display = "inline";
    new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: "module=PDFMaker&return_module=" + module + "&action=PDFMakerAjax&file=docSelect&return_id=" + id,
                onComplete: function(response)
                {
                    getObj('PDFDocDiv').innerHTML = response.responseText;
                    fnvshobj(rootElm, 'PDFDocDiv');

                    var PDFDoc = document.getElementById('PDFDocDiv');
                    var PDFDocHandle = document.getElementById('PDFDocDivHandle');
                    Drag.init(PDFDocHandle, PDFDoc);
                    $("vtbusy_info").style.display = "none";
                }
            }
    );
}

function getPDFBreaklineDiv(rootElm, id)
{
    $("vtbusy_info").style.display = "inline";
    new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: "module=PDFMaker&action=PDFMakerAjax&file=breaklineSelect&return_id=" + id,
                onComplete: function(response)
                {
                    getObj('PDFBreaklineDiv').innerHTML = response.responseText;
                    fnvshobj(rootElm, 'PDFBreaklineDiv');

                    var PDFBreakline = document.getElementById('PDFBreaklineDiv');
                    var PDFBreaklineHandle = document.getElementById('PDFBreaklineDivHandle');
                    Drag.init(PDFBreaklineHandle, PDFBreakline);
                    $("vtbusy_info").style.display = "none";
                }
            }
    );
}

function getPDFImagesDiv(rootElm, id)
{
    $("vtbusy_info").style.display = "inline";
    new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: "module=PDFMaker&action=PDFMakerAjax&file=imagesSelect&return_id=" + id,
                onComplete: function(response)
                {
                    getObj('PDFImagesDiv').innerHTML = response.responseText;
                    fnvshobj(rootElm, 'PDFImagesDiv');

                    var PDFImages = document.getElementById('PDFImagesDiv');
                    var PDFImagesHandle = document.getElementById('PDFImagesDivHandle');
                    Drag.init(PDFImagesHandle, PDFImages);
                    $("vtbusy_info").style.display = "none";
                }
            }
    );
}

function sendPDFmail(module, idstrings)
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
                                        openPopUp('xComposeEmail', this, 'index.php?module=Emails&action=EmailsAjax&file=EditView&pmodule=' + module + '&pid=' + idstrings + '&smodule=' + smodule + '&language=' + document.getElementById('template_language').value + '&sendmail=true&attachment=' + response2.responseText + '.pdf', 'createemailWin', 820, 689, 'menubar=no,toolbar=no,location=no,status=no,resizable=no'); //VlMe
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

function sendEMAILMakerPDFmail(module, idstrings)
{
    var smodule = document.DetailView.module.value;
    var record = document.DetailView.record.value;

    $("vtbusy_info").style.display = "inline";
    new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: "module=EMAILMaker&return_module=" + module + "&action=EMAILMakerAjax&file=mailSelect&idlist=" + idstrings + '&pdftemplateid=' + getSelectedTemplates() + '&language=' + document.getElementById('template_language').value,
                onComplete: function(response)
                {
                    if (response.responseText == "Mail Ids not permitted" || response.responseText == "No Mail Ids")
                    {
                        ele = Math.floor(new Date().getTime() / 1000);
                        openPopUp('xComposeEmail' + ele, this, 'index.php?module=EMAILMaker&action=EMAILMakerAjax&file=EditView&pmodule=' + module + '&pid=' + idstrings + '&smodule=' + smodule + '&sendmail=true&pdftemplateid=' + getSelectedTemplates() + '&language=' + document.getElementById('template_language').value + '&commontemplateid=' + getSelectedEmailTemplates("use_common_email_template"), 'createemailWin' + ele, 1100, 850, 'menubar=no,toolbar=no,location=no,status=no,resizable=no');  //VlMe
                    }
                    else {
                        getObj('sendemakermail_cont').innerHTML = response.responseText;
                        fnvshobj(document.getElementById('template_language'), 'sendemakermail_cont');
                        var EMAKERMail = document.getElementById('sendemakermail_cont');
                        var EMAKERMailHandle = document.getElementById('sendemakermail_cont_handle');
                        Drag.init(EMAKERMailHandle, EMAKERMail);
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
                        openPopUp('xComposeEmail', this, 'index.php?module=Emails&action=EmailsAjax&file=EditView&pmodule=' + module + '&smodule=' + smodule + '&idlist=' + idlist + '&field_lists=' + field_lists + '&language=' + document.getElementById('template_language').value + '&sendmail=true&attachment=' + response2.responseText + '.pdf', 'createemailWin', 820, 689, 'menubar=no,toolbar=no,location=no,status=no,resizable=no');   //VlMe
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

function validatePDFDocForm()
{
    if (document.QcEditView.notes_title.value == '')
    {
        alert_label = getObj('alert_doc_title').innerHTML;
        alert(alert_label);
        return false;
    }
    else {
        document.QcEditView.template_ids.value = getSelectedTemplates();
        document.QcEditView.language.value = document.getElementById('template_language').value;
        return true;
    }

}

function savePDFBreakline()
{
    var record = document.DetailView.record.value;
    $("vtbusy_info").style.display = "inline";
    var frm = document.PDFBreaklineForm;
    var url = 'module=PDFMaker&action=PDFMakerAjax&file=SavePDFBreakline&pid=' + record + '&breaklines=';
    var url_suf = '';
    var url_suf2 = '';
    if (frm != 'undefined')
    {
        for (i = 0; i < frm.elements.length; i++)
        {
            if (frm.elements[i].type == 'checkbox')
            {
                if (frm.elements[i].name == 'show_header' || frm.elements[i].name == 'show_subtotal')
                {
                    if (frm.elements[i].checked)
                        url_suf2 += '&' + frm.elements[i].name + '=true';
                    else
                        url_suf2 += '&' + frm.elements[i].name + '=false';
                }
                else
                {
                    if (frm.elements[i].checked)
                        url_suf += frm.elements[i].name + '|';
                }
            }

        }

        url += url_suf + url_suf2;
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    postBody: url,
                    onComplete: function(response)
                    {
                        fninvsh('PDFBreaklineDiv');
                        $("vtbusy_info").style.display = "none";
                    }
                }
        );

    }
}

function savePDFImages()
{
    var record = document.DetailView.record.value;
    $("vtbusy_info").style.display = "inline";
    var frm = document.PDFImagesForm;
    var url = 'module=PDFMaker&action=PDFMakerAjax&file=SavePDFImages&pid=' + record;
    var url_suf = '';
    if (frm != 'undefined')
    {
        for (i = 0; i < frm.elements.length; i++)
        {
            if (frm.elements[i].type == 'radio')
            {
                if (frm.elements[i].checked)
                {
                    url_suf += '&' + frm.elements[i].name + '=' + frm.elements[i].value;
                }
            }
            else if (frm.elements[i].type == 'text')
            {
                url_suf += '&' + frm.elements[i].name + '=' + frm.elements[i].value;
            }
        }

        url += url_suf;
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    postBody: url,
                    onComplete: function(response)
                    {
                        fninvsh('PDFImagesDiv');
                        $("vtbusy_info").style.display = "none";
                    }
                }
        );
    }
}

function checkIfAny()
{
    var frm = document.PDFBreaklineForm;
    if (frm != 'undefined')
    {
        var j = 0;
        for (i = 0; i < frm.elements.length; i++)
        {
            if (frm.elements[i].type == 'checkbox' && frm.elements[i].name != 'show_header' && frm.elements[i].name != 'show_subtotal')
            {
                if (frm.elements[i].checked)
                {
                    j++;
                }
            }
        }
        if (j == 0)
        {
            frm.show_header.checked = false;
            frm.show_subtotal.checked = false;
            frm.show_header.disabled = true;
            frm.show_subtotal.disabled = true;
        }
        else
        {
            frm.show_header.disabled = false;
            frm.show_subtotal.disabled = false;
        }
    }
}

function getPDFListViewPopup2(srcButt, module)
{
    if (document.getElementById("PDFListViewDiv") == undefined)
    {
        var newdiv = document.createElement('div');
        newdiv.setAttribute('id', 'PDFListViewDiv');
        newdiv.innerHTML = 'Loading';
        document.body.appendChild(newdiv);
//      for IE7 compatiblity we can not use setAttribute('style', <val>) as well as setAttribute('class', <val>)
        newdiv = document.getElementById('PDFListViewDiv');
        newdiv.style.display = 'none';
        newdiv.style.width = '400px';
        newdiv.style.position = 'absolute';
        newdiv.className = 'layerPopup';
    }
    /*var select_options  =  document.getElementById('allselectedboxes').value;
     var x = select_options.split(";");
     var count=x.length;
     if (count < 2)
     {
     alert(alert_arr.SELECT);
     return false;
     }*/
    var select_options = $('allselectedboxes').value;
    var excludedRecords = '';
    var searchurl = '';
    var viewid = '';
    if (document.getElementById('excludedRecords')) {
        excludedRecords = $('excludedRecords').value;
        searchurl = $('search_url').value;
        viewid = getviewId();
    }
    if (select_options == 'all') {
        document.getElementById('idlist').value = select_options;
    } else {
        var x = select_options.split(";");
        var count = x.length;
        if (count < 2)
        {
            alert(alert_arr.SELECT);
            return false;
        }
    }
    //postBody: "module=PDFMaker&return_module="+module+"&action=PDFMakerAjax&file=listviewSelect&idslist="+select_options,
    $('status').show();
    new Ajax.Request(
            'index.php',
            {queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: "module=PDFMaker&return_module=" + module + "&action=PDFMakerAjax&file=listviewSelect&idslist=" + select_options + searchurl + "&excludedRecords=" + excludedRecords + "&viewname=" + viewid,
                onComplete: function(response)
                {
                    getObj('PDFListViewDiv').innerHTML = response.responseText;
                    fnvshobj(srcButt, 'PDFListViewDiv');

                    var PDFListview = document.getElementById('PDFListViewDiv');
                    var PDFListviewHandle = document.getElementById('PDFListViewDivHandle');
                    Drag.init(PDFListviewHandle, PDFListview);
                    $('status').hide();
                }
            }
    );
}

function loadPDFCSS(filename)
{
    if (!filename)
        filename = 'modules/PDFMaker/PDFMaker.css';

    var fileref = document.createElement("link");
    fileref.setAttribute("rel", "stylesheet");
    fileref.setAttribute("type", "text/css");
    fileref.setAttribute("href", filename);
    document.getElementsByTagName("head")[0].appendChild(fileref);
}

/*currently not used but kept for future use*/
function loadPDFJS(filename, callback) {
    if (!filename)
        filename = 'modules/PDFMaker/jQuery/jquery-1.10.2.min.js';

    if (!window.jQuery) {
        var jqueryCallback = function() {
            jQuery.noConflict();
            if (callback) {
                callback();
            }
        };

        var e = document.createElement("script");
        e.src = filename;
        e.type = "text/javascript";
        e.onload = jqueryCallback;
        e.onreadystatechange = function() {
            if (this.readyState == 'complete') {
                jqueryCallback();
            }
        };
        document.getElementsByTagName('head')[0].appendChild(e);
    }
    else if (callback) {
        callback();
    }
}

function downloadNewRelease(type, url, alertLbl)
{
    var ans = confirm(alertLbl);

    if (ans == true)
    {
        $("vtbusy_info").style.display = "inline";
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                    method: 'post',
                    postBody: "module=PDFMaker&action=PDFMakerAjax&file=AjaxRequestHandle&handler=download_release&type=" + type + "&url=" + url,
                    onComplete: function(response)
                    {
                        alert(response.responseText);
                        $("vtbusy_info").style.display = "none";
                        window.location.reload();
                    }
                }
        );
    }
}
