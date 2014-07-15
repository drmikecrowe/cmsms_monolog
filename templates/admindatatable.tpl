<link rel="stylesheet" type="text/css" href="/modules/CMSMonolog/datatables/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="/modules/CMSMonolog/datatables/css/TableTools.css">
<script type="text/javascript" charset="utf8" src="/modules/CMSMonolog/datatables/js/jquery.dataTables.min.js" ></script>
<script type="text/javascript" charset="utf8" src="/modules/CMSMonolog/datatables/js/TableTools.min.js" ></script>
<script type="text/javascript" src="/modules/CMSMonolog/datatables/jquery.inview.min.js"></script>

<style type="text/css">
    .dataTables_processing {
        border: 1px solid #cd0a0a; background: #fef1ec url(/modules/CMSMonolog/datatables/images/ui-bg_inset-soft_95_fef1ec_1x100.png) 50% bottom repeat-x; color: #cd0a0a;
    }
    .search_initlb {
        width: 50px;
    }
    .dataTables_filter { display: none; }
</style>

<div class="demo_jui">
    <div id="message{$id}" Title="Loading Data" class="ui-helper-hidden ui-state-error">Please wait...</div>
    <table cellpadding="0" cellspacing="0" border="0" class="display" id="datatable{$id}" width="90%">
        <thead>
        <tr>
            {counter start=0 assign="key"}
            {foreach from=$headers key=k item=v}
                <th><input {if $show_search[$key]==true }type="text"{else}type="hidden"{/if} name="search{$id}_{$k}" value="Search" class="dt{$id} search_init{$id}" dtidx="{$key}" /></th>
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
    <script type="text/javascript">{literal}
        $('#message{/literal}{$id}{literal}').dialog({
            autoOpen: false,
            show: 'fade',
            position: 'center center',
            resizable:false,
            draggable:false,
            modal:true
        });
        var {/literal}{$id}{literal}InitVals = new Array();
        jQuery(document).ready(function() {
            $('#datatable{/literal}{$id}{literal}').bind('inview', function(event, isInView, visiblePartX, visiblePartY) {
                $('#datatable{/literal}{$id}{literal}').unbind('inview');
                var oTable{/literal}{$id}{literal} = jQuery('#datatable{/literal}{$id}{literal}').dataTable({
                    {/literal}{$searching}{literal}
                    {/literal}{if isset($batch)}{$batch|html_entity_decode}{/if}{literal}
                    {/literal}{$add}{literal}
                    sDom: '<"top"i>rt<"bottom"flp><"clear">S',
                    "scrollY": "200px",
                    "deferRender": true,
                    sPlaceHolder: "head:after",
                    'bFilter': true,
                    'iDisplayLength': 50,
                    'sAjaxSource': "{/literal}{$urlajax|html_entity_decode}{literal}",
                    'bProcessing': true,
                    'bAutoWidth': false,
                    'bServerSide': true,
                    //"bStateSave": true,
                    "aaSorting": [[ {/literal}{$sort}{literal}, "{/literal}{$sortdir}{literal}" ]],
                    "fnPreDrawCallback": function( oSettings ) {
                        //$('#message{/literal}{$id}{literal}').dialog('close');
                        return true;
                    },
                    "oScroller": {
                        "displayBuffer": 10
                    }
                });

                $("thead input").keyup( function (e) {
                    if ( $(this).val().length < 999 && e.keyCode == 13) {
                        //$('#message{/literal}{$id}{literal}').dialog('open');
                        oTable{/literal}{$id}{literal}.fnFilter( this.value, this.getAttribute('dtidx') );
                    }
                } );

                $("thead input").each( function (i) {
                    if ( $(this).hasClass("dt{/literal}{$id}{literal}") )
                    {
                        {/literal}{$id}{literal}InitVals[i] = this.value;
                    }
                } );

                $("thead input").focus( function () {
                    if ( $(this).hasClass("search_init{/literal}{$id}{literal}") )
                    {
                        $(this).removeClass("search_init{/literal}{$id}{literal}");
                        this.value = "";
                    }
                } );

                $("thead input").blur( function (i) {
                    if ( $(this).hasClass("dt{/literal}{$id}{literal}") )
                    {
                        $(this).addClass("search_init{/literal}{$id}{literal}");
                        this.value = {/literal}{$id}{literal}InitVals[$("thead input").index(this)];
                    }
                } );
                {/literal}{if isset($search)}{$search}{/if}{literal}
            } );
        });
        {/literal}
    </script>
</div>
