<?php

namespace App\Twig\Components;

use App\Entity\Subject;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Footer
{
    public ?User $user = null;

    public function __construct(private readonly Security $security)
    {

    }

    public function mount(): void
    {
        $this->user = $this->security->getUser();
    }
}
