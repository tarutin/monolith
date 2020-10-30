<?php

$mail = new class
{
	public $_to = array();
	public $_from = false;
	public $_message = '';
	public $_subject = '';
	public $_attach = false;
	public $_sendby = 'native';

	function __construct() {
        if(isset($_GET['testmail'])) $this->test();
    }

	function send()
	{
        if($this->_sendby == 'ses') $this->ses();
		if($this->_sendby == 'sendgrid') $this->sendgrid();
		if($this->_sendby == 'native') $this->native();
	}

	function sendgrid()
	{
		global $settings;

        if(!$this->_from) {
            $this->_from = [
                'name' => $settings->get('site_name'),
                'email' => $settings->get('mail_robot') . '@' . $settings->get('domain'),
            ];
        }

        $request = 'https://api.sendgrid.com/v3/mail/send';
		$_data = [
			'personalizations' => [[
                'to' => [[
                    'email' => $this->_to,
                ]],
                'subject' => $this->_subject,
            ]],
			'content' => [[
                'type' => 'text/html',
                'value' => $this->_message,
            ]],
			'from' => $this->_from,
		];

        if($this->_attach)
        {
            $_data['attachments'] = [[
                'content' => base64_encode($this->_attach['path']),
                'type' => mime_content_type($this->_attach['path']),
                'filename' => $this->_attach['name'],
                'disposition' => 'attachment',
            ]];
        }

        $session = curl_init($request);
        curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($session, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $settings->_sendgrid_key, 'content-type: application/json']);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, json_encode($_data));
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($session);
        curl_close($session);

        $_data = json_decode($response, true);
        if($_data['errors']) error2log("SENDGRID. {$this->_to}: {$this->_subject}", $response);
        else return true;
	}

    function native()
    {
        global $settings;

        if(!$this->_from) $this->_from = $settings->get('site_name') . ' <' . $settings->get('mail_robot') . '@' . $settings->get('domain') . '>';
		$_headers  = "Content-type: text/html; charset=utf-8\r\nFrom: {$this->_from}\r\n";
		$_subject = $this->_subject;

		if(is_array($this->_to))
		{
			foreach($this->_to AS $_user)
			{
				if(!mail($_user, $_subject, $this->_message, $_headers)) error2log('Невозможно отправить эл. почту на адрес: ' . $_user, $this->_subject);
				else return true;
			}
		}
		else
		{
			if(!mail($this->_to, $_subject, $this->_message, $_headers)) error2log('Невозможно отправить эл. почту на адрес: ' . $this->_to, $this->_subject);
			else return true;
		}
    }

    function ses()
    {
        global $settings;

		if(!$this->_from) $this->_from = $settings->get('site_name') . ' <' . $settings->get('mail_robot') . '@' . $settings->get('domain') . '>';

        include_once(ROOT . 'core/etc/amazon-ses/sdk.class.php');
        $ses = new AmazonSES($settings->get('aws_access_key_id'), $settings->get('aws_secret_access_key'));

		$recip = ["ToAddresses" => explode(',', $this->_to)];
		$message = ["Subject.Data" => $this->_subject, "Body.Html.Data" => $this->_message];
		$res = $ses->send_email($this->_from, $recip, $message);

		if ($res->status != 200) error2log("Невозможно отправить эл. почту на адрес: {$this->_to}, Ошибка: {$res->body->Error->Message}", $this->_subject);
		else return true;
    }

	function test()
	{
		global $settings;

		$this->_to = $settings->get('contact_mail');
		$this->_message = 'Test message ' . time();
		$this->_subject = 'Test subject ' . time();
		$this->send();

		die('SEND');
	}
};
