<?php

namespace app\components;
use yii\helpers\Json;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * 微信API接口类
 */
class WeixinApi
{
    public $appid;
    public $appSecret;
    public $mch_id;
    public $mch_key;

    function __construct()
    {
        // TODO: Implement __construct() method.
        $this->appid = Yii::$app->params['OTHERCONFIG']['WEIXIN']['appId'];
        $this->appSecret = Yii::$app->params['OTHERCONFIG']['WEIXIN']['appSecret'];
        $this->mch_id = isset(Yii::$app->params['OTHERCONFIG']['WEIXIN']['mch_id'])?Yii::$app->params['OTHERCONFIG']['WEIXIN']['mch_id']:'';
        $this->mch_key = isset(Yii::$app->params['OTHERCONFIG']['WEIXIN']['mch_key'])?Yii::$app->params['OTHERCONFIG']['WEIXIN']['mch_key']:'';
    }


    public $status=array(
		  '-1'  =>  '系统繁忙',
		   '0'  =>  '请求成功',
		'40001'  =>  '获取access_token时AppSecret错误，或者access_token无效',
		'40002'  =>  '不合法的凭证类型',
		'40003'  =>  '不合法的OpenID',
		'40004'  =>  '不合法的媒体文件类型',
		'40005'  =>  '不合法的文件类型',
		'40006'  =>  '不合法的文件大小',
		'40007'  =>  '不合法的媒体文件id',
		'40008'  =>  '不合法的消息类型',
		'40009'  =>  '不合法的图片文件大小',
		'40010'  =>  '不合法的语音文件大小',
		'40011'  =>  '不合法的视频文件大小',
		'40012'  =>  '不合法的缩略图文件大小',
		'40013'  =>  '不合法的APPID',
		'40014'  =>  '不合法的access_token',
		'40015'  =>  '不合法的菜单类型',
		'40016'  =>  '不合法的按钮个数',
		'40017'  =>  '不合法的按钮个数',
		'40018'  =>  '不合法的按钮名字长度',
		'40019'  =>  '不合法的按钮KEY长度',
		'40020'  =>  '不合法的按钮URL长度',
		'40021'  =>  '不合法的菜单版本号',
		'40022'  =>  '不合法的子菜单级数',
		'40023'  =>  '不合法的子菜单按钮个数',
		'40024'  =>  '不合法的子菜单按钮类型',
		'40025'  =>  '不合法的子菜单按钮名字长度',
		'40026'  =>  '不合法的子菜单按钮KEY长度',
		'40027'  =>  '不合法的子菜单按钮URL长度',
		'40028'  =>  '不合法的自定义菜单使用用户',
		'40029'  =>  '不合法的oauth_code',
		'40030'  =>  '不合法的refresh_token',
		'40031'  =>  '不合法的openid列表',
		'40032'  =>  '不合法的openid列表长度',
		'40033'  =>  '不合法的请求字符，不能包含\uxxxx格式的字符',
		'40035'  =>  '不合法的参数',
		'40038'  =>  '不合法的请求格式',
		'40039'  =>  '不合法的URL长度',
		'40050'  =>  '不合法的分组id',
		'40051'  =>  '分组名字不合法',
		'41001'  =>  '缺少access_token参数',
		'41002'  =>  '缺少appid参数',
		'41003'  =>  '缺少refresh_token参数',
		'41004'  =>  '缺少secret参数',
		'41005'  =>  '缺少多媒体文件数据',
		'41006'  =>  '缺少media_id参数',
		'41007'  =>  '缺少子菜单数据',
		'41008'  =>  '缺少oauth code',
		'41009'  =>  '缺少openid',
		'42001'  =>  'access_token超时',
		'42002'  =>  'refresh_token超时',
		'42003'  =>  'oauth_code超时',
		'43001'  =>  '需要GET请求',
		'43002'  =>  '需要POST请求',
		'43003'  =>  '需要HTTPS请求',
		'43004'  =>  '需要接收者关注',
		'43005'  =>  '需要好友关系',
		'44001'  =>  '多媒体文件为空',
		'44002'  =>  'POST的数据包为空',
		'44003'  =>  '图文消息内容为空',
		'44004'  =>  '文本消息内容为空',
		'45001'  =>  '多媒体文件大小超过限制',
		'45002'  =>  '消息内容超过限制',
		'45003'  =>  '标题字段超过限制',
		'45004'  =>  '描述字段超过限制',
		'45005'  =>  '链接字段超过限制',
		'45006'  =>  '图片链接字段超过限制',
		'45007'  =>  '语音播放时间超过限制',
		'45008'  =>  '图文消息超过限制',
		'45009'  =>  '接口调用超过限制',
		'45010'  =>  '创建菜单个数超过限制',
		'45015'  =>  '回复时间超过限制',
		'45016'  =>  '系统分组，不允许修改',
		'45017'  =>  '分组名字过长',
		'45018'  =>  '分组数量超过上限',
		'46001'  =>  '不存在媒体数据',
		'46002'  =>  '不存在的菜单版本',
		'46003'  =>  '不存在的菜单数据',
		'46004'  =>  '不存在的用户',
		'47001'  =>  '解析JSON/XML内容错误',
		'48001'  =>  'api功能未授权',
		'50001'  =>  '用户未授权该api',
		);

