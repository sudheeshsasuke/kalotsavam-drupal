<?php

namespace Drupal\kalolsavam\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\kalolsavam\KalolsavamStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to add a database entry, with all the interesting fields.
 */
class KalolsavamAddForm implements FormInterface, ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * We'll need this service in order to check if the user is logged in.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   *
   * We'll use the ContainerInjectionInterface pattern here to inject the
   * current user and also get the string_translation service.
   */
  public static function create(ContainerInterface $container) {
    $form = new static(
      $container->get('current_user')
    );
    // The StringTranslationTrait trait manages the string translation service
    // for us. We can inject the service here.
    $form->setStringTranslation($container->get('string_translation'));
    return $form;
  }

  /**
   * Construct the new form object.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dbtng_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];
    
    $node_array = array(); 
    $query = \Drupal::entityQuery('node'); 
    $query->condition('status', 1); 
    $query->condition('type', 'competition'); 
    $entity_ids = $query->execute(); 
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($entity_ids); 
    $competitions = array(); 
    foreach ($nodes as $node) { 
      $title[$node->getTitle()] = $node->getTitle(); 
    }
    
    $form['message'] = [
      '#markup' => $this->t('Add marks to the participant.'),
    ];

    $form['add'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Add participant mark entry'),
    ];
    // $form['add']['competition'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Competition'),
    //   '#size' => 15,
    // ];

    $form['add']['competition'] = [
      '#type' => 'select',
      '#title' => $this->t('competition'), 
      '#options' => $title, 
      '#empty_option' => $this->t('-select-'), 
      '#required' => TRUE,
    ];

    $form['add']['chest_no'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chest_no'),
      '#size' => 15,
    ];
    $form['add']['mark'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mark'),
      '#size' => 5,
      '#description' => $this->t("Values greater than 100 will cause an exception."),
    ];
    $form['add']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Verify that the user is logged-in.
    if ($this->currentUser->isAnonymous()) {
      $form_state->setError($form['add'], $this->t('You must be logged in to add values to the database.'));
    }
    // Confirm that chest no is numeric.
    if (!intval($form_state->getValue('chest_no'))) {
      $form_state->setErrorByName('chest_no', $this->t('Age needs to be a number'));
    }
    // Confirm that mark is numeric.
    if (!intval($form_state->getValue('mark'))) {
      $form_state->setErrorByName('mark', $this->t('Age needs to be a number'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Gather the current user so the new record has ownership.
    $account = $this->currentUser;
    // Save the submitted entry.
    $entry = [
      'competition' => $form_state->getValue('competition'),
      'chest_no' => $form_state->getValue('chest_no'),
      'mark' => $form_state->getValue('mark'),
      'uid' => $account->id(),
    ];
    $return = KalolsavamStorage::insert($entry);
    if ($return) {
      drupal_set_message($this->t('Created entry @entry', ['@entry' => print_r($entry, TRUE)]));
    }
  }

}
