<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Stagiaire;
use App\Form\StagiaireType;
use App\Repository\StagiaireRepository;
use Doctrine\ORM\EntityManagerInterface;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(StagiaireRepository $repository): Response
    {
        try {
            // Fetch all stagiaires from the repository
            $stagiaires = $repository->findAll();

            // Dump the fetched data for debugging
            var_dump($stagiaires);

            // Render the Twig template with the fetched stagiaires
            return $this->render('all/index.html.twig', [
                'stagiaires' => $stagiaires,
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the process
            // Log or display the error message as needed
            return new Response('An error occurred: ' . $e->getMessage());
        }
    }

    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, StagiaireRepository $repository): Response
    {
        try {
            // Fetch all stagiaires from the repository
            $stagiaires = $repository->findAll();

            $stagiaire = new Stagiaire();

            $form = $this->createForm(StagiaireType::class, $stagiaire);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($stagiaire);
                $entityManager->flush();

                // Redirect to another page or return a response
                return $this->redirectToRoute('app_dashboard'); // Change this to the desired route
            }

            return $this->render('dashboard/index.html.twig', [
                'stagiaires' => $stagiaires, // Pass the variable to the template
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the process
            // Log or display the error message as needed
            return new Response('An error occurred: ' . $e->getMessage());
        }
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(StagiaireRepository $repository, int $id, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $stagiaire = $repository->findOneBy(["id" => $id]);
    
            $form = $this->createForm(StagiaireType::class, $stagiaire);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $manager->persist($stagiaire);
                $manager->flush();
    
                // Redirect to another page or return a response
                return $this->redirectToRoute('app_dashboard'); // Change this to the desired route
            }
    
            return $this->render('edit/index.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the process
            // Log or display the error message as needed
            return new Response('An error occurred: ' . $e->getMessage());
        }
    }
    
    
    #[Route('/suppression/{id}', name: 'delete', methods: ['GET'])]
    public function delete(StagiaireRepository $repository, $id, Request $request, EntityManagerInterface $manager): Response
    {
        $stagiaire = $repository->find($id);
    
        if (!$stagiaire) {
            // Handle the case when the entity with the given ID is not found, e.g., throw an exception or return a response.
        }
    
        $manager->remove($stagiaire);
        $manager->flush();
    
        return $this->redirectToRoute('app_dashboard');

    }
    
    
    
}
