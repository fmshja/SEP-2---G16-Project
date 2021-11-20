<?php
/**
 * This is a component
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();

$document->addStyleSheet("components/com_connecting_home/style.css");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connecting Colleagues</title>
</head>
<body>
    <!-- All the content on the front page -->
    <div class="container flex">
        <!-- Showcase -->
        <section class="showcase-bg">
            <div class="showcase-content">
                <h2>Here you can put anything you want to showcase to the site's users, the old and the new alike!</h2>
            </div>
        </section>
    </div>
</body>
</html>