<?php

$content = new class
{
	public $_contentSystemName = '';
	public $_contentList = array();
	public $_contentLines = array();

	function __construct()
	{
		global $tpl, $db, $structure;

		if(ISADMIN AND getUrl(1) == 'content' AND is_numeric(getUrl(2))) $this->getAdminContent(getUrl(2));
		elseif(ISADMIN AND getUrl(1) == 'content' AND !getUrl(2)) $this->getContentList($db);
		else $this->get($structure->_uri);

		if($_POST['action'] == 'saveContent') $this->save();
	}

	function get($_pageSysName)
	{
		global $db, $tpl;

		$_pageSysName = safe($_pageSysName);
		$_pages = $db->find("SELECT id, name FROM {$db->_structure} WHERE url='{$_pageSysName}' AND status='publish'");
		$_content = $this->getContent($_pages['id']);

		if($_content)
		{
			$tpl->assign('content', clearArray([
				'id' => $_pages['id'],
				'text' => $_content,
				'name' => $_pages['name']
			]));
		}
	}

	function getContentList($db)
	{
		global $tpl;
		$this->_contentList = array();

		$_entries = $db->query("
			SELECT c.text, s.name, s.url, s.id, s.create_date, s.status
			FROM
				{$db->_content} AS c,
				{$db->_structure} AS s
			WHERE c.page_id=s.id
				AND s.status='publish'
			ORDER BY s.id ASC");

		while($_content = $db->fetchAssoc($_entries))
		{
			$_trimText = trim(strip_tags($_content['text']));
			$_content['text'] = wrapByWord($_trimText, 500);
			if(strlen($_trimText) > 500) $_content['text'] .= '...';

			$_content['create_date'] = reset(explode(' ', $_content['create_date']));
			array_push($this->_contentList, $_content);
		}

		$tpl->assign('contentList', $this->_contentList);
	}

	function getAdminContent($_pageid)
	{
		global $db, $tpl;

		$_pageid = intval($_pageid);
		$_contentId = $db->find("SELECT * FROM " . $db->_content . " WHERE page_id='$_pageid'");
		$_pages = $db->find("SELECT id, name FROM " . $db->_structure . " WHERE id='$_pageid' AND status='publish'");

		$tpl->assign('text', $_contentId);
		$tpl->assign('pageid', $_pageid);
		$tpl->assign('contentid', $_contentId['id']);
		$tpl->assign('pageName', $_pages['name']);
	}

	function save($_contentid, $_pageid, $_text)
	{
		global $db, $settings;

		$_contentid = intval($_POST['contentid']);
		$_pageid = intval($_POST['pageid']);

		$_set['text'] = safehtml($_POST['content']);

		if($_contentid) { $db->query("UPDATE {$db->_content} SET " . $db->compile($_set) . " WHERE id='{$_contentid}'"); }
		else {
			$_set['page_id'] = $_pageid;
			$db->insert($db->_content, $_set);
		}

		go($settings->get('site_url') . ADMINURL . '/content/' . $_pageid);
	}

	function getContent($_pageid, $_tplParse = true)
	{
		global $db, $tpl;

		$_pageid = intval($_pageid);
		$_content = $db->find("SELECT * FROM {$db->_content} WHERE page_id='{$_pageid}'");
		return $_tplParse ? $tpl->parseStr($_content['text']) : $_content['text'];
	}

}
