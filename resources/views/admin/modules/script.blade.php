<script>
    $('input[name=icon]').iconpicker();

    function iconFormatter(value, row, index) {
        return '<i class="fa ' + row.icon + '"></i>';
    }

    function booleanFormatter(value, row, index) {
        if (value == 1) {
            return '<i class="fa fa-check"></i>';
        } else {
            return '';
        }
    }

    function stateFormatter(value, row, index) {
        var style = 'label-primary';
        switch (row.state_name) {
            case '已启用':
                style = 'label-success';
                break;
            case '已禁用':
                style = 'label-danger';
                break;
        }
        return [
            '<span class="label ' + style + '">' + row.state_name + '</span>',
        ].join('');
    }

    function actionFormatter(value, row, index) {
        return '<button class="btn btn-primary btn-xs edit" data-toggle="tooltip" data-placement="top" title="编辑"><i class="fa fa-edit"></i></button><span> </span>' +
            '<button class="btn btn-primary btn-xs field" data-toggle="tooltip" data-placement="top" title="字段"><i class="fa fa-list"></i></button><span> </span>';
    }

    window.actionEvents = {
        'click .edit': function (e, value, row, index) {
            $('#form').attr('action', '/admin/modules/' + row.id);
            $('#method').val('PUT');
            $('#name').val(row.name);
            $('#title').val(row.title);
            $('#table_name').val(row.table_name);
            $('#icon').val(row.icon);
            $('#groups').val(row.groups);
            $('#is_lock').bootstrapSwitch('state', row.is_lock);
            $('#is_lock').next().val(row.is_lock);
            $('#use_category').bootstrapSwitch('state', row.use_category);
            $('#use_category').next().val(row.use_category);
            $('#use_category').val(row.use_category);
            $('#sort_type').val(row.sort_type);
            $('#sort_direction').val(row.sort_direction);
            $('#modal_form').modal('show');
        },
        'click .field': function (e, value, row, index) {
            window.location.href = '/admin/modules/fields/' + row.id;
        },
    };

    $('#btn_create').click(function () {
        $('#form').attr('action', '/admin/modules');
        $('#method').val('POST');
        $('#name').val('');
        $('#title').val('');
        $('#table_name').val('');
        $('#icon').val('');
        $('#groups').val('');
        $('#is_lock').bootstrapSwitch('state', false);
        $('#is_lock').next().val(0);
        $('#user_category').bootstrapSwitch('state', false);
        $('#user_category').next().val(0);

    });

    $('#btn_copy').click(function () {
        $('#form').attr('action', '/admin/modules/copy');
        $('#method').val('POST');
        $('#name').val('');
        $('#title').val('');
        $('#table_name').val('');
        $('#icon').val('');
        $('#groups').val('');
        $('#is_lock').bootstrapSwitch('state', false);
        $('#is_lock').next().val(0);
        //获取被复制module_id
        var row = $('#table').bootstrapTable('getSelections');
        if (row.length == 0 || row.length > 1) {
            return toast('warning', '请选择一个模型');
        }
        $('#module_id').val(row[0].id);
    });
</script>