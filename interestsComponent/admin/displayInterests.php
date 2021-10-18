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
//form the query TODO: fix the problem with group id being replaced in results
$query->select($db->quoteName(array('a.id','a.group_name','b.id','b.interest_name')));
$query->from($db->quoteName('app_interests_groups','a'));
$query->innerjoin($db->quoteName('app_interests','b') . ' ON ' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.id_group'));

// do the query
$db->setQuery($query);
$results = $db->loadObjectList();
//echo the results temporarily to see that the query is succesful
echo json_encode($results);

//TODO: present the query results in HTML
//TODO:add query results to the form
?>
<!DOCTYPE html>
<html>
<body>
<h1>This page lists interests</h1>
<form action="" method="post">
  
</form>  

</body>
</html>