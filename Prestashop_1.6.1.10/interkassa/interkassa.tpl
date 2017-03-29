<div class="row">
	<div class="col-xs-12">
		<p class="payment_module">
			{if $api_mode}
			<a class="bankwire" title="Оплатить через Интеркассу" style="line-height: 100%; "  data-toggle="modal" data-target="#InterkassaModal">
			{else}
			<a class="bankwire" href="javascript:$('#interkassa').submit();" title="Оплатить через Интеркассу" style="line-height: 100%; ">
			{/if}
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

{if $api_mode}
<div id="InterkassaModal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" id="plans">
			<div class="container">
				<div class="row">
                    {if  isset($payment_systems.error)}
                    <h1><strong style="color:red;">{$payment_systems.error}</strong><br>
                        Please verify your configuration
                    </h1>
                    {else}

                    <h1>
						1.{l s='Select Payment System' mod='interkassa'}<br>
						2.{l s='Specify currency' mod='interkassa'}<br>
						3.{l s='Press Pay' mod='interkassa'}
					</h1>

					{foreach $payment_systems as $ps => $info}
					<div class="col-md-3 text-center payment_system">
						<div class="panel panel-pricing">
							<div class="panel-heading">
								<img src="{$payment_systems_path}{$ps}.png" alt="{$info['title']}">
								<h3 class="ps-title">{$info['title']}</h3>
							</div>
							<div class="form-group">
								<div class="input-group">
									<div id="radioBtn">
										{foreach $info['currency'] as $currency => $currencyAlias}
										<a class="btn btn-primary btn-sm notActive" data-toggle="fun"
										   data-title="{$currencyAlias}">{$currency}</a>
										{/foreach }
									</div>
									<input type="hidden" name="fun" id="fun">
								</div>
							</div>
							<div class="panel-footer">
								<a class="btn btn-lg btn-block btn-success ik-payment-confirmation" data-title="{$ps}" href="#">{l
									s='Pay via' mod='interkassa'}
									<br>
									<strong>{$info['title']}</strong>
								</a>
							</div>
						</div>
					</div>
					{/foreach }
                    {/if}
				</div>
			</div>
		</div>
	</div>
</div>



<script type="text/javascript">

	var curtrigger = false;

	$('.ik-payment-confirmation').click(function () {
		$('#interkassa').submit();

	});

	$('#radioBtn a').click(function () {
		curtrigger = true;
		var ik_cur = this.innerText;
		var ik_pw_via = $(this).attr('data-title');
		var form = $('#interkassa');


		if($('input[name =  "ik_pw_via"]').length > 0){
			$('input[name =  "ik_pw_via"]').val(ik_pw_via);
		}else{
			form.append(
					$('<input>', {
						type: 'hidden',
						name: 'ik_pw_via',
						val: ik_pw_via
					}));
		}
		$.post('{$ajax_url}',form.serialize() )
				.done(function (data) {
					console.log(data);
					if($('input[name =  "ik_sign"]').length > 0){
						$('input[name =  "ik_sign"]').val(data);
					}
				})
				.fail(function () {
					alert('Что-то не так. Выберите валюту еще раз');
				});
	});

	$('#radioBtn a').on('click', function () {
		var sel = $(this).data('title');
		var tog = $(this).data('toggle');
		$('#' + tog).prop('value', sel);
		$('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
		$('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');
	})

</script>

<style>
    #InterkassaModal{
        z-index: 100000;
    }
	#InterkassaModal .input-group,#InterkassaModal h1{
		text-align: center;
	}

	.payment_system h3, .payment_system img {
		display: inline-block;
		width: 100%;
	}

	.payment_system .panel-heading {
		text-align: center;
	}

	.payment_system .btn-primary, .payment_system .btn-secondary, .payment_system .btn-tertiary {
		padding: 6px;
	}
	.ps-title{
		font-size: 18px;
	}
	.panel-pricing {
		-moz-transition: all .3s ease;
		-o-transition: all .3s ease;
		-webkit-transition: all .3s ease;
	}

	.panel-pricing:hover {
		box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.2);
	}

	.panel-pricing .panel-heading {
		padding: 20px 10px;
	}

	.panel-pricing .panel-heading .fa {
		margin-top: 10px;
		font-size: 58px;
	}

	.panel-pricing .list-group-item {
		color: #777777;
		border-bottom: 1px solid rgba(250, 250, 250, 0.5);
	}

	.panel-pricing .list-group-item:last-child {
		border-bottom-right-radius: 0px;
		border-bottom-left-radius: 0px;
	}

	.panel-pricing .list-group-item:first-child {
		border-top-right-radius: 0px;
		border-top-left-radius: 0px;
	}

	.panel-pricing .panel-body {
		background-color: #f0f0f0;
		font-size: 40px;
		color: #777777;
		padding: 20px;
		margin: 0px;
	}

	#radioBtn .notActive {
		color: #3276b1;
		background-color: #fff;
	}

	.modal {
		display: none;
		overflow: hidden;
		position: fixed;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		z-index: 1050;
		-webkit-overflow-scrolling: touch;
		outline: 0;
	}
	.modal.fade .modal-dialog {
		-webkit-transform: translate(0, -25%);
		-ms-transform: translate(0, -25%);
		-o-transform: translate(0, -25%);
		transform: translate(0, -25%);
		-webkit-transition: -webkit-transform 0.3s ease-out;
		-o-transition: -o-transform 0.3s ease-out;
		transition: transform 0.3s ease-out;
	}
	.modal.in .modal-dialog {
		-webkit-transform: translate(0, 0);
		-ms-transform: translate(0, 0);
		-o-transform: translate(0, 0);
		transform: translate(0, 0);
	}
	.modal-open .modal {
		overflow-x: hidden;
		overflow-y: auto;
	}
	.modal-dialog {
		padding: 15px;
		position: relative;
		width: auto;
		margin: 10px;
	}
	.modal-content {
		position: relative;
		background-color: #ffffff;
		border: 1px solid #999999;
		border: 1px solid rgba(0, 0, 0, 0.2);
		border-radius: 6px;
		-webkit-box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
		box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
		-webkit-background-clip: padding-box;
		background-clip: padding-box;
		outline: 0;
		padding: 15px;
	}
	.modal-header .close {
		margin-top: -2px;
	}
	.modal-footer .btn + .btn {
		margin-left: 5px;
		margin-bottom: 0;
	}
	.modal-footer .btn-group .btn + .btn {
		margin-left: -1px;
	}
	.modal-footer .btn-block + .btn-block {
		margin-left: 0;
	}
	@media (min-width: 768px) {
		.modal-dialog {
			width: 600px;
			margin: 30px auto;
		}
		.modal-content {
			-webkit-box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
		}
	}
	@media (min-width: 992px) {
		.modal-lg {
			width: 900px;
		}
	}
</style>


{/if}