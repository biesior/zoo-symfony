<?php

namespace App\Controller;

use App\Entity\Caretaker;
use App\Form\CaretakerType;
use App\Repository\CaretakerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        return $this->render('caretaker/index.html.twig', [
            'caretakers' => $caretakerRepository->findBy([], ['name' => 'ASC']),
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
     * @Route("/{id}", name="caretaker_show", methods={"GET"})
     */
    public function show(Caretaker $caretaker): Response
    {
        return $this->render('caretaker/show.html.twig', [
            'caretaker' => $caretaker,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="caretaker_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Caretaker $caretaker): Response
    {
        $form = $this->createForm(CaretakerType::class, $caretaker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('caretaker_index');
        }

        return $this->render('caretaker/edit.html.twig', [
            'caretaker' => $caretaker,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="caretaker_delete", methods={"POST"})
     */
    public function delete(Request $request, Caretaker $caretaker): Response
    {
        if ($this->isCsrfTokenValid('delete' . $caretaker->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($caretaker);
            $entityManager->flush();
        }

        return $this->redirectToRoute('caretaker_index');
    }
}
