<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Traits;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\Links;
use CarloNicora\JsonApi\Objects\Meta;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\LinkBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\BuilderLinksInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\LinkBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Traits\LinkCreatorTrait;
use Exception;

trait LinkBuilderTrait
{
    use LinkCreatorTrait;

    /** @var array|LinkBuilderInterface[]  */
    protected array $links=[];

    /**
     * @param string $name
     * @param string $link
     * @return LinkBuilderInterface
     */
    final protected function generateLink(
        string $name,
        string $link
    ) : LinkBuilderInterface
    {
        $response = new LinkBuilder($name, $link);

        $this->links[$name] = $response;

        return $response;
    }

    /**
     * @param LinkBuilder $link
     */
    public function addLink(
        LinkBuilder $link
    ): void
    {
        $this->links[$link->getName()] = $link;
    }

    /**
     * @return array
     */
    public function getLinks() : array
    {
        return $this->links;
    }

    /**
     * @param BuilderLinksInterface $builder
     * @param ResourceBuilderInterface $resourceBuilder
     * @param Links $links
     * @param array $data
     * @param ResourceObject|null $resourceObject
     * @throws Exception
     */
    private function buildLinks(
        BuilderLinksInterface $builder,
        ResourceBuilderInterface $resourceBuilder,
        Links $links,
        array $data,
        ResourceObject $resourceObject=null
    ): void
    {
        foreach ($builder->getLinks() as $link) {
            $url = $this->buildLink($link->getLink(), $resourceBuilder, $data, $resourceObject);

            if (($linkBuilder = $this->servicesProxy->getLinkBuilder()) !== null){
                /** @var ResourceBuilderInterface $rbi */
                $rbi = $this;
                $url = $linkBuilder->buildLink($url, $rbi, $data);
            }

            $meta = null;

            if ($link->getMeta() !== null){
                $meta = new Meta();

                foreach ($link->getMeta() as $metaName=>$metaValue){
                    $meta->add($metaName, $metaValue);
                }
            }

            $links->add(
                new Link(
                    $link->getName(),
                    $url,
                    $meta
                )
            );
        }
    }
}