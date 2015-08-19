<?php

namespace Rottenwood\KingdomBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Деньги
 * @ORM\Table(name="money")
 * @ORM\Entity(repositoryClass="Rottenwood\KingdomBundle\Entity\Infrastructure\MoneyRepository")
 */
class Money {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * Персонаж
     * @ORM\OneToOne(targetEntity="Rottenwood\KingdomBundle\Entity\User")
     * @ORM\JoinColumn(name="character", referencedColumnName="id")
     * @var User
     */
    private $user;

    /**
     * Серебряные монеты
     * @ORM\Column(name="silver", type="integer")
     * @var int
     */
    private $silver;

    /**
     * @ORM\Column(name="gold", type="integer")
     * @var int
     */
    private $gold;

    /**
     * @param User $user
     */
    public function __construct(User $user) { $this->user = $user; }

    /**
     * @return int
     */
    public function getSilver() {
        return $this->silver;
    }

    /**
     * @param int $silver
     */
    public function setSilver($silver) {
        $this->silver = $silver;
    }

    /**
     * @return int
     */
    public function getGold() {
        return $this->gold;
    }

    /**
     * @param int $gold
     */
    public function setGold($gold) {
        $this->gold = $gold;
    }
}
