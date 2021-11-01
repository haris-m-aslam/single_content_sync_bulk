<?php

namespace Drupal\single_content_sync_bulk\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // if ($route = $collection->get('single_content_sync.import')) {
    //   $route->setDefaults(array('_form' => '\Drupal\custom_action\Form\ContentImportForm'));
    // }
  }

}