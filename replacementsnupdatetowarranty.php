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

set_time_limit(0);
$db  = Factory::getDbo();

/* Get all RMA items */
$query = $db->getQuery(true)
    ->select('r.*')
    ->from('#__at_rma_items AS r')
    ->order('r.created_date ASC');

$db->setQuery($query);

$rmaItems = $db->loadObjectList();

$totalUpdated = 0;
$totalNotFound = 0;
$logs = [];

if (!empty($rmaItems)) {

    $i = 1;

    foreach ($rmaItems as $rma) {

        $requestedSn   = trim($rma->requested_sn);
        $replacementSn = trim($rma->replacement_sn);
        $replacementPn = trim($rma->replacement_pn);

        /* Skip empty requested SN */
        if (empty($requestedSn)) {
            continue;
        }

        /* Find warranty item */
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__at_warranty_items')
            ->where('serial_no = ' . $db->quote($requestedSn));

        $db->setQuery($query);

        $warrantyItemId = $db->loadResult();

        if ($warrantyItemId) {

            /* Update replacement info */
            $fields = [
                'serial_no_2 = ' . $db->quote($replacementSn),
                'replacement_pn = ' . $db->quote($replacementPn)
            ];

            $query = $db->getQuery(true)
                ->update('#__at_warranty_items')
                ->set($fields)
                ->where('id = ' . (int) $warrantyItemId);

            try {

                $db->setQuery($query);
                $db->execute();

                $logs[] =
                    $i . '. Updated Warranty S/N: '
                    . $requestedSn
                    . ' → Replacement S/N: '
                    . ($replacementSn ?: '-')
                    . ' (P/N: '
                    . ($replacementPn ?: '-')
                    . ')';

                $totalUpdated++;

            } catch (Exception $e) {

                $logs[] =
                    $i . '. Error updating '
                    . $requestedSn
                    . ' : '
                    . $e->getMessage();
            }

        } else {

            $logs[] =
                $i . '. Warranty not found for S/N: '
                . $requestedSn;

            $totalNotFound++;
        }

        $i++;
    }
}

/* Output Result */
echo '<h2>RMA Warranty Sync Completed</h2>';

echo '<p>Total Updated: <strong>'
    . $totalUpdated
    . '</strong></p>';

echo '<p>Total Warranty Not Found: <strong>'
    . $totalNotFound
    . '</strong></p>';

if (!empty($logs)) {

    echo '<h3>Logs</h3>';

    echo '<ul>';

    foreach ($logs as $log) {

        echo '<li>'
            . htmlspecialchars($log)
            . '</li>';
    }

    echo '</ul>';
}