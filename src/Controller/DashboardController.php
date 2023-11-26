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
use Doctrine\ORM\EntityNotFoundException;
use Knp\Snappy\Pdf;
use Psr\Log\LoggerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;




class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(StagiaireRepository $repository): Response
    {
        try {
            $stagiaires = $repository->findAll();

            var_dump($stagiaires);

            
            return $this->render('all/index.html.twig', [
                'stagiaires' => $stagiaires,
            ]);
        } catch (\Exception $e) {

            return new Response('An error occurred: ' . $e->getMessage());
        }
    }

    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, StagiaireRepository $repository): Response
    {
        try {
            
            $stagiaires = $repository->findAll();

            $stagiaire = new Stagiaire();

            $form = $this->createForm(StagiaireType::class, $stagiaire);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($stagiaire);
                $entityManager->flush();

                
                return $this->redirectToRoute('app_dashboard'); 
            }

            return $this->render('dashboard/index.html.twig', [
                'stagiaires' => $stagiaires, 
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e) {

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
    
                
                return $this->redirectToRoute('app_dashboard'); 
            }
    
            return $this->render('edit/index.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e) {
            
            return new Response('An error occurred: ' . $e->getMessage());
        }
    }
    
    
    #[Route('/suppression/{id}', name: 'delete', methods: ['GET'])]
    public function delete(StagiaireRepository $repository, $id, Request $request, EntityManagerInterface $manager): Response
    {
        $stagiaire = $repository->find($id);
    
        if (!$stagiaire) {
            
        }
    
        $manager->remove($stagiaire);
        $manager->flush();
    
        return $this->redirectToRoute('app_dashboard');

    }
    #[Route('/pdf/{id}', name: 'app_pdf', methods: ['GET'])]
    public function generatePdf(int $id, Pdf $pdf, StagiaireRepository $stagiaireRepository, LoggerInterface $logger): Response
    {
        try {
            $stagiaire = $stagiaireRepository->find($id);
    
            if (!$stagiaire) {
                throw new EntityNotFoundException('Stagiaire not found');
            }
    
            
            $html = $this->renderView('pdf/index.html.twig', array('stagiaire' => $stagiaire));
    
            return new Response(
                $pdf->getOutputFromHtml($html),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="stagiaire-' . $stagiaire->getFilename() . '.pdf"',
                ]
            );
        } catch (EntityNotFoundException $e) {
            
            $logger->error('Stagiaire not found: ' . $e->getMessage());
    
          
            return new Response('Stagiaire not found. Please try again later.');
        } catch (\Exception $e) {
            
            $logger->error('An error occurred while generating PDF: ' . $e->getMessage());
    
            
            return new Response('An error occurred. Please try again later.');
        }
    }
    
    

    
    
    
    
}
