<?php

require_once 'username.civix.php';
use CRM_Username_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function username_civicrm_config(&$config) {
  _username_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function username_civicrm_install() {
  _username_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function username_civicrm_enable() {
  _username_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_validateForm().
 * Checks if the osm username is valid.
 * The field label *must* be: OSM username
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_validateForm/
 */
function username_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
    if ( $formName == 'CRM_Contact_Form_Contact' or
         $formName == 'CRM_Contact_Form_Inline_CustomData' or
         $formName == 'CRM_Contribute_Form_Contribution_Main'
        ) {
       $osmfield = civicrm_api3('CustomField', 'getsingle', array('label' => 'OSM username'));
       if(! $osmfield){
           throw new InvalidArgumentException(sprintf("Could not find custom field with 'OSM username' as its label"));
       }
       
       if ( $formName == 'CRM_Contribute_Form_Contribution_Main') {
               # Sometimes, field id is like custom_1
               $osmfieldid = 'custom_'.$osmfield['id'];
           } else{
               $customRecId = $osm = CRM_Utils_Array::value( "customRecId", $fields, FALSE );
               # And the rest of the time, field id is like custom_1_329. Go figure!
               $osmfieldid = 'custom_'.$osmfield['id'].'_'.$customRecId;
           }
       
       $osm = CRM_Utils_Array::value( $osmfieldid, $fields, FALSE );
       
       if (strlen((string)$osm) > 0) {
           $url = 'https://api.openstreetmap.org/api/0.6/changesets?time=9999-01-01&display_name='.rawurlencode($osm);
           $handle = curl_init($url);
           curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

           /* Get the HTML or whatever is linked in $url. */
           $response = curl_exec($handle);

           /* Check for 404 (file not found). */
           $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
           if($httpCode == 404) {
               $errors[$osmfieldid] = ts( 'OSM username does not exist. Remember that usernames are case sensitive.' );
           }
           curl_close($handle);
       }
    }
    return;
}
