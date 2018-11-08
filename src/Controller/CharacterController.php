<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\CharacterExtra;
use App\Entity\CharacterPhoto;
use App\Entity\Clan;
use App\Entity\Merits;
use App\Entity\Contact;
use App\Entity\Covenant;
use App\Entity\Rank;
use App\Entity\User;
use App\Form\CharacterAlbumUploader;
use App\Form\CharacterCoverUploader;
use App\Form\CharacterCreate;
use App\Form\CharacterPhotoUploader;
use App\Form\CharacterSheetUploader;
use App\Form\RolesEdit;
use App\Utils\ConnectionSystem;
use App\Utils\NotificationsSystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\NoCharacterException;

class CharacterController extends Controller
{
    /**
     * @Route("/character/{characterNameKeyUrl}", name="character")
     */
    public function index($characterNameKeyUrl = null, ConnectionSystem $connectionSystem)
    {
        $character = $this->getUser()->getCharacters()->current();
        $editForm = null;
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $character = $this->getDoctrine()->getRepository(Character::class)->findByKeyUrl($characterNameKeyUrl)[0] ?? null;
        }
        if ($this->isGranted('ROLE_CENSOR')) {
            $editForm = $this->createForm(RolesEdit::class, $character)->createView();
        }

        if (empty($character)) {
            throw new NoCharacterException();
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

        $areConnected = false;
        $connectionInfo = null;
        if (!$isMine && !$this->isGranted('ROLE_STORY_TELLER')) {
            $areConnected = $connectionSystem->areConnected(
                $this->getUser()->getCharacters()->current(), $character
            );
            if (!$areConnected) {
                $connectionInfo = $connectionSystem->getConnectionStatus(
                    $this->getUser()->getCharacters()->current(), $character
                );
            }
        }

        $meritsForm = $this->createForm(CharacterCreate::class, $character);

        return $this->render('character/index.html.twig', [
            'isMine' => $isMine,
            'character' => $character,
            'photos' => $photos,
            'editForm' => $editForm,
            'areConnected' => $areConnected,
            'connectionInfo' => $connectionInfo,
            'meritsForm' => $meritsForm->createView()
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
            throw new NoCharacterException();
        }

        $tempCharacter = new Character();
        $form = $this->createForm(CharacterPhotoUploader::class, $tempCharacter);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // $file stores the uploaded PDF file

            /** @var UploadedFile $file */
            $file = $tempCharacter->getPhoto();

            $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

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

            $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

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

            $characterExtra = $character->getExtra();
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

            $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

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

            $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

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

            $character->setRank($this->getDoctrine()->getManager()->getRepository(Rank::class)->find(4));
            $this->getDoctrine()->getManager()->persist($character->getExtra());
            $this->getDoctrine()->getManager()->persist($character);
            $this->getDoctrine()->getManager()->flush();

            $notificationsSystem->publishNewCharacter($character);

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

    /**
     * @Route("/characters/edit-roles", name="character_update_roles")
     */
    public function updateRoles(Request $request, NotificationsSystem $notificationsSystem)
    {
        /** @var Character $character */
        $character = $this->getDoctrine()->getRepository(Character::class)->find($request->query->get('character_id'));

        $characterModel = new Character();
        $editForm = $this->createForm(RolesEdit::class, $characterModel);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {

            if ($character->getRank() !== $characterModel->getRank()) {
                $who = 'L\'Imperatore';
                $notificationsSystem->roleUpdated($character, $who, 'la tua carica');
                $character->setRank($characterModel->getRank());                
                
                if (null !== $character->getUser()) {
                    if ($characterModel->getRank() !== null) {
                        $character->getUser()->setRole(['ROLE_REGISTERED']);
                    }
                }
            }

            $this->getDoctrine()->getManager()->flush();

        }
        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
        ]);
    }

    /**
     * @Route("/characters/all", name="all-selector")
     */
    public function allSelect(Request $request)
    {
        return new JsonResponse(
            array_map(
                function (Character $character) {
                    return [
                        'id' => $character->getId(),
                        'name' => $character->getCharacterName(),
                        'url' => $this->generateUrl('character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()])
                    ];
                },
                $this->getDoctrine()->getRepository(Character::class)->getAll($request->query->get('n'), $this->isGranted('ROLE_CENSOR'))
            )
        );
    }

    /**
     * @Route("/characters/png", name="png-selector")
     */
    public function pngSelect(Request $request)
    {
        return new JsonResponse(
            array_map(
                function (Character $character) {
                    return [
                        'id' => $character->getId(),
                        'name' => $character->getCharacterName(),
                        'url' => $this->generateUrl('messenger_chat', ['characterName' => $character->getCharacterNameKeyUrl()])
                    ];
                },
                $this->getDoctrine()->getRepository(Character::class)->getAllPng()
            )
        );
    }

    /**
     * @Route("/characters/pg", name="pg-selector")
     */
    public function pgSelect(Request $request)
    {

        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $characters = $this->getDoctrine()->getRepository(Character::class)->findAll();
        } else {
            $characters = $this->getDoctrine()->getRepository(Character::class)->getAllPg($this->getUser()->getCharacters()->current());
        }
        return new JsonResponse(
            array_map(
                function (Character $character) {
                    return [
                        'id' => $character->getId(),
                        'name' => $character->getCharacterName(),
                        'url' => $this->generateUrl('messenger_chat', ['characterName' => $character->getCharacterNameKeyUrl()])
                    ];
                },
                $characters
            )
        );
    }

