<?php 
namespace Ionepub;
use Ionepub\Cache;

/**
* Auth class
*/
class Auth
{
	/**
	 * 定义鉴权模式
	 */
	const MODE_SIMPLE = 1; // 简单模式
	const MODE_TOKEN = 2; // token模式
	const MODE_ACCESS_TOKEN = 3; // access token模式
	const MODE_SIGN = 4;  // sign加签模式

	/**
	 * 鉴权的header前缀
	 */
	const AUTH_HEADER_PREFIC = 'AUTH_';

	/**
	 * access token 有效期，默认7200秒（2h）
	 */
	const ACCESS_TOKEN_EXPIRE = 7200;

	private static $appid = '';

	private static $app_key = '';

	private static $token = '';

	// private static $access_token = '';

	private static $sign = '';

	private static $mode = self::MODE_SIMPLE;

	private static $header_prefix = self::AUTH_HEADER_PREFIC;

	private static $_private_key = 'f2Deds8d';

	/**
	 * 初始化设置appid等参数
	 * @param $appid
	 * @param $app_key
	 * @param $mode 鉴权模式，默认MODE_SIMPLE
	 * @param $prefix 自定义header前缀，默认AUTH_HEADER_PREFIC
	 */
	public static function init($appid, $app_key = '', $mode = '', $prefix = ''){
		if(!$appid){
			return false;
		}
		self::$appid = $appid;
		if($app_key){
			self::$app_key = $app_key;
		}
		if($mode){
			self::set_mode($mode);
		}
		if($prefix){
			self::$header_prefix = $prefix;
		}
		return true;
	}

	public static function set_mode($mode){
		if(in_array($mode, [ self::MODE_SIMPLE, self::MODE_TOKEN, self::MODE_ACCESS_TOKEN, self::MODE_SIGN ])){
			self::$mode = $mode;
			return true;
		}
		return false;
	}

	/**
	 * 校验 鉴权
	 *
	 * @return bool 成功返回true，否则返回false
	 */
	public static function check($param = ''){
		if(!self::$appid){
			return false;
		}
		switch (self::$mode) {
			case self::MODE_TOKEN:
				$re = self::check_by_token();
				break;
			case self::MODE_ACCESS_TOKEN:
				$re = self::check_by_access_token($param);
				break;
			case self::MODE_SIGN:
				$re = self::check_by_sign();
				break;
			default:
				$re = self::check_by_simple();
				break;
		}
		return $re;
	}

	/**
	 * 简单模式鉴权校验
	 * 校验 $_SERVER['HTTP_PREFIX_APPID'] === $appid, PREFIX=$header_prefix
	 * 
	 * @return bool
	 */
	private static function check_by_simple(){
		$header_name = 'HTTP_'. strtoupper(self::$header_prefix) . 'APPID';
		if(isset($_SERVER[ $header_name ]) && $_SERVER[ $header_name ] === self::$appid){
			return true;
		}
		return false;
	}

	/**
	 * 根据appid和app_key取得一个32位的固定token
	 *
	 * @return string 相同的参数返回的token相同
	 */
	public static function get_token(){
		if(!self::$appid || !self::$app_key){
			return false;
		}
		self::$token = md5( self::$appid . self::$_private_key . self::$app_key );
		return self::$token;
	}

	/**
	 * TOKEN模式鉴权
	 * 校验 $_SERVER['HTTP_PREFIX_APPID'] === $appid &&
	 *      $_SERVER['HTTP_PREFIX_TOKEN'] === $token, PREFIX=$header_prefix
	 *
	 * @return bool
	 */
	private static function check_by_token(){
		if(!self::check_by_simple()){
			return false;
		}
		$header_name = 'HTTP_'. strtoupper(self::$header_prefix) . 'TOKEN';
		if(isset($_SERVER[ $header_name ]) && $_SERVER[ $header_name ] === self::get_token()){
			return true;
		}
		return false;
	}

	/**
	 * 根据appid和app_key取得一个32位的有时效的access token，并保持在缓存文件里
	 * 无论access token是否过期，都会刷新
	 *
	 * @param int $expire access token有效期(秒)
	 *
	 * @return string|false
	 */
	public static function create_access_token($expire = self::ACCESS_TOKEN_EXPIRE){
		if(!is_int($expire) || $expire <= 0){
			$expire = self::ACCESS_TOKEN_EXPIRE;
		}
		if(!self::$appid || !self::$app_key){
			return false;
		}
		$cache_key = 'apiauth'.md5(self::$appid);
		// 生成随机token
		$access_token = self::get_rand_string( self::$appid . self::$_private_key . self::$app_key );

		if(Cache::set( $cache_key, $access_token, $expire )){
			return $access_token;
		}

		return false;
	}

	/**
	 * 返回access token
	 *
	 * @return string|null 如果没过期，则返回string，已过期则返回null
	 */
	private static function get_access_token(){
		if(!self::$appid){
			return false;
		}
		$cache_key = 'apiauth'.md5(self::$appid);
		return Cache::get($cache_key);
	}

	/**
	 * Access token模式鉴权
	 *
	 * @param string $access_token 待校验的access token
	 * 
	 * @return bool
	 */
	private static function check_by_access_token($access_token){
		$_access_token = self::get_access_token();
		if(!$_access_token){
			return false;
		}
		return $_access_token === $access_token;
	}

	/**
	 * sign加签模式鉴权
	 * 
	 */
	private static function check_by_sign(){

	}

	/**
	 * 生成32位不重复随机字符串
	 * 当需要循环的次数在百万以内时，重复概率低
	 * @api 调用示例
	 *
	 *		var_dump(get_rand_string('attachment_'));
	 *		# => string(32) "a7e0d3baf3940bcaaf70833ba852de61"
	 * @author lan
	 */
	private static function get_rand_string($prefix = ''){
	    return md5($prefix . microtime() . mt_rand());
	}
}