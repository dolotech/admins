/**********************************************************
 * Author : Michael
 * Email : dolotech@163.com
 * Last modified : 2016-06-11 16:20
 * Filename : constan.go
 * Description :  麻将核心逻辑的一些常量
 * *******************************************************/
package constan

import (
	"algo"
)

const (
	WAITING_OUT_CARD_DELAY = 8 //  等待玩家出牌延时(单位秒)

	TRUSTEESHIP     = 1 //托管
	NON_TRUSTEESHIP = 0 //非托管
)

// 番值
const (
	// 鸡牌(1条)类型
	JI_TYPE_HERO   = 1 // 英雄鸡
	JI_TYPE_RELATE = 2 // 责任鸡
	JI_TYPE_NORMAL = 3 // 固定鸡
	// 番数
	MULTI_FIX_JI    = 1 // 固定鸡
	MULTI_HERO_JI   = 2 // 英雄鸡
	MULTI_RESPON_JI = 1 // 责任鸡
	MULTI_BAO_JI    = 1 // 包鸡

	MULTI_NORMAL_JI = 1 // 普通鸡
	MULTI_GOLD_JI   = 2 // 金鸡

	MULTI_AN_KONG   = 3 // 暗杠
	MULTI_MING_KONG = 3 // 明杠
	MULTI_BU_KONG   = 3 //补杠

	MULTI_PING                     = 1  // 平胡
	MULTI_TING                     = 20 // 天胡
	MULTI_TTING                    = 20 // 天听胡牌
	MULTI_DTING                    = 10 // 地听胡牌
	MULTI_ZIMO                     = 1  // 自摸
	MULTI_KONG_FLOWER              = 2  // 杠花(不算胡牌类型,是额外加1,算自摸,向所有收取)
	MULTI_BIG_PAIR                 = 5  // 大对子
	MULTI_SEVEN_PAIR               = 10 // 七小对
	MULTI_ALL_OF_ONE               = 10 // 清一色
	MULTI_TIAN_HU                  = 10 // 天胡
	MULTI_DI_HU                    = 10 // 地胡
	MULTI_ONE_SUIT_BIG_PAIR        = 15 // 清大对
	MULTI_ONE_SUIT_SEVEN_PAIR      = 20 // 清七对
	MULTI_LONG_SEVEN_PAIR          = 20 // 龙七对
	MULTI_ONE_SUIT_LONG_SEVEN_PAIR = 30 // 清龙对
	MULTI_RE_PAO                   = 1  // 热炮(不算胡牌类型,是额外加1番)
	MULTI_SHAN_XIANG               = 1  // 一炮三响，输赢扭转，胡牌的3家分别按照自己胡牌的牌型给予放炮者番数(不算胡牌类型,是额外加1番)
)

// 胡牌牌型转化为番值
func Type2Fan(huType uint32) int32 {
	var huTypeFan int32 = MULTI_PING                 // 基础平胡番值
	if huType&algo.HU_ONE_SUIT_LONG_SEVEN_PAIR > 0 { // 清龙对
		huTypeFan = MULTI_ONE_SUIT_LONG_SEVEN_PAIR
	} else if huType&algo.HU_LONG_SEVEN_PAIR > 0 { // 龙七对
		huTypeFan = MULTI_LONG_SEVEN_PAIR
	} else if huType&algo.HU_ONE_SUIT_SEVEN_PAIR > 0 { // 清七对
		huTypeFan = MULTI_ONE_SUIT_SEVEN_PAIR
	} else if huType&algo.HU_ONE_SUIT_BIG_PAIR > 0 { // 清大对
		huTypeFan = MULTI_ONE_SUIT_BIG_PAIR
	} else if huType&algo.HU_ALL_OF_ONE > 0 { // 清一色
		huTypeFan = MULTI_ALL_OF_ONE
	} else if huType&algo.HU_SEVEN_PAIR > 0 { // 七小对
		huTypeFan = MULTI_SEVEN_PAIR
	} else if huType&algo.HU_BIG_PAIR > 0 { // 大对子
		huTypeFan = MULTI_BIG_PAIR
	}
	return huTypeFan
}

// 天地听转番
func Ting2Fan(huType uint32) int32 {
	var huTypeFan int32
	if huType&algo.DI_HU > 0 { // 地听hu
		huTypeFan = MULTI_DTING
	} else if huType&algo.TT_HU > 0 { // 天听hu
		huTypeFan = MULTI_TTING
	} else if huType&algo.TIAN_HU > 0 { // 天hu
		huTypeFan = MULTI_TING
	}
	return huTypeFan
}

// 胡牌类型转化为番值
func Mode2Fan(huType uint32, zimo bool) int32 {
	var huTypeFan int32
	if zimo {
		huTypeFan = MULTI_ZIMO
		// if huType&algo.PING_HU > 0 {
		// 	huTypeFan += 1
		// }
	}
	if huType&algo.HU_KONG_FLOWER > 0 { // 杠上开花
		huTypeFan = MULTI_KONG_FLOWER
	} else if huType&algo.RE_PAO > 0 { // 热炮
		huTypeFan = MULTI_RE_PAO
	} else if huType&algo.SHAN_XIANG > 0 { // 一炮三响，输赢扭转，胡牌的3家分别按照自己胡牌的牌型给予放炮者番数
		huTypeFan = MULTI_SHAN_XIANG
	}
	return huTypeFan
}
