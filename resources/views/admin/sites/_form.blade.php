<ul id="tabs" class="nav nav-tabs">
    <li class="active">
        <a href="#tabHome" data-toggle="tab">基本信息</a>
    </li>
    <li>
        <a href="#tabAndroid" data-toggle="tab">第三方</a>
    </li>
</ul>
<div id="tabContents" class="tab-content">
    <div id="tabHome" class="tab-pane fade in active padding-t-15">
        <div class="form-group">
            {!! Form::label('name', '英文名称:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('name', null, ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('title', '标题:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('title', null, ['class' => 'form-control']) !!}
            </div>
        </div>


        <div class="form-group">

            {!! Form::label('domain', '站点域名:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('domain', null, ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('directory', '发布目录:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('directory', null, ['class' => 'form-control']) !!}
            </div>
        </div>


        <div class="form-group">
            {!! Form::label('default_theme_id', '默认主题:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::select('default_theme_id', $themes, !isset($site) ? '1':$site->default_theme_id ,['class' => 'form-control col-sm-2']) !!}
            </div>
            {!! Form::label('mobile_theme_id', '移动主题:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::select('mobile_theme_id', $themes, !isset($site) ? '1':$site->mobile_theme_id ,['class' => 'form-control col-sm-2']) !!}
            </div>
        </div>

    </div>

    <div id="tabAndroid" class="tab-pane fade padding-t-15">
        <div class="form-group">
            {!! Form::label('jpush_app_key', '极光AppKey:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('jpush_app_key', null, ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('jpush_app_secret', '极光AppSecret:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('jpush_app_secret', null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group">
            {!! Form::label('wechat_app_id', '微信AppID:',['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('wechat_app_id', null, ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('wechat_secret', '微信Secret:', ['class' => 'control-label col-sm-1']) !!}
            <div class="col-sm-5">
                {!! Form::text('wechat_secret', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

</div>

<div class="box-footer">
    <a href="/admin/sites">
        <button type="button" class="btn btn-default">取　消</button>
    </a>
    <button type="submit" class="btn btn-info pull-right" id="submit">确　定</button>
</div>