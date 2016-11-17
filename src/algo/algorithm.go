/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-01-23 10:03
 * Filename      : algorithm.go
 * Description   : 胡牌,听牌,智能打牌等麻将相关的核心算法
 * *******************************************************/
package algo

import (
	"math/rand"
	"strconv"
	"time"
)

const (
	// 牌局基础的常量
	TOTAL uint32 = 108 //一副贵州麻将的总数
	BING  uint32 = 2   //同子类型
	TIAO  uint32 = 1   //条子类型
	WAN   uint32 = 0   //万字类型
	HAND  uint32 = 13  //手牌数量
	SEAT  uint32 = 4   //  最多可参与一桌打牌的玩家数量,不算旁观

	// 碰杠胡掩码,用32位每位代表不同的状态
	DRAW      uint32 = 0
	DISCARD   uint32 = 1
	PENG      uint32 = 2 << 0 // 碰
	MING_KONG uint32 = 2 << 1 // 明杠
	AN_KONG   uint32 = 2 << 2 // 暗杠
	BU_KONG   uint32 = 2 << 3 // 补杠
	KONG      uint32 = 2 << 4 // 杠(代表广义的杠)
	HU        uint32 = 2 << 6 // 胡(代表广义的胡)

	PING_HU                     uint32 = 2 << 8  // 平湖
	HU_BIG_PAIR                 uint32 = 2 << 9  // 大对子
	HU_SEVEN_PAIR               uint32 = 2 << 10 // 七小对
	HU_ALL_OF_ONE               uint32 = 2 << 11 // 清一色
	HU_ONE_SUIT_BIG_PAIR        uint32 = 2 << 12 // 清大对
	HU_ONE_SUIT_SEVEN_PAIR      uint32 = 2 << 13 // 清七对
	HU_LONG_SEVEN_PAIR          uint32 = 2 << 14 // 龙七对
	HU_ONE_SUIT_LONG_SEVEN_PAIR uint32 = 2 << 15 // 清龙对
	LONG                        uint32 = 2 << 16 // 龙

	SHA_BAO        uint32 = 2 << 17 // 杀报,你报听其他家胡你打的牌
	QIANG_GANG     uint32 = 2 << 18 // 抢杠, 其他家胡你补杠那张牌
	RE_PAO         uint32 = 2 << 19 // 热炮,热炮就是当有人放杠之后，杠牌者在摸牌尾后任意打一张牌出来，正好是别人要胡的牌，就是热炮
	SHAN_XIANG     uint32 = 2 << 20 // 一炮三响，输赢扭转，胡牌的3家分别按照自己胡牌的牌型给予放炮者番数
	ZIMO           uint32 = 2 << 21 //自摸
	PAOHU          uint32 = 2 << 22 // 炮胡
	HU_KONG_FLOWER uint32 = 2 << 24 // 杠上开花,杠完牌抓到的第一张牌自摸了

	TIAN_HU uint32 = 2 << 25 // 天胡
	DI_HU   uint32 = 2 << 26 // 地听胡
	TT_HU   uint32 = 2 << 27 // 天听胡

	DTING uint32 = 2 << 28 // 地听
	TTING uint32 = 2 << 29 // 天听

)

// 抢杠胡牌型
// var CARDS = []byte{0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x21, 0x22, 0x23, 0x25, 0x25, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x21, 0x22, 0x23, 0x24, 0x24, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x21, 0x22, 0x23, 0x24, 0x24, 0x28, 0x29, 0x01, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x11, 0x25, 0x13, 0x15, 0x14, 0x25, 0x16, 0x17, 0x18, 0x19, 0x29, 0x21, 0x22, 0x23, 0x26, 0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19, 0x27, 0x26, 0x27, 0x28, 0x29, 0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19, 0x28, 0x26, 0x27, 0x28, 0x29, 0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x01, 0x18, 0x19, 0x26, 0x27, 0x01, 0x12}

