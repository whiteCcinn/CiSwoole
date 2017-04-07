1.因为要用到module结构,需要用到directory_trigger参数。所以我用Query_String路由模式
application/config/config.php

enable_query_strings = true;

2.修改system/core/Router.php
//	$this->enable_query_strings = ( ! is_cli() && $this->config->item('enable_query_strings') === TRUE);
	$this->enable_query_strings = $this->config->item('enable_query_strings');

3.修改system/core/CodeIgniter.php
// const CI_VERSION = 3.1.3;
defined('CI_VERSION') or define('CI_VERSION','3.1.3');
PS:所有define之前都需要用defined检测是否已定义常量

4.如果需要用到Request_URI的模式的话，需要修改一下system/core/URI.php

	public function __construct()
	{
		$this->config =& load_class('Config', 'core');

		// If query strings are enabled, we don't need to parse any segments.
		// However, they don't make sense under CLI.
		if (is_cli() OR $this->config->item('enable_query_strings') !== TRUE)
		{
			$this->_permitted_uri_chars = $this->config->item('permitted_uri_chars');

			// If it's a CLI request, ignore the configuration
			if (is_cli())
			{
				$uri = $this->_parse_argv();
			}
			else
			{
				$protocol = $this->config->item('uri_protocol');
				empty($protocol) && $protocol = 'REQUEST_URI';

				switch ($protocol)
				{
					case 'AUTO': // For BC purposes only
					case 'REQUEST_URI':
						$uri = $this->_parse_request_uri();
						break;
					case 'QUERY_STRING':
						$uri = $this->_parse_query_string();
						break;
					case 'PATH_INFO':
					default:
						$uri = isset($_SERVER[$protocol])
							? $_SERVER[$protocol]
							: $this->_parse_request_uri();
						break;
				}
			}

			$this->_set_uri_string($uri);
		}

		log_message('info', 'URI Class Initialized');
	}

改成如下：
	public function __construct()
	{
		$this->config =& load_class('Config', 'core');

		// If query strings are enabled, we don't need to parse any segments.
		// However, they don't make sense under CLI.
		if (is_cli() OR $this->config->item('enable_query_strings') !== TRUE)
		{
			$this->_permitted_uri_chars = $this->config->item('permitted_uri_chars');

			// If it's a CLI request, ignore the configuration
//			if (is_cli())
//			{
//				$uri = $this->_parse_argv();
//			}
//			else
//			{
				$protocol = $this->config->item('uri_protocol');
				empty($protocol) && $protocol = 'REQUEST_URI';

				switch ($protocol)
				{
					case 'AUTO': // For BC purposes only
					case 'REQUEST_URI':
						$uri = $this->_parse_request_uri();
						break;
					case 'QUERY_STRING':
						$uri = $this->_parse_query_string();
						break;
					case 'PATH_INFO':
					default:
						$uri = isset($_SERVER[$protocol])
							? $_SERVER[$protocol]
							: $this->_parse_request_uri();
						break;
				}
//			}

			$this->_set_uri_string($uri);
		}

		log_message('info', 'URI Class Initialized');
	}

4.由于缓冲等级的问题,会迭代输出.修改system/core/Loader.php
//		if (ob_get_level() > $this->_ci_ob_level + 1)
		if (1 > 0)