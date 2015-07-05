<table class="table table-condensed table-hover">
  <caption>
    {include file='benefit_header.tpl'}
  </caption>
  <thead>
    <tr>
      <th class="text-muted" title="N&uacute;mero de ordem">#</th>
      <th title="Nome completo">Nome</th>
      <th class="hidden-xs" title="Perfil no Facebook">Facebook</th>
      <th class="">Identidade</th>
      <th class="hidden-print" title="Compareceu ao evento?">Foi?</th>
      <th class="hidden-xs" title="Adicionado(a) por">Por&nbsp;<span class="hidden-print glyphicon glyphicon-info-sign" data-toggle="tooltip" title="Mostra quem adicionou à lista (geralmente um promoter ou a equipe técnica do app)"></span></th>
  </thead>
  <tbody>
{assign var="counter" value="0"}
{foreach from=$users item=user}
    {assign var="counter" value="{$counter+1}"}
    <tr class="{level_css_class level=$user->trust_level}" id="user-{$user->fbid}-benefit-{$user->eventfbid}_{$user->benefit}">
      <td valign="top">
        <a class="text-muted" href="#user-{$user->fbid}" <a onclick="prompt('Aperte Ctrl+C e Enter', {$user->fbid}'); _gaq.push(['_trackEvent', 'copy_id', 'click', '{$user->fbid}']); return false;">
          {$counter}
        </a>
      </td>
      <td valign="top">
          {if $user->access_level > 0}
            <span class="label hidden-xs pull-left">
              {if $user->access_level eq 1}
                Promoter
              {elseif $user->access_level eq 2}
                Colaborador
              {elseif $user->access_level eq 3}
                Administrador
              {elseif $user->access_level eq 4}
                Super Administrador
              {else}
              {/if}
            </span>
          {/if}

            {if $user->private > 0}
            <small class="text-muted pull-right" data-toggle="tooltip" title="Esta pessoa entrou no modo privado">
              <span class="glyphicon glyphicon-lock"></span>
            </small>
            {else}
              <a class="pull-right" href="//facebook.com/{$user->fbid}" onClick=" _gaq.push(['_trackEvent', 'user_fbname_listing', 'link_click', '{$user->fbid}']);">
                {profile_picture id=$user->fbid name=$user->fbname size=20 class='pull-left'}
              </a>
            {/if}
            <a
              href="{$app_url}/users/{$user->fbid}"
              onClick="_gaq.push(['_trackEvent', 'user_fullname_listing', 'link_click', '{$user->fbid}']);"
              title="{$user->name}">{$user->name}</a>
          </a>
      </td>
      <td class="hidden-xs" valign="top">
          {$user->fbname}
      </td>
      <td class="" valign="top">
        <small class="label label-default text-muted unselectable">
          <abbr data-toggle="tooltip"
          {if $user->fbgender eq 'female'}title="mulher">&#9792;
          {elseif $user->fbgender eq 'male'}title="homem">&#9794;
          {else}
          {/if}
          </abbr>
        </small>
        &nbsp;
        {$user->id_card}
      </td>
      <td class="hidden-print" nowrap="nowrap" valign="top">
        <input
          class="js-switch"
          data-checked-by-server="{if $user->actually_attended eq '1'}true{else}false{/if}"
          data-benefitid="{$user->benefit}"
          data-eventfbid="{$user->eventfbid}"
          data-userfbid="{$user->fbid}"
          id="actually_attended_{$user->fbid}_{$user->eventfbid}_{$user->benefit}"
          name="actually_attended_{$user->fbid}_{$user->eventfbid}_{$user->benefit}"
          title="Por favor, defina se {$user->fbname} compareceu a este evento"
          type="checkbox"
          {if $user->actually_attended eq '1'}checked="checked"{/if} />
          &nbsp;
          <label
  					id="label_actually_attended_{$user->fbid}_{$user->eventfbid}_{$user->benefit}"
  					for="actually_attended_{$user->fbid}_{$user->eventfbid}_{$user->benefit}">
  					{if $user->actually_attended eq '1'}Sim{else}Não{/if}
  				</label>
      </td>
      <td class="hidden-xs">
        {if isset($user->chosen_by_fbid)}
          <a data-toggle="tooltip" href="{$app_url}/users/{$user->chosen_by_fbid}">
            {profile_picture id=$user->chosen_by_fbid size=20 class='pull-left'}
          </a>
        {/if}
      </td>
    </tr>
{foreachelse}
    <tr>
      <td class="alert alert-warning">Lista vazia.</td>
    </tr>
{/foreach}
  </tbody>
</table>
