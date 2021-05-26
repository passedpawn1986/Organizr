<?php

trait NetDataHomepageItem
{
	public function getNetdataHomepageData()
	{
		if (!$this->homepageItemPermissions($this->netdataHomepagePermissions('main'), true)) {
			return false;
		}
		$api = [];
		$api['data'] = [];
		$api['url'] = $this->config['netdataURL'];
		$url = $this->qualifyURL($this->config['netdataURL']);
		for ($i = 1; $i < 8; $i++) {
			if ($this->config['netdata' . ($i) . 'Enabled']) {
				switch ($this->config['netdata' . $i . 'Data']) {
					case 'disk-read':
						$data = $this->disk('in', $url);
						break;
					case 'disk-write':
						$data = $this->disk('out', $url);
						$data['value'] = abs($data['value']);
						$data['percent'] = abs($data['percent']);
						break;
					case 'cpu':
						$data = $this->cpu($url);
						break;
					case 'net-in':
						$data = $this->net('received', $url);
						break;
					case 'net-out':
						$data = $this->net('sent', $url);
						$data['value'] = abs($data['value']);
						$data['percent'] = abs($data['percent']);
						break;
					case 'ram-used':
						$data = $this->ram($url);
						break;
					case 'swap-used':
						$data = $this->swap($url);
						break;
					case 'disk-avail':
						$data = $this->diskSpace('avail', $url);
						break;
					case 'disk-used':
						$data = $this->diskSpace('used', $url);
						break;
					case 'ipmi-temp-c':
						$data = $this->ipmiTemp($url, 'c');
						break;
					case 'ipmi-temp-f':
						$data = $this->ipmiTemp($url, 'f');
						break;
					case 'cpu-temp-c':
						$data = $this->cpuTemp($url, 'c');
						break;
					case 'cpu-temp-f':
						$data = $this->cpuTemp($url, 'f');
						break;
					case 'custom':
						$data = $this->customNetdata($url, $i);
						break;
					default:
						$data = [
							'title' => 'DNC',
							'value' => 0,
							'units' => 'N/A',
							'max' => 100,
						];
						break;
				}
				$data['title'] = $this->config['netdata' . $i . 'Title'];
				$data['colour'] = $this->config['netdata' . $i . 'Colour'];
				$data['chart'] = $this->config['netdata' . $i . 'Chart'];
				$data['size'] = $this->config['netdata' . $i . 'Size'];
				$data['lg'] = $this->config['netdata' . ($i) . 'lg'];
				$data['md'] = $this->config['netdata' . ($i) . 'md'];
				$data['sm'] = $this->config['netdata' . ($i) . 'sm'];
				array_push($api['data'], $data);
			}
		}
		$api = isset($api) ? $api : false;
		$this->setAPIResponse('success', null, 200, $api);
		return $api;
		
	}
	
