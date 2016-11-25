package data

const (
	USERS_INDEX string = "admins_users_index"
	USERS       string = "admins_users:"

	//USER_GROUP_INDEX string = "admins_user_group_index"
	USER_GROUP string = "admins_user_group"

	USER_LOG_INDEX  string = "admins_user_log_index"
	USERS_LIST      string = "admins_users_list"
	USER_LOG        string = "admins_user_log"
	PROPS_ISSUE_LOG string = "admins_props_issue_log"
	LOGIN_SESSION   string = "admins_login_session"
)

const (
	KEY_GAIN_COIN  string = "rankgain"
	KEY_TOTAL_COIN string = "ranktotal"
	KEY_EXP        string = "rankexp"
	KEY_WIN        string = "rankwin"
	KEY_DIAMOND    string = "rankdiamond"

	KEY_LAST_GAIN_COIN  string = "ranklastgain"
	KEY_LAST_TOTAL_COIN string = "ranklasttotal"
	KEY_LAST_EXP        string = "ranklastexp"
	KEY_LAST_WIN        string = "ranklastwin"
	KEY_LAST_DIAMOND    string = "ranklastdiamond"

	KEY_PHONE_INDEX   string = "phoneindex"
	KEY_CIRCLE_NAME   string = "circlename"
	KEY_SOCIAL_ID     string = "socialid"
	KEY_ROOM_ID       string = "roomid"
	KEY_WECHAT_OPNEID string = "wechatid"

	KEY_ROOM                    string = "room"
	KEY_ROOM_CREATE_RECORD      string = "room_create_recode"       //  房间创建记录
	KEY_ROOM_USER_CREATE_RECORD string = "room_user_create_recode:" //   房间针对玩家创建记录
	KEY_TASK                    string = "task:"
	KEY_USER                    string = "user:"

	KEY_FEEDBACK string = "feedback:"

	KEY_GAME_RECORD               string = "gamerecord:"               // 金币场牌局个人记录，用于后台系统
	KEY_GAME_RECORD_QUEUE         string = "gamerecord_queue:"         // 金币场牌局个人记录列表，存储金币场牌局个人记录引用
	KEY_PRIVATE_GAME_RECORD       string = "private_gamerecord:"       // 私人局牌局个人记录，用于后台系统
	KEY_PRIVATE_GAME_RECORD_QUEUE string = "private_gamerecord_queue:" // 私人局牌局个人记录列表，存储私人局牌局个人记录引用
	KEY_MATCH_GAME_RECORD         string = "match_gamerecord:"         // 比赛场牌局个人记录，用于后台系统
	KEY_MATCH_GAME_RECORD_QUEUE   string = "match_gamerecord_queue:"   // 比赛场牌局个人记录列表，存储比赛场牌局个人记录引用

	KEY_CARD_RECORD       string = "card_record"        // 打牌记录
	KEY_CARD_RECORD_INDEX string = "card_record_index:" // 打牌记录累ID

	KEY_ACTIVITY     string = "activity:"
	KEY_NOTICE       string = "notice"
	KEY_TRADE        string = "trade"
	KEY_TRADE_INFO   string = "tradeinfo:"
	KEY_TRADE_RECORD string = "traderecord:"

	KEY_TRADE_INFO_RECORD string = "tradeinforecord:"

	KEY_GAME_RECORD_PRIVATE string = "recordprivate:"
	KEY_GAIN_IN_SOCIAL      string = "gaininsocial:"    // 玩家在某圈子的输赢
	KEY_GAIN_IN_ROOM        string = "gaininroom:"      // 玩家在某房间的输赢
	KEY_SOCIAL_TOTAL_COIN   string = "socialtotalcoin:" //   玩家在指定圈子的总输赢
	KEY_GAME_RECORD_MATCH   string = "recordmatch:"

	KEY_SIGNIN string = "signin:"

	KEY_POSTBOX      string = "postbox:"
	KEY_POSTBOX_LIST string = "postboxlist:"

	KEY_APPLY_MSG string = "applymsg:"
	KEY_AGREE_MSG string = "agreemsg:"

	KEY_LAST_USER_ID string = "lastuserid:"

	KEY_USER_ACTIVE string = "user_active:"

	KEY_CIRCLE string = "circle:"

	KEY_CIRCLE_USER     string = "circleuser:"
	KEY_USERS_IN_CIRCLE string = "usersincircle:"

	KEY_BANKRUPT string = "bankrupt:"
	KEY_ARCHIVE  string = "archive:"

	KEY_OFFLINE_REWARDS string = "offline_rewards:"

	KEY_USER_CHARGEORDER string = "user_chargeorder:"
	KEY_CPORDERID        string = "cporderid"
	KEY_CHARGEORDER      string = "chargeorder:"
	KEY_TRADINGRESULTS   string = "tradingresults:"
	KEY_TRADINGOFFLINE   string = "tradingoffline:"

	KEY_PRIVATE_RECORD  string = "private_record:"         // 私人局牌局记录roomid
	KEY_PRIVATE_RECORDS string = "private_records:"        // 私人局牌局记录
	KEY_ONLINE          string = "online"                  // 在线玩家id列表
	KEY_RESOURCE_CHANGE string = "resource_change_record:" // 资源变动记录

	KEY_ONLINE_STATISTICS  string = "online_statistics"  // 在线统计
	KEY_NEWUSER_STATISTICS string = "newuser_statistics" // 新增统计
	KEY_ACTIVE_STATISTICS  string = "active_statistics"  // 活跃统计
)
