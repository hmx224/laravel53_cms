<ul id="tabs" class="nav nav-tabs">
    <li class="active">
        <a href="#tabHome" data-toggle="tab">基本信息</a>
    </li>
    <li>
        <a href="#tabAndroid" data-toggle="tab">android</a>
    </li>
    <li>
        <a href="#tabIos" data-toggle="tab">ios</a>
    </li>
</ul>
<div id="tabContents" class="tab-content">
    <div id="tabHome" class="tab-pane fade in active padding-t-15">
        <div class="form-group">
            {!! Form::label('name', '名称:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-11">
                {!! Form::text('name', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('logo_url', 'logo地址:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-11">
                {!! Form::text('logo_url', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            <label for="logo_file" class="control-label col-sm-1">上传logo:</label>
            <div class="col-sm-11">
                <input id="logo_file" name="image_file" type="file" class="file" data-preview-file-type="text"
                       data-upload-url="/admin/files/upload?type=image">
            </div>
        </div>
    </div>

    <div id="tabAndroid" class="tab-pane fade padding-t-15">
        <div class="form-group">
            {!! Form::label('android_version', '版本号:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('android_version', null, ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('android_force', '强制更新:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::select('android_force',['否','是'], isset($apps) ?  $apps->android_force : 0 ,['class' => 'form-control col-sm-2']) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('android_url', '程序下载地址:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-11">
                {!! Form::text('android_url', null, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="form-group">
            <label for="other_file" class="control-label col-sm-1">上传程序:</label>
            <div class="col-sm-11">
                <input id="android_other_file" name="other_file" type="file" class="file"
                       data-upload-url="/admin/files/upload?type=file" data-show-preview="false">
            </div>
        </div>
    </div>

    <div id="tabIos" class="tab-pane fade padding-t-15">
        <div class="form-group">
            {!! Form::label('ios_version', '版本号:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('ios_version', null, ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('ios_force', '强制更新:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::select('ios_force',['否','是'], isset($apps) ?  $apps->ios_force : 0 ,['class' => 'form-control col-sm-2']) !!}
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('ios_url', '程序下载地址:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-11">
                {!! Form::text('ios_url', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
</div>

<div class="box-footer">
    <a href="/admin/apps">
        <button type="button" class="btn btn-default">取　消</button>
    </a>
    <button type="submit" class="btn btn-info pull-right" id="submit">确　定</button>
</div>

<script>
    var android_url = $('#android_url').val();
    var ios_url = $('#ios_url').val();
    var logo_url = $('#logo_url').val();
    var images = [];
    var others_android = [];
    var others_ios = [];

    var android_postfix = android_url.substr(android_url.indexOf("."));
    var ios_postfix = ios_url.substr(ios_url.indexOf("."));

    if (android_url == null || android_url.length > 0) {
        others_android = [
            '<div class="file-preview-text" style="text-align: center;">' +
            '<h2 style="font-size: 50px;"><i class="glyphicon glyphicon-file"></i></h2>' +
            android_postfix + '</div>'
        ];
    }

    if (ios_url == null || ios_url.length > 0) {
        others_ios = [
            '<div class="file-preview-text" style="text-align: center;">' +
            '<h2 style="font-size: 50px;"><i class="glyphicon glyphicon-file"></i></h2>' +
            ios_postfix + '</div>'
        ];
    }

    if (logo_url == null || logo_url.length > 0) {
        images = ['<img height="240" src="' + $('#logo_url').val() + '">'];
    }

    $('#android_other_file').fileinput({
        language: 'zh',
        uploadExtraData: {_token: '{{ csrf_token() }}'},
        allowedFileTypes: ['object'],
        initialPreview: others_android,
    });

    $('#android_other_file').on('fileuploaded', function (event, data) {
        $('#android_url').val(data.response.data);
    });

    $('#logo_file').fileinput({
        language: 'zh',
        uploadExtraData: {_token: '{{ csrf_token() }}'},
        initialPreview: images,
    });

    $('#logo_file').on('fileuploaded', function (event, data) {
        $('#logo_url').val(data.response.data);
    });

    $('#submit').click(function () {
        var other_file = $('#other_file').fileinput('getFileStack');
        if (other_file.length > 0) {
            return toast('info', '请先上传文件!')
        }
    });
</script>
