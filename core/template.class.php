<?php

#
# TEMPLATE class, v1.0
#

$tpl = new Template;

class Template
{
	var $_vars = array();
	var $_tpl_vars = array();
	var $_template = 'default';
	var $_template_dir = 'templates/';
	var $_content = '';
	var $_tpl = '';
	var $_tag_regex = '/{\s*(.*?)\s*}/s';
	var $_clear_html = false;
	var $_showUndefinedVars = false;
	var $_denied_clearhtml_pages = ['settings'];

	function __construct()
	{
		global $db, $settings;

		$this->_admin_template = $settings->get('admin_templates');
        $this->_template = $settings->get('default_template');
		$this->_template_dir = ISADMIN  ? 'templates/' . $this->_admin_template : 'templates/' . $this->_template;
		$this->_clear_html = !in_array(getUrl(0), $this->_denied_clearhtml_pages) ? $settings->get('clear_html') : false;
		$this->_showUndefinedVars = $settings->get('tpl_show_undef_vars');
	}

	function assign($tplVar, $value = null)
	{
		if(is_array($tplVar))
		{
			foreach($tplVar as $key => $val)
			{
				if($key != '') $this->_vars[$key] = $val;
			}
		}
		else
		{
			if($tplVar) $this->_vars[$tplVar] = $value;
		}
	}

	function display($_file = '', $_display = false)
	{
		global $db, $mcache;

		if($_file == '' AND $this->_tpl == '') { error('Ошибка! Шаблон страницы не подключен'); }
		if(!is_readable($this->_template_dir)) { error('Ошибка! Папка шаблонов не найдена', $this->_template_dir); }
		$_file = ($_file) ? $_file : $this->_tpl;

		$this->_content = file_get_contents($this->_template_dir . '/' . $_file) OR error('Ошибка! Шаблон страницы пустой или не подключен', $_file);
		#$this->_content = $this->translate($this->_content);

		ob_start();
		eval('?>' . $this->compile() . '<?php ');
		$output = ob_get_contents();
		ob_end_clean();

		if($_display) return $output;
		elseif(!$_display && $this->_clear_html == '1') echo $this->clear_html($output);
		else echo $output;
	}

    function __get($key)
    {
        return (isset($this->_vars[$key])) ? $this->_vars[$key] : null;
    }

