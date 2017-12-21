var selpayIK = {
  actForm : 'https://sci.interkassa.com/',
  Form : 'form[action="https://sci.interkassa.com/"]',
  serialize : function()
  {
    var ds=document.querySelector('#ik_backup').dataset;
    return document.querySelector(selpayIK.Form)?jQuery(selpayIK.Form).serialize():('ik_co_id='+encodeURIComponent(ds.ik_co_id)+'&ik_pm_no='+encodeURIComponent(ds.ik_pm_no)+'&ik_desc='+encodeURIComponent(ds.ik_desc)+'&ik_am='+encodeURIComponent(ds.ik_am)+'&ik_cur='+encodeURIComponent(ds.ik_cur)+'&ik_suc_u='+encodeURIComponent(ds.ik_suc_u)+'&ik_fal_u='+encodeURIComponent(ds.ik_fal_u)+'&ik_pnd_u='+encodeURIComponent(ds.ik_pnd_u)+'&ik_ia_u='+encodeURIComponent(ds.ik_ia_u)+'&ik_sign='+encodeURIComponent(ds.ik_sign)+(ds.ik_pw_via?'&ik_pw_via='+encodeURIComponent(ds.ik_pw_via):'')+(ds.ik_act?'&ik_act='+encodeURIComponent(ds.ik_act):'')+(ds.ik_int?'&ik_pw_via='+encodeURIComponent(ds.ik_int):''))},
  backup : {
    a:function(k,v){if(document.querySelector(selpayIK.Form)){if(document.querySelector('input[name="'+k+'"]'))document.querySelector('input[name="'+k+'"]').value=v;else{var el=document.createElement('input');el.type='hidden',el.name=k,el.value=v;document.querySelector(selpayIK.Form).appendChild(el);}}document.querySelector('#ik_backup').dataset[k]=v;},
    b:function(data){data.forEach(function(v){this.d(v)})},
    c:function(data){data.forEach(function(v){this.a(v[0],v[1])})},
    d:function(k){if(document.querySelector('input[name="'+k+'"]')) jQuery('input[name="'+k+'"]').remove();delete document.querySelector('#ik_backup').dataset[k];},
    f:function(){var form=document.createElement('form');form.action=selpayIK.actForm;var ds=document.querySelector('#ik_backup').dataset;Array('ik_co_id','ik_pm_no','ik_desc','ik_am','ik_cur','ik_suc_u','ik_fal_u','ik_pnd_u','ik_ia_u','ik_sign','ik_pw_via','ik_act','ik_int').forEach(function(k){if(typeof ds[k]!='undefined'){var el=document.createElement('input');el.type='hidden',el.name=k,el.value=ds[k];form.appendChild(el);}});document.body.appendChild(form).submit()}
  },
  selPaysys : function()
	{
    if(jQuery('button.sel-ps-ik').length > 0)
      jQuery('.sel-ps-ik').click()
    else
		{
      var form = jQuery(selpayIK.Form)
      form[0].action = selpayIK.actForm
      setTimeout(function(){form[0].submit()},200)
    }
  },
  paystart : function (data) {
    data_array = (this.IsJsonString(data))? JSON.parse(data) : data
    var form = jQuery(selpayIK.Form);
    if (data_array['resultCode'] != 0) {
      selpayIK.backup.b(['ik_act','ik_int']);
      selpayIK.backup.f()
    }
    else {
      if (data_array['resultData']['paymentForm'] != undefined) {
        var data_send_form = [];
        var data_send_inputs = [];
        data_send_form['url'] = data_array['resultData']['paymentForm']['action'];
        data_send_form['method'] = data_array['resultData']['paymentForm']['method'];
        for (var i in data_array['resultData']['paymentForm']['parameters']) {
          data_send_inputs[i] = data_array['resultData']['paymentForm']['parameters'][i];
        }
        jQuery('body').append('<form method="' + data_send_form['method'] + '" id="tempformIK" action="' + data_send_form['url'] + '"></form>');
        for (var i in data_send_inputs) {
          jQuery('#tempformIK').append('<input type="hidden" name="' + i + '" value="' + data_send_inputs[i] + '" />');
        }
        jQuery('#tempformIK').submit();
      }
      else {
        if (document.getElementById('tempdivIK') == null)
          jQuery(selpayIK.Form).after('<div id="tempdivIK">' + data_array['resultData']['internalForm'] + '</div>');
        else
          jQuery('#tempdivIK').html(data_array['resultData']['internalForm']);
        document.querySelector(selpayIK.Form).action=location.origin+'/modules/interkassa/validation.php';
        document.querySelector('.ps-shown-by-js button').innerText='Нажмите для продолжения после оплаты';
        jQuery('#internalForm').attr('action', 'javascript:selpayIK.selPaysys2()')
      }
    }
  },
  selPaysys2 : function () {
    var form2 = jQuery('#internalForm');
    var msg2 = form2.serialize();
    jQuery.ajax({
      type: 'POST',
      url: selpayIK.req_uri,
      data: msg2,
      success: function (data) {
        selpayIK.paystart2(data.responseText);
      },
      error: function (xhr, str) {
        alert('Error: ' + xhr.responseCode);
      }
    });
  },
  paystart2 : function(string){
    data_array = (this.IsJsonString(data))? JSON.parse(data) : data;
    var form2 = jQuery('#internalForm');
    if (data_array['resultCode'] != 0) {
      form2[0].action = selpayIK.actForm;
      selpayIK.backup.b(['ik_act','ik_int','sci[ik_int]']);
      setTimeout(function(){form2[0].submit()},200)
    }
    else {
      jQuery('#tempdivIK').html('');
      if (data_array['resultData']['paymentForm'] != undefined) {
        var data_send_form = [];
        var data_send_inputs = [];
        data_send_form['url'] = data_array['resultData']['paymentForm']['action'];
        data_send_form['method'] = data_array['resultData']['paymentForm']['method'];
        for (var i in data_array['resultData']['paymentForm']['parameters']) {
          data_send_inputs[i] = data_array['resultData']['paymentForm']['parameters'][i];
        }
        jQuery('#tempdivIK').append('<form method="' + data_send_form['method'] + '" id="tempformIK2" action="' + data_send_form['url'] + '"></form>');
        for (var i in data_send_inputs) {
          jQuery('#tempformIK2').append('<input type="hidden" name="' + i + '" value="' + data_send_inputs[i] + '" />');
        }
        jQuery('#tempformIK2').submit();
      }
      else jQuery('#tempdivIK').append(data_array['resultData']['internalForm']);
    }
  },
  IsJsonString : function(str) {
    try {
      JSON.parse(str);
    } catch (e) {
      return false;
    }
    return true;
  }
}
jQuery(document).ready(function(){
  jQuery('body').prepend('<div class="blLoaderIK"><div class="loaderIK"></div></div>');
  jQuery('.ik_modal').on('show.bs.modal',function(event){jQuery(this).toggleClass('in');jQuery('body').toggleClass('modal-open')});
  jQuery('.ik_modal').on('hide.bs.modal',function(event){jQuery('body').toggleClass('modal-open')})

	jQuery('.ik-payment-confirmation').click(function(e){
		e.preventDefault();

    var pm = jQuery(this).closest('.payment_system');
    var ik_pw_via = jQuery(pm).find('.radioBtn a.active').data('title')
    if(!jQuery(pm).find('.radioBtn a').hasClass('active')){
			alert('ik_err_notslctcurr');
			return;
		} else {
      if(ik_pw_via.search('test_interkassa|qiwi|rbk')==-1){
        selpayIK.backup.c([['ik_act','process'],['ik_int',['json']]])
        jQuery('.blLoaderIK').css('display', 'block');
        jQuery.post(selpayIK.req_uri,selpayIK.serialize(), function (data) {
          var a = JSON.parse(data)
          selpayIK.backup.a('ik_sign',a.sign);
          selpayIK.paystart(a);
          })
          .fail(function () {
            alert('Something wrong');
          })
          .always(function () {
            jQuery('.blLoaderIK').css('display', 'none');
        })
      }
      else selpayIK.backup.f()
		}
    jQuery('#InterkassaModal').modal('hide')
	});
  jQuery('.radioBtn a').on('click',function(){
    jQuery('.blLoaderIK').css('display', 'block');
    var sel = jQuery(this).data('title');
    var tog = jQuery(this).data('toggle');
    jQuery('#' + tog).prop('value', sel);
    jQuery('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
    jQuery('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');

    var ik_pw_via = jQuery(this).attr('data-title');
    selpayIK.backup.a('ik_pw_via',ik_pw_via)

    jQuery.post(selpayIK.req_uri,selpayIK.serialize())
      .always(function (data, status) {
        jQuery('.blLoaderIK').css('display', 'none');
        if(status=='success') selpayIK.backup.a('ik_sign',JSON.parse(data).sign);
        else alert('Something wrong');
      })
  })
});
