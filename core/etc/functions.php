<?php

	foreach($_GET as $k => $v) $_GET[$k] = safevar($v);
	foreach($_POST as $k => $v)
	{
		if(is_array($v)) foreach($v as $vk => $vv) $v[$vk] = escapevar($vv);
		else $_POST[$k] = escapevar($v);
	}
	foreach($_COOKIE as $k => $v) $_COOKIE[$k] = escapevar($v);

	function error2log($header, $description = '')
	{
		$_file = ROOT . 'core/log/error.log';
		$_current = file_get_contents($_file);
		$_current .= date('d.m.Y H:i:s') . " / {$header}: {$description}\n";
		file_put_contents($_file, $_current);
	}

	function message2log($_message)
	{
		$_file = ROOT . 'core/log/message.log';
		$_current = file_get_contents($_file);
		$_current .= date('d.m.Y H:i:s') . " / {$_message}\n";
		file_put_contents($_file, $_current);
	}

	function error404()
	{
		header('HTTP/1.0 404 Not found');
	}

    function parseContacts($_text)
    {
        $_result = '';

        foreach(explode(PHP_EOL, $_text) AS $_line)
        {
            $_line = trim($_line);
            $_line_nummed = preg_replace("/[^0-9]/", "", $_line);

            if(validEmail($_line)) $_result .= "<a href='mailto:{$_line}'>{$_line}</a>" . PHP_EOL;
            elseif(strlen($_line_nummed) == 10) $_result .= "<a href='tel:+7{$_line_nummed}'>{$_line}</a>" . PHP_EOL;
            elseif(strlen($_line_nummed) == 11 && $_line_nummed{0} == 7) $_result .= "<a href='tel:+{$_line_nummed}'>{$_line}</a>" . PHP_EOL;
            elseif(strlen($_line_nummed) == 11 && $_line_nummed{0} == 8) $_result .= "<a href='tel:{$_line_nummed}'>{$_line}</a>" . PHP_EOL;
            else $_result .= $_line . PHP_EOL;
        }

        return $_result;
    }

    function num2str($num)
    {
    	$nul='ноль';
    	$ten=array(
    		array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
    		array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    	);
    	$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    	$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    	$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    	$unit=array( // Units
    		array('копейка' ,'копейки' ,'копеек',	 1),
    		array('рубль'   ,'рубля'   ,'рублей'    ,0),
    		array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
    		array('миллион' ,'миллиона','миллионов' ,0),
    		array('миллиард','милиарда','миллиардов',0),
    	);
    	//
    	list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    	$out = array();
    	if (intval($rub)>0) {
    		foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
    			if (!intval($v)) continue;
    			$uk = sizeof($unit)-$uk-1; // unit key
    			$gender = $unit[$uk][3];
    			list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
    			// mega-logic
    			$out[] = $hundred[$i1]; # 1xx-9xx
    			if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
    			else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
    			// units without rub & kop
    			if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
    		} //foreach
    	}
    	else $out[] = $nul;
    	$out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    	$out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    	return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
    }

    function morph($n, $f1, $f2, $f5)
    {
    	$n = abs(intval($n)) % 100;
    	if ($n>10 && $n<20) return $f5;
    	$n = $n % 10;
    	if ($n>1 && $n<5) return $f2;
    	if ($n==1) return $f1;
    	return $f5;
    }

	function saveUtm()
	{
		if($_GET['utm_source'])
		{
			$_utm = "utm_source={$_GET['utm_source']}";
            if($_GET['utm_medium']) $_utm .= "&utm_medium={$_GET['utm_medium']}";
            if($_GET['utm_campaign']) $_utm .= "&utm_campaign={$_GET['utm_campaign']}";
            if($_GET['utm_content']) $_utm .= "&utm_content={$_GET['utm_content']}";

			cookie('utm', $_utm, (60*60*24*365));
		}

        if(!isset($_COOKIE['ref']) && $_SERVER['HTTP_REFERER'])
        {
            cookie('ref', $_SERVER['HTTP_REFERER'], (60*60*24*365));
        }
	}

	function safevar($var, $sql = false, $strip = false, $xss = true)
	{
        if($xss) $var = xssClean($var); # убираем XSS
		if($strip) $var = htmlentities($var, ENT_QUOTES, 'UTF-8'); # мнемонизировали строку.
		if(get_magic_quotes_gpc()) $var = stripslashes($var); # убрали лишнее экранирование.
		if($sql) $var = mysql_real_escape_string($var); # если нужен MySQL-запрос, то делаем соответствующую очистку.
		if($strip) $var = strip_tags($var); # убираем теги.

		return $var;
	}

	function cookie($_name, $_val, $_expire = 0, $_path = '/', $_domain = false)
	{
		$_expire = $_expire ? time()+$_expire : 0;
		$_domain = !$_domain ? '.' . str_replace('www.', '', HOST) : $_domain;
		$_secure = false;
		// $_httponly = true;

		setcookie($_name, $_val, $_expire, $_path, $_domain, $_secure, $_httponly);
        $_COOKIE[$_name] = $_val;
	}

	function phoneFormat($_phone)
	{
		$_phone = preg_replace("/[^0-9]/", "", $_phone);

		$_len = strlen($_phone);
		$_first = substr($_phone, 0, $_len-7);
		$_first_first = substr($_first, 0, strlen($_first)-3);
		$_first_last3 = substr($_first, -3, $_len);
		$_last7 = substr($_phone, -7, $_len);
		$_last = preg_replace('/([0-9]{3})([0-9]{4})/', '$1 $2', $_last7);

		return "{$_first_first} {$_first_last3} {$_last}";
	}

	function parse_urls($text)
	{
		return preg_replace("#(https?|ftp)://\S+[^\s.,>)\];'\"!?]#",'<a target="_blank" href="\\0">\\0</a>',$text);
	}

	function br2nl($string)
	{
		return preg_replace('#<br\s*?/?>#i', "\n", $string);
	}

	function space2br($string)
	{
		return str_replace(' ', '<br/>', $string);
	}

	function nl2p($string)
	{
		$string = str_replace("\r\n\r\n", '</p><p>', $string);
		return str_replace("\r\n", '<br/>', $string);
	}

	function escapevar($var)
	{
		return htmlentities($var, ENT_QUOTES, 'UTF-8');
	}

	function escape($var)
	{
		$var = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
		return htmlentities($var, ENT_QUOTES, 'UTF-8');
	}

	function safeArray($_array)
	{
		if(is_array($_array))
		{
			foreach($_array AS $r => $v)
			{
				if(trim($v) == '') safevar($_array[$r], true);
			}
		}

		return $_array;
	}

	function clearTags($_str)
	{
		$_result = array();
		foreach(explode(',', $_str) AS $_v)
		{
			if(trim($_v) != '') array_push($_result, trim($_v));
		}

		return implode(',', $_result);
	}

	function clearArray($_array)
	{
		if(is_array($_array))
		{
			foreach($_array AS $r => $v)
			{
				if(!$v && $v != 0 || $v == '') unset($_array[$r]);
			}
		}

		return $_array;
	}

	function array2text($_array)
	{
		$_i = 0;
		$_result = '';
		foreach($_array AS $k => $v)
		{
			$_i++;
			$_result .= "{$k}: {$v}";
			if(sizeof($_array) != $_i) $_result .= ', ';
		}

		return $_result;
	}

	function array2serialize_unique($_array, $_array2)
	{
		$_result = '';
		foreach($_array AS $k => $v)
		{
			if(trim($v) != trim($_array2[$k]))
			{
				$_result[$k] = $v;
			}
		}

		return serialize($_result);
	}

	function array2json_unique($_array, $_array2)
	{
		$_result = '';
		foreach($_array AS $k => $v)
		{
			if(trim($v) != trim($_array2[$k]))
			{
				$_result[$k] = $v;
			}
		}

		return json_encode($_result);
	}

	function numFormat($_num)
	{
		return number_format(intval($_num), 0, ',', ' ');
	}

	function createPassword($length)
	{
		$chars = "234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$i = 0;
		$password = "";
		while ($i < $length)
		{
			$password .= $chars{mt_rand(0,strlen($chars))};
			$i++;
		}
		return $password;
	}

	function niceTime($_time)
	{
	    $_sec = ceil(time() - $_time);
	    $_min = ceil($_sec / 60);
	    $_hour = ceil($_min / 60);
	    $_day = ceil($_hour / 24);
	    $_month = ceil($_day / 30);

		if($_sec < 60) return $_sec . ' ' . plural($_sec, array('секунда', 'секунды', 'секунд')) . ' назад';
		elseif($_min < 60) return $_min . ' ' . plural($_min, array('минута', 'минуты', 'минут')) . ' назад';
		elseif($_hour < 24) return $_hour . ' ' . plural($_hour, array('час', 'часа', 'часов')) . ' назад';
		elseif($_day < 60) return $_day . ' ' . plural($_day, array('день', 'дня', 'дней')) . ' назад';
		elseif($_month < 12) return $_month . ' ' . plural($_month, array('месяц', 'месяца', 'месяцев')) . ' назад';
		else return date('d.m.Y H:i', $_time);
	}

	function plural($n, $forms) # test: один арбуз, два арбуза, пять арбузов
	{
		return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
	}

	function remove_from_array($val, $arr, $preserve = true)
	{
		foreach(array_keys($arr, $val) as $key) { unset($arr[$key]); }
		return ($preserve) ? $arr : array_values($arr);
	}

	function strips($_var)
	{
		if(is_array($_var))
		{
			foreach($_var AS $r => $v) $_var[$r] = stripslashes($v);
		}

		if(is_string($_var))
		{
			$_var = stripslashes($_var);
		}

		return $_var;
	}

	function getSubdomain()
	{
		$_host = explode('.', $_SERVER['HTTP_HOST']);
		$_left = array_pop($_host);
		$_left = array_pop($_host);
		$_phost = implode('.', $_host);
		return $_phost == '' ? false : $_phost;
	}

	function addLastSymbolToUrl()
	{
		$_url0 = $_SERVER['REQUEST_URI'];
		$_url1 = explode('?', $_url0);
		$_url2 = $_url1[0]{strlen($_url1[0])-1};
		$_params = ($_url1[1] == '') ? '' : '?'.$_url1[1];
		$_url = ($_url2 == '/') ? 0 : $_url1[0] .'/'. $_params;

		if($_url) { go301($_url); }
		if(substr_count($_url0, '//')) go301(str_replace('//', '/', $_url0));
	}

	function modeWWW($_mode)
	{
		$_host0 = $_SERVER['HTTP_HOST'];
		$_uri0 = $_SERVER['REQUEST_URI'];
		$_isWWW = substr_count($_host0, 'www.');
		if($_mode == 'add' AND getSubdomain() == false) { go("https://www.{$_host0}{$_uri0}"); }
		if($_mode == 'remove' AND $_isWWW) { go("https://" . str_replace('www.', '', $_host0) . "{$_uri0}"); }
	}

	function modeHTTPS()
	{
		if(!$_SERVER['HTTPS']) go("https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
	}

	function modeHTTP()
	{
		if($_SERVER['HTTPS']) go("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
	}

	function validateURL($url)
	{
		return preg_match('/^(http|https|ftp):\/\/([^:]*:[^@]*@|)(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?/i', $url, $m);
	}

	function validEmail($_email)
	{
		if(!preg_match("/^(?:[a-z0-9]+(?:[+_]?[a-z0-9-.]+)?@[a-z0-9]+(?:\.?[a-z0-9-]+)?\.[a-z]{2,8})$/i", trim($_email))) return false;
		else return true;
	}

	function clearLogin($_login)
	{
		preg_match_all("/[a-z0-9_]/", str_replace(array(' ', '-'), '_', $_login), $m);
		return implode('', $m[0]);
	}

	function clearInt($_int)
	{
		preg_match_all("/[0-9]/", $_int, $m);
		return implode('', $m[0]);
	}

	function checkLogin($_login)
	{
		if(!eregi("^[a-z0-9_]+$", $_login)) return false;
		else return true;
	}

	function getMemUsage()
	{
       if (function_exists('memory_get_usage'))
       {
           return memory_get_usage(1);
       }
       else if ( substr(PHP_OS,0,3) == 'WIN')
       {
           $output = array();
           exec('pslist ' . getmypid() , $output);
           return trim(substr($output[8],38,10));
       }
       else
       {
           return false;
       }
	}

	function wrapByWord($_string, $_swapsize)
	{
		return reset(explode('<br>', wordwrap(mb_substr($_string, 0, $_swapsize+1), $_swapsize, '<br>', false)));
	}

	function getFilesSize($path)
	{
		if(!function_exists('scandir')) return '-1';
	    $fileSize = 0;
	    $dir = scandir($path);

	    foreach($dir as $file)
	    {
	        if (($file!='.') && ($file!='..'))
	            if(is_dir($path . '/' . $file))
	                $fileSize += getFilesSize($path.'/'.$file);
	            else
	                $fileSize += filesize($path . '/' . $file);
	    }

	    return $fileSize;
	}

	function chmod_R($path, $perm)
	{
		$handle = opendir($path);
		while(false !== ($file = readdir($handle)))
		{
			if(($file !== '..'))
			{
				@chmod($path . '/' . $file, $perm);
				if(!is_file($path.'/'.$file) && ($file !== '.'))
				chmod_R($path . '/' . $file, $perm);
			}
		}
		closedir($handle);
	}

	function parseDate($_date)
	{
		# format: 2009-10-08 18:21:03
		$_d0 = explode(' ', $_date);
		$_dDate = explode('-', $_d0[0]);
		$_dTime = explode(':', $_d0[1]);
		return array(
			'Y' => $_dDate[0],
			'm' => $_dDate[1],
			'd' => $_dDate[2],
			'H' => $_dTime[0],
			'i' => $_dTime[1],
			's' => $_dTime[2]
		);
	}

	function getUrl($num = '', $is_max = false, $array = false)
	{
		$_url = $array ? $array : $_GET['url'];
		$_numIndent = ($_url[strlen($_url) - 1] == '/') ? 2 : 1;
		$_values = explode('/', $_url);
		$_max = count($_values) - $_numIndent;

		if($is_max == true) { return ($_values[$_max] == '') ? '/' : $_values[$_max]; }
		else { return $_values[$num]; }
	}

	function ifUrlString($str)
	{
		return (!ereg("[^a-z0-9-]", $str));
	}

    function ts2weekday($_ts)
    {
        $_weekday = date('N', $_ts);

        if($_weekday == 1) $_str = 'Пн';
        if($_weekday == 2) $_str = 'Вт';
        if($_weekday == 3) $_str = 'Ср';
        if($_weekday == 4) $_str = 'Чт';
        if($_weekday == 5) $_str = 'Пт';
        if($_weekday == 6) $_str = "<span class='text-danger'>Сб</span>";
        if($_weekday == 7) $_str = "<span class='text-danger'>Вс</span>";

        return $_str;
    }

    function weekdayToString($_day)
    {
        if($_day == 1) $_str = 'Пн';
        if($_day == 2) $_str = 'Вт';
        if($_day == 3) $_str = 'Ср';
        if($_day == 4) $_str = 'Чт';
        if($_day == 5) $_str = 'Пт';
        if($_day == 6) $_str = 'Сб';
        if($_day == 7) $_str = 'Вс';

        return $_str;
    }

	function getNameMonth($_month)
	{
		if($_month == '01') { $_month = 'января'; }
		elseif($_month == '02') { $_month = 'февраля'; }
		elseif($_month == '03') { $_month = 'марта'; }
		elseif($_month == '04') { $_month = 'апреля'; }
		elseif($_month == '05') { $_month = 'мая'; }
		elseif($_month == '06') { $_month = 'июня'; }
		elseif($_month == '07') { $_month = 'июля'; }
		elseif($_month == '08') { $_month = 'августа'; }
		elseif($_month == '09') { $_month = 'сентября'; }
		elseif($_month == '10') { $_month = 'октября'; }
		elseif($_month == '11') { $_month = 'ноября'; }
		elseif($_month == '12') { $_month = 'декабря'; }

		return $_month;
	}

	function getNameMonthFull($_month)
	{
		if($_month == '01') { $_month = 'Январь'; }
		elseif($_month == '02') { $_month = 'Февраль'; }
		elseif($_month == '03') { $_month = 'Март'; }
		elseif($_month == '04') { $_month = 'Апрель'; }
		elseif($_month == '05') { $_month = 'Май'; }
		elseif($_month == '06') { $_month = 'Июнь'; }
		elseif($_month == '07') { $_month = 'Июль'; }
		elseif($_month == '08') { $_month = 'Август'; }
		elseif($_month == '09') { $_month = 'Сентябрь'; }
		elseif($_month == '10') { $_month = 'Октябрь'; }
		elseif($_month == '11') { $_month = 'Ноябрь'; }
		elseif($_month == '12') { $_month = 'Декабрь'; }

		return $_month;
	}

	function getNameMonthFull2($_month)
	{
		if($_month == '01') { $_month = 'Января'; }
		elseif($_month == '02') { $_month = 'Февраля'; }
		elseif($_month == '03') { $_month = 'Марта'; }
		elseif($_month == '04') { $_month = 'Апреля'; }
		elseif($_month == '05') { $_month = 'Мая'; }
		elseif($_month == '06') { $_month = 'Июня'; }
		elseif($_month == '07') { $_month = 'Июля'; }
		elseif($_month == '08') { $_month = 'Августа'; }
		elseif($_month == '09') { $_month = 'Сентября'; }
		elseif($_month == '10') { $_month = 'Октября'; }
		elseif($_month == '11') { $_month = 'Ноября'; }
		elseif($_month == '12') { $_month = 'Декабря'; }

		return $_month;
	}

	function getNameMonthSimple($_month)
	{
		if($_month == '01') { $_month = 'янв'; }
		elseif($_month == '02') { $_month = 'фев'; }
		elseif($_month == '03') { $_month = 'мар'; }
		elseif($_month == '04') { $_month = 'апр'; }
		elseif($_month == '05') { $_month = 'мая'; }
		elseif($_month == '06') { $_month = 'июн'; }
		elseif($_month == '07') { $_month = 'июл'; }
		elseif($_month == '08') { $_month = 'авг'; }
		elseif($_month == '09') { $_month = 'сен'; }
		elseif($_month == '10') { $_month = 'окт'; }
		elseif($_month == '11') { $_month = 'ноя'; }
		elseif($_month == '12') { $_month = 'дек'; }

		return $_month;
	}

    function timeToReadable($_ts)
    {
        $_date = explode('.', date('d.m.Y', $_ts));
        return "{$_date[0]} " . getNameMonthSimple($_date[1]) . ($_date[2] != date('Y') ? " {$_date[2]}" : '');
    }

    function timeToReadableYear($_ts)
    {
        $_date = explode('.', date('d.m.Y', $_ts));
        return "{$_date[0]} " . getNameMonthSimple($_date[1]) . " {$_date[2]}";
    }

    function timeToReadableMonthYear($_ts)
    {
        $_date = explode('.', date('d.m.Y', $_ts));
        return getNameMonthFull($_date[1]) . ($_date[2] != date('Y') ? " {$_date[2]}" : '');
    }

    function timeToReadableFull($_ts)
    {
        $_date = explode('.', date('d.m.Y', $_ts));
        return "{$_date[0]} " . getNameMonthSimple($_date[1]) . ($_date[2] != date('Y') ? " {$_date[2]}" : '') . ', ' . date('H:i', $_ts);
    }

	function go($url = false)
	{
		if(!$url) { $url = '/'; }
		// $url = str_replace('//', '/', $url);
		$url = safevar($url, false, false, true);
		header('location: ' . $url); exit();
	}

	function go301($url = false)
	{
		if(!$url) { $url = '/'; }
		// $url = str_replace('//', '/', $url);
		$url = safevar($url, false, false, true);
		header('HTTP/1.1 301 Moved Permanently');
		header('location: ' . $url);
		exit();
	}

	function getFileType($_file)
	{
		return strtolower(substr($_file, strrpos($_file, '.')));
	}

	function strtolower_ru($text)
	{
	    $alfavitlover = array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
	    $alfavitupper = array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');

	    return str_replace($alfavitupper, $alfavitlover, strtolower($text));
	}

	function translit($string)
	{
	    $table = array
	    (
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',
			'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
			'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ь' => '', 'ы' => 'y', 'ъ' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', ' ' => '-'
	    );

		$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
		$string = str_replace(array_keys($table), array_values($table), strtolower_ru($string));
	    $string = trim(preg_replace('~[^-a-z0-9]+~u', '', $string), '-');
	    $string = preg_replace('/-+/','-', $string);

		return $string;
	}

	function showImage($source, $options = null)
	{
		header("Cache-Control: private, max-age=2592000, pre-check=2592000");
		header("Pragma: private");
		header("Expires: " . date(DATE_RFC822, strtotime(" 30 day")));

		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime($source)))
		{
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($source)).' GMT', true, 304);
			exit;
		}

		return saveImage($source, null, $options);
	}

	# saveImage('in.png', 'out.png', ['type' => 'scale', 'height' => 900]);
	# saveImage('in.png', 'out.png', ['type' => 'square', 'auto' => 250]);
	function saveImage($source, $saveto, $d)
	{
		list($origWidth, $origHeight, $type) = getimagesize($source);

		switch($type)
		{
			case 1 : $src = imagecreatefromgif($source); break;
			case 2 : $src = imagecreatefromjpeg($source); break;
			case 3 : $src = imagecreatefrompng($source); break;
			default : die('Недопустимый тип файла'); break;
		}

		if($d['type'] == 'square')
		{
			if($origWidth > $origHeight) {
			    $square = $origHeight;
			    $offsetX = ($origWidth - $origHeight) / 2;
			    $offsetY = 0;
			}
			elseif($origHeight > $origWidth) {
			    $square = $origWidth;
			    $offsetX = 0;
			    $offsetY = ($origHeight - $origWidth) / 2;
			}
			else {
			    $square = $origWidth;
			    $offsetX = $offsetY = 0;
			}

			$endSizeX = $endSizeY = $d['auto'];
			$origWidth = $origHeight = $square;
		}

		if($d['type'] == 'scale')
		{
			if($d['auto'])
			{
				$offsetX = $offsetY = 0;

				if($origWidth > $origHeight)
				{
					$endSizeX = $d['auto'];
					$ratio = $origWidth / $endSizeX;
					$endSizeY = $origHeight / $ratio;
				}
				elseif($origHeight > $origWidth)
				{
					$endSizeY = $d['auto'];
					$ratio = $origHeight / $endSizeY;
					$endSizeX = $origWidth / $ratio;
				}
				else
				{
					$endSizeX = $endSizeY = $d['auto'];
				}
			}

			if($d['width'])
			{
				$endSizeX = $d['width'];
				$ratio = $origWidth / $endSizeX;
				$endSizeY = $origHeight / $ratio;
			}

			if($d['height'])
			{
				$endSizeY = $d['height'];
				$ratio = $origHeight / $endSizeY;
				$endSizeX = $origWidth / $ratio;
			}
		}

		$endSizeX = $endSizeX > $origWidth ? $origWidth : $endSizeX;
		$endSizeY = $endSizeY > $origHeight ? $origHeight : $endSizeY;

		$dest = imagecreatetruecolor($endSizeX, $endSizeY);

		if($type == 3)
        {
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
        }
        else
        {
            imagefill($dest, 0, 0, imagecolorallocate($dest, 255, 255, 255));
            imagealphablending($dest, true);
        }


		if(in_array('blur', $d['option']))
		{
			for ($x=1; $x<=35; $x++) imagefilter($src, IMG_FILTER_GAUSSIAN_BLUR);
		}

		imagecopyresampled($dest, $src, 0, 0, $offsetX, $offsetY, $endSizeX, $endSizeY, $origWidth, $origHeight);

		# clean white noise
		// for($y=0; $y<($endSizeY); ++$y)
		// {
		//     for($x=0; $x<($endSizeX); ++$x)
		//     {
		//         $colorat = imagecolorat($dest, $x, $y);
        //
		//         $r = ($colorat >> 16) & 0xFF;
		//         $g = ($colorat >> 8) & 0xFF;
		//         $b = $colorat & 0xFF;
        //
		//         if(($r == 253 && $g == 253 && $b == 253) || ($r == 254 && $g == 254 && $b == 254))
		//         {
		//             imagesetpixel($dest, $x, $y, imagecolorallocate($dest, 255,255,255));
		//         }
		//     }
		// }

        if(in_array('auto_rotate', $d['option']))
        {
            if(function_exists('exif_read_data'))
            {
                $exif = exif_read_data($source);

                if($exif && isset($exif['Orientation']))
                {
                    $orientation = $exif['Orientation'];

                    if($orientation != 1)
                    {
                        $deg = 0;
                        switch($orientation)
                        {
                            case 3: $deg = 180; break;
                            case 6: $deg = 270; break;
                            case 8: $deg = 90; break;
                        }

                        if($deg) $dest = imagerotate($dest, $deg, 0);
                    }
                }
            }
        }

		if(array_key_exists('sign', $d['option']))
		{
            $stamp = imagecreatetruecolor(105, 23);
            imagefilledrectangle($stamp, 0, 0, 150, 70, 0x000000);
            imagestring($stamp, 2, 5, 5, $d['option']['sign']['text'], 0xFFFFFF);
            $sx = imagesx($stamp);
            $sy = imagesy($stamp);

			// imagecopy($dest, $stamp, 30, 30, 0, 0, $sx, $sy); # top left
			// imagecopy($dest, $stamp, 30, (imagesy($dest) - $sy - 30), 0, 0, $sx, $sy); # bottom left
			imagecopy($dest, $stamp, (imagesx($dest) - $sx), (imagesy($dest) - $sy), 0, 0, $sx, $sy); # right bottom
			// imagecopy($dest, $stamp, (imagesx($dest) - $sx - 30), 30, 0, 0, $sx, $sy); # top right
			// imagecopy($dest, $stamp, ((imagesx($dest)/2)-($sx/2)), ((imagesy($dest)/2)-($sy/2)), 0, 0, $sx, $sy); # center center
		}

		imagedestroy($src);

        $_quality = $d['quality'] ? $d['quality'] : 100;

		if($saveto)
		{
            if(in_array('show_and_save', $d['option']))
            {
                if($type == 3)
                {
                    header("Content-type: image/png");
                    imagepng($dest, null);
                }
                else
                {
                    header("Content-type: image/jpeg");
                    imagejpeg($dest, null, $_quality);
                }
            }

            if($type == 3) imagepng($dest, $saveto);
            else imagejpeg($dest, $saveto, $_quality);

            imagedestroy($dest);
		}
		else
		{
            if($type == 3) imagepng($dest, null);
            else imagejpeg($dest, null, $_quality);

			imagedestroy($dest);
			exit;
		}
	}

	function array2xml($root_element_name, $ar)
	{
	    $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$root_element_name}></{$root_element_name}>");
	    $f = create_function('$f,$c,$a','
	            foreach($a as $k=>$v) {
	                if(is_array($v)) {
	                    $ch=$c->addChild($k);
	                    $f($f,$ch,$v);
	                } else {
	                    $c->addChild($k,$v);
	                }
	            }');
	    $f($f,$xml,$ar);
	    return $xml->asXML();
	}

	function object2array($object)
	{
		return @json_decode(@json_encode($object), 1);
	}

    function xssClean($data)
    {
        if(is_array($data) && count($data))
        {
            foreach($data as $k => $v) $data[$k] = xssClean($v);
            return $data;
        }

        if(trim($data) === '') return $data;

        // xss_clean function from Kohana framework 2.3.1
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*w+)[x00-x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        #$data = preg_replace('#(<[^>]+?[x00-x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[x00-x20]*=[x00-x20]*([`\'"]*)[x00-x20]*j[x00-x20]*a[x00-x20]*v[x00-x20]*a[x00-x20]*s[x00-x20]*c[x00-x20]*r[x00-x20]*i[x00-x20]*p[x00-x20]*t[x00-x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[x00-x20]*=([\'"]*)[x00-x20]*v[x00-x20]*b[x00-x20]*s[x00-x20]*c[x00-x20]*r[x00-x20]*i[x00-x20]*p[x00-x20]*t[x00-x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[x00-x20]*=([\'"]*)[x00-x20]*-moz-​binding[x00-x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: exp​ression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[x00-x20]*=[x00-x20]*[`\'"]*.*?exp​ression[x00-x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[x00-x20]*=[x00-x20]*[`\'"]*.*?behaviour[x00-x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[x00-x20]*=[x00-x20]*[`\'"]*.*?s[x00-x20]*c[x00-x20]*r[x00-x20]*i[x00-x20]*p[x00-x20]*t[x00-x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*w+:w[^>]*+>#i', '', $data);

        do
        {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }

        while ($old_data !== $data);

        return $data;
    }

    function reArrayFiles(&$file_post) {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }

?>
