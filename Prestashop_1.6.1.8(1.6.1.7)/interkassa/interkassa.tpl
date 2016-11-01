<div class="row">
	<div class="col-xs-12">
		<p class="payment_module">
			<a class="bankwire" href="javascript:$('#interkassa').submit();" title="Оплатить через Интеркассу" style="line-height: 100%; ">
				<img src="{$img_path}interkassa.gif" style="float:left; height: 100%" title="Интеркасса" alt="Интеркасса"/>
				<br style="clear:both;" />
			</a>
		</p>
		<form id="interkassa" accept-charset="utf-8" method="POST" action="https://sci.interkassa.com">
			<input type="hidden" name="ik_am" value="{$ik_am}">
			<input type="hidden" name="ik_cur" value="{$ik_cur}">
			<input type="hidden" name="ik_desc" value="{$ik_desc}">
			<input type="hidden" name="ik_pm_no" value="{$ik_pm_no}">
			<input type="hidden" name="ik_co_id" value="{$ik_co_id}">
			<input type="hidden" name="ik_ia_u" value="{$ik_ia_u}">
			<input type="hidden" name="ik_suc_u" value="{$ik_suc_u}">
			<input type="hidden" name="ik_fal_u" value="{$ik_fal_u}">
			<input type="hidden" name="ik_pnd_u" value="{$ik_pnd_u}">
			<input type="hidden" name="ik_sign" value="{$ik_sign}">
		</form>
	</div>
</div>


