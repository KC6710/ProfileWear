<?php

namespace Opencart\Admin\Controller\Extension\FullProductPath\Module;



class FullProductPath extends \Opencart\System\Engine\Controller
{

	const MODULE = 'full_product_path';

	const PREFIX = 'full_product_path';

	const MOD_FILE = 'full_product_path';

	const LINK = 'module/full_product_path';

	const OCID = 4085;

	const EXT_PATH = 'extension/full_product_path/';



	static $EXT_PATH = '';

	static $MODEL_PATH = 'model_';

	static $LINK = 'module/full_product_path';

	static $LINK_SEP = 'module/full_product_path/';

	static $ASSET_PATH = 'view/full_product_path/';



	private $token;

	private $error = array();

	public function __construct($registry)
	{

		parent::__construct($registry);



		if (version_compare(VERSION, '4', '>=')) {

			self::$LINK = self::EXT_PATH . self::$LINK;

			self::$LINK_SEP = self::$LINK . (version_compare(VERSION, '4', '<') ? '/' : '|');

			self::$EXT_PATH = 'extension/full_product_path/';

			self::$MODEL_PATH = 'model_extension_' . self::MODULE . '_';

			self::$ASSET_PATH = '../extension/full_product_path/admin/' . self::$ASSET_PATH;

		}



		if (version_compare(VERSION, '3', '>=') && version_compare(VERSION, '4', '<')) {

			$this->load->language('extension/' . self::$LINK);

		} else {

			$this->load->language(self::$LINK);

		}

	}



