<?php
/**
 * This is the admin side of the component-
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();
$options = array("version" => "auto");
$document->addStyleSheet(JURI::root(true) . '/administrator/components/com_connecting_home/style.css');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | Connecting Colleagues</title>
</head>
<body>
    <div class="guide-text bg">
        <p>Sorry, there's nothing on the administrator side!</p>
        <p>On front side of this component, there is the calendar for the users.</p>
    </div>
</body>
</html>
