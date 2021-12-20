<?php
/**
 * This component queries the groups and interests from database and
 * displays them. Needs tables app_interests, app_interests_groups and app_user_interests to
 * exist in order to work. 
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();
$options = array("version" => "auto");
$document->addStyleSheet(JURI::root(true) . '/components/com_interests/style.css');

// Establish the database connection
$db = JFactory::getDbo();
$query = $db->getQuery(true);
// Form the query 
$query->select($db->quoteName(['a.id','a.group_name','b.id','b.interest_name'],['gid','group_name','iid','interest_name']));
$query->from($db->quoteName('app_interests_groups','a'));
$query->innerjoin($db->quoteName('app_interests','b') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.id_group'));

// Do the query
$db->setQuery($query);

// Save the results in an array
$results=$db->loadRowList();

// Do another query for group id's and names
$query->clear();
$query = $db->getQuery(true);
$query->select($db->quotename(array('c.id','c.group_name')));
$query->from($db->quotename('app_interests_groups','c'));
$db->setQuery($query);
$groups=$db->loadRowList();

// Save the formdata to database
if (isset($_POST['SubmitButton'])) {
    $user = JFactory::getUser();
    // The user has chosen less than three interests
    if (empty($_POST['interest']) || count($_POST['interest']) < 3) {
        $message = "Please choose at least three (3) interests!";
        echo "<script>alert('$message');</script>";
    }
    else if($user->id==0){
        $message = "Session expired! Please log in.";
        echo "<script>alert('$message');</script>";
    }
    else {
        // Create empty array
        // $interests="";
        $interests = array();
        // Loop through the form and push the values to an array
        foreach ($_POST['interest'] as $value) {
            // $interests.=$value. " ";
            array_push($interests, $value);
        }
        // Encode the array to json-format
        $interests = json_encode($interests);
        $user = JFactory::getUser();

        // Save interests to database
        $query->clear();
        $query = $db->getQuery(true);
        $columns = array('User_Id','Interest_Id');
        $values = array($db->quote($user->id), $db->quote($interests));

        // Check if user has already saved interests
        $query->select($db->quoteName('User_Id'))
            ->from($db->quoteName('app_user_interests'))
            ->where($db->quoteName('User_Id') . ' = '. $db->quote($user->id));
        $db->setQuery($query);
        $exist = $db->loadRowList();

        // No previous interests
        if ($exist == null) {
            $query->clear();
            $query
                ->insert($db->quoteName('app_user_interests'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
            $db->setQuery($query);
            $db->execute();
        }
        // Update interests
        else {
            $query->clear();
            $query
                ->update($db->quoteName('app_user_interests'))
                ->set($db->quoteName('Interest_Id') . ' = '. $db->quote($interests))
                ->where($db->quoteName('User_Id') . ' = '. $db->quote($user->id));   
            $db->setQuery($query);
            $db->execute();
        }
    }
}

$user = JFactory::getUser();

// Fetches previous interests.
function fetchArray(){
    $db = JFactory::getDbo();
    $user = JFactory::getUser();
    $query = $db->getQuery(true);
    $query->select($db->quoteName('Interest_Id'))
        ->from($db->quoteName('app_user_interests'))
        ->where($db->quoteName('User_Id') . ' = '. $db->quote($user->id));
    $db->setQuery($query);
    $previous = $db->loadRowList();
    // Decode the json array if not null.
    if($previous!=null){
        $previous=$previous[0];
        $previous=json_decode($previous[0]);
    }
    // Make an empty array.
    else{
        $previous=Array();
    }
    return $previous;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interests | Connecting Colleagues</title>
</head>
<body>
    <?php
        $user = JFactory::getUser();
        if($user->id!=0){
            // Get array of previous selections fromm database.
            $previous=fetchArray();

            echo "<section class=\"guide-text\">";
                echo "<h2>Select your interests</h2>";
                echo "<h3>Click a category to see more interests</h3>";
                echo "<hr>";
                echo "</section>";

                    echo "<section class=\"select-interests\">";
                    echo "<form action=\"\" method=\"post\">";
        
            // Loop the second query for group names
            for ($i = 0; $i < count($groups); $i++) {
                $row = $groups[$i];

                echo "<div class=\"interest-group\">";
                // Create a button with group name as text
                echo "<button id=". $i." type=\"button\" class=\"btn\" onclick=\"toggleInterest(this.id)\">". $row[1]. "</button>";
                
                // Interests are inside div that is not displayed until group name is clicked
                echo "<div class=". $i." \" flex fxdir-default\" style=\"display: none;\">";
                        
                // Loop the first guery for interest names and echo them where id's match
                for ($j = 0; $j < count($results); $j++) {
                    $row2 = $results[$j];
                        
                    if ($row[0] == $row2[0]) {
                        // Render the interest in a checkbox element.
                        echo "<div class=\"cbox-custom\">";
                        // Check box if this was previously selected.
                        if(in_array($row2[2],$previous)){
                            echo "<input type=\"checkbox\" name=\"interest[]\" id=\"". $row2[3]. "\" value=\"". $row2[2]. "\" checked>";
                        }
                        // Otherwise render normal checkbox.
                        else{
                            echo "<input type=\"checkbox\" name=\"interest[]\" id=\"". $row2[3]. "\" value=\"". $row2[2]. "\">";
                        }
                        echo "<label for=\"". $row2[3]. "\">". $row2[3]. "</label>";
                        echo "</div>";
                    }
                }
                echo "</div>";
                echo "</div>";
                }
            
            echo "<div class=\"action-buttons\">";
                echo "<hr>";
                echo "<div class=\"flex-row\">";
                    echo "<input type=\"submit\" name=\"SubmitButton\" value=\"Submit\" class=\"btn\"/>";
                echo "</div>";
            echo "</div>";
            echo "</form>";
            echo "</section>";
        }
        else{
            echo "<p>Please log in with your Connecting Colleagues account!</p>";
        }
    ?>

    <script type="text/javascript">
        // Changes the visibility of interests based on if they are visible or not
        function toggleInterest(id) {
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
