<div class="col-xs-12 no-padding">
    <ul id="tabs" class="nav nav-tabs tabs col-sm-12 pull-left no-padding">
        <li class="active">
            <a href="#tabHome" data-toggle="tab">基本信息</a>
        </li>
        <li>
            <a href="#tabContent" data-toggle="tab">正文</a>
        </li>
        <li class="tab_items">
            <a href="#tabItems" data-toggle="tab">投票选项</a>
        </li>
        <li class="pull-right" style="display: none;">
            <button type='button' class="btn btn-success btn-flat pull-right" onclick="appendFile()">投票选项 ＋
            </button>
        </li>
    </ul>
</div>
<div id="tabContents" class="col-xs-12 tab-content no-padding">
    <div id="tabHome" class="tab-pane fade in active padding-t-15">
        <div class="form-group">
            <label class="col-sm-1 control-label">标题</label>
            <div class="col-sm-11">
                {!! Form::text('title', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label">投票类型</label>
            <div class="col-sm-5">
                <div class="pull-left col-sm-3 no-padding">
                    {!! Form::select('multiple', \Modules\Vote\Models\Vote::MULTIPLES, null, ['class' => 'form-control']) !!}
                </div>
            </div>
            {!! Form::label('link', '外链:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-1">
                {!! Form::select('link_type', \Modules\Vote\Models\Vote::getLinkTypes(), null, ['class' => 'form-control','onchange'=>'return showLink(this.value,true)']) !!}
            </div>
            <div class="col-sm-4" id="link"></div>
        </div>
        <div class="form-group">
            {!! Form::label('begin_date', '开始日期:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                <div class='input-group date' id='begin_date'>
                    {!! Form::text('begin_date', null, ['class' => 'form-control']) !!}
                    <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span>
                </div>
            </div>
            {!! Form::label('end_date', '截止日期:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                <div class='input-group date' id='end_date'>
                    {!! Form::text('end_date', null, ['class' => 'form-control']) !!}
                    <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span>
                </div>
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('image_url', '图片地址:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-11">
                {!! Form::text('image_url', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            <label for="image_file" class="control-label col-sm-1">上传图片:</label>
            <div class=" col-sm-11">
                <input id="image_file" name="image_file" type="file" class="file" data-preview-file-type="text"
                       data-upload-url="/admin/files/upload?type=image">
            </div>
        </div>
    </div>

    <div id="tabContent" class="tab-pane fade padding-t-15">
        <div class="form-group">
            <div class="col-sm-12">
                {!! Form::textarea('content', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div id="tabItems" class="tab-pane fade padding-t-15">
        <div class="edit_file1">
            @if(isset($vote))
                @foreach($vote->items as $k=>$item)
                    <div class="file1 panel panel-default">
                        <div class="box-body">
                            <div class="input-group">
                                <ul id="tabs{{$k+1}}" class="nav nav-tabs">
                                    <li class="active">
                                        <a href="#tabHome{{$k+1}}" data-toggle="tab"><label
                                                    class="no-margin">投票选项({{$k+1}})</label></a>
                                    </li>
                                </ul>
                                <span class="input-group-addon files_del"
                                      style="border-left: 1px solid #d2d6de;cursor: pointer;"><span
                                            class="glyphicon glyphicon-remove"></span></span>
                            </div>
                            <div id="tabItems{{$k+1}}" class="tab-content">
                                <div id="tabHome{{$k+1}}" class="tab-pane fade in active padding-t-15">
                                    <div class="col-sm-8 pull-left" style="padding-left: 0;">
                                        <div class="form-group">
                                            <input type="hidden" name="item_id[]" value="{{$item->id}}">
                                            <div class="col-sm-12">
                                                <input type="text" id="item_title{{$k+1}}" class="form-control "
                                                       value="{{$item->title}}"
                                                       name="item_title[]" placeholder="输入标题">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <textarea name="summary[]" class="col-sm-12 form-control" rows="11"
                                                          placeholder="输入描述"
                                                          id="summary{{$k+1}}">{{ $item->summary }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 pull-right" data-id="{{$item->id}}"
                                         style="padding-right: 0;">
                                        <div class="col-sm-12">
                                            <input name="item_url[]" id="item_url{{$k+1}}" type="hidden"
                                                   value="{{$item->url}}">
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <input id="items_file{{$k+1}}" name="item_file" type="file" class="file"
                                                       data-preview-file-type="text"
                                                       data-upload-url="/admin/files/upload?type=image">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        var this_url = $('#item_url' + '{{$k+1}}').val();
                        var image_items = [];

                        if (this_url == null || this_url.length > 0) {
                            image_items = ['<img height="200" src="' + this_url + '" class="thumb">'];
                        }

                        $('#items_file' + '{{$k+1}}').fileinput({
                            language: 'zh',
                            uploadExtraData: {_token: '{{ csrf_token() }}'},
                            allowedFileExtensions: ['jpg', 'gif', 'png'],
                            initialPreview: image_items,
                            maxFileSize: 10240,
                            initialPreviewConfig: [{key: 1}],
                            deleteUrl: '/admin/files/delete?_token={{csrf_token()}}',
                            browseClass: 'btn btn-success',
                            browseIcon: '<i class=\"glyphicon glyphicon-picture\"></i>',
                            removeClass: "btn btn-danger",
                            removeIcon: '<i class=\"glyphicon glyphicon-trash\"></i>',
                            uploadClass: "btn btn-info",
                            uploadIcon: '<i class=\"glyphicon glyphicon-upload\"></i>',
                        }).on('fileuploaded', function (event, data) {
                            $('#item_url' + '{{$k+1}}').val(data.response.data);
                        }).on('filedeleted', function (event, key) {
                            $('#item_url' + '{{$k+1}}').val('');
                        });
                    </script>
                @endforeach
            @endif
        </div>
    </div>
</div>

<div class="col-xs-12 box-footer">
    <a href="{{$base_url}}" type="button" class="btn btn-default">取　消</a>
    <button type="submit" class="btn btn-info pull-right submit">保　存
    </button>
</div>

<style>
    .kv-file-content .thumb, .kv-file-content .file-preview-image {
        height: 130px !important;
    }

    .file-zoom-content .thumb {
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 100%;
    }
</style>

<script>
    $(function () {
        $('#begin_date').datetimepicker({
            format: 'YYYY/MM/DD HH:mm',
            locale: 'zh-cn'
        });
        $('#end_date').datetimepicker({
            format: 'YYYY/MM/DD HH:mm',
            locale: 'zh-cn'
        });
    });

    $('.tabs li').click(function () {
        if ($(this).hasClass('tab_items')) {
            $(this).parent().find('>li:last').show();
        } else {
            $(this).parent().find('>li:last').hide();
        }
    })
    $('.tabs li:last').click(function () {
        $(this).show();
    });

    $(document).ready(function () {
        CKEDITOR.replace('content', {
            height: 800,
            filebrowserUploadUrl: '/admin/files/upload?type=image&_token={{  csrf_token() }}'
        });
    });

    function showLink(type, is_edit) {
        if (type == '{{\Modules\Vote\Models\Vote::LINK_TYPE_NONE}}') {
            $('#link').html('');
        } else if (type == '{{\Modules\Vote\Models\Vote::LINK_TYPE_WEB}}') {
            $('#link').html('{!! Form::text('link', null, ['class' => 'form-control','id'=>'text']) !!}');
            if (is_edit == true) {
                $('#text').val('');
            }
        }
    }

    @if(isset($vote))
        showLink('{{ $vote->link_type }}', false);
            @endif

    var image_url = $('#image_url').val();
    var images = [];

    if (image_url == null || image_url.length > 0) {
        images = ['<img height="200" src="' + image_url + '">'];
    }

    $('#image_file').fileinput({
        language: 'zh',
        uploadExtraData: {_token: '{{ csrf_token() }}'},
        allowedFileExtensions: ['jpg', 'gif', 'png'],
        initialPreview: images,
        maxFileSize: 10240,
        initialPreviewConfig: [{key: 1}],
        deleteUrl: '/admin/files/delete?_token={{csrf_token()}}',
        browseIcon: '<i class=\"glyphicon glyphicon-picture\"></i>',
        removeClass: "btn btn-danger",
        removeIcon: '<i class=\"glyphicon glyphicon-trash\"></i>',
        uploadClass: "btn btn-info",
        uploadIcon: '<i class=\"glyphicon glyphicon-upload\"></i>',
    }).on('fileuploaded', function (event, data) {
        $('#image_url').val(data.response.data);
    }).on('filedeleted', function (event, key) {
        $('#image_url').val('');
    });

    var i = $(".file1").length;
    function appendFile() {
        var n = i + 1;
        i++;

        var html = '<div class="file1 panel panel-default">' +
                '<div class="box-body"><div class="input-group"><ul id="tabs' + n + '" class="nav nav-tabs">' +
                '<li class="active"><a href="#tabHome' + n + '" data-toggle="tab">' +
                '<label class="no-margin">投票选项(' + n + ')</label></a></li></ul>' +
                '<span class="input-group-addon files_del" style="border-left: 1px solid #d2d6de;cursor: pointer;">' +
                '<span class="glyphicon glyphicon-remove"></span></span></div>' +
                '<div id="tabItems' + n + '" class="tab-content">' +
                '<div id="tabHome' + n + '" class="tab-pane fade in active padding-t-15">' +
                '<div class="col-sm-8 pull-left" style="padding-left: 0;">' +
                '<div class="form-group"><input type="hidden" name="item_id[]" value=""><div class="col-sm-12">' +
                '<input type="text" id="item_title' + n + '" class="form-control " value="" name="item_title[]" placeholder="输入标题"></div></div>' +
                '<div class="form-group"><div class="col-sm-12">' +
                '<textarea name="summary[]" class="col-sm-12 form-control" rows="11" placeholder="输入描述" id="summary' + n + '"></textarea></div></div></div> ' +
                '<div class="col-sm-4 pull-right" style="padding-right: 0;"><div class="col-sm-12"> ' +
                '<input name="item_url[]" id="item_url' + n + '"  type="hidden" value=""></div> ' +
                '<div class="form-group"><div class="col-sm-12">' +
                '<input id="item_file' + n + '" name="item_file" type="file" class="file" data-preview-file-type="text" data-upload-url="/admin/files/upload?type=image"></div></div></div></div></div></div></div>'

        $(".edit_file1").append(html);

        var this_url = $('#item_url' + n).val();
        var image_items = [];

        if (this_url == null || this_url.length > 0) {
            image_items = ['<img height="200" src="' + this_url + '">'];
        }

        $('#item_file' + n).fileinput({
            language: 'zh',
            uploadExtraData: {_token: '{{ csrf_token() }}'},
            allowedFileExtensions: ['jpg', 'gif', 'png'],
            initialPreview: image_items,
            maxFileSize: 10240,
            initialPreviewConfig: [{key: 1}],
            deleteUrl: '/admin/files/delete?_token={{csrf_token()}}',
            browseClass: 'btn btn-success',
            browseIcon: '<i class=\"glyphicon glyphicon-picture\"></i>',
            removeClass: "btn btn-danger",
            removeIcon: '<i class=\"glyphicon glyphicon-trash\"></i>',
            uploadClass: "btn btn-info",
            uploadIcon: '<i class=\"glyphicon glyphicon-upload\"></i>',
        }).on('fileuploaded', function (event, data) {
            $('#item_url' + n).val(data.response.data);
        }).on('filedeleted', function (event, key) {
            $('#item_url' + n).val('');
        });
    }

    @if(!isset($vote))
        appendFile();
    @endif

     $('.submit').click(function () {
        var ret = true;
        $('.file').each(function (k, obj) {
            var files = $(this).fileinput('getFileStack');

            if (files.length > 0) {
                return ret = toast('warning', '请先上传文件!');
            }
        });
        return ret;
    });

    $('#tabItems').delegate('.files_del', 'click', function () {
        var cur_num = $(".file1").length;
        if (cur_num < 2) {
            return toast('warning', '投票选项最少有一个');
        } else {
            $(this).parents('div.file1').remove();
        }
    })
</script>