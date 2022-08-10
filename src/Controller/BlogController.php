<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Func;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    // une route est définie par 2 arguments : son chemin (/blog) et son nom (app_blog)
    // à chaque méthode d'un controller nous devons associer une route
    // ici, lorqu'on va sur /blog sur le navigateur, on lance la méthode index()
    public function index(ArticleRepository $repo): Response
    {
        // pour récupérer le repository, je le passe en paramètre de la méthode index()
        // cela s'appelle une injection de dépendance
        $articles = $repo->findAll();
        // j'utilise la méthode findAll() pour récupérer tous les articles en BDD
        return $this->render('blog/index.html.twig', [
            // 'controller_name' => 'BlogController',
            'tabArticles' => $articles // j'envoie le tableau d'articles au template
        ]);
        // toutes les méthodes d'un controller renvoient un objet de type Response
        // render() permet d'afficher le contenu d'un template
    }
    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog',
            'age' => 28
        ]);
    }
    // le premier arg de render() est le fichier à afficher
    // le 2ème arg est un tableau associatif 
    /**
     * @Route("/blog/show/{id}", name="blog_show") // {id} est un paramètre de route, ce sera l'id d'un article
     */
    public function show($id, ArticleRepository $repo)
    {
        $article = $repo->find($id);
        // je passe à find() l'id dans ma route pour récupérer l'article correspondant en BDD
        return $this->render('blog/show.html.twig', ['article' => $article]);
    }
    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/edit/{id}", name="blog_edit")
     */
    public function form(Request $superglobals, EntityManagerInterface $manager, Article $article = null)
    {
        // la classe Request contient les données véhiculées par les superglobales ($_POST, $_GET...)
        // dump($superglobals);
        // si Symfony ne récupère pas d'objet Article, nous en créons un vide
        if (!$article) { // équivalent à if ($article == null)
            $article = new Article; // je crée un objet Article vide prêt à être rempli
            $article->setCreatedAt(new \DateTime()); // ajout de la date seulement à l'insertion d'un article
        }       
        $form = $this->createForm(ArticleType::class, $article); // je lie le formulaire à $article
        // createForm() permet de récupérer un formulaire existant
        $form->handleRequest($superglobals);
        // handleRequest() permet d'insérer le sdonnées du formulaire dans l'objet $article
        // elle permet aussi de faire des vérifications sur le formulaire (quelle est la méthode ? Est-ce que les champs sont tous remplis ? ...)
        // dump($article);
        if ($form->isSubmitted() && $form->isValid()) {
            # code...           
            $manager->persist($article); // prépare la future requête
            $manager->flush(); // exécute la requête (insertion)
            return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
            // cette méthode permet de nous rediriger vers la page de notre article nouvellement créée
        }
        return $this->renderForm("blog/form.html.twig", ['formArticle' => $form, 'editMode' => $article->getId() !== NULL]);
        // si nous sommes sur la route /new, $article n'a pas encore d'id, donc editMode = 0
        // sinon, editMode = 1
        // 2ème manière d'envoyer un formulaire à un template
        // return $this->render("blog/form.html.twig", ['formArticle' => $form->createView()]);
    }
    /**
     * @Route("/blog/delete/{id}", name="blog_delete")
     */
    public function delete(EntityManagerInterface $manager, $id, ArticleRepository $repo)
    {
        $article = $repo->find($id);
        $manager->remove($article);
        // remove() prépare la suppression d'un article
        $manager->flush();
        // exécute la requête préparée (suppression)
        $this->addFlash('success', "L'article a bien été retiré !");
        // addFlash() permet de créer un message de notification
        // le 1er arg est le type de message (ce que l'on veut, pas de type prédéfini)
        // le 2ème arg est le message
        return $this->redirectToRoute("app_blog");
        // redirection vers la liste des articles après la suppression
        // nous afficherons le message flash sur le template affiché sur la route app_blog (index.html.twig)
    }
}
