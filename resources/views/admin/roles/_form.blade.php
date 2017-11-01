<div class="form-group">
    {!! Form::label('name', '名称:',['class' => 'control-label col-sm-1']) !!}
    <div class="col-sm-5">
        {!! Form::text('name', null, ['class' => 'form-control']) !!}
    </div>
    {!! Form::label('description', '备注:', ['class' => 'control-label col-sm-1']) !!}
    <div class="col-sm-5">
        {!! Form::text('description', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <label for="role" class="control-label col-sm-1">关联权限:</label>
    <div class="col-sm-11">
        @if(isset($perms))
            <div class="checkbox">
                @for($i=0; $i<$count; $i++)
                    <label>
                        <input type="checkbox" {{ in_array($permissions[$i]['id'], $perms) ? 'checked' : '' }} name="permission_id[]"
                               value="{{ $permissions[$i]['id'] }}">{{ $permissions[$i]['description'] }}
                    </label>
                    @if($i < $count - 1 && $permissions[$i]['group'] != $permissions[$i+1]['group'] )
                        <br />
                    @endif
                @endfor
            </div>
        @else
            <div class="checkbox">
                @for($i=0; $i<$count; $i++)
                    <label>
                        <input type="checkbox" name="permission_id[]" value="{{$permissions[$i]['id']}}">{{$permissions[$i]['description']}}
                    </label>
                    @if($i < $count - 1 && $permissions[$i]['group'] != $permissions[$i+1]['group'] )
                        <br />
                    @endif
                @endfor
            </div>
        @endif
    </div>

</div>

<div class="box-footer">
    <button type="button" class="btn btn-default" onclick="window.history.back();">取　消</button>
    <button type="submit" class="btn btn-info pull-right">确　定</button>
</div>

<script>
    $(function () {
        $('#begin_time').datetimepicker({
            format: 'YYYY/MM/DD HH:mm',
            locale: 'zh-cn'
        });
        $('#end_time').datetimepicker({
            format: 'YYYY/MM/DD HH:mm',
            locale: 'zh-cn'
        });
    });
</script>