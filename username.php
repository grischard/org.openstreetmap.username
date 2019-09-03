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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function username_civicrm_xmlMenu(&$files) {
  _username_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function username_civicrm_postInstall() {
  _username_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function username_civicrm_uninstall() {
  _username_civix_civicrm_uninstall();
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
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function username_civicrm_disable() {
  _username_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function username_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _username_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function username_civicrm_managed(&$entities) {
  _username_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function username_civicrm_caseTypes(&$caseTypes) {
  _username_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function username_civicrm_angularModules(&$angularModules) {
  _username_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function username_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _username_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function username_civicrm_entityTypes(&$entityTypes) {
  _username_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_validateForm().
 * Checks if the osm username is valid.
 * The field label *must* be: OSM username
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_validateForm/
 */
function username_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  $osmfield = civicrm_api3('CustomField', 'getsingle', array('label' => 'OSM username'));
  switch ($formName) {
    case 'CRM_Contribute_Form_Contribution_Main':
      // Sometimes, field id is like custom_1
      $fieldName = 'custom_' . $osmfield['id'];
      break;
      
    case 'CRM_Contact_Form_Contact':
    case 'CRM_Contact_Form_Inline_CustomData':
       // And the rest of the time, field id is like custom_1_329. Go figure!
       $customRecId = $osm = CRM_Utils_Array::value( "customRecId", $fields, FALSE );
       $osmfieldid = 'custom_'.$osmfield['id'].'_'.$customRecId;
      
    default:
      throw new InvalidArgumentException(sprintf("Could not find custom field with 'OSM username' as its label"));
  }
       
  $osm = CRM_Utils_Array::value( $osmfieldid, $fields, FALSE );
      
  if (!_username_validate_osm_username($osm)) {
    $errors[$fieldname] = 'Invalid / whatever';
  }
}

function _username_validate_osm_username($username) {
  if (strlen((string)$osm) > 0) {
    $url = 'https://api.openstreetmap.org/api/0.6/changesets?time=9999-01-01&display_name='.rawurlencode($osm);
    $handle = curl_init($url);
    curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
    /* Get the HTML or whatever is linked in $url. */
    $response = curl_exec($handle);
    /* Check for 404 (file not found). */
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    // This doesn't handle 50x, 30x, 403.
    //
    // If you want to pass back a reason why this lookup failed, consider more descriptive return values
    // than true/false, or throwing and catching an exception?
    if ($httpCode == 404) {
       return false;
    }
    curl_close($handle);
    return true;
  }  
}
