@foreach($_GET as $k => $v)
    <input type="hidden" id="{{ $k }}" name="{{ $k }}" value="{{ $v }}">
@endforeach
<ul id="tabs" class="nav nav-tabs">
    @foreach($module->groups as $group)
        @if (count($group->editors) > 0)
            <li class="{{ $loop->first ? 'active' : '' }}">
                <a href="#{{ 'tab_' . $group->name }}" data-toggle="tab">{{ $group->name }}</a>
            </li>
        @endif
    @endforeach
</ul>

<div class="tab-content">
    @foreach($module->groups as $group)
        @if (count($group->editors) > 0)
            <div id="{{ 'tab_' . $group->name }}"
                 class="tab-pane fade in {{ $loop->first ? 'active' : '' }} padding-t-15">
                <?php $position = 0; $index = 0; ?>
                @foreach($group->editors as $editor)
                    @if ($editor->show)
                        @if ($position == 0)
                            <div class="form-group">
                                @endif
                                @if($editor->type == \App\Models\ModuleField::EDITOR_TYPE_HTML)
                                    <div class="col-sm-{{ $editor->columns }}">
                                        {!! Form::textarea($editor->name, null, ['class' => 'form-control']) !!}
                                    </div>
                                    <script>
                                        CKEDITOR.replace('{{ $editor->name }}', {
                                            height: '{{ $editor->rows * 20 }}',
                                            filebrowserUploadUrl: '/admin/files/upload?type=image&_token={{  csrf_token() }}'
                                        });
                                    </script>
                                @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_DATETIME)
                                    {!! Form::label($editor->name, $editor->label . ':', ['class' => 'control-label col-sm-1']) !!}
                                    <div class="col-sm-{{ $editor->columns }}">
                                        <div class='input-group date'>
                                            {!! Form::text($editor->name, null, ['class' => 'form-control']) !!}
                                            <span class="input-group-addon"> <span
                                                        class="glyphicon glyphicon-calendar"></span> </span>
                                        </div>
                                    </div>
                                @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_SELECT_SINGLE)
                                    {!! Form::label($editor->name, $editor->label . ':', ['class' => 'control-label col-sm-1']) !!}
                                    <div class="col-sm-{{ $editor->columns }}">
                                        {!! Form::select($editor->name, string_to_option($editor->options), null, ['class' => 'form-control', $editor->readonly ? 'readonly' : '']) !!}
                                    </div>
                                @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_SELECT_MULTI)
                                    {!! Form::label($editor->name, $editor->label . ':', ['class' => 'control-label col-sm-1']) !!}
                                    <div class="col-sm-{{ $editor->columns }}">
                                        {!! Form::select("$editor->name[]", string_to_option($editor->options), array_to_option($editor->selected)?array_to_option($editor->selected):'', ['class' => 'form-control select2','multiple'=>'multiple']) !!}
                                    </div>
                                @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_TEXTAREA)
                                    {!! Form::label($editor->name, $editor->label . ':', ['class' => 'control-label col-sm-1']) !!}
                                    <div class="col-sm-{{ $editor->columns }}">
                                        {!! Form::textarea($editor->name, null, ['class' => 'form-control', 'rows' => $editor->rows, $editor->readonly ? 'readonly' : '']) !!}
                                    </div>
                                @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_IMAGES)
                                    <div class="col-sm-{{ $editor->columns }}">
                                        {!! Form::hidden($editor->name, null, ['class' => 'form-control', 'id' => $editor->name]) !!}
                                    </div>
                                @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_VIDEOS)
                                    <div class="col-sm-{{ $editor->columns }}">
                                        {!! Form::hidden($editor->name, null, ['class' => 'form-control', 'id' => $editor->name]) !!}
                                    </div>
                                @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_TAGS)
                                    {!! Form::label($editor->name, $editor->label . ':', ['class' => 'control-label col-sm-1']) !!}
                                    <div class="col-sm-{{ $editor->columns }}">
                                        {!! Form::select($editor->name . '[]', \App\Models\Tag::list($module->model_class)->sortByDesc('total')->pluck('name', 'name')->toArray(), null, ['class' => 'form-control select2', 'multiple'=>'multiple', $editor->readonly ? 'readonly' : '']) !!}
                                    </div>
                                @else
                                    {!! Form::label($editor->name, $editor->label . ':', ['class' => 'control-label col-sm-1']) !!}
                                    <div class="col-sm-{{ $editor->columns }}">
                                        {!! Form::text($editor->name, null, ['class' => 'form-control', $editor->required ? 'required' : '', $editor->readonly ? 'readonly' : '']) !!}
                                    </div>
                                @endif
                                <?php $position += $editor->columns + 1; if ($loop->last || $position + $group->editors[$index + 1]->columns + 1 > 12) {
                                    $position = 0;
                                } ?>
                                @if($position == 0 || $position == 12)
                            </div>
                        @endif
                        @if($editor->type == \App\Models\ModuleField::EDITOR_TYPE_IMAGE)
                            <div class="form-group">
                                <label for="{{ $editor->name . '_file' }}" class="control-label col-sm-1">上传图片:</label>
                                <div class="col-sm-11">
                                    <input id="{{ $editor->name . '_file' }}" name="{{ $editor->name . '_file' }}"
                                           type="file"
                                           class="file" data-upload-url="/admin/files/upload?type=image">
                                </div>
                            </div>
                            <script>
                                var {{ $editor->name }}_preview = $('#{{ $editor->name }}').val();
                                if ({{ $editor->name }}_preview.length > 0) {
                                    {{ $editor->name }}_preview = ['<img height="240" src="' + {{ $editor->name }}_preview + '" class="kv-preview-data file-preview-image">'];
                                }
                                $('#{{ $editor->name . '_file' }}').fileinput({
                                    language: 'zh',
                                    uploadExtraData: {_token: '{{ csrf_token() }}'},
                                    allowedFileExtensions: ['jpg', 'gif', 'png', 'webp'],
                                    maxFileSize: 10240,
                                    maxFileCount: 1,
                                    resizeImage: true,
                                    maxImageWidth: 640,
                                    maxImageHeight: 960,
                                    resizePreference: 'width',
                                    initialPreview: {{ $editor->name }}_preview,
                                    initialPreviewConfig: [{key: 1}],
                                    deleteUrl: '/admin/files/delete?_token={{csrf_token()}}',
                                    previewFileType: 'image',
                                    overwriteInitial: true,
                                    browseClass: 'btn btn-success',
                                    browseIcon: '<i class=\"glyphicon glyphicon-picture\"></i>',
                                    removeClass: "btn btn-danger",
                                    removeIcon: '<i class=\"glyphicon glyphicon-trash\"></i>',
                                    uploadClass: "btn btn-info",
                                    uploadIcon: '<i class=\"glyphicon glyphicon-upload\"></i>'
                                }).on('fileuploaded', function (event, data) {
                                    $('#{{ $editor->name }}').val(data.response.data);
                                }).on('filedeleted', function (event, key) {
                                    $('#{{ $editor->name }}').val('');
                                });
                            </script>
                        @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_VIDEO)
                            <div class="form-group">
                                <label for="{{ $editor->name . '_file' }}" class="control-label col-sm-1">上传视频:</label>
                                <div class="col-sm-11">
                                    <input id="{{ $editor->name . '_file' }}" name="{{ $editor->name . '_file' }}"
                                           type="file"
                                           class="file" data-upload-url="/admin/files/upload?type=video">
                                </div>
                            </div>
                            <script>
                                var {{ $editor->name }}_preview = $('#{{ $editor->name }}').val();
                                if ({{ $editor->name }}_preview.length > 0) {
                                    {{ $editor->name }}_preview = ['<video height="300" controls="controls" src="' + {{ $editor->name }}_preview + '"></video>'];
                                }
                                $('#{{ $editor->name . '_file' }}').fileinput({
                                    language: 'zh',
                                    uploadExtraData: {_token: '{{ csrf_token() }}'},
                                    allowedFileExtensions: ['mp4', 'mpg', 'mpeg', 'avi', 'wav', 'mp3'],
                                    maxFileSize: 1048576,
                                    initialPreview: {{ $editor->name }}_preview,
                                    initialPreviewConfig: [{key: 1}],
                                    previewFileType: 'video',
                                    deleteUrl: '/admin/files/delete?_token={{csrf_token()}}',
                                    browseClass: 'btn btn-success',
                                    browseIcon: '<i class=\"glyphicon glyphicon-hd-video\"></i>',
                                    removeClass: "btn btn-danger",
                                    removeIcon: '<i class=\"glyphicon glyphicon-trash\"></i>',
                                    uploadClass: "btn btn-info",
                                    uploadIcon: '<i class=\"glyphicon glyphicon-upload\"></i>'
                                }).on('fileuploaded', function (event, data) {
                                    $('#video_url').val(data.response.data);
                                }).on('filedeleted', function (event, key) {
                                    $('#video_url').val('');
                                });
                            </script>
                        @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_AUDIO)
                            <div class="form-group">
                                <label for="{{ $editor->name . '_file' }}" class="control-label col-sm-1">上传音频:</label>
                                <div class="col-sm-11">
                                    <input id="{{ $editor->name . '_file' }}" name="{{ $editor->name . '_file' }}"
                                           type="file"
                                           class="file" data-upload-url="/admin/files/upload?type=audio">
                                </div>
                            </div>
                            <script>
                                var {{ $editor->name }}_preview = $('#{{ $editor->name }}').val();
                                if ({{ $editor->name }}_preview.length > 0) {
                                    {{ $editor->name }}_preview = ['<audio height="100" controls="controls" src="' + {{ $editor->name }}_preview + '"></audio>'];
                                }
                                $('#{{ $editor->name . '_file' }}').fileinput({
                                    language: 'zh',
                                    uploadExtraData: {_token: '{{ csrf_token() }}'},
                                    allowedFileExtensions: ['wav', 'mp3'],
                                    maxFileSize: 1048576,
                                    initialPreview: {{ $editor->name }}_preview,
                                    previewFileType: 'audio',
                                    initialPreviewConfig: [{key: 1}],
                                    deleteUrl: '/admin/files/delete?_token={{csrf_token()}}',
                                    browseClass: 'btn btn-success',
                                    browseIcon: '<i class=\"glyphicon glyphicon-music\"></i>',
                                    removeClass: "btn btn-danger",
                                    removeIcon: '<i class=\"glyphicon glyphicon-trash\"></i>',
                                    uploadClass: "btn btn-info",
                                    uploadIcon: '<i class=\"glyphicon glyphicon-upload\"></i>'
                                }).on('fileuploaded', function (event, data) {
                                    $('#{{ $editor->name }}').val(data.response.data);
                                }).on('filedeleted', function (event, key) {
                                    $('#{{ $editor->name }}').val('');
                                });
                            </script>
                        @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_IMAGES)
                            <div class="form-group">
                                <label for="image_file" class="control-label col-sm-1">上传图集:</label>
                                <div class=" col-sm-11">
                                    <input id="{{ $editor->name . '_file' }}" name="{{ $editor->name . '_file' }}[]"
                                           type="file" class="file file-loading"
                                           data-upload-url="/admin/files/upload?type=image" multiple>
                                </div>
                            </div>
                            <script>
                                var {{ $editor->name }}_preview = [];
                                var {{ $editor->name }}_config = [];
                                @if(isset($content))
                                @foreach($content->images() as $image)
                                {{ $editor->name }}_preview.push('<img height="240" src="{{ $image->url }}" class="kv-preview-data file-preview-image">');
                                {{ $editor->name }}_config.push({
                                    key: '{{ $image->id }}',
                                    image_url: '{{ $image->url }}'
                                });
                                @endforeach
                                @endif
                                $('#{{ $editor->name . '_file' }}').fileinput({
                                    language: 'zh',
                                    uploadExtraData: {_token: '{{ csrf_token() }}'},
                                    allowedFileExtensions: ['jpg', 'gif', 'png', 'webp'],
                                    maxFileSize: 10240,
                                    resizeImage: true,
                                    maxImageWidth: 960,
                                    maxImageHeight: 640,
                                    initialPreview: {{ $editor->name }}_preview,
                                    initialPreviewConfig: {{ $editor->name }}_config,
                                    previewFileType: 'image',
                                    overwriteInitial: false,
                                    deleteUrl: '/admin/files/delete?_token={{csrf_token()}}',
                                    browseClass: 'btn btn-success',
                                    browseIcon: '<i class=\"glyphicon glyphicon-picture\"></i>',
                                    removeClass: "btn btn-danger",
                                    removeIcon: '<i class=\"glyphicon glyphicon-trash\"></i>',
                                    uploadClass: "btn btn-info",
                                    uploadIcon: '<i class=\"glyphicon glyphicon-upload\"></i>'
                                });

                                $(document).ready(function () {
                                    $('#submit').click(function () {
                                        var configs = $('#{{ $editor->name . '_file' }}').fileinput('getPreview').config;
                                        var urls = '';
                                        for (var i = 0; i < configs.length; i++) {
                                            if (i > 0) {
                                                urls += ',';
                                            }
                                            urls += configs[i].image_url;
                                        }
                                        $('#{{ $editor->name }}').val(urls);
                                    });
                                });
                            </script>
                        @elseif($editor->type == \App\Models\ModuleField::EDITOR_TYPE_VIDEOS)
                            <div class="form-group">
                                <label for="image_file" class="control-label col-sm-1">上传视频:</label>
                                <div class=" col-sm-11">
                                    <input id="{{ $editor->name . '_file' }}" name="{{ $editor->name . '_file' }}[]"
                                           type="file" class="file file-loading"
                                           data-upload-url="/admin/files/upload?type=video" multiple>
                                </div>
                            </div>
                            <script>
                                var {{ $editor->name }}_preview = [];
                                var {{ $editor->name }}_config = [];
                                @if(isset($content))
                                @foreach($content->videos() as $video)
                                {{ $editor->name }}_preview.push('<video height="300" controls="controls" src="{{ $video->url }}"></video>');
                                {{ $editor->name }}_config.push({
                                    key: '{{ $video->id }}',
                                    video_url: '{{ $video->url }}'
                                });
                                @endforeach
                                @endif
                                $('#{{ $editor->name . '_file' }}').fileinput({
                                    language: 'zh',
                                    uploadExtraData: {_token: '{{ csrf_token() }}'},
                                    allowedFileExtensions: ['mp4', 'mpg', 'mpeg', 'avi', 'wav', 'mp3'],
                                    maxFileSize: 1048576,
                                    initialPreview: {{ $editor->name }}_preview,
                                    initialPreviewConfig: {{ $editor->name }}_config,
                                    previewFileType: 'video',
                                    overwriteInitial: false,
                                    deleteUrl: '/admin/files/delete?_token={{csrf_token()}}',
                                    browseClass: 'btn btn-success',
                                    browseIcon: '<i class=\"glyphicon glyphicon-hd-video\"></i>',
                                    removeClass: "btn btn-danger",
                                    removeIcon: '<i class=\"glyphicon glyphicon-trash\"></i>',
                                    uploadClass: "btn btn-info",
                                    uploadIcon: '<i class=\"glyphicon glyphicon-upload\"></i>'
                                });

                                $(document).ready(function () {
                                    $('#submit').click(function () {
                                        var configs = $('#{{ $editor->name . '_file' }}').fileinput('getPreview').config;
                                        var urls = '';
                                        for (var i = 0; i < configs.length; i++) {
                                            if (i > 0) {
                                                urls += ',';
                                            }
                                            urls += configs[i].video_url;
                                        }
                                        $('#{{ $editor->name }}').val(urls);
                                    });
                                });
                            </script>
                        @endif
                    @endif
                    <?php $index++ ?>
                @endforeach
            </div>
        @endif
    @endforeach
</div>
<div class="box-footer">
    <button type="button" class="btn btn-default"
            onclick="location.href='{{ isset($back_url) ? $back_url : $base_url }}';"> 取　消
    </button>
    <button type="submit" class="btn btn-info pull-right" id="submit">保　存</button>
</div>

<script>
    $(document).ready(function () {
        $('.date').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            locale: "zh-CN",
            toolbarPlacement: 'bottom',
            showClear: true,
        });

        $('#submit').click(function () {
            var ret = true;
            $('.file').each(function () {
                var files = $(this).fileinput('getFileStack');

                if (files.length > 0) {
                    return ret = toast('info', '请先上传文件!');
                }
            });

            return ret;
        });
    });


    $('.select2').select2({
        tags: true,
        tokenSeparators: [',', ' ']
    });
</script>