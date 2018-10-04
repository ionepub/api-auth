<?php 
namespace Ionepub;
use Desarrolla2\Cache\Adapter\File as FileCache;

/**
* cache for auth
*/
class Cache
{
	public static function set($key, $value, $expire = 0){
		try {
			$cache = new FileCache(sys_get_temp_dir() . '/authcache');

			$cache->set($key, $value, $expire);

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	public static function get($key){
		$cache = new FileCache(sys_get_temp_dir() . '/authcache');
		return $cache->get($key);
	}
}