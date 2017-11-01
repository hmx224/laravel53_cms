<ul id="tabs" class="nav nav-tabs">
    <li class="active">
        <a href="#tabHome" data-toggle="tab">基本信息</a>
    </li>
    <li>
        <a href="#tabContent" data-toggle="tab">正文</a>
    </li>
</ul>
<div id="tabContents" class="tab-content">
    <div id="tabHome" class="tab-pane fade in active padding-t-15">
        <div class="form-group">
            {!! Form::label('name', '简称:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('name', null, ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('code', '编码:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-2">
                {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => '请输入小写英文']) !!}
            </div>
            {!! Form::label('module_id', '模块:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-2">
                {!! Form::select('module_id', $modules, null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('title', '标题:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-11">
                {!! Form::text('title', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('author', '作者:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('author', null, ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('link', '外链:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('link', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('summary', '摘要:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-11">
                {!! Form::textarea('summary', null, ['rows'=>'4','class' => 'form-control']) !!}
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
            <div class="col-sm-11">
                <input id="image_file" name="image_file" type="file" data-preview-file-type="text"
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
</div>

<div id="tabGallery" class="tab-pane fade">
</div>
</div>

<div class="box-footer">
    <button type="button" class="btn btn-default" onclick="window.history.back();">取　消</button>
    <button type="submit" class="btn btn-info pull-right" id="submit">保　存</button>
</div><!-- /.box-footer -->

<script>
    var image_url = $('#image_url').val();
    var images = [];

    if (image_url == null || image_url.length > 0) {
        images = ['<img height="240" src="' + $('#image_url').val() + '">'];
    }

    $('#image_file').fileinput({
        language: 'zh',
        uploadExtraData: {_token: '{{ csrf_token() }}'},
        allowedFileExtensions: ['jpg', 'gif', 'png'],
        initialPreview: images,
        initialPreviewAsData: false,
        initialPreviewConfig: [{key: 1}],
        deleteUrl: '/admin/files/delete?_token={{csrf_token()}}',
        maxFileSize: 10240,
        maxFileCount: 1,
        resizeImage: true,
        maxImageWidth: 640,
        maxImageHeight: 960,
        resizePreference: 'width',
        fileActionSettings: {
            showZoom: false
        },
    }).on('fileuploaded', function (event, data) {
        $('#image_url').val(data.response.data);
    }).on('filedeleted', function (event, key) {
        $('#image_url').val('');
    });

    $('#submit').click(function () {
        var image_file = $('#image_file').fileinput('getFileStack');

        if (image_file.length > 0) {
            return toast('info', '请先上传图片!');
        }

        @if(isset($parent_id) && $parent_id > 0)
        @if(isset($state) && $state == \App\Models\Category::STATE_DISABLED)
        @if(isset($parent_state) && $parent_state == \App\Models\Category::STATE_DISABLED)
        $('#window_msg').slideDown(100);
        $('#window_msg p').html('此栏目有父栏目，请先启用父栏目');
        @endif
        @endif
        @endif

        $('#window_msg').hide();
    });

    $(document).ready(function () {
        CKEDITOR.replace('content', {
            filebrowserUploadUrl: '/admin/files/upload?type=image&_token={{  csrf_token() }}'
        });
    });
</script>