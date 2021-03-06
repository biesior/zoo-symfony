<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AnimalRepository::class)
 */
class Animal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min=0, max=100)
     */
    private $legs;

    /**
     * @ORM\Column(type="datetime")
     */
    private $birthDate;

    /**
     * @ORM\ManyToOne(targetEntity=Cage::class, inversedBy="animals")
     */
    private $cage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canItFly;

    /**
     * @ORM\ManyToMany(targetEntity=Caretaker::class, inversedBy="animals")
     * @ORM\JoinTable(name="caretaker_animal")
     */
    private $caretakers;

    public function __construct()
    {

        $this->caretakers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLegs(): ?int
    {
        return $this->legs;
    }

    public function setLegs(int $legs): self
    {
        $this->legs = $legs;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getCage(): ?Cage
    {
        return $this->cage;
    }

    public function setCage(?Cage $cage): self
    {
        $this->cage = $cage;

        return $this;
    }

    public function getCanItFly(): ?bool
    {
        return $this->canItFly == 1;
    }

    public function setCanItFly(bool $canItFly): self
    {
        $this->canItFly = $canItFly ? 1 : 0;

        return $this;
    }

    /**
     * @return Collection|Caretaker[]
     */
    public function getCaretakers(): Collection
    {
        return $this->caretakers;
    }

    public function addCaretaker(Caretaker $caretaker): self
    {
        if (!$this->caretakers->contains($caretaker)) {
            $this->caretakers[] = $caretaker;
            $caretaker->addAnimal($this);
        }

        return $this;
    }

    public function removeCaretaker(Caretaker $caretaker): self
    {
        if ($this->caretakers->removeElement($caretaker)) {
            $caretaker->removeAnimal($this);
        }

        return $this;
    }


    public function getDescription()
    {
        return $this->description;
    }


    public function setDescription($description): self
    {
        $this->description = $description;
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
