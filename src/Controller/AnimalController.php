<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\Caretaker;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use App\Repository\CaretakerRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
        $animals = $animalRepository->findAll();
        $slugger = new AsciiSlugger();
        $requireFlush = false;
        foreach ($animals as $animal) {
            if (empty($animal->getSlug())) {
                $animal->setSlug(
                    $slugger->slug($animal->getName())->folded()
                );
                $requireFlush = true;
            }
        }

        if ($requireFlush) {
            $this->getDoctrine()->getManager()->flush();
        }


        return $this->render('animal/index.html.twig', [
            'animals' => $animals,
        ]);
    }

    /**
     * @Route("/manage", name="animal_manage", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function manage(AnimalRepository $animalRepository): Response
    {
        return $this->render('animal/manage.html.twig', [
            'animals' => $animalRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="animal_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugger = new AsciiSlugger();
            $animal->setSlug($slugger->slug($animal->getSlug())->folded());
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
     * @Route("/api", name="animal_index_api", methods={"GET"})
     *
     * @deprecated This method is currently used only for dev purposes
     */
    public function apiIndex(AnimalRepository $animalRepository): Response
    {
        $serializer = SerializerBuilder::create()->build();
        $response = new Response(
            $serializer->serialize(
                $animalRepository->findAll(),
                'json',
                SerializationContext::create()->setGroups(['animal_list'])
            ));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/test", name="animal_test", methods={"GET","POST"})
     *
     * @deprecated This method is currently used only for dev purposes
     */
    public function test(AnimalRepository $animalRepository): Response
    {
        $response = new Response(null);

        $response->setContent($this->render('animal/index.html.twig', [
            'animals' => $animalRepository->findAll(),
        ])->getContent());//            ->setExpires($date)
        ;
        return $response;
    }

    /**
     * @Route("/{slug}/api", name="animal_show_api", methods={"GET"})
     */
    public function apiShow(Animal $animal): Response
    {

        $serializer = SerializerBuilder::create()->build();
        $response = new Response(
            $serializer->serialize(
                $animal,
                'json',
                SerializationContext::create()
                    ->setGroups(['animal_list', 'caretaker_list'])
            ));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/{slug}", name="animal_show", methods={"GET"})
     */
    public function show(Animal $animal): Response
    {
        return $this->render('animal/show.html.twig', [
            'animal' => $animal,
        ]);
    }


    /**
     * @Route("/{slug}/edit", name="animal_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, Animal $animal): Response
    {
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugger = new AsciiSlugger();
            $animal->setSlug($slugger->slug($animal->getSlug())->folded());
            $objectManager = $this->getDoctrine()->getManager();

            $objectManager->flush();

            return $this->redirectToRoute('animal_index');
        }

        return $this->render('animal/edit.html.twig', [
            'animal' => $animal,
            'form'   => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="animal_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
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
