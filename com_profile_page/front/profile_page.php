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
    $query->select($db->quoteName(array('id','first_name','last_name','id_email','profile_pic','introduction')))
    ->from($db->quoteName('app_users'))
    ->where($db->quoteName('id') . ' = '. $db->quote($user->id));
    $db->setQuery($query);
    $exist = $db->loadRowList();
}
catch(Exception $e){

}

// Check if form was submitted
if(isset($_POST['submitUserData'])){ 
    if (!empty($_POST['interest']) && count($_POST['interest']) >= 3) {
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
                //Rename the file to avoid overwrites
                $userfilename=$file['name'];
                $tmp=explode('.', $userfilename);
                $file_ext=end($tmp);
                $filestart=$tmp[0];
                $newfilename=null;
                $newfilename.=$user->id;
                $newfilename.=$filestart;
                $newfilename.=".";
                $newfilename.=$file_ext;
                
                //Upload the picture to the folder images/profile_pictures/
                move_uploaded_file($file['tmp_name'],"images/profile_pictures/".$newfilename);
                $imagelink=$newfilename;
            }
        }

        // Loop through the form and push the values to an array
        foreach($_POST['interest'] as $value){
            array_push($interests, $value);
        }
        // Encode the array to json-format
        $interests=json_encode($interests);
        //echo $interests;
        // Save interests to database
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

        //Save rest of the user data to the database
        $query->clear();
        $query=$db->getQuery(true);
        $columns=array('id','first_name','last_name','id_email','profile_pic','introduction');
        $values=array($db->quote($user->id), $db->quote($fname), $db->quote($lname), $db->quote($user->email), $db->quote($imagelink), $db->quote($introduction));

        $query
            ->insert($db->quoteName('app_users'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        $db->setQuery($query);
        $db->execute();

        //Check that the row was properly saved
        $query->clear();
        $query->select($db->quoteName(array('id','first_name','last_name','id_email','profile_pic','introduction')))
        ->from($db->quoteName('app_users'))
        ->where($db->quoteName('id') . ' = '. $db->quote($user->id));
        $db->setQuery($query);
        $exist = $db->loadRowList();
        
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
//Read interest categories from database
function readCategories(){
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->clear();
    $query = $db->getQuery(true);
    $query->select($db->quotename(array('id','group_name')))
          ->from($db->quotename('app_interests_groups'));
    $db->setQuery($query);
    $groups=$db->loadRowList();
    return $groups;
}
function readInterests(){
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->clear();
    $query  ->select($db->quoteName(['id','interest_name','id_group']))
            ->from($db->quoteName('app_interests'));
    $db->setQuery($query);
    $dbinterest = $db->loadRowList();
    return $dbinterest;
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
            $userData=$exist;
            $userData=$userData[0];
            echo "<div class=\"container\"><div class=\"side\">";
            echo "<img src=\"images/profile_pictures/". $userData[4]. "\" height=200 width=300 />";
            echo "<p>". $userData[1]. " ". $userData[2]. "</p>";
            echo "</div><div class=\"content_area\">";
            echo "<p>showcase area</p>";
            echo "<p>". $userData[5]."</p>";
            echo "</div></div>";
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
                    //Start of first page of form
                    echo "<div class=\"tab1\">";

                    echo "<div class=\"form-control\">";
                        echo "<label for=\"fname\">Your first name</label>";
                        echo "<input type=\"text\" name=\"fname\" class=\"text-input\" required>";
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<label for=\"lname\">Your last name</label>";
                        echo "<input type=\"text\" name=\"lname\" class=\"text-input\" required>";
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<h3>Write a short introduction of yourself</h3>";
                        echo "<textarea rows=\"4\" cols=\"50\" maxlength=\"255\" name=\"introduction\" required></textarea>";
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<h2>Upload a profile pic</h2>";
                        echo "<input type=\"file\" name=\"propic\">";
                    echo "</div>";
                    echo "<div class=\"action-buttons\">";
                        echo "<button type=\"button\" class=\"btn\" onclick=\"changePage()\">Next</button>";
                    echo "</div>";
                    echo "</div>";

                    //Start of second part of form
                    echo "<div class=\"tab2\" style=\"display: none;\">";
                    echo "<div class=\"form-control\">";
                    echo "<h3>Select some initial interests</h3>";
                    // Get some intrests from the database for the user to choose from.
                    $dbinterest=readInterests();
                    $categories=readCategories();
                    
                    for ($i = 0; $i < count($categories); $i++) {
                        $row = $categories[$i];
    
                        echo "<div class=\"interest-group\">";
                        // Create a button with group name as text
                        echo "<button id=". $i. " type=\"button\" class=\"btn\" onclick=\"toggleInterest(this.id)\">". $row[1]. "</button>";
                        // Interests are inside div that is not displayed until group name is clicked
                        echo "<div class=". $i. " style=\"display: none;\">";
                        echo "<ul class=\"cbox-custom\">";
                        // Loop the interest guery for interest names and echo them where id's match
                        for ($j = 0; $j < count($dbinterest); $j++) {
                            $row2 = $dbinterest[$j];
                            if ($row[0] == $row2[2]) {
                                // Render the interest in a checkbox element.
                                echo "<li>";
                                echo "<input type=\"checkbox\" name=\"interest[]\" id=\"". $row2[0]. "\" value=\"". $row2[0]. "\">";
                                echo "<label for=\"". $row2[0]. "\">". $row2[1]. "</label>";
                                echo "</li>";
                            }
                        }
                        echo "</ul>";
                        echo "</div>";
                        echo "</div>";
                    }
                    echo "</div>";
                    echo "<div class=\"action-buttons\">";
                        echo "<button type=\"button\" class=\"btn\" onclick=\"changePage()\">Back</button>";
                        echo "<button type=\"submit\" name=\"submitUserData\" class=\"btn\"> Save </button>";
                    echo "</div>";
                    echo "</div>";
                echo "</form>";
            echo "</div>";
        }
    ?>
    <script type="text/javascript">
        var currentTab=1;

        function changePage() {
            if(currentTab==1){
                var tab=document.getElementsByClassName("tab2");
                tab[0].style.display = "block";
                var tab=document.getElementsByClassName("tab1");
                tab[0].style.display = "none";
                currentTab=2;
            }
            else if(currentTab==2){
                var tab=document.getElementsByClassName("tab1");
                tab[0].style.display = "block";
                var tab=document.getElementsByClassName("tab2");
                tab[0].style.display = "none";
                currentTab=1;
            }
        }
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
