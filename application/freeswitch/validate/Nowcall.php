<?php
namespace app\freeswitch\validate;
use think\Validate;
class Nowcall extends Validate
{
    protected $rule = [
        'call_id'  =>  'require',
        'profile' =>  'require',
    ];

    protected $message = [
        'call_id.require'  =>  'call_id必须',
        'profile.require' =>  'profile必须',
    ];
}
?>