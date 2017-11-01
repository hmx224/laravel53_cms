<script>
    $.getJSON('/admin/dictionaries/tree', function (data) {
        $('#tree').treeview({
            showTags: true,
            searchResultColor: 'white',
            data: data,
            levels: 3,
            onNodeSelected: function (event, data) {
                location.href = '/admin/dictionaries?parent_id=' + data.id;
            }
        });
        if (getNodeIndex(getQueryString('parent_id'), data) >= 0) {
            $('#tree').treeview('selectNode', [nodeIndex, {silent: true}]);
        }
        else {
            $('#tree').treeview('selectNode', [0, {silent: true}]);
        }
    });

    $('#table').bootstrapTable({
        onEditableSave: function (field, row, old, $el) {
            $.ajax({
                url: "/admin/dictionaries/" + row.id + '/save',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'value': row.value,
                    'sort': row.sort,
                },
                success: function (data, status) {
                    $('#table').bootstrapTable('refresh');
                },
                error: function (data) {
                    alert('Error');
                }
            });
        }
    });

    $('#table').on('all.bs.table', function (e, name, args) {
        $(window).resize();
    });

    function actionFormatter(value, row, index) {
        return [
            '<button class="btn btn-primary btn-xs edit" data-toggle="modal" data-target="#modal_form">' +
            '<i class="fa fa-edit" data-toggle="tooltip" data-placement="left" title="编辑" ></i></button>',
            '<span> </span>',
            '<button class="btn btn-danger btn-xs remove" data-toggle="modal" data-target="#modal"><i class="fa fa-trash"></i></button>'
        ].join('');
    }

    $("#modal_remove").click(function () {
        var row_id = $(this).data('id');
        $.ajax({
            url: '/admin/dictionaries/' + row_id + '/delete',
            data: {'_token': '{{ csrf_token() }}'},
            success: function (data) {
                window.location.reload();
            }
        });
    });

    window.actionEvents = {
        'click .edit': function (e, value, row, index) {
            $('#form').attr('action', '/admin/dictionaries/' + row.id);
            $('#method').val('PUT');
            $('#code').val(row.code);
            $('#name').val(row.name);
            $('#value').val(row.value);
        },
        'click .remove': function (e, value, row, index) {
            $('#modal_remove').data('id', row.id);
        },
    };

    $('#btn_create').click(function () {
        $('#form').attr('action', '/admin/dictionaries');
        $('#method').val('POST');
        $('#code').val('');
        $('#name').val('');
        $('#value').val('');
    });
</script>