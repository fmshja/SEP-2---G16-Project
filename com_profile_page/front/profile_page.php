<?php
/**
 * This is a component
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
$document = Factory::getDocument();
$document->addStyleSheet("components/com_profile_page/style.css");

//get user
$user = JFactory::getUser();

//set up access to database
$db = JFactory::getDbo();
$query = $db->getQuery(true);

//Check if user has already existing user data
try{
    $query->select($db->quoteName('id'))
    ->from($db->quoteName('app_users'))
    ->where($db->quoteName('id') . ' = '. $db->quote($user->id));
    $db->setQuery($query);
    $exist = $db->loadRowList();
}
catch(Exception $e){

}

//check if form was submitted
if(isset($_POST['submitUserData'])){ 
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $introduction = $_POST['introduction'];
    $interests=array();
    //loop through the form and push the values to an array
    foreach($_POST['interest'] as $value){
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

    $query->clear();
    $query
        ->insert($db->quoteName('app_user_interests'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();
    header("Refresh:0");
}

?>
<!DOCTYPE html>
<html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
</head>
<body>
    <?php  
    //if user has finished account creation
    if($exist!=null){
        echo "
        <div class=\"container\">
            <div class=\"side\">
                <p>picture</p>
                <p>name</p>
            </div>
            <div class=\"content_area\">
                <p>showcase area</p>
            </div>
        </div>";
    }
    //more user data asked
    else{
        echo "<form action=\"\" method=\"post\">
        <h1>Welcome to the connecting colleagues!</h1>
        <h2>Let us finalize your account.</h2>
        <label for=\"fname\"><b>First name:</b></label>
        <input type=\"text\" placeholder=\"Enter your first name\" name=\"fname\" required>
        <label for=\"lname\"><b>Last name:</b></label>
        <input type=\"text\" placeholder=\"Enter your last name\" name=\"lname\" required>
        <h2>Select some initial interests:</h2>";
        $query->clear();
        $query->select($db->quoteName(['id','interest_name']))
        ->from($db->quoteName('app_interests'));
        $db->setQuery($query);
        $dbinterest = $db->loadRowList();

        for($i=0;$i<3;$i++){
            $row=$dbinterest[$i];
            echo "<input type=\"checkbox\" name=\"interest[]\" value=\"". $row[0]. "\">". $row[1]. "</input>";
        }

        echo"<h2>Write a short introduction:</h2>
            <textarea rows=\"4\" cols=\"50\" maxlength=\"255\" name=\"introduction\"></textarea>

            <h2>Upload a profile pic:</h2>
            <input type=\"file\" name=\"propic\" id=\"fileupload\">
            <button type=\"submit\" class=\"button\" name=\"submitUserData\">save</button>
        </form>";
    }
    ?>

<script type="text/javascript">
//add javascript here
</script>
</body>
</html>
