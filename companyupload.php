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
$db = Factory::getDbo();

/*
|--------------------------------------------------------------------------
| Get Users
|--------------------------------------------------------------------------
|
| Joomla 5:
| - gid column removed
| - customer_id custom field may or may not exist
|
| This script assumes:
| - customer_id column still exists in #__users
| - user groups migrated correctly
|--------------------------------------------------------------------------
*/

$query = $db->getQuery(true)
    ->select([
        'DISTINCT ' . $db->quoteName('customer_id'),
        $db->quoteName('name')
    ])
    ->from($db->quoteName('#__users'))
    ->where($db->quoteName('customer_id') . " != ''")
    ->group($db->quoteName('customer_id'));

$db->setQuery($query);

$users = $db->loadObjectList();

/*
|--------------------------------------------------------------------------
| Counters
|--------------------------------------------------------------------------
*/

$total_inserted = 0;
$total_skipped = 0;
$error_rows = [];

/*
|--------------------------------------------------------------------------
| Process Users
|--------------------------------------------------------------------------
*/

if (!empty($users)) {

    foreach ($users as $u) {

        if (empty($u->customer_id)) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Check Existing Company
        |--------------------------------------------------------------------------
        */

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__at_companies'))
            ->where(
                $db->quoteName('customer_id')
                . ' = '
                . $db->quote($u->customer_id)
            );

        $db->setQuery($query);

        $id = $db->loadResult();

        /*
        |--------------------------------------------------------------------------
        | Skip Existing
        |--------------------------------------------------------------------------
        */

        if ($id) {
            $total_skipped++;
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Insert Company
        |--------------------------------------------------------------------------
        */

        try {

            $columns = [
                'customer_id',
                'company_name'
            ];

            $values = [
                $db->quote($u->customer_id),
                $db->quote($u->name)
            ];

            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__at_companies'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));

            $db->setQuery($query);

            $db->execute();

            $total_inserted++;

        } catch (Exception $e) {

            $error_rows[] = [
                'customer_id' => $u->customer_id,
                'company_name' => $u->name,
                'error' => $e->getMessage()
            ];
        }
    }
}

/*
|--------------------------------------------------------------------------
| Output Result
|--------------------------------------------------------------------------
*/

echo '<html><body>';

echo '<h2>Company Sync Completed</h2>';

echo '<div style="margin-bottom:10px;">';
echo 'Total Companies Inserted: <strong>' . $total_inserted . '</strong>';
echo '</div>';

echo '<div style="margin-bottom:10px;">';
echo 'Total Companies Skipped: <strong>' . $total_skipped . '</strong>';
echo '</div>';

if (!empty($error_rows)) {

    echo '<h3>Errors</h3>';

    echo '<table border="1" cellpadding="5" cellspacing="0">';

    echo '<tr>';
    echo '<th>Customer ID</th>';
    echo '<th>Company Name</th>';
    echo '<th>Error</th>';
    echo '</tr>';

    foreach ($error_rows as $row) {

        echo '<tr>';

        echo '<td>' . htmlspecialchars($row['customer_id']) . '</td>';

        echo '<td>' . htmlspecialchars($row['company_name']) . '</td>';

        echo '<td>' . htmlspecialchars($row['error']) . '</td>';

        echo '</tr>';
    }

    echo '</table>';
}

echo '</body></html>';