	function parseStr($_content)
	{
		$this->_content = $_content;

		ob_start();
		eval('?>' . $this->compile() . '<?php ');
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	function load($_file)
	{
		$this->_tpl = $_file;
	}

	function compile()
	{
		$this->_compiled_tpl = preg_replace_callback($this->_tag_regex, array($this, '_CompileTag'), $this->_content);
		return $this->_compiled_tpl;
	}

	function clear_html($_content)
	{
		$_content = preg_replace("/\ +/", ' ', $_content);
		return str_replace('|monolith:rn|', "\r\n", str_replace(array("\n", "\r", "\t"), '', str_replace("\r\n", '|monolith:rn|', $_content)));
	}

	function _compileTag($tag)
	{
		$exp = explode(' ', $tag[1]);
		$operator = array_shift($exp);
		$match = array();

		switch($operator)
		{
			case 'if' :
			{
				if (preg_match('/if (.*)/si', $tag[1], $match))
				{
					return '<?php if(' . $this->_CompileIfConditions($match[1]) . ') {?>';
				}
				else
				{
					return $tag[0];
				}
			}

			case 'code' :
			{
				if (preg_match('/code (.*)/si', $tag[1], $match))
				{
					return '<?php ' . $this->_compileCodeDefinitions($match[1]) . '?>';
				}
				else
				{
					return $tag[0];
				}
			}

			case 'else' :
			{
				return '<?php } else {?>';
			}

			case '/if' :
			{
				return '<?php }?>';
			}

			case 'go' :
			{
				$params = $this->_compileReservedParams($tag[1]);

				if(isset($params['to']))
				{
					return "<?php go('" . preg_replace("!\"|\'|\`!", "", $params['to']) . "'); ?>";
				}
			}

			case 'go301' :
			{
				$params = $this->_compileReservedParams($tag[1]);

				if(isset($params['to']))
				{
					return "<?php go301('" . preg_replace("!\"|\'|\`!", "", $params['to']) . "'); ?>";
				}
			}

			case 'include' :
			{
				$params = $this->_compileReservedParams($tag[1]);

				if(isset($params['file']))
				{
					return "<?php \$this->display('" . preg_replace("!\"|\'|\`!", "", $params['file']) . "'); ?>";
				}
			}

			case 'foreach' :
			{
				$params = $this->_compileReservedParams($tag[1]);

				if (isset($params['name']) && isset($params['source']) && isset($params['key']))
				{
					return "
						<?php
							unset(\$this->_tpl_vars['" . $params["name"] . "']);
							\$this->_tpl_vars['" . $params["name"] . "'] = array();
							\$this->_tpl_vars['" . $params["name"] . "']['iteration'] = 0;

							foreach(" . $this->_compileVar($params["source"]) . " as \$this->_vars['" . $params["key"] . "'] => \$this->_vars['" . $params["name"] . "'])
							{
								\$this->_tpl_vars['" . $params["name"] . "']['iteration']++;
						?>
					";
				}

				if (isset($params['name']) && isset($params['source']) && !isset($params['key']))
				{
					return "
						<?php
							unset(\$this->_tpl_vars['" . $params["name"] . "']);
							\$this->_tpl_vars['" . $params["name"] . "'] = array();
							\$this->_tpl_vars['" . $params["name"] . "']['iteration'] = 0;

							foreach(" . $this->_compileVar($params["source"]) . " as \$this->_vars['" . $params["name"] . "'])
							{
								\$this->_tpl_vars['" . $params["name"] . "']['iteration']++;
						?>
					";
				}

				return $tag[1];
			}

			case '/foreach' :
			{
				return '<?php } ?>';
			}

			case 'loop' :
			{
				$params = $this->_CompileReservedParams($tag[1]);
				if (isset($params['name']) && isset($params['source']))
				{
					return "<?php unset(\$this->_tpl_vars['" . $params["name"] . "']); for(\$this->_tpl_vars['" . $params["name"] . "']['iteration'] = 1; \$this->_tpl_vars['" . $params["name"] . "']['iteration'] <= " . $this->_compileVar($params["source"]) . "; \$this->_tpl_vars['" . $params["name"] . "']['iteration']++) { ?>";
				}

				return $tag[1];
			}

			case '/loop' :
			{
				return '<?php } ?>';
			}

			case 'lq' :
			{
				return '{';
			}

			case 'rq' :
			{
				return '}';
			}

			case 'php' :
			{
				return '<?php ';
			}

			case '/php' :
			{
				return '?>';
			}

			default :
			{
				if (preg_match('/^\\#(.*)/si', $operator, $match))
				{
					$temp = $this->_compileVar($match[1]);

					if($this->_showUndefinedVars == true)
					{
						return '<?php echo isset(' . $temp . ')?' . $temp . ':"{#' . $match[1] . '}"; ?>';
					}
					else
					{
						return '<?php echo isset(' . $temp . ')?' . $temp . ':""; ?>';
					}
				}

				return $tag[0];
			}
		}
	}

	function _compileReservedParams($str = '')
	{
		$params = array();
		$_tmp = explode(' ', $str);

		foreach ($_tmp as $value)
		{
			$_tmp2 = explode('=', $value);
			if (isset($_tmp2[1]))
			{
				$params[$_tmp2[0]] = preg_replace('/^\\#/', '', $_tmp2[1]);
			}
		}

		return $params;
	}

	function _compileVar($var = '')
	{
		$match = array();

		if (isset($this->_vars[$var]))
		{
			$_return = "\$this->_vars['" . $var . "']";
		}
		elseif(preg_match("!(\w+)\.(\w+)\.(\w+)\.(\w+)!i", $var, $match))
		{
			$_return .= "\$this->_vars['" . $match[1] . "']['" . $match[2] . "']['" . $match[3] . "']['" . $match[4] . "']";
		}
		elseif(preg_match("!tpl\.(\w+)\.(\w+)!i", $var, $match))
		{
			$_return .= "\$this->_tpl_vars['" . $match[1] . "']['" . $match[2] . "']";
		}
		elseif(preg_match("!(\w+)\.(\w+)\.(\w+)!i", $var, $match))
		{
			$_return .= "\$this->_vars['" . $match[1] . "']['" . $match[2] . "']['" . $match[3] . "']";
		}
		elseif(preg_match("!tpl\.(\w+)\.(\w+)\.(\w+)!i", $var, $match))
		{
			$_return .= "\$this->_tpl_vars['" . $match[1] . "']['" . $match[2] . "']['" . $match[3] . "']";
		}
		elseif(preg_match("!(\w+)\.(\w+)!i", $var, $match))
		{
			$_return .= "\$this->_vars['" . $match[1] . "']['" . $match[2] . "']";
		}
		elseif(preg_match("!(\w+)\.(\w+)\.(\w+)!i", $var, $match))
		{
			$_return .= "\$this->_vars['" . $match[1] . "']['" . $match[2] . "']['" . $match[3] . "']";
		}
		else
		{
			$_return .= "\$this->_vars['" . $var . "']";
		}

		return $_return;
	}

	function _CompileIfConditions($str = "")
	{
		$str = ltrim($str);
		$output = "";
		$match = array();

		if (preg_match("/^\"(.*?)\"(.*)/si", $str, $match))
		{
			$output .= "\"" . $this->_CompileIfConditions($match[1]) . "\"" . $this->_CompileIfConditions($match[2]);
		}
		elseif (preg_match("/^'(.*?)'(.*)/si", $str, $match))
		{
			$output .= "'" . $this->_CompileIfConditions($match[1]) . "'" . $this->_CompileIfConditions($match[2]);
		}
		elseif (preg_match("/^isset\((.*)\)(.*)/s", $str, $match))
		{
			$output .= "isset(" . $this->_CompileIfConditions($match[1]) . ")" . $this->_CompileIfConditions($match[2]);
		}
		elseif (preg_match("/^\((.*)\)(.*)/s", $str, $match))
		{
			$output .= "(" . $this->_CompileIfConditions($match[1]) . ")" . $this->_CompileIfConditions($match[2]);
		}
		elseif (preg_match("/^sizeof\((.*)\)(.*)/s", $str, $match))
		{
			$output .= "sizeof(" . $this->_CompileIfConditions($match[1]) . ")" . $this->_CompileIfConditions($match[2]);
		}
		elseif (preg_match("/^in_array\((.*),(.*)\)(.*)/s", $str, $match))
		{
			$output .= "in_array(" . $this->_CompileIfConditions($match[1]) . ", " . $this->_CompileIfConditions($match[2]) . ")" . $this->_CompileIfConditions($match[3]);
		}
		elseif (preg_match("/^is_numeric\((.*)\)(.*)/s", $str, $match))
		{
			$output .= "is_numeric(" . $this->_CompileIfConditions($match[1]) . ")" . $this->_CompileIfConditions($match[2]);
		}
		elseif (preg_match("/^(==|>=|<=|!=|&&|\|\||>|<|%|\+|\.)(.*)/si", $str, $match))
		{
			$output .= " " . $match[1] . " " . $this->_CompileIfConditions($match[2]);
		}
		elseif (preg_match("/^\!(.*)/s", $str, $match))
		{
			$output .= "!" . $this->_CompileIfConditions($match[1]);
		}
		elseif (preg_match("/^\\#([a-zA-Z0-9._]*)(.*)/si", $str, $match))
		{
			$output .= $this->_CompileVar($match[1]) . $this->_CompileIfConditions($match[2]);
		}
		else
		{
			return $str;
		}

		return $output;
	}

	function _CompileCodeDefinitions($str = '')
	{
		$output = '';
		$str = ltrim($str);
		$match = array();

		if (preg_match("/^\"(.*?)\"(.*)/si", $str, $match))
		{
			$output .= "\"" . $this->_CompileCodeDefinitions($match[1]) . "\"" . $this->_CompileCodeDefinitions($match[2]);
		}
		else if (preg_match("/^\\#([a-zA-Z0-9._]*) = str_replace\((.*), (.*), (.*)\)(.*)/si", $str, $match))
		{
			$output .= "\$this->_vars['" . $match[1] . "'] = str_replace(".$match[2].", ".$match[3].", ".$this->_CompileCodeDefinitions($match[4]).")" . $this->_CompileCodeDefinitions($match[5]);
		}
		else if (preg_match("/^\\#([a-zA-Z0-9._]*) = count\((.*)\)(.*)/si", $str, $match))
		{
			$output .= "\$this->_vars['" . $match[1] . "'] = count(".$this->_CompileCodeDefinitions($match[2]).")" . $this->_CompileCodeDefinitions($match[5]);
		}
		else if (preg_match("/^\\#([a-zA-Z0-9._]*) = (.*)/si", $str, $match))
		{
			$output .= "\$this->_vars['" . $match[1] . "'] = " . $this->_CompileCodeDefinitions($match[2]);
		}
		else if(preg_match("/^intval\((.*)\)(.*)/s", $str, $match))
		{
			$output .= "intval(" . $this->_CompileIfConditions($match[1]) . ")" . $this->_CompileIfConditions($match[2]);
		}
		else if(preg_match("/^nicetime\((.*)\)(.*)/s", $str, $match))
		{
			$output .= "niceTime(" . $this->_CompileIfConditions($match[1]) . ")" . $this->_CompileIfConditions($match[2]);
		}
		else if(preg_match("/^plural\((.*)\)(.*)/s", $str, $match))
		{
			$output .= "plural(" . $this->_CompileIfConditions($match[1]) . ")" . $this->_CompileIfConditions($match[2]);
		}
		else if(preg_match("/^(.*)\((.*)\)(.*)/s", $str, $match))
		{
			$output .= $this->_CompileIfConditions($match[1]) . "(" . $this->_CompileIfConditions($match[2]) . ")" . $this->_CompileIfConditions($match[3]);
		}
		else if(preg_match("/^ceil\((.*)\)(.*)/s", $str, $match))
		{
			$output .= "ceil(" . $this->_CompileIfConditions($match[1]) . ")" . $this->_CompileIfConditions($match[2]);
		}
		else if(preg_match("/^(\/|==|>=|<=|!=|&&|\|\||>|<|%|\+|\.)(.*)/si", $str, $match))
		{
			$output .= " " . $match[1] . " " . $this->_CompileIfConditions($match[2]);
		}
		else if (preg_match("/^(\.)(.*)/si", $str, $match))
		{
			$output .= " " . $match[1] . " " . $this->_CompileIfConditions($match[2]);
		}
		else if (preg_match("/^\\#([a-zA-Z0-9._]*)(.*)/si", $str, $match))
		{
			$output .= $this->_CompileVar($match[1]) . $this->_CompileCodeDefinitions($match[2]);
		}
		else
		{
			return $str;
		}
		return $output;
	}
}

?>
