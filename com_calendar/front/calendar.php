<?php

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
$document = Factory::getDocument();
$options = array("version" => "auto");
$document->addStyleSheet(JURI::root(true) . '/components/com_calendar/style.css');
$document->addScript('components/com_calendar/script.js');


$user = JFactory::getUser();
$db = JFactory::getDbo();
$query = $db->getQuery(true);

if (isset($_POST['SubmitButton'])) {
    $label = $_POST['Label'];
    $content = $_POST['Content'];
    $date = $_POST['Date'];
    $start_time = $_POST['Start_time'];
    $end_time = $_POST['End_time'];
    $query->clear();
    $query = $db->getQuery(true);
    $columns = array('User_Id', 'Label', 'Content', 'Date', 'Start_time', 'End_time');
    $values = array($db->quote($user->id), $db->quote($label), $db->quote($content), $db->quote($date), $db->quote($start_time), $db->quote($end_time));
    $query
        ->insert($db->quoteName('app_calendar_notes'))
        ->columns($db->quoteName($columns))
        ->values(implode(',', $values));
    $db->setQuery($query);
    $db->execute();
}

if (isset($_POST['RemoveButton'])) {
    $query = $db->getQuery(true);
    $conditions = array($db->quoteName('Note_Id') . ' = ' . $db->quote($_POST['Note_Id']));

    $query
        ->delete($db->quoteName('app_calendar_notes'))
        ->where($conditions);

    $db->setQuery($query);
    $db->execute();
}


$query = $db->getQuery(true);
$query->select($db->quoteName(array('Note_Id', 'Label', 'Content', 'Date', 'Start_time', 'End_time')));
$query->from($db->quoteName('app_calendar_notes'));
$query->where($db->quoteName('User_Id') . ' = '. $db->quote($user->id));
$db->setQuery($query);
$results=$db->loadRowList();
$resultsJSON = json_encode($results);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | Connecting Colleagues</title>
</head>
<body>

    <div class="container">
        <!-- Calendar area -->
        <section class="calendar-area">

            <section class="calendar-container">

                <container class="date-bar">

                    <section class="previous-month btn" onclick="prevmonth()">
                        Previous month
                    </section>

                    <section class="next-month btn" onclick="nextmonth()">
                        Next month
                    </section>

                    <section class="month-shown">
                        <h2 id="month-shown">Month</h2>
                    </section>

                </container>

                <section class="calendar" id="calendar"></section>

            </section>

            <section class="side-bar">
                <h1 id="side"></h1>
            </section>

        </section>
    </div>

    <script>
        let db = <?php echo $resultsJSON; ?>;
        
        let createNewbutton = document.createElement("BUTTON");
        createNewbutton.onclick = function() {createnew()};
        createNewbutton.innerHTML = "Add note";
        document.getElementById("side").outerHTML = "<div id=\"side\"></div>";
        document.getElementById("side").appendChild(createNewbutton);
        let logMatchingitems = [];
        
        function calendarclickevent(day, month, year) {
            logMatchingitems = [];
            for (let b of db) {
                if (b.indexOf(fixDateformat(day, month, year)) != -1) {
                    logMatchingitems.push(b);
                }
            }
            
            if (logMatchingitems.length != 0) {
                document.getElementById("side").outerHTML = "<div id=\"side\"></div>";
                document.getElementById("side").appendChild(createNewbutton);
                let removeButton = document.createElement("BUTTON");
                removeButton.onclick = function() {removeNotes()};
                removeButton.innerHTML = "Remove note";
                document.getElementById("side").appendChild(removeButton);
                let notesList = document.createElement("DL");
                for (let c of logMatchingitems) {
                    let noteLabel = document.createElement("DT");
                    let timeframe = document.createElement("DD");
                    let noteDescription = document.createElement("DD");
                    noteLabel.innerHTML = c[1];
                    timeframe.innerHTML = c[4] + " - " + c[5];
                    noteDescription.innerHTML = c[2];
                    notesList.appendChild(noteLabel);
                    notesList.appendChild(timeframe);
                    notesList.appendChild(noteDescription);
                }
                document.getElementById("side").appendChild(notesList);
            }
            else {
                document.getElementById("side").outerHTML = "<div id=\"side\"></div>";
                document.getElementById("side").appendChild(createNewbutton);
            }
        }

        function createnew() {
            document.getElementById("side").outerHTML = "<form id=\"side\" action=\"\" method=\"post\"><label>Label</label><br><input name=\"Label\" maxlength=25 required=\"required\"><br><label>Start time - End time</label><br><input type=\"time\" name=\"Start_time\"><input type=\"time\" name=\"End_time\"></input><br><label>Date</label><br><input type=\"date\" name=\"Date\" required=\"required\"><br><label>Notes</label><br><textarea name=\"Content\" maxlength=254></textarea><br><input type=\"submit\" name=\"SubmitButton\" value=\"Create\"></form>";
        }

        function removeNotes() {
            document.getElementById("side").outerHTML = "<form id=\"side\" action=\"\" method=\"post\"></form>";
            for (let d of logMatchingitems) {
                let radioButton = document.createElement("INPUT");
                radioButton.type = "radio";
                radioButton.name = "Note_Id";
                radioButton.value = d[0];
                let newLabel = document.createElement("LABEL");
                newLabel.innerHTML = d[1];
                document.getElementById("side").appendChild(radioButton);
                document.getElementById("side").appendChild(newLabel);
                document.getElementById("side").appendChild(document.createElement("BR"));
            }
            let submitButton = document.createElement("INPUT");
            submitButton.type = "submit";
            submitButton.name = "RemoveButton";
            submitButton.value = "Remove";
            document.getElementById("side").appendChild(submitButton);
        }

    </script>

    <iframe onload="drawcalgrid()">

</body>
</html>