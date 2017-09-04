<section>
    <p>{l s='Accepting payments by credit card quickly and safely with Interkassa 2.0' mod='interkassa2'}</p>
</section>

{if $api_mode == 'on'}
<!-- Trigger the modal with a button -->
<button type="button" style="display:none;" id="modalopenbut" class="btn btn-info btn-lg" data-toggle="modal" data-target="#InterkassaModal">{l s='Select Payment System' mod='interkassa2'}</button>

<!-- Modal -->
<div id="InterkassaModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="plans">
            <div class="container">
                <div class="row">
                    <h1>
                        0.{l s='Select Payment System' mod='interkassa2'}<br>
                        1.{l s='Specify currency' mod='interkassa2'}<br>
                        2.{l s='Press Pay' mod='interkassa2'}
                    </h1>
                    {foreach $payment_systems as $ps => $info}
                        <div class="col-md-2 text-center payment_system">
                            <div class="panel panel-warning panel-pricing">
                                <div class="panel-heading">
                                    <img src="{$ik_dir}paysystems/{$ps}.png" alt="{$info['title']}">
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="btn-group radioBtn">
                                            {foreach $info['currency'] as $currency => $currencyAlias}
                                                <a class="btn tempclass btn-primary notActive" data-toggle="fun"
                                                       data-title="{$currencyAlias}">{$currency}</a>
                                            {/foreach }
                                        </div>
                                        <input type="hidden" name="fun" id="fun">
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach }
                </div>
                <div>
                    <a class="btn btn-lg btn-block-secondary btn-success ik-payment-confirmation"  href="#"><!--btn-block-->{l
                        s='Pay' mod='interkassa2'}
                        <br>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

function test(){
$("#modalopenbut").click();
}
     var curtrigger = false;
    
    function paystart(string){
        data_array = JSON.parse(string);
        console.log(data_array);var form = $('[action = "{$url}"]');
        if(data_array['resultCode']!=0){
            //alert(data_array['resultMsg']);
            form[0].action="https://sci.interkassa.com/";
            $('input[name =  "ik_act"]').remove();
            $('input[name =  "ik_int"]').remove();
                form.submit();
        }
        else{
            if(data_array['resultData']['paymentForm']!=undefined)
            {
                var data_send_form=[];
                var data_send_inputs=[];
                data_send_form['url'] = data_array['resultData']['paymentForm']['action'];
                data_send_form['method'] = data_array['resultData']['paymentForm']['method'];
                for(var i in data_array['resultData']['paymentForm']['parameters']){
                    data_send_inputs[i]=data_array['resultData']['paymentForm']['parameters'][i];
                }
                $('body').append('<form method="'+data_send_form['method']+'" id="tempform" action="'+data_send_form['url']+'"></form>');
                for(var i in data_send_inputs){
                    $("#tempform").append('<input type="hidden" name="'+i+'" value="'+data_send_inputs[i]+'" />');
                }
                $('#tempform').submit();
            }
            else{
                $('.col-md-8').append('<div id="tempdiv">'+data_array['resultData']['internalForm']+'</div>');
                var form2=$('#internalForm');
                //$('input[name =  "ik_act"]').remove();
                //$('input[name =  "ik_int"]').remove();
                //$('input[name =  "sci[ik_int]"]').remove();
                form2[0].action="javascript:test2()";
            }
        }
    }
    
    function test2(){
    var form2=$('#internalForm');
    var msg2 = form2.serialize();
    console.log(msg2);
        $.ajax({
            type: 'POST',
            url: '{$ajax_url2}',
            data: msg2,
            success: function(data_unser) {
                console.log(data_unser);
                paystart2(data_unser);
            },
            error:  function(xhr, str){
                alert('Возникла ошибка: ' + xhr.responseCode);
            }
        });
    }
    
    function paystart2(string){
        data_array = JSON.parse(string);
        console.log(data_array);
        var form2=$('#internalForm');
        if(data_array['resultCode']!=0){
            //alert(data_array['resultMsg']);
            form2[0].action="https://sci.interkassa.com/";
            form2.submit();
        }
        else{
            $('#tempdiv').html('');
            if(data_array['resultData']['paymentForm']!=undefined)
            {
                var data_send_form=[];
                var data_send_inputs=[];
                data_send_form['url'] = data_array['resultData']['paymentForm']['action'];
                data_send_form['method'] = data_array['resultData']['paymentForm']['method'];
                for(var i in data_array['resultData']['paymentForm']['parameters']){
                    data_send_inputs[i]=data_array['resultData']['paymentForm']['parameters'][i];
                }
                $('#tempdiv').append('HALLLLOOOOOOOOO<form method="'+data_send_form['method']+'" id="tempform2" action="'+data_send_form['url']+'"></form>');
                for(var i in data_send_inputs){
                    $("#tempform2").append('<input type="hidden" name="'+i+'" value="'+data_send_inputs[i]+'" />');
                }
                $('#tempform2').submit();
            }
            else{
                $('.col-md-8').append('<div id="tempdiv">'+data_array['resultData']['internalForm']+'</div>');
            }
        }
    }
    
    window.onload = function() {

        $('.ik-payment-confirmation').click(function () {
            if (!curtrigger) {
                alert('Вы не выбрали валюту');
                return;
            }
            var form = $('[action = "{$url}"]');
            if($('input[name =  "ik_pw_via"]').val()!='test_interkassa_test_xts')
            {
                form.append(
                            $('<input>', {
                                type: 'hidden',
                                name: 'ik_act',
                                val: 'process'
                            }));
                form.append(
                            $('<input>', {
                                type: 'hidden',
                                name: 'ik_int',
                                val: 'json'
                            }));
                var msg = form.serialize();
                console.log(msg);
                $.ajax({
                    type: 'POST',
                    url: '{$ajax_url2}',
                    data: msg,
                    success: function(data_unser) {
                        paystart(data_unser);
                    },
                    error:  function(xhr, str){
                        alert('Возникла ошибка: ' + xhr.responseCode);
                    }
                });
            }
            else
            {
                form[0].action="https://sci.interkassa.com/";
                form.submit();
            }
         $('#InterkassaModal').modal('hide');       
        });

        $('.radioBtn a').click(function () {
            curtrigger = true;
            var ik_cur = this.innerText;
            var ik_pw_via = $(this).attr('data-title');
            var form = $('[action = "{$url}"]');

            if ($('input[name =  "ik_pw_via"]').length > 0) {
                $('input[name =  "ik_pw_via"]').val(ik_pw_via);
            } else {
                form.append(
                        $('<input>', {
                            type: 'hidden',
                            name: 'ik_pw_via',
                            val: ik_pw_via
                        }));
            }
            $.post('{$ajax_url}', form.serialize())
                    .done(function (data) {
                        if ($('input[name =  "ik_sign"]').length > 0) {
                            $('input[name =  "ik_sign"]').val(data);
                        }
//                $('#InterkassaModal').modal('hide');
                    })
                    .fail(function () {
                        alert('Something wrong');
                    });
        });

        $('.radioBtn a').on('click', function () {
            var sel = $(this).data('title');
            var tog = $(this).data('toggle');
            $('#' + tog).prop('value', sel);
            $('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
            $('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');
        })
    }

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

    .btn-block-secondary{
        display:block;
        width:30%;
        position: relative;
        margin: auto;
    }

    .radioBtn .notActive {
        color: #3276b1;
        background-color: #fff;
    }
    .payment_system
    {
    height:235px;
    }
    .tempclass{
    font-size:10px;
    }
</style>
