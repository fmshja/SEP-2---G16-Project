<?php
/**
 * This component queries the groups and interests from database and
 * displays them. Needs tables app_interests and app_interests_groups to
 * exists with content in order to work. 
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();
$options = array("version" => "auto");
$document->addStyleSheet(JURI::root(true) . '/administrator/components/com_interests/style.css');

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
$results = $db->loadRowList();

// Do another query for group id's and names (needs a fix that removes the need for a second query variable)
$query2 = $db->getQuery(true);
$query2->select($db->quotename(array('c.id','c.group_name')));
$query2->from($db->quotename('app_interests_groups','c'));
$db->setQuery($query2);
$groups=$db->loadRowList();

// Check if form was submitted
if(isset($_POST['submitGroup'])){ 
    // Get the group name
    $input = $_POST['group-name'];
    $query->clear();
    $columns = array('group_name');
    $values = array($db->quote($input));
    $query
        ->insert($db->quoteName('app_interests_groups'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();
    header("Refresh:0");
}

// Check if form was submitted
if(isset($_POST['submitInterest'])){ 
    $groupid = $_POST['group-names'];
    $iname = $_POST['interest-name'];
    $query->clear();
    $columns = array('id_group', 'interest_name');
    $values = array($db->quote($groupid), $db->quote($iname));
    $query
        ->insert($db->quoteName('app_interests'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();
    header("Refresh:0");
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
    <section class="guide-text">
        <h1>Manage interests</h1>
        <p>On this page, you can view the interest categories and their interests.</p>
        <p>You can also add new categories and interests to the categories.</p>
        <hr>
    </section>
    
    <section class="add-new-element">
        <!-- Add a new interest category -->
        <div class="new-category">
            <button id="open-group" class="btn" onclick="toggleForm('group', 'open-group')"> + ADD A NEW CATEGORY </button>

            <div id="group" class="section-bg" style="display: none;">
                <form action="" method="post">
                    <h2>Add a new interest category</h2>

                    <div class="input-data">
                        <label for="group-name">Enter a name for the category</label>
                        <input type="text" name="group-name" class="text-input" required>
                    </div>
            
                    <div class="action-buttons flex">
                        <button type="submit" class="btn" name="submitGroup">Save</button>
                        <button type="button" class="btn" onclick="toggleForm('group', 'open-group')">Close</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add a new interest -->
        <div class="new-interest">
            <button id="open-interest" class="btn" onclick="toggleForm('interest', 'open-interest')"> + ADD A NEW INTEREST </button>

            <div id="interest" class="section-bg" style="display: none;">
                <form action="" method="post">
                    <h2>Add a new interest to a category</h2>

                    <div class="input-data">
                        <label for="group-names">Choose a category</label>
                        <select name="group-names" class="text-input" required>
                            <option value="default" selected="true" disabled="disabled">---</option>
                            <?php
                                for($i = 0; $i < count($groups); $i++){
                                    $row = $groups[$i];
                                    echo "<option value=". $row[0]. ">". $row[1]. "</option>";
                                }
                            ?>
                        </select>
                    </div>
                
                    <div class="input-data">
                        <label for="interest-name">Enter a name for the interest</label>
                        <input type="text" name="interest-name" class="text-input" required>
                    </div>

                    <div class="action-buttons flex">
                        <button type="submit" class="btn" name="submitInterest">Save</button>
                        <button type="button" class="btn" onclick="toggleForm('interest', 'open-interest')">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <hr>

    <!-- View and select interests -->
    <section class="select-interests">
        <form action="" method="post">
            <?php
                // Loop the second query for group names
                for ($i = 0; $i < count($groups); $i++) {
                    $row = $groups[$i];

                    echo "<div class=\"interest-group\">";
                    // Create a button with group name as text
                    echo "<button id=". $i." type=\"button\" class=\"btn\" onclick=\"toggleInterest(this.id)\">". $row[1]. "</button>";
                
                    // Interests are inside div that is not displayed until group name is clicked
                    echo "<div class=". $i." \" flex fxdir-default\" style=\"display: none;\">";
                    // Loop the first guery for interest names and echo them where id's match
                    for($j = 0; $j < count($results); $j++){
                        $row2 = $results[$j];
                        
                        if($row[0] == $row2[0]){
                            // Render the interest in a checkbox element.
                            echo "<div class=\"cbox-custom\">";
                                echo "<input type=\"checkbox\" id=\"". $row2[3]. "\" value=\"". $row2[3]. "\">";
                                echo "<label for=\"". $row2[3]. "\">". $row2[3]. "</label>";
                            echo "</div>";
                        }
                        
                    }
                    echo "</div>";
                    echo "</div>";
                }
            ?>
        </form>
    </section>
    

    <script type="text/javascript">
        // Changes the visibility of interests in a interest category based on 
        // if they are visible or not.
        function toggleInterest(id) {
            var x = document.getElementsByClassName(id);
            if (x[0].style.display === "none") {
                x[0].style.display = "block";
            } 
            else {
                x[0].style.display = "none";
            }
        }

        // Changes the visibility of the addition forms for the categories and 
        // the interests and the display text of those buttons. 
        function toggleForm(type, btn_type) {
            var x = document.getElementById(type);
            var y = document.getElementById(btn_type);

            if (x.style.display === "none") {
                x.style.display = "block";
                y.innerHTML = "- CLOSE THE FORM";
            }
            else {
                x.style.display = "none";
                if (btn_type === "open-group") {
                    y.innerHTML = "+ ADD A NEW CATEGORY";
                }
                else {
                    y.innerHTML = "+ ADD A NEW INTEREST";
                }
            }
        }
    </script>
</body>
</html>