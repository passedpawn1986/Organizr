<?php

trait OrganizrFunctions
{
	public function embyJoinAPI($array)
	{
		$username = ($array['username']) ?? null;
		$email = ($array['email']) ?? null;
		$password = ($array['password']) ?? null;
		if (!$username) {
			$this->setAPIResponse('error', 'Username not supplied', 422);
			return false;
		}
		if (!$email) {
			$this->setAPIResponse('error', 'Email not supplied', 422);
			return false;
		}
		if (!$password) {
			$this->setAPIResponse('error', 'Password not supplied', 422);
			return false;
		}
		return $this->embyJoin($username, $email, $password);
	}
	
	public function embyJoin($username, $email, $password)
	{
		try {
			#create user in emby.
			$headers = array(
				"Accept" => "application/json"
			);
			$data = array();
			$url = $this->config['embyURL'] . '/emby/Users/New?name=' . $username . '&api_key=' . $this->config['embyToken'];
			$response = Requests::Post($url, $headers, json_encode($data), array());
			$response = $response->body;
			//return($response);
			$response = json_decode($response, true);
			//return($response);
			$userID = $response["Id"];
			//return($userID);
			#authenticate as user to update password.
			//randomizer four digits of DeviceId
			// I dont think ther would be security problems with hardcoding deviceID but randomizing it would mitigate any issue.
			$deviceIdSeceret = rand(0, 9) . "" . rand(0, 9) . "" . rand(0, 9) . "" . rand(0, 9);
			//hardcoded device id with the first three digits random 0-9,0-9,0-9,0-9
			$embyAuthHeader = 'MediaBrowser Client="Emby Mobile", Device="Firefox", DeviceId="' . $deviceIdSeceret . 'aWxssS81LgAggFdpbmRvd3MgTlQgMTAuMDsgV2luNjxx7IHf2NCkgQXBwbGVXZWJLaXQvNTM3LjM2IChLSFRNTCwgbGlrZSBHZWNrbykgQ2hyb21lLzcyLjAuMzYyNi4xMTkgU2FmYXJpLzUzNy4zNnwxNTUxNTczMTAyNDI4", Version="4.0.2.0"';
			$headers = array(
				"Accept" => "application/json",
				"Content-Type" => "application/json",
				"X-Emby-Authorization" => $embyAuthHeader
			);
			$data = array(
				"Pw" => "",
				"Username" => $username
			);
			$url = $this->config['embyURL'] . '/emby/Users/AuthenticateByName';
			$response = Requests::Post($url, $headers, json_encode($data), array());
			$response = $response->body;
			$response = json_decode($response, true);
			$userToken = $response["AccessToken"];
			#update password
			$embyAuthHeader = 'MediaBrowser Client="Emby Mobile", Device="Firefox", Token="' . $userToken . '", DeviceId="' . $deviceIdSeceret . 'aWxssS81LgAggFdpbmRvd3MgTlQgMTAuMDsgV2luNjxx7IHf2NCkgQXBwbGVXZWJLaXQvNTM3LjM2IChLSFRNTCwgbGlrZSBHZWNrbykgQ2hyb21lLzcyLjAuMzYyNi4xMTkgU2FmYXJpLzUzNy4zNnwxNTUxNTczMTAyNDI4", Version="4.0.2.0"';
			$headers = array(
				"Accept" => "application/json",
				"Content-Type" => "application/json",
				"X-Emby-Authorization" => $embyAuthHeader
			);
			$data = array(
				"CurrentPw" => "",
				"NewPw" => $password,
				"Id" => $userID
			);
			$url = $this->config['embyURL'] . '/emby/Users/' . $userID . '/Password';
			Requests::Post($url, $headers, json_encode($data), array());
			#update config
			$headers = array(
				"Accept" => "application/json",
				"Content-Type" => "application/json"
			);
			$url = $this->config['embyURL'] . '/emby/Users/' . $userID . '/Policy?api_key=' . $this->config['embyToken'];
			$response = Requests::Post($url, $headers, $this->getEmbyTemplateUserJson(), array());
			#add emby.media
			try {
				#seperate because this is not required
				$headers = array(
					"Accept" => "application/json",
					"X-Emby-Authorization" => $embyAuthHeader
				);
				$data = array(
					"ConnectUsername " => $email
				);
				$url = $this->config['embyURL'] . '/emby/Users/' . $userID . '/Connect/Link';
				Requests::Post($url, $headers, json_encode($data), array());
			} catch (Requests_Exception $e) {
				$this->writeLog('error', 'Emby Connect Function - Error: ' . $e->getMessage(), 'SYSTEM');
				$this->setAPIResponse('error', $e->getMessage(), 500);
				return false;
			}
			$this->setAPIResponse('success', 'User has joined Emby', 200);
			return true;
		} catch (Requests_Exception $e) {
			$this->writeLog('error', 'Emby create Function - Error: ' . $e->getMessage(), 'SYSTEM');
			$this->setAPIResponse('error', $e->getMessage(), 500);
			return false;
		}
	}
	
