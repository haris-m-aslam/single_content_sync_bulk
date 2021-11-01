<?php
      
namespace Drupal\single_content_sync_bulk\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CustomRedirectionSubscriber implements EventSubscriberInterface {

  public function alterRedirection(FilterResponseEvent $event) {
    $response = $event->getResponse();
    // if ($response instanceOf RedirectResponse && $response->getTargetUrl() == 'http://localhost/drupal_cont_sync1/admin/content/import') {
    //   $response->setTargetUrl('http://localhost/drupal_cont_sync1/admin/content');
    // } 
  }

  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['alterRedirection'];
    return $events;
  }
}