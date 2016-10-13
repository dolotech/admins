<?php
/**
 * 后台管理日志
 * @author 彭 qq:249828165
 */
class Act_admin extends Page {
    public $db = null;
    public $curEvent = 0;
    private $limit = 30, $page = 0;
    public $events = array(
        0 => '全部',
        1 => '登录后台',
        4 => '发元宝',
        5 => '发物品',
        6 => '扣钱',
        111 => 'GM操作 - 封停',
        112 => 'GM操作 - 解封',
        113 => 'GM操作 - 封IP',
        114 => 'GM操作 - 解IP',
        115 => 'GM操作 - 禁言',
        116 => 'GM操作 - 解禁',
        119 => 'GM操作 - 登陆',
    );

    public function __construct() {
        parent::__construct();
        $this->db = Db::getInstance();
        if (isset($this->input['page']) && is_numeric($this->input['page'])) {
            $this->page = $this->input['page'];
        }
        if (isset($this->input['limit']) && is_numeric($this->input['limit'])) {
            $this->limit = $this->input['limit'];
        }
        if(isset($this->input['kw']['event'])){
            $this->curEvent = $this->input['kw']['event'];
        }
        $this->assign('events', Form::select('kw[event]', $this->events, $this->curEvent));
    }

    public function process ()
    {
		$where = array();
        if ($this->input['kw']['start_time'] && $this->input['kw']['end_time']) {
            $where[] = "ctime >=".strtotime($this->input['kw']['start_time']);
            $where[] = "ctime <=".strtotime($this->input['kw']['end_time']);
        }
		if ($this->input['kw']['admin_name']) {
			$where[] = "admin_name='{$this->input['kw']['admin_name']}'";
		}
		if ($this->input['kw']['event']) {
			$where[] = "event='{$this->input['kw']['event']}'";
		}

		$sql = 'select * from admin_log';
		if ($where) {
			$sql .= " where ".implode(' and ', $where);

		}
        $totalRecord = $this->db->getOne(str_replace("select *", "select count(*)", $sql));
        $sql .= ' order by id desc';
        $limit = " limit " . ($this->page * $this->limit) . ", {$this->limit}";
        $data = $this->db->getAll($sql.$limit);

		foreach($data as &$Pval)
		{
			$Pval['ctime'] = date('Y-m-d H:i:s', $Pval['ctime']);
            $Pval['event'] = $this->events[$Pval['event']] ? $this->events[$Pval['event']] : $Pval['event'];
            $Pval['ip'] = $Pval['ip'] . '(' . Utils::ip2addr($Pval['ip']) . ')';
		}

        $this->assign('data', $data);
        $this->assign('page', $this->createPager($totalRecord));
        $this->display();
    }

    /**
     *
     * @param int $totalRecord
     * @return string
     */
    private function createPager($totalRecord) {
        if (! $totalRecord)
            return;

        $pager = Utils::pager(Admin::url('', '','', true), $totalRecord, $this->page, $this->limit);
        return $pager;
    }

}
