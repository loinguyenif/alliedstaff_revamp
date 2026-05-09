<?php
defined('_JEXEC') or die;

class ModRokQuicklinksHelper
{
    public static function getLinks($params)
    {
        $links = $params->get('links', []);
        return $links;
    }
}
