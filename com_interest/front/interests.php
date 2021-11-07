<?php
/**
 * this component queries the groups and interests from database and
 * displays them. Needs tables app_interests, app_interests_groups and app_user_interests to
 * exist in order to work. 
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

//do another query for group id's and names
$query->clear();
$query = $db->getQuery(true);
$query->select($db->quotename(array('c.id','c.group_name')));
$query->from($db->quotename('app_interests_groups','c'));
$db->setQuery($query);
$groups=$db->loadRowList();

//save the formdata to database
if(isset($_POST['SubmitButton'])){
    //make sure some interests are chosen 
    if(!empty($_POST['interest'])) {
        //create empty array
        //$interests="";
        $interests=array();
        //loop through the form and push the values to an array
        foreach($_POST['interest'] as $value){
            //$interests.=$value. " ";
            array_push($interests, $value);
        }
        //encode the array to json-format
        $interests=json_encode($interests);
        $user = JFactory::getUser();

        //save interests to database
        $query->clear();
        $query=$db->getQuery(true);
        $columns=array('User_Id','Interest_Id');
        $values=array($db->quote($user->id), $db->quote($interests));

        //Check if user has already saved interests
        $query->select($db->quoteName('User_Id'))
            ->from($db->quoteName('app_user_interests'))
            ->where($db->quoteName('User_Id') . ' = '. $db->quote($user->id));
        $db->setQuery($query);
        $exist = $db->loadRowList();

        //no previous interests
        if($exist==null){
            $query->clear();
            $query
                ->insert($db->quoteName('app_user_interests'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
            $db->setQuery($query);
            $db->execute();
        }
        //update interests
        else{
            $query->clear();
            $query
                ->update($db->quoteName('app_user_interests'))
                ->set($db->quoteName('Interest_Id') . ' = '. $db->quote($interests))
                ->where($db->quoteName('User_Id') . ' = '. $db->quote($user->id));   
            $db->setQuery($query);
            $db->execute();
        }
    }
    //no interests selected
    else{
        echo "Please choose something!";
    }
}

$user = JFactory::getUser();

?>
<!DOCTYPE html>
<html>
<head>
<title>Interests</title>
</head>
<body>
<h1>Please select what interests you:</h1>
<h2>Click a category to see more</h2>

<?php
//echo "<p>This user's id is {$user->id}</p>";
?>

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
                echo "<input type=\"checkbox\" name=\"interest[]\" value=\"". $row2[2]. "\">". $row2[3]. "</input>";
            }
        }
        echo "</div>";
    }
    ?>
    <input type="submit" name="SubmitButton" value="Submit"/>
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
</script>
</body>
</html>