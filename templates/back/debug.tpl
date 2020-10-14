<!doctype html>
<html>
	<head>
		<title>DEBUG — {#settings.site_name}</title>
		<meta charset='utf-8'/>
		<meta name='robots' content='noindex'/>
		<style>
			body { padding:20px 50px; font-size:12px; color:#464646; }
			a { color:#5980d3; text-decoration:none; }
		</style>
		<script>
			function toggleDisplay(id) { document.getElementById(id).style.display = (document.getElementById(id).style.display == "block") ? "none" : "block"; }
		</script>
	</head>

	<body>
		<pre>{php}

				global $tpl, $mcache;

				echo print_r_tree($tpl->_vars);

				#echo '<br/><br/>';
				#echo print_r_tree(array(
				#	'memcached' => $mcache->getAllData()
				#));

				#echo '<br/>';
				#echo print_r_tree($_SERVER);
				#echo '<br/>';
				#echo print_r_tree(ini_get_all());

				function print_r_tree($data)
				{
				    $out = print_r($data, true);
				    $out = str_replace('<', '&lt;', $out);
				    $out = @preg_replace('/([ \t]*)(\[[^\]]+\][ \t]*\=\>[ \t]*[a-z0-9 \t_]+)\n[ \t]*\(/iUe',"'\\1<a href=\"javascript:toggleDisplay(\''.(\$id = substr(md5(rand().'\\0'), 0, 7)).'\');\">\\2</a><div id=\"'.\$id.'\" style=\"display: none;\">'", $out);
				    $out = @preg_replace('/^\s*\)\s*$/m', '</div>', $out);
				    return $out;
				}

		{/php}</pre>
	</body>
</html>
