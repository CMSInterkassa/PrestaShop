function gogogo(){
  var form = document.createElement('form');
  form.action='https://sci.interkassa.com/';
  var ds = document.querySelector('#ik_backup').dataset;
  Array('ik_co_id','ik_pm_no','ik_desc','ik_am','ik_cur','ik_suc_u','ik_fal_u','ik_pnd_u','ik_ia_u','ik_sign').forEach(function(k){if(typeof ds[k]!='undefined'){var el=document.createElement('input');el.type='hidden',el.name=k,el.value=ds[k];form.appendChild(el)}})
  document.body.appendChild(form).submit()
}
