<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Very simple class to flush the documentManager. Intended for use with the zf2 mvc 
 * onRender event - this way a UOW can be built up by many different parts of the application
 * code and flushed all in one go.
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RenderFlushListener {
    
    protected $documentManager;
    
    public function __construct(DocumentManager $documentManager) {
        $this->documentManager = $documentManager;
    }    
    
    public function onRender(){
        $this->documentManager->flush();
    }
}