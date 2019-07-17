<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="`order`")
 */
class Order
{
    const STATUS_UNASSIGNED = 'UNASSIGNED';
    const STATUS_TAKEN = 'TAKEN';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("default")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("default")
     */
    private $distance;

    /**
     * @ORM\Column(type="string", length=30)
     * @Groups("default")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Regex("/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/")
     * Value should be between -90.000000 AND 90.000000
     */
    private $originLatitude;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Regex("/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/")
     * Value should be between -180.000000 AND 180.000000
     */
    private $originLongitude;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Regex("/^(\+|-)?(?:90(?:(?:\.0{1,6})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,6})?))$/")
     * Value should be between -90.000000 AND 90.000000
     */
    private $destinationLatitude;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Regex("/^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/")
     * Value should be between -180.000000 AND 180.000000
     */
    private $destinationLongitude;

    public function __construct($originLatitude, $originLongitude, $destinationLatitude, $destinationLongitude)
    {
        $this->setStatus(self::STATUS_UNASSIGNED);
        $this->setCreatedAt(new \DateTime());
        $this->setOriginLatitude($originLatitude);
        $this->setOriginLongitude($originLongitude);
        $this->setDestinationLatitude($destinationLatitude);
        $this->setDestinationLongitude($destinationLongitude);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, array(self::STATUS_UNASSIGNED, self::STATUS_TAKEN))) {
            throw new \InvalidArgumentException("Invalid status");
        }

        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOriginLatitude(): ?string
    {
        return $this->originLatitude;
    }

    public function setOriginLatitude(string $originLatitude): self
    {
        $this->originLatitude = $originLatitude;

        return $this;
    }

    public function getOriginLongitude(): ?string
    {
        return $this->originLongitude;
    }

    public function setOriginLongitude(string $originLongitude): self
    {
        $this->originLongitude = $originLongitude;

        return $this;
    }

    public function getDestinationLatitude(): ?string
    {
        return $this->destinationLatitude;
    }

    public function setDestinationLatitude(string $destinationLatitude): self
    {
        $this->destinationLatitude = $destinationLatitude;

        return $this;
    }

    public function getDestinationLongitude(): ?string
    {
        return $this->destinationLongitude;
    }

    public function setDestinationLongitude(string $destinationLongitude): self
    {
        $this->destinationLongitude = $destinationLongitude;

        return $this;
    }
}
