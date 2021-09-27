<?php

// no direct access
defined('_JEXEC') or die;

class plgContentHelloworld extends JPlugin
{
    public function onContentAfterTitle($context, &$article, &$params, $limitstart)
        {
                return "<p>Nokia Connecting People Test Plugin</p>";
        }
}
?>