{foreach from=$app_messages key=key item=item}
<div class="alert alert-{$key}">{$item}</div>
{foreachelse}

{/foreach}