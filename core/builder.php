<?php

$builder = new class
{
	function __construct()
	{
		global $db, $tpl, $settings, $structure, $user;

		saveUtm();
        modeWWW('remove');
        modeHTTPS();
		if(getUrl(0) == 'media' && getUrl(1) == 'compile') $this->mediaCompile();

		$tpl->assign(clearArray([
            'host' => HOST,
			'uri' => $structure->_uri,
			'url' => URI,
			'path' => explode('?', URI),
			'structure' => $structure->getList(),
			'settings' => $settings->get(),
			'cookie' => $_COOKIE,
			'time' => time(),
			'subdomain' => SUBDOMAIN,
			'i' => ISADMIN ? $settings->get('site_url') . $tpl->_template_dir . '/assets' : null,
			'token' => TOKEN,
		]));
	}

	function initialization()
	{
		global $db, $tpl, $user, $structure, $settings;

		$_query = "SELECT * FROM {$db->_structure} WHERE url='{$structure->_uri}' AND status='publish'";
		$_entry = $db->find($_query);
		$_nums = $db->num($_query);

		$this->_pageid = $_entry['id'];
		$this->_module = explode(',', $_entry['module']);
		$_isPermission = $user->checkPermission($this->_pageid, $db);
		$_isPageHas = getUrl(0) ? $structure->hasPageByUrl(safe('/'.getUrl(0).'/')) : true;

		if($_isPermission['close'] == 1 || !$_isPageHas)
		{
			header('HTTP/1.0 404 Not found');
			$tpl->load('404.tpl');
			return;
		}

		if($_isPageHas)
		{
			if(
				$_nums > 0 AND $_entry['isAuth'] AND $_isPermission['auth'] == 0 AND $_isPermission['close'] == 0 OR
				$_nums > 0 AND !$_entry['isAuth'] AND $_isPermission['close'] == 0 OR
				$_nums > 0 AND $_entry['isAuth'] AND $_isPermission['auth'] == 0 AND $_isPermission['close'] == 0
			){
	 			$tpl->assign(clearArray(array
	 			(
					'meta' => clearArray([
						'header' => $_entry['header'],
						'title'	=> $_entry['title'],
						'description' => $_entry['description'],
						'keywords' => $_entry['keywords'],
					])
				)));

				$tpl->load($_entry['template'] . '.tpl');

				if(!$_entry['module']) return false;
				return $this->_module;
			}
			elseif(
				$_entry['isAuth'] AND $_isPermission['auth'] == 1 AND $_isPermission['close'] == 0 OR
				$_entry['isAuth'] AND $_isPermission['close'] == 0
			){
				$tpl->display('auth.tpl');
				exit;
			}
		}

		return false;
	}

	function error404()
	{
		global $tpl;

		header('HTTP/1.0 404 Not found');
		$tpl->display('404.tpl');
		exit;
	}

	function mediaCompile()
	{
        global $db;

		$_site = safe(getUrl(2));
        $_type = safe(getUrl(4));

		if($_site == 'show')
		{
			$_show_id = safe(getUrl(3));
			$_file = safe(getUrl(5));
			$_types = [
                'header' => ['width' => 1280, 'type' => 'scale', 'option' => ['show_and_save']],
                'event' => ['width' => 350, 'type' => 'scale', 'option' => ['show_and_save']],
                'gallery' => ['height' => 530, 'type' => 'scale', 'option' => ['show_and_save']],
                'gallery-preview' => ['auto' => 250, 'type' => 'square', 'option' => ['show_and_save']],
            ];

			$_file_physic = ROOT . "media/source/{$_site}/{$_show_id}/{$_file}";
			$_save_to_dir = ROOT . "media/compile/{$_site}/{$_show_id}/{$_type}";
			$_save_to = "{$_save_to_dir}/{$_file}";
		}

		if($_site == 'actor')
		{
			$_actor_id = safe(getUrl(3));
			$_file = safe(getUrl(5));
			$_types = [
                'photo' => ['width' => 350, 'type' => 'scale', 'option' => ['show_and_save']],
            ];

			$_file_physic = ROOT . "media/source/{$_site}/{$_actor_id}/{$_file}";
			$_save_to_dir = ROOT . "media/compile/{$_site}/{$_actor_id}/{$_type}";
			$_save_to = "{$_save_to_dir}/{$_file}";
		}

		if($_site == 'people')
		{
			$_people_id = safe(getUrl(3));
			$_file = safe(getUrl(5));
			$_types = [
                'avatar' => ['auto' => 650, 'type' => 'square', 'option' => ['show_and_save']],
                'sign' => ['height' => 150, 'type' => 'scale', 'option' => ['show_and_save']],
                'gallery' => ['height' => 530, 'type' => 'scale', 'option' => ['show_and_save']],
                'gallery-preview' => ['auto' => 250, 'type' => 'square', 'option' => ['show_and_save']],
            ];

			$_file_physic = ROOT . "media/source/{$_site}/{$_people_id}/{$_file}";
			$_save_to_dir = ROOT . "media/compile/{$_site}/{$_people_id}/{$_type}";
			$_save_to = "{$_save_to_dir}/{$_file}";
		}

		if($_types[$_type])
		{
            $oldmask = umask(0);
    		mkdir($_save_to_dir, 0777, true);
            umask($oldmask);

			saveImage($_file_physic, $_save_to, $_types[$_type]);
			exit;
		}
	}
};


function css($_files)
{
    minify($_files, 'css');
}

function js($_files)
{
    minify($_files, 'js');
}

function minify($_files, $_type)
{
    global $settings;

    $_content = '';
    $_files = clearArray($_files);
    $_file = md5(implode(',', $_files));
    $_file_minify = "/static/c/{$_file}.{$_type}";
    $_file_minify_updated = filemtime(ROOT . $_file_minify);
    $_updated = 0;

    if($settings->_debug_mode)
    {
        foreach($_files AS $_f) {
            if(is_file($_f)) $_file_updated = filemtime(ROOT . $_f);
            if($_file_updated > $_updated) $_updated = $_file_updated;
        }

        if($_file_minify_updated < $_updated)
        {
            foreach($_files AS $_f)
            {
                if($_type == 'js') $_content .= minify_js(file_get_contents($_f));
                if($_type == 'css') $_content .= minify_css(file_get_contents($_f));
            }

            file_put_contents(ROOT . $_file_minify, $_content);
        }
    }
    else
    {
        $_updated = $_file_minify_updated;
    }

    if($_type == 'js') echo "<script src='{$_file_minify}?{$_updated}'></script>";
    if($_type == 'css') echo "<link rel='stylesheet' href='{$_file_minify}?{$_updated}'/>";
}

function minify_css($input)
{
    # spaces
    $input = preg_replace("/\s{2,}/", " ", $input);
    $input = str_replace("\n", " ", $input);
    $input = str_replace('@CHARSET "UTF-8";', "", $input);
    $input = str_replace(', ', ",", $input);

    # comments
    $input = preg_replace("/(\/\*[\w\'\s\r\n\*\+\,\"\-\.]*\*\/)/", " ", $input);

    return $input;
}

function minify_js($input)
{
    include_once(ROOT . 'core/etc/minify.php');

    $input = Minifier::minify($input) . ';';
    $input = str_replace("\n", " ", $input);
    $input = str_replace(', ', ",", $input);

    return $input;
}
