<?php

$actor = new Actor;

class Actor
{
	function __construct()
	{
        if(ISADMIN)
        {
            if(!getUrl(2)) $this->actors();
            if(getUrl(2) == 'peoples') $this->peoples();
            if(getUrl(2) == 'peoples' && $_GET['edit'] && $_GET['action'] == 'resort') $this->resortPeopleGallery();
            if(getUrl(2) == 'peoples' && $_GET['edit'] && $_GET['removegallery']) $this->removePeopleGallery();
            if($_POST['action'] == 'manageActor') $this->manageActor();
            if($_POST['action'] == 'managePeople') $this->managePeople();
        }
        else
        {
            $this->actors();
            $this->peoples();
            if($_POST['action'] == 'displayPeople') $this->displayPeople();
        }
    }

    function displayPeople()
    {
        global $db, $tpl;

        $_people_id = intval($_POST['people_id']);

        $tpl->assign('people', $this->peoples($_people_id));
        echo $tpl->display('modal-people.tpl');
        exit;
    }

	function resortPeopleGallery()
	{
		global $db;

        $_people_id = intval($_GET['edit']);
		$i = 1;
		foreach($_POST['item'] as $v)
		{
			$db->query("UPDATE {$db->_files} SET position='{$i}' WHERE id='{$v}' AND type='people-gallery-{$_people_id}' LIMIT 1");
		    $i++;
		}

		exit;
	}

    function removePeopleGallery()
    {
        global $db;

        $_people_id = intval($_GET['edit']);
        $_file_id = intval($_GET['removegallery']);

        $db->query("UPDATE {$db->_files} SET status='delete' WHERE id='{$_file_id}' AND type='people-gallery-{$_people_id}' LIMIT 1");

        cookie('notice', 'Удалено', 2);
        go("/back/actors/peoples?edit={$_people_id}");
    }

    function managePeople()
    {
        global $db;

        $_people_id = intval($_POST['people_id']);
        $_people = [
            'name' => safe($_POST['name']),
            'caption' => safe($_POST['caption']),
            'content' => strip_tags(safehtml($_POST['content']), '<p><a><strong><italic><b><i>'),
        ];

        if(!$_people_id)
        {
            $_people_id = $db->status($db->_peoples) + 1;
            $db->insert($db->_peoples, $_people);
        }
        else
        {
            $_update = $db->compile($_people);
            $db->query("UPDATE {$db->_peoples} SET {$_update} WHERE id='{$_people_id}'");
        }

        if($_FILES['avatar']['tmp_name']) {
            $db->query("UPDATE {$db->_files} SET status='delete' WHERE type='people-avatar-{$_people_id}' AND status='publish'");
            $this->_upload($_FILES['avatar'], 'people', $_people_id, "people-avatar-{$_people_id}");
        }

        if($_FILES['sign']['tmp_name']) {
            $db->query("UPDATE {$db->_files} SET status='delete' WHERE type='people-sign-{$_people_id}' AND status='publish'");
            $this->_upload($_FILES['sign'], 'people', $_people_id, "people-sign-{$_people_id}");
        }

        foreach(reArrayFiles($_FILES['gallery']) AS $_file) {
            $this->_upload($_file, 'people', $_people_id, "people-gallery-{$_people_id}");
        }

        cookie('notice', 'Сохранено', 2);
        go('/back/actors/peoples');
    }

    function manageActor()
    {
        global $db;

        $_actor_id = intval($_POST['actor_id']);
        $_actor = [
            'content' => strip_tags(safehtml($_POST['content']), '<p><a><strong><italic><b><i>'),
        ];
        $_update = $db->compile($_actor);

        $db->query("UPDATE {$db->_actors} SET {$_update} WHERE id='{$_actor_id}'");

        if($_FILES['photo']['tmp_name']) {
            $db->query("UPDATE {$db->_files} SET status='delete' WHERE type='actor-photo-{$_actor_id}' AND status='publish'");
            $this->_upload($_FILES['photo'], 'actor', $_actor_id, "actor-photo-{$_actor_id}");
        }

        cookie('notice', 'Сохранено', 2);
        go("/back/actors?edit={$_actor_id}");
    }

    function peoples($_people_id = null)
    {
        global $tpl, $db;

        if($_people_id) $_filter = "WHERE id='{$_people_id}'";

        $_sort = ISADMIN ? 'id' : 'RAND()';
        $_peoples = $db->findAll("SELECT * FROM {$db->_peoples} {$_filter} ORDER BY {$_sort} ASC");
        foreach($_peoples AS $i => $_people) {
            $_peoples[$i]['avatar'] = $db->find("SELECT * FROM {$db->_files} WHERE type='people-avatar-{$_people['id']}' AND status='publish'");
            $_peoples[$i]['sign'] = $db->find("SELECT * FROM {$db->_files} WHERE type='people-sign-{$_people['id']}' AND status='publish'");
            $_peoples[$i]['gallery'] = $db->findAll("SELECT * FROM {$db->_files} WHERE type='people-gallery-{$_people['id']}' AND status='publish' ORDER BY position ASC");
        }

        if($_people_id) return $_peoples[0];
        else $tpl->assign('peoples', $_peoples);
    }

    function actors()
    {
        global $tpl, $db;

        $_actors = $db->findAll("SELECT * FROM {$db->_actors} ORDER BY id ASC");
        foreach($_actors AS $i => $_actor) {
            $_actors[$i]['photo'] = $db->find("SELECT * FROM {$db->_files} WHERE type='actor-photo-{$_actor['id']}' AND status='publish'");
        }

        $tpl->assign('actors', $_actors);
    }

	function _upload($_file, $_folder, $_id, $_type)
	{
		global $db;

		$_file_name = md5($_type . time() . rand(1, 100000));
		$_file_ext = getFileType($_file['name']);
		$_file_path = ROOT . "/media/source/{$_folder}/{$_id}/{$_file_name}{$_file_ext}";
		list($_width, $_height) = getimagesize($_file['tmp_name']);

        if($_width > 8000) {
            cookie('warning', 'Ошибка. Файл слишком большой для загрузки', 2);
            return false;
        }

		mkdir(ROOT . "/media/source/{$_folder}/{$_id}/", 0777, true);

		if(move_uploaded_file($_file['tmp_name'], $_file_path))
		{
            $_image_id = $db->status($db->_files) + 1;
			$_image = [
                'id' => $_image_id,
                'date' => time(),
                'position' => $_image_id,
                'name' => $_file['name'],
				'type' => $_type,
				'file' => $_file_name . $_file_ext,
                'extension' => $_file_ext,
			];

			$db->insert($db->_files, $_image);
			return $_image;
		}
	}
}
