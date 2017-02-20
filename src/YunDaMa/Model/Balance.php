<?php
/**
 * User: echo
 * Date: 17/2/20
 * Time: 上午10:00
 */

namespace wgqi1126\YunDaMa\Model;


class Balance
{
    /**
     * @var int
     */
    public $uid;
    /**
     * @var int
     */
    public $balance;

    public function __construct($uid, $balance)
    {
        $this->uid = $uid;
        $this->balance = $balance;
    }
}