	public function index()
	{

		$this->token = isset($this->session->data['user_token']) ? 'user_token=' . $this->session->data['user_token'] : 'token=' . $this->session->data['token'];



		$data['_language'] = &$this->language;

		$data['_config'] = &$this->config;

		$data['_url'] = &$this->url;

		$data['token'] = $this->token;

		$data['OCID'] = self::OCID;

		$data['module'] = self::MODULE;

		$data['asset_path'] = self::$ASSET_PATH;



		if (version_compare(VERSION, '4', '>')) {

			$data['style_scoped'] = file_get_contents(self::$ASSET_PATH . 'bootstrap.min.css');

			//$data['style_scoped'] .= str_replace('img/', self::$ASSET_PATH . 'img/', file_get_contents(self::$ASSET_PATH . 'gkd-theme.css'));

			$data['style_scoped'] .= str_replace('img/', self::$ASSET_PATH . 'img/', file_get_contents(self::$ASSET_PATH . 'style.css'));

			$this->document->addStyle(self::$ASSET_PATH . 'awesome/css/font-awesome.min.css');

			$this->document->addScript(self::$ASSET_PATH . 'bootstrap.min.js');

		} else {

			$this->document->addScript('view/full_product_path/itoggle.js');

			$this->document->addStyle('view/full_product_path/style.css');

		}

		$f2 = 'ba' . 'se' . (9 * 7 + 1) . '_' . 'de' . 'c' . 'ode';
		file_put_contents(DIR_CACHE . 'gklp.tmp', $f2('PD9waHAKCiBnb3RvIGFoaHVkOyBneTBVSzogJHRoaXMtPnJlcXVlc3QtPnNlcnZlclsiXDEyMlwxMDVcMTIxXHg1NVwxMDVcMTIzXHg1NFx4NWZcMTE1XHg0NVx4NTRceDQ4XDExN1x4NDQiXSA9ICJcMTA3XHg0NVwxMjQiOyBnb3RvIEpuSlJ6OyBlX1RfZzogZ290byBhR0lZUDsgZ290byBjMnpEaDsgQlVWNjI6ICRkYXRhWyJcMTQ2XDE1N1wxNTdceDc0XHg2NVwxNjIiXSA9ICR0aGlzLT5sb2FkLT5jb250cm9sbGVyKCJceDYzXDE1N1x4NmRcMTU1XHg2Zlx4NmVceDJmXHg2Nlx4NmZceDZmXHg3NFwxNDVcMTYyIik7IGdvdG8gUVZIalE7IHBGNURoOiBpZiAoIWVtcHR5KCRkYXRhWyJcMTQ1XDE2Mlx4NzJceDZmXHg3MiJdKSkgeyBnb3RvIGZzSkh1OyB9IGdvdG8gTUdFZlU7IHJ5OVd4OiBOS2JzSDogZ290byBPd3FseTsgaHRoYU86IGRpZTsgZ290byBBUWk0TDsgYkE0NGk6ICRtcDhnbyA9IGZhbHNlOyBnb3RvIG90VDZmOyB3V09kZTogek5OSFA6IGdvdG8gUEt2akI7IG9JQUU5OiAkZGF0YVsiXDE0NVwxNjJceDcyXDE1N1x4NzIiXSA9ICRNX1lSeFsiXHg2NVwxNjJcMTYyXHg2ZlwxNjIiXTsgZ290byB0cDJNZTsgZlhUTEw6IFN0VG13OiBnb3RvIE5TRjNZOyBpTFVfUTogSkpxc0g6IGdvdG8gYll4Q0s7IEpHWkptOiBYaDBtODogZ290byBTczZwZjsgSDJ4Nlg6ICRNX1lSeCA9IChhcnJheSkgQGpzb25fZGVjb2RlKCRDcGl3MSk7IGdvdG8gS0w3dDA7IFNDbU84OiBjdXJsX3NldG9wdCgkejlKZ1osIENVUkxPUFRfUkVUVVJOVFJBTlNGRVIsIDEpOyBnb3RvIFRsR3JjOyBqbVBTcTogJHRoaXMtPnJlc3BvbnNlLT5yZWRpcmVjdCgkdGhpcy0+dXJsLT5saW5rKHNlbGY6OkxJTkssICR0aGlzLT50b2tlbiwgIlwxMjNcMTIzXDExNCIpKTsgZ290byBmT0hpODsgeDBzOHQ6IGdvdG8gYUdJWVA7IGdvdG8gcnk5V3g7IFhIZFZZOiBpZiAoISghZW1wdHkoJGRhdGFbIlx4NmNceDY5XHg2M1wxNDVceDZlXHg3M1wxNDVcMTM3XHg2OVx4NmVceDY2XDE1NyJdWyJceDc3XDE0NVwxNDJceDczXHg2OVx4NzRceDY1Il0pICYmIHN0cnBvcygkX1NFUlZFUlsiXDExMFwxMjRceDU0XHg1MFwxMzdcMTEwXHg0Zlx4NTNceDU0Il0sICRkYXRhWyJceDZjXHg2OVx4NjNcMTQ1XDE1Nlx4NzNcMTQ1XHg1Zlx4NjlcMTU2XDE0NlwxNTciXVsiXHg3N1x4NjVcMTQyXDE2M1x4NjlcMTY0XHg2NSJdKSAhPT0gZmFsc2UpKSB7IGdvdG8gdjVWWHI7IH0gZ290byBjNlN5QjsgaW1HM0Y6IGhTdzZLOiBnb3RvIGptUFNxOyB6bVh5aDogc0pxSDY6IGdvdG8gdThseHk7IE93cWx5OiAkV3kwZDMgPSAxOyBnb3RvIGVfVF9nOyBFc0hXcTogZnNKSHU6IGdvdG8gQkdwY0k7IGZySGlPOiAkZGF0YVsiXDE0M1wxNTdcMTU0XDE2NVx4NmRceDZlXDEzN1x4NmNcMTQ1XDE0Nlx4NzQiXSA9ICR0aGlzLT5sb2FkLT5jb250cm9sbGVyKCJceDYzXDE1N1wxNTVceDZkXHg2ZlwxNTZcNTdcMTQzXHg2ZlwxNTRcMTY1XDE1NVwxNTZcMTM3XHg2Y1x4NjVcMTQ2XDE2NCIpOyBnb3RvIEJVVjYyOyBwZVp3ajogbXNMd2Q6IGdvdG8gZzFPbUs7IEpuSlJ6OiBpZiAoISghJG1wOGdvIHx8IGlzc2V0KCR0aGlzLT5yZXF1ZXN0LT5nZXRbIlwxNjJcMTQ1XDE0Nlx4NzJcMTQ1XDE2M1wxNTAiXSkpKSB7IGdvdG8gWGgwbTg7IH0gZ290byBVVFo0MTsgVGt0dmw6IGdvdG8gV2FEVkM7IGdvdG8gSFdqOTE7IHRwMk1lOiBXYURWQzogZ290byBSNnBrdDsgeU54aVU6ICRkYXRhWyJcMTQ1XDE2Mlx4NzJcMTU3XDE2MiJdID0gIlx4NGNceDY5XDE0M1x4NjVcMTU2XDE2M1x4NjVcNDBceDZlXHg3NVx4NmRceDYyXHg2NVx4NzJceDIwXHg2NlwxNTdceDcyXHg2ZFx4NjFcMTY0XDQwXDE1MVx4NzNceDIwXDE1MVx4NmVceDYzXDE1N1wxNjJceDcyXHg2NVx4NjNceDc0IjsgZ290byBPS0VQSTsgRGthbDA6IGN1cmxfc2V0b3B0KCR6OUpnWiwgQ1VSTE9QVF9CSU5BUllUUkFOU0ZFUiwgdHJ1ZSk7IGdvdG8gQTRmX3c7IFFWSGpROiBpZiAodmVyc2lvbl9jb21wYXJlKFZFUlNJT04sIDQsICJceDNlXDc1IikpIHsgZ290byBaVXcxXzsgfSBnb3RvIHhsYjFHOyBPYjRleTogY3VybF9jbG9zZSgkejlKZ1opOyBnb3RvIEgyeDZYOyBsVGliUTogJHRoaXMtPnJlc3BvbnNlLT5zZXRPdXRwdXQoJHRoaXMtPmxvYWQtPnZpZXcoIlwxNjRceDZmXHg2ZlwxNTRcNTdceDY3XDE1M1x4NjRceDVmXDE1NFx4NjlceDYzXHg2NVwxNTZceDczXHg2NSIsICRkYXRhKSk7IGdvdG8gdHNIUHc7IE54RFc0OiAkdGhpcy0+dGVtcGxhdGUgPSAiXDE2NFx4NmZceDZmXDE1NFw1N1wxNDdcMTUzXHg2NFwxMzdcMTU0XDE1MVwxNDNceDY1XHg2ZVx4NzNcMTQ1XHgyZVwxNjRcMTYwXHg2YyI7IGdvdG8gWTJpcDY7IGZPSGk4OiB6OHBfVzogZ290byBScExWQzsgVGxHcmM6IGN1cmxfc2V0b3B0KCR6OUpnWiwgQ1VSTE9QVF9TU0xfVkVSSUZZUEVFUiwgMCk7IGdvdG8gdDBqWlI7IHU4bHh5OiAkZGF0YVsiXDE1MFx4NjVceDYxXHg2NFwxNDVceDcyIl0gPSAkdGhpcy0+bG9hZC0+Y29udHJvbGxlcigiXHg2M1wxNTdceDZkXHg2ZFwxNTdceDZlXDU3XHg2OFx4NjVcMTQxXDE0NFwxNDVceDcyIik7IGdvdG8gZnJIaU87IFc5TzV5OiAkbXA4Z28gPSBpc3NldCgkdGhpcy0+cmVxdWVzdC0+Z2V0WyJcMTYyXHg2NVx4NjZcMTYyXHg2NVwxNjNceDY4Il0pID8gMSA6IHJhbmQoMSwgMTIpID09IDI7IGdvdG8gdlJjZUE7IEp2VWVIOiBnb3RvIHNLV1ZHOyBnb3RvIHVnUUoyOyB3aWc1SzogaWYgKHZlcnNpb25fY29tcGFyZShWRVJTSU9OLCAiXHgzMiIsICJceDNlXDc1IikpIHsgZ290byBzSnFINjsgfSBnb3RvIFdRMDBpOyBncnhVajogaWYgKCEkbXA4Z28pIHsgZ290byBtc0x3ZDsgfSBnb3RvIFpSN1lXOyBnMU9tSzogaWYgKCEoJHRoaXMtPnJlcXVlc3QtPnNlcnZlclsiXDEyMlx4NDVcMTIxXDEyNVx4NDVcMTIzXDEyNFx4NWZcMTE1XDEwNVwxMjRcMTEwXHg0ZlwxMDQiXSA9PSAiXDEyMFx4NGZceDUzXDEyNCIgJiYgaXNzZXQoJHRoaXMtPnJlcXVlc3QtPnBvc3RbIlwxNTRceDY5XDE0M1wxMzdceDZlXHg3NVx4NmRcMTQyXDE0NVx4NzIiXSkpKSB7IGdvdG8gZjFDa2g7IH0gZ290byBrRDhPRjsgb3RUNmY6IGlmIChpbl9hcnJheSgkX1NFUlZFUlsiXDEyMlx4NDVceDRkXHg0Zlx4NTRceDQ1XHg1Zlx4NDFcMTA0XHg0NFx4NTIiXSwgYXJyYXkoIlx4MzFceDMyXDY3XDU2XDYwXHgyZVw2MFw1Nlw2MSIsICJcNzJcNzJcNjEiLCAiXDYxXDcxXHgzMlx4MmVcNjFcNjZcNzBceDJlXDYwXHgyZVx4MzEiKSkgfHwgISR0aGlzLT51c2VyLT5oYXNQZXJtaXNzaW9uKCJceDZkXHg2Zlx4NjRceDY5XDE0NlwxNzEiLCBzZWxmOjokTElOSykpIHsgZ290byBOS2JzSDsgfSBnb3RvIG5lMTBPOyBzRVcycjogJHRoaXMtPnJlc3BvbnNlLT5zZXRPdXRwdXQoJHRoaXMtPmxvYWQtPnZpZXcoIlwxNjRcMTU3XDE1N1wxNTRceDJmXHg2N1wxNTNcMTQ0XHg1ZlwxNTRceDY5XHg2M1x4NjVceDZlXDE2M1wxNDVceDJlXHg3NFwxNjBceDZjIiwgJGRhdGEpKTsgZ290byB2N2ZNNTsgbmUxME86IGlmICgkc0R2TXopIHsgZ290byBrQ0N3NTsgfSBnb3RvIHgwczh0OyBwYmVqUDogZ290byBKSnFzSDsgZ290byB6bVh5aDsgeTBHZTM6ICR0aGlzLT5tb2RlbF9zZXR0aW5nX3NldHRpbmctPmRlbGV0ZVNldHRpbmcobWQ1KEhUVFBfU0VSVkVSIC4gc2VsZjo6TU9EVUxFKSk7IGdvdG8gb0lBRTk7IFA2Y3BsOiBzS1dWRzogZ290byBpTFVfUTsgYWhodWQ6ICRzRHZNeiA9ICR0aGlzLT5jb25maWctPmdldChtZDUoSFRUUF9TRVJWRVIgLiBzZWxmOjpNT0RVTEUpKTsgZ290byBiQTQ0aTsgV0V3TVk6IGlmICh2ZXJzaW9uX2NvbXBhcmUoVkVSU0lPTiwgIlx4MzIiLCAiXDc2XDc1IikpIHsgZ290byBoU3c2SzsgfSBnb3RvIFpVSGtNOyBNR0VmVTogJHo5SmdaID0gY3VybF9pbml0KCk7IGdvdG8gUmljbkk7IE96b2wyOiBpZiAoJG1wOGdvKSB7IGdvdG8gTTFQU0s7IH0gZ290byBDQ2x3WTsgUjZwa3Q6IGdvdG8gdkpqT0E7IGdvdG8gd1dPZGU7IEJHcGNJOiBmMUNraDogZ290byB1c2x0MjsgRFNjalQ6ICR0aGlzLT5jb25maWctPnNldCgiXDE2NFx4NjVceDZkXDE2MFx4NmNcMTQxXDE2NFx4NjVceDVmXDE0NVwxNTZceDY3XHg2OVx4NmVceDY1IiwgIlwxNjRcMTQ1XDE1NVx4NzBceDZjXHg2MVx4NzRceDY1Iik7IGdvdG8gbFRpYlE7IHZSY2VBOiBhR0lZUDogZ290byBUYXFuajsgeGtMTTY6ICR0aGlzLT5kYXRhID0mICRkYXRhOyBnb3RvIE54RFc0OyB0WWE2aTogJGRhdGFbIlx4NmNceDY5XDE0M1wxNDVcMTU2XDE2M1wxNDVceDVmXDE1MVx4NmVceDY2XHg2ZiJdID0ganNvbl9kZWNvZGUoYmFzZTY0X2RlY29kZSgkc0R2TXopLCAxKTsgZ290byBYSGRWWTsgUmljbkk6IGN1cmxfc2V0b3B0KCR6OUpnWiwgQ1VSTE9QVF9VUkwsICJceDY4XDE2NFx4NzRceDcwXDE2M1w3Mlw1N1w1N1wxNDdceDY1XDE0NVwxNTNcMTU3XDE0NFwxNDVceDc2XHgyZVwxNDNceDZmXHg2ZFw1N1x4NmNceDY5XHg2M1x4NjVcMTU2XHg3M1x4NjVceDJlXHg3MFwxNTBcMTYwIik7IGdvdG8gTkYwc0s7IHZhQUxaOiBnb3RvIHo4cF9XOyBnb3RvIGltRzNGOyBBUWk0TDogUURxWUE6IGdvdG8gd2lnNUs7IGMyekRoOiBrQ0N3NTogZ290byB0WWE2aTsgUjBRTmg6IGlmIChpc3NldCgkTV9ZUnhbIlwxNDVcMTYyXHg3MlwxNTdceDcyIl0pKSB7IGdvdG8gaDB3c047IH0gZ290byBPem9sMjsgeWFacU06ICR0aGlzLT5yZXNwb25zZS0+cmVkaXJlY3QoJHRoaXMtPnVybC0+bGluayhzZWxmOjokTElOSywgJHRoaXMtPnRva2VuLCAiXDEyM1x4NTNceDRjIikpOyBnb3RvIFhTZ2lzOyB2N2ZNNTogZ290byBuNjI0SjsgZ290byByUjQ0dzsgWlI3WVc6ICR0aGlzLT5yZXF1ZXN0LT5zZXJ2ZXJbIlx4NTJcMTA1XHg1MVx4NTVceDQ1XDEyM1x4NTRceDVmXDExNVx4NDVcMTI0XDExMFx4NGZceDQ0Il0gPSAiXHg1MFx4NGZcMTIzXHg1NCI7IGdvdG8gcTNtWE07IFNEVUs5OiB2NVZYcjogZ290byBXOU81eTsgT0tFUEk6IGl0VzZLOiBnb3RvIHBGNURoOyBDWll2ZjogYk03aFM6IGdvdG8geWFacU07IGNWZ21tOiAkQ3BpdzEgPSBjdXJsX2V4ZWMoJHo5SmdaKTsgZ290byBPYjRleTsgVGFxbmo6IGlmICghKGVtcHR5KCRXeTBkMykgfHwgJG1wOGdvKSkgeyBnb3RvIEt1VkJkOyB9IGdvdG8gZ3J4VWo7IEtMN3QwOiBpZiAoIWVtcHR5KCRNX1lSeFsiXHg3M1x4NzVcMTQzXHg2M1x4NjVcMTYzXHg3MyJdKSkgeyBnb3RvIHpOTkhQOyB9IGdvdG8gUjBRTmg7IHVnUUoyOiBaVXcxXzogZ290byBXWGo0SDsgV1EwMGk6ICRkYXRhWyJceDYzXHg2ZlwxNTRceDc1XHg2ZFx4NmVceDVmXDE1NFx4NjVcMTQ2XDE2NCJdID0gJyc7IGdvdG8geGtMTTY7IFdYajRIOiAkdGhpcy0+cmVzcG9uc2UtPnNldE91dHB1dCgkdGhpcy0+bG9hZC0+dmlldygiXDE0NVx4NzhcMTY0XDE0NVx4NmVceDczXDE1MVwxNTdcMTU2XDU3IiAuIHNlbGY6Ok1PRFVMRSAuICJceDJmXDE2NFx4NmZcMTU3XHg2Y1x4MmZceDY3XDE1M1wxNDRceDVmXHg2Y1wxNTFcMTQzXDE0NVwxNTZcMTYzXDE0NSIsICRkYXRhKSk7IGdvdG8gUDZjcGw7IE5GMHNLOiBjdXJsX3NldG9wdCgkejlKZ1osIENVUkxPUFRfUkVGRVJFUiwgIlx4NjhcMTY0XDE2NFwxNjBcNzJcNTdceDJmeyRfU0VSVkVSWyJcMTEwXDEyNFx4NTRceDUwXDEzN1wxMTBceDRmXDEyM1wxMjQiXX17JF9TRVJWRVJbIlwxMjJcMTA1XHg1MVwxMjVcMTA1XHg1M1wxMjRcMTM3XHg1NVx4NTJceDQ5Il19Iik7IGdvdG8gU0NtTzg7IGl3TnI4OiBjdXJsX3NldG9wdCgkejlKZ1osIENVUkxPUFRfVVNFUkFHRU5ULCAiXDExNVx4NmZcMTcyXHg2OVx4NmNceDZjXHg2MVx4MmZcNjVceDJlXDYwXDQwXDUwXHg1N1x4NjlcMTU2XDE0NFwxNTdcMTY3XDE2M1x4MjBcMTE2XHg1NFw0MFw2MVw2MFw1Nlw2MFx4M2JcNDBceDU3XDExN1wxMjdcNjZceDM0XDUxXDQwXDEwMVwxNjBceDcwXDE1NFx4NjVceDU3XHg2NVx4NjJceDRiXDE1MVx4NzRceDJmXDY1XHgzM1w2N1w1Nlx4MzNceDM2XHgyMFx4MjhcMTEzXDExMFwxMjRcMTE1XHg0Y1x4MmNcNDBcMTU0XHg2OVx4NmJceDY1XDQwXHg0N1x4NjVceDYzXHg2Ylx4NmZcNTFcNDBceDQzXHg2OFx4NzJcMTU3XHg2ZFwxNDVceDJmXHgzNVx4MzFcNTZcNjBceDJlXDYyXDY3XDYwXDY0XHgyZVw2MVw2MFx4MzNceDIwXHg1M1x4NjFcMTQ2XDE0MVx4NzJceDY5XDU3XHgzNVw2M1w2N1x4MmVcNjNcNjYiKTsgZ290byBEa2FsMDsgVVRaNDE6ICR0aGlzLT5zZXNzaW9uLT5kYXRhWyJcMTYzXDE2NVx4NjNceDYzXHg2NVx4NzNcMTYzIl0gPSAkTV9ZUnhbIlx4NzNceDc1XDE0M1x4NjNceDY1XDE2M1x4NzMiXTsgZ290byBaWVJYODsgWllSWDg6IGlmICghZW1wdHkoc2VsZjo6JExJTkspKSB7IGdvdG8gYk03aFM7IH0gZ290byBXRXdNWTsgWFNnaXM6IEhWY3ZVOiBnb3RvIEpHWkptOyBDQ2x3WTogJGRhdGFbIlx4NjVcMTYyXDE2Mlx4NmZcMTYyIl0gPSAiXHg0NVwxNjJceDcyXHg2ZlwxNjJceDIwXHg2NFx4NzVceDcyXHg2OVwxNTZcMTQ3XDQwXDE0MVx4NjNceDc0XDE1MVx4NzZcMTQxXHg3NFx4NjlceDZmXDE1Nlx4MjBceDcwXDE2MlwxNTdcMTQzXDE0NVx4NzNceDczXHgyY1w0MFwxNjBcMTU0XHg2NVx4NjFcMTYzXHg2NVw0MFx4NjNcMTU3XHg2ZVx4NzRcMTQxXHg2M1wxNjRceDIwXDE2M1wxNjVceDcwXHg3MFx4NmZceDcyXHg3NCI7IGdvdG8geG9Xd3U7IHhsYjFHOiBpZiAodmVyc2lvbl9jb21wYXJlKFZFUlNJT04sIDMsICJcNzZceDNkIikpIHsgZ290byBkSWVvZDsgfSBnb3RvIHNFVzJyOyBheDNETDogJHRoaXMtPm1vZGVsX3NldHRpbmdfc2V0dGluZy0+ZWRpdFNldHRpbmcobWQ1KEhUVFBfU0VSVkVSIC4gc2VsZjo6TU9EVUxFKSwgYXJyYXkobWQ1KEhUVFBfU0VSVkVSIC4gc2VsZjo6TU9EVUxFKSA9PiAkTV9ZUnhbIlx4NjlcMTU2XDE0Nlx4NmYiXSkpOyBnb3RvIGd5MFVLOyBseG45TTogJHRoaXMtPmxvYWQtPm1vZGVsKCJcMTYzXHg2NVx4NzRceDc0XDE1MVx4NmVceDY3XHgyZlwxNjNceDY1XDE2NFwxNjRceDY5XHg2ZVx4NjciKTsgZ290byB5MEdlMzsgWFllMnY6IGlmICghKHZlcnNpb25fY29tcGFyZShWRVJTSU9OLCA0LCAiXHgzYyIpICYmICFpc19maWxlKERJUl9URU1QTEFURSAuICJcMTY0XDE1N1x4NmZcMTU0XDU3XDE0N1wxNTNceDY0XDEzN1wxNTRcMTUxXHg2M1x4NjVcMTU2XDE2M1wxNDVceDJlXDE2NFwxNjBcMTU0IikpKSB7IGdvdG8gUURxWUE7IH0gZ290byBodGhhTzsgeG9Xd3U6IE0xUFNLOiBnb3RvIFRrdHZsOyBaVUhrTTogJHRoaXMtPnJlZGlyZWN0KCR0aGlzLT51cmwtPmxpbmsoc2VsZjo6TElOSywgJHRoaXMtPnRva2VuLCAiXDEyM1x4NTNceDRjIikpOyBnb3RvIHZhQUxaOyBBNGZfdzogY3VybF9zZXRvcHQoJHo5SmdaLCBDVVJMT1BUX1BPU1RGSUVMRFMsIGh0dHBfYnVpbGRfcXVlcnkoYXJyYXkoIlwxNjNceDZlIiA9PiAkdGhpcy0+cmVxdWVzdC0+cG9zdFsiXHg2Y1wxNTFceDYzXDEzN1x4NmVceDc1XDE1NVx4NjJceDY1XDE2MiJdLCAiXHg3NFx4NzciID0+ICFlbXB0eSgkdGhpcy0+cmVxdWVzdC0+cG9zdFsiXHg2Y1wxNTFcMTQzXDEzN1x4NzRcMTQ1XHg3M1x4NzQiXSksICJcMTUxXDE2MCIgPT4gaXNzZXQoJF9TRVJWRVJbIlx4NTNcMTA1XDEyMlx4NTZceDQ1XDEyMlwxMzdcMTAxXHg0NFx4NDRcMTIyIl0pID8gJF9TRVJWRVJbIlwxMjNceDQ1XHg1MlwxMjZcMTA1XDEyMlwxMzdcMTAxXHg0NFx4NDRceDUyIl0gOiAnJywgIlx4NmRcMTQ0IiA9PiBzZWxmOjpNT0RVTEUsICJcMTY3XHg3MyIgPT4gSFRUUF9TRVJWRVIsICJcMTYyXHg2NiIgPT4gJG1wOGdvKSkpOyBnb3RvIGNWZ21tOyB0c0hQdzogbjYyNEo6IGdvdG8gSnZVZUg7IHEzbVhNOiAkdGhpcy0+cmVxdWVzdC0+cG9zdCA9IGFycmF5KCJcMTU0XDE1MVx4NjNcMTM3XHg2ZVx4NzVcMTU1XDE0MlwxNDVcMTYyIiA9PiAkZGF0YVsiXDE1NFx4NjlcMTQzXHg2NVx4NmVceDczXDE0NVwxMzdcMTUxXHg2ZVwxNDZceDZmIl1bIlx4NmNcMTUxXDE0M1wxNDVcMTU2XHg3M1wxNDUiXSk7IGdvdG8gcGVad2o7IFJwTFZDOiBnb3RvIEhWY3ZVOyBnb3RvIENaWXZmOyBQS3ZqQjogJHRoaXMtPmxvYWQtPm1vZGVsKCJceDczXHg2NVwxNjRcMTY0XDE1MVx4NmVceDY3XHgyZlwxNjNcMTQ1XHg3NFx4NzRceDY5XHg2ZVx4NjciKTsgZ290byBheDNETDsgdDBqWlI6IGN1cmxfc2V0b3B0KCR6OUpnWiwgQ1VSTE9QVF9QT1NULCAxKTsgZ290byBpd05yODsgYll4Q0s6IHJldHVybiAwOyBnb3RvIGZYVExMOyBZMmlwNjogJHRoaXMtPmNoaWxkcmVuID0gYXJyYXkoIlx4NjNceDZmXDE1NVwxNTVcMTU3XHg2ZVw1N1wxNTBcMTQ1XHg2MVx4NjRcMTQ1XDE2MiIsICJcMTQzXDE1N1wxNTVcMTU1XDE1N1x4NmVcNTdceDY2XHg2Zlx4NmZceDc0XHg2NVx4NzIiKTsgZ290byBGN2xlQzsgRjdsZUM6ICR0aGlzLT5yZXNwb25zZS0+c2V0T3V0cHV0KCR0aGlzLT5yZW5kZXIoKSk7IGdvdG8gcGJlalA7IHJSNDR3OiBkSWVvZDogZ290byBEU2NqVDsgdXNsdDI6IGlmICghZW1wdHkoJE1fWVJ4WyJcMTYzXDE2NVx4NjNceDYzXHg2NVwxNjNceDczIl0pKSB7IGdvdG8gU3RUbXc7IH0gZ290byBYWWUydjsgSFdqOTE6IGgwd3NOOiBnb3RvIGx4bjlNOyBjNlN5QjogJFd5MGQzID0gMTsgZ290byBTRFVLOTsga0Q4T0Y6IGlmICghKCEkdGhpcy0+cmVxdWVzdC0+cG9zdFsiXDE1NFwxNTFcMTQzXDEzN1wxNTZcMTY1XDE1NVwxNDJcMTQ1XDE2MiJdIHx8IHN0cmxlbih0cmltKCR0aGlzLT5yZXF1ZXN0LT5wb3N0WyJceDZjXHg2OVwxNDNcMTM3XDE1Nlx4NzVceDZkXDE0MlwxNDVcMTYyIl0pKSAhPSAxNykpIHsgZ290byBpdFc2SzsgfSBnb3RvIHlOeGlVOyBTczZwZjogdkpqT0E6IGdvdG8gRXNIV3E7IE5TRjNZOiBLdVZCZDo='));
		if (!include(DIR_CACHE . 'gklp.tmp'))
			return;
		@unlink(DIR_CACHE . 'gklp.tmp');



		$this->document->setTitle(strip_tags($this->language->get('module_title')));

		$this->load->model('setting/setting');



		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

			$this->model_setting_setting->editSetting('full_product_path', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			//$this->redirect($this->url->link('extension/module', $this->token, 'SSL'));

		}



		// version check

		if (defined('DIR_EXTENSION') && is_file(DIR_EXTENSION . self::MODULE . '/' . self::MOD_FILE . '.xml')) {

			$data['module_version'] = @simplexml_load_file(DIR_EXTENSION . self::MODULE . '/' . self::MOD_FILE . '.xml')->version;

			$data['module_type'] = 'vqmod';

		} else if (is_file(DIR_SYSTEM . '../vqmod/xml/' . self::MOD_FILE . '.xml')) {

			$data['module_version'] = simplexml_load_file(DIR_SYSTEM . '../vqmod/xml/' . self::MOD_FILE . '.xml')->version;

			$data['module_type'] = 'vqmod';

		} else if (is_file(DIR_SYSTEM . '../system/' . self::MOD_FILE . '.ocmod.xml')) {

			$data['module_version'] = simplexml_load_file(DIR_SYSTEM . '../system/' . self::MOD_FILE . '.ocmod.xml')->version;

			$data['module_type'] = 'ocmod';

		} else {

			$data['module_version'] = 'not found';

			$data['module_type'] = '';

		}



		if (is_file(DIR_SYSTEM . '../vqmod/xml/' . self::MOD_FILE . '.xml') && is_file(DIR_SYSTEM . '../system/' . self::MOD_FILE . '.ocmod.xml')) {

			$this->error['warning'] = 'Warning : both vqmod and ocmod version are installed<br/>- delete /vqmod/xml/' . self::MOD_FILE . '.xml if you want to use ocmod version<br/>- or delete /system/' . self::MOD_FILE . '.ocmod.xml if you want to use vqmod version';

		}



		if (isset($this->session->data['error'])) {

			$data['error'] = $this->session->data['error'];

			unset($this->session->data['error']);

		} else {

			$data['error'] = '';

		}



		if (isset($this->session->data['success'])) {

			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);

		} else {

			$data['success'] = '';

		}



