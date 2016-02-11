<?php
/**
 * @file abstract_local_analytics.php
 * Abstract class for common functionality.
 */

require_once(dirname(__FILE__) . '/local_analytics_interface.php');

/**
 * Abstract local analytics class.
 */
 abstract class AbstractLocalAnalytics implements local_analytics_interface {

   /**
    * Encode a substring if required.
    *
    * @param string input
    *   The string that might be encoded.
    * @param boolean encode
    *   Whether to encode the URL.
    */
   static private function mightEncode($input, $encode) {
     if (!$encode) {
       return $input;
     }

     return urlencode($input);
   }

   /**
    * Get the Tracking URL for the request.
    *
    * @param int leading_slash
    *   Whether to add a leading slash to the URL.
    * @param int urlencode
    *   Whether to encode URLs.
    * @return string
    *   A URL to use for tracking.
    */
   static public function trackurl($urlencode = FALSE, $leading_slash = FALSE) {
     global $DB, $PAGE, $COURSE;
     $pageinfo = get_context_info_array($PAGE->context->id);
     $trackurl = "'";

     if ($leading_slash) {
       $trackurl .= "/";
     }

     // Adds course category name.
     if (isset($pageinfo[1]->category)) {
       if ($category = $DB->get_record('course_categories', array (
         'id' => $pageinfo[1]->category
       ))) {
         $cats = explode("/", $category->path);
         foreach (array_filter($cats) as $cat) {
           if ($categorydepth = $DB->get_record("course_categories", array (
             "id" => $cat
           ))) {
             ;
             $trackurl .= self::mightEncode($categorydepth->name, $urlencode) . '/';
           }
         }
       }
     }

     // Adds course full name.
     if (isset($pageinfo[1]->fullname)) {
       if (isset($pageinfo[2]->name)) {
         $trackurl .= self::mightEncode($pageinfo[1]->fullname, $urlencode) . '/';
       } else if ($PAGE->user_is_editing()) {
         $trackurl .= self::mightEncode($pageinfo[1]->fullname, $urlencode) . '/' . get_string('edit', 'local_analytics');
       } else {
         $trackurl .= self::mightEncode($pageinfo[1]->fullname, $urlencode) . '/' . get_string('view', 'local_analytics');
       }
     }

     // Adds activity name.
     if (isset($pageinfo[2]->name)) {
       $trackurl .= self::mightEncode($pageinfo[2]->modname, $urlencode) . '/' . self::mightEncode($pageinfo[2]->name, $urlencode);
     }

     $trackurl .= "'";
     return $trackurl;
   }

 }