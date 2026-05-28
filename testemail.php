<?php
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Boot the DI container
$container = \Joomla\CMS\Factory::getContainer();
$container->alias('session.web', 'session.web.site')
    ->alias('session', 'session.web.site')
    ->alias('JSession', 'session.web.site')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\Session::class, 'session.web.site')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');

// Instantiate the application.
$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
\Joomla\CMS\Factory::$application = $app;

use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailHelper;

/* Boot Joomla Application */
date_default_timezone_set('Singapore');
set_time_limit(0);

/* Email Body */
$body = '
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>

<h3>Test Email Joomla 5 + PHP8.3</h3>

<p>Date: <strong>' . date('d-m-Y H:i:s') . '</strong></p>

</body>
</html>
';

/* Show Output */
echo $body;

/* Send Email */
try {

    $mailer = Factory::getMailer();

    /* Sender */
    $config = Factory::getConfig();

    $mailer->setSender([
        $config->get('mailfrom'),
        $config->get('fromname')
    ]);

    /* HTML Email */
    $mailer->isHtml(true);

    /* Recipients */
    $mailer->addRecipient([
        'nguyenducloi1503@gmail.com',
        'scarlett@ifoundries.com'
    ]);

    /* BCC */
    $mailer->addBcc([
        'nguyenducloi1503@gmail.com'
    ]);

    /* Subject */
    $mailer->setSubject('Allied Telesis : Test Email');

    /* Body */
    $mailer->setBody($body);

    /* Send */
    $sent = $mailer->send();

    if (!$sent) {
        throw new Exception('Mail send failed');
    }

    echo '<p style="color:green;"><strong>Email Sent Successfully</strong></p>';

} catch (Exception $e) {

    echo '<p style="color:red;"><strong>Email Error:</strong> '
        . htmlspecialchars($e->getMessage())
        . '</p>';

}