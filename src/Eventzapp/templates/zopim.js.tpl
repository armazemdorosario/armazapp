{literal}$zopim(function() {{/literal}
{if $logged_in eq "1"}
$zopim.livechat.setName('{$current_user_name}');
$zopim.livechat.setEmail('{$current_user_email}');
$zopim.livechat.addTags('{$current_user_gender}');
{/if}
$zopim.livechat.addTags('{if $logged_in eq "1"}logged{else}not-logged{/if}');
{literal}});{/literal}
