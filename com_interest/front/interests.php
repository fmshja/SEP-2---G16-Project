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
    // Make sure some interests are chosen 
    if (!empty($_POST['interest']) && count($_POST['interest']) >= 3) {
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
    // The user has chosen less than three interests
    else {
        // echo "<script language=\"javascript\">";
        // echo "showSubmitAlert(true);";
        // echo "alert(\"Please choose at least three (3) interests!\")";
        // echo "</script>";
        echo "Please choose at least three (3) interests!";
    }
}

$user = JFactory::getUser();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interests | Connecting Colleagues</title>
</head>
<body>
    <section class="submit-alert" style="display: none;">
        Please choose at least three (3) interests!
    </section>

    <section class="guide-text">
        <h2>Select your interests</h2>
        <h3>Click a category to see more interests</h3>
    </section>

    <hr>

    <?php
        //echo "<p>This user's id is {$user->id}</p>";
    ?>

    <section class="select-interests">
        <form action="" method="post">
            <?php
                // Loop the second query for group names
                for ($i = 0; $i < count($groups); $i++) {
                    $row = $groups[$i];

                    echo "<div class=\"interest-group\">";
                    // Create a button with group name as text
                    echo "<button id=". $i. " type=\"button\" class=\"btn\" onclick=\"toggleInterest(this.id)\">". $row[1]. "</button>";
                
                    // Interests are inside div that is not displayed until group name is clicked
                    echo "<div class=". $i. " style=\"display: none;\">";
                    
                    // Loop the first guery for interest names and echo them where id's match
                    for ($j = 0; $j < count($results); $j++) {
                        $row2 = $results[$j];
                    
                        echo "<ul class=\"cbox-custom\">";
                        if ($row[0] == $row2[0]) {
                            // Render the interest in a checkbox element.
                            echo "<li>";
                            echo "<input type=\"checkbox\" name=\"interest[]\" id=\"". $row2[3]. "\" value=\"". $row2[3]. "\">";
                            echo "<label for=\"". $row2[3]. "\">". $row2[3]. "</label>";
                            echo "</li>";
                        }
                        echo "</ul>";
                    }
                    echo "</div>";
                    echo "</div>";
                }
            ?>
            <hr>
            <input type="submit" name="SubmitButton" value="Submit" class="btn"/>
        </form>
    </section>

    <script type="text/javascript">
        function showSubmitAlert(x) {
            if (x) {
                document.getElementsByClassName("submit-alert").style.display = "block";
            }
        }

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