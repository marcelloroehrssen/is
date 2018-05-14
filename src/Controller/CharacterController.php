<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\CharacterExtra;
use App\Entity\CharacterPhoto;
use App\Entity\Clan;
use App\Entity\Covenant;
use App\Entity\Notifications;
use App\Entity\User;
use App\Form\CharacterAlbumUploader;
use App\Form\CharacterCoverUploader;
use App\Form\CharacterCreate;
use App\Form\CharacterPhotoUploader;
use App\Form\CharacterSheetUploader;
use App\UserAlreadyAssociatedException;
use App\Utils\NotificationsSystem;
use Doctrine\ORM\PersistentCollection;
use http\Env\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CharacterController extends Controller
{
    /**
     * @Route("/character/{characterNameKeyUrl}", name="character")
     */
    public function index($characterNameKeyUrl = null)
    {
        $character = $this->getUser()->getCharacters()->current();
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $character = $this->getDoctrine()->getRepository(Character::class)->findByKeyUrl($characterNameKeyUrl)[0] ?? null;
        }

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
    public function uploadSheet(Request $request, NotificationsSystem $notificationsSystem)
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

            $notificationsSystem->publishNewCharacterSheet($this->getUser(), $character);
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


    /**
     * @Route("/characters", name="characters")
     */
    public function characters()
    {
        $characters = $this->getDoctrine()->getRepository(Character::class)->getAllCharacterOrderedByAssociation();

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        usort($users,
            function (User $user1, User $user2) {
                return $user1->getCharacters()->count() > $user2->getCharacters()->count();
            }
        );

        $clans = $this->getDoctrine()->getRepository(Clan::class)->findAll();

        $covenant = $this->getDoctrine()->getRepository(Covenant::class)->findAll();

        return $this->render('character/list.html.twig', [
            'characters' => $characters,
            'users' => $users,
            'clan' => $clans,
            'covenant' => $covenant,
        ]);
    }

    /**
     * @Route("/characters/associate", name="character-associate")
     */
    public function charactersAssociate(Request $request, NotificationsSystem $notificationsSystem)
    {
        $characterId = $request->query->get('character');
        $userId = $request->query->get('user');
        $conflict = $request->query->getBoolean('conflict', false);

        $character = $this->getDoctrine()->getRepository(Character::class)->find($characterId);
        if (empty($character)) {
            $this->createNotFoundException("Personaggio $characterId non trovato, operazione non riscita");
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($userId);
        if (empty($user)) {
            $this->createNotFoundException("User $characterId non trovato, operazione non riuscita");
        }

        if (!$conflict && $user->getCharacters()->count() > 0) {
            return new JsonResponse([
                'username' => $user->getUsername(),
                'userId' => (int)$user->getId(),
                'characterName' => $user->getCharacters()[0]->getCharacterName(),
                'characterId' => (int)$user->getCharacters()[0]->getId(),
                'newCharacterName' => $character->getCharacterName(),
                'newCharacterId' => (int)$characterId
            ], \Symfony\Component\HttpFoundation\Response::HTTP_CONFLICT);
        }

        if ($conflict) {
            $oldCharacter = $user->getCharacters()[0];
            $oldCharacter->setUser(null);
            $this->getDoctrine()->getManager()->persist($oldCharacter);
        }

        $character->setUser($user);
        $this->getDoctrine()->getManager()->persist($character);
        $this->getDoctrine()->getManager()->flush();

        $notificationsSystem->associateCharacter($this->getUser(), $character);

        return new JsonResponse([]);
    }

    /**
     * @Route("/characters/create", name="character-create")
     */
    public function charactersCreate(Request $request, NotificationsSystem $notificationsSystem)
    {
        $character = new Character();
        $form = $this->createForm(CharacterCreate::class, $character);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (empty($character->getExtra())) {
                $character->setExtra(new CharacterExtra());
            }
            $this->getDoctrine()->getManager()->persist($character->getExtra());
            $this->getDoctrine()->getManager()->persist($character);
            $this->getDoctrine()->getManager()->flush();

            $notificationsSystem->publishNewCharacter($this->getUser(), $character);

            return $this->redirectToRoute("characters");
        }


        return $this->render('character/create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/characters/delete", name="character-delete")
     */
    public function removeCharacter(Request $request)
    {
        $characterId = $request->query->get('character');
        $character = $this->getDoctrine()->getRepository(Character::class)->find($characterId);
        if (empty($character)) {
            $this->createNotFoundException("Personaggio $characterId non trovato, operazione non riuscita");
        }

        $this->getDoctrine()->getManager()->remove($character);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute("characters");
    }

    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}
