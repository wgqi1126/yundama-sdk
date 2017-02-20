<?php
/**
 * User: echo
 * Date: 17/2/20
 * Time: 上午10:03
 */

namespace wgqi1126\YunDaMa\Model;


class Result
{
    /**
     * @var int
     */
    public $cid;

    /**
     * @var string
     */
    public $text;

    public function __construct($cid, $text)
    {
        $this->cid = $cid;
        $this->text = $text;
    }
}