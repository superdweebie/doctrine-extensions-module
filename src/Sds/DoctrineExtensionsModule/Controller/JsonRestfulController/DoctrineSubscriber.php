<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Controller\JsonRestfulController;

use Doctrine\Common\EventSubscriber;
use Sds\DoctrineExtensions\AccessControl\Events as AccessControlEvents;
use Sds\DoctrineExtensions\AccessControl\EventArgs as AccessControlEventArgs;
use Sds\DoctrineExtensions\Freeze\Events as FreezeEvents;
use Sds\DoctrineExtensions\SoftDelete\Events as SoftDeleteEvents;
use Sds\DoctrineExtensions\State\Events as StateEvents;
use Sds\DoctrineExtensions\Validator\Events as ValidatorEvents;
use Sds\DoctrineExtensions\Validator\EventArgs as ValidatorEventArgs;
use Sds\DoctrineExtensionsModule\Exception;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DoctrineSubscriber implements EventSubscriber
{

    protected $flushExceptions = [];

    public function getSubscribedEvents(){
        return [
            AccessControlEvents::createDenied,
            AccessControlEvents::updateDenied,
            AccessControlEvents::deleteDenied,
            FreezeEvents::freezeDenied,
            FreezeEvents::thawDenied,
            FreezeEvents::frozenUpdateDenied,
            FreezeEvents::frozenDeleteDenied,
            SoftDeleteEvents::restoreDenied,
            SoftDeleteEvents::softDeleteDenied,
            SoftDeleteEvents::softDeletedUpdateDenied,
            StateEvents::transitionDenied,
            ValidatorEvents::invalidCreate,
            ValidatorEvents::invalidUpdate,
        ];
    }

    public function getFlushExceptions() {
        return $this->flushExceptions;
    }

    public function invalidCreate(ValidatorEventArgs $eventArgs){
        $this->validationEvent($eventArgs);
    }

    public function invalidUpdate(ValidatorEventArgs $eventArgs){
        $this->validationEvent($eventArgs);
    }

    public function createDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function updateDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function deleteDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function updateRolesDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function freezeDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function thawDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function frozenUpdateDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function frozenDeleteDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function softDeletedUpdateDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function softDeleteDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function restoreDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    public function transitionDenied(AccessControlEventArgs $eventArgs){
        $this->accessControlEvent($eventArgs);
    }

    protected function validationEvent(ValidatorEventArgs $eventArgs){
        $exception = new Exception\InvalidDocumentException;
        $exception->setValidatorMessages($eventArgs->getMessages());
        $exception->setDocument($eventArgs->getDocument());
        $this->flushExceptions[] = $exception;
    }

    public function accessControlEvent(AccessControlEventArgs $eventArgs){
        $exception = new Exception\AccessControlException;
        $exception->setAction($eventArgs->getAction());
        $exception->setDocument($eventArgs->getDocument());
        $this->flushExceptions[] = $exception;
    }
}
