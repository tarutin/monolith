<?php

$structure = new class
{
	public $_uri = '';
	public $_modules = array();
	public $_pages = array();
	public $_page = array();
	public $_table_structure = '';
	public $_table_permission = '';
	public $_deniedModules = array('geo', 'memcache', 'mail', 'core', 'tpl', 'builder', 'function', 'db', 'settings', 'template', 'users', 'auth', 'structure', 'trash', 'cache', 'error', 'browser');

	function __construct()
	{
		global $user, $db, $tpl, $settings;

		$this->_uri = $this->getValidUrl(URI);
		$this->_path = array_shift(explode('?', URI)) . '/';
		$this->getPageNames();

		if(ISADMIN)
		{
			$tpl->assign('pageName', $this->getNameById(getUrl(2)));
			if(getUrl(1) == 'structure' || !getUrl(1)) $tpl->assign('modulesList', $this->getModulesList());
		}

		if(ISADMIN) $tpl->assign('numModules', sizeof(explode(',', $this->getModuleById(getUrl(2), $db))));
		if($_POST['action'] == 'addStructureItem' OR $_POST['action'] == 'editStructureItem') die($this->save($user, $db, $settings));
		if($_POST['action'] == 'deleteStructureItem') die($this->go2trash($_POST['sId']));
		if($_POST['action'] == 'getStructureItem') die($this->getItem($db, $_POST['sId']));
		if($_POST['action'] == 'getTplList') die($this->getTemplatesList($settings));
		if($_POST['action'] == 'getSelectedGroups') die($this->getSelectedGroups());
		if($_POST['action'] == 'saveGroups') die($this->saveGroups());
		if($_POST['action'] == 'getChild') die($this->getChild());
		if($_POST['action'] == 'updateStructure') die($this->updatePosition());
		if($_POST['action'] == 'getSubstructure') die($this->getChildJson());
	}

	function getPageNames()
	{
		global $db, $tpl;

		$_query = explode('?', REALURI);
		$i = 0;
		$j = 0;
		$_url = '/';

		foreach(explode('/', trim($this->_uri, '/')) AS $_sysname)
		{
			$_url .= $_sysname . '/';
			$_page = $this->getPageByUrl($_url, $db);
			if(!$_page['name']) continue;
			$tpl->assign('getPage'.$i, $_page['name']);
			$i++;
		}

		foreach(explode('/', trim($_query[0], '/')) AS $_sysname)
		{
			if(!$_sysname) continue;
			$tpl->assign('getUrl'.$j, $_sysname);
			$j++;
		}

		foreach(explode('&', $_query[1]) AS $_val)
		{
			$_x = explode('=', $_val);
			if($_x[0]) $tpl->assign('getUrl'.$_x[0], urldecode($_x[1]));
		}
	}

	function updatePosition()
	{
		global $db, $mcache;

		$this->updatePositionRecursion($_POST['structureList'], 0, -2, $db);
	    $mcache->delete('structure');
	    return;
	}

	function updatePositionRecursion($children, $parent, $level, $db)
	{
		$position = 0;
	    $level++;
	    foreach($children AS $k => $v)
	    {
	        if($level != '-1' && $v['id'] != '1') $db->query("UPDATE {$db->_structure} SET position='{$position}', parent_id='{$parent}', level_id='{$level}' WHERE id='{$v['id']}'") OR die(mysql_error());
	        if($level != '-1') $this->changeParentUrl($parent);
	        if(isset($v['children'][0])) $this->updatePositionRecursion($v['children'], $v['id'], $level, $db);
	   		$position++;
	    }
	}

	function getChild()
	{
		global $db;

		if(empty($_POST['url'])) { $_query = $db->query("SELECT id, name, level_id, parent_id, url, module, template, isHide FROM {$db->_structure} WHERE level_id='-1' AND status='publish' ORDER BY `position` ASC"); }
		else { $_query = $db->query("SELECT id, name, level_id, parent_id, url, module, template, isHide FROM {$db->_structure} WHERE parent_id='{$_POST['url']}' AND status='publish' ORDER BY `position` ASC"); }

		$_pages = array();
		while($_entry = $db->fetchAssoc($_query))
		{
			$_isParent = $db->num("SELECT id FROM {$db->_structure} WHERE parent_id='{$_entry['id']}' AND status='publish'");
			$_entry[] = ($_isParent) ? 1 : 0;
			array_push($_pages, implode('|', $_entry));
		}

		return implode('|#|', $_pages);
	}

	function getChildJson()
	{
		global $db;

		$_query = $db->query("SELECT id, name, level_id, parent_id, url, module, template, isHide FROM {$db->_structure} WHERE parent_id='{$_POST['url']}' AND status='publish' ORDER BY `position` ASC");

		$_pages = array();
		while($_entry = $db->fetchAssoc($_query))
		{
			array_push($_pages, $_entry);
		}

		return json_encode($_pages);
	}

	function getSysnameLevel0()
	{
		global $db;

		$_query = $db->query("SELECT sysname FROM {$db->_structure} WHERE level_id='0'");

		$_pages = array();
		while($_entry = $db->fetchAssoc($_query))
		{
			array_push($_pages, $_entry['sysname']);
		}

		return $_pages;
	}

	function getItem($db, $id)
	{
		$_entries = $db->find("SELECT name, sysname, title, keywords, description, module, template, isAuth, isHide, isClose, id, header FROM {$db->_structure} WHERE id='{$id}'");
		return implode("|", $_entries);
	}

	// function stSettingsGetStructureId($db, $val, $module)
	// {
	// 	$_result = array();
	// 	$val = safe($val);
	// 	$module = safe($module);
	// 	$_setting = $db->query("SELECT structure_id FROM {$db->_structure_settings} WHERE value='{$val}' AND module='{$module}'");
	// 	while($_entries = $db->fetchAssoc($_setting))
	// 	{
	// 		array_push($_result, $_entries['structure_id']);
	// 	}
	// 	return $_result;
	// }

	// function stSettingsGetValue($db, $sId, $module)
	// {
	// 	$sId = intval($sId);
	// 	$module = safe($module);
	// 	$_setting = $db->find("SELECT value FROM {$db->_structure_settings} WHERE structure_id='{$sId}' AND module='{$module}'");
	// 	return $_setting['value'];
	// }
    //
	// function stSettingsSetValue($db, $module, $sId, $value)
	// {
	// 	$module = safe($module);
	// 	$sId = intval($sId);
	// 	$value = safe($value);
	// 	$_POST['id'] = intval($_POST['id']);
	// 	$db->query("DELETE FROM {$db->_structure_settings} WHERE structure_id='{$_POST['id']}' AND module='{$module}'");
	// 	$db->query("INSERT INTO {$db->_structure_settings} (structure_id, module, value) VALUES ('{$sId}', '{$module}', '{$value}')");
	// 	return true;
	// }

	function getValidUrl($_sUri)
	{
		global $db, $settings;
		$_sUriE = explode('?', $_sUri);
		$_sUri = safe($_sUriE[0]);
		$_sUri = ($_sUri != '/') ? substr($_sUri, strlen($settings->get('site_url'))-1) : '/';
		$_sUri = $_sUri[strlen($_sUri)-1] != '/' ? $_sUri.'/' : $_sUri;
		$_sUri = safe($_sUri);
		if($db->num("SELECT id FROM {$db->_structure} WHERE url='{$_sUri}' AND status='publish'") == 1) { return $_sUri; }

		$_query = '/';
		$_urls = explode('/', trim($_sUri, '/'));
		foreach($_urls as $_url)
		{
			array_pop($_urls);
			$_query = safe(str_replace('//', '/', '/'.implode('/', $_urls).'/'));
			$_res = "SELECT id FROM {$db->_structure} WHERE url='{$_query}' AND status='publish' ORDER BY `level_id` DESC LIMIT 1";

			if($db->num($_res) == 1) { return $_query; break; }
		}
	}

	function hasPageByUrl($_url)
	{
		global $db;

		$_url = safe($_url);
		return $db->num("SELECT url FROM {$db->_structure} WHERE url='{$_url}'");
	}

	function getPageidByUrl($_url, $db)
	{
		$_url = safe($_url);
		return $db->value("SELECT id FROM {$db->_structure} WHERE url='{$_url}'");
	}

	function getNameSysnameById($_id, $db)
	{
		$_id = intval($_id);
		$_result = $db->find("SELECT name, sysname FROM {$db->_structure} WHERE id='{$_id}'");
		return array('sysname' => $_result['sysname'], 'name' => $_result['name']);
	}

	function getPageByUrl($_url, $db)
	{
		$_url = safe($_url);
		return $db->find("SELECT * FROM {$db->_structure} WHERE url='{$_url}'");
	}

	function getSysnameLevelById($_id, $db)
	{
		$_id = intval($_id);
		$_result = $db->find("SELECT sysname, level_id, parent_id FROM {$db->_structure} WHERE id='{$_id}'");
		return array('sysname' => $_result['sysname'], 'level_id' => $_result['level_id'], 'parent_id' => $_result['parent_id']);
	}

	function getSysnameById($_id, $db)
	{
		$_id = intval($_id);
		$_result = $db->find("SELECT sysname FROM {$db->_structure} WHERE id='{$_id}'");
		return $_result['sysname'];
	}

	function getIdBySysname($_sysname, $db = null)
	{
        global $db;

		$_sysname = safe($_sysname);
		$_result = $db->find("SELECT id FROM {$db->_structure} WHERE sysname='{$_sysname}'");
		return $_result['id'];
	}

	function getPageurlById($_id, $db)
	{
		$_id = intval($_id);
		$_result = $db->find("SELECT url FROM {$db->_structure} WHERE id='{$_id}'");
		return $_result['url'];
	}

	function getModuleById($_id, $db)
	{
		$_id = intval($_id);
		$_result = $db->find("SELECT module FROM {$db->_structure} WHERE id='{$_id}'");
		return $_result['module'];
	}

	function getNameById($_id)
	{
		global $db;

		$_id = intval($_id);
		return $db->value("SELECT name FROM {$db->_structure} WHERE id='{$_id}'");
	}

	function getNameByUrl($_url)
	{
		global $db;

		$_url = safe($_url);
		return $db->find("SELECT name, sysname FROM {$db->_structure} WHERE url='{$_url}'");
	}

	function getNameBySysname($_id, $db)
	{
		$_id = intval($_id);
		$_result = $db->find("SELECT name FROM {$db->_structure} WHERE sysname='{$_id}'");
		return $_result['name'];
	}

	function getList()
	{
		global $mcache, $user, $db, $settings;

		$_cached = $mcache->get('structure');

		if($_cached[$user->_groupname]) return $_cached[$user->_groupname];
		else
		{
			$this->_pages = array();
			$this->getStructureRecursion($user, $db, $settings);
			$_cached[$user->_groupname] = $this->_pages;
			if($_cached[$user->_groupname]) $mcache->set('structure', $_cached);

			return $this->_pages;
		}
	}

	function getStructureRecursion($user, $db, $settings, $_url = false)
	{
		if(isset($_url))
		{
			$_url = safe($_url);
			$_structureId = $db->find("SELECT id, url FROM {$db->_structure} WHERE url='{$_url}'");
			$_level = $_structureId['id'];
		}
		else { $_level = 0; }

	    $_entries = $db->query("SELECT * FROM {$db->_structure} WHERE parent_id='{$_level}' AND status='publish' ORDER BY `position` ASC");

		$i=0;
		while($_entry = $db->fetchAssoc($_entries))
		{
			$i++;
			$_hide = $db->num("SELECT * FROM {$db->_structure_permission} WHERE structure_id='{$_entry['id']}' AND group_id='{$user->_group_id}' AND action='hide'");
			$_isChild = $db->num("SELECT id FROM {$db->_structure} WHERE parent_id='{$_entry['id']}' AND status='publish'");

			$_entry['num'] = $i;
			$_entry['isPermissionHide'] = (!$_hide) ? 1 : 0;
			$_entry['uri'] = str_replace('//', '/', $settings->get('site_url') . trim($_entry['url'], '/') . '/');
			$_entry['isChild'] = ($_isChild) ? 1 : 0;
			$_entry['parent_url'] = $_url;
			$_modulesArray = explode(',', $_entry['module']);
			if(count($_modulesArray) > 1) $_entry['moduleList'] = $_modulesArray;

			array_push($this->_pages, clearArray($_entry));
			if($_isChild) { $this->getStructureRecursion($user, $db, $settings, $_entry['url']); } # if(!$_hide && $_isChild)
		}
	}

	function saveGroups()
	{
		global $db, $mcache;

		$db->query("DELETE FROM {$db->_structure_permission} WHERE structure_id='{$_POST['sId']}' AND action='{$_POST['groupAction']}'");
		foreach(explode('|', $_POST['groupsId']) AS $_gId) $db->query("INSERT INTO {$db->_structure_permission} (structure_id, group_id, action) VALUES ('{$_POST['sId']}', '$_gId', '{$_POST['groupAction']}')");
		$mcache->delete('structure');

		return;
	}

	function getSelectedGroups()
	{
		global $db;
		$_gId = array();
		$_query = $db->query("SELECT group_id FROM {$db->_structure_permission} WHERE structure_id='{$_POST['sId']}' AND action='{$_POST['groupAction']}'");
		while($_result = $db->fetchAssoc($_query)) array_push($_gId, $_result['group_id']);
		return implode('|', $_gId);
	}

	function save($user, $db, $settings)
	{
		global $mcache;

		$mcache->delete('structure');

		$_lvlId = $db->find("SELECT url, level_id, parent_id, id, sysname FROM " . $db->_structure . " WHERE id='" . $_POST['id'] . "'");
		$_parentId = $db->find("SELECT url, id FROM " . $db->_structure . " WHERE id='" . $_lvlId['parent_id'] . "'");
		$_lvlId['level_id']++;

		if($_POST['action'] == 'addStructureItem')
		{
			$_position = $db->num("SELECT id FROM {$db->_structure} WHERE parent_id='{$_POST['id']}' AND status='publish'");
			$_url = $_lvlId['url'] . $_POST['sysname'] . '/';
			$db->query("
				INSERT INTO " . $db->_structure . "
				(url, parent_id, level_id, user_id, position, create_date, update_date,
				name, sysname, title, header, keywords, description, module, template,
				isAuth, isHide, isClose)
				VALUES ('$_url', '$_POST[id]', '$_lvlId[level_id]', '".$user->_id."',
				'$_position', '".DATE."', '".DATE."',  '$_POST[name]',
				'$_POST[sysname]', '$_POST[title]', '$_POST[header]', '$_POST[keywords]',
				'$_POST[description]', '$_POST[module]', '$_POST[template]',
				'0', '0', '0')
			");
		}
		else
		{
			$_url = $_parentId['url'] . $_POST['sysname'];
			$_oldMQuery = $db->find("SELECT module, sysname FROM " . $db->_structure . " WHERE id='" . $_POST['id'] . "'");

			if($_lvlId['level_id'] != '0') { $_url .= '/'; }
			$_nAuth = (!$_POST['needAuth']) ? 0 : 1;
			$_hPage = (!$_POST['hidePage']) ? 0 : 1;
			$_cPage = (!$_POST['closePage']) ? 0 : 1;

			if($_oldMQuery['sysname'] == ADMINURL)
			{
				$settings->update('backoffice_url', $_POST['sysname']);
			}

			// $_oldModules = explode(',', $_oldMQuery['module']);
			// $_newModules = explode(',', $_POST['module']);
			// foreach(array_diff($_oldModules, $_newModules) AS $_diffModule) $db->query("DELETE FROM {$db->_structure_settings} WHERE structure_id='{$_POST['id']}' AND module='{$_diffModule}'");

			$db->query('
				UPDATE ' . $db->_structure . "
				SET url='$_url', name='$_POST[name]', sysname='$_POST[sysname]',
				title='$_POST[title]', header='$_POST[header]', keywords='$_POST[keywords]',
				description='$_POST[description]', module='$_POST[module]',
				template='$_POST[template]', isAuth='$_nAuth',
				isHide='$_hPage', isClose='$_cPage',
				update_date='".DATE."'
				WHERE id='$_POST[id]'
			");

			if($_POST['sysname'] != $_lvlId['sysname']) { $this->changeParentUrl($_POST['id']); }
		}
		return;
		#go($settings->get('site_url') . 'admin/structure/');
	}

	function deleteIncludedModuleData($id, $db)
	{
		foreach(explode(',', $this->getModuleById($id, $db)) AS $_module)
		{
			switch($_module)
			{
				case 'content' :
				{
					$db->query("DELETE FROM {$db->_prefix}content WHERE page_id='{$id}' LIMIT 1");
					break;
				}

				case 'article' :
				{
					$db->query("DELETE FROM {$db->_prefix}article WHERE page_id='{$id}' LIMIT 1");
					break;
				}
			}
		}
	}

	function go2trash($id)
	{
		global $mcache, $db;

		$db->query('UPDATE ' . $db->_structure . " SET status='delete' WHERE id=" . $id);
		$mcache->delete('structure');

		return;
	}

	function restore($id)
	{
		global $mcache, $db, $settings;

		$db->query('UPDATE ' . $db->_structure . " SET status='publish' WHERE id=" . $id);
		$mcache->delete('structure');

		go($settings->get('site_url') . $settings->get('backoffice_url') . '/trash');
	}

	function delete($id)
	{
		global $mcache, $db, $settings;

		$db->query('DELETE FROM ' . $db->_structure . ' WHERE id=' . $id);
		// $db->query('DELETE FROM ' . $db->_structure_settings . ' WHERE structure_id=' . $id);
		$db->query('DELETE FROM ' . $db->_structure_permission . ' WHERE structure_id=' . $id);

		$this->deleteIncludedModuleData($id, $db);
		$this->deleteParentUrl($id);
		$mcache->delete('structure');

		go($settings->get('site_url') . $settings->get('backoffice_url') . '/trash');
	}

	function deleteParentUrl($_parentId)
	{
		global $db;
        $_entries = $db->query('SELECT * FROM ' . $db->_structure . " WHERE parent_id='$_parentId'");
        if($_entries)
        {
			while($_entry = $db->fetchAssoc($_entries))
			{
				$db->query('DELETE FROM ' . $db->_structure . " WHERE id='" . $_entry['id'] . "'");
				// $db->query('DELETE FROM ' . $db->_structure_settings . ' WHERE structure_id=' . $_entry['id']);
				$db->query('DELETE FROM ' . $db->_structure_permission . ' WHERE structure_id=' . $_entry['id']);
				$this->deleteIncludedModuleData($_entry['id'], $db);
				$this->deleteParentUrl($_entry['id']);
			}
		}
	}

	function changeParentUrl($_parentId)
	{
		global $db;
        $_entries = $db->query('SELECT * FROM ' . $db->_structure . " WHERE parent_id='$_parentId'");
        if($_entries)
        {
			while($_entry = $db->fetchAssoc($_entries))
			{
				$_parentId = $db->find("SELECT url, id FROM " . $db->_structure . " WHERE id='" . $_entry['parent_id'] . "'");
				$_url = $_parentId['url'] . $_entry['sysname'] . '/';
				$db->query('UPDATE ' . $db->_structure . " SET url='$_url' WHERE id='" . $_entry['id'] . "'");
				$this->changeParentUrl($_entry['id']);
			}
		}
	}

	function getModulesList()
	{
		global $db;
    	$dh = opendir(ROOT . 'core/');
		while($_file = readdir($dh))
		{
			$_module = str_replace('.class.php', '', $_file);
			if($_file != '.' && $_file != '..' && preg_match("/(.*)\.class\.php/i", $_file) && !in_array($_module, $this->_deniedModules))
			{
				array_push($this->_modules, $_module);
			}
		}

		closedir($dh);
    	return $this->_modules;
	}

	function getTemplatesList($settings)
	{
		$_templates = array();
    	$dh = @opendir(ROOT . 'templates/' . $_POST['loaddir']);
		while($_file = @readdir($dh))
		{
			if($_file[0] == '.') continue;
			array_push($_templates, $_file);
		}

		@closedir($dh);
		return implode('|', $_templates);
	}
};