	public function netdataSettingsArray($infoOnly = false)
	{
		$homepageInformation = [
			'name' => 'Netdata',
			'enabled' => true,
			'image' => 'plugins/images/tabs/netdata.png',
			'category' => 'Monitor',
			'settingsArray' => __FUNCTION__
		];
		if ($infoOnly) {
			return $homepageInformation;
		}
		$homepageSettings = array(
			'settings' => array(
				'Enable' => array(
					array(
						'type' => 'switch',
						'name' => 'homepageNetdataEnabled',
						'label' => 'Enable',
						'value' => $this->config['homepageNetdataEnabled']
					),
					array(
						'type' => 'select',
						'name' => 'homepageNetdataAuth',
						'label' => 'Minimum Authentication',
						'value' => $this->config['homepageNetdataAuth'],
						'options' => $this->groupSelect()
					)
				),
				'Connection' => array(
					array(
						'type' => 'input',
						'name' => 'netdataURL',
						'label' => 'URL',
						'value' => $this->config['netdataURL'],
						'help' => 'Please enter the local IP:PORT of your netdata instance'
					),
					array(
						'type' => 'blank',
						'label' => ''
					),
				),
			)
		);
		for ($i = 1; $i <= 7; $i++) {
			$homepageSettings['settings']['Chart ' . $i] = array(
				array(
					'type' => 'switch',
					'name' => 'netdata' . $i . 'Enabled',
					'label' => 'Enable',
					'value' => $this->config['netdata' . $i . 'Enabled']
				),
				array(
					'type' => 'blank',
					'label' => ''
				),
				array(
					'type' => 'input',
					'name' => 'netdata' . $i . 'Title',
					'label' => 'Title',
					'value' => $this->config['netdata' . $i . 'Title'],
					'help' => 'Title for the netdata graph'
				),
				array(
					'type' => 'select',
					'name' => 'netdata' . $i . 'Data',
					'label' => 'Data',
					'value' => $this->config['netdata' . $i . 'Data'],
					'options' => $this->netdataOptions(),
				),
				array(
					'type' => 'select',
					'name' => 'netdata' . $i . 'Chart',
					'label' => 'Chart',
					'value' => $this->config['netdata' . $i . 'Chart'],
					'options' => $this->netdataChartOptions(),
				),
				array(
					'type' => 'select',
					'name' => 'netdata' . $i . 'Colour',
					'label' => 'Colour',
					'value' => $this->config['netdata' . $i . 'Colour'],
					'options' => $this->netdataColourOptions(),
				),
				array(
					'type' => 'select',
					'name' => 'netdata' . $i . 'Size',
					'label' => 'Size',
					'value' => $this->config['netdata' . $i . 'Size'],
					'options' => $this->netdataSizeOptions(),
				),
				array(
					'type' => 'blank',
					'label' => ''
				),
				array(
					'type' => 'switch',
					'name' => 'netdata' . $i . 'lg',
					'label' => 'Show on large screens',
					'value' => $this->config['netdata' . $i . 'lg']
				),
				array(
					'type' => 'switch',
					'name' => 'netdata' . $i . 'md',
					'label' => 'Show on medium screens',
					'value' => $this->config['netdata' . $i . 'md']
				),
				array(
					'type' => 'switch',
					'name' => 'netdata' . $i . 'sm',
					'label' => 'Show on small screens',
					'value' => $this->config['netdata' . $i . 'sm']
				),
			);
		}
		$homepageSettings['settings']['Custom data'] = array(
			array(
				'type' => 'html',
				'label' => '',
				'override' => 12,
				'html' => '
			<div>
			    <p>This is where you can define custom data sources for your netdata charts. To use a custom source, you need to select "Custom" in the data field for the chart.</p>
			    <p>To define a custom data source, you need to add an entry to the JSON below, where the key is the chart number you want the custom data to be used for. Here is an example to set chart 1 custom data source to RAM percentage:</p>
			    <pre>{
			    "1": {
			        "url": "/api/v1/data?chart=system.ram&format=array&points=540&group=average&gtime=0&options=absolute|percentage|jsonwrap|nonzero&after=-540&dimensions=used|buffers|active|wired",
			        "value": "result,0",
			        "units": "%",
			        "max": 100
			    }
			}</pre>
			    <p>The URL is appended to your netdata URL and returns JSON formatted data. The value field tells Organizr how to return the value you want from the netdata API. This should be formatted as comma-separated keys to access the desired value.</p>
			    <table class="table table-striped">
			        <thead>
			            <tr>
			                <th>Parameter</th>
			                <th>Description</th>
			                <th>Required</th>
			            </tr>
			        </thead>
			        <tbody>
			            <tr>
			                <td>url</td>
			                <td>Specifies the netdata API endpoint</td>
			                <td><i class="fa fa-check text-success" aria-hidden="true"></i></td>
			            </tr>
			            <tr>
			                <td>value</td>
			                <td>Specifies the selector used to get the data form the netdata response</td>
			                <td><i class="fa fa-check text-success" aria-hidden="true"></i></td>
			            </tr>
			            <tr>
			                <td>units</td>
			                <td>Specifies the units shown in the graph/chart. Defaults to %</td>
			                <td><i class="fa fa-times text-danger" aria-hidden="true"></i></td>
			            </tr>
			            <tr>
			                <td>max</td>
			                <td>Specifies the maximum possible value for the data. Defaults to 100</td>
			                <td><i class="fa fa-times text-danger" aria-hidden="true"></i></td>
			            </tr>
			            <tr>
			                <td>mutator</td>
			                <td>Used to perform simple mathematical operations on the result (+, -, /, *). For example: dividing the result by 1000 would be "/1000". These operations can be chained together by putting them in a comma-seprated format.</td>
			                <td><i class="fa fa-times text-danger" aria-hidden="true"></i></td>
			            </tr>
			            <tr>
			                <td>netdata</td>
			                <td>Can be used to override the netdata instance data is retrieved from (in the format: http://IP:PORT)</td>
			                <td><i class="fa fa-times text-danger" aria-hidden="true"></i></td>
			            </tr>
			        </tbody>
			    </table>
			</div>'
			),
			array(
				'type' => 'html',
				'name' => 'netdataCustomTextAce',
				'class' => 'jsonTextarea hidden',
				'label' => 'Custom definitions',
				'override' => 12,
				'html' => '<div id="netdataCustomTextAce" style="height: 300px;">' . htmlentities($this->config['netdataCustom']) . '</div>',
			),
			array(
				'type' => 'textbox',
				'name' => 'netdataCustom',
				'class' => 'jsonTextarea hidden',
				'id' => 'netdataCustomText',
				'label' => '',
				'value' => $this->config['netdataCustom'],
			)
		);
		$homepageSettings['settings']['Options'] = array(
			array(
				'type' => 'select',
				'name' => 'homepageNetdataRefresh',
				'label' => 'Refresh Seconds',
				'value' => $this->config['homepageNetdataRefresh'],
				'options' => $this->timeOptions()
			),
		);
		return array_merge($homepageInformation, $homepageSettings);
	}
	
	public function netdataHomepagePermissions($key = null)
	{
		$permissions = [
			'main' => [
				'enabled' => [
					'homepageNetdataEnabled'
				],
				'auth' => [
					'homepageNetdataAuth'
				],
				'not_empty' => [
					'netdataURL'
				]
			]
		];
		if (array_key_exists($key, $permissions)) {
			return $permissions[$key];
		} elseif ($key == 'all') {
			return $permissions;
		} else {
			return [];
		}
	}
	
	public function homepageOrderNetdata()
	{
		if ($this->homepageItemPermissions($this->netdataHomepagePermissions('main'))) {
			return '
				<div id="' . __FUNCTION__ . '">
					<div class="white-box homepage-loading-box"><h2 class="text-center" lang="en">Loading Netdata...</h2></div>
					<script>
						// Netdata
						homepageNetdata("' . $this->config['homepageNetdataRefresh'] . '");
						// End Netdata
					</script>
				</div>
				';
		}
	}
	
	public function disk($dimension, $url)
	{
		$data = [];
		// Get Data
		$dataUrl = $url . '/api/v1/data?chart=system.io&dimensions=' . $dimension . '&format=array&points=540&group=average&gtime=0&options=absolute|jsonwrap|nonzero&after=-540';
		try {
			$response = Requests::get($dataUrl);
			if ($response->success) {
				$json = json_decode($response->body, true);
				$data['value'] = $json['latest_values'][0] / 1000;
				$data['percent'] = $this->getPercent($json['latest_values'][0], $json['max']);
				$data['units'] = 'MiB/s';
				$data['max'] = $json['max'];
			}
		} catch (Requests_Exception $e) {
			$this->writeLog('error', 'Netdata Connect Function - Error: ' . $e->getMessage(), 'SYSTEM');
		};
		return $data;
	}
	
	public function diskSpace($dimension, $url)
	{
		$data = [];
		// Get Data
		$dataUrl = $url . '/api/v1/data?chart=disk_space._&format=json&points=509&group=average&gtime=0&options=ms|jsonwrap|nonzero&after=-540&dimension=' . $dimension;
		try {
			$response = Requests::get($dataUrl);
			if ($response->success) {
				$json = json_decode($response->body, true);
				$data['value'] = $json['result']['data'][0][1];
				$data['percent'] = $data['value'];
				$data['units'] = '%';
				$data['max'] = 100;
			}
		} catch (Requests_Exception $e) {
			$this->writeLog('error', 'Netdata Connect Function - Error: ' . $e->getMessage(), 'SYSTEM');
		};
		return $data;
	}
	
	public function net($dimension, $url)
	{
		$data = [];
		// Get Data
		$dataUrl = $url . '/api/v1/data?chart=system.net&dimensions=' . $dimension . '&format=array&points=540&group=average&gtime=0&options=absolute|jsonwrap|nonzero&after=-540';
		try {
			$response = Requests::get($dataUrl);
			if ($response->success) {
				$json = json_decode($response->body, true);
				$data['value'] = $json['latest_values'][0] / 1000;
				$data['percent'] = $this->getPercent($json['latest_values'][0], $json['max']);
				$data['units'] = 'Mbit/s';
				$data['max'] = $json['max'];
			}
		} catch (Requests_Exception $e) {
			$this->writeLog('error', 'Netdata Connect Function - Error: ' . $e->getMessage(), 'SYSTEM');
		};
		return $data;
	}
	
	public function cpu($url)
	{
		$data = [];
		$dataUrl = $url . '/api/v1/data?chart=system.cpu&format=array';
		try {
			$response = Requests::get($dataUrl);
			if ($response->success) {
				$json = json_decode($response->body, true);
				$data['value'] = $json[0];
				$data['percent'] = $data['value'];
				$data['max'] = 100;
				$data['units'] = '%';
			}
		} catch (Requests_Exception $e) {
			$this->writeLog('error', 'Netdata Connect Function - Error: ' . $e->getMessage(), 'SYSTEM');
		};
		return $data;
	}
	
	public function ram($url)
	{
		$data = [];
		$dataUrl = $url . '/api/v1/data?chart=system.ram&format=array&points=540&group=average&gtime=0&options=absolute|percentage|jsonwrap|nonzero&after=-540&dimensions=used|buffers|active|wired';
		try {
			$response = Requests::get($dataUrl);
			if ($response->success) {
				$json = json_decode($response->body, true);
				$data['value'] = $json['result'][0];
				$data['percent'] = $data['value'];
				$data['max'] = 100;
				$data['units'] = '%';
			}
		} catch (Requests_Exception $e) {
			$this->writeLog('error', 'Netdata Connect Function - Error: ' . $e->getMessage(), 'SYSTEM');
		};
		return $data;
	}
	
	public function swap($url)
	{
		$data = [];
		$dataUrl = $url . '/api/v1/data?chart=system.swap&format=array&points=540&group=average&gtime=0&options=absolute|percentage|jsonwrap|nonzero&after=-540&dimensions=used';
		try {
			$response = Requests::get($dataUrl);
			if ($response->success) {
				$json = json_decode($response->body, true);
				$data['value'] = $json['result'][0];
				$data['percent'] = $data['value'];
				$data['max'] = 100;
				$data['units'] = '%';
			}
		} catch (Requests_Exception $e) {
			$this->writeLog('error', 'Netdata Connect Function - Error: ' . $e->getMessage(), 'SYSTEM');
		};
		return $data;
	}
	
	public function getPercent($val, $max)
	{
		if ($max == 0) {
			return 0;
		} else {
			return ($val / $max) * 100;
		}
	}
	
	public function customNetdata($url, $id)
	{
		try {
			$customs = json_decode($this->config['netdataCustom'], true, 512, JSON_THROW_ON_ERROR);
		} catch (Exception $e) {
			$customs = false;
		}
		if ($customs == false) {
			return [
				'error' => 'unable to parse custom JSON'
			];
		} else if (!isset($customs[$id])) {
			return [
				'error' => 'custom definition not found'
			];
		} else {
			$data = [];
			$custom = $customs[$id];
			if (isset($custom['url']) && isset($custom['value'])) {
				if (isset($custom['netdata']) && $custom['netdata'] != '') {
					$url = $this->qualifyURL($custom['netdata']);
				}
				$dataUrl = $url . '/' . $custom['url'];
				try {
					$response = Requests::get($dataUrl);
					if ($response->success) {
						$json = json_decode($response->body, true);
						if (!isset($custom['max']) || $custom['max'] == '') {
							$custom['max'] = 100;
						}
						$data['max'] = $custom['max'];
						if (!isset($custom['units']) || $custom['units'] == '') {
							$custom['units'] = '%';
						}
						$data['units'] = $custom['units'];
						$selectors = explode(',', $custom['value']);
						foreach ($selectors as $selector) {
							if (is_numeric($selector)) {
								$selector = (int)$selector;
							}
							if (!isset($data['value'])) {
								$data['value'] = $json[$selector];
							} else {
								$data['value'] = $data['value'][$selector];
							}
						}
						if (isset($custom['mutator'])) {
							$data['value'] = $this->parseMutators($data['value'], $custom['mutator']);
						}
						if ($data['max'] == 0) {
							$data['percent'] = 0;
						} else {
							$data['percent'] = ($data['value'] / $data['max']) * 100;
						}
					}
				} catch (Requests_Exception $e) {
					$this->writeLog('error', 'Netdata Connect Function - Error: ' . $e->getMessage(), 'SYSTEM');
				};
			} else {
				$data['error'] = 'custom definition incomplete';
			}
			return $data;
		}
	}
	
	public function parseMutators($val, $mutators)
	{
		$mutators = explode(',', $mutators);
		foreach ($mutators as $m) {
			$op = $m[0];
			try {
				$m = (float)substr($m, 1);
				switch ($op) {
					case '+':
						$val = $val + $m;
						break;
					case '-':
						$val = $val - $m;
						break;
					case '/':
						$val = $val / $m;
						break;
					case '*':
						$val = $val * $m;
						break;
					default:
						break;
				}
			} catch (Exception $e) {
				//
			}
		}
		return $val;
	}
}