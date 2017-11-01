<table id="table"
       data-toggle="table"
       data-url="/admin/dictionaries/table/{{ $parent_id }}">
    <thead>
    <tr>
        <th data-field="code">编码</th>
        <th data-field="name">名称</th>
        <th data-field="value" data-editable="true">值</th>
        <th data-field="sort" data-align="center" data-width="60" data-editable="true">序号
        </th>
        <th data-field="action" data-align="center" data-width="70"
            data-formatter="actionFormatter" data-events="actionEvents">
            操作
        </th>
    </tr>
    </thead>
</table>