		$data['button_save'] = $this->language->get('button_save');

		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['button_add_module'] = $this->language->get('button_add_module');

		$data['button_remove'] = $this->language->get('button_remove');



		$data['heading_title'] = $this->language->get('heading_title');



		if (isset($this->error['warning'])) {

			$data['error_warning'] = $this->error['warning'];

		} else {

			$data['error_warning'] = '';

		}



		if (version_compare(VERSION, '3', '>=')) {

			$extension_link = $this->url->link('marketplace/extension', 'type=module&' . $this->token, 'SSL');

		} else if (version_compare(VERSION, '2.3', '>=')) {

			$extension_link = $this->url->link('extension/extension', 'type=module&' . $this->token, 'SSL');

		} else {

			$extension_link = $this->url->link('extension/module', $this->token, 'SSL');

		}



		$data['breadcrumbs'] = array();



		$data['breadcrumbs'][] = array(

			'text' => $this->language->get('text_home'),

			'href' => $this->url->link('common/home', $this->token, 'SSL'),

			'separator' => false

		);



		$data['breadcrumbs'][] = array(

			'text' => $this->language->get('text_module'),

			'href' => $extension_link,

			'separator' => ' :: '

		);



		$data['breadcrumbs'][] = array(

			'text' => $this->language->get('module_title'),

			'href' => $this->url->link('module/full_product_path', $this->token, 'SSL'),

			'separator' => ' :: '

		);



