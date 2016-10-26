/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-01-23 10:06
 * Filename      : client.go
 * Description   : 玩家牌型数据
 * *******************************************************/
package algo

import (
	"errors"
	"inter"
	"sync/atomic"
)

func NewClient() *Client {
	return &Client{ting: -1}
}

type Client struct {
	cards         []byte        // 手牌
	outCards      []byte        // 海底牌
	pengCards     []inter.IPeng // 碰牌
	kongCards     []inter.IKong // 杠牌
	huData        inter.IHu     // 胡牌状态
	operateSeat   uint32
	operateValue  uint32 // 碰杠胡掩码
	operateCard   byte
	lotsCards     []uint32 // 胡牌后抓鸡	中鸡的牌 高16位为牌值，低两位为0时是普通鸡，为1时是金鸡
	turn          bool     // 摸起牌，或者碰牌后又没打牌
	skipKongCards []byte   // 跳过暗杠的牌
	coin          int32    //
	tingStatus    uint32   // 天地听状态
	discardCount  uint32   // 记录玩家出牌次数
	lastStatus    uint32   // 记录最后一次出牌状态,是否杠后打牌，用于热炮等逻辑 0:正常，1:为杠后抓牌打牌
	ting          int64
	konglink      []inter.IKong // 连杠列表
	trusteeship   uint32        // 1:托管，2：取消托管
}

//  重置数据
func (this *Client) Reset() {
	this.huData = nil
	this.operateValue = 0
	this.operateSeat = 0
	this.operateCard = byte(0)
	this.turn = false

	this.coin = 0
	this.tingStatus = 0
	this.ting = -1
	this.trusteeship = 0
	this.lastStatus = 0
	this.discardCount = 0

	this.cards = []byte{}
	this.outCards = []byte{}
	this.pengCards = []inter.IPeng{}
	this.kongCards = []inter.IKong{}
	this.konglink = []inter.IKong{}
	this.skipKongCards = []byte{}
	this.lotsCards = []uint32{}

}

// 托管
func (this *Client) SetTrusteeship(value uint32) {
	atomic.StoreUint32(&this.trusteeship, value)
}

func (this *Client) GetTrusteeship() uint32 {
	return atomic.LoadUint32(&this.trusteeship)
}
func (this *Client) GetLastKongStatus() []inter.IKong {
	return this.konglink
}

// 当到下一家出牌时，清除 记录的杠牌状态记录
func (this *Client) ClearLastKongStatus() {
	this.konglink = this.konglink[:0]
}
func (this *Client) GetLastStatue() uint32 {
	return this.lastStatus
}

func (this *Client) SetLastStatue(value uint32) {
	this.lastStatus = value
}
func (this *Client) GetDiscardCount() uint32 {
	return this.discardCount
}
func (this *Client) GetTingStatus() uint32 {
	return this.tingStatus
}

func (this *Client) SetTingStatus(value uint32) {
	this.tingStatus = value
}

func (this *Client) SkipKong(card byte) {
	this.skipKongCards = append(this.skipKongCards, card)
}
func (this *Client) Turn() bool {
	return this.turn
}
func (this *Client) SetLotsCards(cards []uint32) {
	this.lotsCards = cards
}
func (this *Client) GetLotsCards() []uint32 {
	return this.lotsCards
}
func (this *Client) SetCoin(value int32) {
	this.coin = value
}

func (this *Client) GetCoin() int32 {
	return this.coin
}

func (this *Client) SetHuData(data inter.IHu) {
	this.huData = data
}

func (this *Client) GetHuData() inter.IHu {
	return this.huData
}
func (this *Client) QiangKongDetect(card byte) uint32 {
	status := this.existHu(card)
	if status > 0 {
		status = status | QIANG_GANG
	}
	return status
}

// 打牌检测,胡牌
func (this *Client) DiscardDetectHu(card byte) uint32 {
	status := this.existHu(card)
	if status > 0 {
		status = status | PAOHU
	}
	return status
}

// 打牌检测,明杠／碰牌
func (this *Client) DiscardDetectKongPeng(card byte) uint32 {
	var status uint32
	if this.existMingKong(card) {
		status = status | MING_KONG
		status = status | KONG
	}
	if this.existPeng(card) {
		status = status | PENG
	}
	return status
}

