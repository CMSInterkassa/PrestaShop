<section>
    <p>{l s='Accepting payments by credit card quickly and safely with Interkassa 2.0' mod='interkassa2'}</p>
</section>

{if $api_mode == 'on'}
<!-- Trigger the modal with a button -->
<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#InterkassaModal">{l s='Select Payment System' mod='interkassa2'}</button>

<!-- Modal -->
<div id="InterkassaModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="plans">
            <div class="container">
                <div class="row">
                    <h1>
                        1.{l s='Select Payment System' mod='interkassa2'}<br>
                        2.{l s='Specify currency' mod='interkassa2'}<br>
                        3.{l s='Press Pay' mod='interkassa2'}
                    </h1>
                    {foreach $payment_systems as $ps => $info}
                        <div class="col-md-3 text-center payment_system">
                            <div class="panel panel-warning panel-pricing">
                                <div class="panel-heading">
                                    <img src="{$ik_dir}paysystems/{$ps}.png" alt="{$info['title']}">
                                    <h3>{$info['title']}</h3>
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div id="radioBtn" class="btn-group">
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
                                        s='Pay via' mod='interkassa2'}
                                        <br>
                                        <strong>{$info['title']}</strong>
                                    </a>
                                </div>
                            </div>
                        </div>
                    {/foreach }
                </div>
            </div>
        </div>
    </div>
</div>



<script type="text/javascript">

    var curtrigger = false;

        $('.ik-payment-confirmation').click(function () {
            if(!curtrigger){
                alert('Вы не выбрали валюту');
                return;
            }
            $('#conditions-to-approve input').click();
            $('#payment-confirmation button').click();

        });

    $('#radioBtn a').click(function () {
        curtrigger = true;
        var ik_cur = this.innerText;
        var ik_pw_via = $(this).attr('data-title');
        var form = $('[action = "https://sci.interkassa.com/"]');


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
//                $('#InterkassaModal').modal('hide');
            })
            .fail(function () {
                alert('Something wrong');
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


{/if}
<style>
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
        padding: 8px;
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
</style>