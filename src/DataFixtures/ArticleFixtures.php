<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i <= 10 ; $i++) { 
            $article = new Article;
            // on instancie la classe Article qui se trouve dans le dossier App/Entity
            // nous pouvons faire appel au setter pour insérer les données
            $article->setTitle("Titre de l'article n°$i")
            ->setContent("<p>Contenu de l'article n°$i</p>")
            ->setImage("http://picsum.photos/200/300")
            ->setCreatedAt(new \DateTime()); // on instancie la classe DateTime pour récupérer la date d'aujourd'hui
            $manager->persist($article);
            // persist() permet de faire persister l'article dans le temps et préparer son insertion en BDD
        }

        $manager->flush();
        // flush() permet d'exécuter la requête SQL préparée grâce à persist()
    }
}
