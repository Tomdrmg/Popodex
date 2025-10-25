<?php

namespace App\Entity;

use App\Repository\CardBackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: CardBackRepository::class)]
class CardBack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $backgroundImage = null;

    #[ORM\Column]
    private ?int $borderOpacity = 100;

    #[ORM\Column]
    private ?int $borderWidth = 12;

    #[ORM\Column]
    private ?int $fontSize = 62;

    #[ORM\Column]
    private ?int $outlineWidth = 14;

    #[ORM\Column]
    private ?int $textPosition = 195;

    #[ORM\Column]
    private ?int $curvature = 50;

    private ?File $backgroundImageFile = null;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\OneToMany(targetEntity: Card::class, mappedBy: 'back')]
    private Collection $cards;

    #[ORM\Column(length: 255)]
    private ?string $renderedImage = null;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getBackgroundImage(): ?string
    {
        return $this->backgroundImage;
    }

    public function setBackgroundImage(string $backgroundImage): static
    {
        $this->backgroundImage = $backgroundImage;

        return $this;
    }

    public function getBorderOpacity(): ?int
    {
        return $this->borderOpacity;
    }

    public function setBorderOpacity(int $borderOpacity): static
    {
        $this->borderOpacity = $borderOpacity;

        return $this;
    }

    public function getBorderWidth(): ?int
    {
        return $this->borderWidth;
    }

    public function setBorderWidth(int $borderWidth): static
    {
        $this->borderWidth = $borderWidth;

        return $this;
    }

    public function getFontSize(): ?int
    {
        return $this->fontSize;
    }

    public function setFontSize(int $fontSize): static
    {
        $this->fontSize = $fontSize;

        return $this;
    }

    public function getOutlineWidth(): ?int
    {
        return $this->outlineWidth;
    }

    public function setOutlineWidth(int $outlineWidth): static
    {
        $this->outlineWidth = $outlineWidth;

        return $this;
    }

    public function getTextPosition(): ?int
    {
        return $this->textPosition;
    }

    public function setTextPosition(int $textPosition): static
    {
        $this->textPosition = $textPosition;

        return $this;
    }

    public function getCurvature(): ?int
    {
        return $this->curvature;
    }

    public function setCurvature(int $curvature): static
    {
        $this->curvature = $curvature;

        return $this;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setBack($this);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getBack() === $this) {
                $card->setBack(null);
            }
        }

        return $this;
    }

    public function getBackgroundImageFile(): ?File
    {
        return $this->backgroundImageFile;
    }

    public function setBackgroundImageFile(?File $backgroundImageFile): static
    {
        $this->backgroundImageFile = $backgroundImageFile;

        return $this;
    }

    public function getRenderedImage(): ?string
    {
        return $this->renderedImage;
    }

    public function setRenderedImage(string $renderedImage): static
    {
        $this->renderedImage = $renderedImage;

        return $this;
    }
}
