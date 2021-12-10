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

// Check if form was submitted.
if(isset($_POST['submitUserData'])){ 
    if (empty($_POST['interest']) || count($_POST['interest']) < 3) {
        $message = "Please choose at least three (3) interests!";
        echo "<script>alert('$message');</script>";
    }
    else {
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
        if ($exist != null) {
            echo "<section class=\"user-profile\">";
                $userData = $exist;
                $userData = $userData[0];
                echo "<div class=\"profile-container flex-row\">";
                    echo "<div class=\"side-bar-left flex-column\">";
                        echo "<div class=\"profile-pic\">";
                            echo "<img src=\"images/profile_pictures/". $userData[4]. "\" alt=\"User profile picture\"/>";
                        echo "</div>";
                        echo "<div><h3>". $userData[1]. " ". $userData[2]. "</h3></div>";
                    echo "</div>";
                    echo "<div class=\"content-area\">";
                        echo "<h3>Your introduction</h3>";
                        echo "<hr>";
                        echo "<p>". $userData[5]."</p>";
                    echo "</div>";
                echo "</div>";
            echo "</section>";
        }
        else {
            echo "<section class=\"modal-box\">";
            echo "<div class=\"modal-content\">";
            echo "<form name=\"acc-details\" action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                echo "<div class=\"modal-instructions\">";
                    echo "<h2>Welcome to Connecting Colleagues!</h2>";
                    echo "<h3>Let's finalize your account details.</h3>";
                    echo "<h3 id=\"tab-number \">Part 1 of 2</h3>";
                    echo "<hr>";
                echo "</div>";

                // The start of the first page of the form.
                echo "<div class=\"tab1\">";
                    echo "<div class=\"form-control\">";
                        echo "<label for=\"fname\">Your first name</label>";
                        echo "<input type=\"text\" name=\"fname\" class=\"text-input medium-input\" required>";
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<label for=\"lname\">Your last name</label>";
                        echo "<input type=\"text\" name=\"lname\" class=\"text-input medium-input\" required>";
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<label for=\"introduction\">Write a short introduction of yourself</label>";
                        echo "<textarea name=\"introduction\" class=\"text-input\" onkeyup=\"countChar(this);\" required></textarea>";
                        echo "<p id=\"charNum\">Minimum 75 characters needed</p>";
                    echo "</div>";

                    echo "<div class=\"form-control\">";
                        echo "<label for=\"propic\">Upload a profile picture</label>";
                        echo "<input type=\"file\" name=\"propic\" class=\"photo-input\">";
                    echo "</div>";
                    
                    echo "<div class=\"actions-buttons\">";
                        echo "<hr>";
                        echo "<div class=\"flex-row\">";
                            echo "<input type=\"button\" id=\"tab1-save\" class=\"btn\" onclick=\"changePage()\" value=\"Next\">";
                        echo "</div>";
                    echo "</div>";
                echo "</div>";

                // The start of the second page of the form.
                echo "<div class=\"tab2\" style=\"display: none;\">";

                    echo "<div class=\"form-control\">";
                        echo "<div class=\"interests-instructions\">";
                            echo "<p>Select at least three (3) interests.</p>";
                        echo "</div>";
                    
                        // Get some intrests from the database for the user to choose from.
                        $dbinterest=readInterests();
                        $categories=readCategories();
                    
                        for ($i = 0; $i < count($categories); $i++) {
                            $row = $categories[$i];
    
                            echo "<div class=\"interest-group\">";
                            // Create a button with group name as text
                            echo "<button id=". $i." type=\"button\" class=\"btn\" onclick=\"toggleInterest(this.id)\">". $row[1]. "</button>";
                            
                            // Interests are inside div that is not displayed until group name is clicked
                            echo "<div class=". $i." \" flex fxdir-default\" style=\"display: none;\">";

                            // Loop the interest guery for interest names and echo them where id's match
                            for ($j = 0; $j < count($dbinterest); $j++) {
                                $row2 = $dbinterest[$j];

                                if ($row[0] == $row2[2]) {
                                    // Render the interest in a checkbox element.
                                    echo "<div class=\"cbox-custom\">";
                                    echo "<input type=\"checkbox\" name=\"interest[]\" id=\"". $row2[0]. "\" value=\"". $row2[0]. "\">";
                                    echo "<label for=\"". $row2[0]. "\">". $row2[1]. "</label>";
                                    echo "</div>";
                                }
                            }   
                            echo "</div>";
                            echo "</div>";
                        }
                    echo "</div>";

                    echo "<div class=\"action-buttons\">";
                        echo "<hr>";
                        echo "<div class=\"flex-row\">";
                            echo "<input type=\"button\" class=\"btn\" onclick=\"changePage()\" value=\"Back\">";
                            echo "<input type=\"submit\" name=\"submitUserData\" id=\"save\" class=\"btn\" onclick=\"toggle()\" value=\"Save\">";
                        echo "</div>";
                    echo "</div>";

                echo "</div>";
            echo "</form>";
            echo "</div>";
            echo "</section>";
        }
    ?>
    
    <script>
        var currentTab = 1;

        // Deals with the page change in the account finalization and the input field checks.
        function changePage() {
            if (currentTab == 1) {
                var f = document.forms["acc-details"]["fname"].value;
                var l = document.forms["acc-details"]["lname"].value;
                var i = document.forms["acc-details"]["introduction"].value;
                var iLen = i.length;

                // Check that the input field for the first name isn't empty.
                if (f == null || f == "") {
                    alert("The first name must be filled out!");
                }
                // Check that the input field for the last name isn't empty.
                else if (l == null || l == "") {
                    alert("The last name must be filled out!");
                }
                // Check that the input field for the introduction isn't empty.
                else if (i == null || i == "") {
                    alert("You must write yourself an introduction!");
                }
                else if (iLen < 75) {
                    alert("Your introduction must be at least 75 characters long!");
                }
                // Change the tab 1 to tab 2.
                else {
                    var tab = document.getElementsByClassName("tab2");
                    tab[0].style.display = "block";
                    var tab = document.getElementsByClassName("tab1");
                    tab[0].style.display = "none";
                    currentTab = 2;
                    var tabPart = document.getElementById("tab-number");
                    tabPart.innerHTML = "Part 2 of 2";
                }
            }
            // Change the tab 2 to tab 1.
            else if (currentTab == 2) {
                var tab = document.getElementsByClassName("tab1");
                tab[0].style.display = "block";
                var tab = document.getElementsByClassName("tab2");
                tab[0].style.display = "none";
                currentTab = 1;
                var tabPart = document.getElementById("tab-number");
                tabPart.innerHTML = "Part 1 of 2";
            }
        }

        // Prints out how many characters are needed in the introduction.
        function countChar(val) {
            var len = val.value.length;
            var numChar = document.getElementById("charNum");
            if (len >= 75) {
                numChar.innerHTML = "✓ The character count has been reached!";
            }
            else {
                numChar.innerHTML = "Minimum " + (75 - len).toString() + " characters needed";
            }
        }

        // Toggles visibility of the interests in each interest catagory.
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