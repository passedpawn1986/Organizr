<?php
// Print output all purrty
function prettyPrint($v)
{
	$trace = debug_backtrace()[0];
	echo '<pre style="white-space: pre; text-overflow: ellipsis; overflow: hidden; background-color: #f2f2f2; border: 2px solid black; border-radius: 5px; padding: 5px; margin: 5px;">' . $trace['file'] . ':' . $trace['line'] . ' ' . gettype($v) . "\n\n" . print_r($v, 1) . '</pre><br/>';
}

// Clean Directory string
function cleanDirectory($path)
{
	$path = str_replace(array('/', '\\'), '/', $path);
	if (substr($path, -1) != '/') {
		$path = $path . '/';
	}
	if ($path[0] != '/' && $path[1] != ':') {
		$path = '/' . $path;
	}
	return $path;
}

// Get Gravatar Email Image
function gravatar($email = '')
{
	$email = md5(strtolower(trim($email)));
	$gravurl = "https://www.gravatar.com/avatar/$email?s=100&d=mm";
	return $gravurl;
}

function parseDomain($value, $force = false)
{
	$badDomains = array('ddns.net', 'ddnsking.com', '3utilities.com', 'bounceme.net', 'duckdns.org', 'freedynamicdns.net', 'freedynamicdns.org', 'gotdns.ch', 'hopto.org', 'myddns.me', 'myds.me', 'myftp.biz', 'myftp.org', 'myvnc.com', 'noip.com', 'onthewifi.com', 'redirectme.net', 'serveblog.net', 'servecounterstrike.com', 'serveftp.com', 'servegame.com', 'servehalflife.com', 'servehttp.com', 'serveirc.com', 'serveminecraft.net', 'servemp3.com', 'servepics.com', 'servequake.com', 'sytes.net', 'viewdns.net', 'webhop.me', 'zapto.org');
	$Domain = $value;
	$Port = strpos($Domain, ':');
	if ($Port !== false) {
		$Domain = substr($Domain, 0, $Port);
	}
	$check = substr_count($Domain, '.');
	if ($check >= 3) {
		if (is_numeric($Domain[0])) {
			$Domain = '';
		} else {
			if (in_array(strtolower(explode('.', $Domain)[2] . '.' . explode('.', $Domain)[3]), $badDomains)) {
				$Domain = '.' . explode('.', $Domain)[0] . '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2] . '.' . explode('.', $Domain)[3];
			} else {
				$Domain = '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2] . '.' . explode('.', $Domain)[3];
			}
		}
	} elseif ($check == 2) {
		if (in_array(strtolower(explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2]), $badDomains)) {
			$Domain = '.' . explode('.', $Domain)[0] . '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2];
		} elseif (explode('.', $Domain)[0] == 'www') {
			$Domain = '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2];
		} elseif (explode('.', $Domain)[1] == 'co') {
			$Domain = '.' . explode('.', $Domain)[0] . '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2];
		} else {
			$Domain = '.' . explode('.', $Domain)[1] . '.' . explode('.', $Domain)[2];
		}
	} elseif ($check == 1) {
		$Domain = '.' . $Domain;
	} else {
		$Domain = '';
	}
	/*
	if (is_numeric($Domain[0]) || strpos($Domain, '.') == false) {
		$Domain = '';
	} else {
		if (substr($Domain, 0, 3) == 'www') {
			$Domain = substr($Domain, 3, strlen($Domain) - 3);
		} else {
			$Domain = '.' . $Domain;
		}
	}
	*/
	return ($force) ? $value : $Domain;
}

// Cookie Custom Function
function coookie($type, $name, $value = '', $days = -1, $http = true)
{
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https") {
		$Secure = true;
		$HTTPOnly = true;
	} elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
		$Secure = true;
		$HTTPOnly = true;
	} else {
		$Secure = false;
		$HTTPOnly = false;
	}
	if (!$http) {
		$HTTPOnly = false;
	}
	$Path = '/';
	$Domain = parseDomain($_SERVER['HTTP_HOST']);
	$DomainTest = parseDomain($_SERVER['HTTP_HOST'], true);
	if ($type == 'set') {
		$_COOKIE[$name] = $value;
		header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
			. (empty($days) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', time() + (86400 * $days)) . ' GMT')
			. (empty($Path) ? '' : '; path=' . $Path)
			. (empty($Domain) ? '' : '; domain=' . $Domain)
			. (!$Secure ? '' : '; secure')
			. (!$HTTPOnly ? '' : '; HttpOnly'), false);
		header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
			. (empty($days) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', time() + (86400 * $days)) . ' GMT')
			. (empty($Path) ? '' : '; path=' . $Path)
			. (empty($Domain) ? '' : '; domain=' . $DomainTest)
			. (!$Secure ? '' : '; secure')
			. (!$HTTPOnly ? '' : '; HttpOnly'), false);
	} elseif ($type == 'delete') {
		unset($_COOKIE[$name]);
		header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
			. (empty($days) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', time() - 3600) . ' GMT')
			. (empty($Path) ? '' : '; path=' . $Path)
			. (empty($Domain) ? '' : '; domain=' . $Domain)
			. (!$Secure ? '' : '; secure')
			. (!$HTTPOnly ? '' : '; HttpOnly'), false);
		header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
			. (empty($days) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', time() - 3600) . ' GMT')
			. (empty($Path) ? '' : '; path=' . $Path)
			. (empty($Domain) ? '' : '; domain=' . $DomainTest)
			. (!$Secure ? '' : '; secure')
			. (!$HTTPOnly ? '' : '; HttpOnly'), false);
	}
}

