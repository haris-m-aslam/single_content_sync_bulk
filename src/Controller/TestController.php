<?php

namespace Drupal\single_content_sync_bulk\Controller;

use Drupal\Core\Controller\ControllerBase;
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

class TestController extends ControllerBase {

  /**
   * The content exporter service.
   *
   * @var \Drupal\single_content_sync\ContentExporterInterface
   */
  protected $contentExporter;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ContentExportForm constructor.
   *
   * @param \Drupal\single_content_sync\ContentExporterInterface $content_exporter
   *   The content exporter service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ContentExporterInterface $content_exporter, FileSystemInterface $file_system, EntityTypeManagerInterface $entity_type_manager) {
    $this->contentExporter = $content_exporter;
    $this->fileSystem = $file_system;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('single_content_sync.exporter'),
      $container->get('file_system'),
      $container->get('entity_type.manager')
    );
  }

  public function importContent(){
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
    
    
    $this->messenger()->addStatus('Content imported');
    return [];
  }

  public function zipContent(){
    $zip = new \ZipArchive;
    $destination = \Drupal::service('file_system')->realpath('public://export');
    $zip->open($destination . "/contentexport.zip", constant("ZipArchive::CREATE"));

    $files = \Drupal::service('file_system')->scanDirectory('public://export', '/.yml/');
    if(!empty($files)){
      foreach($files as $file){
        $file_path = \Drupal::service('file_system')->realpath($file->uri);
        $file_name = \Drupal::service('file_system')->basename($file->uri);
        $zip->addFile($file_path, $file_name);
      }
    }
    $zip->close();

    $fileName = 'contentexport.zip';
    $uri = 'public://export/' . $fileName;

    $headers = array(
      'Content-Type' => 'application/zip',
      'Content-Disposition' => 'attachment;filename="'.$fileName.'"'
    );

    return new BinaryFileResponse($uri, 200, $headers, true);
  }

}