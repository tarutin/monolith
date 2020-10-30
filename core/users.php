<?php

$user = new class
{
	var $_users = array();
	var $_groups = array();
	var $_sessionid = '';
	var $_cookiename = 'is_login';
	var $_anonym_sessid = '001';
	var $_anonym_userid = 0;
	var $_anonym_groupid = 0;
	var $_id = 0;
	var $_group_id = 0;
	var $_login = 'Anonymous';
	var $_groupname = 'Unregistered';
	var $_salt = 'bid7dmnlth';

	function __construct()
	{
		global $db, $settings, $tpl;

		$this->_sessionid = $_COOKIE[$this->_cookiename];
		$_anonymous = $this->getUserByLogin('Anonymous');
		$this->_anonym_userid = $_anonymous['id'];
		$this->_anonym_groupid = $_anonymous['group_id'];

		$this->auth();
		if(getUrl(0) == 'logout') $this->logout();

		if(ISADMIN)
		{
			if(getUrl(1) == 'users') $this->get();
			if(getUrl(1) == 'users' OR getUrl(1) == 'structure') $tpl->assign('groups', $this->getGroupList());
			if(getUrl(1) == 'users' && $_GET['delete']) { $this->go2trash($_GET['delete']); }
			if($_POST['action'] == 'addUser') { $this->saveUser('add'); }
			if($_POST['action'] == 'editUser') { $this->saveUser('edit'); }
			if($_POST['action'] == 'selfEditUser') { $this->saveUser('selfedit'); }
			if($_POST['action'] == 'getUser') { die(json_encode($this->getUserById($_POST['userid']))); }
			if($_POST['action'] == 'doAuth') $this->authLogin();
		}

		if(!ISADMIN)
		{
		}
	}


	function get()
	{
		global $db, $tpl;

		$_search = (isset($_GET['search'])) ? safe($_GET['search']) : false;

		if($_search)
		{
			if(is_numeric($_search)) $_where = " AND u.id = '{$_search}'";
			else $_where = " AND (u.email LIKE '%{$_search}%' OR u.login LIKE '%{$_search}%' OR u.firstname LIKE '%{$_search}%' OR u.lastname LIKE '%{$_search}%')";
		}

		# pager
		$_users_num = $db->value("SELECT COUNT(id) count FROM {$db->_users} u WHERE u.status!='delete'{$_where}");
        $_on_pg = 200;
        $_on_start = !$_GET['page'] ? 0 : $_on_pg * ($_GET['page'] - 1);
        $_limit = $_GET['page'] == 'all' ? '' : " LIMIT {$_on_start}, {$_on_pg}";

		$_query = $db->query("
			SELECT u.*, g.name group_name
			FROM {$db->_users} u
			LEFT JOIN {$db->_users_groups} g ON u.group_id=g.id
			WHERE u.status!='delete'{$_where}
			ORDER BY u.id DESC
			{$_limit}
		");

		while($_user = $db->fetchAssoc($_query))
		{
			$_user['date'] = date('d.m.Y H:i:s', $_user['date']);
			array_push($this->_users, $_user);
		}

		$tpl->assign(array(
			'users' => $this->_users,
			'users_num' => $_users_num,
			'newUserId' => $db->status($db->_users) + 1,
			'newGroupId' => $db->status($db->_users_groups) + 1,
			'pages' => ceil($_users_num / $_on_pg)
		));
	}

	function getUserById($id)
	{
		global $db;

		$id = (int) $id;
		$_entries = $db->find("SELECT * FROM {$db->_users} WHERE id='{$id}'");
		$_entries['additional'] = json_decode($_entries['additional'], true);

		return $_entries;
	}

	function hasUserByLogin($_login)
	{
		global $db;

		$_login = safe($_login);
		return $db->num("SELECT login FROM {$db->_users} WHERE login='{$_login}'");
	}

	function getUserByLogin($login)
	{
		global $db;

		$_entries = $db->find("SELECT * FROM {$db->_users} WHERE login='" . safe($login) . "'");
		if($_entries) $_entries['additional'] = json_decode($_entries['additional'], true);
		return $_entries;
	}

	function getUserByEmail($email)
	{
		global $db;

		$email = safe($email);
		return $db->find("SELECT * FROM {$db->_users} WHERE email='{$email}'");
	}

	function getGroupList()
	{
		global $db;

        return $db->findAll("SELECT * FROM {$db->_users_groups}");
	}

	function getGroupName($_groupid, $db)
	{
		$_groupid = (int) $_groupid;
		$_group = $db->find("SELECT id, name FROM " . $db->_users_groups . " WHERE id='" . $_groupid . "'");
		return $_group['name'];
	}

	function getGroupidByName($_name)
	{
		global $db;

		$_name = safe($_name);
		$_group = $db->find("SELECT id, name FROM " . $db->_users_groups . " WHERE name='" . $_name . "'");
		return $_group['id'];
	}

	function delete($_userid)
	{
		global $db, $settings;

		$_userid = (int) $_userid;
		$db->query("DELETE FROM " . $db->_users . " WHERE id='" . $_userid . "'");
		go($settings->get('site_url') . ADMINURL . '/trash');
	}

	function go2trash($_userid)
	{
		global $db, $settings;

		$_userid = (int) $_userid;
		$db->query("UPDATE {$db->_users} SET status='delete' WHERE id='{$_userid}'");

		go($settings->get('site_url') . ADMINURL . '/users');
	}

	function restore($_userid)
	{
		global $db, $settings, $mcache;

		$_userid = (int) $_userid;
		$db->query("UPDATE " . $db->_users . " SET status='publish' WHERE id='" . $_userid . "'");
		$mcache->flush();

		go($settings->get('site_url') . ADMINURL . '/trash');
	}

	function saveUser($_action)
	{
		global $db, $mail;

		if($_action == 'edit')
		{
			$db->query("
				UPDATE " . $db->_users . "
				SET login='" . safe($_POST['login']) . "',
					email='" . safe($_POST['email']) . "',
					group_id='" . safe($_POST['group_id']) . "'
				WHERE id='{$_POST['userid']}'
			");

			if(trim($_POST['password']) != '') { $db->query("UPDATE " . $db->_users . " SET password='" . md5($this->_salt . md5($_POST['password'])) . "' WHERE id='{$_POST['userid']}'"); }

			#if(in_array($_POST['group_id'], array(1, 3, 10))) $mail->verifyEmail($_POST['email']);
		}

		if($_action == 'selfedit')
		{
			$_new_pass = md5($this->_salt . md5($_POST['password']));

			$db->query("UPDATE {$db->_users} SET email='{$_POST['email']}' WHERE id='{$_POST['userid']}'");
			if(trim($_POST['password']) != '') { $db->query("UPDATE {$db->_users} SET password='{$_new_pass}' WHERE id='{$_POST['userid']}'"); }
		}

		if($_action == 'add')
		{
			$_user = array
			(
				'date' => time(),
				'group_id' => $_POST['group_id'],
				'email' => safe($_POST['email']),
				'login' => safe($_POST['login']),
				'firstname' => safe($_POST['firstname']),
				'lastname' => safe($_POST['lastname']),
				'password' => md5($this->_salt . md5($_POST['password'])),
				'ip' => IP,
				'status' => 'publish'
			);
			$db->insert($db->_users, $_user);
            cookie('notice', 'Сохранено', 2);

			// if(in_array($_POST['group_id'], array(1, 3, 10))) $mail->verifyEmail($_POST['email']);
		}

		go($_POST['back']);
	}

	function auth()
	{
		global $db, $tpl, $cities;

		$_query = "SELECT s.id, s.user_id FROM {$db->_users_sessions} AS s, {$db->_users} AS u WHERE s.user_id=u.id AND s.token='" . safe($this->_sessionid) . "'";

		if(!$db->num($_query))
		{
			$this->_id = (int) $this->_anonym_userid;
			$this->_group_id = (int) $this->_anonym_groupid;
			$this->login($this->_id);
		}
		else
		{
			$_sess_userid = $db->fetchAssoc($db->query($_query));
			$this->_id = (int) $_sess_userid['user_id'];
		}

		$_user = $this->getUserById($this->_id);

		$_user_data = '';
		foreach($_user AS $_k => $_u)
		{
			$_user_data .= is_numeric($_u) ? "\$this->_".$_k." = ".$_u.";\n" : "\$this->_".$_k." = '".$_u."';\n";
		}
		eval($_user_data);

		$this->_groupname = $this->getGroupName($this->_group_id, $db);
		$_user['group'] = $this->_groupname;
		$tpl->assign('user', clearArray($_user));
	}

	function checkPermission($_id, $db)
	{
		$_id = (int) $_id;
		$_isAuth = $db->num("SELECT id FROM {$db->_structure_permission} WHERE structure_id='{$_id}' AND group_id='{$this->_group_id}' AND action='auth'") ? 1 : 0;
		$_isClose = $db->num("SELECT id FROM {$db->_structure_permission} WHERE structure_id='{$_id}' AND group_id='{$this->_group_id}' AND action='close'") ? 1 : 0;

		return array('auth' => $_isAuth, 'close' => $_isClose);
	}

	function authLogin()
	{
		global $tpl, $db;

		$_user = $db->find("
			SELECT id FROM {$db->_users}
			WHERE login='" . safe(trim($_POST['login'])) . "'
			  AND password='" . md5($this->_salt . md5(trim($_POST['password']))) . "'
			  AND status='publish'
		");

		$this->logout(false);

		if($_user['id']) {
            $this->login($_user['id']);
            cookie('notice', 'Вы успешно вошли', 2);
            go($_POST['go']);
        }
		else {
            cookie('warning', 'Неправильный логин или пароль.', 2);
            // $tpl->assign('auth_error', true);
        }
	}

	function login($_userid)
	{
		global $settings, $db;

		$_userid = intval($_userid);
		$this->_sessionid = $this->_anonym_userid == $_userid ? $this->_anonym_sessid : md5(uniqid(rand(), true));

		cookie($this->_cookiename, $this->_sessionid, intval($settings->get('cookie_lifetime')));

		if($this->_sessionid == $this->_anonym_sessid) {
            $db->query("UPDATE {$db->_users_sessions} SET changed='".time()."' WHERE user_id='{$_userid}'");
        }
		else {
            // $_geo = geoip_record_by_name(IP);
            $_insert = [
                'token' => $this->_sessionid,
                'date' => time(),
                'user_id' => $_userid,
                'device' => safe($_SERVER['HTTP_USER_AGENT']),
                'geo' => $_geo ? json_encode($_geo, JSON_UNESCAPED_SLASHES) : NULL,
                'ip' => IP,
            ];
            $db->insert($db->_users_sessions, $_insert);
        }
	}

	function logout($redirect = true)
	{
		global $settings, $db;
		if($this->_anonym_sessid != $this->_sessionid) $db->query("DELETE FROM {$db->_users_sessions} WHERE token='{$this->_sessionid}'");

		if($redirect)
		{
			$_backurl = !empty($_GET['return']) ? urldecode($_GET['return']) : $settings->get('site_url');
			go($_backurl);
		}
	}

	function clear()
	{
		global $db, $settings;
		$db->query("DELETE FROM {$db->_users_sessions} WHERE changed < '" . (time() - intval($settings->get('cookie_lifetime'))) . "'");

		die('old sessions cleared');
	}
};
