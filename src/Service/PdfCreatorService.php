<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

class PdfCreatorService
{
    /**
     * Crée un PDF pour impression recto-verso avec gestion du retournement.
     * Il faut imprimer sur la face avant, retourner la feuille et imprimer sur l'arrière. (prendre la droite et mettre à gauche |123| -> |321|)
     *
     * @param array $imagesData Données des images (front et back)
     * @param string $orientation Type d'image ('P' : portrait ou 'L' : longueur)
     * @param string $filename Nom du fichier PDF
     * @return Response
     */
    public function createPdfFromImages(array $imagesData, string $orientation = 'P', string $filename = 'impression.pdf'): Response
    {
        $pdf = new \TCPDF($orientation, 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->setMargins(0, 0, 0);

        foreach ($imagesData as $image) {
            $pdf->AddPage();
            $this->addImageToPage($pdf, $image['content']);
        }

        $pdfContent = $pdf->Output('', 'S');

        return $this->createPdfResponse($pdfContent, $filename);
    }

    /**
     * Ajoute une image à une page en l'adaptant
     */
    private function addImageToPage(\TCPDF $pdf, string $imageContent): void
    {
        // Créer un fichier temporaire
        $tempFile = tempnam(sys_get_temp_dir(), 'img_');
        file_put_contents($tempFile, $imageContent);

        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        // Obtenir les dimensions de l'image
        $imageInfo = getimagesize($tempFile);
        $imageWidth = $imageInfo[0];
        $imageHeight = $imageInfo[1];

        // Calculer le ratio pour adapter l'image à la page
        $ratioWidth = $pageWidth / $imageWidth;
        $ratioHeight = $pageHeight / $imageHeight;
        $ratio = min($ratioWidth, $ratioHeight);

        // Nouvelles dimensions
        $newWidth = $imageWidth * $ratio;
        $newHeight = $imageHeight * $ratio;

        // Centrer l'image
        $x = ($pageWidth - $newWidth) / 2;
        $y = ($pageHeight - $newHeight) / 2;

        // Ajouter l'image
        $pdf->Image($tempFile, $x, $y, $newWidth, $newHeight, 'JPEG', '', '', false, 300, '', false, false, 0);

        // Nettoyer
        unlink($tempFile);
    }

    /**
     * Ajoute une image en pleine page
     */
    private function addImageToFullPage(\TCPDF $pdf, string $imageContent): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'img_');
        file_put_contents($tempFile, $imageContent);

        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();

        // Utiliser toute la page
        $pdf->Image($tempFile, 0, 0, $pageWidth, $pageHeight, 'JPEG', '', '', false, 300, '', false, false, 0);

        unlink($tempFile);
    }

    /**
     * Crée la réponse HTTP pour le PDF
     */
    private function createPdfResponse(string $pdfContent, string $filename): Response
    {
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContent($pdfContent);
        $response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'no-cache, private');
        $response->headers->set('Content-Length', strlen($pdfContent));

        return $response;
    }
}
