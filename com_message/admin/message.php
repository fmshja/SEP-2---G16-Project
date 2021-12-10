<?php
/**
 * This is admin side of the message-component.
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();
$options = array("version" => "auto");
$document->addStyleSheet(JURI::root(true) . '/administrator/components/com_message/style.css');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaging | Connecting Colleagues</title>
</head>
<body>
    <div class="guide-text bg">
        <p>Sorry, there's nothing on the administrator side!</p>
        <p>On the front side of this component, there is the user messaging for the matched groups.</p>
    </div>
</body>
</html>