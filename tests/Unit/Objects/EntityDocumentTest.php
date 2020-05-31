<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit\Objects;

use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityField;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityResource;
use CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit\Abstracts\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;

class EntityDocumentTest extends AbstractTestCase
{
    /**
     * @return EntityDocument
     * @throws Exception
     */
    public function testInitialisation() : EntityDocument
    {
        $response = new EntityDocument($this->getServices());

        $this->assertInstanceOf(EntityDocument::class, $response);

        return $response;
    }

    /**
     * @param EntityDocument $document
     * @return EntityDocument
     * @depends testInitialisation
     * @throws Exception
     */
    public function testLoadingEntityCorrectly(EntityDocument $document) : EntityDocument
    {
        $document->loadEntity('entity');

        $this->assertEquals(1,1);

        return $document;
    }

    /**
     * @param EntityDocument $document
     * @depends testLoadingEntityCorrectly
     * @throws Exception
     */
    public function testGetField(EntityDocument $document) : void
    {
        /** @var MockObject|EntityResource $er */
        $er = $this->getMockBuilder(EntityResource::class)
        ->disableOriginalConstructor()
        ->getMock();

        $tagId = new EntityField(
            $er,
            'id',
            [
                '$type' => 'int',
                '$encrypted' => true,
                '$required' => true,
                '$databaseField' => "entityId"
            ],
            true)
        ;

        $this->assertEquals($tagId->getName(), $document->getField('id')->getName());
    }

    /**
     * @param EntityDocument $document
     * @depends testLoadingEntityCorrectly
     * @throws Exception
     */
    public function testGetRelationshipField(EntityDocument $document) : void
    {
        /** @var MockObject|EntityResource $er */
        $er = $this->getMockBuilder(EntityResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tagId = new EntityField(
            $er,
            'id',
            [
                '$type' => 'int',
                '$encrypted' => true,
                '$required' => true,
                '$databaseField' => "tagId"
            ],
            true)
        ;

        $this->assertEquals($tagId->getName(), $document->getField('tags.id')->getName());
    }
}