<?php

$db = new Db;

class Db
{
	var $_link = 0;
	var $_query = 0;
	var $_fetchArray = [];
	var $_queryList = [];

	function __construct()
	{
		global $settings;

		$this->_host = $settings->get('db_host');
		$this->_name = $settings->get('db_name');
		$this->_user = $settings->get('db_user');
		$this->_pass = $settings->get('db_password');
		$this->_prefix = $settings->get('db_prefix');

		$this->connect();
	}

	function connect()
	{
		if($this->_link == 0)
		{
			$this->_link = @mysqli_connect($this->_host, $this->_user, $this->_pass, $this->_name) OR error('Нет соеденения с БД', mysqli_error($this->_link));
			$this->query("SET NAMES 'utf8'");
			$this->query("SET CHARACTER SET 'utf8'");
			$this->query("SET SESSION collation_connection = 'utf8_general_ci'");
			$this->query("SET time_zone = '+03:00'");

			$this->tables();
		}
	}

	function close()
	{
		mysqli_close($this->_link);
	}

	function query($_str)
	{
		$this->_query = @mysqli_query($this->_link, $_str) OR error('Неверный запрос', $_str . '<br/><br/>' . mysqli_error($this->_link));
		array_push($this->_queryList, $_str);

		return $this->_query;
	}

	function tables()
	{
        if($_res = $this->query("SHOW TABLES"))
        {
            while($_table = $this->fetchArray($_res))
            {
				$_table_name = str_replace($this->_prefix, '', $_table[0]);
				$_tables .= "\$this->_{$_table_name} = '{$_table[0]}';\n";
            }

            $this->free($_res);
        }

		eval($_tables);
	}

	function status($_tbl)
	{
		$row = $this->find("SHOW TABLE STATUS LIKE '{$_tbl}'");
		return ($row[Auto_increment] - 1);
	}

    function findAll($query)
    {
        $result = [];
        if($res = $this->query($query))
        {
            while($row = $this->fetchAssoc($res))
            {
                $result[] = $row;
            }

            $this->free($res);
        }

        return $result;
    }

    function findColumn($query)
    {
        $result = [];

        if($res = $this->query($query))
        {
            while($row = $this->fetchArray($res))
            {
                $result[] = $row[0];
            }

            $this->free($res);
        }

        return $result;
    }

    function free($_res)
    {
        return @mysqli_free_result($_res);
    }

    function value($_query)
    {
        $data = $this->fetchArray($this->query($_query));
        return $data[0] ? $data[0] : null;
    }

	function find($_str)
	{
		return $this->fetchAssoc($this->query($_str));
	}

	function num($_query)
	{
		return @mysqli_num_rows($this->query($_query));
	}

	function insert($_tbl, $_data)
	{
		return $this->query("INSERT INTO {$_tbl} SET " . $this->compile($_data));
	}

	function compile($_data)
	{
		$result = '';
		foreach($_data AS $k => $v)
		{
			$v = trim($v);
			$result .= "`{$k}` = " . ($v == '' ? 'null' : (is_string($v) ? "'".$v."'" : $v)) . ", ";
		}

		return substr($result, 0, -2);
	}

	function fetchArray($_query_id)
	{
		return @mysqli_fetch_array($_query_id);
	}

	function fetchAssoc($_query_id)
	{
		return @mysqli_fetch_assoc($_query_id);
	}

	function escape($_val)
	{
		if(is_array($_val))
		{
			foreach($_val AS $_v => $_i) $_val[$_v] = escapevar($_i);
		}
		else $_val = escapevar($_val);

		return $_val;
	}

}

function safe($_string)
{
	global $db;

	$_string = safevar($_string, false, false, true);
	return mysqli_real_escape_string($db->_link, $_string);
}

function safehtml($_string, $_encode = true)
{
	global $db;

	if($_encode) $_string = html_entity_decode($_string, ENT_QUOTES, 'UTF-8');
	$_string = safevar($_string, false, false, false);
	return mysqli_real_escape_string($db->_link, $_string);
}
