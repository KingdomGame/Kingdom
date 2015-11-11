<?php

namespace Rottenwood\KingdomBundle\Entity\Infrastructure;

class ItemRepository extends AbstractRepository {

    /**
     * @param int $itemId
     * @return Item|null
     */
    public function findById($itemId) {
        return $this->find($itemId);
    }

    /**
     * @return Item[]
     */
    public function findAllItems() {
        return $this->findAll();
    }
}