	/*loads users from emby and returns a correctly formated policy for a new user.
	*/
	public function getEmbyTemplateUserJson()
	{
		$headers = array(
			"Accept" => "application/json"
		);
		$data = array();
		$url = $this->config['embyURL'] . '/emby/Users?api_key=' . $this->config['embyToken'];
		$response = Requests::Get($url, $headers, array());
		$response = $response->body;
		$response = json_decode($response, true);
		//error_Log("response ".json_encode($response));
		$this->writeLog('error', 'userList:' . json_encode($response), 'SYSTEM');
		//$correct stores the template users object
		$correct = null;
		foreach ($response as $element) {
			if ($element['Name'] == $this->config['INVITES-EmbyTemplate']) {
				$correct = $element;
			}
		}
		$this->writeLog('error', 'Correct user:' . json_encode($correct), 'SYSTEM');
		if ($correct == null) {
			//return empty JSON if user incorrectly configured template
			return "{}";
		}
		//select policy section and remove possibly dangerous rows.
		$policy = $correct['Policy'];
		//writeLog('error', 'policy update'.$policy, 'SYSTEM');
		unset($policy['AuthenticationProviderId']);
		unset($policy['InvalidLoginAttemptCount']);
		unset($policy['DisablePremiumFeatures']);
		unset($policy['DisablePremiumFeatures']);
		return (json_encode($policy));
	}
	
	public function checkHostPrefix($s)
	{
		if (empty($s)) {
			return $s;
		}
		return (substr($s, -1, 1) == '\\') ? $s : $s . '\\';
	}
	
	public function approvedFileExtension($filename)
	{
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		switch ($ext) {
			case 'gif':
			case 'png':
			case 'jpeg':
			case 'jpg':
			case 'svg':
				return true;
				break;
			default:
				return false;
		}
	}
	
	public function getImages()
	{
		$allIconsPrep = array();
		$allIcons = array();
		$ignore = array(".", "..", "._.DS_Store", ".DS_Store", ".pydio_id", "index.html");
		$dirname = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'tabs' . DIRECTORY_SEPARATOR;
		$path = 'plugins/images/tabs/';
		$images = scandir($dirname);
		foreach ($images as $image) {
			if (!in_array($image, $ignore)) {
				$allIconsPrep[$image] = array(
					'path' => $path,
					'name' => $image
				);
			}
		}
		$dirname = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'userTabs' . DIRECTORY_SEPARATOR;
		$path = 'plugins/images/userTabs/';
		$images = scandir($dirname);
		foreach ($images as $image) {
			if (!in_array($image, $ignore)) {
				$allIconsPrep[$image] = array(
					'path' => $path,
					'name' => $image
				);
			}
		}
		ksort($allIconsPrep);
		foreach ($allIconsPrep as $item) {
			$allIcons[] = $item['path'] . $item['name'];
		}
		return $allIcons;
	}
	
	public function imageSelect($form)
	{
		$i = 1;
		$images = $this->getImages();
		$return = '<select class="form-control tabIconImageList" id="' . $form . '-chooseImage" name="chooseImage"><option lang="en">Select or type Icon</option>';
		foreach ($images as $image) {
			$i++;
			$return .= '<option value="' . $image . '">' . basename($image) . '</option>';
		}
		return $return . '</select>';
	}
	
