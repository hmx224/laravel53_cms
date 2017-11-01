<div class="cb-toolbar">操作:</div>
<div class="btn-group margin-bottom">

    @if($state != \Modules\Video\Models\Video::DEFAULT_STATE_LIST)
        <label class="cb-toolbar"><input type="checkbox" id="check">全选</label>
    @else
        <input type="hidden" name="state" id="state" value=""/>
    @endif

    <button class="btn btn-primary btn-xs margin-r-5" id="btn_create"
            onclick="window.location.href='{{ $base_url }}' + '/create?category_id=' + $('#category_id').val();">新增
    </button>
    <button class="btn btn-success btn-xs margin-r-5 state" value="{{ \Modules\Video\Models\Video::STATE_PUBLISHED }}">
        发布
    </button>
    <button class="btn btn-warning btn-xs margin-r-5 state" value="{{ \Modules\Video\Models\Video::STATE_CANCELED }}">
        撤回
    </button>
    <button class="btn btn-danger btn-xs margin-r-5" id="btn_delete"
            value="{{ \Modules\Video\Models\Video::STATE_DELETED }}" onclick="remove()" data-toggle="modal"
            data-target="#modal">删除
    </button>
    <button class="btn btn-default btn-xs margin-r-5" id="btn_sort">排序</button>

    <button class="btn {{$state == \Modules\Video\Models\Video::DEFAULT_STATE ?  'btn-primary': 'btn-default'}} btn-xs margin-r-5"
            id="grid">网格布局
    </button>

    <button class="btn {{$state ==  \Modules\Video\Models\Video::DEFAULT_STATE_LIST ?  'btn-primary': 'btn-default'}} btn-xs margin-r-5 "
            id="list">列表布局
    </button>

</div>
<div class="btn-group margin-bottom pull-right">
    @if($state != \Modules\Video\Models\Video::DEFAULT_STATE_LIST)
        <button type="button"
                class="btn  btn-xs margin-r-5 filter {{ $state  == \Modules\Video\Models\Video::DEFAULT_STATE ?  'btn-info':'btn-default' }} "
                data-active="btn-info" value="{{ \Modules\Video\Models\Video::DEFAULT_STATE }}">全部
        </button>
        <button type="button"
                class="btn  btn-xs margin-r-5 filter  {{ $state == \Modules\Video\Models\Video::STATE_NORMAL ?  'btn-primary':'btn-default' }}"
                data-active="btn-primary" value="{{ \Modules\Video\Models\Video::STATE_NORMAL }}">未发布
        </button>
        <button type="button"
                class="btn  btn-xs margin-r-5 filter {{ $state == \Modules\Video\Models\Video::STATE_PUBLISHED ?  'btn-success':'btn-default' }}"
                data-active="btn-success"
                value="{{ \Modules\Video\Models\Video::STATE_PUBLISHED }}">已发布
        </button>
        <button type="button"
                class="btn btn-xs margin-r-5 filter {{ $state == \Modules\Video\Models\Video::STATE_CANCELED ?  'btn-warning':'btn-default' }}"
                data-active="btn-warning"
                value="{{ \Modules\Video\Models\Video::STATE_CANCELED }}">已撤回
        </button>
        <button type="button"
                class="btn btn-xs margin-r-5 filter {{ $state  == \Modules\Video\Models\Video::STATE_DELETED  ?  'btn-danger' : 'btn-default'}} "
                data-active="btn-danger"
                value="{{ \Modules\Video\Models\Video::STATE_DELETED }}">已删除
        </button>
        <button type="button" class="btn btn-default btn-xs margin-r-5" style="height:22px;" data-toggle="modal"
                data-target="#modal_query">
            <span class="fa fa-search"></span>
        </button>
    @else
        <button type="button"
                class="btn  btn-xs btn-info margin-r-5 filter"
                data-active="btn-info" value="">全部
        </button>
        <button type="button"
                class="btn  btn-xs btn-default margin-r-5 filter"
                data-active="btn-primary" value="{{ \Modules\Video\Models\Video::STATE_NORMAL }}">未发布
        </button>
        <button type="button"
                class="btn  btn-xs  btn-default margin-r-5 filter"
                data-active="btn-success"
                value="{{ \Modules\Video\Models\Video::STATE_PUBLISHED }}">已发布
        </button>
        <button type="button"
                class="btn btn-xs  btn-default margin-r-5 filter"
                data-active="btn-warning"
                value="{{ \Modules\Video\Models\Video::STATE_CANCELED }}">已撤回
        </button>
        <button type="button"
                class="btn btn-xs btn-default margin-r-5 filter"
                data-active="btn-danger"
                value="{{ \Modules\Video\Models\Video::STATE_DELETED }}">已删除
        </button>
        <button type="button" class="btn btn-default btn-xs margin-r-5" style="height:22px;" data-toggle="modal"
                data-target="#modal_query">
            <span class="fa fa-search"></span>
        </button>
    @endif
