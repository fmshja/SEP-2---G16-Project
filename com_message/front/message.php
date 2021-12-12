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
function renderForm($i,$recipientId,$fname,$email) {
    // Form start
    echo "<div class=". $i. " class=\"event-form\" style=\"display: none;\">";
    echo "<form action=\"\" method=\"post\">";
            
        // Hidden input fields for the id, name and email of the sender.
        echo "<div class=\"hidden-form-control\" style=\"display: none;\">";
            echo "<input type=\"hidden\" name=\"recipient_id\" id=\"recipient_id\" value=\"". $recipientId. "\" required>";
            echo "<input type=\"hidden\" id=\"sender\" name=\"sender\" value=\"". $fname.  "\" required>";
            echo "<input type=\"hidden\" name=\"sender_email\" id=\"sender_email\" value=\"". $email. "\" required>";
        echo "</div>";

        // A date for the meeting
        echo "<div class=\"form-control\">";
            echo "<label for=\"meeting\">A date for the meeting</label>";
            echo "<input type=\"date\" id=\"meeting\" name=\"meeting\" class=\"text-input medium-input\" required>";
        echo "</div>";

        // A time of the day for the meeting
        echo "<div class=\"form-control\">";
            echo "<label for=\"mtime\">A time for the meeting</label>";
            echo "<input type=\"time\" id=\"mtime\" name=\"mtime\" class=\"text-input medium-input\" required>";
        echo "</div>";

        // Additional message for the one receiving the meeting invitation.
        echo "<div class=\"form-control\">";
            echo "<label for=\"message\">A message to the receiver</label>";
            echo "<textarea name=\"message\" class=\"text-input\" maxlength=\"255\" required></textarea>";
        echo "</div>";

        echo "<div class=\"action-buttons flex\">";
            echo "<input type=\"submit\" class=\"btn\" name=\"sendMessage\" value=\"Send\">";
        echo "</div>";

    echo "</form>";
    echo "</div>";
}

//Select users group id from database and return it.
function getGroup(){
    $user = JFactory::getUser();
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $id=null;
    $query->select($db->quoteName(array('id_group','id_user')))
    ->from($db->quoteName('app_formed_user_groups'))
    ->where($db->quoteName('id_user') . ' = '. $db->quote($user->id));
    $db->setQuery($query);
    $group = $db->loadRowList();
    if($group!=null){
        $group=$group[0];
        $id=$group[0];
    }
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
    <title>Messaging | Contact Colleagues</title>
</head>
<body>
    <?php
        // Check if user is logged in.
        if ($user->id != 0) {
            echo "<section class=\"mtach-info bg-light\">";

                echo "<div class=\"guide-text\">";
                    echo "<h2>You were matched with following users:</h2>";
                    echo "<h3>Click a name to propose a meeting with them.</h3>";
                    echo "<hr>";
                echo "</div>";
        
                echo "<div class=\"flex fxdir-default\">";
                    $id=getGroup();
                    if(is_null($id)==false){
                        $matches=getMatches($id);
                        for ($i=0;$i<count($matches);$i++) {
                            $userid=$matches[$i];
                            if ($userid[0]!=$user->id) {
                                $mdata=getMatchData($userid[0]);
                                // $mdata now contains a matched user data
                                $mdata=$mdata[0];
                                // Fullname of the match
                                $mname=$mdata[1]. " ". $mdata[2];

                                $recipientId=$mdata[0];
                                $fname=getFullName();
                                $email=$user->email;

                                echo "<div class=\"flex-item\">";
                                echo "<input id=". $i. " type=\"button\" class=\"matches mbtn btn\" onclick=\"toggleForm(this.id)\" value=". $mname .">";
                                renderForm($i,$recipientId,$fname,$email);
                                echo "</div>";
                            }
                        }
                    }
                    else{
                        echo "<p>There are no matches for you at this time.</p>";
                        echo "<p>Please wait for the next matching period.</p>";
                    }
                    echo "</div>";
                echo "</section>";

            // Messages addressed to the user.
            echo "<section class=\"received-msg\">";
                echo "<div class=\"guide-text\">";
                    echo "<h2>Received messages</h2>";
                    echo "<hr>";
                echo "</div>";

                echo "<div class=\"flex fxdir-default\">";
                    $messages=getMessages();
                    for ($i=0;$i<count($messages);$i++) {
                        $message=$messages[$i];

                        echo "<div class=\"msg-box flex-item\">";

                            echo "<div class=\"sender-info\">";
                                echo "<div>";
                                    echo "<div class=\"msg-label\">Sender's name:</div>";
                                    echo "<div class=\"msg-content\">". $message[1] ."</div>";
                                echo "</div>";

                                echo "<div>";
                                    echo "<div class=\"msg-label\">Sender's email:</div>";
                                    echo "<div class=\"msg-content\">". $message[2] ."</div>";
                                echo "</div>";

                                echo "<div>";
                                    echo "<div class=\"msg-label\">Proposed meeting date:</div>";
                                    echo "<div class=\"msg-content\">". $message[4] ."</div>";
                                echo "</div>";

                                echo "<div>";
                                    echo "<div class=\"msg-label\">Proposed meeting time:</div>";
                                    echo "<div class=\"msg-content\">". $message[5] ."</div>";
                                echo "</div>";
                            echo "</div>";

                            echo "<div class=\"sender-msg\">";
                                echo "<div class=\"msg-label\">A message from the sender:</div>";
                                echo "<div class=\"msg-content\">" . $message[3] . "</div>";
                            echo "</div>";

                        echo "</div>";
                    }
                echo "</div>";
            echo "</section>";
        }
        // Send an alert to the user.
        else {
            $message = "Please log in with your Connecting Colleagues account!";
            echo "<script>alert($message);</script>";
        }
    ?>

    <script type="text/javascript">
        // Toggles the form and the color of the button when a button is clicked.
        function toggleForm(id) {
            var x = document.getElementsByClassName(id);
            var y = document.getElementById(id);
            if (x[0].style.display === "none") {
                x[0].style.display = "block";
                y.style.backgroundColor = "var(--primary-color)";
                y.style.color = "var(--secondary-color)";
            } 
            else {
                x[0].style.display = "none";
                y.style.backgroundColor = "var(--secondary-color)";
                y.style.color = "var(--primary-color)";
            }
        }
    </script>
</body>
</html>
