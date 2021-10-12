<?php
defined('_JEXEC') or die;

class plgContentdbtest extends JPlugin{
    public function onContentAfterTitle($context, &$article, &$params, $limitstart){

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Select first name column from table 'testi'.
        $query->select($db->quoteName(array('fname')));
        $query->from($db->quoteName('testi'));

        // Reset the query using the query object.
        $db->setQuery($query);

        $results = $db->loadObjectList();
        //return the results
        return json_encode($results);
    }
}
?>