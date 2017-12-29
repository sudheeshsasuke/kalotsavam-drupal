<?php

namespace Drupal\kalolsavam\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\kalolsavam\KalolsavamStorage;

/**
 * Controller for DBTNG Example.
 */
class KalolsavamController extends ControllerBase {

  /**
   * Render a list of entries in the database.
   */
  public function entryList() {
    $content = [];

    $content['message'] = [
      '#markup' => $this->t('Generate a list of all entries in the database. There is no filter in the query.'),
    ];

    $rows = [];
    $headers = [t('Id'), t('uid'), t('Competition'), t('Chest_No'), t('Mark')];

    foreach ($entries = KalolsavamStorage::load() as $entry) {
      // Sanitize each entry.
      $rows[] = array_map('Drupal\Component\Utility\SafeMarkup::checkPlain', (array) $entry);
    }
    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => t('No entries available.'),
    ];
    // Don't cache this page.
    $content['#cache']['max-age'] = 0;

    return $content;
  }

  /**
   * Render a filtered list of entries in the database.
   */
  public function entryAdvancedList() {
    $content = [];

    $content['message'] = [
      '#markup' => $this->t('All marks entered by the judges') . ' ' .
      $this->t(''),
    ];

    $headers = [
      t('Id'),
      t('Created by'),
      t('Competition'),
      t('Chest_No'),
      t('Mark'),
    ];

    $node_array = array(); 
    $query = \Drupal::entityQuery('node'); 
    $query->condition('status', 1); 
    $query->condition('type', 'competition'); 
    $entity_ids = $query->execute(); 
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($entity_ids); 
    $competitions = array(); 
    foreach ($nodes as $node) { 
      $title[] = $node->getTitle(); 
    }

    $rows = [];
    foreach ($entries = KalolsavamStorage::advancedLoad() as $entry) {
      // Sanitize each entry.
      $rows[] = array_map('Drupal\Component\Utility\SafeMarkup::checkPlain', $entry);
    }
    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#attributes' => ['id' => 'dbtng-example-advanced-list'],
      '#empty' => t('No entries available.'),
    ];
    // Don't cache this page.
    $content['#cache']['max-age'] = 0;
    return $content;
  }
  
  public function aggregateMarkList() {
    $content = [];

    $content['message'] = [
      '#markup' => $this->t('A more complex list of entries in the database.') . ' ' .
      $this->t('Only the entries with name = "John" and age older than 18 years are shown, the username of the person who created the entry is also shown.'),
    ];

    $headers = [
      t('Competition'),
      t('Mark'),
    ];

    $node_array = array(); 
    $query = \Drupal::entityQuery('node'); 
    $query->condition('status', 1); 
    $query->condition('type', 'competition'); 
    $entity_ids = $query->execute(); 
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($entity_ids); 
    $competitions = array(); 
    foreach ($nodes as $node) { 
      $title[] = $node->getTitle(); 
    }

    $rows = [];
    foreach ($entries = KalolsavamStorage::aggregateMarkLoad() as $entry) {
      // Sanitize each entry.
      $rows[] = array_map('Drupal\Component\Utility\SafeMarkup::checkPlain', $entry);
    }
    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#attributes' => ['id' => 'dbtng-example-advanced-list'],
      '#empty' => t('No entries available.'),
    ];
    // Don't cache this page.
    $content['#cache']['max-age'] = 0;
    return $content;
  }

}
