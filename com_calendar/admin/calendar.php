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
    <title>Calendar | Connecting Colleagues</title>
</head>
<body>
    <div class="guide-text bg">
        <p>On front side of this componen is the calendar, nothing on the administrator side.</p>
    </div>

    <script type="text/javascript">
    //add javascript here
    </script>
</body>
</html>
