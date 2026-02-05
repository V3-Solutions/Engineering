<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
  exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$details = trim($_POST['details'] ?? '');

if ($name === '' || $email === '' || $details === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Please complete all fields.']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Please provide a valid email.']);
  exit;
}

$smtp = [
  'host' => 'smtp.example.com',
  'port' => 587,
  'username' => 'user@example.com',
  'password' => 'app-password',
  'secure' => 'tls',
  'from_email' => 'no-reply@example.com',
  'from_name' => 'Engineering Firm',
  'to_email' => 'iamjaymarfrace@yahoo.com'
];

if (strpos($smtp['host'], 'example.com') !== false || strpos($smtp['username'], 'user@') !== false) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'SMTP is not configured yet.']);
  exit;
}

$subject = 'New Project Inquiry';
$messageLines = [
  "Name: {$name}",
  "Email: {$email}",
  "Project Details:",
  $details
];
$message = implode("\r\n", $messageLines);

$headers = [
  'From: ' . $smtp['from_name'] . ' <' . $smtp['from_email'] . '>',
  'Reply-To: ' . $email,
  'Content-Type: text/plain; charset=UTF-8'
];

function smtpSend($smtp, $to, $subject, $message, $headers) {
  $secure = $smtp['secure'] ?? '';
  $host = $smtp['host'];
  $port = (int) $smtp['port'];

  $remote = ($secure === 'ssl' ? "ssl://{$host}" : $host) . ':' . $port;
  $socket = stream_socket_client($remote, $errno, $errstr, 10);
  if (!$socket) {
    return "Connection failed: {$errstr}";
  }

  $read = function () use ($socket) {
    $data = '';
    while ($line = fgets($socket, 515)) {
      $data .= $line;
      if (substr($line, 3, 1) === ' ') {
        break;
      }
    }
    return $data;
  };

  $send = function ($command) use ($socket) {
    fwrite($socket, $command . "\r\n");
  };

  $expect = function ($code) use ($read) {
    $response = $read();
    if (strpos($response, (string) $code) !== 0) {
      return $response;
    }
    return null;
  };

  if ($error = $expect(220)) {
    fclose($socket);
    return $error;
  }

  $send('EHLO localhost');
  if ($error = $expect(250)) {
    fclose($socket);
    return $error;
  }

  if ($secure === 'tls') {
    $send('STARTTLS');
    if ($error = $expect(220)) {
      fclose($socket);
      return $error;
    }
    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
      fclose($socket);
      return 'TLS negotiation failed.';
    }
    $send('EHLO localhost');
    if ($error = $expect(250)) {
      fclose($socket);
      return $error;
    }
  }

  $send('AUTH LOGIN');
  if ($error = $expect(334)) {
    fclose($socket);
    return $error;
  }
  $send(base64_encode($smtp['username']));
  if ($error = $expect(334)) {
    fclose($socket);
    return $error;
  }
  $send(base64_encode($smtp['password']));
  if ($error = $expect(235)) {
    fclose($socket);
    return $error;
  }

  $send('MAIL FROM:<' . $smtp['from_email'] . '>');
  if ($error = $expect(250)) {
    fclose($socket);
    return $error;
  }

  $send('RCPT TO:<' . $to . '>');
  if ($error = $expect(250)) {
    fclose($socket);
    return $error;
  }

  $send('DATA');
  if ($error = $expect(354)) {
    fclose($socket);
    return $error;
  }

  $data = 'Subject: ' . $subject . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $message . "\r\n.";
  $send($data);
  if ($error = $expect(250)) {
    fclose($socket);
    return $error;
  }

  $send('QUIT');
  fclose($socket);
  return null;
}

$error = smtpSend($smtp, $smtp['to_email'], $subject, $message, $headers);

if ($error) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Email failed to send.']);
  exit;
}

echo json_encode(['success' => true]);