	/**
	 * 通过get方式发送到微信服务器，并返回服务器响应信息
	 * @param string $url		链接地址
	 * @param integer $timeout	超时时间
	 * @return string $backDate	微信服务器返回的json信息，发送失败时返回false
	 */
	public function curlGet($url,$timeout=5){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$backData = curl_exec($ch);
		curl_close($ch);

		return $backData;
	}

	/**
	 * 通过post方式发送到微信服务器，并返回服务器响应信息
	 * @param string $url		链接地址
	 * @param string $postData	json格式数据
	 * @param integer $timeout	超时时间
	 * @return string $backDate	微信服务器返回的json信息，发送失败时返回false
	 */
	public function curlPost($url,$postData,$timeout=5){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$backData = curl_exec($ch);
		curl_close($ch);

		return $backData;
	}

	/**
	 * 用于检查是否从微信服务器请求
	 */
	public function checkSignature(){
		if (isset($_REQUEST['signature']) && isset($_REQUEST['timestamp']) && isset($_REQUEST['nonce'])) {
			$signature = $_REQUEST["signature"];
			$timestamp = $_REQUEST["timestamp"];
			$nonce = $_REQUEST["nonce"];

			$token = Yii::$app->params['token'];
			$tmpArr = array($token, $timestamp, $nonce);
			sort($tmpArr, SORT_STRING);
			$tmpStr = implode( $tmpArr );
			$tmpStr = sha1( $tmpStr );

			if( $tmpStr == $signature ){
				return true;
			}
		}

		return false;
	}