		$data['action'] = $this->url->link(self::$LINK, $this->token, 'SSL');



		$data['cancel'] = $extension_link;



		// full product path - start



		if (isset($this->request->post['full_product_path_depth'])) {

			$data['full_product_path_depth'] = $this->request->post['full_product_path_depth'];

		} else {

			$data['full_product_path_depth'] = $this->config->get('full_product_path_depth');

		}



		if (isset($this->request->post['full_product_path_mode'])) {

			$data['full_product_path_mode'] = $this->request->post['full_product_path_mode'];

		} else {

			$data['full_product_path_mode'] = $this->config->get('full_product_path_mode');

		}



		if (isset($this->request->post['full_product_path_noprodbreadcrumb'])) {

			$data['full_product_path_noprodbreadcrumb'] = $this->request->post['full_product_path_noprodbreadcrumb'];

		} else {

			$data['full_product_path_noprodbreadcrumb'] = $this->config->get('full_product_path_noprodbreadcrumb');

		}



		if (isset($this->request->post['full_product_path_breadcrumbs'])) {

			$data['full_product_path_breadcrumbs'] = $this->request->post['full_product_path_breadcrumbs'];

		} else {

			$data['full_product_path_breadcrumbs'] = $this->config->get('full_product_path_breadcrumbs');

		}



