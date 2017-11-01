<?php

return [
    '定义片段' => "@yield('title')\n",
    '注入片段(1)' => "@section('title', 'Page Title')\n",
    '注入片段(2)' => "@section('sidebar')\n    <p>This is appended to the master sidebar.</p>\n@endsection\n",
    '继承模板' => "@extends('layouts.master')\n",
    '包含子视图' => "@include('layouts.header')\n",
    '显示数据' => "{{ \$name }}",
    '显示原生数据' => "{!! \$name !!}",
    '判断语句' => "@if (count(\$records) === 1)\n    I have one record!\n@elseif (count(\$records) > 1)\n    I have multiple records!\n@else\n    I don't have any records!\n@endif\n",
    '循环语句(1)' => "@foreach (\$users as \$user)\n    <p>This is user {{ \$user->id }}</p>\n@endforeach\n",
    '循环语句(2)' => "@for (\$i = 0; \$i < 10; \$i++)\n    <p>The current value is {{ \$i }}</p>\n@endfor\n",
];