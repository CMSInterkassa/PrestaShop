
{if $status == 'success'}
    <p>{l s='Your order ' mod='interkassa2'} <span class="bold">{$shop_name}</span> {l s='successfully paid.' mod='interkassa2'}
        <br /><br /><span class="bold">{l s='Your order will be delivered as quickly as possible.' mod='interkassa2'}</span>
        <br /><br />{l s='In case of any questions please contact us' mod='interkassa2'} <a href="{$link->getPageLink('contact', true)}">{l
            s='Customer Support' mod='interkassa2'}</a>.
        <br /><br />{l s='You can view your ' mod='interkassa2'} <a href="{$link->getPageLink('history', true)}">{l
            s='order history' mod='interkassa2'}</a>.
    </p>
    {else}
    {if $status == 'waitAccept'}
    <p>{l s='Your order' mod='interkassa2'} <span class="bold">{$shop_name}</span> {l s='awaiting payment.' mod='interkassa2'}
        <br /><br /><span class="bold">{l s='Your order will be delivered as quickly as possible after the receipt after payment.'
            mod='interkassa2'}</span>
        <br /><br />{l s='In case of any questions please contact us' mod='interkassa2'} <a href="{$link->getPageLink('contact', true)}">{l s='Customer Support' mod='interkassa2'}</a>.
    </p>
    {else}
    <p class="canceled">
        {l s='Your order has not been paid. If you think that this was an error, please contact us.' mod='interkassa2'}
        <a href="{$link->getPageLink('contact', true)}">{l s='Customer Support' mod='interkassa2'}</a>.
    </p>
    {/if}
{/if}
