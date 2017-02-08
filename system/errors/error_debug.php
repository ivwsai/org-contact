<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>系统发生错误(<?php echo strip_tags($e['errcode']); ?>)</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <style>
        body {
            font-family: 'Microsoft Yahei', Verdana, arial, sans-serif;
            font-size: 14px;
        }

        a {
            text-decoration: none;
            color: #174B73;
        }

        a:hover {
            text-decoration: none;
            color: #FF6600;
        }

        h2 {
            border-bottom: 1px solid #DDD;
            padding: 8px 0;
            font-size: 25px;
        }

        .title {
            margin: 4px 0;
            color: #F60;
            font-weight: bold;
        }

        .message, #trace {
            padding: 1em;
            border: solid 1px #000;
            margin: 10px 0;
            background: #FFD;
            line-height: 150%;
        }

        .message {
            background: #FFD;
            color: #2E2E2E;
            border: 1px solid #E0E0E0;
        }

        #trace {
            background: #E7F7FF;
            border: 1px solid #E0E0E0;
            color: #535353;
        }

        .notice {
            padding: 10px;
            margin: 5px;
            color: #666;
            background: #FCFCFC;
            border: 1px solid #E0E0E0;
        }

        .red {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="notice">
    <h2>系统发生错误(<?php echo strip_tags($e['errcode']); ?>) </h2>
    <?php if (isset($e['file'])) { ?>
        <p><strong>错误位置:</strong>　FILE: <span class="red"><?php echo $e['file']; ?></span>　LINE: <span
                class="red"><?php echo $e['line']; ?></span></p>
    <?php } ?>
    <p class="title">[ 错误信息 ]</p>

    <p class="message"><?php echo strip_tags($e['errmsg']); ?></p>
    <?php if (isset($e['trace'])) { ?>
        <p class="title">[ 堆栈信息 ]</p>
        <p id="trace">
            <?php echo nl2br($e['trace']); ?>
        </p>
    <?php } ?>
</div>
<div align="center" style="color:#FF3300;margin:5pt;font-family:Verdana">Netap<sup
        style='color:gray;font-size:9pt'><?php echo VERSION; ?></sup><span style="color:gray;font-size:9px;">&nbsp;&nbsp;Copyright 1999-2014 © 91.com All rights reserved.</span>
</div>
</body>
</html>