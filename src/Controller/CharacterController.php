<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\CharacterExtra;
use App\Entity\CharacterPhoto;
use App\Form\CharacterAlbumUploader;
use App\Form\CharacterCoverUploader;
use App\Form\CharacterPhotoUploader;
use App\Form\CharacterSheetUploader;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CharacterController extends Controller
{
    /**
     * @Route("/character/{characterNameKeyUrl}", name="character")
     */
    public function index($characterNameKeyUrl = null)
    {
        $character = $this->getUser()->getCharacters()->current();

        if (empty($character)) {
            throw $this->createNotFoundException('Non hai ancora un personaggio');
        }

        $isMine = false;
        if (empty($characterNameKeyUrl)) {
            $isMine = true;
        } else if ($character->getCharacterNameKeyUrl() !== $characterNameKeyUrl) {
            $character = $this->getDoctrine()->getRepository(Character::class)->findByKeyUrl($characterNameKeyUrl)[0] ?? null;
            if (empty($character)) {
                $character = $this->getUser()->getCharacters()->current();
            }
        } else {
            $isMine = true;
        }

        $photos = $this->getDoctrine()->getRepository(CharacterPhoto::class)->getPhotos($character);

        return $this->render('character/index.html.twig', [
            'controller_name' => 'CharacterController',
            'isMine' => $isMine,
            'character' => $character,
            'photos' => $photos
        ]);
    }

    /**
     * @Route("/character/upload/photo", name="character-upload-photo")
     */
    public function uploadPhoto(Request $request)
    {
        if ($request->request->get('character_id')) {
            $character = $this->getDoctrine()->getRepository(Character::class)->find($request->request->get('character_id'));
        } else {
            $character = $this->getUser()->getCharacters()->current();
        }

        if (empty($character)) {
            throw $this->createNotFoundException('Non hai ancora un personaggio');
        }

        $tempCharacter = new Character();
        $form = $this->createForm(CharacterPhotoUploader::class, $tempCharacter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // $file stores the uploaded PDF file

            /** @var UploadedFile $file */
            $file = $tempCharacter->getPhoto();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('photo_directory'),
                $fileName
            );

            // updates the 'brochure' property to store the PDF file name
            // instead of its contents
            $character->setPhoto($fileName);
            $this->getDoctrine()->getManager()->persist($character);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
        ]);
    }

    /**
     * @Route("/character/upload/cover", name="character-upload-cover")
     */
    public function uploadCover(Request $request)
    {
        $tempCharacter = new CharacterExtra();
        $form = $this->createForm(CharacterCoverUploader::class, $tempCharacter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // $file stores the uploaded PDF file

            /** @var UploadedFile $file */
            $file = $tempCharacter->getCover();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('cover_directory'),
                $fileName
            );

            // updates the 'brochure' property to store the PDF file name
            // instead of its contents
            if ($request->request->get('character_id')) {
                $character = $this->getDoctrine()->getRepository(Character::class)->find($request->request->get('character_id'));
            } else {
                $character = $this->getUser()->getCharacters()->current();
            }

            $characterExtra =$character->getExtra();
            if (null === $characterExtra) {
                $characterExtra = new CharacterExtra();
                $character = $this->getUser()->getCharacter();
                $character->setExtra($characterExtra);
                $this->getDoctrine()->getManager()->persist($character);
            }

            $characterExtra->setCover($fileName);
            $this->getDoctrine()->getManager()->persist($characterExtra);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
        ]);
    }

    /**
     * @Route("/character/upload/album", name="character-upload-album")
     */
    public function uploadAlbum(Request $request)
    {
        $tempCharacter = new CharacterPhoto();
        $form = $this->createForm(CharacterAlbumUploader::class, $tempCharacter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // $file stores the uploaded PDF file

            /** @var UploadedFile $file */
            $file = $tempCharacter->getPath();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('album_directory'),
                $fileName
            );

            // updates the 'brochure' property to store the PDF file name
            // instead of its contents
            if ($request->request->get('character_id')) {
                $character = $this->getDoctrine()->getRepository(Character::class)->find($request->request->get('character_id'));
            } else {
                $character = $this->getUser()->getCharacters()->current();
            }

            $tempCharacter->setCharacter($character);
            $tempCharacter->setPath($fileName);
            $tempCharacter->setUploadDate(new \DateTime());

            $this->getDoctrine()->getRepository(CharacterPhoto::class)->cleanAlbum($character);

            $this->getDoctrine()->getManager()->persist($tempCharacter);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
        ]);
    }

    /**
     * @Route("/character/upload/sheet", name="character-upload-sheet")
     */
    public function uploadSheet(Request $request)
    {
        $character = $this->getDoctrine()->getRepository(Character::class)->find($request->request->get('character_id'));

        $tempCharacter = new CharacterExtra();
        $form = $this->createForm(CharacterSheetUploader::class, $tempCharacter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // $file stores the uploaded PDF file

            /** @var UploadedFile $file */
            $file = $tempCharacter->getSheet();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('sheet_directory'),
                $fileName
            );

            // updates the 'brochure' property to store the PDF file name
            // instead of its contents

            $characterExtra = $character->getExtra();

            $characterExtra->setSheet($fileName);
            $this->getDoctrine()->getManager()->persist($characterExtra);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
        ]);
    }

    /**
     * @Route("/character/update/bio", name="character-update-bio")
     */
    public function updateBio(Request $request)
    {
        if ($request->request->get('character_id')) {
            $character = $this->getDoctrine()->getRepository(Character::class)->find($request->request->get('character_id'));
        } else {
            $character = $this->getUser()->getCharacters()->current();
        }

        $extra = $character->getExtra();
        $extra->setBio($request->request->get('character_bio_updater')['bio']);

        $this->getDoctrine()->getManager()->persist($extra);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
        ]);
    }

    /**
     * @Route("/character/update/quote", name="character-update-quote")
     */
    public function updateQuote(Request $request)
    {
        if ($request->request->get('character_id')) {
            $character = $this->getDoctrine()->getRepository(Character::class)->find($request->request->get('character_id'));
        } else {
            $character = $this->getUser()->getCharacters()->current();
        }

        $extra = $character->getExtra();
        $extra->setQuote($request->request->get('quote'));
        $extra->setCite($request->request->get('cite'));

        $this->getDoctrine()->getManager()->persist($extra);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
        ]);
    }

    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}
