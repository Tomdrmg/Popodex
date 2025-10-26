<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CardBack;
use App\Entity\Series;
use App\Form\CardBackType;
use App\Form\CardType;
use App\Form\SeriesType;
use App\Service\CardDrawerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class CardController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_cards');
    }

    #[Route('/cards', name: 'app_cards')]
    public function cards(EntityManagerInterface $entityManager): Response
    {
        return $this->render('card/cards.html.twig', [
            "allSeries" => $entityManager->getRepository(Series::class)->findAll(),
        ]);
    }

    #[Route('/card/new', name: 'app_card_new')]
    public function cardNew(Request $request, EntityManagerInterface $entityManager): Response {
        $card = new Card();
        return $this->cardHandleForm($request, $card, $entityManager, 'new');
    }

    #[Route('/card/{id}/edit', name: 'app_card_edit')]
    public function cardEdit(Request $request, Card $card, EntityManagerInterface $entityManager): Response {
        return $this->cardHandleForm($request, $card, $entityManager, 'edit');
    }

    private function cardHandleForm(Request $request, Card $card, EntityManagerInterface $entityManager, string $action): Response {
        $isNew = $action === 'new';

        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $backgroundImageFile = $form->get('backgroundImageFile')->getData();
            $previewImageData = $form->get('previewImage')->getData();

            $validFiles = true;

            if ($isNew) {
                if (!$imageFile) {
                    $this->addFlash('error', 'Aucune image transmise');
                    $validFiles = false;
                }

                if (!$previewImageData) {
                    $this->addFlash('error', 'Aucune preview transmise');
                    $validFiles = false;
                }

                if (!$card->isFullArt() && !$backgroundImageFile) {
                    $this->addFlash('error', 'Aucune image de fond transmise');
                    $validFiles = false;
                }
            }

            if ($validFiles) {
                if ($isNew) {
                    // To generate id
                    $card->setImage("temp");
                    $card->setBackgroundImage("temp");
                    $card->setRenderedImage("temp");
                    $entityManager->persist($card);
                    $entityManager->flush();
                }

                // Gestion de la preview
                if ($previewImageData) {
                    try {
                        if (!$isNew) {
                            $oldFilename = $card->getRenderedImage();
                            if ($oldFilename && file_exists($this->getParameter('card_directory').'/'.$oldFilename)) {
                                unlink($this->getParameter('card_directory').'/'.$oldFilename);
                            }
                        }

                        // Décoder l'image base64
                        $previewData = explode(',', $previewImageData);
                        $previewImage = base64_decode($previewData[1]);

                        $epoch = time();
                        $previewFilename = 'rendered-card-'.$card->getId().'-'.$epoch.'.png';
                        $previewPath = $this->getParameter('card_directory').'/'.$previewFilename;

                        file_put_contents($previewPath, $previewImage);

                        $card->setRenderedImage($previewFilename);
                        $card->setLastRender(time());
                    } catch (\Exception $e) {
                        $this->addFlash('warning', 'Erreur lors de la sauvegarde de la preview');
                    }
                }

                if ($imageFile) {
                    // Pour l'édition, supprimer l'ancienne image si elle existe
                    if (!$isNew) {
                        $oldFilename = $card->getImage();
                        if ($oldFilename && file_exists($this->getParameter('card_directory').'/'.$oldFilename)) {
                            unlink($this->getParameter('card_directory').'/'.$oldFilename);
                        }
                    }

                    // Générer le nouveau nom de fichier
                    $epoch = time();
                    $extension = $imageFile->guessExtension();
                    $newFilename = 'image-card-'.$card->getId().'-'.$epoch.'.'.$extension;

                    try {
                        $imageFile->move(
                            $this->getParameter('card_directory'),
                            $newFilename
                        );

                        $card->setImage($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                    }
                }

                if ($card->isFullArt()) {
                    if (!$isNew) {
                        $oldFilename = $card->getBackgroundImage();
                        if ($oldFilename && file_exists($this->getParameter('card_directory').'/'.$oldFilename)) {
                            unlink($this->getParameter('card_directory').'/'.$oldFilename);
                        }
                    }

                    $card->setBackgroundImage(null);
                }

                if (!$card->isFullArt() && $backgroundImageFile) {
                    // Pour l'édition, supprimer l'ancienne image si elle existe
                    if (!$isNew) {
                        $oldFilename = $card->getBackgroundImage();
                        if ($oldFilename && file_exists($this->getParameter('card_directory').'/'.$oldFilename)) {
                            unlink($this->getParameter('card_directory').'/'.$oldFilename);
                        }
                    }

                    // Générer le nouveau nom de fichier
                    $epoch = time();
                    $extension = $backgroundImageFile->guessExtension();
                    $newFilename = 'background-card-'.$card->getId().'-'.$epoch.'.'.$extension;

                    try {
                        $backgroundImageFile->move(
                            $this->getParameter('card_directory'),
                            $newFilename
                        );

                        $card->setBackgroundImage($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                    }
                }

                if ($isNew) {
                    $entityManager->persist($card);
                }
                $entityManager->flush();

                $this->addFlash('success', $isNew ? 'Carte créé avec succès!' : 'Carte modifié avec succès!');
                return $this->redirectToRoute('app_cards');
            }
        }

        return $this->render('card/cardForm.html.twig', [
            'form' => $form->createView(),
            'card' => $card,
            'isNew' => $isNew,
            'backs' => $entityManager->getRepository(CardBack::class)->findAll(),
            'existingBackgroundImageUrl' => $card->getBackgroundImage() ? $this->generateUrl('app_card_background', ['id' => $card->getId()]).'?v='.$card->getLastRender() : null,
            'existingImageUrl' => $card->getImage() ? $this->generateUrl('app_card_image', ['id' => $card->getId()]).'?v='.$card->getLastRender() : null
        ]);
    }

    #[Route('/card/{id}/image', name: 'app_card_image')]
    public function getCardImage(Card $card, Request $request): Response
    {
        $filename = $card->getImage();
        $filePath = $this->getParameter('card_directory').'/'.$filename;

        return $this->returnImage($filename, $filePath, $request);
    }

    #[Route('/card/{id}/background', name: 'app_card_background')]
    public function getCardBackground(Card $card, Request $request): Response
    {
        $filename = $card->getBackgroundImage();
        $filePath = $this->getParameter('card_directory').'/'.$filename;

        return $this->returnImage($filename, $filePath, $request);
    }

    #[Route('/card/{id}/render', name: 'app_card_render')]
    public function getCardRender(Card $card, Request $request): Response
    {
        $filename = $card->getRenderedImage();
        $filePath = $this->getParameter('card_directory').'/'.$filename;

        return $this->returnImage($filename, $filePath, $request);
    }

    #[Route('/card/{id}/delete', name: 'app_card_delete')]
    public function cardDelete(EntityManagerInterface $entityManager, Card $card): Response
    {
        $oldFilename = $card->getRenderedImage();
        if ($oldFilename && file_exists($this->getParameter('card_directory').'/'.$oldFilename)) {
            unlink($this->getParameter('card_directory').'/'.$oldFilename);
        }

        $oldFilename = $card->getBackgroundImage();
        if ($oldFilename && file_exists($this->getParameter('card_directory').'/'.$oldFilename)) {
            unlink($this->getParameter('card_directory').'/'.$oldFilename);
        }

        $oldFilename = $card->getImage();
        if ($oldFilename && file_exists($this->getParameter('card_directory').'/'.$oldFilename)) {
            unlink($this->getParameter('card_directory').'/'.$oldFilename);
        }

        $entityManager->remove($card);
        $entityManager->flush();

        $this->addFlash('info', 'Carte supprimée');
        return $this->redirectToRoute('app_cards');
    }

    #[Route('/card/series', name: 'app_series')]
    public function series(Request $request, EntityManagerInterface $entityManager): Response
    {
        $series = new Series();
        $form = $this->createForm(SeriesType::class, $series);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($series);
            $entityManager->flush();

            $this->addFlash('success', 'Série créée avec succès!');
            return $this->redirectToRoute('app_series');
        }

        $allSeries = $entityManager->getRepository(Series::class)->findAll();

        return $this->render('card/series.html.twig', [
            'series' => $allSeries,
            'form' => $form->createView(),
            'editingSeries' => null,
        ]);
    }

    #[Route('/card/series/{id}/edit', name: 'app_series_edit')]
    public function edit(Request $request, Series $series, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SeriesType::class, $series);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Série modifiée avec succès!');
            return $this->redirectToRoute('app_series');
        }

        $allSeries = $entityManager->getRepository(Series::class)->findAll();

        return $this->render('card/series.html.twig', [
            'series' => $allSeries,
            'form' => $form->createView(),
            'editingSeries' => $series,
        ]);
    }

    #[Route('card/series/{id}/delete', name: 'app_series_delete')]
    public function delete(Request $request, Series $series, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($series);
        $entityManager->flush();
        $this->addFlash('success', 'Série supprimée avec succès!');

        return $this->redirectToRoute('app_series');
    }

    #[Route('/card/backs', name: 'app_backs')]
    public function backs(EntityManagerInterface $entityManager): Response
    {
        return $this->render('card/backs.html.twig', [
            "backs" => $entityManager->getRepository(CardBack::class)->findAll(),
        ]);
    }

    #[Route('/card/back/new', name: 'app_back_new')]
    public function backNew(Request $request, EntityManagerInterface $entityManager): Response {
        $cardBack = new CardBack();
        return $this->backHandleForm($request, $cardBack, $entityManager, 'new');
    }

    #[Route('/card/back/{id}/edit', name: 'app_back_edit')]
    public function backEdit(Request $request, CardBack $cardBack, EntityManagerInterface $entityManager): Response {
        return $this->backHandleForm($request, $cardBack, $entityManager, 'edit');
    }

    private function backHandleForm(Request $request, CardBack $cardBack, EntityManagerInterface $entityManager, string $action): Response {
        $isNew = $action === 'new';

        $form = $this->createForm(CardBackType::class, $cardBack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $backgroundImageFile = $form->get('backgroundImageFile')->getData();
            $previewImageData = $form->get('previewImage')->getData();

            $validFiles = true;

            if ($isNew) {
                if (!$previewImageData) {
                    $this->addFlash('error', 'Aucune preview transmise');
                    $validFiles = false;
                }

                if (!$backgroundImageFile) {
                    $this->addFlash('error', 'Aucune image de fond transmise');
                    $validFiles = false;
                }
            }

            if ($validFiles) {
                if ($isNew) {
                    // To generate id
                    $cardBack->setBackgroundImage("temp");
                    $cardBack->setRenderedImage("temp");
                    $entityManager->persist($cardBack);
                    $entityManager->flush();
                }

                // Gestion de la preview
                if ($previewImageData) {
                    try {
                        if (!$isNew) {
                            $oldFilename = $cardBack->getRenderedImage();
                            if ($oldFilename && file_exists($this->getParameter('back_card_directory').'/'.$oldFilename)) {
                                unlink($this->getParameter('back_card_directory').'/'.$oldFilename);
                            }
                        }

                        // Décoder l'image base64
                        $previewData = explode(',', $previewImageData);
                        $previewImage = base64_decode($previewData[1]);

                        $epoch = time();
                        $previewFilename = 'rendered-back-'.$cardBack->getId().'-'.$epoch.'.png';
                        $previewPath = $this->getParameter('back_card_directory').'/'.$previewFilename;

                        file_put_contents($previewPath, $previewImage);

                        $cardBack->setRenderedImage($previewFilename);
                        $cardBack->setLastRender(time());
                    } catch (\Exception $e) {
                        $this->addFlash('warning', 'Erreur lors de la sauvegarde de la preview');
                    }
                }

                if ($backgroundImageFile) {
                    // Pour l'édition, supprimer l'ancienne image si elle existe
                    if (!$isNew) {
                        $oldFilename = $cardBack->getBackgroundImage();
                        if ($oldFilename && file_exists($this->getParameter('back_card_directory').'/'.$oldFilename)) {
                            unlink($this->getParameter('back_card_directory').'/'.$oldFilename);
                        }
                    }

                    // Générer le nouveau nom de fichier
                    $epoch = time();
                    $extension = $backgroundImageFile->guessExtension();
                    $newFilename = 'background-back-'.$cardBack->getId().'-'.$epoch.'.'.$extension;

                    try {
                        $backgroundImageFile->move(
                            $this->getParameter('back_card_directory'),
                            $newFilename
                        );

                        $cardBack->setBackgroundImage($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                    }
                }

                if ($isNew) {
                    $entityManager->persist($cardBack);
                }
                $entityManager->flush();

                $this->addFlash('success', $isNew ? 'Dos de carte créé avec succès!' : 'Dos de carte modifié avec succès!');
                return $this->redirectToRoute('app_backs');
            }
        }

        return $this->render('card/backForm.html.twig', [
            'form' => $form->createView(),
            'cardBack' => $cardBack,
            'isNew' => $isNew,
            'existingImageUrl' => $cardBack->getBackgroundImage() ? $this->generateUrl('app_back_background', ['id' => $cardBack->getId()]).'?v='.$cardBack->getLastRender() : null
        ]);
    }

    #[Route('/card/back/{id}/background', name: 'app_back_background')]
    public function getBackBackground(CardBack $cardBack, Request $request): Response
    {
        $filename = $cardBack->getBackgroundImage();
        $filePath = $this->getParameter('back_card_directory').'/'.$filename;

        return $this->returnImage($filename, $filePath, $request);
    }

    #[Route('/card/back/{id}/render', name: 'app_back_render')]
    public function getBackRender(CardBack $cardBack, Request $request): Response
    {
        $filename = $cardBack->getRenderedImage();
        $filePath = $this->getParameter('back_card_directory').'/'.$filename;

        return $this->returnImage($filename, $filePath, $request);
    }

    #[Route('/card/back/{id}/delete', name: 'app_back_delete')]
    public function backDelete(EntityManagerInterface $entityManager, CardBack $cardBack): Response
    {
        $oldFilename = $cardBack->getRenderedImage();
        if ($oldFilename && file_exists($this->getParameter('back_card_directory').'/'.$oldFilename)) {
            unlink($this->getParameter('back_card_directory').'/'.$oldFilename);
        }

        $oldFilename = $cardBack->getBackgroundImage();
        if ($oldFilename && file_exists($this->getParameter('back_card_directory').'/'.$oldFilename)) {
            unlink($this->getParameter('back_card_directory').'/'.$oldFilename);
        }

        $entityManager->remove($cardBack);
        $entityManager->flush();

        $this->addFlash('info', 'Dos de carte supprimé');
        return $this->redirectToRoute('app_backs');
    }

    public function returnImage(string $filename, string $filePath, Request $request): Response
    {
        if (!$filename || !file_exists($filePath)) {
            throw $this->createNotFoundException('Image non trouvée');
        }

        // Vérification rapide si le fichier n'a pas changé
        $lastModified = \DateTime::createFromFormat('U', (string) filemtime($filePath));
        $etag = md5_file($filePath);

        // Créer une réponse pour la vérification
        $response = new Response();
        $response->setEtag($etag);
        $response->setLastModified($lastModified);
        $response->setPrivate();
        $response->setMaxAge(30 * 24 * 60 * 60);
        $response->headers->addCacheControlDirective('must-revalidate');

        // Si pas modifié, retourner 304 immédiatement
        if ($response->isNotModified($request)) {
            return $response;
        }

        // Seulement si modifié, streamer le fichier
        $streamResponse = new StreamedResponse(function() use ($filePath) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = fopen($filePath, 'rb');
            stream_copy_to_stream($fileStream, $outputStream);
            fclose($fileStream);
            fclose($outputStream);
        });

        // Copier les headers de cache vers la réponse streamée
        $streamResponse->headers->add($response->headers->all());
        $streamResponse->headers->set('Content-Type', mime_content_type($filePath));
        $streamResponse->headers->set('Content-Disposition', 'inline; filename="'.$filename.'"');
        $streamResponse->headers->set('Content-Length', (string) filesize($filePath));

        return $streamResponse;
    }
}
