<?php
/**
 * This is a component
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();
$options = array("version" => "auto");
$document->addStyleSheet(JURI::root(true) . '/components/com_connecting_home/style.css');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Connecting Colleagues</title>
</head>
<body>
    <!-- All the content on the front page -->
    <div class="container flex">
        <!-- Showcase -->
        <section class="showcase flex">
            <div class="showcase-title bg">
                <h2>Welcome to Connecting Colleagues!</h2>
            </div>
            <div class="showcase-content bg">
                <p>Here you can put anything you want to showcase to the site's users, the old and the new alike!</p>
            </div>
        </section>
    </div>
</body>
</html>