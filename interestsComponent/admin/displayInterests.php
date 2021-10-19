<?php
/**
 * this component queries the groups and interests from database and
 * displays them. Needs tables app_interests and app_interests_groups to
 * exists with content in order to work. 
 */
defined('_JEXEC') or die('Restricted access');

//establish the database connection
$db = JFactory::getDbo();
$query = $db->getQuery(true);
//form the query 
$query->select($db->quoteName(['a.id','a.group_name','b.id','b.interest_name'],['gid','group_name','iid','interest_name']));
$query->from($db->quoteName('app_interests_groups','a'));
$query->innerjoin($db->quoteName('app_interests','b') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.id_group'));

// do the query
$db->setQuery($query);

//results in an array
$results=$db->loadRowList();

//do another query for group id's and names
$query2 = $db->getQuery(true);
$query2->select($db->quotename(array('c.id','c.group_name')));
$query2->from($db->quotename('app_interests_groups','c'));
$db->setQuery($query2);
$groups=$db->loadRowList();

//TODO: make the li elements clickable and make clicking submit actually do something
?>
<!DOCTYPE html>
<html>
<body>
<h1>This page lists interests</h1>
<form action="" method="post">
    <?php
    //loop the second query for group names
    for($i=0;$i<count($groups);$i++){
        $row=$groups[$i];
        echo "<ul><b>". $row[1]. "</b>";
        //loop the first guery for interest names and echo them where id's match
        for($j=0;$j<count($results);$j++){
            $row2=$results[$j];
            if($row[0]==$row2[0]){
                echo "<li>". $row2[3]. "</li>";
            }
        }
        echo "</ul>";
    }
    ?>
    <input type="submit" name="SubmitButton" value="Submit"/>
</form>  

</body>
</html>
