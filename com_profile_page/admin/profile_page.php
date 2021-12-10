<?php
/**
 * This is admin side of component the user component. As admin does not need to 
 * adjust their own settings here, this page houses the user pairing algorithm related matters.
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();
$options = array("version" => "auto");
$document->addStyleSheet(JURI::root(true) . '/administrator/components/com_profile_page/style.css');

if(isset($_POST['matching-button'])){
    $command = escapeshellcmd('python .\components\com_profile_page\run.py');
    $output = shell_exec($command.' 2>&1');
    $output = nl2br($output); // change the linebreaks to <br> tags
    // Debug print
    echo '<section class="console_output">Formed groups:<br>'. $output .'</section>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matching Algorithm | Connecting Colleagues</title>
</head>
<body>
    <section class="big-button">
        <div class="guide-text">
            <h2>A manual start-up for the matching algorithm</h2>
            <p>The admin needs to manually active the matching algorithm of the users by clicking the button below.</p>
        </div>
        <form action="" method="post">
            <input type="submit" name="matching-button" class="btn" value="Start the algorithm"/>
        </form>
    </section>
</body>
</html>