function getOS()
{
	if (PHP_SHLIB_SUFFIX == "dll") {
		return "win";
	} else {
		return "*nix";
	}
}

if (!function_exists('getallheaders')) {
	function getallheaders()
	{
		$headers = array();
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}
function random_ascii_string($length)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

// Generate Random string
function randString($length = 10, $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
	$tmp = '';
	for ($i = 0; $i < $length; $i++) {
		$tmp .= substr(str_shuffle($chars), 0, 1);
	}
	return $tmp;
}

function encrypt($password, $key = null)
{
	$key = (isset($GLOBALS['organizrHash'])) ? $GLOBALS['organizrHash'] : $key;
	return openssl_encrypt($password, 'AES-256-CBC', $key, 0, fillString($key, 16));
}

function decrypt($password, $key = null)
{
	if (empty($password)) {
		return '';
	}
	$key = (isset($GLOBALS['organizrHash'])) ? $GLOBALS['organizrHash'] : $key;
	return openssl_decrypt($password, 'AES-256-CBC', $key, 0, fillString($key, 16));
}

function fillString($string, $length)
{
	$filler = '0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*';
	if (strlen($string) < $length) {
		$diff = $length - strlen($string);
		$filler = substr($filler, 0, $diff);
		return $string . $filler;
	} elseif (strlen($string) > $length) {
		return substr($string, 0, $length);
	} else {
		return $string;
	}
}

function userIP()
{
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	} elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	} elseif (isset($_SERVER['HTTP_FORWARDED'])) {
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	} elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	} else {
		$ipaddress = 'UNKNOWN';
	}
	if (strpos($ipaddress, ',') !== false) {
		list($first, $last) = explode(",", $ipaddress);
		unset($last);
		return $first;
	} else {
		return $ipaddress;
	}
}

function arrayIP($string)
{
	if (strpos($string, ',') !== false) {
		$result = explode(",", $string);
	} else {
		$result = array($string);
	}
	foreach ($result as &$ip) {
		$ip = is_numeric(substr($ip, 0, 1)) ? $ip : gethostbyname($ip);
	}
	return $result;
}

function getCert()
{
	$url = 'http://curl.haxx.se/ca/cacert.pem';
	$file = __DIR__ . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . 'cacert.pem';
	if (!file_exists($file)) {
		file_put_contents($file, fopen($url, 'r'));
	} elseif (file_exists($file) && time() - 2592000 > filemtime($file)) {
		file_put_contents($file, fopen($url, 'r'));
	}
	return $file;
}

function curl($curl, $url, $headers = array(), $data = array())
{
	// Initiate cURL
	$curlReq = curl_init($url);
	if (in_array(trim(strtoupper($curl)), ["GET", "POST", "PUT", "DELETE"])) {
		curl_setopt($curlReq, CURLOPT_CUSTOMREQUEST, trim(strtoupper($curl)));
	} else {
		return null;
	}
	curl_setopt($curlReq, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curlReq, CURLOPT_CAINFO, getCert());
	curl_setopt($curlReq, CURLOPT_CONNECTTIMEOUT, 5);
	if (localURL($url)) {
		curl_setopt($curlReq, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curlReq, CURLOPT_SSL_VERIFYPEER, 0);
	}
	// Format Headers
	$cHeaders = array();
	foreach ($headers as $k => $v) {
		$cHeaders[] = $k . ': ' . $v;
	}
	if (count($cHeaders)) {
		curl_setopt($curlReq, CURLOPT_HTTPHEADER, $cHeaders);
	}
	// Format Data
	switch (isset($headers['Content-Type']) ? $headers['Content-Type'] : '') {
		case 'application/json':
			curl_setopt($curlReq, CURLOPT_POSTFIELDS, json_encode($data));
			break;
		case 'application/x-www-form-urlencoded':
			curl_setopt($curlReq, CURLOPT_POSTFIELDS, http_build_query($data));
			break;
		default:
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
			curl_setopt($curlReq, CURLOPT_POSTFIELDS, http_build_query($data));
	}
	// Execute
	$result = curl_exec($curlReq);
	$httpcode = curl_getinfo($curlReq);
	// Close
	curl_close($curlReq);
	// Return
	return array('content' => $result, 'http_code' => $httpcode);
}

function getHeaders($url)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
	curl_setopt($ch, CURLOPT_CAINFO, getCert());
	if (localURL($url)) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	}
	curl_exec($ch);
	$headers = curl_getinfo($ch);
	curl_close($ch);
	return $headers;
}