//
// 热炮牌型
//var CARDS = []byte{
//	0x01, 0x11, 0x11, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x11, 0x25,
//	0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x21, 0x22, 0x23, 0x24, 0x24,
//	0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x21, 0x22, 0x23, 0x24, 0x24,
//	0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x09, 0x09, 0x21, 0x22, 0x23, 0x25, 0x25,
//
//	0x16, 0x17, 0x18, 0x19, 0x29, 0x21, 0x22,
//	0x23, 0x26, 0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19, 0x27, 0x26,
//	0x27, 0x28, 0x29, 0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19, 0x28,
//	0x26, 0x27, 0x28, 0x29, 0x01, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19,
//	0x25, 0x26, 0x27, 0x25, 0x01, 0x15, 0x13, 0x14, 0x11, 0x12,
//}

// 正常牌型，高四位表示色值(0:万，1：条,2:饼)，低四位表示1-9的牌值
var CARDS = []byte{
	0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09,
	0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09,
	0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09,
	0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09,
	0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19,
	0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19,
	0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19,
	0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19,
	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
}

// var CARDS = []byte{
// 	0x04, 0x04, 0x06, 0x07, 0x08, 0x16, 0x17, 0x18, 0x24, 0x24, 0x24, 0x25, 0x28,
// 	0x04, 0x04, 0x06, 0x07, 0x08, 0x16, 0x17, 0x18, 0x24, 0x24, 0x24, 0x25, 0x28,
// 	0x04, 0x04, 0x06, 0x07, 0x08, 0x16, 0x17, 0x18, 0x24, 0x24, 0x24, 0x25, 0x28,
// 	0x04, 0x04, 0x06, 0x07, 0x08, 0x16, 0x17, 0x18, 0x24, 0x24, 0x24, 0x25, 0x28,
//
// 	0x25, 0x25, 0x25, 0x02, 0x09,
// 	0x03, 0x08, 0x03, 0x04, 0x07, 0x02, 0x07, 0x08, 0x09,
// 	0x07, 0x02, 0x03, 0x04, 0x03, 0x04, 0x07, 0x08, 0x09,
//
// 	0x15, 0x16, 0x17, 0x18, 0x19,
// 	0x15, 0x16, 0x17, 0x18, 0x19,
//
// 	0x25, 0x26, 0x27, 0x28, 0x29,
// 	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
// 	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
// }

// var CARDS = []byte{
// 	0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19,
// 	0x01, 0x12, 0x13, 0x06,
//
// 	0x11, 0x11, 0x11, 0x01, 0x05, 0x05, 0x05, 0x08, 0x09,
// 	0x06, 0x06, 0x06, 0x14,
//
// 	0x01, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19,
// 	0x01, 0x12, 0x13, 0x24,
//
// 	0x21, 0x22, 0x23, 0x14, 0x25, 0x26, 0x27, 0x28, 0x29,
// 	0x21, 0x22, 0x23, 0x24,
//
// 	0x04, 0x02, 0x05, 0x02, 0x09,
// 	0x03, 0x08, 0x03, 0x04, 0x07, 0x02, 0x07, 0x08, 0x09,
// 	0x07, 0x02, 0x03, 0x04, 0x03, 0x04, 0x07, 0x08, 0x09,
//
// 	0x15, 0x16, 0x17, 0x18, 0x19,
// 	0x15, 0x16, 0x17, 0x18, 0x19,
//
// 	0x25, 0x26, 0x27, 0x28, 0x29,
// 	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
// 	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
// }

