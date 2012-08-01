<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Doctrine\ODM\MongoDB\DocumentManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;

/**
 * Very simple class to flush the documentManager. Intended for use with the zf2 mvc 
 * onRender event - this way a UOW can be built up by many different parts of the application
 * code and flushed all in one go.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RenderFlushListener implements ListenerAggregateInterface {
    
    protected $documentManager;
    
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    
    public function __construct(DocumentManager $documentManager) {
        $this->documentManager = $documentManager;
    }
    
    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'onRender'));
    }

    /**
     * Detach all our listeners from the event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }     
    
    public function onRender($event){        
        $this->documentManager->flush();
    }
}