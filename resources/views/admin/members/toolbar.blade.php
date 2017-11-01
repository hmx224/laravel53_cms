<div class="cb-toolbar">操作:</div>
<div class="btn-group margin-bottom">
    <input type="hidden" name="state" id="state" value=""/>
    <button class="btn btn-primary btn-xs margin-r-5" id="create" onclick="location='/admin/members/create';">新增</button>
</div>
<div class="btn-group margin-bottom pull-right">
    <button type="button" class="btn btn-info btn-xs margin-r-5 filter"  data-active="btn-info" id="" value="">全部</button>
    <button type="button" class="btn btn-default btn-xs margin-r-5 filter" data-active="btn-success"
            value="{{ \App\Models\Member::STATE_ENABLED }}">已启用
    </button>
    <button type="button" class="btn btn-default btn-xs margin-r-5 filter" data-active="btn-danger"
            value="{{ \App\Models\Member::STATE_DISABLED }}">已禁用
    </button>
    <button type="button" class="btn btn-default btn-xs margin-r-5" id="query" data-toggle="modal" data-target="#modal_query">查询</button>
</div>

<script>
    /* 筛选 */
    $('.filter').click(function () {
        $('#state').val($(this).val());
        $('#table').bootstrapTable('selectPage', 1);

        //改变按钮样式
        $('.filter').removeClass('btn-primary btn-info btn-success btn-danger btn-warning');
        $('.filter').addClass('btn-default');
        $(this).removeClass('btn-default');
        $(this).addClass($(this).data('active'));
    });
</script>