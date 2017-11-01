<div class="cb-toolbar">操作:</div>
<div class="btn-group margin-bottom">
    <input type="hidden" name="state" id="state" value=""/>
    <button class="btn btn-success btn-xs margin-r-5 action"
            value="{{ \App\Models\Message::STATE_PASSED }}">审核</button>
    <button class="btn btn-danger btn-xs margin-r-5" id="delete"
            value="{{ \App\Models\Message::STATE_DELETED }}" onclick="modalRemove()" data-toggle="modal"
            data-target="#modal">删除
    </button>
</div>
<div class="btn-group margin-bottom pull-right">
    <button type="button" class="btn btn-info btn-xs margin-r-5 filter" id="" value="">全部</button>
    <button type="button" class="btn btn-primary btn-xs margin-r-5 filter"
            value="{{ \App\Models\Message::STATE_NORMAL }}">未审核
    </button>
    <button type="button" class="btn btn-success btn-xs margin-r-5 filter"
            value="{{ \App\Models\Message::STATE_PASSED }}">已审核
    </button>
    <button type="button" class="btn btn-danger btn-xs margin-r-5 filter"
            value="{{ \App\Models\Message::STATE_DELETED }}">已删除
    </button>
</div>
<script>
    var remove_open = false;
    $("#modal_remove").delegate(this, "click", function () {
        if (remove_open == true) {
            return false;
        }

        var state = $('#delete').val();
        var rows = $('#table').bootstrapTable('getSelections');

        var ids = new Array();
        for (var i = 0; i < rows.length; i++) {
            ids[ids.length] = rows[i].id;
        }

        $.ajax({
            url: '/admin/messages/state/' + state,
            type: 'POST',
            data: {'_token': '{{ csrf_token() }}', 'ids': ids},
            success: function () {
                window.location.reload();
            }
        });
    });

    function modalRemove() {
        remove_open = false;
        var rows = $('#table').bootstrapTable('getSelections');
        if (rows.length > 0) {
            $('#msg').html('您确认删除这<strong><span class="text-danger">' + rows.length + '</span></strong>条信息吗？');
            $('#modal_remove').show();
        } else {
            $('#msg').html('请选择要删除的数据！');
            $('#modal_remove').hide();
        }
    }

    /* 操作 */
    $('.action').click(function () {
        var state = $(this).val();
        var rows = $('#table').bootstrapTable('getSelections');

        var ids = new Array();
        for (var i = 0; i < rows.length; i++) {
            ids[ids.length] = rows[i].id;
        }
        if (ids.length > 0) {
            $.ajax({
                url: '/admin/messages/state/' + state,
                type: 'POST',
                data: {'_token': '{{ csrf_token() }}', 'ids': ids},
                success: function () {
                    window.location.reload();
                }
            });
        }
    });

    /* 筛选 */
    $('.filter').click(function () {
        $('#state').val($(this).val());
        $('#table').bootstrapTable('selectPage', 1);
        $('#table').bootstrapTable('refresh', {
            query: {
                state: $(this).val(),
                _token: '{{ csrf_token() }}'
            }
        });
    });
</script>