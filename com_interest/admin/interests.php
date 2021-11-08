<?php
/**
 * this component queries the groups and interests from database and
 * displays them. Needs tables app_interests and app_interests_groups to
 * exists with content in order to work. 
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$document = Factory::getDocument();
$options = array("version" => "auto");
$document->addStyleSheet("components/com_displayinterests/style.css");


//establish the database connection
$db = JFactory::getDbo();
$query = $db->getQuery(true);
//form the query 
$query->select($db->quoteName(['a.id','a.group_name','b.id','b.interest_name'],['gid','group_name','iid','interest_name']));
$query->from($db->quoteName('app_interests_groups','a'));
$query->innerjoin($db->quoteName('app_interests','b') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.id_group'));

// do the query
$db->setQuery($query);

//save the results in an array
$results=$db->loadRowList();

//do another query for group id's and names (needs a fix that removes the need for a second query variable)
$query2 = $db->getQuery(true);
$query2->select($db->quotename(array('c.id','c.group_name')));
$query2->from($db->quotename('app_interests_groups','c'));
$db->setQuery($query2);
$groups=$db->loadRowList();

if(isset($_POST['submitGroup'])){ //check if form was submitted
    $input = $_POST['gname']; //get group name
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

//check if form was submitted
if(isset($_POST['submitInterest'])){ 
    $groupid = $_POST['group-names'];
    $iname = $_POST['iname'];
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
<html>
<head>
<title>Interests</title>
</head>
<body>
<h1>This page lists interests</h1>

<button id="openform" onclick="openForm('group')">+ ADD CATEGORY</button>
<div id="group" style="display: none;">
    <form action="" method="post">
        <h1>Add new category</h1>
        <label for="gname"><b>category name:</b></label>
        <input type="text" placeholder="Enter category name" name="gname" required>
        <button type="submit" class="button" name="submitGroup">save</button>
        <button type="button" class="button" onclick="closeForm('group')">Close</button>
    </form>
</div>

<button id="openform" onclick="openForm('interest')">+ ADD INTEREST</button>
<div id="interest" style="display: none;">
    <form action="" method="post">
        <h1>Add new interest</h1>

        <label for="group-names"><b>Choose a category name:</b></label>
        <select name="group-names" id="group-names">
        <?php
        for($i=0;$i<count($groups);$i++){
            $row=$groups[$i];
            echo "<option value=". $row[0]. ">". $row[1]. "</option>";
        }
        ?>
        </select>
        <label for="iname"><b>interest name:</b></label>
        <input type="text" placeholder="Enter interest name" name="iname" required>
        <button type="submit" class="button" name="submitInterest">save</button>
        <button type="button" class="button" onclick="closeForm('interest')">Close</button>
    </form>
</div>

<form action="" method="post">
    <?php
    //loop the second query for group names
    for($i=0;$i<count($groups);$i++){
        $row=$groups[$i];
        //create a button with group name as text
        echo "<button id=". $i. " type=\"button\" onclick=\"toggleInterest(this.id)\">". $row[1]. "</button>";
        //interests are inside div that is not displayed until group name is clicked
        echo "<div class=". $i. " style=\"display: none;\">";
        //loop the first guery for interest names and echo them where id's match
        for($j=0;$j<count($results);$j++){
            $row2=$results[$j];
            if($row[0]==$row2[0]){
                //render the interest in a checkbox element 
                echo "<input type=\"checkbox\" name=\"". $row2[3]. "\">". $row2[3]. "</input>";
            }
        }
        echo "</div>";
    }
    ?>
    <!--<input type="submit" name="SubmitButton" value="Submit"/>-->
</form>
<p id="test"></p>

<script type="text/javascript">
//changes the visibility of interests based on if they are visible or not
function toggleInterest(id){
    var x = document.getElementsByClassName(id);
    if(x[0].style.display==="none") {
        x[0].style.display="block";
    } 
    else {
        x[0].style.display="none";
    }
}
function openForm(type){
    //set form visible
    document.getElementById(type).style.display = "block";
}
function closeForm(type){
    //hide the form
    document.getElementById(type).style.display = "none";
}
</script>
</body>
</html>
