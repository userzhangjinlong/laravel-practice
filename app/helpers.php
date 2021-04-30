<?php

use App\Exceptions\ApiException;
//use App\Handler\Facade\OssFacade;
use App\Utils\CodeMsgUtil;


/**
 * 获取IP
 * @return array|false|string
 */
function getIp()
{
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else {
            if (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
    }
    return $realip;
}

/**
 * 页面跳转
 *
 * @param array $newArray
 * @param string $url
 */
function JumpUrl($newArray = [], $url = '')
{
    $topString = "<html><body><form style='display:none;' id='form1' name='form1' method='post' action='" . $url . "'>";
    $centerString = "";
    foreach ($newArray as $key => $val) {
        $centerString .= "<input name='" . $key . "' type='text' value='" . $val . "' />";
    }
    $bottomString = "</form></html></body>
            <script type='text/javascript'>function load_submit(){document.form1.submit()}load_submit();</script>";
    echo $topString . $centerString . $bottomString;
    exit;
}

/**
 * 生成随机KEY
 *
 * @param int $length
 * @param bool $isEncrypt
 *
 * @return string
 */
function generateUserKey($length = 16, $isEncrypt = true)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $newString = '';
    for ($i = 0; $i < $length; $i++) {
        // 这里提供两种字符获取方式
        // 第一种是使用 substr 截取$chars中的任意一位字符；
        // 第二种是取字符数组 $chars 的任意元素
        $newString .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    if ($isEncrypt === true) {
        $newString = md5($newString);
    }
    return $newString;
}

/**
 * 去掉特殊字符串
 *
 * @param string $data
 *
 * @return string
 */
function base64url_encode($data = '')
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * 还原特殊字符串
 *
 * @param string $data
 *
 * @return bool|string
 */
function base64url_decode($data = '')
{
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

/**
 * 解析内链地址 内链
 *
 * @param string $value
 *
 * @return mixed
 */
function resolveInternalChain($value = '')
{
    $valueArray = explode('$', $value);
    return json_decode(base64_decode($valueArray[1]), true);
}

/**
 * 获取内链地址
 *
 * @param int $type 分类
 * @param array $param 参数集合
 *
 * @return string
 */
function getInternalChain($type, $param = [])
{
    $encodeArray = [
        'type' => $type,
        'redirect_param' => $param,
    ];
    return "teacher$" . base64_encode(json_encode($encodeArray));
}

/**
 * 获取txt上传信息
 *
 * @param string $_token
 * @param string $url
 *
 * @return array
 */
function getUploadTxtInfo($_token = '', $url = '/admin/user/push_file_rule?is_regist=2')
{
    return [
        'showPreview' => true,
        'showUpload' => true,
        'initialPreviewFileType' => 'text',
        'language' => 'zh',
        'uploadUrl' => $url,
        'uploadExtraData' => ['_token' => $_token],
        'autoReplace' => true,
        'allowedFileExtensions' => ['txt'],
        'maxFileSize' => 10240,
    ];
}

/**
 * 响应成功返回的函数
 *
 * @param int $status
 * @param array $content
 *
 * @return \Illuminate\Http\JsonResponse
 */
function responseSuccess(int $status = 1000, $content = [])
{
    return (new \App\Handler\SystemHandle\ValidateHandler())->success($status, $content);
}

/**
 * 响应成功并附带平台分享信息
 *
 * @param int $status
 * @param $content
 * @return \Illuminate\Http\JsonResponse
 */
function responseSuccessWithShare(int $status = 1000, $content = [])
{
    return (new \App\Handler\SystemHandle\ValidateShareHandler())->successWithShare($status, $content);
}

/**
 * 响应错误返回的函数
 *
 * @param int $status
 * @param array $content
 *
 * @return \Illuminate\Http\JsonResponse
 */
function responseError(int $status = 4000, $content = [])
{
    return (new \App\Handler\SystemHandle\ValidateHandler())->error($status, $content);
}

/***
 * 上传图片到又拍云
 *
 * @param $file @文件对象
 *
 * @return bool|string
 */
function upImage($file)
{
    $ext = explode('/', $file->getMimeType());
    $name = 'image/' . time() . str_random(5) . '.' . end($ext);
    $storage = Storage::disk(config('admin.upload.disk'))->put($name, file_get_contents($file->getRealPath()));
    if ($storage) {
        return $name;
    }
    return false;
}

/***
 * 上传图片到又拍云
 *
 * @param $file @文件对象
 *
 * @return bool|string
 */
function upCourseCoverImage($file)
{
    $ext = explode('/', $file->getMimeType());
    $name = config('oss.courserDir') . '/' . date('Y-m-d') . '/' . md5(time()) . rand(10000,
            99999) . '.' . end($ext);
    $storage = Storage::disk(config('admin.upload.disk'))->put($name, file_get_contents($file->getRealPath()));
    if ($storage) {
        return $name;
    }
    return false;
}

/**
 * 把时分秒的格式转换成秒数
 * @param string $times
 * @return float|int
 */
function timeToSec($times = '')
{
    $result = 0;

    if (!$times) {
        return $result;
    }

    $arr = explode(':', $times);

    $hour = isset($arr[0]) && $arr[0] ? $arr[0] : 0;
    $result += $hour * 3600;

    $minute = isset($arr[1]) && $arr[1] ? $arr[1] : 0;
    $result += $minute * 60;

    $second = isset($arr[2]) && $arr[2] ? $arr[2] : 0;
    $result += $second;

    return $result;
}

/**
 * 把秒数转换为时分秒的格式
 * @param int $times
 * @return string
 */
function secToTime($times = 0)
{
    $result = '00:00:00';

    if (!$times) {
        return $result;
    }

    if ($times > 0) {
        $hour = floor($times / 3600);
        $hour = $hour < 10 ? '0' . $hour : $hour;

        $minute = floor(($times - 3600 * $hour) / 60);
        $minute = $minute < 10 ? '0' . $minute : $minute;

        $second = floor((($times - 3600 * $hour) - 60 * $minute) % 60);
        $second = $second < 10 ? '0' . $second : $second;

        $result = $hour . ':' . $minute . ':' . $second;
    }

    return $result;
}

/**
 * 处理关键词关联
 * @param string $keywords 逗号分隔的关键词
 * @param int $linkId 关联对象ID
 * @param int $type 关联对象类型1单课2专栏
 *
 * @return bool
 */
function dealKeywordLink($keywords = '', $linkId = 0, $type = 1)
{
    return (new \App\Handler\Common\KeywordHandler())->dealKeywordLink($keywords, $linkId, $type);
}

/***
 * 发送post请求
 *
 * @param       $url
 * @param array $postData
 *
 * @return mixed
 */
function curlPost($url, array $postData)
{
    $response = \Zttp\Zttp::asFormParams()->post($url, $postData);
    return $response->json();
}

/**
 * [httpGuzzle 封装GuzzleHttp,简单调用]
 * @param $curlType
 * @param $url
 * @param $data
 * @param $headers
 * @return mixed
 * @throws \Exception
 */
function httpGuzzle($curlType, $url, $data = [], $headers = [])
{
    $curlType = strtoupper($curlType);
    switch ($curlType) {
        case 'GET':
            $config['query'] = $data;
            break;
        case 'POST':
            $config['form_params'] = $data;
            break;
        case 'PUT':
            $config['form_params'] = $data;
            break;
        default:
            # code...
            break;
    }
    $config['verify'] = false;
    if ($headers) {
        $config['headers'] = $headers;
    }

    $client = new \GuzzleHttp\Client();
    $response = $client->request($curlType, $url, $config);
    $result = json_decode($response->getBody(), true);

    return $result;
}

/**
 * GET 请求
 * @param string $url
 */
function http_get($url){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}

/**
 * POST 请求
 * @param string $url
 * @param array $param
 * @param boolean $post_file 是否文件上传
 * @return string content
 */
function http_post($url,$param,$post_file=false){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (is_string($param) || $post_file) {
        $strPOST = $param;
    } else {
        $aPOST = array();
        foreach($param as $key=>$val){
            $aPOST[] = $key."=".urlencode($val);
        }
        $strPOST =  join("&", $aPOST);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_POST,true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}

/**
 * 获取两个日期间的所有日期
 * @param string $startDate
 * @param string $endDate
 * @param string $format
 * @return array|false|string
 */
function getDatesFromRange($startDate = '', $endDate = '', $format = 'Y-m-d')
{
    if (!$startDate || !$endDate) {
        return [];
    }

    $startTime = strtotime($startDate);
    $endTIme = strtotime($endDate);
    if ($startTime > $endTIme) {
        $temp = $startTime;
        $startTime = $endTIme;
        $endTIme = $temp;
    }
    $dateArr = [];
    while ($startTime <= $endTIme) {
        $dateArr[] = date($format, $startTime);
        $startTime = strtotime('+1 day', $startTime);
    }

    return $dateArr;
}

/**
 * 获取分页数据,学员管理端
 * @param  \Illuminate\Http\Request  $request
 * @return array
 */
function getPageSize()
{
    $currentPage = (int) \Illuminate\Support\Facades\Input::get('current', '1');
    $pageSize    = (int) \Illuminate\Support\Facades\Input::get('page_size', '10');
    $currentPage = $currentPage > 0 ? $currentPage : 1;
    $pageSize    = $pageSize > 0 ? $pageSize : 10;

    return ['current' => $currentPage, 'page_size' => $pageSize];
}

/**
 * 重新组装含分页的数据,学员管理端
 * @param $paginate
 * @param  string  $keyName
 * @return array
 */
function getPaginateData(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginate, $keyName = 'data')
{
    $returnData = [
        'pagination' => [
            'total'     => $paginate->total(),//总条数
            'page_size' => $paginate->perPage(),//每页条数
            'current'   => $paginate->currentPage(),//当前页数
        ],
        $keyName     => $paginate->items(),
    ];

    return $returnData;
}

/**
 * 获取分页数据,学员学习端
 * @param \Illuminate\Http\Request $request
 * @return array
 */
function getStudentPageSize()
{
    $currentPage = (int)\Illuminate\Support\Facades\Input::get('current', '1');
    $pageSize    = (int)\Illuminate\Support\Facades\Input::get('pageSize', '10');
    $currentPage = $currentPage > 0 ? $currentPage : 1;
    $pageSize    = $pageSize > 0 ? $pageSize : 10;

    return ['current' => $currentPage, 'pageSize' => $pageSize];
}

/**
 * 重新组装含分页的数据
 * @param $paginate
 * @param string $keyName
 * @return array
 */
function getStudentPaginateData(array $paginate, $keyName = 'list')
{
    $returnData = [
        'pagination' => [
            'total'     => $paginate['total'],//总条数
            'pageSize'  => $paginate['per_page'],//每页条数
            'current'   => $paginate['current_page'],//当前页数
            'pageTotal' => ceil($paginate['total'] / $paginate['per_page']),//总页数
        ],
        $keyName     => $paginate['data'],
    ];

    return $returnData;
}

/**
 * 组装含分页的数据
 * @param $paginate
 * @param string $keyName
 * @return array
 */
function setStudentPaginateData($total = 0, $pageSize = 0, $current = 0, $list = [], $keyName = 'list')
{
    $returnData = [
        'pagination' => [
            'total'     => $total,//总条数
            'pageSize'  => $pageSize,//每页条数
            'current'   => $current,//当前页数
            'pageTotal' => ceil($total / $pageSize),//总页数
        ],
        $keyName     => $list,
    ];

    return $returnData;
}

/**
 * API异常信息抛出方法
 * @param $code
 * @throws ApiException
 */
function apiException($code)
{
    if (!is_numeric($code)) {
        $code = 4000;
    }
    $msg = CodeMsgUtil::get($code);
    throw new ApiException($msg, $code);
}

/**
 * 浮点数转百分数
 * 不足1按1算；超过100按100算；向下取整；
 * @param int $floor
 * @return string
 */
function floorToPercent($floor = 0)
{
    if (!$floor) {
        return 0;
    }
    if ($floor < 1) {
        return 1;
    }
    if ($floor > 100) {
        return 100;
    }
    return intval($floor);
}

/**
 * 百分百格式化
 * 大于等于1%，向下取整
 * 小于1%取1%
 * @param $molecular int 分子
 * @param $denominator int 分母
 * @return float|int
 */
function percentReformat(int $molecular, int $denominator)
{
    if ($molecular <= 0 || $denominator <= 0) {
        return 0;
    }
    $bc = bcdiv($molecular, $denominator, 6) * 100;
    if ($bc <= 1) {
        return 1;
    }
    if ($bc > 100) {
        return 100;
    }
    return floor($bc);
}

/**
 * 日期转换，秒转时分
 * @param int $floor
 * @return string
 */
function secToHour($sec = 0, $type = 'hour')
{
    $time = 0;
    if (!$sec) {
        return $time;
    }

    switch ($type) {
        case 'hour':
            $time = round($sec / 3600);
            break;
        case 'minute':
            $time = round($sec / 60);
            break;
        default:
            break;
    }

    return $time ?: 1;
}

/**
 * 获取图片完整路径
 * @param string $url
 * @return string
 */
function getImageUrl($url = '', $isAvatar = 1)
{
    //带http和https的直接返回
    if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
        return $url;
    }

    //运营后台头像
    if (strpos($url, 'vendor/laravel-admin/') !== false) {
        return (new \App\Services\CollegeAdmin\Statistics\StatisticsService())->getConfigCollegeInfo('college_api_url') . $url;
    }

    //前缀
    $ossHandler = new \App\Handler\Common\OssHandler();
    $imgPrefix  = $ossHandler->ossImgPrefix();

    //默认头像
    $defaultImg = '';
    if ($isAvatar) {
        $defaultImg = $imgPrefix . config('oss.defaultUser');
    }

    //没有url的返回默认头像
    if (!$url) {
        return $defaultImg;
    }

    //拼接url
    $url = $imgPrefix . $url;

    return $url;
}

/**
 * 处理前端上传的排序表达字符串,切割字符串
 * @return array
 */
function dealOrderData($sorter = '')
{
    $sortArr = [];

    if (empty($sorter)) {
        return $sortArr;
    }
    $str = $sorter;
    $index = strrpos($str, '_');
    if ($index < 1) {
        return $sortArr;
    }

    $key = substr($str, 0, $index);
    $asc = strtoupper(substr($str, $index + 1, 3));
    $sortArr['key'] = $key;
    $sortArr['sort'] = $asc;
    return $sortArr;
}

function get_file_size($num)
{
    $p = 0;
    $format = 'bytes';
    if ($num > 0 && $num < 1024) {
        $p = 0;
        return number_format($num) . ' ' . $format;
    }
    if ($num >= 1024 && $num < pow(1024, 2)) {
        $p = 1;
        $format = 'KB';
    }
    if ($num >= pow(1024, 2) && $num < pow(1024, 3)) {
        $p = 2;
        $format = 'MB';
    }
    if ($num >= pow(1024, 3) && $num < pow(1024, 4)) {
        $p = 3;
        $format = 'GB';
    }
    if ($num >= pow(1024, 4) && $num < pow(1024, 5)) {
        $p = 3;
        $format = 'TB';
    }
    $num /= pow(1024, $p);
    return number_format($num, 3) . ' ' . $format;
}

/**
 * 根据二维数组某个元素进行排序:SORT_ASC升序，SORT_DESC降序
 * @param array $data 数组
 * @param string $field 二维元素key
 * @param int $sort 排序
 *
 * @return array
 */
function dealArraySort($data = [], $field = '', $sort = SORT_ASC)
{
    //先用array_column多维数组按照纵向（列）取出
    $date = array_column($data, $field);
    //再用array_multisort结合array_column得到的结果对$data进行排序
    array_multisort($date, $sort, $data);
    return $data;
}

/**
 * 获取本周时间区间
 * @param int $type 结束时间类型:1本周末,2今天凌晨,3当前时间,4当前整点时间
 * @param null $date 要计算的日期
 * @return array
 */
function getWeekDate($type = 1, $date = null)
{
    $first = 1;
    $tDate = $date ?: date("Y-m-d");
    $w = date('w', strtotime($tDate));
    $weekStart = date('Y-m-d 00:00:00', strtotime("$tDate -" . ($w ? $w - $first : 6) . ' days'));
    switch ($type) {
        case 1:
            $weekend = date('Y-m-d 23:59:59', strtotime("$weekStart +6 days"));
            break;
        case 2:
            $weekend = date('Y-m-d 23:59:59');
            break;
        case 3:
            $weekend = date('Y-m-d H:i:s');
            break;
        case 4:
            $weekend = date('Y-m-d H:00:00');
            break;
        default:
            $weekend = date('Y-m-d 23:59:59', strtotime("$weekStart +6 days"));
            break;

    }

    return [$weekStart, $weekend];
}

function file_url_token($url, $time)
{
    return trim(OssFacade::fileUrlPrefix(), '\/') . OssFacade::OssSecret('/' . trim($url, '\/'), $time);
}

/**
 * 处理字符串长度
 * @param string $str
 * @param int $type 1-昵称（限制4字），2-岗位（限制5字）
 */
function dealStringLength($str = '', $type = 0)
{
    if (!$str || !$type) {
        return $str;
    }

    switch ($type) {
        case 1:
            $length = 4;
            break;
        case 2:
            $length = 5;
            break;
    }

    $str = mb_substr($str, 0, $length);

    return $str;
}

//检查金额小数点处理
function returnPriceDeal($price = 0)
{
    if ($price == intval($price)) {
        $price = intval($price);
    }
    return $price;
}

//获取配置信息
function getSettingConfig($key = '')
{
    return (new \App\Handler\UserHandler())->getSettingConfig($key);
}

//获取advanced_setting
function getAdvancedSetting($key = '')
{
    return (new \App\Handler\AdvanceSettingHandler())->getAdvancedSetting($key);

}

/**
 * 生成加密字符串
 * @param string $str
 * @param string $secret
 * @return string
 */
function opensslEncrypt(string $str, string $secret = '')
{
    return openssl_encrypt($str, 'DES-ECB', $secret);
}

/**
 * 解密字符串
 * @param string $str
 * @param string $secret
 * @return string
 */
function opensslDecrypt(string $str, string $secret = '')
{
    return openssl_decrypt($str, 'DES-ECB', $secret);
}

/**
 * 生成补位编号
 * @param string $value 需要进行补位的id
 * @param int $length 整个字符串长度
 * @return string
 */
function makeFillInNumber($value = '', $length = 8)
{
    $diffValue = $length - strlen($value);
    if ($diffValue > 0) {
        for ($i = 0; $i < $diffValue; $i++) {
            $value = '0' . $value;
        }
    }
    return $value;
}

function getUserInfo($token='')
{
    return (new \App\Handler\Data\UsersHandler())->getUserInfo($token);
}

/**
 * 获取客户端浏览器信息
 * @param   null
 * @author  https://blog.jjonline.cn/phptech/168.html
 * @return  string
 */
function get_broswer()
{
    $sys = isset($_SERVER['HTTP_USER_AGENT']) ?  $_SERVER['HTTP_USER_AGENT']: "";  //获取用户代理字符串
    if (stripos($sys, "Firefox/") > 0) {
        preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
        $exp[0] = "Firefox";
        $exp[1] = $b[1];    //获取火狐浏览器的版本号
    } elseif (stripos($sys, "Maxthon") > 0) {
        preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
        $exp[0] = "傲游";
        $exp[1] = $aoyou[1];
    } elseif (stripos($sys, "MSIE") > 0) {
        preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
        $exp[0] = "IE";
        $exp[1] = $ie[1];  //获取IE的版本号
    } elseif (stripos($sys, "OPR") > 0) {
        preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
        $exp[0] = "Opera";
        $exp[1] = $opera[1];
    } elseif (stripos($sys, "Edge") > 0) {
        //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
        preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
        $exp[0] = "Edge";
        $exp[1] = $Edge[1];
    } elseif (stripos($sys, "Chrome") > 0) {
        preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
        $exp[0] = "Chrome";
        $exp[1] = $google[1];  //获取google chrome的版本号
    } elseif (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0) {
        preg_match("/rv:([\d\.]+)/", $sys, $IE);
        $exp[0] = "IE";
        $exp[1] = $IE[1];
    } else {
        $exp[0] = "未知浏览器";
        $exp[1] = "";
    }
    return [$exp[0],$exp[1]];
}

function get_os()
{
//    $agent = $_SERVER['HTTP_USER_AGENT'];
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ?  $_SERVER['HTTP_USER_AGENT']: "";  //获取用户代理字符串
    $os = false;

    if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
        $os = 'Windows 95';
    } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
        $os = 'Windows ME';
    } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
        $os = 'Windows 98';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
        $os = 'Windows Vista';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
        $os = 'Windows 7';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
        $os = 'Windows 8';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
        $os = 'Windows 10';#添加win10判断
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
        $os = 'Windows XP';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
        $os = 'Windows 2000';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
        $os = 'Windows NT';
    } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
        $os = 'Windows 32';
    } else if (preg_match('/linux/i', $agent)) {
        $os = 'Linux';
    } else if (preg_match('/unix/i', $agent)) {
        $os = 'Unix';
    } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'SunOS';
    } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'IBM OS/2';
    } else if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent)) {
        $os = 'Macintosh';
    } else if (preg_match('/PowerPC/i', $agent)) {
        $os = 'PowerPC';
    } else if (preg_match('/AIX/i', $agent)) {
        $os = 'AIX';
    } else if (preg_match('/HPUX/i', $agent)) {
        $os = 'HPUX';
    } else if (preg_match('/NetBSD/i', $agent)) {
        $os = 'NetBSD';
    } else if (preg_match('/BSD/i', $agent)) {
        $os = 'BSD';
    } else if (preg_match('/OSF1/i', $agent)) {
        $os = 'OSF1';
    } else if (preg_match('/IRIX/i', $agent)) {
        $os = 'IRIX';
    } else if (preg_match('/FreeBSD/i', $agent)) {
        $os = 'FreeBSD';
    } else if (preg_match('/teleport/i', $agent)) {
        $os = 'teleport';
    } else if (preg_match('/flashget/i', $agent)) {
        $os = 'flashget';
    } else if (preg_match('/webzip/i', $agent)) {
        $os = 'webzip';
    } else if (preg_match('/offline/i', $agent)) {
        $os = 'offline';
    } else {
        $os = '未知操作系统';
    }
    return $os;
}