	public function getThemes()
	{
		$themes = array();
		foreach (glob(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . "*.css") as $filename) {
			$themes[] = array(
				'name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($filename)),
				'value' => preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($filename))
			);
		}
		return $themes;
	}
	
	public function getSounds()
	{
		$sounds = array();
		foreach (glob(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'sounds' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . "*.mp3") as $filename) {
			$sounds[] = array(
				'name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($filename)),
				'value' => preg_replace('/\\.[^.\\s]{3,4}$/', '', 'plugins/sounds/default/' . basename($filename) . '.mp3')
			);
		}
		foreach (glob(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'sounds' . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . "*.mp3") as $filename) {
			$sounds[] = array(
				'name' => preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($filename)),
				'value' => preg_replace('/\\.[^.\\s]{3,4}$/', '', 'plugins/sounds/custom/' . basename($filename) . '.mp3')
			);
		}
		return $sounds;
	}
	
	public function getBranches()
	{
		return array(
			array(
				'name' => 'Develop',
				'value' => 'v2-develop'
			),
			array(
				'name' => 'Master',
				'value' => 'v2-master'
			)
		);
	}
	
	public function getAuthTypes()
	{
		return array(
			array(
				'name' => 'Organizr DB',
				'value' => 'internal'
			),
			array(
				'name' => 'Organizr DB + Backend',
				'value' => 'both'
			),
			array(
				'name' => 'Backend Only',
				'value' => 'external'
			)
		);
	}
	
	public function getLDAPOptions()
	{
		return array(
			array(
				'name' => 'Active Directory',
				'value' => '1'
			),
			array(
				'name' => 'OpenLDAP',
				'value' => '2'
			),
			array(
				'name' => 'First IPA',
				'value' => '3'
			),
		);
	}
	
	public function getAuthBackends()
	{
		$backendOptions = array();
		$backendOptions[] = array(
			'name' => 'Choose Backend',
			'value' => false,
			'disabled' => true
		);
		foreach (array_filter(get_class_methods('Organizr'), function ($v) {
			return strpos($v, 'plugin_auth_') === 0;
		}) as $value) {
			$name = str_replace('plugin_auth_', '', $value);
			if (strpos($name, 'disabled') === false) {
				$backendOptions[] = array(
					'name' => ucwords(str_replace('_', ' ', $name)),
					'value' => $name
				);
			} else {
				$backendOptions[] = array(
					'name' => $this->$value(),
					'value' => 'none',
					'disabled' => true,
				);
			}
		}
		ksort($backendOptions);
		return $backendOptions;
	}
	
	public function importUserButtons()
	{
		$emptyButtons = '
		<div class="col-md-12">
            <div class="white-box bg-org">
                <h3 class="box-title m-0" lang="en">Currently User import is available for Plex only.</h3> </div>
        </div>
	';
		$buttons = '';
		if (!empty($this->config['plexToken'])) {
			$buttons .= '<button class="btn m-b-20 m-r-20 bg-plex text-muted waves-effect waves-light importUsersButton" onclick="importUsers(\'plex\')" type="button"><span class="btn-label"><i class="mdi mdi-plex"></i></span><span lang="en">Import Plex Users</span></button>';
		}
		if (!empty($this->config['jellyfinURL']) && !empty($this->config['jellyfinToken'])) {
			$buttons .= '<button class="btn m-b-20 m-r-20 bg-primary text-muted waves-effect waves-light importUsersButton" onclick="importUsers(\'jellyfin\')" type="button"><span class="btn-label"><i class="mdi mdi-fish"></i></span><span lang="en">Import Jellyfin Users</span></button>';
		}
		if (!empty($this->config['embyURL']) && !empty($this->config['embyToken'])) {
			$buttons .= '<button class="btn m-b-20 m-r-20 bg-emby text-muted waves-effect waves-light importUsersButton" onclick="importUsers(\'emby\')" type="button"><span class="btn-label"><i class="mdi mdi-emby"></i></span><span lang="en">Import Emby Users</span></button>';
		}
		return ($buttons !== '') ? $buttons : $emptyButtons;
	}
	
	public function getHomepageMediaImage()
	{
		$refresh = false;
		$cacheDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
		if (!file_exists($cacheDirectory)) {
			mkdir($cacheDirectory, 0777, true);
		}
		@$image_url = $_GET['img'];
		@$key = $_GET['key'];
		@$image_height = $_GET['height'];
		@$image_width = $_GET['width'];
		@$source = $_GET['source'];
		@$itemType = $_GET['type'];
		if (strpos($key, '$') !== false) {
			$key = explode('$', $key)[0];
			$refresh = true;
		}
		switch ($source) {
			case 'plex':
				$plexAddress = $this->qualifyURL($this->config['plexURL']);
				$image_src = $plexAddress . '/photo/:/transcode?height=' . $image_height . '&width=' . $image_width . '&upscale=1&url=' . $image_url . '&X-Plex-Token=' . $this->config['plexToken'];
				break;
			case 'emby':
				$embyAddress = $this->qualifyURL($this->config['embyURL']);
				$imgParams = array();
				if (isset($_GET['height'])) {
					$imgParams['height'] = 'maxHeight=' . $_GET['height'];
				}
				if (isset($_GET['width'])) {
					$imgParams['width'] = 'maxWidth=' . $_GET['width'];
				}
				$image_src = $embyAddress . '/Items/' . $image_url . '/Images/' . $itemType . '?' . implode('&', $imgParams);
				break;
			case 'jellyfin':
				$jellyfinAddress = $this->qualifyURL($this->config['jellyfinURL']);
				$imgParams = array();
				if (isset($_GET['height'])) {
					$imgParams['height'] = 'maxHeight=' . $_GET['height'];
				}
				if (isset($_GET['width'])) {
					$imgParams['width'] = 'maxWidth=' . $_GET['width'];
				}
				$image_src = $jellyfinAddress . '/Items/' . $image_url . '/Images/' . $itemType . '?' . implode('&', $imgParams);
				break;
			default:
				# code...
				break;
		}
		if (isset($image_url) && isset($image_height) && isset($image_width) && isset($image_src)) {
			$cachefile = $cacheDirectory . $key . '.jpg';
			$cachetime = 604800;
			// Serve from the cache if it is younger than $cachetime
			if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile) && $refresh == false) {
				header("Content-type: image/jpeg");
				@readfile($cachefile);
				exit;
			}
			ob_start(); // Start the output buffer
			header('Content-type: image/jpeg');
			$options = array('verify' => false);
			$response = Requests::get($image_src, array(), $options);
			if ($response->success) {
				echo $response->body;
			}
			// Cache the output to a file
			$fp = fopen($cachefile, 'wb');
			fwrite($fp, ob_get_contents());
			fclose($fp);
			ob_end_flush(); // Send the output to the browser
			die();
		} else {
			die("Invalid Request");
		}
	}
	
	public function cacheImage($url, $name, $extension = 'jpg')
	{
		$cacheDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
		if (!file_exists($cacheDirectory)) {
			mkdir($cacheDirectory, 0777, true);
		}
		$cacheFile = $cacheDirectory . $name . '.' . $extension;
		$cacheTime = 604800;
		if ((file_exists($cacheFile) && time() - $cacheTime < filemtime($cacheFile)) || !file_exists($cacheFile)) {
			@copy($url, $cacheFile);
		}
	}
	
	public function checkFrame($array, $url)
	{
		if (array_key_exists("x-frame-options", $array)) {
			if ($array['x-frame-options'] == "deny") {
				return false;
			} elseif ($array['x-frame-options'] == "sameorgin") {
				$digest = parse_url($url);
				$host = (isset($digest['host']) ? $digest['host'] : '');
				if ($this->getServer() == $host) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			if (!$array) {
				return false;
			}
			return true;
		}
	}
	
	public function frameTest($url)
	{
		if (!$url || $url == '') {
			$this->setAPIResponse('error', 'URL not supplied', 404);
			return false;
		}
		$array = array_change_key_case(get_headers($this->qualifyURL($url), 1));
		$url = $this->qualifyURL($url);
		if ($this->checkFrame($array, $url)) {
			$this->setAPIResponse('success', 'URL approved for iFrame', 200);
			return true;
		} else {
			$this->setAPIResponse('error', 'URL failed approval for iFrame', 409);
			return false;
		}
	}
	
	public function groupSelect()
	{
		$groups = $this->getAllGroups();
		$select = array();
		foreach ($groups as $key => $value) {
			$select[] = array(
				'name' => $value['group'],
				'value' => $value['group_id']
			);
		}
		return $select;
	}
	
	public function showLogin()
	{
		if ($this->config['hideRegistration'] == false) {
			return '<p><span lang="en">Don\'t have an account?</span><a href="#" class="text-primary m-l-5 to-register"><b lang="en">Sign Up</b></a></p>';
		}
	}
	
	public function checkoAuth()
	{
		return $this->config['plexoAuth'] && $this->config['authBackend'] == 'plex' && $this->config['authType'] !== 'internal';
	}
	
	public function checkoAuthOnly()
	{
		return $this->config['plexoAuth'] && $this->config['authBackend'] == 'plex' && $this->config['authType'] == 'external';
	}
	
	public function showoAuth()
	{
		$buttons = '';
		if ($this->config['plexoAuth'] && $this->config['authBackend'] == 'plex' && $this->config['authType'] !== 'internal') {
			$buttons .= '<a href="javascript:void(0)" onclick="oAuthStart(\'plex\')" class="btn btn-lg btn-block text-uppercase waves-effect waves-light bg-plex text-muted" data-toggle="tooltip" title="" data-original-title="Login with Plex"> <span>Login</span><i aria-hidden="true" class="mdi mdi-plex m-l-5"></i> </a>';
		}
		return ($buttons) ? '
		<div class="panel">
            <div class="panel-heading bg-org" id="plex-login-heading" role="tab">
            	<a class="panel-title" data-toggle="collapse" href="#plex-login-collapse" data-parent="#login-panels" aria-expanded="false" aria-controls="organizr-login-collapse">
	                <img class="lazyload loginTitle" data-src="plugins/images/tabs/plex.png"> &nbsp;
                    <span class="text-uppercase fw300" lang="en">Login with Plex</span>
            	</a>
            </div>
            <div class="panel-collapse collapse in" id="plex-login-collapse" aria-labelledby="plex-login-heading" role="tabpanel">
                <div class="panel-body">
               		<div class="row">
			            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
			                <div class="social m-b-0">' . $buttons . '</div>
			            </div>
			        </div>
               </div>
            </div>
        </div>
	' : '';
	}
	
	public function logoOrText()
	{
		if ($this->config['useLogoLogin'] == false) {
			return '<h1>' . $this->config['title'] . '</h1>';
		} else {
			return '<img class="loginLogo" src="' . $this->config['loginLogo'] . '" alt="Home" />';
		}
	}
	
	public function settingsDocker()
	{
		$type = ($this->docker) ? 'Official Docker' : 'Native';
		return '<li><div class="bg-info"><i class="mdi mdi-flag mdi-24px text-white"></i></div><span class="text-muted hidden-xs m-t-10" lang="en">Install Type</span> ' . $type . '</li>';
	}
	
	public function settingsPathChecks()
	{
		$paths = $this->pathsWritable($this->paths);
		$items = '';
		$type = (array_search(false, $paths)) ? 'Not Writable' : 'Writable';
		$result = '<li class="mouse" onclick="toggleWritableFolders();"><div class="bg-info"><i class="mdi mdi-folder mdi-24px text-white"></i></div><span class="text-muted hidden-xs m-t-10" lang="en">Organizr Paths</span> ' . $type . '</li>';
		foreach ($paths as $k => $v) {
			$items .= '<li class="folders-writable hidden"><div class="bg-primary"><i class="mdi mdi-folder mdi-24px text-white"></i></div><a tabindex="0" type="button" class="btn btn-default btn-outline popover-info pull-right clipboard" lang="en" data-container="body" title="" data-toggle="popover" data-placement="left" data-content="' . $v['path'] . '" data-original-title="File Path" data-clipboard-text="' . $v['path'] . '">' . $k . '</a> ' . (($v['writable']) ? 'Writable' : 'Not Writable') . '</li>';
		}
		return $result . $items;
	}
	
	public function pathsWritable($paths)
	{
		$results = array();
		foreach ($paths as $k => $v) {
			$results[$k] = [
				'writable' => is_writable($v),
				'path' => $v
			];
		}
		return $results;
	}
	
	public function clearTautulliTokens()
	{
		foreach (array_keys($_COOKIE) as $k => $v) {
			if (strpos($v, 'tautulli') !== false) {
				$this->coookie('delete', $v);
			}
		}
	}
	
	public function clearJellyfinTokens()
	{
		foreach (array_keys($_COOKIE) as $k => $v) {
			if (strpos($v, 'user-') !== false) {
				$this->coookie('delete', $v);
			}
		}
		$this->coookie('delete', 'jellyfin_credentials');
	}
	
	public function analyzeIP($ip)
	{
		if (strpos($ip, '/') !== false) {
			$explodeIP = explode('/', $ip);
			$prefix = $explodeIP[1];
			$start_ip = $explodeIP[0];
			$ip_count = 1 << (32 - $prefix);
			$start_ip_long = ip2long($start_ip);
			$last_ip_long = ip2long($start_ip) + $ip_count - 1;
		} elseif (substr_count($ip, '.') == 3) {
			$start_ip_long = ip2long($ip);
			$last_ip_long = ip2long($ip);
		}
		return (isset($start_ip_long) && isset($last_ip_long)) ? array('from' => $start_ip_long, 'to' => $last_ip_long) : false;
	}
	
	public function authProxyRangeCheck($from, $to)
	{
		$approved = false;
		$userIP = ip2long($_SERVER['REMOTE_ADDR']);
		$low = $from;
		$high = $to;
		if ($userIP <= $high && $low <= $userIP) {
			$approved = true;
		}
		return $approved;
	}
	
	public function userDefinedIdReplacementLink($link, $variables)
	{
		return strtr($link, $variables);
	}
	
	public function requestOptions($url, $override = false, $timeout = null)
	{
		$options = [];
		if (is_numeric($timeout)) {
			$timeout = $timeout / 1000;
			array_push($options, array('timeout' => $timeout));
		}
		if ($this->localURL($url, $override)) {
			array_push($options, array('verify' => false));
			
		}
		return $options;
	}
}