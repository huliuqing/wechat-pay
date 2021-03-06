<?php
/**
 * @license MIT
 * @author zhangv
 */
namespace zhangv\wechat;
use zhangv\wechat\HttpClient;

class WechatOAuth {

	public $responseJSON = null;
	public $errCode = null;
	public $errMsg = null;

	private $appId = null;
	private $appSecret = null;
	private $httpClient = null;
	private $accessToken = null;

	public function __construct($appId,$appSecret) {
		$this->appId = $appId;
		$this->appSecret = $appSecret;
		$this->httpClient = new HttpClient();
	}

	public function setHttpClient($httpClient){
		$this->httpClient = $httpClient;
	}

	public function setAccessToken($accessToken){
		$this->accessToken = $accessToken;
	}

	public function authorizeURI($redirectURI,$scope = 'snsapi_userinfo',$state = ''){
		$redirectURI = urlencode($redirectURI);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$redirectURI}&response_type=code&scope=$scope&state=$state#wechat_redirect";
	}

	public function authorize($code){
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code=$code&grant_type=authorization_code";
		$this->responseJSON = $this->httpClient->get($url);
		return json_decode($this->responseJSON);
	}

	public function getUserInfo($openId){
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token={$this->accessToken}&openid=$openId&lang=zh_CN";
		$this->responseJSON = $this->httpClient->get($url);
		return json_decode($this->responseJSON);
	}

	public function refreshToken($refreshToken){
		$url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$this->appId}&grant_type=refresh_token&refresh_token=$refreshToken";
		$this->responseJSON = $this->httpClient->get($url);
		return $this->responseJSON;
	}

	public function verifyToken($accessToken,$openId){
		$url = "https://api.weixin.qq.com/sns/auth?access_token=$accessToken&openid=$openId";
		$this->responseJSON = $this->httpClient->get($url);
		return $this->responseJSON;
	}

	public function getAccessToken(){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
		$this->responseJSON = $this->httpClient->get($url);
		$json = json_decode($this->responseJSON);
		$this->accessToken = $json->access_token;
		return $this->accessToken;
	}

	public function getTicket(){
		$accessToken = $this->getAccessToken();
		// $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
		$this->responseJSON = $this->httpClient->get($url);
		return $this->responseJSON;
	}
}