//加解密 用户唯一标识
function  encryptUserKey($user_id,$key,$iv=""){
    return  base64url_encode(base64_encode(openssl_encrypt($user_id,'AES-128-ECB',$key,OPENSSL_RAW_DATA,$iv)));
}
function decrypeUserKey($crypedData,$key,$iv=""){
    return rtrim(openssl_decrypt(base64_decode($crypedData),'AES-128-ECB',$key,OPENSSL_RAW_DATA,$iv),"\0");
}
//友好时间  默认往前推两天
function friendlyTime($time){
    if($time==(date('Y/m/d',time()))){
        return '今天';
    }
    if($time==(date('Y/m/d',strtotime('-1 day')))){
        return '昨天';
    }
    else{
        return $time;
    }
}
/**
 * 字符串脱敏处理
 * type  1:姓名 2:身份证 3:手机号 4:住址
 */
function hiddingParam($string, $type){
        if (!$string){
            return $string;
        }
        // 处理图形验证码
        $position = strpos($string, "*");
        if ($position == false) {
            if ($type == 1){
                return hiding_sensitive_data($string, 1, 10);
            } elseif ($type == 2) {
                return hiding_sensitive_data($string, 7, 20);
            } elseif ($type == 3) {
                return hiding_sensitive_data($string, 3, 4);
            } elseif ($type == 4) {
                $len = strlen($string);
                if ($len > 12){
                    $begin = 8;
                } else {
                    $begin = 4;
                }
                // 住址
                return hiding_sensitive_data($string, $begin, 128);
            } else {
                throw new \Exception("脱敏类型错误");
            }
        } else {
            return $string;
        }

}

