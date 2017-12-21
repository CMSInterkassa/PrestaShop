<section id="ik_backup" data-ik_co_id="{$ik_co_id}" data-ik_pm_no="{$ik_pm_no}" data-ik_desc="{$ik_desc}" data-ik_am="{$ik_am}" data-ik_cur="{$ik_cur}" data-ik_suc_u="{$ik_suc_u}" data-ik_fal_u="{$ik_fal_u}" data-ik_pnd_u="{$ik_pnd_u}" data-ik_ia_u="{$ik_ia_u}" data-ik_sign="{$ik_sign}">
    <p>{l s='Interkassa' mod='interkassa'}</p>
</section>
{if $api_mode == 'on'}
<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#InterkassaModal">{l s='Select Payment System' mod='interkassa'}</button>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="{$ik_dir}assets/ik.js"></script>
<script>
  selpayIK.req_uri='{$ajax_url}';
</script>
<link href="{$ik_dir}assets/ik.css" rel="stylesheet">
<div id="InterkassaModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="plans">
            <div class="container">
                <div class="row">
                    <h1>
                        1. {l s='Select Payment System' mod='interkassa'}<br>
                        2. {l s='Specify currency' mod='interkassa'}<br>
                        3. {l s='Press Pay' mod='interkassa'}
                    </h1>
                    <div class="row">
                    {foreach $payment_systems as $ps => $info}{if $ps!='test'||($ps=='test' && $mode=='test')}
                        <div class="col-md-3 text-center payment_system">
                            <div class="panel panel-warning panel-pricing">
                                <div class="panel-heading">
                                    <img src="{$ik_dir}/assets/paysystems/{$ps}.png" alt="{$info['title']}">
                                    <h3>{$info['title']}</h3>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div id="radioBtn" class="btn-group radioBtn">
                                            {foreach $info['currency'] as $currency => $currencyAlias}
                                                {if $currency == $shop_cur}
                                                    <a class="btn btn-primary btn-sm active" data-toggle="fun"
                                                       data-title="{$currencyAlias}">{$currency}</a>
                                                {else}
                                                    <a class="btn btn-primary btn-sm notActive" data-toggle="fun"
                                                       data-title="{$currencyAlias}">{$currency}</a>
                                                {/if}
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
                    {/if}{/foreach}
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/if}
