<main>
  <ul id="container" class="list-inline has-crush">
  {foreach from=$current_viplist_users item=user}
  <li class="item col-xs-4 col-sm-3 col-md-2 col-lg-2">
    <a
      class="attendee-gender-{$user->fbgender} {if $user->private eq 1 AND $current_user_is_administrator eq 1}private{/if}"
      data-placement="bottom"
      data-toggle="tooltip" href="//facebook.com/{$user->fbid}"
      id="attendee-{$user->fbid}"
      onClick="_gaq.push( [ '_trackEvent', 'attendee', 'click_image', '{$user->fbid}' ] );"
      rel="external" target="_blank" title="{$user->fbname}">
      <span class="clear clearfix"></span>

      {profile_picture class='img-responsive' id=$user->fbid lazy='lazy' name=$user->fbname size=190}

      {if $user->private eq 1 AND $current_user_is_administrator eq 1}
      <span class="label label-info">
        <span class="glyphicon glyphicon-lock"></span>&nbsp;Privado
      </span>
      {/if}

      <span class="clear clearfix"></span>
    </a>
  </li>
  {foreachelse}
    <li class="alert alert-info">
      <p>Parece que ninguém entrou nesta lista ainda...
        atualize daqui a alguns minutinhos pra ter certeza.</p>
    </li>
  {/foreach}
  </ul>
  <div class="clear clearfix"></div>
  <hr />
</main>
<script src="{$app_url}/bower_components/dist/masonry.pkgd.min.js"></script>
<script type="text/javascript">
if(Masonry) {
  var msnry = new Masonry( '#container', { columnWidth = 190 } );
}
</script>
