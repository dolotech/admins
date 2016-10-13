<?php

// 注意：数据索引定义后不能进行修改，否则会引起权限混乱
// 'hidden' => true

return array(
    30 => array(
        'title' => '角色管理',
        'sub'	=> array(
            10 => array('sidebar' => true, 'title' => '角色查询', 'url' => '?mod=role&act=list'),
            20 => array('sidebar' => true, 'title' => '在线明细', 'url' => '?mod=role&act=online'),
            30 => array('sidebar' => true, 'title' => '聊天查询', 'url' => '?mod=role&act=chat2'),
        ),
    ),
    20 => array(
        'title' => '游戏日志',
        'sub'	=> array(
            //在下面数组中加入'sidebar' => true, 则会在左边菜单显示
            10 => array('title' => '神魔探险日志', 'url' => '?mod=log&act=kxzzz'),
            20 => array('title' => '玩家创建日志', 'url' => '?mod=log&act=create_role'),
            30 => array('title' => '开服活动排名', 'url' => '?mod=log&act=sevenday_rank'),
            40 => array('title' => '升阶奖励领取日志', 'url' => '?mod=log&act=sevenday'),
            50 => array('title' => '发货记录', 'url' => '?mod=log&act=recharge'),
            60 => array('title' => '充值返利活动日志', 'url' => '?mod=log&act=recharge_activity1'),
            90 => array('title' => '帮派查询', 'url' => '?mod=log&act=guild'),
            100 => array('title' => '物品记录明细', 'url' => '?mod=log&act=item'),
            110 => array('sidebar' => false, 'title' => '充值订单查询', 'url' => '?mod=log&act=charge_order'),
        	120 => array('sidebar' => false, 'title' => '奖励中心日志', 'url' => '?mod=log&act=reward_center'),
            130 => array('sidebar' => false, 'title' => '热血屠龙日志', 'url' => '?mod=log&act=killdragon'),
        	140 => array('sidebar' => false, 'title' => '副本奖励获得日志', 'url' => '?mod=log&act=shenmo_tower2'),
        	150 => array('sidebar' => false, 'title' => '竞技场奖励排名', 'url' => '?mod=log&act=arena_prize'),
        	160 => array('sidebar' => false, 'title' => '竞技场荣誉记录', 'url' => '?mod=log&act=jjc_shengwang'),
        	170 => array('sidebar' => false, 'title' => '元宝日志', 'url' => '?mod=log&act=yb'),
        	180 => array('sidebar' => false, 'title' => '投资计划领奖日志', 'url' => '?mod=log&act=invest_plan_reward'),
        	190 => array('sidebar' => false, 'title' => '合服活动奖励排名', 'url' => '?mod=log&act=combine_recharge_rank'),
        ),
        //这里添加菜单栏，数组的key写了以后不能修改，建议使用10,20,30...为了方便以后在中间插入菜单   
    ),
    25 => array(
        'title' => '统计报表',
        'sub'	=> array(
            10 => array('sidebar' => false, 'title' => '商城消费统计', 'url' => '?mod=reports&act=game_shop'),
            11 => array('sidebar' => false, 'title' => '物品消耗统计', 'url' => '?mod=reports&act=item_used'),
            12 => array('sidebar' => false, 'title' => '银两消耗统计', 'url' => '?mod=reports&act=coin_used'),
            13 => array('sidebar' => false, 'title' => '银两产出统计', 'url' => '?mod=reports&act=coin_obtain'),
        	20 => array('sidebar' => false, 'title' => '充值订单报表', 'url' => '?mod=reports&act=charge'),
        	// 30 => array('sidebar' => false, 'title' => '充值统计报表', 'url' => '?mod=reports&act=charge_stat'),
        	40 => array('sidebar' => true, 'title' => '运营数据统计', 'url' => '?mod=reports&act=game_stat'),
        	41 => array('sidebar' => true, 'title' => '留存统计', 'url' => '?mod=reports&act=game_stat_retention'),
        	42 => array('sidebar' => true, 'title' => '流失统计', 'url' => '?mod=reports&act=game_stat_lost_level'),
        	43 => array('sidebar' => false, 'title' => '实时等级分布', 'url' => '?mod=reports&act=game_stat_level'),
        	50 => array('sidebar' => false, 'title' => '在线统计', 'url' => '?mod=reports&act=online'),
        	60 => array('sidebar' => false, 'title' => '神魔探险统计', 'url' => '?mod=reports&act=smtx'),
        	70 => array('sidebar' => false, 'title' => '今日实时概况', 'url' => '?mod=reports&act=today_summary'),
        	80 => array('sidebar' => false, 'title' => '今日充值概况', 'url' => '?mod=reports&act=today_charge_summary'),
        	90 => array('sidebar' => false, 'title' => '玩家元宝消耗统计', 'url' => '?mod=reports&act=yb_stat'),
        	100 => array('sidebar' => false, 'title' => '今日在线时长统计', 'url' => '?mod=reports&act=ol_stat'),
        	110 => array('sidebar' => false, 'title' => 'VIP等级统计', 'url' => '?mod=reports&act=vip_stat'),
        	120 => array('sidebar' => false, 'title' => '元宝充值统计', 'url' => '?mod=reports&act=charge_stat'),
        ),
    ),
    28 => array(
        'title' => '游戏设置',
        'sub'	=> array(
            10 => array('sidebar' => false, 'title' => '游戏公告', 'url' => '?mod=setting&act=announcement'),
            20 => array('sidebar' => false, 'title' => '投资计划信息', 'url' => '?mod=setting&act=invest_plan'),
            30 => array('sidebar' => false, 'title' => '礼包管理', 'url' => '?mod=setting&act=gift'),
            40 => array('sidebar' => false, 'title' => '批量发送道具', 'url' => '?mod=setting&act=send_item'),
        	41 => array('sidebar' => false, 'title' => '指定玩家发送道具', 'url' => '?mod=setting&act=send_item2'),
        	50 => array('sidebar' => false, 'title' => '生成数据', 'url' => '?mod=setting&act=gen_code'),
        	60 => array('sidebar' => false, 'title' => '新手指导员', 'url' => '?mod=setting&act=player_admin'),
        	70 => array('sidebar' => false, 'title' => '欢乐淘活动时间', 'url' => '?mod=setting&act=happy_draw'),
        	80 => array('sidebar' => false, 'title' => '执行SQL', 'url' => '?mod=setting&act=sql_exe'),		
        ),
    ),
    29 => array(
        'title' => '运维助手',
        'sub'	=> array(
            10 => array('sidebar' => false, 'title' => '开服申请', 'url' => '?mod=operation&act=app_s'),
        ),
    ),
    32 => array(
        'title' => 'GM操作', // 对指定的角色进行GM操作，请求时必须转入角色ID
        'sub'	=> array(
            11 => array(
                'target' => 'dialog', 
                'title' => '封号' , 
                'hidden' => true, 
                'url' => '?mod=role&act=send_gmact&cmd=1',
                'data' => '{"args":["id","serverid","platformid"]}',
            ),
            12 => array(
                'target' => 'dialog',
                'title' => '解封' ,
                'hidden' => true,
                'url' => '?mod=role&act=send_gmact&cmd=2',
                'data' => '{"args":["id","serverid","platformid"]}',
            ),
            13 => array(
                'target' => 'dialog', 
                'title' => '封IP' , 
                'hidden' => true, 
                'url' => '?mod=role&act=send_gmact&cmd=3',
                'data' => '{"args":["id","serverid","platformid"]}',
            ),
            14 => array(
                'target' => 'dialog',
                'title' => '解IP' ,
                'hidden' => true,
                'url' => '?mod=role&act=send_gmact&cmd=4',
                'data' => '{"args":["id","serverid","platformid"]}',
            ),
            15 => array(
                'target' => 'dialog', 
                'title' => '禁言' , 
                'hidden' => true, 
                'url' => '?mod=role&act=send_gmact&cmd=5',
                'data' => '{"args":["id","serverid","platformid"]}',
            ),
            16 => array(
                'target' => 'dialog',
                'title' => '解禁' ,
                'hidden' => true,
                'url' => '?mod=role&act=send_gmact&cmd=6',
                'data' => '{"args":["id","serverid","platformid"]}',
            ),
            19 => array(
                'target' => 'new_page',
                'title' => '登陆' ,
                'hidden' => true,
                'url' => '?mod=role&act=login',
                'data' => '{"args":["account","serverid","platformid"]}',
            ),
            20 => array(
                'target' => 'dialog',
                'title' => '-' ,
                'hidden' => true,
                'url' => '',
                'data' => '',
            ),
            21 => array(
                'target' => 'page',
                'title' => '在线明细' ,
                'hidden' => true,
                'url' => '?mod=role&act=online',
                'data' => '{"args":["id","serverid"]}',
            ),
            22 => array(
                'target' => 'page',    //target的值可以是dialog表示弹出一个对话框里显示，new_page表示新建一个页面显示，page表示当前页面显示
                'title' => '升级日志' ,
                'hidden' => true,
                'url' => '?mod=log&act=level',   //添加右边的菜单在这里加
                'data' => '{"args":["id","serverid"]}',
            ),
            23 => array(
                'target' => 'page',    //target的值可以是dialog表示弹出一个对话框里显示，new_page表示新建一个页面显示，page表示当前页面显示
                'title' => '坐骑升级日志' ,
                'hidden' => true,
                'url' => '?mod=log&act=mount',   //添加右边的菜单在这里加
                'data' => '{"args":["id","serverid"]}',
            ),
        	24 => array(
        		'target' => 'page',    //target的值可以是dialog表示弹出一个对话框里显示，new_page表示新建一个页面显示，page表示当前页面显示
        		'title' => '添加新手指导' ,
        		'hidden' => true,
        		'url' => '?mod=setting&act=player_admin',   //添加右边的菜单在这里加
        		'data' => '{"args":["id"]}',
        		),
        	25 => array(
        		'target' => 'page',    //target的值可以是dialog表示弹出一个对话框里显示，new_page表示新建一个页面显示，page表示当前页面显示
        		'title' => '充值明细' ,
        		'hidden' => true,
        		'url' => '?mod=log&act=charge_order',   //添加右边的菜单在这里加
        		'data' => '{"args":["account"]}',
        		),
            30 => array(
                'target' => 'dialog',
                'title' => '-' ,
                'hidden' => true,
                'url' => '',
                'data' => '',
            ),
            31 => array(
                'target' => 'dialog',
                'title' => '发元宝' ,
                'hidden' => true,
                'url' => '?mod=role&act=send_yb',
                'data' => '{"args":["id","serverid","platformid"]}',
            ),
            32 => array(
                'target' => 'dialog',
                'title' => '发物品' ,
                'hidden' => true,
                'url' => '?mod=role&act=send_item',
                'data' => '{"args":["id","serverid","platformid"]}',
            ),
            34 => array(
                'target' => 'page',
                'title' => '发道具(多个)' ,
                'hidden' => true,
                'url' => '?mod=setting&act=send_item2',
                'data' => '{"args":["id"]}',
            ),
            33 => array(
                'target' => 'dialog',
                'title' => '扣钱' ,
                'hidden' => true,
                'url' => '?mod=role&act=send_delcurrency',
                'data' => '{"args":["id","serverid","platformid"]}',
            ),
            40 => array(
                'target' => 'dialog',
                'title' => '-' ,
                'hidden' => true,
                'url' => '',
                'data' => '',
            ),
            41 => array(
                'target' => 'dialog',
                'title' => '设为内部号' ,
                'hidden' => true,
                'url' => '?mod=role&act=set_local_player&do=add',
                'data' => '{"args":["id","account","serverid","platformid"]}',
            ),
            42 => array(
                'target' => 'dialog',
                'title' => '取消内部号' ,
                'hidden' => true,
                'url' => '?mod=role&act=set_local_player&do=del',
                'data' => '{"args":["id","account","serverid","platformid"]}',
            ),
        ),
    ),
    90 => array(
        'title' => '系统设置',
        'sub' => array(
            10 => array('title' => '用户组管理', 'url' => '?mod=users&act=group_list'),
            20 => array('title' => '后台用户帐号管理', 'url' => '?mod=users&act=list'),
            30 => array('title' => '后台操作日志', 'url' => '?mod=log&act=admin'),
            40 => array('title' => '修改我的密码', 'url' => '?mod=users&act=set_password'),
        ),
    ),
);