/**
 * 字符串脱敏处理
 */
/**
+----------------------------------------------------------
 * 将一个字符串部分字符用*替代隐藏
+----------------------------------------------------------
 * @param string $string 待转换的字符串
 * @param int  $bengin 起始位置，从0开始计数，当$type=4时，表示左侧保留长度
 * @param int  $len  需要转换成*的字符个数，当$type=4时，表示右侧保留长度
 * @param int  $type  转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；
 *                               3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
 * @param string $glue  分割符
+----------------------------------------------------------
 * @return string 处理后的字符串
+----------------------------------------------------------
 */
if (! function_exists('hiding_sensitive_data')){
    //生成随机数
//    function hiding_sensitive_data($string, $start = 1, $end = 1, $length = 1, $replace = "*")
    function hiding_sensitive_data($string, $bengin = 0, $len = 4, $type = 0,$char="*", $glue = "@") {
        if (empty($string))
            return false;
        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }
        if ($type == 0) {
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = $char;
            }
            $string = implode("", $array);
        } else if ($type == 1) {
            $array = array_reverse($array);
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = $char;
            }
            $string = implode("", array_reverse($array));
        } else if ($type == 2) {
            $array = explode($glue, $string);
            $array[0] = hiding_sensitive_data($array[0], $bengin, $len, 1);
            $string = implode($glue, $array);
        } else if ($type == 3) {
            $array = explode($glue, $string);
            $array[1] = hiding_sensitive_data($array[1], $bengin, $len, 0);
            $string = implode($glue, $array);
        } else if ($type == 4) {
            /* $left = $bengin;
             $right = $len;
             $tem = array();
             for ($i = 0; $i < ($len - $right); $i++) {
                 if (isset($array[$i]))
                     $tem[] = $i >= $left ? $char : $array[$i];
             }
             $array = array_chunk(array_reverse($array), $right);
             $array = array_reverse($array[0]);
             for ($i = 0; $i < $right; $i++) {
                 $tem[] = $array[$i];
             }
             $string = implode("", $tem);*/
            //需要替换的下标数组
            $arr_count=count($array);
            $left=$bengin;
            $right=$arr_count-$len;
            for ($i = 0; $i < $arr_count; $i++) {
                if (isset($array[$i])  &&  $i>=$left  &&  $i <$right){
                    $array[$i]="*";
                }
            }
            $string = implode("", $array);
        }
        return $string;
    }
}
