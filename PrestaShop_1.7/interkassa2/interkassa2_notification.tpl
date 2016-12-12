
{if $status == 'success'}
    <p>{l s='Ваш заказ ' mod='interkassa2'} <span class="bold">{$shop_name}</span> {l s='успешно оплачен.' mod='interkassa2'}
        <br /><br /><span class="bold">{l s='Ваш заказ будет доставлен так быстро, как это возможно.' mod='interkassa2'}</span>
        <br /><br />{l s='В случае любых вопросов свяжитесь с нами' mod='interkassa2'} <a href="{$link->getPageLink('contact', true)}">{l
            s='Поддержка клиентов' mod='interkassa2'}</a>.
        <br /><br />{l s='Вы можете просмотреть свою ' mod='interkassa2'} <a href="{$link->getPageLink('history', true)}">{l
            s='Историю заказов' mod='interkassa2'}</a>.
    </p>
    {else}
    {if $status == 'waitAccept'}
    <p>{l s='Ваш заказ' mod='interkassa2'} <span class="bold">{$shop_name}</span> {l s='ожидает оплаты.' mod='interkassa2'}
        <br /><br /><span class="bold">{l s='Ваш заказ будет доставлен так быстро, как это возможно, после получения факта оплаты.'
            mod='interkassa2'}</span>
        <br /><br />{l s='В случае любых вопросов свяжитесь с нами' mod='interkassa2'} <a href="{$link->getPageLink('contact', true)}">{l s='Поддержка клиентов' mod='interkassa2'}</a>.
    </p>
    {else}
    <p class="canceled">
        {l s='Ваш заказ не был оплачен. Если вам кажется что это была ошибка, свяжитесь с нами.' mod='interkassa2'}
        <a href="{$link->getPageLink('contact', true)}">{l s='Поддержка клиентов' mod='interkassa2'}</a>.
    </p>
    {/if}
{/if}
