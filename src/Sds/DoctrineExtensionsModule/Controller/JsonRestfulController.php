<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Controller;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Proxy\Proxy;
use Sds\DoctrineExtensions\AccessControl\Events as AccessControlEvents;
use Sds\DoctrineExtensions\AccessControl\EventArgs as AccessControlEventArgs;
use Sds\DoctrineExtensions\Identity\Events as IdentityEvents;
use Sds\DoctrineExtensions\Freeze\Events as FreezeEvents;
use Sds\DoctrineExtensions\Serializer\Serializer;
use Sds\DoctrineExtensions\SoftDelete\Events as SoftDeleteEvents;
use Sds\DoctrineExtensions\State\Events as StateEvents;
use Sds\DoctrineExtensions\Validator\Events as ValidatorEvents;
use Sds\DoctrineExtensions\Validator\EventArgs as ValidatorEventArgs;
use Sds\DoctrineExtensionsModule\Exception;
use Sds\DoctrineExtensionsModule\Options\JsonRestfulController as Options;
use Zend\Http\Header\CacheControl;
use Zend\Http\Header\ContentRange;
use Zend\Http\Header\Location;
use Zend\Http\Header\LastModified;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ModelInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class JsonRestfulController extends AbstractRestfulController implements EventSubscriber
{

    protected $model;

    protected $range;

    protected $options;

    protected $flushExceptions = [];

    public function getSubscribedEvents(){
        return [
            AccessControlEvents::createDenied,
            AccessControlEvents::updateDenied,
            AccessControlEvents::deleteDenied,
            IdentityEvents::updateRolesDenied,
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

    public function onDispatch(MvcEvent $e) {
        $this->model = $this->acceptableViewModelSelector($this->options->getAcceptCriteria());
        $this->options->getDocumentManager()->getEventManager()->addEventSubscriber($this);
        return parent::onDispatch($e);
    }

    public function getFlushExceptions() {
        return $this->flushExceptions;
    }

    public function getOptions() {
        return $this->options;
    }

    public function setOptions(Options $options) {
        $this->options = $options;
    }

    public function __construct(Options $options = null) {
        if (!isset($options)){
            $options = new Options;
        }
        $this->setOptions($options);
    }

    public function getList(){

        $list = $this->doGetList($this->options->getDocumentManager()->getClassMetadata($this->options->getDocumentClass()));

        if (count($list) == 0){
            $this->response->setStatusCode(204);
            return $this->response;
        }

        //apply any select
        if ($select = $this->getSelect()){
            $select = array_fill_keys($select, 0);
            foreach ($list as $key => $item){
                $list[$key] = array_intersect_key($item, $select);
            }
        }
        return $this->model->setVariables($list);
    }

    /**
     * If list array is supplied, it will be filtered and sorted in php.
     * If list is empty, it will be loaded from the db, (filter and sort will be applied by the db).
     *
     * If metadata is not suppled, it will be retrieved using $this->options->getDocumentClass()
     *
     * @param array $list
     * @param \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $metadata
     * @return type
     */
    protected function doGetList(ClassMetadata $metadata, $list = null){

        $criteria = $this->getCriteria($metadata);

        //filter list on criteria
        if (count($criteria) > 0 && $list){
            $list = $this->applyCriteriaToList($list, $criteria);
        }

        if ($list){
            $total = count($list);
        } else {
            //load the total from the db
            $totalQuery = $this->options->getDocumentManager()->createQueryBuilder()
                ->find($metadata->name);
            $total = $this->addCriteriaToQuery($totalQuery, $criteria)
                ->getQuery()
                ->execute()
                ->count();
        }

        if ($total == 0){
            return [];
        }

        $offset = $this->getOffset();
        if ($offset > $total - 1){
            throw new Exception\BadRangeException();
        }
        $sort = $this->getSort();
        $serializer = $this->options->getSerializer();

        if ($list){
            //apply any required sort to the result
            if (count($sort) > 0){
                $this->applySortToList($list, $sort);
            }
            $list = array_slice($list, $offset, $this->getLimit());
            foreach ($list as $item){
                $items[] = $serializer->applySerializeMetadataToArray($item, $metadata->name);
            }
        } else {
            $resultsQuery = $this->options->getDocumentManager()->createQueryBuilder()
                ->find($metadata->name);
            $this->addCriteriaToQuery($resultsQuery, $criteria);
            $resultsQuery
                ->limit($this->getLimit())
                ->skip($offset);
            $resultsCursor = $this->addSortToQuery($resultsQuery, $sort)
                ->eagerCursor(true)
                ->getQuery()
                ->execute();
            foreach ($resultsCursor as $result){
                $items[] = $this->options->getSerializer()->toArray($result, $metadata->name);
            }
        }

        $max = $offset + count($items) - 1;
        $this->response->getHeaders()->addHeader(ContentRange::fromString("Content-Range: $offset-$max/$total"));
        return $items;
    }

    public function get($id){

        $parts = explode('/', $id);
        $id = $parts[0];

        array_shift($parts);
        $deeperResource = $parts;
        $result = $this->doGet($id, $this->options->getDocumentManager()->getClassMetadata($this->options->getDocumentClass()), $deeperResource);

        if ($result instanceof ModelInterface){
            return $result;
        } else {
            if ($select = $this->getSelect()){
                $result = array_intersect_key($result, array_fill_keys($select, 0));
            }
            return $this->model->setVariables($result);
        }
    }

    protected function doGet($document, ClassMetadata $metadata, $deeperResource = []){

        $documentManager = $this->options->getDocumentManager();
        $serializer = $this->options->getSerializer();

        if (count($deeperResource) == 0 ){
            if (is_string($document)){
                $document = $documentManager
                    ->createQueryBuilder()
                    ->find($metadata->name)
                    ->field($metadata->identifier)->equals($document)
                    ->hydrate(false)
                    ->getQuery();
                $document = $document->getSingleResult();

                if ( ! $document){
                    throw new Exception\DocumentNotFoundException();
                }
            }
            return $this->completeGet($document, $metadata);
        }

        $field = $deeperResource[0];
        array_shift($deeperResource);

        //check if field can be returned
        if ( ! $serializer->isSerializableField($field, $metadata)){
            throw new Exception\DocumentNotFoundException();
        }

        $mapping = $metadata->fieldMappings[$field];

        if (isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'many'){
            $this->request->getQuery()->set($metadata->fieldMappings[$field]['mappedBy'], $document);
            return $this->forward()->dispatch(
                $this->getFieldMetadata($metadata, $field)->rest['endpoint'],
                [
                    'id' => implode('/', $deeperResource),
                    'surpressResponse' => true
                ]
            );
        }

        if (is_string($document)){
            $document = $documentManager
                ->createQueryBuilder()
                ->find($metadata->name)
                ->field($metadata->identifier)->equals($document)
                ->field($field)->exists(true)
                ->select($field)
                ->hydrate(false)
                ->getQuery()
                ->getSingleResult();

            if ( ! $document){
                throw new Exception\DocumentNotFoundException();
            }
        }

        switch (true){
            case isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'one':
                if (is_array($document)){
                    $fieldValue = $document[$field];
                } else {
                    $fieldValue = $metadata->reflFields[$field]->getValue($document);
                }

                if ( ! isset($fieldValue)){
                    throw new Exception\DocumentNotFoundException;
                }
                array_unshift($deeperResource, $this->getDocumentId($fieldValue));
                return $this->forward()->dispatch(
                    $documentManager->getClassMetadata($metadata->fieldMappings[$field]['targetDocument'])->rest['endpoint'],
                    [
                        'id' => implode('/', $deeperResource),
                        'surpressResponse' => true
                    ]
                );
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'many':
                $embeddedMetadata = $this->getFieldMetadata($metadata, $field);
                if (count($deeperResource) > 0){
                    $collection = $document[$field];
                    $key = $this->findDocumentKeyInCollection($deeperResource[0], $collection, $embeddedMetadata);
                    array_shift($deeperResource);
                    return $this->doGet(
                        isset($key) ? $collection[$key] : null,
                        $embeddedMetadata,
                        $deeperResource
                    );
                } else {
                    return $this->doGetList(
                        $embeddedMetadata,
                        $document[$field]
                    );
                }
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'one':
                if (count($deeperResource) > 0){
                    return $this->forwardEmbeddedOne('doGet', $document, $field, $metadata, $deeperResource);
                } else {
                    return $this->completeGet($document[$field], $documentManager->getClassMetadata($metadata->fieldMappings[$field]['targetDocument']));
                }
        }

        throw new Exception\DocumentNotFoundException();
    }

    protected function completeGet(array $document, ClassMetadata $metadata){
        $serializer = $this->options->getSerializer();

        if (isset($metadata->rest['cache'])){
            $cacheControl = new CacheControl;
            foreach ($metadata->rest['cache'] as $key => $value){
                $cacheControl->addDirective($key, $value);
            }
            $this->response->getHeaders()->addHeader($cacheControl);
        }
        if (isset($metadata->stamp['updatedOn'])){
            $lastModified = new LastModified;
            $sec = $document[$metadata->stamp['updatedOn']]->sec;
            $lastModified->setDate(new \DateTime("@$sec"));
            $this->response->getHeaders()->addHeader($lastModified);
        }
        return $serializer->applySerializeMetadataToArray($document, $metadata->name);
    }

    public function create($data){

        $documentManager = $this->options->getDocumentManager();
        $document = null;
        $deeperResource = [];

        if ($path = $this->getEvent()->getRouteMatch()->getParam('id')){
            $parts = explode('/', $path);
            $document = $parts[0];
            array_shift($parts);
            $deeperResource = $parts;
        }

        $metadata = $documentManager->getClassMetadata($this->options->getDocumentClass());
        $createdDocument = $this->doCreate($data, $document, $metadata, $deeperResource);

        if ($this->getEvent()->getRouteMatch()->getParam('surpressResponse')){
            return $createdDocument;
        }

        $this->flush();
        $createdMetadata = $documentManager->getClassMetadata(get_class($createdDocument));

        $this->response->setStatusCode(201);
        $this->response->getHeaders()->addHeader(Location::fromString(
            'Location: ' .
            $this->request->getUri()->getPath() .
            '/' .
            $createdMetadata->reflFields[$createdMetadata->identifier]->getValue($createdDocument)
        ));

        return $this->response;
    }

    protected function doCreate(
        array $data,
        $document,
        ClassMetadata $metadata,
        array $deeperResource
    ){

        $documentManager = $this->options->getDocumentManager();

        if (count($deeperResource) == 0){
            $createdDocument = $this->unserialize($data, $document, $metadata, Serializer::UNSERIALIZE_PATCH);
            if ($documentManager->contains($createdDocument)){
                $exception = new Exception\DocumentAlreadyExistsException();
                $exception->setDocument($createdDocument);
                throw $exception;
            }
            if ( ! $metadata->isEmbeddedDocument){
                $documentManager->persist($createdDocument);
            }
            return $createdDocument;
        }

        $field = $deeperResource[0];
        array_shift($deeperResource);

        $mapping = $metadata->fieldMappings[$field];

        if (is_string($document)){
            $document = $documentManager
                ->createQueryBuilder()
                ->find($metadata->name)
                ->field($metadata->identifier)->equals($document)
                ->hydrate(true)
                ->getQuery()
                ->getSingleResult();

            if ( ! $document){
                throw new Exception\DocumentNotFoundException();
            }
        }

        switch (true){
            case isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'many':
                $referencedMetadata = $this->getFieldMetadata($metadata, $field);
                try {
                    $createdDocument = $this->forward()->dispatch(
                        $referencedMetadata->rest['endpoint'],
                        [
                            'id' => implode('/', $deeperResource),
                            'surpressResponse' => true
                        ]
                    );
                } catch (Exception\DocumentAlreadyExistsException $exception){
                    $createdDocument = $exception->getDocument();
                }
                $collection = $metadata->reflFields[$field]->getValue($document);
                if ($collection->contains($createdDocument)){
                    throw new Exception\DocumentAlreadyExistsException();
                }
                if (isset($mapping['mappedBy'])){
                    if ($createdDocument instanceof Proxy){
                        $createdDocument->__load();
                    }
                    $referencedMetadata->reflFields[$mapping['mappedBy']]->setValue($createdDocument, $document);
                }
                return $createdDocument;
            case isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'one':
                if (is_array($document)){
                    $fieldValue = $document[$field];
                } else {
                    $fieldValue = $metadata->reflFields[$field]->getValue($document);
                }

                if ( ! isset($fieldValue)){
                    throw new Exception\DocumentNotFoundException;
                }
                array_unshift($deeperResource, $this->getDocumentId($fieldValue));
                return $this->forward()->dispatch(
                    $documentManager->getClassMetadata($metadata->fieldMappings[$field]['targetDocument'])->rest['endpoint'],
                    [
                        'id' => implode('/', $deeperResource),
                        'surpressResponse' => true
                    ]
                );
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'many':
                $embeddedMetadata = $this->getFieldMetadata($metadata, $field);
                $collection = $metadata->reflFields[$field]->getValue($document);
                if (count($deeperResource) > 0){
                    $key = $this->findDocumentKeyInCollection($deeperResource[0], $collection, $embeddedMetadata);
                    array_shift($deeperResource);
                    return $this->doCreate(
                        $data,
                        $collection[$key],
                        $embeddedMetadata,
                        $deeperResource
                    );
                } else {
                    $createdDocument = $this->doCreate(
                        $data,
                        null,
                        $embeddedMetadata,
                        $deeperResource
                    );
                    $existingKey = $this->findDocumentKeyInCollection(
                        $embeddedMetadata->reflFields[$embeddedMetadata->identifier]->getValue($createdDocument),
                        $collection,
                        $embeddedMetadata
                    );
                    if (isset($existingKey)){
                        throw new Exception\DocumentAlreadyExistsException();
                    }
                    $collection[] = $createdDocument;
                    return $createdDocument;
                }
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'one':
                $embeddedMetadata = $this->getFieldMetadata($metadata, $field);
                return $this->doCreate(
                    $data,
                    $metadata->reflFields[$field]->getValue($document),
                    $embeddedMetadata,
                    $deeperResource
                );
        }
        throw new Exception\DocumentNotFoundException();
    }

    public function update($id, $data){

        $documentManager = $this->options->getDocumentManager();

        $parts = explode('/', $id);
        $document = $parts[0];
        array_shift($parts);
        $deeperResource = $parts;

        $metadata = $documentManager->getClassMetadata($this->options->getDocumentClass());
        $updatedDocument = $this->doUpdate($data, $document, $metadata, $deeperResource);

        if ($this->getEvent()->getRouteMatch()->getParam('surpressResponse')){
            return $updatedDocument;
        }

        $this->flush();

        $updatedMetadata = $documentManager->getClassMetadata(get_class($updatedDocument));
        $newId = $updatedMetadata->reflFields[$updatedMetadata->identifier]->getValue($updatedDocument);
        if ($newId != $id){
            $parts = explode('/', $this->request->getUri()->getPath());
            array_pop($parts);
            $location = implode('/', $parts) . '/' . $newId;
            $this->response->getHeaders()->addHeader(Location::fromString(
                'Location: ' . $location
            ));
        }

        $this->response->setStatusCode(204);
        return $this->response;
    }

    protected function doUpdate(
        array $data,
        $document,
        ClassMetadata $metadata,
        array $deeperResource = []
    ){

        $documentManager = $this->options->getDocumentManager();

        if (count($deeperResource) == 0 ){
            $document = $this->unserialize($data, $document, $metadata, Serializer::UNSERIALIZE_UPDATE);
            if ( ! $documentManager->contains($document) && ! $metadata->isEmbeddedDocument){
                return $this->doCreate($data, $document, $metadata, []);
            }

            return $document;
        }

        $field = $deeperResource[0];
        array_shift($deeperResource);
        if ( ! isset($metadata->fieldMappings[$field])){
            throw new Exception\DocumentNotFoundException();
        }
        $mapping = $metadata->fieldMappings[$field];

        if (isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'many'){
            $referencedMetadata = $this->getFieldMetadata($metadata, $field);
            $referencedDocuments = $this->forward()->dispatch(
                $referencedMetadata->rest['endpoint'],
                [
                    'id' => implode('/', $deeperResource),
                    'surpressResponse' => true
                ]
            );
            $document = $documentManager->getReference($metadata->name, $document);
            if ( ! is_array($referencedDocuments)){
                $referencedDocuments = [$referencedDocuments];
            }
            foreach ($referencedDocuments as $referencedDocument){
                $referencedMetadata->reflFields[$metadata->fieldMappings[$field]['mappedBy']]->setValue(
                    $referencedDocument,
                    $document
                );
            }
            return $document;
        }

        if (is_string($document)){
            $document = $documentManager
                ->createQueryBuilder()
                ->find($metadata->name)
                ->field($metadata->identifier)->equals($document)
                ->getQuery()
                ->getSingleResult();

            if ( ! $document){
                throw new Exception\DocumentNotFoundException();
            }
        }

        switch (true){
            case isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'one':
                $referenceMetadata = $this->getFieldMetadata($metadata, $field);
                if (count($deeperResource) == 0 && isset($data[$referenceMetadata->identifier])) {
                    $referenceId = $data[$referenceMetadata->identifier];
                } else {
                    $referenceId = $this->getDocumentId($metadata->reflFields[$field]->getValue($document));
                }

                array_unshift($deeperResource, $referenceId);
                $updatedDocument = $this->forward()->dispatch(
                    $referenceMetadata->rest['endpoint'],
                    [
                        'id' => implode('/', $deeperResource),
                        'surpressResponse' => true
                    ]
                );

                if (count($deeperResource) == 1){
                    $metadata->reflFields[$field]->setValue($document, $updatedDocument);
                }
                return $document;
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'many':
                $embeddedMetadata = $this->getFieldMetadata($metadata, $field);
                if (count($deeperResource) > 0){
                    $collection = $metadata->reflFields[$field]->getValue($document);
                    $key = $this->findDocumentKeyInCollection($deeperResource[0], $collection, $embeddedMetadata);
                    array_shift($deeperResource);
                    $updatedDocument = $this->doUpdate(
                        $data,
                        isset($key) ? $collection[$key] : null,
                        $embeddedMetadata,
                        $deeperResource
                    );
                    if (isset($key)){
                        $collection[$key] = $updatedDocument;
                    } else {
                        $collection[] = $updatedDocument;
                    }
                    return $document;
                } else {
                    $this->doReplaceList($data, $embeddedMetadata, $metadata->reflFields[$field]->getValue($document));
                    return $document;
                }
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'one':
                $updatedDocument = $this->doUpdate(
                    $data,
                    $metadata->reflFields[$field]->getValue($document),
                    $documentManager->getClassMetadata($metadata->fieldMappings[$field]['targetDocument']),
                    $deeperResource
                );
                $metadata->reflFields[$field]->setValue($document, $updatedDocument);
                return $document;
        }
        throw new Exception\DocumentNotFoundException();
    }

    public function patch($id, $data){

        $documentManager = $this->options->getDocumentManager();

        $parts = explode('/', $id);
        $document = $parts[0];
        array_shift($parts);
        $deeperResource = $parts;

        $metadata = $documentManager->getClassMetadata($this->options->getDocumentClass());
        $patchedDocument = $this->doPatch($data, $document, $metadata, $deeperResource);

        if ($this->getEvent()->getRouteMatch()->getParam('surpressResponse')){
            return $patchedDocument;
        }

        $this->flush();

        $patchedMetadata = $documentManager->getClassMetadata(get_class($patchedDocument));
        $newId = $patchedMetadata->reflFields[$patchedMetadata->identifier]->getValue($patchedDocument);
        if ($newId != $id){
            $parts = explode('/', $this->request->getUri()->getPath());
            array_pop($parts);
            $location = implode('/', $parts) . '/' . $newId;
            $this->response->getHeaders()->addHeader(Location::fromString(
                'Location: ' . $location
            ));
        }

        $this->response->setStatusCode(204);
        return $this->response;
    }

    protected function doPatch(
        array $data,
        $document,
        ClassMetadata $metadata,
        array $deeperResource = []
    ){

        $documentManager = $this->options->getDocumentManager();

        if (count($deeperResource) == 0 ){
            if (is_string($document)){
                $id = $document;
            } else {
                $id = $metadata->reflFields[$metadata->identifier]->getValue($document);
            }
            if (isset($data[$metadata->identifier]) && $data[$metadata->identifier] != $id){
                //Remember id for id update
                $newId = $data[$metadata->identifier];
            }
            $data[$metadata->identifier] = $id;
            $document = $this->unserialize($data, $document, $metadata, Serializer::UNSERIALIZE_PATCH);
            if ( ! $documentManager->contains($document) && ! $metadata->isEmbeddedDocument){
                return $this->doCreate([], $document, $metadata, []);
            }

            if (isset($newId)){
                $this->doDelete($document, $metadata, []);

                //clone the document
                $newDocument = $metadata->newInstance();
                foreach ($metadata->reflFields as $field => $refl){
                    $refl->setValue($newDocument, $refl->getValue($document));
                }
                $metadata->reflFields[$metadata->identifier]->setValue($newDocument, $newId);

                //update references
                foreach ($metadata->associationMappings as $field => $mapping){
                    if ($mapping['reference'] && $mapping['type'] == 'many' && $mapping['mappedBy']){
                        $documentManager
                            ->createQueryBuilder($mapping['targetDocument'])
                            ->update()
                            ->multiple(true)
                            ->field($mapping['mappedBy'])->equals($id)
                            ->field($mapping['mappedBy'])->set($newId)
                            ->getQuery()
                            ->execute();
                    }
                }
                return $this->doCreate([], $newDocument, $metadata, []);
            }
            return $document;
        }

        $field = $deeperResource[0];
        array_shift($deeperResource);
        if ( ! isset($metadata->fieldMappings[$field])){
            throw new Exception\DocumentNotFoundException();
        }
        $mapping = $metadata->fieldMappings[$field];

        if (isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'many'){
            $referencedMetadata = $this->getFieldMetadata($metadata, $field);
            $referencedDocuments = $this->forward()->dispatch(
                $referencedMetadata->rest['endpoint'],
                [
                    'id' => implode('/', $deeperResource),
                    'surpressResponse' => true
                ]
            );
            $document = $documentManager->getReference($metadata->name, $document);
            if ( ! is_array($referencedDocuments)){
                $referencedDocuments = [$referencedDocuments];
            }
            foreach ($referencedDocuments as $referencedDocument){
                $referencedMetadata->reflFields[$metadata->fieldMappings[$field]['mappedBy']]->setValue(
                    $referencedDocument,
                    $document
                );
            }
            return $document;
        }

        if (is_string($document)){
            $document = $documentManager
                ->createQueryBuilder()
                ->find($metadata->name)
                ->field($metadata->identifier)->equals($document)
                ->getQuery()
                ->getSingleResult();

            if ( ! $document){
                throw new Exception\DocumentNotFoundException();
            }
        }

        switch (true){
            case isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'one':
                $referenceMetadata = $this->getFieldMetadata($metadata, $field);
                if (count($deeperResource) == 0 && isset($data[$referenceMetadata->identifier])) {
                    $referenceId = $data[$referenceMetadata->identifier];
                } else {
                    $referenceId = $this->getDocumentId($metadata->reflFields[$field]->getValue($document));
                }

                array_unshift($deeperResource, $referenceId);
                $patchedDocument = $this->forward()->dispatch(
                    $referenceMetadata->rest['endpoint'],
                    [
                        'id' => implode('/', $deeperResource),
                        'surpressResponse' => true
                    ]
                );

                if (count($deeperResource) == 1){
                    $metadata->reflFields[$field]->setValue($document, $patchedDocument);
                }
                return $document;
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'many':
                $embeddedMetadata = $this->getFieldMetadata($metadata, $field);
                if (count($deeperResource) > 0){
                    $collection = $metadata->reflFields[$field]->getValue($document);
                    $key = $this->findDocumentKeyInCollection($deeperResource[0], $collection, $embeddedMetadata);
                    array_shift($deeperResource);
                    $patchedDocument = $this->doPatch(
                        $data,
                        isset($key) ? $collection[$key] : null,
                        $embeddedMetadata,
                        $deeperResource
                    );
                    if (isset($key)){
                        $collection[$key] = $patchedDocument;
                    } else {
                        $collection[] = $patchedDocument;
                    }
                    return $document;
                } else {
                    $this->doPatchList(
                        $data,
                        $embeddedMetadata,
                        $metadata->reflFields[$field]->getValue($document)
                    );
                    return $document;
                }
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'one':
                return $this->doPatch(
                    $data,
                    $metadata->reflFields[$field]->getValue($document),
                    $documentManager->getClassMetadata($metadata->fieldMappings[$field]['targetDocument']),
                    $deeperResource
                );
        }
        throw new Exception\DocumentNotFoundException();
    }

    public function patchList($data){

        $documentManager = $this->options->getDocumentManager();
        $collection = $this->doPatchList($data, $documentManager->getClassMetadata($this->options->getDocumentClass()));

        if ($this->getEvent()->getRouteMatch()->getParam('surpressResponse')){
            return $collection;
        }

        $this->flush();
        $this->response->setStatusCode(204);
        return $this->response;
    }

    protected function doPatchList(array $data, ClassMetadata $metadata, $list = []){

        $documentManager = $this->options->getDocumentManager();
        foreach ($data as $key => $item){
            $document = $this->unserialize($item, $item[$metadata->identifier], $metadata, Serializer::UNSERIALIZE_PATCH);
            if ($documentManager->contains($document)){
                $list[$key] = $document;
            } else {
                $list[$key] = $this->doCreate($item, $document, $metadata, []);
            }
        }

        return $list;
    }

    public function replaceList($data){

        $documentManager = $this->options->getDocumentManager();
        $collection = $this->doReplaceList($data, $documentManager->getClassMetadata($this->options->getDocumentClass()));

        if ($this->getEvent()->getRouteMatch()->getParam('surpressResponse')){
            return $collection;
        }

        $this->flush();
        $this->response->setStatusCode(204);
        return $this->response;
    }

    protected function doReplaceList(array $data, ClassMetadata $metadata, $list = []){
        $this->doDeleteList($metadata, $list);

        $documentManager = $this->options->getDocumentManager();
        foreach ($data as $key => $item){
            $document = $this->unserialize($item, $item[$metadata->identifier], $metadata, Serializer::UNSERIALIZE_UPDATE);
            if ($documentManager->contains($document)){
                $list[$key] = $document;
            } else {
                $list[$key] = $this->doCreate($item, $document, $metadata, []);
            }
        }

        return $list;
    }

    public function delete($id){

        $documentManager = $this->options->getDocumentManager();

        $parts = explode('/', $id);
        $document = $parts[0];
        array_shift($parts);
        $deeperResource = $parts;

        $metadata = $documentManager->getClassMetadata($this->options->getDocumentClass());
        $this->doDelete($document, $metadata, $deeperResource);
        $this->flush();

        $this->response->setStatusCode(204);
        return $this->response;
    }

    protected function doDelete($document, $metadata, $deeperResource){

        $documentManager = $this->options->getDocumentManager();

        if (count($deeperResource) == 0 ){
            if (is_string($document)){
                $documentManager
                    ->createQueryBuilder($metadata->name)
                    ->remove()
                    ->field($metadata->identifier)->equals($document)
                    ->getQuery()
                    ->execute();
            } else {
                $documentManager->remove($document);
            }
            return;
        }

        $field = $deeperResource[0];
        array_shift($deeperResource);
        $mapping = $metadata->fieldMappings[$field];

        if (isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'many'){
            return $this->forward()->dispatch(
                $this->getFieldMetadata($metadata, $field)->rest['endpoint'],
                [
                    'id' => implode('/', $deeperResource),
                    'surpressResponse' => true
                ]
            );
        }

        if (is_string($document)){
            $document = $documentManager
                ->createQueryBuilder()
                ->find($metadata->name)
                ->field($metadata->identifier)->equals($document)
                ->getQuery()
                ->getSingleResult();

            if ( ! $document){
                throw new Exception\DocumentNotFoundException();
            }
        }

        switch (true){
            case isset($mapping['reference']) && $mapping['reference'] && $mapping['type'] == 'one':
                if (count($deeperResource) > 0){
                    if (is_array($document)){
                        $fieldValue = $document[$field];
                    } else {
                        $fieldValue = $metadata->reflFields[$field]->getValue($document);
                    }

                    if ( ! isset($fieldValue)){
                        throw new Exception\DocumentNotFoundException;
                    }
                    array_unshift($deeperResource, $this->getDocumentId($fieldValue));
                    return $this->forward()->dispatch(
                        $documentManager->getClassMetadata($metadata->fieldMappings[$field]['targetDocument'])->rest['endpoint'],
                        [
                            'id' => implode('/', $deeperResource),
                            'surpressResponse' => true
                        ]
                    );
                } else {
                    $metadata->reflFields[$field]->setValue($document, null);
                }
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'many':
                $embeddedMetadata = $this->getFieldMetadata($metadata, $field);
                $collection = $metadata->reflFields[$field]->getValue($document);
                if (count($deeperResource) > 0){
                    $embeddedId = $deeperResource[0];
                    array_shift($deeperResource);
                    if( ! ($embeddedDocument = $collection->filter(function($item) use ($embeddedId, $embeddedMetadata){
                        if ($embeddedMetadata->reflFields[$embeddedMetadata->identifier]->getValue($item) == $embeddedId){
                            return true;
                        }
                    })[0])){
                        throw new Exception\DocumentNotFoundException;
                    };
                    if (count($deeperResource) == 0){
                        $collection->removeElement($embeddedDocument);
                        return;
                    } else {
                        return $this->doDelete(
                            $embeddedDocument,
                            $embeddedMetadata,
                            $deeperResource
                        );
                    }
                } else {
                    return $this->doDeleteList(
                        $embeddedMetadata,
                        $collection
                    );
                }
            case isset($mapping['embedded']) && $mapping['embedded'] && $mapping['type'] == 'one':
                if (count($deeperResource) > 0){
                    return $this->forwardEmbeddedOne('doDelete', $document, $field, $metadata, $deeperResource);
                } else {
                    $metadata->reflFields[$field]->setValue($document, null);
                    return;
                }
        }

        throw new Exception\DocumentNotFoundException();
    }

    public function deleteList(){
        $this->doDeleteList($this->options->getDocumentManager()->getClassMetadata($this->options->getDocumentClass()));
        $this->response->setStatusCode(204);
        return $this->response;
    }

    protected function doDeleteList(ClassMetadata $metadata, $list = null){

        if ($list){
            foreach ($list as $key => $item){
                $list->remove($key);
            }
        } else {
            $this->options->getDocumentManager()
                ->createQueryBuilder($metadata->name)
                ->remove()
                ->getQuery()
                ->execute();
        }
    }

    protected function unserialize(array $data, $document, ClassMetadata $metadata, $mode = null){

        if (is_string($document)){
            $data[$metadata->identifier] = $document;
            $document = null;
        } elseif (is_object($document)) {
            $data[$metadata->identifier] = $metadata->reflFields[$metadata->identifier]->getValue($document);
        }

        $serializer = $this->options->getSerializer();
        $document = $serializer->fromArray($data, $metadata->name, $mode, $document);
        return $document;
    }

    protected function findDocumentKeyInCollection($id, $collection, $metadata){

        if (isset($metadata->identifier)){
            foreach ($collection as $key => $item){
                if ($this->getDocumentId($item) == $id){
                    $embeddedDocumentKey = $key;
                    break;
                }
            }
        }
        if (! isset($embeddedDocumentKey)){
            if ( isset($collection[$id])){
                $embeddedDocumentKey = $id;
            } else {
                return;
            }
        }
        return $embeddedDocumentKey;
    }

    protected function getLimit(){

        list($lower, $upper) = $this->getRange();
        return $upper - $lower + 1;
    }

    protected function getOffset(){

        return $this->getRange()[0];
    }

    protected function getRange(){

        if (isset($this->range)){
            return $this->range;
        }

        $header = $this->getRequest()->getHeader('Range');
        $limit = $this->options->getLimit();

        if ($header) {
            list($lower, $upper) = array_map(
                function($item){return intval($item);},
                explode('-', explode('=', $header->getFieldValue())[1])
            );
            if ($lower > $upper){
                throw new Exception\BadRangeException();
            }
            if ($upper - $lower + 1 > $limit){
                $upper = $limit - 1;
            }
            $this->range = [$lower, $upper];
        } else {
            $this->range = [0, $limit - 1];
        }
        return $this->range;
    }

    protected function getCriteria($metadata){

        $result = [];
        foreach ($this->request->getQuery() as $key => $value){
            //ignore criteria that null and for fields that don't exist
            if (isset($value) && array_key_exists($key, $metadata->reflFields)){
                if (substr($value, 0, 1) == '['){
                    $value = explode(',', substr($value, 1, -1));
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function addCriteriaToQuery($query, $criteria){
        foreach($criteria as $field => $value){
            if (is_array($value)){
                $query->field($field)->in($value);
            } else {
                $query->field($field)->equals($value);
            }
        }
        return $query;
    }

    protected function addSortToQuery($query, $sort){
        foreach($sort as $s){
            $query->sort($s['field'], $s['direction']);
        }
        return $query;
    }

    protected function applyCriteriaToList($list, $criteria){
        return array_filter($list, function($item) use ($criteria){
            foreach ($criteria as $field => $criteriaValue){
                $pieces = explode('.', $field);
                $fieldValue = $item[$pieces[0]];
                array_shift($pieces);
                foreach ($pieces as $piece){
                    $fieldValue = $fieldValue[$piece];
                }
                switch (true){
                    case is_array($fieldValue && is_array($criteriaValue)):
                        foreach ($criteriaValue as $value){
                            if (in_array($value, $fieldValue)){
                                return true;
                            }
                        }
                        return false;
                    case is_array($fieldValue):
                        if (in_array($criteriaValue, $fieldValue)){
                            return true;
                        }
                        return false;
                    case is_array($criteriaValue):
                        if (in_array($fieldValue, $criteriaValue)){
                            return true;
                        }
                        return false;
                    default:
                        if ($fieldValue == $criteriaValue){
                            return true;
                        }
                        return false;
                }
            }
        });
    }

    protected function applySortToList(&$list, $sort){
        usort($list, function($a, $b) use ($sort){
            foreach ($sort as $s){
                if ($s['direction'] == 'asc'){
                    if ($a[$s['field']] < $b[$s['field']]){
                       return -1;
                    } else if ($a[$s['field']] > $b[$s['field']]) {
                        return 1;
                    }
                } else {
                    if ($a[$s['field']] > $b[$s['field']]){
                       return -1;
                    } else if ($a[$s['field']] < $b[$s['field']]) {
                        return 1;
                    }
                }
            }
            return 0;
        });
    }

    protected function getSort(){

        foreach ($this->request->getQuery() as $key => $value){
            if (substr($key, 0, 4) == 'sort' && (! isset($value) || $value == '')){
                $sort = $key;
                break;
            }
        }

        if ( ! isset($sort)){
            return [];
        }

        $sortFields = explode(',', str_replace(')', '', str_replace('sort(', '', $sort)));
        $return = [];

        foreach ($sortFields as $value)
        {
            $return[] = [
                'field' => substr($value, 1),
                'direction' => substr($value, 0, 1) == '+' ? 'asc' : 'desc'
            ];
        }

        return $return;
    }

    protected function getSelect(){
        foreach ($this->request->getQuery() as $key => $value){
            if (substr($key, 0, 6) == 'select' && (! isset($value) || $value == '')){
                $select = $key;
                break;
            }
        }

        if ( ! isset($select)){
            return;
        }

        return explode(',', str_replace(')', '', str_replace('select(', '', $select)));
    }

    protected function getIdentifier($routeMatch, $request)
    {
        $id = $routeMatch->getParam('id', false);
        if ($id) {
            return $id;
        }

        return false;
    }

    protected function getDocumentId($document){
        if (is_array($document)){
            return $document['_id'];
        }
        if (is_string($document)){
            return $document;
        }
        $metadata = $this->options->getDocumentManager()->getClassMetadata(get_class($document));
        if ($document instanceof Proxy){
            return $document->{'get' . ucfirst($metadata->identifier)}();
        } else {
            return $metadata->reflFields[$metadata->identifier]->getValue($document);
        }
    }

    protected function getFieldMetadata($metadata, $field){
        return $this
            ->options
            ->getDocumentManager()
            ->getClassMetadata($metadata->fieldMappings[$field]['targetDocument']);
    }

    protected function flush(){
        $this->options->getDocumentManager()->flush();
        if (count($this->flushExceptions) == 1){
            throw $this->flushExceptions[0];
        } elseif (count($this->flushExceptions) > 1){
            $flushException = new Exception\FlushException;
            $exceptionSerializer = $this->options->getExceptionSerializer();
            foreach ($this->flushExceptions as $exception){
                $exceptions[] = $exceptionSerializer->serializeException($exception);
            }
            $flushException->setInnerExceptions($exceptions);
            throw $flushException;
        }
    }

    public function invalidCreate(ValidatorEventArgs $eventArgs){
        $this->validationEvent($eventArgs);
    }

    public function invalidUpdate(ValidatorEventArgs $eventArgs){
        $this->validationEvent($eventArgs);
    }

    protected function validationEvent(ValidatorEventArgs $eventArgs){
        $exception = new Exception\InvalidDocumentException;
        $exception->setValidatorMessages($eventArgs->getMessages());
        $exception->setDocument($eventArgs->getDocument());
        $this->flushExceptions[] = $exception;
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

    public function accessControlEvent(AccessControlEventArgs $eventArgs){
        $exception = new Exception\AccessControlException;
        $exception->setAction($eventArgs->getAction());
        $exception->setDocument($eventArgs->getDocument());
        $this->flushExceptions[] = $exception;
    }
}
