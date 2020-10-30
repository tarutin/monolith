<?php

	function renderTime()
	{
	    list($msec, $sec) = explode(chr(32), microtime());
	    return ($sec+$msec);
	}

	if(function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Moscow');
	setlocale(LC_COLLATE, 'ru_RU.UTF-8');
	setlocale(LC_CTYPE, 'ru_RU.UTF-8');
	ini_set('error_log', ROOT . 'core/log/error.log');
	header('Content-type: text/html; charset=utf-8');
	mb_internal_encoding('UTF-8');
	iconv_set_encoding('internal_encoding', 'UTF-8');
	iconv_set_encoding('input_encoding', 'UTF-8');
	iconv_set_encoding('output_encoding', 'UTF-8');
	ob_start('ob_gzhandler');

	define('STARTRENDER', renderTime());
	define('PHPSELF', str_replace('/index.php', '', $_SERVER['PHP_SELF']));
	define('DATE', date('Y-m-d H:i:s'));
	define('URI', $_SERVER['REQUEST_URI']);
	define('REALURI', str_replace(PHPSELF, '', URI));
	define('IP', $_SERVER['HTTP_X_REAL_IP'] ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR']);
	define('REFERER', $_SERVER['HTTP_REFERER']);
	define('HOST', $_SERVER['HTTP_HOST']);
	define('TOKEN', bin2hex(openssl_random_pseudo_bytes(16)));
	define('ISTOKEN', true);

	include_once(ROOT . 'core/etc/functions.php');
	include_once(ROOT . 'core/settings.php');

	define('SUBDOMAIN', getSubdomain() ? getSubdomain() : null);
	define('ADMINURL', $settings->get('backoffice_url'));
	define('ISADMIN', ADMINURL == getUrl(0) ? 1 : 0);

	include_once(ROOT . 'core/mail.php');
	include_once(ROOT . 'core/etc/errors.php');
	include_once(ROOT . 'core/db.php');
	include_once(ROOT . 'core/memcache.php');
	include_once(ROOT . 'core/template.php');
	include_once(ROOT . 'core/users.php');
	include_once(ROOT . 'core/structure.php');
	include_once(ROOT . 'core/builder.php');

	if($builder->initialization()) {
		foreach($builder->_module AS $_module) {
			if($_module) include_once(ROOT . "core/{$_module}.php");
		}
	}

	$tpl->assign('debug', [
		'queryCount' => sizeof($db->_queryList),
        'memoryLimit' => ini_get('memory_limit'),
		'memoryUsage' => round(getMemUsage()/1024/1024, 1), # in Mb
		'renderTime' => substr(renderTime() - STARTRENDER, 0, 5)
	]);

	if($settings->_debug_mode)
	{
		$tpl->assign('queriesList', $db->_queryList);
		if(isset($_GET['debug'])) { $tpl->_clear_html = false; $tpl->display('debug.tpl'); exit; }
		if(isset($_GET['phpinfo'])) { phpinfo(); exit; }
	}

	$tpl->display();
	$db->close();
