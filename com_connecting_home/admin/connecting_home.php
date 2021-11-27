<?php
/**
 * this is admin side of component
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
    <title>Home | Connecting Colleagues</title>
</head>
<body>
    <div class="guide-text bg">
        <p>Sorry, there's nothing on the administrator side!</p>
        <p>On the front side of this component, there is the main home page for the website.</p>
    </div>

    <script type="text/javascript">
    //add javascript here
    </script>
</body>
</html>