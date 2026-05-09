<?php

/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\Factory;

$app   = Factory::getApplication();
$option = $app->input->get('option');
$task =  $app->input->get('task');

$enduser_view = false;
$enduser_view2 = false;

if ($option == 'com_atelman') {
    if ($task == 'enduserrmarequest') {
        $enduser_view = true;
    }
    if ($task == 'insertRMADetail' || $task == 'insertEndUserDetail' || $task == 'editEndUserDetail') {
        $enduser_view2 = true;
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>">

<head>
    <jdoc:include type="head" />
    <?php if ($enduser_view || $enduser_view2) : ?>
        <link rel="stylesheet" href="administrator/templates/rt_missioncontrol_j15/css/core.css" type="text/css" />
        <link rel="stylesheet" href="administrator/templates/rt_missioncontrol_j15/css/core-gecko.css" type="text/css" />
        <link rel="stylesheet" href="administrator/templates/rt_missioncontrol_j15/css/colors.css.php" type="text/css" />
        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Josefin+Sans+Std+Light&subset=latin" type="text/css" />
        <script type="text/javascript" src="media/system/js/validate.js"></script>
        <script type="text/javascript" src="media/system/js/jquery-1.10.1.js"></script>
        <script type="text/javascript" src="media/system/js/jquery-noconflict.js"></script>
        <script type="text/javascript" src="administrator/templates/rt_missioncontrol_j15/js/MC.js"></script>
        <script type="text/javascript" src="administrator/templates/rt_missioncontrol_j15/js/MC.Notice.js"></script>
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template.css" type="text/css" />
        <!--[if lte IE 6]>
			<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/ieonly.css" rel="stylesheet" type="text/css" />
		<![endif]-->

        <script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/jquery-1.10.1.js" type="text/javascript"></script>
        <script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/jquery.placeholder.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            jQuery.noConflict();
            jQuery(document).ready(function() {

            });
        </script>
    <?php endif; ?>

</head>

<?php if ($enduser_view) : // Login with Email 
?>

    <body id="mc-login" class="<?php //$mctrl->displayBodyTags(); 
                                ?>">
        <div id="mc-frame">
            <div id="mc-header">
                <div class="mc-wrapper">
                    <div id="mc-status">
                        <?php //$mctrl->displayLoginStatus(); 
                        ?>
                    </div>
                </div>
                <div id="mc-logo">
                    <img src="images/ATelesis_2color_web.png" alt="ATelesis_2color_web" />
                    <h1><?php echo JText::_("End-User RMA Submission"); ?></h1>
                </div>
            </div>
            <div id="mc-body">
                <div class="mc-wrapper">
                    <jdoc:include type="component" />
                </div>
            </div>
            <div id="mc-footer">
                <div class="mc-wrapper">
                    <p class="copyright">Allied Telesis <?php echo date("Y"); ?></p>
                </div>
            </div>
            <div id="mc-message">
                <jdoc:include type="message" />
            </div>
        </div>
        <jdoc:include type="modules" name="debug" />
    </body>
<?php elseif ($enduser_view2) : ?>

    <body id="mc-standard" class="width-1080">
        <div id="mc-frame">
            <div id="mc-header">
                <div class="mc-wrapper">
                    <div id="mc-status">
                        <?php //$mctrl->displayStatus(); 
                        ?>
                    </div>
                    <div id="mc-logo">
                        <img src="images/ATelesis_2color_web.png" alt="ATelesis_2color_web" />
                        <h1><?php echo JText::_("End-User RMA Submission"); ?></h1>
                    </div>
                    <div class="clr"></div>
                </div>
            </div>
            <div id="mc-body">
                <div class="mc-wrapper">
                    <jdoc:include type="message" />
                    <div id="mc-title">
                        <?php //$mctrl->displayTitle(); 
                        ?>
                        <?php //$mctrl->displayToolbar(); 
                        ?>
                        <div class="clr"></div>
                    </div>
                    <div id="mc-submenu">
                        <?php //$mctrl->displaySubMenu(); 
                        ?>
                    </div>

                    <div id="mc-component">
                        <jdoc:include type="component" />
                    </div>
                    <div class="clr"></div>
                </div>
            </div>

            <div id="mc-footer">
                <div class="mc-wrapper">
                    <p class="copyright">Allied Telesis <?php echo date("Y") ?></p>
                </div>
            </div>
            <div id="mc-message">

            </div>
        </div>
    </body>
<?php else: ?>

    <body>
        <div id="allied-telesis-front">
            <div id="wrapper">
                <div class="top-header">
                    <div id="logo"></div>
                    <div id="topmenu">
                        <jdoc:include type="modules" name="mainmenu" />
                    </div>
                    <div class="clear"></div>
                </div>
                <jdoc:include type="message" />
                <jdoc:include type="component" />
                <div id="footer">Allied Telesis &#169; <?php echo date("Y"); ?></div>
            </div>
        </div>
        <jdoc:include type="modules" name="debug" />
    </body>
<?php endif; ?>

</html>