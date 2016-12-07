<?php

return [
    'template'  =>  [
        'layout_on'     =>  true,
        'layout_name'   =>  'layout',
    ],
    // 视图输出字符串内容替换
    'view_replace_str' => [
        '__ROOT__' => request()->root(),
        '__PUBLIC__' => request()->root().DS.'public',
    ],
];
