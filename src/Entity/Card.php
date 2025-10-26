<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'cards.html.twig')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Series $series = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $backgroundImage = null;

    #[ORM\Column]
    private ?bool $fullArt = false;

    #[ORM\Column]
    private ?int $imageVerticalPosition = 50;

    #[ORM\Column]
    private ?int $borderOpacity = 100;

    #[ORM\Column]
    private ?int $borderWidth = 12;

    #[ORM\Column]
    private ?int $movesMarginTop = 75;

    /**
     * @var Collection<int, CardMove>
     */
    #[ORM\OneToMany(targetEntity: CardMove::class, mappedBy: 'card', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $moves;

    #[ORM\ManyToOne(inversedBy: 'cards.html.twig')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CardBack $back = null;

    #[ORM\Column(length: 255)]
    private ?string $renderedImage = null;

    #[ORM\Column]
    private ?int $lastRender = 0;

    public function __construct()
    {
        $this->moves = new ArrayCollection();
        $this->date = new \DateTime();
    }

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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getSeries(): ?Series
    {
        return $this->series;
    }

    public function setSeries(?Series $series): static
    {
        $this->series = $series;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getBackgroundImage(): ?string
    {
        return $this->backgroundImage;
    }

    public function setBackgroundImage(?string $backgroundImage): static
    {
        $this->backgroundImage = $backgroundImage;

        return $this;
    }

    public function isFullArt(): ?bool
    {
        return $this->fullArt;
    }

    public function setFullArt(bool $fullArt): static
    {
        $this->fullArt = $fullArt;

        return $this;
    }

    public function getImageVerticalPosition(): ?int
    {
        return $this->imageVerticalPosition;
    }

    public function setImageVerticalPosition(int $imageVerticalPosition): static
    {
        $this->imageVerticalPosition = $imageVerticalPosition;

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

    public function getMovesMarginTop(): ?int
    {
        return $this->movesMarginTop;
    }

    public function setMovesMarginTop(int $movesMarginTop): static
    {
        $this->movesMarginTop = $movesMarginTop;

        return $this;
    }

    /**
     * @return Collection<int, CardMove>
     */
    public function getMoves(): Collection
    {
        return $this->moves;
    }

    public function addMove(CardMove $move): static
    {
        if (!$this->moves->contains($move)) {
            $this->moves->add($move);
            $move->setCard($this);
        }

        return $this;
    }

    public function removeMove(CardMove $move): static
    {
        if ($this->moves->removeElement($move)) {
            // set the owning side to null (unless already changed)
            if ($move->getCard() === $this) {
                $move->setCard(null);
            }
        }

        return $this;
    }

    public function getBack(): ?CardBack
    {
        return $this->back;
    }

    public function setBack(?CardBack $back): static
    {
        $this->back = $back;

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

    public function getLastRender(): ?int
    {
        return $this->lastRender;
    }

    public function setLastRender(int $lastRender): static
    {
        $this->lastRender = $lastRender;

        return $this;
    }
}
