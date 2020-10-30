<?php

	if(!$settings->get('debug_mode')) ini_set('display_errors', 0);
	else
	{
		ini_set('error_reporting', 1);
		ini_set('display_errors', 1);
	}

	function error($header, $description = '', $_prop = false)
	{
		global $settings, $mail;

		if($settings->get('debug_mode'))
		{
			echo "<center><h1>" . $header . "</h1>" . $description . "</center>";
		}
		else
		{
			$_msg = '
				<h2>Error</h2>
				Site: '.$_SERVER['HTTP_HOST'].'<br/>
				Error: '.$header.'<br/>
				Comment: '.htmlspecialchars($description).'<br/>
				Referer: '.$_SERVER['HTTP_REFERER'].'<br/>
				Url: '.$_SERVER['QUERY_STRING'].'<br/>
				IP: ' . $_SERVER['REMOTE_ADDR'];

			$mail->_to = 'alexeytarutin@gmail.com';
			$mail->_message = $_msg;
			$mail->_subject = 'Ошибка с сайта — ' . $_SERVER['HTTP_HOST'];
			$mail->send();
		}

		if($_prop != 'noerror') exit();
	}

	function errorHandlerF($errno, $errstr, $errfile, $errline, $errcontext)
	{
		global $settings, $mail;

		if($errno == E_ERROR || $errno == E_PARSE || $errno == E_CORE_ERROR || $errno == E_COMPILE_ERROR || $errno == E_USER_ERROR)
		{
			$_msg = '
				<h2>Error</h2>
				Site: '.$settings->get('domain').'<br/>
				#.: '.$errno.'<br/>
				Error: '.$errstr.'<br/>
				File: '.$errfile.'<br/>
				Line: '.$errline.'<br/>
				Url: '.$_SERVER['QUERY_STRING'];

			$mail->_to = 'alexeytarutin@gmail.com';
			$mail->_message = $_msg;
			$mail->_subject = 'Ошибка с сайта — ' . $settings->get('domain');
			$mail->send();
		}
		return true;
	}
	if(!$settings->get('debug_mode')) set_error_handler('errorHandlerF');
