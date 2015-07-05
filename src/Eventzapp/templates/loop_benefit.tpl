<li class="list-group-item" id="{$benefit->eventfbid}">
  <span class="badge" data-toggle="tooltip" title="{$benefit->info_text}">{$benefit->num_people_claimed}</span>
  <a href="{$app_url}/{$app_routes.viplist}/{$benefit->eventfbid}/{$app_routes.download}"
    target="benefits-viewer" title="{$benefit->name}">
    {profile_picture id={$benefit->eventfbid} size=20}
    {$benefit->name}
  </a>
</li>