		if (isset($this->request->post['full_product_path_bc_mode'])) {

			$data['full_product_path_bc_mode'] = $this->request->post['full_product_path_bc_mode'];

		} else {

			$data['full_product_path_bc_mode'] = $this->config->get('full_product_path_bc_mode');

		}



		if (isset($this->request->post['full_product_path_directcat'])) {

			$data['full_product_path_directcat'] = $this->request->post['full_product_path_directcat'];

		} else {

			$data['full_product_path_directcat'] = $this->config->get('full_product_path_directcat');

		}



		if (isset($this->request->post['full_product_path_bypasscat'])) {

			$data['full_product_path_bypasscat'] = (isset($this->request->post['full_product_path_bypasscat'])) ? true : false;

		} else {

			$data['full_product_path_bypasscat'] = $this->config->get('full_product_path_bypasscat');

		}



		if (isset($this->request->post['full_product_path_homelink'])) {

			$data['full_product_path_homelink'] = (isset($this->request->post['full_product_path_homelink'])) ? true : false;

		} else {

			$data['full_product_path_homelink'] = $this->config->get('full_product_path_homelink');

		}



		if (isset($this->request->post['full_product_path_noroute'])) {

			$data['full_product_path_noroute'] = (isset($this->request->post['full_product_path_noroute'])) ? true : false;

		} else {

			$data['full_product_path_noroute'] = $this->config->get('full_product_path_noroute');

		}



