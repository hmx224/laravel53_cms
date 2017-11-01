<script>
    function actionFormatter(value, row, index) {
        return '<button class="btn btn-primary btn-xs edit" data-toggle="tooltip" data-placement="top" title="编辑"><i class="fa fa-edit"></i></button><span> </span>' +
            '<button class="btn btn-danger btn-xs remove modal_remove" data-toggle="modal" data-placement="top" title="删除" data-target="#modal"><i class="fa fa-trash"></i></button><span> </span>';
    }

    $('#table_field').bootstrapTable({
        onEditableSave: function (field, row, old, $el) {
            $.ajax({
                url: "/admin/modules/fields/" + row.id + '/save',
                method: 'post',
                data: {'_token': '{{ csrf_token() }}', 'index': row.index},
                success: function (data, status) {
                },
                error: function (data) {
                    return toast('warning', '保存失败');
                },
            });
        },
    });

    $('#table_column').bootstrapTable({
        onEditableSave: function (field, row, old, $el) {
            $.ajax({
                url: "/admin/modules/fields/" + row.id + '/save',
                method: 'post',
                data: {'_token': '{{ csrf_token() }}', 'column_index': row.column_index, 'column_width': row.column_width},
                success: function (data, status) {
                },
                error: function (data) {
                    return toast('warning', '保存失败');
                },
            });
        },
    });

    $('#table_editor').bootstrapTable({
        onEditableSave: function (field, row, old, $el) {
            $.ajax({
                url: "/admin/modules/fields/" + row.id + '/save',
                method: 'post',
                data: {'_token': '{{ csrf_token() }}', 'editor_index': row.editor_index, 'editor_rows': row.editor_rows, 'editor_column': row.editor_columns},
                success: function (data, status) {
                },
                error: function (data) {
                    return toast('warning', '保存失败');
                },
            });
        },
    });

    window.actionEvents = {
        'click .edit': function (e, value, row, index) {
            $('#tabs li:first').tab('show');
            $('#form').attr('action', '/admin/modules/fields/' + row.id);
            $('#method').val('PUT');
            $('#name').val(row.name);
            $('#title').val(row.title);
            $('#label').val(row.label);
            $('#type').val(row.type);
            $('#default').val(row.default);
            $('#required').bootstrapSwitch('state', row.required);
            $('#required').next().val(row.required);
            $('#unique').bootstrapSwitch('state', row.unique);
            $('#unique').val(row.unique);
            $('#unique').next().val(row.unique);
            $('#min_length').val(row.min_length);
            $('#max_length').val(row.max_length);
            $('#index').val(row.index);
            $('#column_show').bootstrapSwitch('state', row.column_show);
            $('#column_show').next().val(row.column_show);
            $('#column_editable').bootstrapSwitch('state', row.column_editable);
            $('#column_editable').next().val(row.column_editable);
            $('#column_align').val(row.column_align);
            $('#column_width').val(row.column_width);
            $('#column_formatter').val(row.column_formatter);
            $('#column_index').val(row.column_index);
            $('#editor_show').bootstrapSwitch('state', row.editor_show);
            $('#editor_show').next().val(row.editor_show);
            $('#editor_readonly').bootstrapSwitch('state', row.editor_readonly);
            $('#editor_readonly').next().val(row.editor_readonly);
            $('#editor_type').val(row.editor_type);
            $('#editor_options').val(row.editor_options);
            $('#editor_columns').val(row.editor_columns);
            $('#editor_rows').val(row.editor_rows);
            $('#editor_group').val(row.editor_group);
            $('#editor_index').val(row.editor_index);
            $('#modal_form').modal('show');
        },
        'click .remove': function (e, value, row, index) {
            $('#modal_remove').data('id', row.id);
        },
    };

    $('#btn_create').click(function () {
        $('#tabs li:first').tab('show');
        $('#form').attr('action', '/admin/modules/fields');
        $('#method').val('POST');
        $('#name').val('');
        $('#title').val('');
        $('#label').val('');
        $('#type').val(1);
        $('#default').val('');
        $('#required').bootstrapSwitch('state', false);
        $('#required').next().val(0);
        $('#unique').bootstrapSwitch('state', false);
        $('#unique').next().val(0);
        $('#min_length').val(0);
        $('#max_length').val(0);
        $('#index').val(0);
        $('#column_show').bootstrapSwitch('state', false);
        $('#column_show').next().val(0);
        $('#column_editable').bootstrapSwitch('state', false);
        $('#column_editable').next().val(0);
        $('#column_align').val(1);
        $('#column_width').val(0);
        $('#column_formatter').val('');
        $('#column_index').val(0);
        $('#editor_show').bootstrapSwitch('state', false);
        $('#editor_show').next().val(0);
        $('#editor_readonly').bootstrapSwitch('state', false);
        $('#editor_readonly').next().val(0);
        $('#editor_type').val(1);
        $('#editor_options').val('');
        $('#editor_columns').val(11);
        $('#editor_rows').val(1);
        $('#editor_group').val('{{ reset($groups) }}');
        $('#editor_index').val(0);
    });

    $('#modal_remove').click(function () {
        var row_id = $(this).data('id');
        $.ajax({
            url: '/admin/modules/fields/' + row_id,
            method: 'post',
            data: {'_token': '{{ csrf_token() }}', '_method': 'delete'},
            success: function (data) {
                window.location.reload();
            }
        });
    });

</script>