// 摸牌检测,胡牌／暗杠／补杠
func (this *Client) DrawDetect(card byte) uint32 {
	status := this.existZimo()
	if status > 0 {
		status = status | ZIMO
	}
	if len(this.ExistAnKong()) > 0 {
		status = status | AN_KONG
		status = status | KONG
	} else if this.existBuKong(card) {
		status = status | BU_KONG
		status = status | KONG
	}
	return status
}
func (this *Client) Peng(position uint32, card byte) {

	le := len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}

	le = len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}

	p := &Peng{position: position, card: card}
	this.pengCards = append(this.pengCards, p)
	this.turn = true
}
func (this *Client) existPeng(card byte) bool {
	le := len(this.cards)
	count := 0
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			count = count + 1
			if count == 2 {
				return true
			}
		}
	}
	return false
}

func (this *Client) HasAnKong(card byte) bool {
	var i int = 0
	for _, v := range this.cards {
		if v == card {
			i++
		}
		if i == 4 {
			return true
		}
	}
	return false
}

func (this *Client) GetAnKong() byte {
	var i map[byte]int
	i = make(map[byte]int)
	for _, v := range this.cards {
		i[v] += 1
		if i[v] == 4 {
			return v
		}
	}
	return 0
}

func (this *Client) AnKong(card byte) {
	le := len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}
	le = len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}
	le = len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}
	le = len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}
	k := &Kong{classify: AN_KONG, card: card}
	this.kongCards = append(this.kongCards, k)
	this.konglink = append(this.konglink, k)
}

func (this *Client) RemoveLastOutCard(card byte) {
	if len(this.outCards) > 0 {
		if this.outCards[len(this.outCards)-1] == card {
			this.outCards = this.outCards[:len(this.outCards)-1]
		}
	}
}

// 玩家碰牌，移除被碰玩家桌面上对应被碰的牌
func (this *Client) RemoveOutCard(card byte) {
	le := len(this.outCards)
	if le == 0 {
		return
	}
	for i := le - 1; i >= 0; i-- {
		if card == this.outCards[i] {
			this.outCards = append(this.outCards[:i], this.outCards[i+1:]...)
			break
		}
	}
}
func (this *Client) MingKong(position uint32, card byte) {
	le := len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}
	le = len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}
	le = len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}

	k := &Kong{position: position, classify: MING_KONG, card: card}
	this.kongCards = append(this.kongCards, k)
	this.konglink = append(this.konglink, k)
}

func (this *Client) BuKong(card byte) {
	le := len(this.pengCards)
	for i := 0; i < le; i++ {
		if card == this.pengCards[i].GetCard() {
			this.pengCards = append(this.pengCards[:i], this.pengCards[i+1:]...)
			break
		}
	}

	le = len(this.cards)
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			break
		}
	}
	k := &Kong{classify: BU_KONG, card: card}
	this.kongCards = append(this.kongCards, k)
	this.konglink = append(this.konglink, k)
}

// 用于上手有杠
func (this *Client) ExistAnKong() (kong []byte) {
	le := len(this.cards)
	for j := 0; j < le-3; j++ {
		if this.existSkipKong(this.cards[j]) {
			break
		}
		count := 0
		for i := j + 1; i < le; i++ {
			if this.cards[j] == this.cards[i] {
				count = count + 1
				if count == 3 {
					kong = append(kong, this.cards[i])
					break
				}
			}
		}
	}
	return
}

func (this *Client) existSkipKong(card byte) bool {
	for _, kongcard := range this.skipKongCards {
		if card == kongcard {
			return true
		}
	}
	return false
}

func (this *Client) existBuKong(card byte) bool {
	le := len(this.pengCards)
	for i := 0; i < le; i++ {
		if card == this.pengCards[i].GetCard() {
			return true
		}
	}
	return false
}

func (this *Client) existMingKong(card byte) bool {
	le := len(this.cards)
	count := 0
	for i := 0; i < le; i++ {
		if card == this.cards[i] {
			count = count + 1
			if count == 3 {
				return true
			}
		}
	}
	return false

}

// 放炮胡检测
func (this *Client) existHu(card byte) uint32 {
	list := make([]byte, len(this.cards), len(this.cards))
	copy(list, this.cards)
	for i := 0; i < len(this.kongCards); i++ {
		card := this.kongCards[i].GetCard()
		list = append(list, card)
	}
	for i := 0; i < len(this.pengCards); i++ {
		card := this.pengCards[i].GetCard()
		list = append(list, card)
	}
	isonecolor := onecolor(list)
	cards := make([]byte, len(this.cards)+1)
	copy(cards, this.cards)
	cards[len(cards)-1] = card

	value := ExistHu(cards, isonecolor)
	if value > 0 {
		if this.tingStatus&TTING > 0 {
			value = value | TT_HU
		} else if this.tingStatus&DTING > 0 {
			value = value | DI_HU
		}
	}

	return value
}