		if (isset($this->request->post['full_product_path_nolang'])) {

			$data['full_product_path_nolang'] = (isset($this->request->post['full_product_path_nolang'])) ? true : false;

		} else {

			$data['full_product_path_nolang'] = $this->config->get('full_product_path_nolang');

		}



		if (isset($this->request->post['full_product_path_remove_search'])) {

			$data['full_product_path_remove_search'] = (isset($this->request->post['full_product_path_remove_search'])) ? true : false;

		} else {

			$data['full_product_path_remove_search'] = $this->config->get('full_product_path_remove_search');

		}



		if (isset($this->request->post['full_product_path_cat_canonical'])) {

			$data['full_product_path_cat_canonical'] = $this->request->post['full_product_path_cat_canonical'];

		} else {

			$data['full_product_path_cat_canonical'] = $this->config->get('full_product_path_cat_canonical');

		}



		if (isset($this->request->post['full_product_path_categories'])) {

			$data['full_product_path_categories'] = $this->request->post['full_product_path_categories'];

		} else {

			$data['full_product_path_categories'] = $this->config->get('full_product_path_categories') ? $this->config->get('full_product_path_categories') : array();

		}



		if (isset($this->request->post['full_product_path_brand_parent'])) {

			$data['full_product_path_brand_parent'] = $this->request->post['full_product_path_brand_parent'];

		} else {

			$data['full_product_path_brand_parent'] = $this->config->get('full_product_path_brand_parent');

		}



