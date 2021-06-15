<?php

namespace App\Controller;

use App\Entity\Cage;
use App\Form\CageType;
use App\Repository\CageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/cage")
 */
class CageController extends AbstractController
{
    /**
     * @Route("/", name="cage_index", methods={"GET"})
     */
    public function index(CageRepository $cageRepository): Response
    {
        $slugger = new AsciiSlugger();
        $requireFlush = false;
        $cages = $cageRepository->findAll();
        foreach ($cages as $cage) {
            if (is_null($cages) || empty($cage->getSlug())) {
                $cage->setSlug(
                    $slugger->slug($cage->getName())->folded()
                );
            }
            $requireFlush = true;
        }

        if ($requireFlush) {
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('cage/index.html.twig', [
            'cages' => $cages,
        ]);
    }

    /**
     * @Route("/manage", name="cage_manage", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function manage(CageRepository $cageRepository): Response
    {
        return $this->render('cage/manage.html.twig', [
            'cages' => $cageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="cage_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $cage = new Cage();
        $form = $this->createForm(CageType::class, $cage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugger = new AsciiSlugger();
            $cage->setSlug($slugger->slug($cage->getSlug())->folded());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cage);
            $entityManager->flush();

            return $this->redirectToRoute('cage_index');
        }

        return $this->render('cage/new.html.twig', [
            'cage' => $cage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="cage_show", methods={"GET"})
     */
    public function show(Cage $cage): Response
    {
        return $this->render('cage/show.html.twig', [
            'cage' => $cage,
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="cage_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Cage $cage): Response
    {
        $form = $this->createForm(CageType::class, $cage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugger = new AsciiSlugger();
            $cage->setSlug($slugger->slug($cage->getSlug())->folded());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cage_index');
        }

        return $this->render('cage/edit.html.twig', [
            'cage' => $cage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="cage_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Cage $cage): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cage->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cage_index');
    }
}
