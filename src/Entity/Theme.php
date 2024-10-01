<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isAudio = null;

    #[ORM\Column]
    private ?bool $card = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isAudio(): ?bool
    {
        return $this->isAudio;
    }

    public function setAudio(bool $isAudio): static
    {
        $this->isAudio = $isAudio;

        return $this;
    }

    public function isCard(): ?bool
    {
        return $this->card;
    }

    public function setCard(bool $card): static
    {
        $this->card = $card;

        return $this;
    }
}