// var CARDS = []byte{
// 	0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19,
// 	0x11, 0x12, 0x13, 0x14,
//
// 	0x01, 0x01, 0x01, 0x01, 0x05, 0x05, 0x05, 0x08, 0x09,
// 	0x06, 0x06, 0x06, 0x06,
//
// 	0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19,
// 	0x11, 0x12, 0x13, 0x14,
//
// 	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
// 	0x21, 0x22, 0x23, 0x24,
//
// 	0x07, 0x02, 0x05, 0x02, 0x09,
// 	0x03, 0x08, 0x03, 0x04, 0x07, 0x02, 0x07, 0x08, 0x09,
// 	0x04, 0x02, 0x03, 0x04, 0x03, 0x04, 0x07, 0x08, 0x09,
//
// 	0x15, 0x16, 0x17, 0x18, 0x19,
// 	0x15, 0x16, 0x17, 0x18, 0x19,
//
// 	0x25, 0x26, 0x27, 0x28, 0x29,
// 	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
// 	0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29,
// }

// // 天听拍型
// var CARDS = []byte{
// 	1, 1, 1, 1, 5, 6, 7, 8, 9,
// 	1, 2, 3, 4,
//
// 	17, 18, 19, 20, 21, 22, 23, 24, 25,
// 	17, 18, 19, 4,
//
// 	33, 34, 35, 36, 37, 38, 39, 40, 41,
// 	33, 34, 35, 2,
//
// 	17, 18, 19, 20, 21, 22, 23, 24, 25,
// 	17, 18, 19, 3,
// 	21, 22, 23, 24, 25, 20,
//
// 	4, 4, 3, 5, 6, 7, 8, 9,
// 	1, 4, 3, 4, 5, 6, 7, 8, 9,
// 	37, 38, 39, 40, 41,
//
// 	21, 22, 23, 24, 25,
//
// 	5, 6, 7, 8, 9, 20, 36,
//
// 	33, 34, 35, 36, 37, 38, 39, 40, 41,
// 	33, 34, 35, 36, 37, 38, 39, 40, 41,
// }
//
//
//
//
//// 天听拍型
//var CARDS = []byte{
//	1, 2, 3, 4, 5, 6, 7, 8, 9,
//	1, 2, 3, 4,
//
//	17, 18, 19, 20, 21, 22, 23, 24, 25,
//	17, 18, 19, 4,
//
//	33, 34, 35, 36, 37, 38, 39, 40, 41,
//	33, 34, 35, 1,
//
//	17, 18, 19, 20, 21, 22, 23, 24, 25,
//	17, 18, 19, 1,
//	21, 22, 23, 24, 25, 20,
//
//	1, 4, 3, 5, 6, 7, 8, 9,
//	1, 4, 3, 4, 5, 6, 7, 8, 9,
//	37, 38, 39, 40, 41,
//
//	21, 22, 23, 24, 25,
//
//	5, 6, 7, 8, 9, 20, 36,
//
//	33, 34, 35, 36, 37, 38, 39, 40, 41,
//	33, 34, 35, 36, 37, 38, 39, 40, 41,
//}
//
//
// var CARDS = []byte{
// 	1, 2, 3, 4, 5, 6, 7, 8, 9,
// 	1, 2, 3, 4,
//
// 	17, 18, 19, 20, 21, 22, 23, 24, 25,
// 	17, 18, 19, 20,
//
// 	33, 34, 35, 36, 37, 38, 39, 40, 41,
// 	33, 34, 35, 36,
//
// 	17, 18, 19, 20, 21, 22, 23, 24, 25,
// 	17, 18, 19, 25,
//
// 	41, 4, 20, 36, 25, 24,
//
// 	1, 4, 3, 5, 6, 7, 8, 9,
// 	1, 4, 3, 4, 5, 6, 7, 8, 9,
// 	37, 38, 39, 40, 41,
//
// 	21, 22, 23, 24, 1,
//
// 	5, 6, 7, 8, 9, 22, 23,
//
// 	33, 34, 35, 1, 37, 38, 39, 40, 41,
// 	33, 34, 35, 36, 37, 38, 39, 40, 21,
// }

