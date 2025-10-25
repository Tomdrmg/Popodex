<?php

namespace App\Twig\Components;

use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class FileInput
{
    public bool $compact = false;
    public bool $showPreview = true;
    public string $acceptedTypes = "";
    public FormView $formElement;

    public ?string $existingImageUrl = null;

}
