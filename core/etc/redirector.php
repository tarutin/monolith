<?php

	$_map = [
        // 'pagefrom' => 'pageto',
	];

	$_uri = array_shift(explode('?', URI));
	if(isset($_map[$_uri])) go301($_map[$_uri]);