function download($url, $path)
{
	ini_set('max_execution_time', 0);
	set_time_limit(0);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_CAINFO, getCert());
	if (localURL($url)) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	}
	$raw_file_data = curl_exec($ch);
	curl_close($ch);
	file_put_contents($path, $raw_file_data);
	return (filesize($path) > 0) ? true : false;
}

function localURL($url)
{
	if (strpos($url, 'https') !== false) {
		preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $url, $result);
		$result = (!empty($result) ? true : false);
		return $result;
	}
	return false;
}

function array_filter_key(array $array, $callback)
{
	$matchedKeys = array_filter(array_keys($array), $callback);
	return array_intersect_key($array, array_flip($matchedKeys));
}

// Qualify URL
function qualifyURL($url, $return = false)
{
	//local address?
	if (substr($url, 0, 1) == "/") {
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$protocol = "https://";
		} else {
			$protocol = "http://";
		}
		$url = $protocol . getServer() . $url;
	}
	// Get Digest
	$digest = parse_url($url);
	// http/https
	if (!isset($digest['scheme'])) {
		if (isset($digest['port']) && in_array($digest['port'], array(80, 8080, 8096, 32400, 7878, 8989, 8182, 8081, 6789))) {
			$scheme = 'http';
		} else {
			$scheme = 'https';
		}
	} else {
		$scheme = $digest['scheme'];
	}
	// Host
	$host = (isset($digest['host']) ? $digest['host'] : '');
	// Port
	$port = (isset($digest['port']) ? ':' . $digest['port'] : '');
	// Path
	$path = (isset($digest['path']) && $digest['path'] !== '/' ? $digest['path'] : '');
	// Output
	$array = array(
		'scheme' => $scheme,
		'host' => $host,
		'port' => $port,
		'path' => $path
	);
	return ($return) ? $array : $scheme . '://' . $host . $port . $path;
}

function getServerPath($over = false)
{
	if ($over) {
		if ($GLOBALS['PHPMAILER-domain'] !== '') {
			return $GLOBALS['PHPMAILER-domain'];
		}
	}
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https") {
		$protocol = "https://";
	} elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
		$protocol = "https://";
	} else {
		$protocol = "http://";
	}
	$domain = '';
	if (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], '.') !== false) {
		$domain = $_SERVER['SERVER_NAME'];
	} elseif (isset($_SERVER['HTTP_HOST'])) {
		if (strpos($_SERVER['HTTP_HOST'], ':') !== false) {
			$domain = explode(':', $_SERVER['HTTP_HOST'])[0];
			$port = explode(':', $_SERVER['HTTP_HOST'])[1];
			if ($port !== "80" && $port !== "443") {
				$domain = $_SERVER['HTTP_HOST'];
			}
		} else {
			$domain = $_SERVER['HTTP_HOST'];
		}
	}
	$url = $protocol . $domain . str_replace("\\", "/", dirname($_SERVER['REQUEST_URI']));
	if (strpos($url, '/api') !== false) {
		$url = explode('/api', $url);
		return $url[0] . '/';
	} else {
		return $url;
	}
}

function get_browser_name()
{
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) {
		return 'Opera';
	} elseif (strpos($user_agent, 'Edge')) {
		return 'Edge';
	} elseif (strpos($user_agent, 'Chrome')) {
		return 'Chrome';
	} elseif (strpos($user_agent, 'Safari')) {
		return 'Safari';
	} elseif (strpos($user_agent, 'Firefox')) {
		return 'Firefox';
	} elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) {
		return 'Internet Explorer';
	}
	return 'Other';
}

function getServer($over = false)
{
	if ($over) {
		if ($GLOBALS['PHPMAILER-domain'] !== '') {
			return $GLOBALS['PHPMAILER-domain'];
		}
	}
	return isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"];
}

/* Function is to get all the contents from ics and explode all the datas according to the events and its sections */
function getIcsEventsAsArray($file)
{
	$icalString = file_get_contents_curl($file);
	$icsDates = array();
	/* Explode the ICs Data to get datas as array according to string ‘BEGIN:’ */
	$icsData = explode("BEGIN:", $icalString);
	/* Iterating the icsData value to make all the start end dates as sub array */
	foreach ($icsData as $key => $value) {
		$icsDatesMeta [$key] = explode("\n", $value);
	}
	/* Itearting the Ics Meta Value */
	foreach ($icsDatesMeta as $key => $value) {
		foreach ($value as $subKey => $subValue) {
			/* to get ics events in proper order */
			$icsDates = getICSDates($key, $subKey, $subValue, $icsDates);
		}
	}
	return $icsDates;
}

/* funcion is to avaid the elements wich is not having the proper start, end  and summary informations */
function getICSDates($key, $subKey, $subValue, $icsDates)
{
	if ($key != 0 && $subKey == 0) {
		$icsDates [$key] ["BEGIN"] = $subValue;
	} else {
		$subValueArr = explode(":", $subValue, 2);
		if (isset ($subValueArr [1])) {
			$icsDates [$key] [$subValueArr [0]] = $subValueArr [1];
		}
	}
	return $icsDates;
}

function file_get_contents_curl($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function getExtension($string)
{
	return preg_replace("#(.+)?\.(\w+)(\?.+)?#", "$2", $string);
}