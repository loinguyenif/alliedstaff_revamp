<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

class PlgAtelesisLogs extends CMSPlugin
{
    public function onLogEvent($message)
    {
        $file = JPATH_ROOT . '/tmp/test.log';
        $text = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
        file_put_contents($file, $text, FILE_APPEND);

        Factory::getApplication()->enqueueMessage('✅ Plugin chạy: ' . $message);
        return true;
    }
    public function onAfterAction($dataObj, $old_data = '', $new_data = '')
    {
        $app = Factory::getApplication();
        $db        =    Factory::getDBO();

        $log       =    new stdClass();
        $log->section = $dataObj->section;
        $log->action_type = $dataObj->action_type;
        $log->action_by = $dataObj->action_by;
        $log->which_id = $dataObj->id;
        $log->remarks = $dataObj->action_remarks;
        $log->before_update = $old_data;
        $log->after_update = $new_data;
        $log->action_date = date("Y-m-d H:i:s");

        // Insert the object into the logs table.
        $result = $db->insertObject('#__at_logs', $log);


        return true;
    }
}
