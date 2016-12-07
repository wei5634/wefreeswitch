wefreeswitch
===============

刚好在学习thinkphp5，以前的freeswitch项目拿来练手了
php连接freeswitch event_socket
freeswitch ui,主要用于一台或多台freeswitch各状态查看，没有涉及到xml配置文件的修改
只在windows上测试过，linux还没测试过

 + 查看当前通话
 + 查看用户目录
 + 查看注册用户
 + 查看Sofia SIP状态
 + 查看系统各种状态
 + 运行api命令行
 + cdr历史话单(未)

## 环境

php5.4+
mysql 5+

## 使用轮子
后端:thinkphp5
前端:bootstrap3.3.7,jquery

### 安装
---------------------
安装freeswitch
修改conf/autoload_configs/event_socket.conf.xml
listen-ip
listen-port
password
此次本地测试，所以不用修改
打开freeswitch
---------------------

本程序文件，添加以下信息到application/extra/freeswitch_esl.php
-------------------------------------------------------
<?php
return array (
  0 => 
  array (
    'server_name' => 'LocalIP',
    'esl_host' => '127.0.0.1',
    'esl_port' => '8021',
    'esl_password' => 'ClueCon',
    'sid' => '1',
  ),
  1 => 
  array (
    'server_name' => 'FS IP44',
    'esl_host' => '192.168.10.64',
    'esl_port' => '8021',
    'esl_password' => 'ClueCon',
    'sid' => '2',
  ),
); 
-------------------------------------------------------
可以浏览网站了

### CDR历史记录

freeswitch修改
conf/autoload_configs/xml_cdr.conf.xml
修改url为http://你的域名/index.php/freeswitch/cdrxml/index/pass/123456/cdr_table/fs_xml_cdr

123456为cdr认证密码，你可以在程序的配置内application/extra/freeswitch.php修改xml_cdr_password,密码修改后上面url地址你也应该同时变下。

fs_xml_cdr为cdr存储表，请自行建立mysql表

### 杂项
composer都是thinkphp的，可以忽略

## author

Wayne Wu
http://www.wuweixian.com
email:wei5634@126.com
qq:99124363