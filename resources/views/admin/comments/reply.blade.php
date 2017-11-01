<div class="modal fade common" id="modal_replies" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width:850px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal_replies" aria-hidden="true"> &times;</button>
                <h4 class="modal-title" id="replies_title"></h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="window_msg" style="display: none;">
                    <p></p>
                </div>
                <div id="reply_contents">
                    @yield('handle')
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('.close').click(function () {
        $('#modal_replies').modal('hide');
    });
</script>