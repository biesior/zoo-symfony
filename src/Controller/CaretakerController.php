<?php

namespace App\Controller;

use App\Entity\Caretaker;
use App\Form\CaretakerNewType;
use App\Form\CaretakerType;
use App\Repository\CaretakerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/caretaker")
 */
class CaretakerController extends AbstractController
{


    private UserPasswordEncoderInterface $passwordEncoder;


    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

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
     * @IsGranted("ROLE_ADMIN")
     */
    public function manage(CaretakerRepository $caretakerRepository): Response
    {
        return $this->render('caretaker/manage.html.twig', [
            'caretakers' => $caretakerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="caretaker_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, CaretakerRepository $caretakerRepository): Response
    {
        $caretaker = new Caretaker();
        $form = $this->createForm(CaretakerNewType::class, $caretaker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPass = $form->get('newPassword')->getData();
            if (!empty($newPass)) {
                $caretaker->setPassword($this->passwordEncoder->encodePassword($caretaker, $newPass));
            }

            $slugger = new AsciiSlugger();
            $newSlug = $this->resolveSlug(
                $caretakerRepository,
                $slugger->slug($caretaker->getName())->folded(),
                $slugger);
            $caretaker->setSlug($newSlug);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($caretaker);
            $entityManager->flush();

            return $this->redirectToRoute('caretaker_manage');
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

    private function resolveSlug(CaretakerRepository $caretakerRepository, $proposedSlug, $slugger, $attempt = 0, $id = 0)
    {
        $trySlug = $proposedSlug;
        if ($attempt > 0) {
            $trySlug = $proposedSlug . '-' . $attempt;
        }
        $slugs = $caretakerRepository->countSlugsWithoutId($trySlug, $id);
        $bp = 1;
        if ($slugs > 0) {
            $attempt++;
            return $this->resolveSlug($caretakerRepository, $proposedSlug, $slugger, $attempt, $id);
        } else {
            return $trySlug;
        }

        return $proposedSlug;
    }

    /**
     * @Route("/{slug}/edit", name="caretaker_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(Request $request, string $slug, CaretakerRepository $caretakerRepository): Response
    {
        $caretaker = $caretakerRepository->findOneBy(['slug' => $slug]);
        $form = $this->createForm(CaretakerType::class, $caretaker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPass = $form->get('newPassword')->getData();

            if (!empty($newPass)) {
                $caretaker->setPassword($this->passwordEncoder->encodePassword($caretaker, $newPass));
            }

            $slugger = new AsciiSlugger();
            $newSlug = $slugger->slug($caretaker->getSlug())->folded();
            $finalSlug = $this->resolveSlug($caretakerRepository, $newSlug, $slugger, 0, $caretaker->getId());
            $caretaker->setSlug($finalSlug);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('caretaker_edit', ['slug' => $finalSlug]);
//            return $this->redirectToRoute('caretaker_manage');
        }

        return $this->render('caretaker/edit.html.twig', [
            'caretaker' => $caretaker,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="caretaker_delete", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, string $slug, CaretakerRepository $caretakerRepository): Response
    {
        $caretaker = $caretakerRepository->findOneBy(['slug' => $slug]);
        if ($this->isCsrfTokenValid('delete' . $caretaker->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($caretaker);
            $entityManager->flush();
        }

        return $this->redirectToRoute('caretaker_manage');
    }
}
