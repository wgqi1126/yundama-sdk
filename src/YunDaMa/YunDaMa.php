<?php
/**
 * User: echo
 * Date: 17/2/17
 * Time: 下午6:56
 */

namespace wgqi1126\YunDaMa;

use CURLFile;
use Exception;
use wgqi1126\YunDaMa\Model\Balance;
use wgqi1126\YunDaMa\Model\Result;

class YunDaMa
{
    /**
     * @var string
     */
    protected static $url = 'http://api.yundama.com/api.php';
    /**
     * @var string
     */
    protected $username;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var int
     */
    protected $appId;
    /**
     * @var string
     */
    protected $appKey;

    /**
     * YunDaMa constructor.
     * @param $username string
     * @param $password string
     * @param $appId int
     * @param $appKey string
     */
    public function __construct($username, $password, $appId, $appKey)
    {
        $this->username = $username;
        $this->password = $password;
        $this->appId = $appId;
        $this->appKey = $appKey;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function login()
    {
        $data = $this->buildData(['method' => 'login']);
        $rs = $this->post($data);
        return $rs['uid'];
    }

    /**
     * @return Balance
     * @throws Exception
     */
    public function balance()
    {
        $data = $this->buildData(['method' => 'balance']);
        $rs = $this->post($data);
        return new Balance($rs['uid'], $rs['balance']);
    }

    /**
     * @param $file string
     * @param $type int
     * @param $timeout int
     * @return Result
     * @throws Exception
     */
    public function upload($file, $type, $timeout)
    {
        $data = $this->buildData(['method' => 'upload', 'codetype' => $type, 'timeout' => $timeout, 'file' => '@' . $file]);
        $rs = $this->post($data);
        return new Result($rs['cid'], $rs['text']);
    }

    /**
     * @param $cid int
     * @return Result
     * @throws Exception
     */
    public function result($cid)
    {
        $data = $this->buildData(['method' => 'result', 'cid' => $cid]);
        $rs = $this->post($data);
        return new Result($rs['cid'], $rs['text']);
    }

    /**
     * @param $file string
     * @param $type int
     * @param $timeout int
     * @return Result
     * @throws Exception
     */
    public function decode($file, $type, $timeout)
    {
        $rs = $this->upload($file, $type, $timeout);
        if ($rs->text) {
            return $rs;
        }
        for ($i = 0; $i < $timeout; $i++) {
            sleep(1);
            try {
                return $this->result($rs->cid);
            } catch (Exception $e) {
                if ($e->getCode() == -3002) {
                    continue;
                } else {
                    throw $e;
                }
            }
        }
    }

    /**
     * @param $data array
     * @return array
     */
    protected function buildData($data)
    {
        $data['username'] = $this->username;
        $data['password'] = $this->password;
        $data['appid'] = $this->appId;
        $data['appkey'] = $this->appKey;
        return $data;
    }

    /**
     * @param $data array
     * @return array
     * @throws Exception
     */
    protected function post($data)
    {
        foreach ($data as $k => $v) {
            if (strpos($v, '@') !== 0) {
                continue;
            }
            if (version_compare(PHP_VERSION, '5.5.0')) {
                $data[$k] = new CURLFile(substr($v, 1));
            } else {
                $data[$k] = $v . ';type=' . mime_content_type(substr($v, 1));
            }
        }
        $ch = curl_init(self::$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data',
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $rs = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code != 200) {
            throw new Exception("http response code is {$code}");
        }
        $json = json_decode($rs, true);
        if ($json['ret'] !== 0) {
            throw new Exception("response error {$rs}", $json['ret']);
        }
        return $json;
    }
}