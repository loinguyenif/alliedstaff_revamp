<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

class PlgSystemPartnerRedirect extends CMSPlugin
{
    /**
     * Bắt event onUserLogin
     * @param array $user Thông tin user
     * @param array $options Options login
     */
    public function onUserLogin($user, $options = [])
    {
        $app = Factory::getApplication();

        // Chỉ áp dụng cho backend
        if (!$app->isClient('administrator')) {
            return;
        }

        // Nếu truy cập từ /partner
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/partner') === 0) {
            // Redirect về /partner thay vì /administrator
            $app->redirect('/partner');
        }
    }

    /**
     * Bắt event onAfterRoute để xử lý mọi URL /partner
     * có thể thêm logic ACL nếu cần
     */
    public function onAfterRoute()
    {
        $app = Factory::getApplication();

        if (!$app->isClient('administrator')) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/partner') === 0) {
            // Có thể thêm custom logic ACL hoặc rewrite
            // ví dụ: chặn user không phải Partner nhóm
        }
    }
}