    /**
     * @Route("/connection/modal/{action}/{id}", name="character-connection-modal")
     */
    public function modalConnection($action, $id, ConnectionSystem $connectionSystem)
    {
        switch ($action) {
            case 'send':
                $url = $this->generateUrl('character-connection-send', [
                    'characterId' => $id
                ]);
            break;
            //SOLO ROLE_STORY_TELLER
            case 'manage':

                $pgs = $this->getDoctrine()->getManager()->getRepository(Character::class)->findAll();
                $currentPG = $this->getDoctrine()->getManager()->getRepository(Character::class)->find($id);

                return $this->render('character/connect-manage.html.twig', [
                    'currentCharacter' => $id,
                    'pgs' => array_map (function($pg) use ($currentPG, $connectionSystem) {
                        $data['id'] = $pg->getId();
                        $data['characterName'] = $pg->getCharacterName();
                        $data['connectionInfo'] = $connectionSystem->getConnectionStatus($currentPG, $pg);
                        return $data;
                    }, $pgs)
                ]);
            break;
            case 'view':
                $user = $this->getUser()->getCharacters()[0];
                $connections = $connectionSystem->getAllContactRequest($user);

                $pgs = [];
                if (!empty($connections)) {
                    $pgs = array_map (function(Contact $connection) use ($user, $connectionSystem) {

                        if ($connection->getCharacter1()->equals($user)) {
                            $data['pg'] = $connection->getCharacter2();
                        } else {
                            $data['pg'] = $connection->getCharacter1();
                        }
                        $data['connectionInfo'] = $connectionSystem->getConnectionStatus($user, $data['pg']);
                        return $data;
                    }, $connections);
                }
                return $this->render('character/connect-view.html.twig', [
                    'currentCharacter' => $id,
                    'pgs' => $pgs
                ]);
            break;
            default:
            case 'confirm':
                $url = $this->generateUrl('character-connection-confirm', [
                    'connectionId' => $id
                ]);
            break;
        }
        return $this->render('character/connect.html.twig', [
            'url' => $url
        ]);
    }

    /**
     * @Route("/connection/delete/{connectionId}", name="character-connection-delete")
     */
    public function deleteConnection($connectionId, ConnectionSystem $connectionSystem)
    {
        $connectionSystem->disconnect($connectionId);
        return new JsonResponse();
    }

    /**
     * @Route("/connection/force/{character1Id}/{character2Id}", name="character-connection-force")
     */
    public function forceConnection($character1Id, $character2Id, ConnectionSystem $connectionSystem)
    {
        $character1 = $this->getDoctrine()->getManager()->getRepository(Character::class)->find($character1Id);
        $character2 = $this->getDoctrine()->getManager()->getRepository(Character::class)->find($character2Id);

        $connecionInfo = $connectionSystem->getConnectionStatus($character1, $character2);

        if (empty($connecionInfo)) {
            $connectionSystem->connect($character1, $character2, true);
        } else {
            if ($connecionInfo->currentUserIsRequesting) {
                $connectionSystem->confirm($connecionInfo->connectionId, $character2, true);
            } else {
                $connectionSystem->confirm($connecionInfo->connectionId, $character1, true);
            }
        }
        return new JsonResponse();
    }

    /**
     * @Route("/connection/confirm/{connectionId}", name="character-connection-confirm")
     */
    public function confirmConnection($connectionId, ConnectionSystem $connectionSystem)
    {
        $connectionSystem->confirm($connectionId, $this->getUser()->getCharacters()[0]);
        return new JsonResponse();
    }

    /**
     * @Route("/connection/send/{characterId}", name="character-connection-send")
     */
    public function sendConnection($characterId, ConnectionSystem $connectionSystem)
    {
        $character2 = $this->getDoctrine()->getManager()->getRepository(Character::class)->find($characterId);
        $connectionSystem->connect($this->getUser()->getCharacters()[0], $character2);
        return new JsonResponse();
    }

    /**
    * @Route("/characters/downtime/show/{dtid}", name="characters-downtime-show")
    */
    public function downtimeShow($dtid)
    {
        $dt = $this->getDoctrine()->getManager()->getRepository(Merits::class)->find($dtid);
        return new JsonResponse([
            'name' => $dt->getName(),
            'dt' => $dt->getAssociatedDowntime()
        ]);
    }

    /**
    * @Route("/characters/edit/{characterid}/do", name="character-edit-do")
    */
    public function editDo(Request $request, $characterid = null)
    {
        if ($characterid != null) {
            $character = $this->getDoctrine()->getManager()->getRepository(Character::class)->find($characterid);
        } else {
            $character = new Character();
        }

        $form = $this->createForm(CharacterCreate::class, $character);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $character->getUser()->setRole([$character->getFigs()->getRole()]);
            
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute("character", ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]);
    }

    /**
    * @Route("/characters/edit/{characterid}/{action}", name="character-edit")
    */
    public function edit($characterid = null, $action = null)
    {
        if ($characterid != null) {
            $character = $this->getDoctrine()->getManager()->getRepository(Character::class)->find($characterid);
        } else {
            $character = new Character();
        }
        $form = $this->createForm(CharacterCreate::class, $character);
        return $this->render('character/edit.html.twig', [
            'form' => $form->createView(),
            'action' => $action == 'edit' ? $this->generateUrl('character-edit-do',['characterid' => $character->getId()]) : $this->generateUrl('character-create')
        ]);
    }

    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}
