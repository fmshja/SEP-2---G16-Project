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
    if ($_POST['fname'] == '') {
        echo '<script>
            var msg = document.getElementById("modal-alert");
            msg.style.display = "block";
            msg.innerHTML = "The first name must be filled out!";
        </script>';
    }
    else if ($_POST['lname'] == '') {
        echo '<script>
            var msg = document.getElementById("modal-alert");
            msg.style.display = "block";
            msg.innerHTML = "The last name must be filled out!";
        </script>';
    }
    else if ($_POST['introduction'] == '') {
        echo '<script>
            var msg = document.getElementById("modal-alert");
            msg.style.display = "block";
            msg.innerHTML = "You must write yourself an introduction!";
        </script>';
    }
    else if (empty($_POST['interest']) || count($_POST['interest']) < 3) {
        echo '<script>
            var msg = document.getElementById("modal-alert");
            msg.style.display = "block";
            msg.innerHTML = "Please choose at least three (3) interests!";
        </script>';
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
    <section id="myModal" class="modal-box">
        <div class="alert-message modal-alert" style="display: none;">
            <i class="fas fa-exclamation-circle"></i>
            This is an alert!
        </div>
        <div class="modal-content">
        <form name="acc-details" action="" method="post" onsubmit="return validateForm()" enctype="multipart/form-data">
            <div class="modal-instructions">
                <h2>Welcome to Connecting Colleagues!</h2>
                <h3>Let's finalize your account details.</h3>
                <h3 id="tab-number">Part 1 of 2</h3>
                <hr>
            </div>

            <!-- The start of the first page of the form. -->
            <div class="tab1">
                <div class="form-control">
                    <label for="fname">Your first name</label>
                    <input type="text" name="fname" class="text-input medium-input" required>
                </div>

                <div class="form-control">
                    <label for="lname">Your last name</label>
                    <input type="text" name="lname" class="text-input medium-input" required>
                </div>

                <div class="form-control">
                    <label for="introduction">Write a short introduction of yourself</label>
                    <textarea name="introduction" class="text-input" required></textarea>
                </div>

                <div class="form-control">
                    <label for="pro-pic" class="custom-upload btn">
                        <input type="file" name="pro-pic" class="file-input">
                        <i class="fas fa-upload"></i> Upload a profile picture
                    </label>
                </div>
                        
                <hr>
                <div class="action-buttons flex">
                    <input type="button" id="tab1-save" class="btn" onclick="changePage()" value="Next">
                </div>
            </div>

            <!-- The start of the second page of the form. -->
            <div class="tab2" style="display: none;">

                <div class="form-control">
                    <div class="interests-instructions">
                        <p>Select at least three (3) interests.</p>
                    </div>

                    <?php
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

                                    // Loop the interest guery for interest names and echo them where id's match
                                    for ($j = 0; $j < count($dbinterest); $j++) {
                                        $row2 = $dbinterest[$j];

                                        echo "<ul class=\"cbox-custom\">";
                                        if ($row[0] == $row2[2]) {
                                            // Render the interest in a checkbox element.
                                            echo "<li>";
                                            echo "<input type=\"checkbox\" name=\"interest[]\" id=\"". $row2[0]. "\" value=\"". $row2[0]. "\">";
                                            echo "<label for=\"". $row2[0]. "\">". $row2[1]. "</label>";
                                            echo "</li>";
                                        }
                                        echo "</ul>";
                                    }
                                        
                                echo "</div>";
                            echo "</div>";
                        }
                    ?>
                </div>

                <hr>
                <div class="action-buttons flex">
                    <input type="button" class="btn" onclick="changePage()" value="Back">
                    <input type="submit" name="submitUserData" id="save" class="btn" value="Save">
                </div>

            </div>
        </form>
        </div>
    </section>
    
    
    <section class="user-profile">
        <div class="alert-message profile-alert" style="display: none;">
            <i class="fas fa-exclamation-circle"></i>
            This is an alert!
        </div>
        <!--
        <div class="profile-container flex-row">
            <div class="side-bar-left flex-column">
                <div class="profile-pic">
                    Profile picture rendered here
                    <img src="default-pro-pic.jpg" alt="User profile picture"/>
                </div>
                <div>Firstname Lastname</div>
            </div>
            <div class="content-area">
                <div>Showcase area</div>
                <div>Introduction</div>
            </div>
        </div>
        -->

        <?php
            // USER PROFILE
            if ($exist != null) {
                $userData = $exist;
                $userData = $userData[0];
                echo "<div class=\"profile-container flex-row\">";
                    echo "<div class=\"side-bar-left flex-column\">";
                        echo "<div class=\"profile-pic\">";
                            // echo "<img src=\"default-pro-pic.jpg\" alt=\"User profile picture\"/>";
                            echo "<img src=\"images/profile_pictures/". $userData[4]. "\" alt=\"User profile picture\"/>";
                        echo "</div>";
                        echo "<div>". $userData[1]. " ". $userData[2]. "</div>";
                    echo "</div>";
                    echo "<div class=\"content-area\">";
                        echo "<div>Showcase area</div>";
                        echo "<div>". $userData[5]."</div>";
                    echo "</div>";
                echo "</div>";
            }
            else {
                echo '<script>
                    var msg = document.getElementById("profile-alert");
                    msg.style.display = "block";
                    msg.innerHTML = "There was an error in reading your information.<br>Please make sure you have logged in!";
                </script>';
            }
        ?>
    </section>
    


    <?php
        // Ask the user for more data if the account is new.
        if ($exist != null) {
            echo '<script>
                // Get the modal box.
                var modalObject = document.getElementById("myModal");

                // Get the button that closes the modal box.
                var buttonObject = document.getElementById("save");

                // Open the modal box.
                modalObject.style.display = "block";

                // When the user clicks on the submit button, close the modal box if 
                // the required fields are not empty.
                buttonObject.onclick = function() {
                    var firstName = document.forms["acc-details"]["fname"].value;
                    var lastName = document.forms["acc-details"]["lname"].value;
                    var introText = document.forms["acc-details"]["introduction"].value;

                    if (firstName != "" && lastName != "" && introText != "") {
                        modalObject.style.display = "none";
                    }
                }
            </script>';
        }
    ?>

    <script>
        function validateForm() {
            var f = document.forms["acc-details"]["fname"].value;
            var l = document.forms["acc-details"]["lname"].value;
            var i = document.forms["acc-details"]["introduction"].value;

            if (f == null || f == "" || l == null || l == "" || i == null || i == "") {
                alert("Please fill all required fields.");
                return false;
            }
        }

        var currentTab = 1;

        function changePage() {
            if(currentTab == 1){
                var tab = document.getElementsByClassName("tab2");
                tab[0].style.display = "block";
                var tab = document.getElementsByClassName("tab1");
                tab[0].style.display = "none";
                currentTab = 2;
                var tabPart = document.getElementById("tab-number");
                tabPart.innerHTML = "Part 2 of 2";
            }
            else if(currentTab == 2){
                var tab = document.getElementsByClassName("tab1");
                tab[0].style.display = "block";
                var tab = document.getElementsByClassName("tab2");
                tab[0].style.display = "none";
                currentTab = 1;
                var tabPart = document.getElementById("tab-number");
                tabPart.innerHTML = "Part 1 of 2";
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
