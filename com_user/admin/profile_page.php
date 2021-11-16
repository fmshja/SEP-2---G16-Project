<?php
/**
 * this is admin side of component the user component. As admin does not need to 
 * adjust their own settings here, this page houses the user pairing algorithm related matters.
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();
$document->addStyleSheet("components/com_user/style.css");

if(isset($_POST['button'])){
    echo "<p>it just works</p>";
    $command = escapeshellcmd('.\components\com_user\cc_groups.py');
    $output = shell_exec($command);
}


?>
<!DOCTYPE html>
<html>
<head>
<title>Algorithm</title>
</head>
<body>
<div class="Big_Button">
    <form action="" method="post">
        <input type="submit" name="button" value="Start the algorithm"/>
    </form>
</div>

<script type="text/javascript">

</script>
</body>
</html>