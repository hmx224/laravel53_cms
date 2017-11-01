<div class="col-xs-12 no-padding">
    <ul id="tabs" class="nav nav-tabs tabs col-sm-12 pull-left no-padding subject">
        <li class="active">
            <a href="#tabHome" data-toggle="tab">基本信息</a>
        </li>
        <li>
            <a href="#tabContent" data-toggle="tab">正文</a>
        </li>

        @if(isset($survey))
            @foreach($survey->subjects as $k=>$item_subject)
                <li class="tab_subjects_item" value="{{ $k+1 }}">
                    <a href="#tabSubjectsItems{{ $k+1 }}" data-toggle="tab">问卷题目{{ $k+1 }}</a>
                </li>
            @endforeach
        @else
            <li class="tab_subjects_item" value="1">
                <a href="#tabSubjectsItems1" data-toggle="tab">问卷题目1</a>
            </li>
        @endif

        <li class="pull-right" style="list-style:none;">
            <button type='button' class="btn btn-success btn-flat pull-right" onclick="appendSubject()">问卷题目 ＋
            </button>
        </li>
    </ul>
</div>
<div id="tabContents" class="col-xs-12 tab-content no-padding subject_content">
    <div id="tabHome" class="tab-pane fade in active padding-t-15">
        <div class="form-group">
            <label class="col-sm-1 control-label">标题</label>
            <div class="col-sm-11">
                {!! Form::text('title', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-1 control-label">问卷类型:</label>
            <div class="col-sm-5">
                {!! Form::select('multiple', \Modules\Survey\Models\Survey::MULTIPLE, null, ['class' => 'form-control']) !!}
            </div>

            {!! Form::label('link', '外链:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-1">
                {!! Form::select('link_type', \Modules\Survey\Models\Survey::getLinkTypes(), null, ['class' => 'form-control','onchange'=>'return showLink(this.value,true)']) !!}
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
                {!! Form::textarea('description', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    @if(isset($survey))
        @foreach($survey->subjects as $k=>$item_subject)
            <div id="tabSubjectsItems{{$k+1}}" class="tab-pane fade padding-t-15 tab_subjects">
                <div class="edit_file{{$k+1}}">
                    <div class="file1 panel panel-default">
                        <div class="box-body">
                            <div class="input-group">
                                <ul id="tabs{{$k+1}}" class="nav nav-tabs">
                                    <li class="active">
                                        <a href="#tabHome{{$k+1}}" data-toggle="tab"><label
                                                    class="no-margin">问卷题目</label></a>
                                    </li>
                                    <span class="pull-right">
                                    <button type="button" class="btn btn-success btn-flatpull-right"
                                            onclick="appendFile()">题目选项 ＋
                                    </button></span>
                                </ul>
                                <span class="input-group-addon files_del"
                                      style="border-left: 1px solid #d2d6de;cursor: pointer;"><span
                                            class="glyphicon glyphicon-remove"></span></span>
                            </div>
                            <div id="tabSubjects{{$k+1}}" class="tab-content">
                                <div id="tabHome{{$k+1}}" class="tab-pane fade in active padding-t-15">
                                    <div class="col-sm-8 pull-left" style="padding-left: 0;">
                                        <div class="form-group">
                                            <input type="hidden"
                                                   name="item_id_subject[]"
                                                   value="{{$item_subject->id}}">
                                            <div class="col-sm-12">
                                                <input type="text" id="item_subject{{$k+1}}"
                                                       class="form-control "
                                                       value="{{$item_subject->title}}"
                                                       name="item_subject[]" placeholder="输入标题">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <textarea name="summary_subject[]" class="col-sm-12 form-control"
                                                          rows="11"
                                                          placeholder="输入描述"
                                                          id="summary{{$k+1}}">{{ $item_subject->summary }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 pull-right" data-id="{{$item_subject->id}}"
                                         style="padding-right: 0;">
                                        <div class="col-sm-12">
                                            <input name="item_url_subject[]" id="item_url_subject{{$k+1}}"
                                                   type="hidden"
                                                   value="{{$item_subject->url}}">
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <input id="items_file_subject{{$k+1}}" name="item_file_subject"
                                                       type="file" class="file"
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
                        var this_url = $('#item_url_subject{{$k+1}}').val();
                        var image_items = [];

                        if (this_url == null || this_url.length > 0) {
                            image_items = ['<img height="200" src="' + this_url + '" class="thumb">'];
                        }

                        $("#items_file_subject{{$k+1}}").fileinput({
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
                            $('#item_url_subject{{$k+1}}').val(data.response.data);
                        }).on('filedeleted', function (event, key) {
                            $('#item_url_subject{{$k+1}}').val('');
                        });
                    </script>
                    @foreach($item_subject->items as $item_k=>$item)
                        @if($item_subject->id == $item->refer_id)
                            <div class="file1 panel panel-default">
                                <div class="box-body">
                                    <div class="input-group">
                                        <ul id="tabs{{$item_k+1}}" class="nav nav-tabs">
                                            <li class="active">
                                                <a href="#tabHome{{$item_k+1}}" data-toggle="tab"><label
                                                            class="no-margin">题目选项({{$item_k+1}})</label></a>
                                            </li>

                                        </ul>
                                        <span class="input-group-addon files_del"
                                              style="border-left: 1px solid #d2d6de;cursor: pointer;"><span
                                                    class="glyphicon glyphicon-remove"></span></span>
                                    </div>
                                    <div id="tabItems{{$item_k+1}}" class="tab-content">
                                        <div id="tabHome{{$item_k+1}}"
                                             class="tab-pane fade in active padding-t-15">
                                            <div class="col-sm-8 pull-left" style="padding-left: 0;">
                                                <div class="form-group">
                                                    <input type="hidden" name="item_id{{$k+1}}[]"
                                                           value="{{$item->id}}">
                                                    <div class="col-sm-12">
                                                        <input type="text" id="item_title{{$item_k+1}}"
                                                               class="form-control "
                                                               value="{{$item->title}}"
                                                               name="item_title{{$k+1}}[]" placeholder="输入标题">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-12">
                                                <textarea name="summary{{$k+1}}[]" class="col-sm-12 form-control"
                                                          rows="11"
                                                          placeholder="输入描述"
                                                          id="summary{{$item_k+1}}">{{ $item->summary }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4 pull-right" data-id="{{$item->id}}"
                                                 style="padding-right: 0;">
                                                <div class="col-sm-12">
                                                    <input name="item_url{{$k+1}}[]"
                                                           id="item_url_{{$item->refer_id}}_{{ $item_k+1 }}"
                                                           type="hidden"
                                                           value="{{$item->url}}">
                                                </div>
                                                <div class="form-group">
                                                    <div class="col-sm-12">
                                                        <input id="items_file_{{$item->refer_id}}_{{$item_k+1}}"
                                                               name="item_file{{$k+1}}" type="file"
                                                               class="file"
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
                                var this_url = $('#item_url_{{$item->refer_id}}_{{$item_k+1}}').val();
                                var image_items = [];

                                if (this_url == null || this_url.length > 0) {
                                    image_items = ['<img height="200" src="' + this_url + '" class="thumb">'];
                                }

                                $("#items_file_{{$item->refer_id}}_{{ $item_k+1 }}").fileinput({
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
                                    $('#item_url_{{$item->refer_id}}_{{$item_k+1}}').val(data.response.data);
                                }).on('filedeleted', function (event, key) {
                                    $('#item_url_{{$item->refer_id}}_{{$item_k+1}}').val('');
                                });
                            </script>
                        @endif

                    @endforeach

                </div>
            </div>
        @endforeach
    @else
        <div id="tabSubjectsItems1" class="tab-pane fade padding-t-15 tab_subjects">
            <div class="edit_file1">
                <div class="file1 panel panel-default">
                    <div class="box-body">
                        <div class="input-group">
                            <ul id="tabs1" class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tabHome1" data-toggle="tab"><label
                                                class="no-margin">问卷题目</label></a>
                                </li>
                                <span class="pull-right">
                                                 <button type="button" class="btn btn-success btn-flatpull-right "
                                                         onclick="appendFile()">题目选项 ＋
                                            </button></span>
                            </ul>
                            <span class="input-group-addon files_del"
                                  style="border-left: 1px solid #d2d6de;cursor: pointer;"><span
                                        class="glyphicon glyphicon-remove"></span></span>
                        </div>
                        <div id="tabItems1" class="tab-content">
                            <div id="tabHome1" class="tab-pane fade in active padding-t-15">
                                <div class="col-sm-8 pull-left" style="padding-left: 0;">
                                    <div class="form-group">
                                        <input type="hidden" name="item_id_subject[]" value="">
                                        <div class="col-sm-12">
                                            <input type="text" id="item_subject" class="form-control"
                                                   value=""
                                                   name="item_subject[]" placeholder="输入标题">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                                <textarea name="summary_subject[]" class="col-sm-12 form-control"
                                                          rows="11"
                                                          placeholder="输入描述"
                                                          id="summary_subject"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 pull-right" data-id=""
                                     style="padding-right: 0;">
                                    <div class="col-sm-12">
                                        <input name="item_url_subject[]" id="item_url_subject1" type="hidden"
                                               value="">
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12">
                                            <input id="items_file_subject1" name="item_file_subject" type="file"
                                                   class="file"
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
                    var this_url = $('#item_url_subject1').val();
                    var image_items = [];

                    if (this_url == null || this_url.length > 0) {
                        image_items = ['<img height="200" src="' + this_url + '" class="thumb">'];
                    }

                    $('#items_file_subject1').fileinput({
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
                        $('#item_url_subject1').val(data.response.data);
                    }).on('filedeleted', function (event, key) {
                        $('#item_url_subject1').val('');
                    });
                </script>
            </div>

        </div>
    @endif


</div>

<div class="col-xs-12 box-footer">
    <a href="/admin/surveys" type="button" class="btn btn-default">取　消</a>
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

    $(document).ready(function () {
        CKEDITOR.replace('description', {
            height: 800,
            filebrowserUploadUrl: '/admin/files/upload?type=image&_token={{  csrf_token() }}'
        });
    });

    function showLink(type, is_edit) {
        if (type == '{{\Modules\Survey\Models\Survey::LINK_TYPE_NONE}}') {
            $('#link').html('');
        } else if (type == '{{\Modules\Survey\Models\Survey::LINK_TYPE_WEB}}') {
            $('#link').html('{!! Form::text('link', null, ['class' => 'form-control','id'=>'text']) !!}');
            if (is_edit == true) {
                $('#text').val('');
            }
        }
    }

    @if(isset($survey))
        showLink('{{ $survey->link_type }}', false);
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

    //add subject length
    function appendFile() {
        var subject_curr = $('.tab_subjects_item.active').val();

        var i;
        @if(isset($survey))

        if (subject_curr == 0) {
            var subject_curr_num = $('.tab_subjects').length;
        } else {
            var subject_curr_num = subject_curr;
        }
        var i = $(".edit_file" + subject_curr + '>' + ".file1").length - 1;

                @else

        var i = $(".edit_file" + subject_curr + '>' + ".subject_items").length;
        if (subject_curr == 0) {
            var subject_curr_num = $('.tab_subjects').length;
            $('.tab_subjects_item.active').attr('value', subject_curr_num);
        } else if (subject_curr == undefined) {
            var subject_curr_num = $('.tab_subjects').length;
        } else {
            var subject_curr_num = $('.tab_subjects_item.active').val();
        }
                @endif

        var n = i + 1;
        i++;

        var html =
            '<div class="file1 panel panel-default subject_items">' +
            '<div class="box-body"><div class="input-group"><ul id="tabs' + n + '" class="nav nav-tabs">' +
            '<li class="active"><a href="#tabHome' + n + '" data-toggle="tab">' +
            '<label class="no-margin">题目选项(' + n + ')</label></a></li>' +
            '</ul>' +
            '<span class="input-group-addon files_del" style="border-left: 1px solid #d2d6de;cursor: pointer;">' +
            '<span class="glyphicon glyphicon-remove"></span></span></div>' +
            '<div id="tabItems' + n + '" class="tab-content">' +
            '<div id="tabHome' + n + '" class="tab-pane fade in active padding-t-15">' +
            '<div class="col-sm-8 pull-left" style="padding-left: 0;">' +
            '<div class="form-group"><div class="col-sm-12">' +
            '<input type="hidden" name="item_id' + subject_curr_num + '[]" >' +
            '<input type="text" id="item_title' + n + '" class="form-control " value="" name="item_title' + subject_curr_num + '[]" placeholder="输入标题"></div></div>' +
            '<div class="form-group"><div class="col-sm-12">' +
            '<textarea name="summary' + subject_curr_num + '[]" class="col-sm-12 form-control" rows="11" placeholder="输入描述" id="summary' + n + '"></textarea></div></div></div> ' +
            '<div class="col-sm-4 pull-right" style="padding-right: 0;"><div class="col-sm-12"> ' +
            '<input name="item_url' + subject_curr_num + '[]" id="item_url' + subject_curr_num + '_' + n + '"  type="hidden" value=""></div> ' +
            '<div class="form-group"><div class="col-sm-12">' +
            '<input id="item_file' + subject_curr_num + '_' + n + '" name="item_file' + subject_curr_num + '" type="file" class="file" data-preview-file-type="text" data-upload-url="/admin/files/upload?type=image">' +
            '</div></div></div></div></div></div></div>';

        if ($('.tab_subjects_item').hasClass('active')) {
            $(".edit_file" + subject_curr_num).append(html);
        } else {
            //default status
            if (subject_curr_num == 1) {
                $(".edit_file1").append(html);
            } else {
                $(".edit_file" + subject_curr_num).append(html);
            }
        }

        var this_url = $('#item_url' + subject_curr_num + '_' + n).val();
        var image_items = [];

        if (this_url == null || this_url.length > 0) {
            image_items = ['<img height="200" src="' + this_url + '">'];
        }

        $('#item_file' + subject_curr_num + '_' + n).fileinput({
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
            $('#item_url' + subject_curr_num + '_' + n).val(data.response.data);
        }).on('filedeleted', function (event, key) {
            $('#item_url' + subject_curr_num + '_' + n).val('');
        });
    }

    @if(!isset($survey))
        appendFile();
    @endif


    function appendSubject() {

        // 题目的追加
        var j = $('.tab_subjects').length;

        var subject_length = $('.tab_subjects_item:last').val() + 1;

        var n = j + 1;
        j++;

        var html = '<li class="tab_subjects_item">' +
            '<a href="#tabSubjectsItems' + n + '" data-toggle="tab">问卷题目' + n + '</a>' +
            '</li>';

        var subject_content =
            '<div id="tabSubjectsItems' + n + '" class="tab-pane fade padding-t-15 tab_subjects">' +
            '<div class="edit_file' + n + '">' +
            '<div class="file1 panel panel-default">' +

            '<div class="box-body"><div class="input-group"><ul id="tabs' + n + '" class="nav nav-tabs">' +
            '<li class="active"><a href="#tabHome' + n + '" data-toggle="tab">' +
            '<label class="no-margin">问卷题目</label></a></li>' +
            '<span class="pull-right">' +
            '<button type="button" class="btn btn-success btn-flatpull-right " onclick="appendFile()">题目选项 ＋ ' +
            '</button>' +
            '</span>' +
            '</ul>' +
            '<span class="input-group-addon files_del" style="border-left: 1px solid #d2d6de;cursor: pointer;">' +
            '<span class="glyphicon glyphicon-remove"></span></span></div>' +
            '<div id="tabSubjects' + n + '" class="tab-content">' +
            '<div id="tabHome' + n + '" class="tab-pane fade in active padding-t-15">' +
            '<div class="col-sm-8 pull-left" style="padding-left: 0;">' +
            '<div class="form-group">' +
            '<input type="hidden" name="item_id_subject[]" value="">' +
            '<div class="col-sm-12">' +
            '<input type="text" id="item_subject' + n + '" class="form-control " value="" name="item_subject[]" placeholder="输入标题"></div></div>' +
            '<div class="form-group"><div class="col-sm-12">' +
            '<textarea name="summary_subject[]" class="col-sm-12 form-control" rows="11" placeholder="输入描述" id="summary_subject' + n + '"></textarea></div></div></div> ' +
            '<div class="col-sm-4 pull-right" style="padding-right: 0;"><div class="col-sm-12"> ' +
            '<input name="item_url_subject[]" id="item_url_subject' + n + '"  type="hidden" value=""></div> ' +
            '<div class="form-group"><div class="col-sm-12">' +
            '<input id="item_file_subject' + n + '" name="item_file_subject" type="file" class="file" data-preview-file-type="text" data-upload-url="/admin/files/upload?type=image">' +
            '</div></div></div></div></div></div></div>' +

            '<div class="file1 panel panel-default subject_items">' +
            '<div class="box-body"><div class="input-group"><ul id="tabs' + (n ) + '" class="nav nav-tabs">' +
            '<li class="active"><a href="#tabHome' + (n) + '" data-toggle="tab">' +
            '<label class="no-margin">题目选项1</label></a></li>' +
            '</ul>' +
            '<span class="input-group-addon files_del" style="border-left: 1px solid #d2d6de;cursor: pointer;">' +
            '<span class="glyphicon glyphicon-remove"></span></span></div>' +
            '<div id="tabItems' + (n) + '" class="tab-content">' +
            '<div id="tabHome' + (n) + '" class="tab-pane fade in active padding-t-15">' +
            '<div class="col-sm-8 pull-left" style="padding-left: 0;">' +
            '<div class="form-group">' +
            '<input type="hidden" name="item_id' + (n) + '[]" value="">' +
            '<div class="col-sm-12">' +
            '<input type="text" id="item_title' + n + '" class="form-control " value="" name="item_title' + n + '[]" placeholder="输入标题"></div></div>' +
            '<div class="form-group"><div class="col-sm-12">' +
            '<textarea name="summary' + n + '[]" class="col-sm-12 form-control" rows="11" placeholder="输入描述" id="summary' + n + '"></textarea></div></div></div> ' +
            '<div class="col-sm-4 pull-right" style="padding-right: 0;"><div class="col-sm-12"> ' +
            '<input name="item_url' + (subject_length) + '[]" id="item_url' + subject_length + '_' + (1) + '"  type="hidden" value=""></div> ' +
            '<div class="form-group"><div class="col-sm-12">' +
            '<input id="item_file' + subject_length + '_' + (1) + '"  name="item_file' + subject_length + '" type="file" class="file" data-preview-file-type="text" data-upload-url="/admin/files/upload?type=image">' +
            '</div></div></div></div></div></div></div></div></div>';

        $(".subject").append(html); //追加标签

        $('.tab_subjects_item:last').attr('value', n);   //追加标签value值

        $(".subject_content").append(subject_content);       //追加内容

        var this_url = $('#item_url' + (subject_length) + '_' + (1)).val();
        var this_url_subject = $('#item_url_subject' + (n)).val();

        var image_items = [];
        var image_items_subject = [];

        if (this_url == null || this_url.length > 0) {
            image_items = ['<img height="200" src="' + this_url + '">'];
        }

        if (this_url_subject == null || this_url_subject.length > 0) {
            image_items_subject = ['<img height="200" src="' + this_url_subject + '">'];
        }

        $('#item_file' + (subject_length) + '_' + (1)).fileinput({
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
            $('#item_url' + (subject_length) + '_' + (1)).val(data.response.data);
        }).on('filedeleted', function (event, key) {
            $('#item_url' + (subject_length) + '_' + (1)).val('');
        });

        $('#item_file_subject' + (n)).fileinput({
            language: 'zh',
            uploadExtraData: {_token: '{{ csrf_token() }}'},
            allowedFileExtensions: ['jpg', 'gif', 'png'],
            initialPreview: image_items_subject,
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
            $('#item_url_subject' + (n)).val(data.response.data);
        }).on('filedeleted', function (event, key) {
            $('#item_url_subject' + (n)).val('');
        });
    }

    $('#tabContents').delegate('.files_del', 'click', function () {

        var subject_curr = $('.tab_subjects_item.active').val();

                @if(isset($survey))
        var subject_curr_num = subject_curr;
        @else
        if (subject_curr == 0) {
            var subject_curr_num = $('.tab_subjects_item').length;
        } else {
            var subject_curr_num = subject_curr;
        }
        @endif

        if ($('.tab_subjects_item').hasClass('active')) {
            var cur_num = $(".edit_file" + subject_curr_num + '>' + ".file1").length;
            if (cur_num <= 2) {
                return toast('warning', '题目和题目选项最少有一个');
            } else {
                $(this).parents('div.edit_file' + subject_curr_num + '>' + '.file1').remove();
            }
        }
    });

</script>