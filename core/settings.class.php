<?php

#
# SETTINGS class, v1.0
#

$settings = new Settings;

class Settings
{
	var $_config;
	var $_configData = array();

	function Settings()
	{
		$this->_config = ROOT . 'core/config.inc';
		$this->_configData = $this->getConfig();

        $_settings = '';
        foreach($this->_configData AS $_sk => $_sv) { $_settings .= "\$this->_{$_sk} = '{$_sv}';\n"; }
        eval($_settings);

		if($_POST['action'] == 'saveSettings') $this->save();
		if(getUrl(0) == $this->get('backoffice_url') AND getUrl(1) == 'settings' AND getUrl(2) == 'delete') $this->delete();
	}

	function get($keyword = false)
	{
		return !$keyword ? $this->_configData : $this->_configData[$keyword];
	}

	function getConfig()
	{
		$h = fopen($this->_config, 'r');
		$_info = fread($h, filesize($this->_config));
		fclose($h);

		return json_decode($_info, true);
	}

	function save()
	{
		$i=0;
		$_sSave = array();
		foreach($_POST['key'] AS $_key)
		{
			if(trim($_key) != '') $_sSave[$_key] = $_POST['val'][$i];
			$i++;
		}

		$this->write($_sSave);

		go($this->get('site_url') . $this->get('backoffice_url') . '/settings/');
	}

	function update($_var, $_val)
	{
		$this->_configData[$_var] = $_val;
		$this->write($this->_configData);
	}

	function delete()
	{
		unset($this->_configData[getUrl(3)]);
		$this->write($this->_configData);

		go($this->get('site_url') . $this->get('backoffice_url') . '/settings/');
	}

	function write($_data)
	{
		$_data = json_encode($_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$_h = fopen($this->_config, 'w');
		fwrite($_h, $_data);
		fclose($_h);
	}
}

?>
