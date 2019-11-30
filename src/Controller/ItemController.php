<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\CharacterStat;
use App\Entity\Clue;
use App\Entity\Elysium;
use App\Entity\Equipment;
use App\Entity\Item;
use App\Entity\Requirement;
use App\Form\ItemCreate;
use App\Form\ItemElysiumAssign;
use App\Form\RequirementType;
use App\Repository\ItemRepository;
use Dompdf\Dompdf;
use Endroid\QrCode\Factory\QrCodeFactoryInterface;
use Endroid\QrCode\Response\QrCodeResponse;
use Endroid\QrCode\ErrorCorrectionLevel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ItemController extends AbstractController
{
    /**
     * @Route("/item", name="item-index")
     *
     * @return Response
     */
    public function index(ItemRepository $itemRepository)
    {
        $items = $itemRepository->findAll();
        return $this->render(
            'item/index.html.twig',
            [
                'items' => $items
            ]
        );
    }

    /**
     * @Route("/item/create/{iid}", name="item-create", defaults={"iid"=null})
     * @ParamConverter("item", options={"id" = "iid"})
     *
     * @param Request $request
     * @param Item|null $item
     *
     * @return Response
     */
    public function create(Request $request, Item $item = null)
    {
        $isNew = false;
        if (null === $item) {
            $isNew = true;
            $item = new Item();
            $equipment = new Equipment();
            $equipment->setQuantity(1);
            $item->setEquipment($equipment);
        }
        $form = $this->createForm(ItemCreate::class, $item);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($isNew) {
                $this->getDoctrine()->getManager()->persist($item);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('item-index');
        }

        return $this->render(
            'item/create.html.twig', [
            'form' => $form->createView(),
            'item' => $item
        ]);
    }

    /**
     * @Route("/item/delete/{iid}", name="item-delete")
     * @ParamConverter("item", options={"id" = "iid"})
     *
     * @param Item $item
     * @param Request $request
     * @param string $kernelDir
     *
     * @return Response
     */
    public function delete(Item $item, Request $request, string $kernelDir)
    {
        $path = [
            $kernelDir,
            'public',
            'images',
            150 . '-' . $item->getHash() . '.png',
        ];
        if (file_exists(implode(DIRECTORY_SEPARATOR, $path))) {
            unlink(implode(DIRECTORY_SEPARATOR, $path));
        }

        $equipment = $item->getEquipment();
        $item->setEquipment(null);
        $this->getDoctrine()->getManager()->flush();

        $this->getDoctrine()->getManager()->remove($item);
        $this->getDoctrine()->getManager()->flush();
        if ($request->query->get('a', false)) {
            $this->getDoctrine()->getManager()->remove($equipment);
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->redirectToRoute('item-index');
    }

    /**
     * @Route("/item/{iid}/clue/{rid}", name="item-clue-view", requirements={"rid"="\d+"})
     * @ParamConverter("requirement", options={"id" = "rid"})
     *
     * @param Requirement $requirement
     *
     * @return Response
     */
    public function viewClue(Requirement $requirement)
    {
        return $this->render(
            'item/view-clue.html.twig', [
            'requirement' => $requirement,
        ]);
    }

    /**
     * @Route("/item/{iid}/clue/create/{rid}", name="item-clue-create", defaults={"rid"=null})
     * @ParamConverter("item", options={"id" = "iid"})
     * @ParamConverter("requirement", options={"id" = "rid"})
     *
     * @param Item $item
     * @param Request $request
     * @param Requirement $requirement
     *
     * @return Response
     */
    public function createClue(Item $item, Request $request,  Requirement $requirement = null)
    {
        $isNew = false;
        if (null === $requirement) {
            $isNew = true;
            $requirement = new Requirement();
            $requirement->setItem($item);
        }
        $form = $this->createForm(RequirementType::class, $requirement);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($isNew) {
                $this->getDoctrine()->getManager()->persist($requirement);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('item-index');
        }

        return $this->render(
            'item/add-clue.html.twig', [
            'form' => $form->createView(),
            'item' => $item,
            'requirement' => $requirement
        ]);
    }

    /**
     * @Route("/item/clue/delete/{cid}", name="item-clue-delete")
     * @ParamConverter("clue", options={"id" = "cid"})
     *
     * @param Clue $clue
     *
     * @return Response
     */
    public function deleteClue(Clue $clue)
    {
        $this->getDoctrine()->getManager()->remove($clue);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('item-index');
    }

    /**
     * @Route("/item/associate/{iid}", name="item-associate")
     * @ParamConverter("item", options={"id" = "iid"})
     *
     * @param Item $item
     * @param Request $request
     *
     * @return Response
     */
    public function associate(Item $item, Request $request)
    {
        $form = $this->createForm(ItemElysiumAssign::class, $item);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('item-index');
        }

        return $this->render('item/assign-elysium.html.twig', [
            'form' => $form->createView(),
            'item' => $item
        ]);
    }

    /**
     * @Route("/item/dissociate/{iid}/{eid}", name="item-dissociate")
     * @ParamConverter("item", options={"id" = "iid"})
     * @ParamConverter("elysium", options={"id" = "eid"})
     *
     * @param Item $item
     * @param Elysium $elysium
     *
     * @return Response
     */
    public function dissociate(Item $item, Elysium $elysium)
    {
        $item->removeElysium($elysium);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('item-index');
    }

    /**
     * @Route("/item/view/{code}", name="item-view")
     *
     * @param string $code
     * @param ItemRepository $itemRepository
     *
     * @return Response
     */
    public function view(string $code, ItemRepository $itemRepository)
    {
        /** @var Item $item */
        $item = $itemRepository->findOneByHash($code);

        /** @var Character $character */
        if ($this->isGranted('ROLE_STORY_TELLER')) {
            $passedRequirements = $item->getRequirements()->toArray();
        } else {
            $character = $this->getUser()->getCharacters()->current();
            $characterStats = $character->getStats();
            $passedRequirements = array_filter(
                $item->getRequirements()->toArray(),
                static function (Requirement $requirement) use ($characterStats) {
                    /** @var CharacterStat $characterStat */
                    foreach ($characterStats as $characterStat) {
                        if ($requirement->getStats()->equals($characterStat->getStat())) {
                            return $requirement->getLevel() <= $characterStat->getLevel();
                        }
                    }
                }
            );

            if (count($passedRequirements) > 0) {
                /** @var Requirement $passedRequirement */
                foreach ($passedRequirements as $passedRequirement) {
                    $passedRequirement->addHandledBy($character);
                }
                $this->getDoctrine()->getManager()->flush();
            }
        }

        return $this->render(
            'item/item-view.html.twig', [
                'requirements' => $passedRequirements
        ]);
    }

    /**
     * @Route("/item/qr/{size}-{code}.png", name="item-qr-view")
     *
     * @param string $size
     * @param string $code
     *
     * @return Response
     */
    public function qr(string $size, string $code, QrCodeFactoryInterface $codeFactory, string $kernelDir)
    {
        $path = [
            $kernelDir,
            'public',
            'images',
            $size . '-' . $code . '.png',
        ];

        if (!file_exists(implode(DIRECTORY_SEPARATOR, $path))) {

            $url = $this->generateUrl(
                'item-view',
                ['code' => $code],
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
     * @Route("/item/pdf/{eid}.pdf", name="item-event-pdf")
     * @ParamConverter("elysium", options={"id" = "eid"})
     *
     * @param Elysium $elysium
     *
     * @return Response
     */
    public function pdf(Elysium $elysium)
    {
        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml(
            $this->render('item/pdf.html.twig', ['elysium' => $elysium])
        );
        $dompdf->render();

        $response = new Response($dompdf->output());
        $response->headers->add(['Content-Type' => 'application/pdf']);

        return $response;
    }
}
