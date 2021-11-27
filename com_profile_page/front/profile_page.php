<?php
/**
 * This is the profile page component.
 * Create folder profile_pictures in joomla/images for profile pictures
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
$document = Factory::getDocument();
$document->addStyleSheet(JURI::root(true) . '/components/com_profile_page/style.css');


// Get user
$user = JFactory::getUser();

// Set up access to database
$db = JFactory::getDbo();
$query = $db->getQuery(true);

// Check if user has already existing user data
try{
    $query->select($db->quoteName('id'))
    ->from($db->quoteName('app_users'))
    ->where($db->quoteName('id') . ' = '. $db->quote($user->id));
    $db->setQuery($query);
    $exist = $db->loadRowList();
}
catch(Exception $e){

}

// Check if form was submitted
if(isset($_POST['submitUserData'])){ 
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $introduction = $_POST['introduction'];
    $interests=array();
    $user = JFactory::getUser();
    $imagelink=null;

    // If user submitted a profile picture
    if(isset($_FILES['propic'])){
        // Verify image
        $file=$_FILES['propic'];
        $uploadOk=verifyFile($file);
        // Upload the picture if it passes verification
        if($uploadOk==1){
            $userfilename=$file['name'];
            move_uploaded_file($file['tmp_name'],"images/profile_pictures/".$userfilename);
        }
    }

    // Loop through the form and push the values to an array
    foreach($_POST['interest'] as $value){
        array_push($interests, $value);
    }
    // Encode the array to json-format
    $interests=json_encode($interests);

    // Save interests to database
    /*
    $query->clear();
    $query=$db->getQuery(true);
    $columns=array('User_Id','Interest_Id');
    $values=array($db->quote($user->id), $db->quote($interests));

    $query
        ->insert($db->quoteName('app_user_interests'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();
    header("Refresh:0");
    */
}

// Check the file for various things
function verifyFile($file){
    $uploadOk = 1;
    $filename=$file['name'];
    $filesize=$file['size'];
    $filetmp=$file['tmp_name'];
    $filetype=$file['type'];
    $tmp=explode('.', $filename);
    $file_ext=end($tmp);
    $target_file="images/profile_pictures/" . basename($filename);

    $extensions= array("jpeg","jpg","png");
    // Check file type
    if(in_array($file_ext,$extensions)=== false){
        $uploadOk=0;
    }
    // Check filesize
    if($filesize > 2097152) {
        $uploadOk=0;
    }
    // Check if file already with same name exists
    if (file_exists($target_file)) {
        $uploadOk=0;
    }
    // Return 0 if problems were encountered, otherwise return 1.
    return $uploadOk;
}

?>

<!DOCTYPE html>
<html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Connecting Colleagues</title>
</head>
<body>
    <?php  
        // If user has finished account creation
        if ($exist != null) {
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
        // Ask the user for more data.
        else {
            echo "<section class=\"additional-info bg\"";
            echo "<div class=\"mbox\">";
                echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";

                    echo "<div class=\"mbox-instructions\">";
                        echo "<h2>Welcome to the connecting colleagues!</h2>";
                        echo "<h3>Let us finalize your account.</h3>";
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<label for=\"fname\">Your first name</label>";
                        echo "<input type=\"text\" name=\"fname\" class=\"text-input\" required>";
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<label for=\"lname\">Your last name</label>";
                        echo "<input type=\"text\" name=\"lname\" class=\"text-input\" required>";
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<h3>Select some initial interests</h3>";
                        // Get some intrests from the database for the user to choose from.
                        $query->clear();
                        $query  ->select($db->quoteName(['id','interest_name']))
                                ->from($db->quoteName('app_interests'));
                        $db->setQuery($query);
                        $dbinterest = $db->loadRowList();

                        for ($i = 0; $i < 3; $i++) {
                            $row = $dbinterest[$i];
                            echo "<input type=\"checkbox\" name=\"interest[]\" value=\"". $row[0]. "\">". $row[1]. "</input>";
                        }
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<h3>Write a short introduction of yourself</h3>";
                        echo "<textarea rows=\"4\" cols=\"50\" maxlength=\"255\" name=\"introduction\"></textarea>";
                    echo "</div>";


                    echo "<div class=\"form-control\">";
                        echo "<h2>Upload a profile pic</h2>";
                        echo "<input type=\"file\" name=\"propic\">";
                    echo "</div>";
                    
                    echo "<div class=\"action-buttons\">";
                        echo "<button type=\"submit\" name=\"submitUserData\" class=\"btn\"> Save </button>";
                    echo "</div>";

                echo "</form>";
            echo "</div>";
        }
    ?>

    <script type="text/javascript">
    //add javascript here
    </script>
</body>
</html>