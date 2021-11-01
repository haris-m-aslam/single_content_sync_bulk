<?php

namespace Drupal\single_content_sync_bulk\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Class ContentImportForm.
 */
class ContentImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_import_form_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['zip_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Zip File'),
      '#description' => $this->t('upload exported zip file'),
      '#weight' => '0',
      '#upload_validators' => [
        'file_validate_extensions' => ['zip'],
      ],
      '#required' => true
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('zip_file')[0]) {
      $file = File::load($form_state->getValue('zip_file')[0]);

      $file_real_path = \Drupal::service('file_system')->realpath($file->getFileUri());
      $file_contents = file_get_contents($file_real_path);
      $directory = 'public://export/';
      \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
      \Drupal::service('file_system')->saveData($file_contents, $directory . 'contentexport.zip', FileSystemInterface::EXISTS_REPLACE);


      $zip_file = 'public://export/contentexport.zip';
      $f‌​ilepath2 = 'public://export';
      $destination2 = \Drupal::service('file_system')->realpath($f‌​ilepath2);
      $zip = \Drupal::service('plugin.manager.archiver')->getInstance(['filepath' => $zip_file]);
      $zip->extract($destination2);


      $files = \Drupal::service('file_system')->scanDirectory('public://export', '/.yml/');
      if(!empty($files)){
        foreach($files as $file){
          $file_path = \Drupal::service('file_system')->realpath($file->uri);
          \Drupal::service('single_content_sync.importer')->importFromFile($file_path);
          \Drupal::service('file_system')->unlink($file_path);
        }
      }
      
      
      $this->messenger()->addStatus('Content imported successfully');
    } else {
      $this->messenger()->addMessage('Upload Failed!! Contact administrator!!', 'error');
    }
  }

}