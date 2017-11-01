<div class="btn-group margin-bottom col-md-12" style="padding: 0;">
    <input type="hidden" name="state" id="state" value=""/>
    <div class="btn-group pull-right">
        <button type="button" class="btn btn-info btn-xs margin-r-5 filter" data-active="btn-info" value="">全部</button>
        <button type="button" class="btn btn-default btn-xs margin-r-5 filter" data-active="btn-primary" value="{{ \App\Models\Order::STATE_UNPAID }}">待支付</button>
        <button type="button" class="btn btn-default btn-xs margin-r-5 filter" data-active="btn-success" value="{{ \App\Models\Order::STATE_COMPLETED }}">已完成</button>
        <button type="button" class="btn btn-default btn-xs margin-r-5 filter" data-active="btn-warning" value="{{ \App\Models\Order::STATE_WAIT_REFUND }}">待退款</button>
        <button type="button" class="btn btn-default btn-xs margin-r-5 filter" data-active="btn-success" value="{{ \App\Models\Order::STATE_ALREADY_REFUND }}">已退款</button>
        <button type="button" class="btn btn-default btn-xs margin-r-5" data-toggle="modal" data-target="#modal_query"><span class="fa fa-search"></span></button>
    </div>
</div>

<script>
    var remove_open = false;
    $("#modal_remove").click(function () {
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
            url: '/admin/orders/state/' + state,
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
            $('#msg').html('您确认取消这<strong><span class="text-danger">' + rows.length + '</span></strong>件订单吗？');
            $('#modal_remove').show();
        } else {
            $('#msg').html('请选择要取消的订单！');
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
                url: '/admin/orders/state/' + state,
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
        var value = $(this).val();
        $('#state').val(value);
        $('#table').bootstrapTable('selectPage', 1);

        //改变按钮样式
        $('.filter').removeClass('btn-primary btn-info btn-success btn-danger btn-warning');
        $('.filter').addClass('btn-default');
        $(this).removeClass('btn-default');
        $(this).addClass($(this).data('active'));
    });
</script>