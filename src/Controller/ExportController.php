<?php

namespace App\Controller;

use App\Entity\Card;
use App\Repository\CardRepository;
use App\Service\ImageMergerService;
use App\Service\PdfCreatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExportController extends AbstractController
{
    #[Route('/export/{ids}', name: 'app_export', requirements: ['ids' => '\d+(,\d+)*'])]
    public function export(string $ids, CardRepository $repository, ImageMergerService $imageMerger, PdfCreatorService $pdfCreator): Response
    {
        try {
            // Convertir la chaîne d'IDs en tableau
            $idArray = explode(',', $ids);

            // Nettoyer les IDs (supprimer les espaces, etc.)
            $idArray = array_map('intval', $idArray);
            $idArray = array_filter($idArray); // Supprimer les valeurs vides

            if (empty($idArray)) {
                return $this->json(['error' => 'Aucun ID valide fourni'], 400);
            }

            // Récupérer les entités

            /** @var array<int, Card> $entities */
            $entities = $repository->findBy(['id' => $idArray]);

            if (empty($entities)) {
                return $this->json(['error' => 'Aucune entité trouvée'], 404);
            }

            $images = [];

            /** @var array<int, array<int, Card>> $groups */
            $groups = array_chunk($entities, 9);
            foreach ($groups as $group) {
                $imagePaths = array_fill(0, 18, null);
                for ($i = 0; $i < count($group); $i++) {
                    $imagePaths[$i] = $this->getParameter('card_directory') . '/' . $group[$i]->getRenderedImage();
                    $imagePaths[9 + $i] = $this->getParameter('back_card_directory') . '/' . $group[$i]->getBack()->getRenderedImage();
                }

                $imagesData = $imageMerger->createDoubleSidedImage($imagePaths);
                $images[] = $imagesData['front'];
                $images[] = $imagesData['back'];
            }

            return $pdfCreator->createPdfFromImages($images);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