// 自摸胡检测
func (this *Client) existZimo() uint32 {
	list := make([]byte, len(this.cards))
	copy(list, this.cards)
	for i := 0; i < len(this.kongCards); i++ {
		card := this.kongCards[i].GetCard()
		list = append(list, card)
	}
	for i := 0; i < len(this.pengCards); i++ {
		card := this.pengCards[i].GetCard()
		list = append(list, card)
	}
	isonecolor := onecolor(list)
	cards := make([]byte, len(this.cards))
	copy(cards, this.cards)

	value := ExistHu(this.cards, isonecolor)
	if value > 0 {
		if this.tingStatus&TTING > 0 {
			value = value | TT_HU
		} else if this.tingStatus&DTING > 0 {
			value = value | DI_HU
		}
	}
	return value
}

// 玩家出牌
func (this *Client) Out(card byte) error {
	for i := 0; i < len(this.cards); i++ {
		if card == this.cards[i] {
			this.cards = append(this.cards[:i], this.cards[i+1:]...)
			this.outCards = append(this.outCards, card)
			this.turn = false
			this.discardCount++
			return nil
		}
	}
	return errors.New("card is not exist")
}
func (this *Client) GetLast() byte {
	if len(this.cards) > 0 {
		card := this.cards[len(this.cards)-1]
		return card
	}
	return byte(0)
}
func (this *Client) GetCards() []byte {
	cards := make([]byte, len(this.cards), len(this.cards))
	copy(cards, this.cards)
	return cards
}
func (this *Client) Exist(card byte) bool {
	if card == 0 {
		return false
	}
	if len(this.cards) == 0 {
		return false
	}
	for i := 0; i < len(this.cards); i++ {
		if this.cards[i] == card {
			return true
		}
	}
	return false
}

// 验证地听
func (this *Client) VerifyDiTing(card byte) uint32 {

	var value uint32
	for i := 0; i < len(this.cards); i++ {
		if card == this.cards[i] {
			cards := append([]byte{}, this.cards[:i]...)
			cards = append(cards, this.cards[i+1:]...)
			value = Ting(cards, false)
			return value
		}
	}
	return value
}

// 地听
func (this *Client) DiTing() uint32 {
	var value uint32 = 0
	for i := 0; i < len(this.cards); i++ {
		cards := append([]byte{}, this.cards[:i]...)
		cards = append(cards, this.cards[i+1:]...)
		value = Ting(cards, false)
		if value > 0 {
			value = DTING
		}
		return value
	}
	return value
}

// 用于结算玩家是否听牌 ,返回值大于0表示听牌，具体的掩码为对应值为胡牌牌型（非结算cache==false）

func (this *Client) TianTing() uint32 {
	return Ting(this.cards, false)
}
func (this *Client) GetTing() uint32 {
	if this.ting != -1 {
		return uint32(this.ting)
	}
	list := make([]byte, len(this.cards), len(this.cards))
	copy(list, this.cards)
	for i := 0; i < len(this.kongCards); i++ {
		card := this.kongCards[i].GetCard()
		list = append(list, card)
	}
	for i := 0; i < len(this.pengCards); i++ {
		card := this.pengCards[i].GetCard()
		list = append(list, card)
	}

	isonecolor := onecolor(list)
	t := Ting(this.cards, isonecolor)
	this.ting = int64(t)
	return t
}
func (this *Client) In(card byte) {
	this.cards = append(this.cards, card)
	this.turn = true
}
func (this *Client) SetCards(cards []byte) {
	this.cards = cards
}

func (this *Client) GetKongCards() []inter.IKong {
	return this.kongCards
}
func (this *Client) GetPengCards() []inter.IPeng {
	return this.pengCards
}
func (this *Client) GetOutCards() []byte {
	return this.outCards
}

func (this *Client) ClearOperate() {
	this.operateValue = 0
	this.operateSeat = 0
	this.operateCard = byte(0)
}
func (this *Client) GetOperate() uint32 {
	return this.operateValue
}
func (this *Client) ExistOperateValue(value uint32) bool {
	if (this.operateValue & value) > 0 {
		return true
	} else {
		return false
	}
}

func (this *Client) GetOperateCard() byte {
	return this.operateCard
}
func (this *Client) GetOperateSeat() uint32 {
	return this.operateSeat
}
func (this *Client) SetOperate(seat uint32, card byte, value uint32) {
	this.operateValue = value
	this.operateSeat = seat
	this.operateCard = card
}

func (this *Client) ExistKong() bool {
	return len(this.kongCards) > 0
}
