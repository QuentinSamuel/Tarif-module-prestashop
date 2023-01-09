{if !$tax_enabled}
    {if Configuration::get('TARIF_TYPE') == '0'}
        {$price|displayPrice}
    {elseif Configuration::get('TARIF_TYPE') == '1'}
        {$price_tax_exc|displayPrice}
    {else}
        {$price|displayPrice} {$price_tax_exc|displayPrice}
    {/if}
{else}
    {if Configuration::get('TARIF_TYPE') == '0'}
        {$price|displayPrice}
    {elseif Configuration::get('TARIF_TYPE') == '1'}
        {$price|displayPrice}
    {else}
        {$price|displayPrice} {$price|displayPrice}
    {/if}
{/if}
