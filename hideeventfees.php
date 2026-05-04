<?php

require_once 'hideeventfees.civix.php';
// phpcs:disable
use CRM_Hideeventfees_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function hideeventfees_civicrm_config(&$config): void {
  _hideeventfees_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function hideeventfees_civicrm_install(): void {
  _hideeventfees_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function hideeventfees_civicrm_enable(): void {
  _hideeventfees_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_searchColumns().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_searchColumns
 */
function hideeventfees_civicrm_searchColumns($objectName, &$headers, &$rows, &$selector) {
  if ($objectName == 'event' && !CRM_Core_Permission::check('civicrm see event fees')) {
    foreach ($headers as $id => $header) {
      if (in_array(($header['sort'] ?? ''), ['participant_fee_amount', 'participant_fee_level'])) {
        unset($headers[$id]);
      }
    }

  }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 *
 */
function hideeventfees_civicrm_buildForm($formName, &$form) {
  if (in_array($formName, ['CRM_Contact_Form_Search_Advanced', 'CRM_Event_Form_Search'])) {
    if (!CRM_Core_Permission::check('civicrm see event fees')) {
      CRM_Core_Resources::singleton()->addScript(
        "CRM.$(function($) {
          $('.selector td.crm-participant-participant_fee_amount').remove();
          $('.selector td.crm-participant-participant_fee_level').remove();
        });"
      );
    }
  }

  if (in_array($formName, ['CRM_Event_Form_ParticipantView', 'CRM_Event_Form_Participant'])) {
    $isBackendRegistration = $formName === 'CRM_Event_Form_Participant'
      && (($form->getAction() & CRM_Core_Action::ADD) === CRM_Core_Action::ADD);

    if (!CRM_Core_Permission::check('civicrm see event fees') && !$isBackendRegistration) {
      $form->assign('fee_level', []);
      $form->assign('hasPayment', FALSE);
      CRM_Core_Resources::singleton()->addStyle(
        "tr.crm-event-eventfees-form-block-price_set_amount { display: none !important;}"
      );
    }
  }

}

/**
 * Implements hook_civicrm_permission().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function hideeventfees_civicrm_permission(&$permissions) {
  $permissions['civicrm see event fees'] = [
    ts('CiviEvent: see event fees'),
    ts('See event fees.'),
  ];
}
