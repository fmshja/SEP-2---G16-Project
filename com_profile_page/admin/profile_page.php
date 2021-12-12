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

$console_output = "No console output";

if(isset($_POST['matching-button'])){
    // find or create the config file
    $cfg_path = '.\components\com_profile_page\run_config.json';
    $valid_cfg_existed = false;
    if(file_exists($cfg_path)) {
        // config found, use it
        $cfg = json_decode(file_get_contents($cfg_path), false);
        if (json_last_error()) {
            // failed to parse the json
            echo '<span id="error">Error while decoding run_config.json (' . json_last_error_msg() . ')<br>' .
                 '(Did you remember to escape the backslashes?)</span>';
        } else {
            $valid_cfg_existed = true;
        }
    }
    if (!$valid_cfg_existed) {
        // not found or it was invalid, create the default config
        $cfg = new stdClass();
        $cfg->python_cmd = 'python';

        if (!file_exists($cfg_path)) {
            // save the default config
            file_put_contents($cfg_path, json_encode($cfg, JSON_PRETTY_PRINT));
        }
    }

    $command = $cfg->python_cmd . ' .\components\com_profile_page\run.py';
    $console_output = shell_exec($command.' 2>&1');
    $console_output = nl2br($console_output); // change the linebreaks to <br> tags
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
    <section class="console_output"><?php echo $console_output ?></section>
</body>
</html>