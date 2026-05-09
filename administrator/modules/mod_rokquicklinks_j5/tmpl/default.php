<?php defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use  Joomla\CMS\Uri\Uri;
?>
<style>
    .rok-quicklinks .quicklinks-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        list-style: none;
        padding: 0;
    }

    .rok-quicklinks .quicklinks-grid li {
        color: #fff;
        padding: 10px 15px;
        border-radius: 6px;
        background-color: #F4F8FB;
        text-align: center;
    }

    .rok-quicklinks .quicklinks-grid li a {
        color: #000;
        text-decoration: none;
        font-size: 12px;
        font-weight: normal;
        width: 100px;
        display: block;

    }

    .rok-quicklinks .quicklinks-grid li img {
        margin: 0 auto;
        padding-bottom: 10px;
        display: block;
        text-align: center;
    }
</style>
<div class="rok-quicklinks">
    <?php if (!empty($links)) : ?>
        <ul class="quicklinks-grid">
            <?php foreach ($links as $value) : ?>
                <?php
                $link = $value->link;
                ?>
                <li>
                    <a href="<?php echo htmlspecialchars($link->url); ?>">
                        <?php if (!empty($link->icon)) : ?>
                            <img src="<?php echo Uri::root() . $link->icon; ?>" alt="<?php echo htmlspecialchars($link->title); ?>">
                        <?php endif; ?>
                        <strong><?php echo htmlspecialchars($link->title); ?></strong>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>