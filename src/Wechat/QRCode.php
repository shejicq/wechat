<?php
namespace Overtrue\Wechat;

use Overtrue\Wechat\Utils\Bag;

/**
 * 二维码
 */
class QRCode
{
    /**
     * 应用ID
     *
     * @var string
     */
    protected $appId;

    /**
     * 应用secret
     *
     * @var string
     */
    protected $appSecret;

    const DAY = 86400;

    const SCENE_QR_FOREVER     = 'QR_LIMIT_SCENE'; // 临时
    const SCENE_QR_TEMPORARY   = 'QR_LIMIT_SCENE'; // 永久
    const SCENE_QR_STR_FOREVER = 'QR_LIMIT_STR_SCENE'; // 永久的字符串参数值

    const API_CREATE = 'https://mp.weixin.qq.com/cgi-bin/qrcode/create';
    const API_SHOW   = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';


    /**
     * constructor
     *
     * @param string $appId
     * @param string $appSecret
     */
    public function __construct($appId, $appSecret)
    {
        $this->appId     = $appId;
        $this->appSecret = $appSecret;
    }

    /**
     * 创建二维码
     *
     * @param array   $scene
     * @param boolean $temporary
     *
     * @return Bag
     */
    protected function create(array $scene, $temporary = true, $expireSeconds = null)
    {
        $expireSeconds || $expireSeconds = 7 * self::DAY;

        $http = new Http(new AccessToken($this->appId, $this->appSecret));

        $params = array(
                   'expire_seconds' => min($expireSeconds, 7 * self::DAY),
                   'action_name'    => $temporary ? 'QR_SCENE' : 'QR_LIMIT_SCENE',
                   'action_info'    => array('scene' => $scene),
                  );

        return new Bag($http->jsonPost(self::API_CREATE, $params));
    }

    /**
     * 永久二维码
     *
     * @param int    $sceneValue
     * @param int    $expireSeconds
     * @param string $type
     *
     * @return Bag
     */
    public function forever($sceneValue, $expireSeconds = null, $type = self::SCENE_QR_FOREVER)
    {
        $sceneKey = $type == self::SCENE_QR_FOREVER ? 'scene_id' : 'scene_str';
        $scene = array($sceneKey => $sceneValue);

        return $this->create($scene, false, $expireSeconds);
    }

    /**
     * 临时二维码
     *
     * @param int $sceneId
     * @param int $expireSeconds
     *
     * @return Bag
     */
    public function temporary($sceneId, $expireSeconds = null)
    {
        return $this->create($scene, true, $expireSeconds);
    }

    /**
     * 获取二维码
     *
     * @return
     */
    public function show($ticket)
    {
        return self::API_SHOW . "?ticket={$ticket}";
    }

    /**
     * 保存二维码
     *
     * @param string $ticket
     * @param string $filename
     *
     * @return boolean
     */
    public function save($ticket, $filename)
    {
        return file_put_contents($filename, file_get_contents($this->show($ticket)));
    }
}