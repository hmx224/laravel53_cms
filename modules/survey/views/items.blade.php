<style>
    #items_table th, #items_table td {
        text-align: center;
        vertical-align: middle !important;
    }
</style>
<div class="row">
    <div class="box box-info">
        <div class="box-body">

            <table id="items_table"
                   data-toggle="table"
                   data-url="/admin/surveys/items/table/{{$survey_id}}"
                   data-pagination="true"
                   data-toolbar="#toolbar">
                <thead>
                <tr>
                    <th data-field="id" data-width="45">ID</th>
                    <th data-field="subject">问卷题目</th>
                    <th data-field="title">问卷选项</th>
                    <th data-field="count" data-width="90" data-editable="true">参与数</th>
                    <th data-field="percent" data-width="120">百分比</th>
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
                    url: "/admin/surveys/items/" + row.id,
                    data: row,
                    success: function (data, status) {
                        $('#items_table').bootstrapTable('refresh');
                    },
                    error: function (data) {
                        alert('Error');
                    },
                });
            },
            onLoadSuccess: function (data) {
                var data = $('#items_table').bootstrapTable('getData', true);
                //合并单元格
                mergeCells(data, "subject", 1, $('#items_table'));
            }
        });
    })();

    /**
     * 合并单元格
     * @param data  原始数据（在服务端完成排序）
     * @param fieldName 合并属性名称
     * @param colspan   合并列
     * @param target    目标表格对象
     */
    function mergeCells(data, fieldName, colspan, target) {
        var sortMap = {};
        for (var i = 0; i < data.length; i++) {
            for (var j in data[i]) {
                if (j == fieldName) {
                    var key = data[i][j];
                    if (sortMap.hasOwnProperty(key)) {
                        sortMap[key] = sortMap[key] * 1 + 1;
                    } else {
                        sortMap[key] = 1;
                    }
                    break;
                }
            }
        }
        var index = 0;
        for (var i in sortMap) {
            var count = sortMap[i] * 1;
            $(target).bootstrapTable('mergeCells', {index: index, field: fieldName, colspan: colspan, rowspan: count});
            index += count;
        }
    }

</script>