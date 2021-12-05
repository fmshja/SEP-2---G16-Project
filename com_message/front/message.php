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

//Send the message
if(isset($_POST['sendMessage'])){
    //Saved variables
    $recipientId=$_POST['recipient_id'];
    $sender_name=$_POST['sender'];
    $sender_email=$_POST['sender_email'];
    $message=$_POST['message'];
    $meeting_date=$_POST['meeting'];
    $meeting_time=$_POST['mtime'];

    //Store variables to the database
    $query->clear();
    $query=$db->getQuery(true);
    $columns=array('recipient_id','sender_name','sender_email','message','meeting_date','meeting_time');
    $values=array($db->quote($recipientId), $db->quote($sender_name), $db->quote($sender_email), $db->quote($message), $db->quote($meeting_date), $db->quote($meeting_time));

    $query
        ->insert($db->quoteName('app_messages'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();
}

//Render form for event planning
function renderForm($i,$recipientId,$fname,$email){
    //Form start
    echo "<div class=". $i. " style=\"display: none;\">";
    echo "<form action=\"\" method=\"post\">";
    //Hidden id-field
    echo "<input type=\"hidden\" name=\"recipient_id\" id=\"recipient_id\" value=\"". $recipientId. "\"/>";
    //Sender name
    echo "<input type=\"hidden\" id=\"sender\" name=\"sender\" value=\"". $fname.  "\" required><br><br>";
    //Hidden email-field
    echo "<input type=\"hidden\" name=\"sender_email\" id=\"sender_email\" value=\"". $email. "\"/>";
    //Meeting date
    echo "<label for=\"meeting\">Date:</label>";
    echo "<input type=\"date\" id=\"meeting\" name=\"meeting\" required>";
    //Meeting time
    echo "<label for=\"mtime\">Select a time:</label>";
    echo "<input type=\"time\" id=\"mtime\" name=\"mtime\" required>";
    //Message
    echo "<textarea rows=\"5\" cols=\"100\" maxlength=\"255\" name=\"message\" required></textarea>";

    echo "<input type=\"submit\" name=\"sendMessage\" value=\"Send\">";
    echo "</form>";
    echo "</div>";
}

//Select users group id from database and return it.
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

//Get matched users with group id.
function getMatches($id){
    $user = JFactory::getUser();
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('id_user')))
    ->from($db->quoteName('app_formed_user_groups'))
    ->where($db->quoteName('id_group') . ' = '. $db->quote($id));
    $db->setQuery($query);
    $matches = $db->loadRowList();
    return $matches;
}
//Get all data of a single user by id.
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
//Get current users full name and return it.
function getFullName(){
    $db = JFactory::getDbo();
    $user = JFactory::getUser();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('first_name','last_name')))
    ->from($db->quoteName('app_users'))
    ->where($db->quoteName('id') . ' = '. $db->quote($user->id));
    $db->setQuery($query);
    $fname = $db->loadRowList();
    $fname=$fname[0];
    $fname=$fname[0]. " ". $fname[1];
    return $fname;
}
//Get all messages adresssed to the logged in user.
function getMessages(){
    $db = JFactory::getDbo();
    $user = JFactory::getUser();
    $query = $db->getQuery(true);
    $query->select($db->quoteName(array('recipient_id','sender_name','sender_email','message','meeting_date','meeting_time')))
    ->from($db->quoteName('app_messages'))
    ->where($db->quoteName('recipient_id') . ' = '. $db->quote($user->id));
    $db->setQuery($query);
    $messages = $db->loadRowList();
    return $messages;
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
        $matches=getMatches($id);
        for($i=0;$i<count($matches);$i++){
            $userid=$matches[$i];
            if($userid[0]!=$user->id){
                $mdata=getMatchData($userid[0]);
                //$mdata now contains a matched user data
                $mdata=$mdata[0];
                //Fullname of the match
                $mname=$mdata[1]. " ". $mdata[2];

                $recipientId=$mdata[0];
                $fname=getFullName();
                $email=$user->email;

                echo "<button id=". $i. " type=\"button\" class=\"btn\" onclick=\"toggleForm(this.id)\">". $mname. "</button>";
                renderForm($i,$recipientId,$fname,$email);
            }
        }
        //Messages adressed to the user.
        echo "<h2>Your messages:</h2>";
        $messages=getMessages();
        for($i=0;$i<count($messages);$i++){
            $message=$messages[$i];
            echo "<div class=\"message-box\" style=\"border-style: solid;\">";
            echo "<p><b>Sender:</b> ".$message[1]. "</p>";
            echo "<p><b>Sender email: </b>".$message[2]. "</p>";
            echo "<p><b>Proposed meeting date </b>: ".$message[4]. "<b> meeting time: </b>".$message[5]. "</p>";
            echo $message[3];
            echo "</div>";
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