//  找出杂牌，用于机器人和智能托管,原则上，听牌后不再碰牌，打牌不拆三张，顺子，连牌，坎。拆偏张和单张,杂牌中随机出牌
func SearchDirtyCard(cs []byte) byte {
	if len(cs) == 0 {
		return 0
	}
	var card byte
	le := len(cs)
	better := make([]byte, le)
	copy(better, cs)
	Sort(better, 0, len(cs)-1)

	arr := []string{}
	for i := 0; i < le; i++ {
		arr = append(arr, strconv.FormatInt(int64(better[i]), 10), " ")
	}
	for i := 0; i < le-2; i++ {
		for j := i + 1; j < le-1; j++ {
			for k := j + 1; k < le; k++ {
				if better[i] > 0 {
					if better[i] == better[j] && better[j] == better[k] {
						better[i] = 0x00
						better[j] = 0x00
						better[k] = 0x00
						break
					}
				}
			}
		}
	}

	for i := 0; i < le-2; i++ {
		for j := i + 1; j < le-1; j++ {
			for k := j + 1; k < le; k++ {
				if better[i] > 0 {
					if better[i]+1 == better[j] && better[j]+1 == better[k] {
						better[i] = 0x00
						better[j] = 0x00
						better[k] = 0x00
						break
					}
				}
			}
		}
	}

	for n := 0; n < len(better)-1; n++ {
		for m := n + 1; m < len(better); m++ {
			if better[n] > 0 && better[n] == better[m] {
				better[n] = 0x00
				better[m] = 0x00
				break
			}
		}
	}
	for n := 0; n < len(better)-1; n++ {
		for m := n + 1; m < len(better); m++ {
			if better[n] > 0 && better[n]+1 == better[m] {
				if better[n]&0x0f != 1 && better[m]&0x0f != 9 {
					better[n] = 0x00
					better[m] = 0x00
					break
				}
			}
		}
	}

	for n := 0; n < len(better)-1; n++ {
		for m := n + 1; m < len(better); m++ {
			if better[n] > 0 && better[n]+2 == better[m] {
				if better[n]&0x0f != 1 && better[m]&0x0f != 9 {
					better[n] = 0x00
					better[m] = 0x00
					break
				}
			}
		}
	}

	bet := make([]byte, 0)
	for i := 0; i < len(better); i++ {
		if better[i] > 0 {
			bet = append(bet, better[i])
			if better[i]&0x0f == 1 || better[i]&0x0f == 9 {
				card = better[i]
				break
			}
		}
	}

	if card == 0 {
		if len(bet) > 0 {
			r := rand.New(rand.NewSource(time.Now().UnixNano()))
			ran := int(r.Intn(len(bet)))
			card = bet[ran]

		} else {
			card = cs[0]
		}
	}
	return card
}

// 对牌值从小到大排序，采用快速排序算法
func Sort(arr []byte, start, end int) {
	if start < end {
		i, j := start, end
		key := arr[(start+end)/2]
		for i <= j {
			for arr[i] < key {
				i++
			}
			for arr[j] > key {
				j--
			}
			if i <= j {
				arr[i], arr[j] = arr[j], arr[i]
				i++
				j--
			}
		}

		if start < j {
			Sort(arr, start, j)
		}
		if end > i {
			Sort(arr, i, end)
		}
	}
}

// 判断是否有4个相同的牌
func existFour(cards []byte) bool {
	le := len(cards)
	for j := 0; j < le-3; j++ {
		count := 0
		for i := j + 1; i < le; i++ {
			if cards[j] == cards[i] {
				count = count + 1
				if count == 3 {
					return true
				}
			}
		}
	}
	return false
}

// 是否是同一色
func onecolor(cards []byte) bool {
	lenght := len(cards)
	if lenght == 0 {
		return false
	}
	color := cards[0] >> 4
	for i := 1; i < lenght; i++ {
		card := cards[i]
		if color != card>>4 {
			return false
		}
	}
	return true
}

