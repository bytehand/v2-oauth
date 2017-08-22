<?php
	$error_description = null;
	if (array_key_exists('error', $_GET))
	{
		if (array_key_exists('error_description', $_GET))
			$error_description = $_GET['error_description'];
		else
			$error_description = 'Access denied.';
	}

	if (array_key_exists('code', $_GET))
	{
		$url = 'https://api.bytehand.com/v2/oauth/token';
		$data = array('code' => $_GET["code"], 'grant_type' => 'authorization_code',
			'client_secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
			'redirect_uri' => 'https://dev.bytehand.com/demo/v2/oauth', 'client_id' => '1');
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)));
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);

		if ($result === FALSE)
			$error_description = 'Oauth code exchange failed.';
		else
		{
			$oauth_credential = json_decode($result, true);
			$redirect_to = 'https://dev.bytehand.com/demo/v2/oauth?access_token='.$oauth_credential['access_token'];
			header('Location: '.$redirect_to);
			exit();
		}
	}

	$messages = null;
	if (array_key_exists('access_token', $_GET))
	{
		$result = file_get_contents('https://api.bytehand.com/v2/sms/messages?access_token='.$_GET['access_token']);
		if ($result === FALSE)
			$error_description = 'Failed to query SMS messages.';
		else
			$messages = json_decode($result, true);
	}
?>
<html style="font-family: &quot;Roboto&quot;,&quot;Helvetica&quot;,&quot;Arial&quot;,sans-serif;">
	<head></head>
	<body style="margin: 20px;">
		<h1>BYTEHAND API v2 OAuth Test</h1>
<?php
	if ($error_description != null)
	{
?>
		<div style="padding: 20px; background: red; color: white; margin-bottom: 20px;"><?php echo($error_description); ?></div>
<?php
	}
?>
		<a href="https://www.bytehand.com/api/v2/oauth/authorize?client_id=1&amp;response_type=code&amp;scope=profile_view,sms_view&amp;state=12345678&amp;redirect_uri=https://dev.bytehand.com/demo/v2/oauth">
			View my recent SMS messages.</a>
<?php
	if ($messages != null)
	{
?>
		<table cellpadding="5px" style="margin-top: 20px;">
			<tr><td><b>ID</b></td><td><b>SENDER</b></td><td><b>RECEIVER</b></td><td><b>TEXT</b></td></tr>
<?php
		foreach ($messages as &$message)
		{
?>
			<tr>
				<td><?php echo($message['id']); ?></td>
				<td><?php echo($message['sender']); ?></td>
				<td><?php echo($message['receiver']); ?></td>
				<td><?php echo($message['text']); ?></td>
			</tr>
<?php                         
		}
?>
		</table>
<?php
	}
?>
	</body>
</html>