	/**
	 * 用于申请开发者模式时给予微信服务器验证
	 */
	public function valid(){
		if (!empty($_REQUEST['echostr']) && $this->checkSignature()) {
			echo $_REQUEST['echostr'];

		} else {
			echo '';
		}

		Yii::$app->end();
	}
	/**
	 * 从Memcache中取得accessToken，如果空则从微信服务器申请一个保存到Memcache
	 * @param	bool $refresh		是否强制刷新accessToken
	 * @return	string $accessToken 返回accessToken字串，如果申请错误则返回false
	 */
	public function getAccessToken($refresh=false){
		//若不强制刷新accessToken，且memcache中存在accessToken，则返回accessToken
		if (!$refresh) {
			$accessToken = Yii::$app->cache->get('accessToken');

			if ( !empty($accessToken) ) {
				return $accessToken;
			}
		}

		//若Memcache无accessToken，或已过期，则从微信服务器里申请一个保存到Memcache
		$curlResponse = $this->curlGet('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appSecret);

		//转换成数组
		$response = JSON::decode($curlResponse);

		//如果accessToken非空，则保存到Memcache并返回
		if ( !empty($response['access_token']) ) {
			Yii::$app->cache->set( 'accessToken', $response['access_token'], (int)($response['expires_in'] * 0.9) );

			return Yii::$app->cache->get('accessToken');
		}

		Yii::log(__METHOD__.' curl get token error, curl response:'.$curlResponse, 'error', 'application');
		return false;
	}

	/**
	 * 取得js sdk授权凭证
	 * 基于memcache缓存
	 */
	public function getJsApiTicket(){
		$jsApiTicket = Yii::$app->cache->get('jsApiTicket');

		if ( !empty($jsApiTicket) ) {
			return $jsApiTicket;
		}

		$accessToken = $this->getAccessToken();

		if ($accessToken) {
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";

			$curlResponse = $this->curlGet($url);

			$response = JSON::decode($curlResponse);

			if ( !empty($response['ticket']) && !empty($response['expires_in']) ) {
				Yii::$app->cache->set( 'jsApiTicket', $response['ticket'], (int)($response['expires_in'] * 0.9) );

				return Yii::$app->cache->get('jsApiTicket');
			}

			Yii::log(__METHOD__.' curl get jsApiTicket error, curl response:'.$curlResponse, 'error', 'application');
		}

		return false;
	}

	/**
	 * 获得特定页面jssdk的配置
	 * @param	string	$url		页面url，注意不带#参数
	 * @param	integer	$timestamp	时间戳，默认当前
	 * @return	array				配置集合
	 */
	public function getJsApiConfig($url, $timestamp=0){
		$timestamp = $timestamp==0 ? $_SERVER['REQUEST_TIME'] : $timestamp;

		//取得jsapi验证ticket
		$jsApiTicket = $this->getJsApiTicket();

		if (!empty($jsApiTicket)) {
			//生成16位随机字符
			$nonceStr = '';
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			for ($i = 0; $i < 16; $i++) {
				$nonceStr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			}

			//生成signature
			$string = "jsapi_ticket=$jsApiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
			$signature = sha1($string);

			return array(
				"appId"     => $this->appid,
				"nonceStr"  => $nonceStr,
				"timestamp" => $timestamp,
				"url"       => $url,
				"signature" => $signature,
				"rawString" => $string
			);
		}

		Yii::log(__METHOD__.' empty jsApiTicket', 'error', 'application');
		return false;
	}

	/**
	 * 从微信服务器取得二维码ticket
	 * @param string $postData Json格式的数据
	 * @return string 二维码ticket
	 */
	public function getTicket($postData){
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->getAccessToken();

		if ($curlResponse = $this->curlPost($url, $postData)) {
			$response = JSON::decode($curlResponse);

			if (!empty($response['ticket'])) {
				return $response['ticket'];
			}
		}

		Yii::log(__METHOD__.' curl get qrcode ticket error, curl response:'.$curlResponse, 'error', 'application');
		return false;
	}

	/**
	 * 判断字符串是否为xml格式，返回xml对象，格式不正确返回false
	 * @param string $string	字符串
	 * @return object SimpleXMLElement对象
	 */
	public function loadXml($string){
		if ($string==='') {
			return false;
		}

		return simplexml_load_string($string,'SimpleXMLElement',LIBXML_NOCDATA);
	}

	/**
	 * 生成自动回复文本的xml
	 * @param string $toUser		微信用户openId
	 * @param string $content		发送的文本内容
	 * @param integer $timestamp	消息时间戳
	 * @return string $xml			文本消息，xml格式字符串
	 */
	public function buildTextXml($toUser='', $content='', $timestamp=0){
		$xml  = '<xml>';
		$xml .= '<ToUserName><![CDATA['.$toUser.']]></ToUserName>';
		$xml .= '<FromUserName><![CDATA['.Yii::$app->params['originalId'].']]></FromUserName>';
		$xml .= '<CreateTime>'.$timestamp.'</CreateTime>';
		$xml .= '<MsgType><![CDATA[text]]></MsgType>';
		$xml .= '<Content><![CDATA['.$content.']]></Content>';
		$xml .= '</xml>';

		return $xml;
	}

	/**
	 * 发送客服文本消息
	 * @param string $accessToken	微信服务器接入全局凭证
	 * @param string $toUser		微信用户openId
	 * @param string $content		发送的文本内容
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function sendServiceText($accessToken, $toUser='', $content=''){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$accessToken;

		$postData = '{"touser":"'.$toUser.'","msgtype":"text","text":{"content":"'.addslashes($content).'"}}';

		return $this->curlPost($url, $postData);
	}

	/**
	 * 生成自动回复图片的xml
	 * @param string $toUser		微信用户openId
	 * @param string $mediaId		图片文件媒体id
	 * @param integer $timestamp	消息时间戳
	 * @return string $xml			图片消息，xml格式字符串
	 */
	public function buildImageXml($toUser='', $mediaId='', $timestamp=0){
		$xml  = '<xml>';
		$xml .= '<ToUserName><![CDATA['.$toUser.']]></ToUserName>';
		$xml .= '<FromUserName><![CDATA['.Yii::$app->params['originalId'].']]></FromUserName>';
		$xml .= '<CreateTime>'.$timestamp.'</CreateTime>';
		$xml .= '<MsgType><![CDATA[image]]></MsgType>';
		$xml .= '<Image>';
		$xml .= '<MediaId><![CDATA['.$mediaId.']]></MediaId>';
		$xml .= '</Image>';
		$xml .= '</xml>';

		return $xml;
	}

	/**
	 * 发送客服图片消息
	 * @param string $accessToken	微信服务器接入全局凭证
	 * @param string $toUser		微信用户openId
	 * @param string $mediaId		图片文件媒体id
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function sendServiceImage($accessToken, $toUser='', $mediaId=''){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$accessToken;

		$postData = '{"touser":"'.$toUser.'","msgtype":"image","image":{"media_id":"'.$mediaId.'"}}';

		return $this->curlPost($url, $postData);
	}

	/**
	 * 生成自动回复语音的xml
	 * @param string $toUser		微信用户openId
	 * @param string $mediaId		语音文件媒体id
	 * @param integer $timestamp	消息时间戳
	 * @return string $xml			语音消息，xml格式字符串
	 */
	public function buildVoiceXml($toUser='', $mediaId='', $timestamp=0){
		$xml  = '<xml>';
		$xml .= '<ToUserName><![CDATA['.$toUser.']]></ToUserName>';
		$xml .= '<FromUserName><![CDATA['.Yii::$app->params['originalId'].']]></FromUserName>';
		$xml .= '<CreateTime>'.$timestamp.'</CreateTime>';
		$xml .= '<MsgType><![CDATA[voice]]></MsgType>';
		$xml .= '<Voice>';
		$xml .= '<MediaId>'.$mediaId.'</MediaId>';
		$xml .= '</Voice>';
		$xml .= '</xml>';

		return $xml;
	}

	/**
	 * 发送客服语音消息
	 * @param string $accessToken	微信服务器接入全局凭证
	 * @param string $toUser		微信用户openId
	 * @param string $mediaId		语音文件媒体id
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function sendServiceVoice($accessToken, $toUser='', $mediaId=''){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$accessToken;

		$postData = '{"touser":"'.$toUser.'","msgtype":"voice","voice":{"media_id":"'.$mediaId.'"}}';

		return $this->curlPost($url, $postData);
	}

	/**
	 * 生成自动回复视频的xml
	 * @param string $toUser		微信用户openId
	 * @param string $mediaId		视频文件媒体id
	 * @param integer $timestamp	消息时间戳
	 * @param string $title			视频标题
	 * @param string $description	视频描述
	 * @return string $xml			视频消息，xml格式字符串
	 */
	public function buildVideoXml($toUser='', $mediaId='', $timestamp=0, $title='', $description=''){
		$xml  = '<xml>';
		$xml .= '<ToUserName><![CDATA['.$toUser.']]></ToUserName>';
		$xml .= '<FromUserName><![CDATA['.Yii::$app->params['originalId'].']]></FromUserName>';
		$xml .= '<CreateTime>'.$timestamp.'</CreateTime>';
		$xml .= '<MsgType><![CDATA[video]]></MsgType>';
		$xml .= '<Video>';
		$xml .= '<MediaId><![CDATA['.$mediaId.']]></MediaId>';
		$xml .= '<Title><![CDATA['.$title.']]></Title>';
		$xml .= '<Description><![CDATA['.$description.']]></Description>';
		$xml .= '</Video> ';
		$xml .= '</xml>';

		return $xml;
	}

	/**
	 * 发送客服视频消息
	 * @param string $accessToken	微信服务器接入全局凭证
	 * @param string $toUser		微信用户openId
	 * @param string $mediaId		视频文件媒体id
	 * @param string $title			视频标题
	 * @param string $description	视频描述
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function sendServiceVideo($accessToken, $toUser='', $mediaId='', $title='', $description=''){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$accessToken;

		$postData = '{"touser":"'.$toUser.'","msgtype":"video","video":{"media_id":"'.$mediaId.'","title":"'.$title.'","description":"'.$description.'"}}';

		return $this->curlPost($url, $postData);
	}

	/**
	 * 生成自动回复音乐的xml
	 * @param string $toUser		微信用户openId
	 * @param string $thumbMediaId	省略图文件媒体id
	 * @param integer $timestamp	消息时间戳
	 * @param string $title			音乐标题
	 * @param string $description	音乐描述
	 * @param string $musicUrl		音乐链接
	 * @param string $HQMusicUrl	高音质音乐链接
	 * @return string $xml			音乐消息，xml格式字符串
	 */
	public function buildMusicXml($toUser='', $thumbMediaId='', $timestamp=0, $title='', $description='', $musicUrl='', $HQMusicUrl=''){
		$xml  = '<xml>';
		$xml .= '<ToUserName><![CDATA['.$toUser.']]></ToUserName>';
		$xml .= '<FromUserName><![CDATA['.Yii::$app->params['originalId'].']]></FromUserName>';
		$xml .= '<CreateTime>'.$timestamp.'</CreateTime>';
		$xml .= '<MsgType><![CDATA[music]]></MsgType>';
		$xml .= '<Music>';
		$xml .= '<Title><![CDATA['.$title.']]></Title>';
		$xml .= '<Description><![CDATA['.$description.']]></Description>';
		$xml .= '<MusicUrl><![CDATA['.htmlspecialchars($musicUrl).']]></MusicUrl>';
		$xml .= '<HQMusicUrl><![CDATA['.htmlspecialchars($HQMusicUrl).']]></HQMusicUrl>';
		$xml .= '<ThumbMediaId><![CDATA['.$thumbMediaId.']]></ThumbMediaId>';
		$xml .= '</Music>';
		$xml .= '</xml>';

		return $xml;
	}

	/**
	 * 发送客服音乐消息
	 * @param string $accessToken	微信服务器接入全局凭证
	 * @param string $toUser		微信用户openId
	 * @param string $thumbMediaId	省略图文件媒体id
	 * @param string $title			音乐标题
	 * @param string $description	音乐描述
	 * @param string $musicUrl		音乐链接
	 * @param string $HQMusicUrl	高音质音乐链接
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function sendServiceMusic($accessToken, $toUser='', $thumbMediaId='', $title='', $description='', $musicUrl='', $HQMusicUrl=''){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$accessToken;

		$postData = '{"touser":"'.$toUser.'","msgtype":"music","music":{"title":"'.$title.'","description":"'.$description.'","musicurl":"'.$musicUrl.'","hqmusicurl":"'.$HQMusicUrl.'","thumb_media_id":"'.$thumbMediaId.'" }}';

		return $this->curlPost($url, $postData);
	}

	/**
	 * 生成自动回复图文的xml
	 * @param string $toUser		微信用户openId
	 * @param string $thumbMediaId	省略图文件媒体id
	 * @param integer $timestamp	消息时间戳
	 * @param array $articles		图文消息合集，每个item是一个数组：array('title'=>..., 'description'=>..., 'picurl'=>..., 'url'=>...)
	 * @return string $xml			图文消息，xml格式字符串。如果articles数超过限定则返回false
	 */
	public function buildNewsXml($toUser='', $timestamp=0, $articles=array()){
		$articleCount = count($articles);

		//图文数量不得超过限制数
		if ( $articleCount>Yii::$app->params['newsItemLimit'] ) {
			return false;
		}

		$xml  = '<xml>';
		$xml .= '<ToUserName><![CDATA['.$toUser.']]></ToUserName>';
		$xml .= '<FromUserName><![CDATA['.Yii::$app->params['originalId'].']]></FromUserName>';
		$xml .= '<CreateTime>'.$timestamp.'</CreateTime>';
		$xml .= '<MsgType><![CDATA[news]]></MsgType>';
		$xml .= '<ArticleCount>'.count($articles).'</ArticleCount>';
		$xml .= '<Articles>';
		foreach ($articles as $item) {
			$xml .= '<item>';
			$xml .= '<Title><![CDATA['.$item['title'].']]></Title> ';
			$xml .= '<Description><![CDATA['.$item['description'].']]></Description>';
			$xml .= '<PicUrl><![CDATA['.$item['picurl'].']]></PicUrl>';
			$xml .= '<Url><![CDATA['.$item['url'].']]></Url>';
			$xml .= '</item>';
		}
		$xml .= '</Articles>';
		$xml .= '</xml> ';

		return $xml;
	}

	/**
	 * 发送客服图文消息
	 * @param string $accessToken	微信服务器接入全局凭证
	 * @param string $toUser		微信用户openId
	 * @param array $articles		图文消息合集，每个item是一个数组：array('title'=>..., 'description'=>..., 'picurl'=>..., 'url'=>...)
	 * @return string				微信服务器返回的json结果。如果articles数超过限定则返回false，发送失败则返回false
	 */
	public function sendServiceNews($accessToken, $toUser='', $articles=array()){
		//图文数量不得超过限制数
		if ( count($articles)>Yii::$app->params['newsItemLimit'] ) {
			return false;
		}

		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$accessToken;

		$postData = '';

		//php5.4及以上版本可使用json_encode的JSON_UNESCAPED_UNICODE跳过中文字符转换
		if ( version_compare(PHP_VERSION,'5.4','>=') ) {
			$postData = '{"touser":"'.$toUser.'","msgtype":"news","news":{"articles":'.json_encode($articles,JSON_UNESCAPED_UNICODE).'}}';

		//php5.4以下版本需拼接json以跳过中文字符转换
		} else {
			$postData = '{"touser":"'.$toUser.'","msgtype":"news","news":{"articles":[';
			foreach ($articles as $item) {
				$postData .= '{"title":"'.addslashes($item['title']).'","description":"","url":"'.$item['url'].'","picurl":"'.$item['picurl'].'"},';
			}
			$postData = rtrim($postData, ',').']}}';
		}

		return $this->curlPost($url, $postData);
	}

	/**
	 * 生成发送文本的JSON
	 * @param string $toUser		微信用户openId
	 * @param string $content		发送的文本内容
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function buildTextJson($toUser='', $content=''){
		return '{"touser":"'.$toUser.'","msgtype":"text","text":{"content":"'.addslashes($content).'"}}';
	}

	/**
	 * 生成发送图片的JSON
	 * @param string $toUser		微信用户openId
	 * @param string $mediaId		图片文件媒体id
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function buildImageJson($toUser='', $mediaId=''){
		return '{"touser":"'.$toUser.'","msgtype":"image","image":{"media_id":"'.$mediaId.'"}}';
	}

	/**
	 * 生成发送语音的JSON
	 * @param string $toUser		微信用户openId
	 * @param string $mediaId		语音文件媒体id
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function buildVoiceJson($toUser='', $mediaId=''){
		return '{"touser":"'.$toUser.'","msgtype":"voice","voice":{"media_id":"'.$mediaId.'"}}';
	}

	/**
	 * 生成发送视频的JSON
	 * @param string $toUser		微信用户openId
	 * @param string $mediaId		视频文件媒体id
	 * @param string $title			视频标题
	 * @param string $description	视频描述
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function buildVideoJson($toUser='', $mediaId='', $title='', $description=''){
		return '{"touser":"'.$toUser.'","msgtype":"video","video":{"media_id":"'.$mediaId.'","title":"'.$title.'","description":"'.$description.'"}}';
	}

	/**
	 * 生成发送音乐的JSON
	 * @param string $toUser		微信用户openId
	 * @param string $thumbMediaId	省略图文件媒体id
	 * @param string $title			音乐标题
	 * @param string $description	音乐描述
	 * @param string $musicUrl		音乐链接
	 * @param string $HQMusicUrl	高音质音乐链接
	 * @return string				微信服务器返回的json结果，发送失败则返回false
	 */
	public function buildMusiJSON($toUser='', $thumbMediaId='', $title='', $description='', $musicUrl='', $HQMusicUrl=''){
		return '{"touser":"'.$toUser.'","msgtype":"music","music":{"title":"'.$title.'","description":"'.$description.'","musicurl":"'.htmlspecialchars($musicUrl).'","hqmusicurl":"'.htmlspecialchars($HQMusicUrl).'","thumb_media_id":"'.$thumbMediaId.'" }}';
	}

	/**
	 * 生成发送图文的JSON
	 * @param string $toUser		微信用户openId
	 * @param array $articles		图文消息合集，每个item是一个数组：array('title'=>..., 'description'=>..., 'picurl'=>..., 'url'=>...)
	 * @return string				微信服务器返回的json结果。如果articles数超过限定则返回false，发送失败则返回false
	 */
	public function buildNewsJson($toUser='', $articles=array()){
		$articleCount = count($articles);

		//图文数量不得超过限制数
		if ( $articleCount<1 || $articleCount>Yii::$app->params['newsItemLimit'] ) {
			return false;
		}

		$json = '';

		//php5.4及以上版本可使用json_encode的JSON_UNESCAPED_UNICODE跳过中文字符转换
		if ( version_compare(PHP_VERSION,'5.4','>=') ) {
			$json = '{"touser":"'.$toUser.'","msgtype":"news","news":{"articles":'.json_encode($articles,JSON_UNESCAPED_UNICODE).'}}';

		//php5.4以下版本需拼接json以跳过中文字符转换
		} else {
			$json = '{"touser":"'.$toUser.'","msgtype":"news","news":{"articles":[';
			foreach ($articles as $item) {
				$json .= '{"title":"'.addslashes($item['title']).'","description":"","url":"'.$item['url'].'","picurl":"'.$item['picurl'].'"},';
			}
			$json = rtrim($json, ',').']}}';
		}

		return $json;
	}

	public function sendService($accessToken='', $postData=''){
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$accessToken;

		return $this->curlPost($url, $postData);
	}

	/**
	 *	上传素材到微信服务器
	 *	@param string $type		上传文件类型
	 *	@param string $local	本地文件路径
	 *	@return string			微信服务器返回的json结果。如果上传失败则返回false
	 */
	public function uploadWeixin($type=0,$local='')
	{
		if($type!=0&&$type<=4&&$local!=''&&ApiLimit::model()->increaseApiCount('uploadMediaFile')){
			$local=rtrim(Yii::$app->params['uploadFilePath'],'/').'/'.$local;
			$typeArr=array('1'=>'image','2'=>'voice','3'=>'video','4'=>'thumb');
			$token = $this->getAccessToken();
			$url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=$token&type=$typeArr[$type]";
			$fields['media'] = '@'.$local;
			$getBack = JSON::decode($this->curlPost($url,$fields));
			return $getBack;
		}
		return false;
	}

	public function downloadFile($mediaId){
		$token = $this->getAccessToken();

		$url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.$token.'&media_id='.$mediaId;

		$file = $this->curlGet($url);

		echo $file;
	}

    /**
     *	获取授权格式url
     * @param string $url 回调地址
     * @param bool $defaulttype 授权方式
     * @return string
     */
    public function getOauthUrl($url='',$defaulttype=true)
	{
		$url = urlencode($url);
        if($defaulttype){
            $oauthUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appid."&redirect_uri=".$url."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";

        }else{
            $oauthUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_userinfo#wechat_redirect';
        }

		return $oauthUrl;
	}

	/**
	 *	获取授权token
	 *	@param	string	$code	获取code
	 *	@return	string 			返回授权token
	 */
	public function getOauthToken($code='')
	{
		if ($code=='') {
			return false;
		}
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appid.'&secret='.$this->appSecret.'&code='.$code.'&grant_type=authorization_code';
		$token = $this->curlGet($url,$timeout=10);
		return $token;
	}


    /**
     * 将xml转为array
     * @param string $xml
     * @throws NotFoundHttpException
     */
    public function FromXml($xml)
    {
        if(!$xml){
            return "";
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }


    /**
     * 输出xml字符
     * @throws ForbiddenHttpException
     **/
    public function ToXml($params)
    {
        if(!is_array($params)
            || count($params) <= 0)
        {
            throw new ForbiddenHttpException("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($params as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param int $second   url执行超时时间，默认30s
     * @throws NotFoundHttpException
     */
    private static function postXmlCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new NotFoundHttpException("curl出错，错误码:$error");
        }
    }


    public function getPaySign($params){
        if(!is_array($params)){
            return "";
        }

        sort($params);

        $stringA = implode('&',$params);
        $stringSignTemp =$stringA."&key=".$this->mch_key;
        $sign = strtoupper(md5($stringSignTemp));
        return $sign;
    }


    public function getOrderPay($openid,$notify_url,$out_trade_no,$body,$money)
    {
        $nonce_str=md5(time());
        $ip = $_SERVER["REMOTE_ADDR"];

        $key = 'appid='.$this->appid.'&mch_id='.$this->mch_id.'&total_fee='.$money.'&nonce_str='.$nonce_str.'&out_trade_no='.$out_trade_no.'&spbill_create_ip='.$ip.'&openid='.$openid.'&body='.$body.'&notify_url='.$notify_url.'&trade_type=JSAPI';

        $keyArray = explode('&',$key);
        $sign = $this->getPaySign($keyArray);

        $keyArray[] = "sign=".$sign;
        $params =  [];
        foreach($keyArray as $v){
            $keyvalue = explode('=',$v);
            $params[$keyvalue[0]]=$keyvalue[1];
        }

        $xml = $this->ToXml($params);

        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        $token = $this->postXmlCurl($xml,$url,$timeout=10);
        $data = $this->FromXml($token);

        return $data;
    }


}