<h3>{$title_section}</h3>

{if isset($message) }<p>{$message}</p>{/if}

{$formstart}

<table border="0">
    <tbody>
    <tr>
        <td>
            <div class="pageoverflow">
                <table cellspacing="0" border="0" align="left">
                    <tr>
                        <td>
                            <table cellspacing="0" class="pagetable" align="left">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th style="text-align: center;">Config</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach from=$fields item=entry}
                                    <tr>
                                        <td style="text-align: right;">{$entry->name}</td>
                                        <td style="text-align: right;">{$entry->input}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
    </tbody>
</table>


<div class="pageoverflow">
    <p class="pagetext">&nbsp;</p>
    <p class="pageinput">{$settingssubmit}</p>
    <p class="pageinput">
        {$clearbutton}
    </p>

</div>

{$formend}