// 判断是否为胡牌牌型,返回0表示不胡牌，大于0，用32位每表示不同的胡牌牌型
func ExistHu(cs []byte, isonecolor bool) uint32 {
	le := len(cs)
	var huType uint32 = 0
	if le == 2 {
		if cs[0] == cs[1] {
			huType = huType | HU
			huType = huType | HU_BIG_PAIR
			if isonecolor {
				huType = huType | HU_ALL_OF_ONE
			}
			return huType
		}
	}
	Sort(cs, 0, len(cs)-1)

	// 七小对牌型胡牌
	if le == 14 {
		hasHu := true
		for n := 0; n < le-1; n += 2 {
			if cs[n] != cs[n+1] {
				hasHu = false
				break
			}
		}
		if hasHu {
			huType = huType | HU
			huType = huType | HU_SEVEN_PAIR
			if isonecolor {
				huType = huType | HU_ALL_OF_ONE
			}
			long := existFour(cs)
			if long {
				huType = huType | LONG
			}
			if long && isonecolor {
				huType = huType | HU_ONE_SUIT_LONG_SEVEN_PAIR // 清龙背
			} else if long {
				huType = huType | HU_LONG_SEVEN_PAIR // 龙七对
			} else if isonecolor {
				huType = huType | HU_ONE_SUIT_SEVEN_PAIR // 清七对
			}
			return huType
		}
	}
	var existShun = false // 是否存在顺子
	if (huType & HU) != HU {
		// 3n +2 牌型胡牌
		for n := 0; n < le-1; n++ {
			if cs[n] == cs[n+1] {
				list := make([]byte, le)
				copy(list, cs)
				list[n] = 0x00
				list[n+1] = 0x00
				for i := 0; i < le-2; i++ {
					if list[i] > 0 {
						for j := i + 1; j < le-1; j++ {
							if list[j] > 0 {
								for k := j + 1; k < le; k++ {
									if list[k] > 0 {
										if list[i]+1 == list[j] && list[j]+1 == list[k] {
											if !existShun {
												existShun = true
											}
											list[i] = 0x00
											list[j] = 0x00
											list[k] = 0x00
											break
										} else if list[i] == list[j] && list[j] == list[k] {
											list[i] = 0x00
											list[j] = 0x00
											list[k] = 0x00
											break
										}
									}

								}
							}
						}
					}
				}
				num := false
				for i := 0; i < le; i++ {
					if list[i] > 0 {
						num = true
						break
					}
				}
				if !num {
					huType = huType | HU
					huType = huType | PING_HU
					if !existShun {
						huType = huType | HU_BIG_PAIR
						if isonecolor {
							huType = huType | HU_ONE_SUIT_BIG_PAIR
						}
					}
					if isonecolor {
						huType = huType | HU_ALL_OF_ONE
					}
				}
			}
		}
	}
	return huType

}

// 判断是否为听牌牌型,采用当前牌型叠加左右牌值，遍历尝试是否胡牌
func Ting(cs []byte, isonecolor bool) uint32 {
	tingTestCards := make([]byte, len(cs))
	copy(tingTestCards, cs)
	length := len(tingTestCards)

	for i := 0; i < length; i++ {
		card := tingTestCards[i]
		pre := card - 1
		next := card + 1
		has := false
		for _, v := range tingTestCards {
			if v == card || next&0x0F > 0x09 {
				has = true
				break
			}
		}
		if !has {
			tingTestCards = append(tingTestCards, next)
		}
		has = false
		for _, v := range tingTestCards {
			if v == pre || pre&0x0F < 0x01 {
				has = true
				break
			}
		}
		if !has {
			tingTestCards = append(tingTestCards, pre)
		}
	}
	for _, v := range tingTestCards {
		cards := make([]byte, len(cs)+1)
		copy(cards, cs)
		cards[len(cards)-1] = v
		value := ExistHu(cards, isonecolor)
		if value > 0 {
			return value
		}
	}
	return 0
}
