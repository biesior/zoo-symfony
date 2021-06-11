<?php

namespace App\Controller;

use App\Entity\Caretaker;
use App\Form\CaretakerType;
use App\Repository\CaretakerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @Route("/caretaker")
 */
class CaretakerController extends AbstractController
{
    /**
     * @Route("/", name="caretaker_index", methods={"GET"})
     */
    public function index(CaretakerRepository $caretakerRepository): Response
    {
        $slugger = new AsciiSlugger();
        $requireFlush = false;
        $caretakers = $caretakerRepository->findBy([], ['name' => 'ASC']);

        foreach ($caretakers as $caretaker) {
            if (empty($caretaker->getSlug())) {
                $caretaker->setSlug(
                    $slugger->slug($caretaker->getName())->folded()
                );
                $requireFlush = true;
            }
        }

        if ($requireFlush) {
            $this->getDoctrine()->getManager()->flush();
        }

        return $this->render('caretaker/index.html.twig', [
            'caretakers' => $caretakers,
        ]);
    }

    /**
     * @Route("/manage", name="caretaker_manage", methods={"GET"})
     */
    public function manage(CaretakerRepository $caretakerRepository): Response
    {
        return $this->render('caretaker/manage.html.twig', [
            'caretakers' => $caretakerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="caretaker_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $caretaker = new Caretaker();
        $form = $this->createForm(CaretakerType::class, $caretaker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugger = new AsciiSlugger();
            $caretaker->setSlug($slugger->slug($caretaker->getName())->folded());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($caretaker);
            $entityManager->flush();

            return $this->redirectToRoute('caretaker_index');
        }

        return $this->render('caretaker/new.html.twig', [
            'caretaker' => $caretaker,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="caretaker_show", methods={"GET"})
     */
    public function show(string $slug, CaretakerRepository $caretakerRepository): Response
    {
        $caretaker = $caretakerRepository->findOneBy(['slug' => $slug]);
        return $this->render('caretaker/show.html.twig', [
            'caretaker' => $caretaker,
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="caretaker_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, string $slug, CaretakerRepository $caretakerRepository): Response
    {
        $caretaker = $caretakerRepository->findOneBy(['slug' => $slug]);
        $form = $this->createForm(CaretakerType::class, $caretaker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugger = new AsciiSlugger();
            $caretaker->setSlug($slugger->slug($caretaker->getName())->folded());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('caretaker_index');
        }

        return $this->render('caretaker/edit.html.twig', [
            'caretaker' => $caretaker,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="caretaker_delete", methods={"POST"})
     */
    public function delete(Request $request, string $slug, CaretakerRepository $caretakerRepository): Response
    {
        $caretaker = $caretakerRepository->findOneBy(['slug' => $slug]);
        if ($this->isCsrfTokenValid('delete' . $caretaker->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($caretaker);
            $entityManager->flush();
        }

        return $this->redirectToRoute('caretaker_index');
    }
}
