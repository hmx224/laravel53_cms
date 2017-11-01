<div class="modal fade common" id="modal_category" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" style="width:360px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"> &times;</button>
                <h4 class="modal-title" id="modal_title">选择栏目</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="window_msg" style="display: none;">
                    <p></p>
                </div>
                <div id="contents">
                    <div class="row" id="content">
                        <div class="no-padding pull-left col-sm-12">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div id="trees"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                        <button type="button" class="btn btn-primary" id="btn_tree_submit">确认</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var category_ids = new Array();

    function getTrees(user_id, ids) {
        var url = '/admin/users/tree/' + user_id;
        category_ids = ids;
        $.getJSON(url, function (data) {
            $('#trees').treeview({
                data: data,
                selectedIcon: 'glyphicon glyphicon-ok',
                multiSelect: true,
                onNodeSelected: function (event, data) {
                    if ($.inArray(data.id, category_ids) == -1) {
                        category_ids.push(data.id);
                        category_ids = $.unique(category_ids);
                    }
                },
                onNodeUnselected: function (event, data) {
                    category_ids = $.grep(category_ids, function (val) {
                        return val != data.id;
                    });
                }
            });
        });
    }

    $("#btn_tree_submit").click(function () {
        var user_id = $(this).data('id');

        $.ajax({
            url: '/admin/users/grant/' + user_id,
            type: 'POST',
            data: {'_token': '{{ csrf_token() }}', 'category_ids': category_ids},
            success: function () {
                window.location.reload();
            }
        });
    });
</script>