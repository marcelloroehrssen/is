<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\CharacterExtra;
use App\Entity\CharacterPhoto;
use App\Entity\CharacterStat;
use App\Entity\Contact;
use App\Entity\User;
use App\Form\CharacterAlbumUploader;
use App\Form\CharacterCoverUploader;
use App\Form\CharacterCreate;
use App\Form\CharacterPhotoUploader;
use App\Form\CharacterSheetUploader;
use App\Form\CharacterStatType;
use App\Form\RolesEdit;
use App\Repository\CharacterPhotoRepository;
use App\Repository\CharacterRepository;
use App\Repository\ClanRepository;
use App\Repository\CovenantRepository;
use App\Repository\RankRepository;
use App\Repository\UserRepository;
use App\Utils\ConnectionSystem;
use App\Utils\NotificationsSystem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\NoCharacterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Endroid\QrCode\Response\QrCodeResponse;
use Endroid\QrCode\ErrorCorrectionLevel;

class CharacterController extends AbstractController
{
    /**
     * @Route("/character/{characterNameKeyUrl}", name="character")
     *
     * @param string|null $characterNameKeyUrl
     * @param ConnectionSystem $connectionSystem
     * @param CharacterRepository $characterRepository
     * @param CharacterPhotoRepository $characterPhotoRepository
     *
     * @return Response
     *
     * @throws NoCharacterException
     */
    public function index(
        ConnectionSystem $connectionSystem,
        CharacterRepository $characterRepository,
        CharacterPhotoRepository $characterPhotoRepository,
        string $characterNameKeyUrl = null)
    {
        $character = $this->getUser()->getCharacters()->current();
        $editForm = null;
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $character = $characterRepository->findByKeyUrl($characterNameKeyUrl)[0] ?? null;
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
        } elseif ($character->getCharacterNameKeyUrl() !== $characterNameKeyUrl) {
            $character = $characterRepository->findByKeyUrl($characterNameKeyUrl)[0] ?? null;
            if (empty($character)) {
                $character = $this->getUser()->getCharacters()->current();
            }
        } else {
            $isMine = true;
        }

