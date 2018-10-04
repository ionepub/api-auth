# api接口鉴权 - Api Auth

提供：

- [x] 简单鉴权，校验appid - `Auth::MODE_SIMPLE`
- [x] token鉴权，校验appid和token - `Auth::MODE_TOKEN`
- [x] access token鉴权，校验access token - `Auth::MODE_ACCESS_TOKEN`
- [ ] sign加签鉴权 - `Auth::MODE_SIGN`

## 概念说明

- `appid`

类似微信公众平台的appid，分配给一个系统或者一个用户的应用ID，单个系统或者用户、设备应该保证唯一

- `app_key`

类型微信公众平台的应用key，对应每个appid应保证唯一，可用于获取access token等关键内容，应确保不外泄

- `header prefix`

在一些模式下，对接口的校验是通过header中的自定义头部信息的校验来完成的，而prefix是对自定义头的前缀的设置，默认为`Auth::AUTH_HEADER_PREFIC`

- `token`

在token模式下，对接口的校验需要一个token，这个token是通过appid、app_key来生成的，固定不变

- `access token`

在access token模式下，可以根据appid、app_key生成一个有时效的access token

- `expire`

针对access token模式，设置access token的有效时间，秒为单位，默认为`Auth::ACCESS_TOKEN_EXPIRE` (7200秒)


## install

```
	"repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/ionepub/api-auth.git"
        }
    ],
    "require": {
        "ionepub/api-auth": "1.0.0"
    }
```

## simple usage

```
use Ionepub\Auth;

$appid = '123456';
$app_key = 'adadsad';

Auth::init($appid, $app_key, Auth::MODE_SIMPLE);

var_dump(Auth::check());
```

curl：

```
curl -H "auth-appid:123456" localhost
```

## 简单鉴权模式

简单鉴权模式校验 $_SERVER['HTTP_PREFIX_APPID'] 是否等于 appid。

请求：

```
curl -H "auth-appid:123456" localhost
```

> 说明：`-H`将在请求加上自定义的header `auth-appid` (忽略大小写)，值为`123456`

校验代码：

```
// 初始化
Auth::init($appid, $app_key, Auth::MODE_SIMPLE);

// 校验 $_SERVER['HTTP_PREFIX_APPID'] === $appid, PREFIX默认为AUTH_
var_dump(Auth::check());
```

简单鉴权模式只校验appid的值，app_key的值可忽略。

## token鉴权模式

这种模式下将校验 $_SERVER['HTTP_PREFIX_APPID'] 是否等于 appid，同时校验 $_SERVER['HTTP_PREFIX_TOKEN'] 是否等于 token。

token的值随着appid和app_key的改变而改变，当appid和app_key值固定时，token值固定。

**初始化**

```
Auth::init($appid, $app_key, Auth::MODE_TOKEN);
# or
Auth::init($appid, $app_key, Auth::MODE_SIMPLE);
Auth::set_mode(Auth::MODE_TOKEN);
```

**获取token**

```
$token = Auth::get_token();
var_dump($token); # token = aa4d11eb7a0d7846e4d37d1782bcbb2d
```

**请求**

```
curl -H "auth-appid:123456" -H "auth-token:aa4d11eb7a0d7846e4d37d1782bcbb2d" localhost
```

**校验**

```
var_dump(Auth::check());
```

## access token鉴权

这种模式下将校验access token是否有效

**初始化**

```
Auth::init($appid, $app_key, Auth::MODE_ACCESS_TOKEN);
# or
Auth::init($appid, $app_key, Auth::MODE_SIMPLE);
Auth::set_mode(Auth::MODE_ACCESS_TOKEN);
```

**获取access token**

```
$expire = 60; // 有效期60秒，默认有效期 Auth::ACCESS_TOKEN_EXPIRE = 7200
$access_token = Auth::create_access_token($expire);
var_dump($access_token);
```

**校验**

```
$access_token = $_GET['access_token']; // or other method
var_dump(Auth::check($access_token));
```

## sign加签校验模式

todo

## functions

### Auth::init($appid, $app_key = '', $mode = '', $prefix = '')

- $mode 模式，可选：`Auth::MODE_SIMPLE`, `Auth::MODE_TOKEN`, `Auth::MODE_ACCESS_TOKEN`, `Auth::MODE_SIGN`
- $prefix header前缀，默认`Auth::AUTH_HEADER_PREFIC` (Auth_)

### Auth::set_mode($mode)

设置鉴权模式

- $mode 模式，可选：`Auth::MODE_SIMPLE`, `Auth::MODE_TOKEN`, `Auth::MODE_ACCESS_TOKEN`, `Auth::MODE_SIGN`

### Auth::check($param = '')

校验主方法

- $param access_token模式下，需要传递一个$access_token参数，其他模式不需要传参

### Auth::get_token()

token模式下，取得token的值

### Auth::create_access_token($expire = Auth::ACCESS_TOKEN_EXPIRE)

access token模式下，创建一个access token

- $expire 有效期，默认`Auth::ACCESS_TOKEN_EXPIRE` (7200秒)

