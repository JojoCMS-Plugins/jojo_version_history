<h3>Current Version: {$value}</h3>

{foreach from=$revisions item=r}{if $r.version != 0}
    <h3>Revision {$r.version} - {$r.date|date_format} by {$r.user}</h3>
{foreach from=$r.changelog item=field key=r}
    <h4>{$field}</h4>
    <div class="revisiondiff">
        {$r|nl2br}
    </div>
{/foreach}{/if}
{foreachelse}
    There are no recorded revisions to this content
{/foreach}

