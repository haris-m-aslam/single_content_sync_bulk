<?php

namespace Drupal\single_content_sync_bulk\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Link;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Url;
use Drupal\single_content_sync\ContentExporterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * create custom action
 *
 * @Action(
 *   id = "node_export_action",
 *   label = @Translation("Export Content"),
 *   type = "node"
 * )
 */
class ExportAction extends ActionBase {
  /**
   * {@inheritdoc}
   */
  public function execute($node = NULL) {
    if ($node) {
      $output = \Drupal::service('single_content_sync.exporter')->doExportToYml($node, True);
      $export_in_yaml = $output;
      $content = Yaml::decode($export_in_yaml);
      
      $directory = 'public://export';
      \Drupal::service('file_system')->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);

      $name = implode('-', [
        $content['entity_type'],
        $content['bundle'],
        $content['uuid'],
      ]);
      $file = \Drupal::service('file_system')->saveData($output, "{$directory}/{$name}.yml", FileSystemInterface::EXISTS_REPLACE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    foreach ($entities as $entity) {
      $this->execute($entity);
    }

    \Drupal::messenger()->addStatus('Content exported');

    $host = \Drupal::request()->getSchemeAndHttpHost();
    
    $url = 'http://localhost/drupal_cont_sync1/test-zip-file';
    // $url = $host. '/test-zip-file';
    $response = new RedirectResponse($url);
    $listener = function ($event) use ($response) {
      $event->setResponse($response);
    };
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->addListener(KernelEvents::RESPONSE, $listener);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
      /** @var \Drupal\node\NodeInterface $object */
      // TODO: write here your permissions
      $result = $object->access('create', $account, TRUE);
      return $return_as_object ? $result : $result->isAllowed();
  }

}