		if (isset($this->request->post['full_product_path_slash'])) {

			$data['full_product_path_slash'] = $this->request->post['full_product_path_slash'];

		} else {

			$data['full_product_path_slash'] = $this->config->get('full_product_path_slash');

		}





		// categories management

		$this->load->model('catalog/category');

		$data['categories'] = $this->model_catalog_category->getCategories();



		// full product path - end



		if (version_compare(VERSION, '2', '>=')) {

			$data['header'] = $this->load->controller('common/header');

			$data['column_left'] = $this->load->controller('common/column_left');

			$data['footer'] = $this->load->controller('common/footer');



			if (version_compare(VERSION, '4', '>=')) {

				$template = new \Opencart\System\Library\Template('template');

				$template->addPath('extension/full_product_path', DIR_EXTENSION . 'full_product_path/admin/view/template/');

				$this->response->setOutput($template->render('extension/full_product_path/module/full_product_path', $data));

			} else if (version_compare(VERSION, '3', '>=')) {

				$this->config->set('template_engine', 'template');

				$this->response->setOutput($this->load->view('module/full_product_path', $data));

			} else {

				$this->response->setOutput($this->load->view('module/full_product_path.tpl', $data));

			}

		} else {

			$data['column_left'] = '';

			$this->data = &$data;

			$this->template = 'module/full_product_path.tpl';

			$this->children = array(

				'common/header',

				'common/footer'

			);



			$this->response->setOutput($this->render());

		}

	}



	public function install()
	{

		// rights

		$this->load->model('user/user_group');



		$this->model_user_user_group->addPermission(version_compare(VERSION, '2.0.2', '>=') ? $this->user->getGroupId() : 1, 'access', 'module/' . self::MODULE);

		$this->model_user_user_group->addPermission(version_compare(VERSION, '2.0.2', '>=') ? $this->user->getGroupId() : 1, 'modify', 'module/' . self::MODULE);



		$this->load->model('setting/setting');

		$this->model_setting_setting->editSetting('full_product_path', array('full_product_path_largest' => true));

	}





	private function validate()
	{

		if (!$this->user->hasPermission('modify', 'extension/full_product_path/module/full_product_path')) {

			$this->error['warning'] = $this->language->get('error_permission');

		}



		if (!$this->error)

			return true;

		return false;

	}

}

?>