        $photos = $characterPhotoRepository->getPhotos($character);

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
            'meritsForm' => $meritsForm->createView(),
        ]);
    }

    /**
     * @Route("/character/upload/photo", name="character-upload-photo")
     *
     * @param Request $request
     * @param CharacterRepository $characterRepository
     *
     * @return RedirectResponse
     *
     * @throws NoCharacterException
     */
    public function uploadPhoto(
        Request $request,
        CharacterRepository $characterRepository)
    {
        if ($request->request->get('character_id')) {
            $character = $characterRepository->find($request->request->get('character_id'));
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

            $this->addFlash('notice', 'Foto caricata con successo');
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl(),
        ]);
    }

    /**
     * @Route("/character/upload/cover", name="character-upload-cover")
     *
     * @param Request $request
     * @param CharacterRepository $characterRepository
     *
     * @return RedirectResponse
     */
    public function uploadCover(Request $request, CharacterRepository $characterRepository)
    {
        $tempCharacter = new CharacterExtra();
        $form = $this->createForm(CharacterCoverUploader::class, $tempCharacter);
        $form->handleRequest($request);
        $character = null;
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
                $character = $characterRepository->find($request->request->get('character_id'));
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

            $this->addFlash('notice', 'Cover caricata con successo');
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl(),
        ]);
    }

    /**
     * @Route("/character/upload/album", name="character-upload-album")
     *
     * @param Request $request
     * @param CharacterRepository $characterRepository
     * @param CharacterPhotoRepository $characterPhotoRepository
     *
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function uploadAlbum(
        Request $request,
        CharacterRepository $characterRepository,
        CharacterPhotoRepository $characterPhotoRepository)
    {
        $tempCharacter = new CharacterPhoto();
        $form = $this->createForm(CharacterAlbumUploader::class, $tempCharacter);
        $form->handleRequest($request);
        $character = null;
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
                $character = $characterRepository->find($request->request->get('character_id'));
            } else {
                $character = $this->getUser()->getCharacters()->current();
            }

            $tempCharacter->setCharacter($character);
            $tempCharacter->setPath($fileName);
            $tempCharacter->setUploadDate(new \DateTime());

            $characterPhotoRepository->cleanAlbum($character);

            $this->getDoctrine()->getManager()->persist($tempCharacter);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', 'Foto album caricata con successo');
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl(),
        ]);
    }

    /**
     * @Route("/character/upload/sheet", name="character-upload-sheet")
     *
     * @param Request $request
     * @param NotificationsSystem $notificationsSystem
     * @param CharacterRepository $characterRepository
     *
     * @return RedirectResponse
     */
    public function uploadSheet(
        Request $request,
        NotificationsSystem $notificationsSystem,
        CharacterRepository $characterRepository)
    {
        $character = $characterRepository->find($request->request->get('character_id'));

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

            $characterExtra = $character->getExtra();

            $characterExtra->setSheet($fileName);
            $this->getDoctrine()->getManager()->persist($characterExtra);
            $this->getDoctrine()->getManager()->flush();

            $notificationsSystem->publishNewCharacterSheet($this->getUser(), $character);

            $this->addFlash('notice', 'Scheda personaggio caricata con successo');
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl(),
        ]);
    }

    /**
     * @Route("/character/update/bio", name="character-update-bio")
     *
     * @param Request $request
     * @param CharacterRepository $characterRepository
     *
     * @return RedirectResponse
     */
    public function updateBio(
        Request $request,
        CharacterRepository $characterRepository)
    {
        if ($request->request->get('character_id')) {
            $character = $characterRepository->find($request->request->get('character_id'));
        } else {
            $character = $this->getUser()->getCharacters()->current();
        }

        $extra = $character->getExtra();
        $extra->setBio($request->request->get('character_bio_updater')['bio']);

        $this->getDoctrine()->getManager()->persist($extra);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('notice', 'Bio aggiornato con successo');

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl(),
        ]);
    }

    /**
     * @Route("/character/update/quote", name="character-update-quote")
     *
     * @param Request $request
     * @param CharacterRepository $characterRepository
     *
     * @return RedirectResponse
     */
    public function updateQuote(
        Request $request,
        CharacterRepository $characterRepository)
    {
        if ($request->request->get('character_id')) {
            $character = $characterRepository->find($request->request->get('character_id'));
        } else {
            $character = $this->getUser()->getCharacters()->current();
        }

        $extra = $character->getExtra();
        $extra->setQuote($request->request->get('quote'));
        $extra->setCite($request->request->get('cite'));

        $this->getDoctrine()->getManager()->persist($extra);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('notice', 'Citazione aggiornata con successo');

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl(),
        ]);
    }

    /**
     * @Route("/characters", name="characters")
     *
     * @param CharacterRepository $characterRepository
     * @param UserRepository $userRepository
     * @param ClanRepository $clanRepository
     * @param CovenantRepository $covenantRepository
     *
     * @return Response
     */
    public function characters(
        CharacterRepository $characterRepository,
        UserRepository $userRepository,
        ClanRepository $clanRepository,
        CovenantRepository $covenantRepository)
    {
        $characters = $characterRepository->getAllCharacterOrderedByAssociation();

        $users = $userRepository->findAll();
        usort($users,
            function (User $user1, User $user2) {
                return $user1->getCharacters()->count() > $user2->getCharacters()->count();
            }
        );

        $clans = $clanRepository->findAll();
        $covenant = $covenantRepository->findAll();

        return $this->render('character/list.html.twig', [
            'characters' => $characters,
            'users' => $users,
            'clan' => $clans,
            'covenant' => $covenant,
        ]);
    }

    /**
     * @Route("/characters/associate", name="character-associate")
     *
     * @param Request $request
     * @param NotificationsSystem $notificationsSystem
     * @param CharacterRepository $characterRepository
     * @param UserRepository $userRepository
     *
     * @return JsonResponse
     */
    public function charactersAssociate(
        Request $request,
        NotificationsSystem $notificationsSystem,
        CharacterRepository $characterRepository,
        UserRepository $userRepository)
    {
        $characterId = $request->query->get('character');
        $userId = $request->query->get('user');
        $conflict = $request->query->getBoolean('conflict', false);

        $character = $characterRepository->find($characterId);
        if (empty($character)) {
            $this->createNotFoundException("Personaggio $characterId non trovato, operazione non riscita");
        }

        $user = $userRepository->find($userId);
        if (empty($user)) {
            $this->createNotFoundException("User $characterId non trovato, operazione non riuscita");
        }

        if (!$conflict && $user->getCharacters()->count() > 0) {
            return new JsonResponse([
                'username' => $user->getUsername(),
                'userId' => (int) $user->getId(),
                'characterName' => $user->getCharacters()[0]->getCharacterName(),
                'characterId' => (int) $user->getCharacters()[0]->getId(),
                'newCharacterName' => $character->getCharacterName(),
                'newCharacterId' => (int) $characterId,
            ], Response::HTTP_CONFLICT);
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
     *
     * @param Request $request
     * @param NotificationsSystem $notificationsSystem
     * @param RankRepository $rankRepository
     *
     * @return RedirectResponse|Response
     */
    public function charactersCreate(
        Request $request,
        NotificationsSystem $notificationsSystem,
        RankRepository $rankRepository)
    {
        $character = new Character();
        $form = $this->createForm(CharacterCreate::class, $character);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (empty($character->getExtra())) {
                $character->setExtra(new CharacterExtra());
            }

            $character->setRank($rankRepository->find(4));
            $this->getDoctrine()->getManager()->persist($character->getExtra());
            $this->getDoctrine()->getManager()->persist($character);
            $this->getDoctrine()->getManager()->flush();

            $notificationsSystem->publishNewCharacter($character);

            $this->addFlash('notice', 'Personaggio creato con successo');

            return $this->redirectToRoute('characters');
        }

        return $this->render('character/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/characters/delete", name="character-delete")
     *
     * @param Request $request
     * @param CharacterRepository $characterRepository
     *
     * @return RedirectResponse
     */
    public function removeCharacter(
        Request $request,
        CharacterRepository $characterRepository)
    {
        $characterId = $request->query->get('character');
        $character = $characterRepository->find($characterId);
        if (empty($character)) {
            $this->createNotFoundException("Personaggio $characterId non trovato, operazione non riuscita");
        }

        $this->getDoctrine()->getManager()->remove($character);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('notice', 'Personaggio rimosso con successo');

        return $this->redirectToRoute('characters');
    }

    /**
     * @Route("/characters/edit-roles", name="character_update_roles")
     *
     * @param Request $request
     * @param NotificationsSystem $notificationsSystem
     * @param CharacterRepository $characterRepository
     *
     * @return RedirectResponse
     */
    public function updateRoles(
        Request $request,
        NotificationsSystem $notificationsSystem,
        CharacterRepository $characterRepository)
    {
        /** @var Character $character */
        $character = $characterRepository->find($request->query->get('character_id'));

        $characterModel = new Character();
        $editForm = $this->createForm(RolesEdit::class, $characterModel);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if ($character->getRank() !== $characterModel->getRank()) {
                $who = 'L\'Imperatore';
                $notificationsSystem->roleUpdated($character, $who, 'la tua carica');
                $character->setRank($characterModel->getRank());

                if (null !== $character->getUser()) {
                    if (null !== $characterModel->getRank()) {
                        $character->getUser()->setRole(['ROLE_REGISTERED']);
                    }
                }
            }

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', 'Ruolo aggiornato con successo');
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl(),
        ]);
    }

    /**
     * @Route("/characters/all", name="all-selector")
     *
     * @param Request $request
     * @param CharacterRepository $characterRepository
     *
     * @return JsonResponse
     */
    public function allSelect(
        Request $request,
        CharacterRepository $characterRepository)
    {
        return new JsonResponse(
            array_map(
                function (Character $character) {
                    return [
                        'id' => $character->getId(),
                        'name' => $character->getCharacterName(),
                        'url' => $this->generateUrl(
                            'character', ['characterNameKeyUrl' => $character->getCharacterNameKeyUrl()]
                        ),
                    ];
                },
                $characterRepository->getAll($request->query->get('n'), $this->isGranted('ROLE_CENSOR'))
            )
        );
    }

    /**
     * @Route("/characters/png", name="png-selector")
     *
     * @param CharacterRepository $characterRepository
     *
     * @return JsonResponse
     */
    public function pngSelect(CharacterRepository $characterRepository)
    {
        return new JsonResponse(
            array_map(
                function (Character $character) {
                    return [
                        'id' => $character->getId(),
                        'name' => $character->getCharacterName(),
                        'url' => $this->generateUrl(
                            'messenger_chat', ['characterName' => $character->getCharacterNameKeyUrl()]
                        ),
                    ];
                },
                $characterRepository->getAllPng()
            )
        );
    }

    /**
     * @Route("/characters/pg", name="pg-selector")
     *
     * @param Request $request
     * @param CharacterRepository $characterRepository
     *
     * @return JsonResponse
     */
    public function pgSelect(
        Request $request,
        CharacterRepository $characterRepository)
    {
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $characters = $characterRepository->getAllPg(
                $request->query->get('n')
            );
        } else {
            $characters = $characterRepository->getAllPg(
                $request->query->get('n'),
                $this->getUser()->getCharacters()->current()
            );
        }

        return new JsonResponse(
            array_map(
                function (Character $character) {
                    return [
                        'id' => $character->getId(),
                        'name' => $character->getCharacterName(),
                        'url' => $this->generateUrl(
                            'messenger_chat', ['characterName' => $character->getCharacterNameKeyUrl()]
                        ),
                    ];
                },
                $characters
            )
        );
    }

    /**
     * @Route("/connection/modal/{action}/{id}", name="character-connection-modal")
     *
     * @param string $action
     * @param int $id
     * @param ConnectionSystem $connectionSystem
     * @param CharacterRepository $characterRepository
     *
     * @return Response
     */
    public function modalConnection(
        string $action,
        int $id,
        ConnectionSystem $connectionSystem,
        CharacterRepository $characterRepository)
    {
        switch ($action) {
            case 'send':
                $url = $this->generateUrl('character-connection-send', [
                    'characterId' => $id,
                ]);
            break;
            //SOLO ROLE_STORY_TELLER
            case 'manage':

                $pgs = $characterRepository->findAll();
                $currentPG = $characterRepository->find($id);

                return $this->render('character/connect-manage.html.twig', [
                    'currentCharacter' => $id,
                    'pgs' => array_map(function ($pg) use ($currentPG, $connectionSystem) {
                        $data['id'] = $pg->getId();
                        $data['characterName'] = $pg->getCharacterName();
                        $data['connectionInfo'] = $connectionSystem->getConnectionStatus($currentPG, $pg);

                        return $data;
                    }, $pgs),
                ]);
            break;
            case 'view':
                $user = $this->getUser()->getCharacters()[0];
                $connections = $connectionSystem->getAllContactRequest($user);

                $pgs = [];
                if (!empty($connections)) {
                    $pgs = array_map(function (Contact $connection) use ($user, $connectionSystem) {
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
                    'pgs' => $pgs,
                ]);
            break;
            default:
            case 'confirm':
                $url = $this->generateUrl('character-connection-confirm', [
                    'connectionId' => $id,
                ]);
            break;
        }

        return $this->render('character/connect.html.twig', [
            'url' => $url,
        ]);
    }

    /**
     * @Route("/connection/delete/{connectionId}", name="character-connection-delete")
     *
     * @param int $connectionId
     * @param ConnectionSystem $connectionSystem
     *
     * @return JsonResponse
     */
    public function deleteConnection(
        int $connectionId,
        ConnectionSystem $connectionSystem)
    {
        $connectionSystem->disconnect($connectionId);
        return new JsonResponse();
    }

    /**
     * @Route("/connection/force/{character1Id}/{character2Id}", name="character-connection-force")
     * @ParamConverter("character1", options={"id" = "character1Id"})
     * @ParamConverter("character2", options={"id" = "character2Id"})
     *
     * @param Character $character1
     * @param Character $character2
     * @param ConnectionSystem $connectionSystem
     *
     * @return JsonResponse
     */
    public function forceConnection(
        Character $character1,
        Character $character2,
        ConnectionSystem $connectionSystem)
    {
        $connectionInfo = $connectionSystem->getConnectionStatus($character1, $character2);

        if (empty($connectionInfo)) {
            $connectionSystem->connect($character1, $character2, true);
        } else {
            if ($connectionInfo->currentUserIsRequesting) {
                $connectionSystem->confirm($connectionInfo->connectionId, $character2, true);
            } else {
                $connectionSystem->confirm($connectionInfo->connectionId, $character1, true);
            }
        }

        return new JsonResponse();
    }

    /**
     * @Route("/connection/confirm/{connectionId}", name="character-connection-confirm")
     *
     * @param int $connectionId
     * @param ConnectionSystem $connectionSystem
     *
     * @return JsonResponse
     */
    public function confirmConnection(
        int $connectionId,
        ConnectionSystem $connectionSystem)
    {
        $connectionSystem->confirm($connectionId, $this->getUser()->getCharacters()[0]);
        return new JsonResponse();
    }

    /**
     * @Route("/connection/send/{characterId}", name="character-connection-send")
     * @ParamConverter("character", options={"id" = "characterId"})
     *
     * @param Character $character
     * @param ConnectionSystem $connectionSystem
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function sendConnection(
        Character $character,
        ConnectionSystem $connectionSystem)
    {
        $connectionSystem->connect($this->getUser()->getCharacters()[0], $character);
        return new JsonResponse();
    }

    /**
     * @Route("/characters/edit/do/{characterid}", name="character-edit-do", defaults={"characterid"=null})
     * @ParamConverter("character", options={"id" = "characterid"})
     *
     * @param Request $request
     * @param Character|null $character
     *
     * @return RedirectResponse
     */
    public function editDo(
        Request $request,
        Character $character = null)
    {
        $character = $character ?: new Character();

        $form = $this->createForm(CharacterCreate::class, $character);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $character->getUser()) {
                $character->getUser()->setRole([$character->getFigs()->getRole()]);
            }
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
        ]);
    }

    /**
     * @Route("/characters/edit/{characterid}/{action}", name="character-edit", defaults={"characterid"=null})
     * @ParamConverter("character", options={"id" = "characterid"})
     *
     * @param Character|null $character
     * @param string|null $action
     *
     * @return Response
     */
    public function edit(Character $character = null, string $action = null)
    {
        $character = $character ?: new Character();

        $form = $this->createForm(CharacterCreate::class, $character);

        $actionEditOrCreate = $this->generateUrl('character-create');
        if ('edit' == $action) {
            $actionEditOrCreate = $this->generateUrl('character-edit-do', ['characterid' => $character->getId()]);
        }

        return $this->render('character/edit.html.twig', [
            'form' => $form->createView(),
            'action' => $actionEditOrCreate
        ]);
    }

    /**
     * @Route("/characters/stats/add/{characterid}", name="character-add-stats")
     * @ParamConverter("character", options={"id" = "characterid"})
     *
     * @param Character $character
     * @param Request $request
     *
     * @return Response
     */
    public function statsAdd(Character $character, Request $request)
    {
        $stats = new CharacterStat();
        $stats->setCharacter($character);

        $form = $this->createForm(CharacterStatType::class, $stats);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($stats);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('character', [
                'characterNameKeyUrl' => $character->getCharacterNameKeyUrl()
            ]);
        }

        return $this->render('character/stats_add.html.twig', [
            'form' => $form->createView(),
            'characterid' => $character->getId()
        ]);
    }

    /**
     * @Route("/characters/stats/remove/{csid}", name="character-remove-stats")
     * @ParamConverter("characterStat", options={"id" = "csid"})
     *
     * @param CharacterStat $characterStat
     *
     * @return Response
     */
    public function statsRemove(CharacterStat $characterStat)
    {
        $this->getDoctrine()->getManager()->remove($characterStat);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('character', [
            'characterNameKeyUrl' => $characterStat->getCharacter()->getCharacterNameKeyUrl()
        ]);
    }

    /**
     * @Route("/characters/qr/{type}-{size}-{id}", name="characters-qr-view")
     *
     * @param string $type
     * @param string $size
     * @param string $id
     *
     * @return Response
     */
    public function qr(string $type, string $size, string $id, QrCodeFactoryInterface $codeFactory, string $kernelDir)
    {
        $path = [
            $kernelDir,
            'public',
            'images',
            $size . '-' . $id .'-'. $type . '.png',
        ];

        if (!file_exists(implode(DIRECTORY_SEPARATOR, $path))) {

            $url = $this->generateUrl(
                'character-blip-' . $type,
                ['id' => $id],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $qr = $codeFactory->create($url, [
                'size' => $size,
                'writer' => 'png',
                'error_correction_level' => ErrorCorrectionLevel::MEDIUM,
                'label' => null,
                'margin' => 0
            ]);

            $content = $qr->writeString();

            file_put_contents(implode(DIRECTORY_SEPARATOR, $path), $content);
        } else {
            $content = file_get_contents(implode(DIRECTORY_SEPARATOR, $path));
        }

        $response = new Response($content);
        $response->headers->add([
            'Accept-Ranges' => 'bytes',
            'Connection' => 'Keep-Alive',
            'Content-Length' => strlen($content),
            'Content-Type' => 'application/png',
            'Keep-Alive' => 'timeout=5, max=100',
            'Server' => 'Apache'
        ]);

        return $response;
    }

    /**
     * @Route("/characters/blip/simple/{id}", name="character-blip-simple")
     *
     * @param Character $character
     *
     * @return Response
     */
    public function blipSimple(Character $character)
    {
        return $this->render('character/character-blip.html.twig', [
            'type' => 'semplice',
            'attackerHasWon' => true,
            'character' => $character
        ]);
    }

    /**
     * @Route("/characters/blip/complex/{id}", name="character-blip-complex")
     *
     * @param Character $character
     *
     * @return Response
     */
    public function blipComplex(Character $character)
    {
        /** @var Character $userCharacter */
        $userCharacter = $this->getUser()->getCharacters()->current();
        if (null === $userCharacter) {
            throw $this->createAccessDeniedException();
        }

        list($userCharacterDefense, $userCharacterAttack, $userCharacterHasAuspex) = $this->getUsefulStats($userCharacter);
        list($characterDefense, $characterAttack, $characterHasAuspex) = $this->getUsefulStats($character);

        $dice1 = rand(0, 10);
        $dice2 = rand(0, 10);

        $attackerHasWon = false;
        $userThrow = $userCharacterAttack + $dice1;
        $defenderThrow = $characterDefense + $dice2;
        
        if ($userThrow > $defenderThrow) {
            $attackerHasWon = true;
        }

        return $this->render('character/character-blip.html.twig', [
            'type' => 'complesso',
            'attackerHasWon' => $attackerHasWon,
            'userCharacterHasAuspex' => $userCharacterHasAuspex,
            'character' => $character
        ]);
    }

    private function getUsefulStats(Character $character)
    {
        $userCharacterDefense = 0;
        $userCharacterAttack = 0;
        $userCharacterHasAuspex = false;

        $stats = $character->getStats();
        foreach ($stats as $stat) {
            if ($stat->getStat()->getLabel() === '0 - Difesa Potere') {
                $userCharacterDefense = $stat->getLevel();
            }
            if ($stat->getStat()->getLabel() === '0 - Attacco Potere') {
                $userCharacterDefense = $stat->getLevel();
            }
            if ($stat->getStat()->getLabel() === '1 - Auspex (Disciplina)') {
                $userCharacterHasAuspex = true;
            }
        }

        return [$userCharacterDefense, $userCharacterAttack, $userCharacterHasAuspex];
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}
