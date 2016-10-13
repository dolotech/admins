<?php
return array(
    'appid' => '123',
    'appkey' => 'key123',
    // 平台服务器ID -> 逻辑游戏服务器ID
    'psid2lgsid' => array(),
    // 逻辑游戏服务器ID -> 目标游戏服务器ID
    'lgsid2tgsid' => array(
        // 3 => 2,
        // 4 => 2,
    ),
    // 目标游戏服务器ID -> 包含的逻辑游戏服务器ID
    'tgsid2lgsids' => array(
        // 2 => array(2, 3, 4),
    ),
);
