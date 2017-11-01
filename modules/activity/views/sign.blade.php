<div class="row">
    <div class="box box-info">
        <div class="box-body">

            <table id="items_table"
                   data-toggle="table"
                   data-url="/admin/activities/data/table/{{$activity_id}}"
                   data-pagination="true"
                   data-show-export="true"
                   data-toolbar="#toolbar"
                   data-page-list="[25, 50, 100, 1000, 10000]"
                   data-page-size="15"
            >
                <thead>
                <tr>
                    <th data-field="id" data-align="center" data-width="30">ID</th>
                    <th data-field="person_name" data-align="center" data-width="120">姓名</th>
                    <th data-field="person_mobile" data-align="center" data-width="120">手机号</th>
                    <th data-field="nick_name" data-align="center" data-width="120">会员</th>
                    <th data-field="ip" data-align="center" data-width="120">IP</th>
                    <th data-field="created_at" data-align="center" data-width="120">创建时间</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    (function () {
        $('#items_table').bootstrapTable({
            onEditableSave: function (field, row, old, $el) {
                row._token = '{{ csrf_token() }}';
                $.ajax({
                    type: "put",
                    url: "/admin/surveys/data/" + row.id,
                    data: row,
                    success: function (data, status) {
                        $('#items_table').bootstrapTable('refresh');
                    },
                    error: function (data) {
                        alert('Error');
                    },
                });
            },
        });

    })();
</script>