</div>

<script>
    /*当前标识*/
    global_state = {{$state}}

    $("#list").click(function () {
        window.location.href = '{{ $base_url }}/list';
    })
    $("#grid").click(function () {
        window.location.href = '{{ $base_url }}';
    })
    /*菜单批量操作*/
    function getCheckedIds() {
        var ids = [];
        $('.check').each(function () {
            if ($(this).prop('checked')) {
                ids[ids.length] = $(this).attr('value');
            }
        });
        return ids;
    }
    /*删除*/
    var remove_open = false;
    $("#modal_remove").click(function () {
        if (remove_open == true) {
            return false;
        }
        var state = {{ Modules\Video\Models\Video::STATE_DELETED }};
        var ids = getCheckedIds();
        $.ajax({
            url: '{{ $base_url }}' + '/state',
            type: 'POST',
            data: {'_token': '{{ csrf_token() }}', 'ids': ids, 'state': state},
            success: function () {
                $('#modal').modal('hide');
                window.location.reload();
            }
        });
    });

    function remove() {
        remove_open = false;
        var ids = getCheckedIds();
        if (ids.length > 0) {
            $('#msg').html('您确认删除这<strong><span class="text-danger">' + ids.length + '</span></strong>条信息吗？');
            $('#modal_remove').show();
        } else {
            $('#msg').html('请选择要删除的数据！');
            $('#modal_remove').hide();
        }
    }

    /* 修改状态 */
    $('.state').click(function () {
        var state = $(this).val();
        var ids = getCheckedIds();
        if (ids.length > 0) {
            $.ajax({
                url: '{{ $base_url }}' + '/state',
                type: 'POST',
                data: {'_token': '{{ csrf_token() }}', 'ids': ids, 'state': state},
                success: function () {
                    window.location.reload();
                }
            });
        }
    });

    /* 筛选 */
    $('.filter').click(function () {
        var state = $(this).val();
        console.log(state);
        if (global_state == {{ \Modules\Video\Models\Video::DEFAULT_STATE_LIST }}) {
            $('#state').val($(this).val());
            $('#table').bootstrapTable('refresh', {
                query: {
                    state: $(this).val(),
                    _token: '{{ csrf_token() }}'
                }
            });
        } else {
            switch (state) {
                case "{{ \Modules\Video\Models\Video::STATE_DELETED }}":
                    window.location.href = '{{ $base_url }}/filters/' + state;
                    break;
                case "{{ \Modules\Video\Models\Video::STATE_NORMAL }}":
                    window.location.href = '{{ $base_url }}/filters/' + state;
                    break;
                case "{{ \Modules\Video\Models\Video::STATE_CANCELED }}":
                    window.location.href = '{{ $base_url }}/filters/' + state;
                    break;
                case "{{ \Modules\Video\Models\Video::STATE_PUBLISHED }}":
                    window.location.href = '{{ $base_url }}/filters/' + state;
                    break;
                case "{{ \Modules\Video\Models\Video::DEFAULT_STATE }}":
                    window.location.href = '{{ $base_url }}';
                    break;
            }
        }

        //改变按钮样式
        $('.filter').removeClass('btn-primary btn-info btn-success btn-danger btn-warning');
        $('.filter').addClass('btn-default');
        $(this).removeClass('btn-default');
        $(this).addClass($(this).data('active'));

    });


</script>