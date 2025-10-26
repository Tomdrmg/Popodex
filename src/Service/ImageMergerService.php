<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\Response;

class ImageMergerService
{
    private Imagine $imagine;

    public function __construct()
    {
        $this->imagine = new Imagine();
    }

    /**
    * Crée deux images composites à partir de 18 images
    *
    * @param array $images Tableau de 18 images (chemins ou ressources)
    * @return array Tableau avec les contenus des images et leurs métadonnées
    */
    public function createDoubleSidedImage(array $images): array
    {
        if (count($images) !== 18) {
            throw new \InvalidArgumentException('Exactly 18 images are required');
        }

        // Dimensions des images sources
        $sourceWidth = 476;
        $sourceHeight = 665;

        // Dimensions de l'image finale
        $finalWidth = 1596;   // 3 * 476 + marges si nécessaire
        $finalHeight = 2257;  // 3 * 665 + marges si nécessaire

        // Créer les deux images finales
        $frontImage = $this->createCompositeImage(
            array_slice($images, 0, 9), // Premières 9 images
            $finalWidth,
            $finalHeight,
            $sourceWidth,
            $sourceHeight,
            false
        );

        $backImage = $this->createCompositeImage(
            array_slice($images, 9, 9), // 9 images suivantes
            $finalWidth,
            $finalHeight,
            $sourceWidth,
            $sourceHeight,
            true
        );

        // Générer le contenu des images en mémoire
        $frontContent = $frontImage->get('jpg', ['quality' => 95]);
        $backContent = $backImage->get('jpg', ['quality' => 95]);

        return [
            'front' => [
                'content' => $frontContent,
                'mime_type' => 'image/jpeg',
                'filename' => 'recto.jpg'
            ],
            'back' => [
                'content' => $backContent,
                'mime_type' => 'image/jpeg',
                'filename' => 'verso.jpg'
            ]
        ];
    }

    /**
     * Crée une image composite 3x3
     */
    private function createCompositeImage(array $images, int $canvasWidth, int $canvasHeight, int $sourceWidth, int $sourceHeight, bool $isBack)
    {
        // Créer une image vide
        $canvas = $this->imagine->create(new Box($canvasWidth, $canvasHeight));

        $columns = 3;
        $rows = 3;

        // Calculer les positions pour centrer si nécessaire
        $totalContentWidth = $columns * $sourceWidth;
        $totalContentHeight = $rows * $sourceHeight;

        $startX = ($canvasWidth - $totalContentWidth + ($isBack ? 68 : 0)) / 2; // 68px compensation du décalage de l'imprimante
        $startY = ($canvasHeight - $totalContentHeight) / 2;

        // Placer les images dans la grille
        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $columns; $col++) {
                $index = $row * $columns + ($isBack ? 2 - $col : $col);

                if (isset($images[$index])) {
                    $image = $this->loadImage($images[$index]);

                    // Redimensionner si nécessaire (au cas où les dimensions ne correspondent pas)
                    if ($image->getSize()->getWidth() !== $sourceWidth ||
                        $image->getSize()->getHeight() !== $sourceHeight) {
                        $image = $image->resize(new Box($sourceWidth, $sourceHeight));
                    }

                    // Calculer la position
                    $x = $startX + ($col * $sourceWidth);
                    $y = $startY + ($row * $sourceHeight);

                    // Placer l'image sur le canvas
                    $canvas->paste($image, new Point($x, $y));
                }
            }
        }

        return $canvas;
    }

    /**
     * Charge une image à partir de différents types d'entrée
     */
    private function loadImage($image)
    {
        if ($image instanceof UploadedFile) {
            return $this->imagine->open($image->getPathname());
        } elseif (is_string($image) && file_exists($image)) {
            return $this->imagine->open($image);
        } elseif (is_resource($image)) {
            // Gérer les ressources si nécessaire
            return $this->imagine->load($image);
        } else {
            throw new \InvalidArgumentException('Invalid image type provided');
        }
    }

    /**
     * Crée une réponse HTTP pour une image
     */
    public function createImageResponse(string $imageContent, string $filename = 'image.jpg'): Response
    {
        $response = new Response($imageContent);
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'no-cache, private');
        $response->headers->set('Content-Length', strlen($imageContent));

        return $response;
    }
}
