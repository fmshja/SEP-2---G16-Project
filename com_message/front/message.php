<?php
/**
 * This is the frontend of the message-component
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
$document = Factory::getDocument();
$document->addStyleSheet(JURI::root(true) . '/components/com_message/style.css');
$user = JFactory::getUser();

//Establish database connection
$db = JFactory::getDbo();
$query = $db->getQuery(true);

//Set up mailer variable and assign current user as sender
if($user->id!=0){
    $mailer = JFactory::getMailer();
    $config = JFactory::getConfig();
    //$sender = array($user->email,$user->username);
    $sender = array( 
        $config->get( 'mailfrom' ),
        $config->get( 'fromname' ) 
    );
    $mailer->setSender($sender);
}

if(isset($_POST['sendEmail'])){
    //Add recipient to the email
    $recipient=$_POST['recipient'];
    $mailer->addRecipient($recipient);
    //Add body and subject to the email
    $date=$_POST['meeting'];
    $time=$_POST['mtime'];
    $message=$_POST['message'];
    //$body=$date . $time . $message;
    $body='test';
    $mailer->setSubject('Meeting proposal');
    $mailer->setBody($body);
    //send the email
    $send = $mailer->Send();
    if($send!==true){
        echo 'Error sending email: ';
    } 
    else{
        echo 'Mail sent';
    }
}

//Render form for event planning
function renderForm($i,$email){
    echo "<div class=". $i. " style=\"display: none;\">";
    echo "<form action=\"\" method=\"post\">";
    echo "<label for=\"recipient\">Recipient:</label>";
    echo "<input type=\"text\" id=\"recipient\" name=\"recipient\" value=\"". $email.  "\" required><br><br>";
    echo "<label for=\"meeting\">Date:</label>";
    echo "<input type=\"date\" id=\"meeting\" name=\"meeting\" required>";
    echo "<label for=\"mtime\">Select a time:</label>";
    echo "<input type=\"time\" id=\"mtime\" name=\"mtime\" required>";
    echo "<textarea rows=\"5\" cols=\"100\" maxlength=\"255\" name=\"message\" required></textarea>";

    echo "<input type=\"submit\" name=\"sendEmail\" value=\"Send\">";
    echo "</form>";
    echo "</div>";
}

//Select users group id from database and return it
function getGroup(){
    $user = JFactory::getUser();
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('id_group','id_user')))
    ->from($db->quoteName('app_formed_user_groups'))
    ->where($db->quoteName('id_user') . ' = '. $db->quote($user->id));
    $db->setQuery($query);
    $group = $db->loadRowList();
    $group=$group[0];
    $id=$group[0];
    return $id;
}

//Get matched users with group id
function getMatches($id){
    $user = JFactory::getUser();
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('id_user')))
    ->from($db->quoteName('app_formed_user_groups'))
    // . ' AND ' . $db->quote('id_user'). ' != '. $db->quote($user->id)
    ->where($db->quoteName('id_group') . ' = '. $db->quote($id));
    $db->setQuery($query);
    $matches = $db->loadRowList();
    return $matches;
}

function getMatchData($mid){
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('id','first_name','last_name','id_email','profile_pic','introduction')))
    ->from($db->quoteName('app_users'))
    ->where($db->quoteName('id') . ' = '. $db->quote($mid));
    $db->setQuery($query);
    $mdata = $db->loadRowList();
    return $mdata;
}

?>
<!DOCTYPE html>
<html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact colleague</title>
</head>
<body>
    <?php
    //Check if user is logged in
    if($user->id!=0){
        echo "<h1>You were matched with following users:</h1>";
        echo "<h2>Click a name to propose a meeting with them.</h2>";
        $id=getGroup();
        //echo $id;
        $matches=getMatches($id);
        for($i=0;$i<count($matches);$i++){
            $userid=$matches[$i];
            if($userid[0]!=$user->id){
                $mdata=getMatchData($userid[0]);
                //$mdata now contains a matched user data
                $mdata=$mdata[0];
                $email=$mdata[3];

                echo "<button id=". $i. " type=\"button\" class=\"btn\" onclick=\"toggleForm(this.id)\">". $mdata[1]. "</button>";
                renderForm($i,$email);
            }
        }
    }
    //Print an error message
    else{
        echo "You are not supposed to be here.";
    }
    ?>


<script type="text/javascript">
//add javascript here
function toggleForm(id) {
            var x = document.getElementsByClassName(id);
            if(x[0].style.display === "none") {
                x[0].style.display = "block";
            } 
            else {
                x[0].style.display = "none";
            }
        }
</script>
</body>
</html>