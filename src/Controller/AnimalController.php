<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\Caretaker;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use App\Repository\CaretakerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/animal")
 */
class AnimalController extends AbstractController
{
    /**
     * @Route("/", name="animal_index", methods={"GET"})
     */
    public function index(AnimalRepository $animalRepository): Response
    {
        return $this->render('animal/index.html.twig', [
            'animals' => $animalRepository->findAll(),
        ]);
    }

    /**
     * @Route("/manage", name="animal_manage", methods={"GET"})
     */
    public function manage(AnimalRepository $animalRepository): Response
    {
        return $this->render('animal/manage.html.twig', [
            'animals' => $animalRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="animal_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($animal);
            $entityManager->flush();

            return $this->redirectToRoute('animal_index');
        }

        return $this->render('animal/new.html.twig', [
            'animal' => $animal,
            'form'   => $form->createView(),
        ]);
    }


    /**
     * @Route("/api", name="animal_api", methods={"GET"})
     *
     * @deprecated This method is currently used only for dev purposes
     */
    public function apiIndex(AnimalRepository $animalRepository): Response
    {
        $animals = [];
        foreach ($animalRepository->findAll() as $animal) {
            $sub = [
                'id'          => $animal->getId(),
                'name'        => $animal->getName(),
                'description' => $animal->getDescription(),
            ];
            $careArr = [];
            foreach ($animal->getCaretakers() as $caretaker) {
                $careArr[] = [
                    'id'   => $caretaker->getId(),
                    'name' => $caretaker->getName(),
                ];
            }
            $sub['caretakers'] = $careArr;
            $animals[] = $sub;
        }
        return $this->json(['animals' => $animals]);
    }

    /**
     * @Route("/test", name="animal_test", methods={"GET","POST"})
     *
     * @deprecated This method is currently used only for dev purposes
     */
    public function test(AnimalRepository $animalRepository): Response
    {
//        return $this->json($animalRepository->completeJsonData());
        $response = new Response(null);

        $response->setContent( $this->render('animal/index.html.twig', [
            'animals' => $animalRepository->findAll(),
        ])->getContent());
//            ->setExpires($date)
        ;
        return $response;
    }

    /**
     * @Route("/{id}", name="animal_show", methods={"GET"})
     */
    public function show(Animal $animal): Response
    {
        return $this->render('animal/show.html.twig', [
            'animal' => $animal,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="animal_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Animal $animal, CaretakerRepository $caretakerRepository): Response
    {
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objectManager = $this->getDoctrine()->getManager();

//            $curCaretakers = $animal->getCaretakers();

//            // todo: check if isn't it better way?
//            foreach ($curCaretakers as $ctaker) {
//                $ctaker->addAnimal($animal);
//                $objectManager->persist($ctaker);
//            }
//            if (isset($_POST['animal']['caretakers'])) {
//                foreach ($_POST['animal']['caretakers'] as $caretakerId) {
//                    $animal->addCaretaker($caretakerRepository->find(intval($caretakerId)));
//                }
//            }
//            $objectManager->persist($animal);
            $objectManager->flush();

            return $this->redirectToRoute('animal_index');
        }

        return $this->render('animal/edit.html.twig', [
            'animal' => $animal,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="animal_delete", methods={"POST"})
     */
    public function delete(Request $request, Animal $animal): Response
    {
        if ($this->isCsrfTokenValid('delete' . $animal->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($animal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('animal_index');
    }
}
