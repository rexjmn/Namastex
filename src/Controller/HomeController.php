<?php

namespace App\Controller;


use App\Entity\Article;
use App\Form\ArticleForm;
use App\Repository\ArticleRepository;
use App\Service\fileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{

    #[Route('/', name: 'app_home')]
    #[Route('/home', name: 'app_home_legacy')]
    public function index(ArticleRepository $repo): Response
    {
        $articles = $repo->findAll();
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'articles' => $articles,
        ]);
    }
    
    #[Route('/article/{id}', name: 'app_article_show', requirements: ['id' => '\d+'])]
    public function show(Article $article): Response
    {
        return $this->render('home/show.html.twig', [
            'article' => $article,
        ]);
    }
    
    #[Route('/article/new', name: 'app_article_new')]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, fileUploader $fileUploader): Response
    {
          $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Debe iniciar sesión para crear un artículo.');
            return $this->redirectToRoute('app_login');
        }
        $article = new Article();
        $article->setCreatedAt(new \DateTimeImmutable());
         $article->setAuthor($user);

        $form = $this->createForm(ArticleForm::class, $article);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $article->setImageFilename($imageFileName);
            }
           $em->persist($article);
            $em->flush();
            
            $this->addFlash('success', 'Article créé avec succès !');
            return $this->redirectToRoute('app_home');
        }
        
        return $this->render('home/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/article/edit/{id}', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Article $article, Request $request, EntityManagerInterface $em, fileUploader $fileUploader): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez vous connecter pour modifier un article.');
            return $this->redirectToRoute('app_login');
        }
        if ($article->getAuthor() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous devez avoir le droit de modifier cet article.');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ArticleForm::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $imageFileName = $fileUploader->upload($imageFile);
                $article->setImageFilename($imageFileName);
            }
            $em->flush(); 

            $this->addFlash('success', 'Article modifié avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/article/delete/{id}', name: 'app_article_delete')]
    public function delete(Article $article, EntityManagerInterface $em): Response
    {
         $user = $this->getUser();
          if (!$user) {
            $this->addFlash('error', 'Debe iniciar sesión para eliminar un artículo.');
            return $this->redirectToRoute('app_login');
        }
         if ($article->getAuthor() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'No tiene permiso para eliminar este artículo.');
            return $this->redirectToRoute('app_home');
        }
        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'Article supprimé avec succès !');
        return $this->redirectToRoute('app_home');
    }
    
    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }
    #[Route('/admin', name: 'app_admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(ArticleRepository $repo): Response {
        $articles = $repo->findAll();
        return $this->render('home/admin_dashboard.html.twig', [
            'articles' => $articles,
        ]);
    }
    #[Route('/my-articles', name: 'app_my_articles')]
    #[IsGranted('ROLE_USER')]
    public function myArticles(ArticleRepository $repo): Response
    {
        $user = $this->getUser();
        $articles = $repo->findBy(['author' => $user]);
        
        return $this->render('home/my_articles.html.twig', [
            'articles' => $articles,
        ]);
    }
}