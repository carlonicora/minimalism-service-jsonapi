<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Objects\Traits;

use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityLink;

trait LinksTrait
{
    /** @var array|null  */
    private ?array $links=null;

    /**
     * @param array $links
     */
    protected function addLinks(array $links) : void
    {
        $this->links = [];
        foreach ($links ?? [] as $linkName=>$link) {
            if (is_array($link)){
                $this->links[] = new EntityLink($linkName, $link['href'], $link['meta']);
            } else {
                $this->links[] = new EntityLink($linkName, $link);
            }
        }
    }

    /**
     * @return array|null|EntityLink[]
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }
}