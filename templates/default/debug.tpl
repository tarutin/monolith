{php}
    global $tpl;
    unset($tpl->_vars['site']);

    if($_GET['debug']) {
        echo json_encode($tpl->_vars[$_GET['debug']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if(isset($_GET['debug'])) {
        echo json_encode($tpl->_vars, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
{/php}
