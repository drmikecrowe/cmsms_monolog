<link rel="stylesheet" type="text/css"
      href="/modules/CMSMonolog/bower_components/datatables/media/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css"
      href="/modules/CMSMonolog/bower_components/datatables-tabletools/css/dataTables.tableTools.css">
<script type="text/javascript" charset="utf8"
        src="/modules/CMSMonolog/bower_components/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8"
        src="/modules/CMSMonolog/bower_components/datatables-tabletools/js/dataTables.tableTools.js"></script>
<script type="text/javascript" src="/modules/CMSMonolog/bower_components/jquery.inview/jquery.inview.min.js"></script>

<style type="text/css">
    .search_initlb {
        width: 50px;
    }
    .dataTables_wrapper {
        min-height: 800px;
    }
    .dataTables_filter {
        display: none;
    }
</style>

<div style="min-height: 800px">
    <img title="Refresh" id="refresh{$id}" src="/modules/CMSMonolog/templates/Actions-view-refresh-icon.png" style="float:right; cursor: pointer;" />
    <img title="Clear Filter" id="clear{$id}" src="/modules/CMSMonolog/templates/Actions-edit-clear-icon.png" style="float:right; cursor: pointer; padding-right: 10px;" />
    <div id="message{$id}" Title="Loading Data" class="ui-helper-hidden ui-state-error">Please wait...</div>
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="datatable{$id}" width="90%">
        <thead>
        <tr>
            {counter start=0 assign="key"}
            {foreach from=$headers key=k item=v}
                <th><input {if $show_search[$key]==true }type="text" {else}type="hidden"{/if} name="search{$id}_{$k}"
                           value="Search" class="dt{$id} search_init{$id}" dtidx="{$key}"/></th>
                {counter}
            {/foreach}
        </tr>
        <tr>
            {foreach from=$headers key=k item=v}
                <th scope="col">{$v}</th>
            {/foreach}
        </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
        </tfoot>
    </table>
    <script type="text/javascript">
        $('#message{$id}').dialog({
            autoOpen: false,
            show: 'fade',
            position: 'center center',
            resizable: false,
            draggable: false,
            modal: true
        });
        var {$id}InitVals = new Array();
        var oTable{$id};
        jQuery(document).ready(function () {
            $('#datatable{$id}').bind('inview', function (event, isInView, visiblePartX, visiblePartY) {
                $('#datatable{$id}').unbind('inview');
                oTable{$id} = jQuery('#datatable{$id}').DataTable({
                    {$searching}
                    {if isset($batch)}{$batch|html_entity_decode}{/if}
                    {$add}
                    'dom': '<"top"i>rt<"bottom"flp><"clear">S',
                    'deferRender': true,
                    'searching': true,
                    'pagelength': 10,
                    'ajax': '{$urlajax|html_entity_decode}',
                    'processing': true,
                    'autoWidth': false,
                    'serverSide': true,
                    "order": [
                        [ {$sort}, "{$sortdir}" ]
                    ]
                });

                $("thead input").keyup(function (e) {
                    if ($(this).val().length < 999 && e.keyCode == 13) {
                        oTable{$id}.columns(this.getAttribute('dtidx')).search(this.value).draw();
                    }
                });

                $("thead input").each(function (i) {
                    if ($(this).hasClass("dt{$id}")) {
                        {$id}InitVals[i] = this.value;
                    }
                });

                $("thead input").focus(function () {
                    if ($(this).hasClass("search_init{$id}")) {
                        $(this).removeClass("search_init{$id}");
                        this.value = "";
                    }
                });

                $("thead input").blur(function (i) {
                    if ($(this).hasClass("dt{$id}")) {
                        $(this).addClass("search_init{$id}");
                        this.value = {$id}InitVals[$("thead input").index(this)];
                    }
                });
                {if isset($search)}{$search}{/if}
            });
            $("#refresh{$id}").click(function(elem) {
                oTable{$id}.ajax.reload();
            });
            $("#clear{$id}").click(function(elem) {
                {foreach $headers as $k}
                oTable{$id}.columns({$k@index}).search('').draw();
                {/foreach}
            });
        });
    </script>
</div>
