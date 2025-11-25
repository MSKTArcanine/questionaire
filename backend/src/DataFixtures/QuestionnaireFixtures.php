<?php

namespace App\DataFixtures;

use App\Entity\Choice;
use App\Entity\Question;
use App\Entity\Questionnaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuestionnaireFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //Questionnaire : ex => Q1: Que cherchez vous ? -> C1: Livres ! -> Q2 : Quel ouvrage ? -> C1 : "test", C2 : "test2"; C2: Vinyl ! -> Q3 : Quel vinyl ? -> C1: "vinyl1", C2: "vinyl2"
        $questionnaire = new Questionnaire();
        $questionnaire->setTitle("Kestuveu ?");
        $questionnaire->setDescription("Oui.");
        $manager->persist($questionnaire);

        $q1 = new Question();
        $q1->setTitle("Que cherchez vous ?");
        $q1->setDescription("kestushersh ?");
        $questionnaire->addQuestion($q1);
        $manager->persist($q1);

        $q2 = new Question();
        $q2->setTitle('KelLivre ?');
        $questionnaire->addQuestion($q2);
        $manager->persist($q2);

        $q3 = new Question();
        $q3->setTitle('KelVinyle');
        $questionnaire->addQuestion($q3);
        $manager->persist($q3);

        // 3) Créer les choices + embranchements

        // Q1 -> Q2
        $c11 = new Choice();
        $c11->setContent('Livres !');
        $q1->addChoice($c11);
        $c11->setNextQuestion($q2);
        $manager->persist($c11);

        // Q1 -> Q3
        $c12 = new Choice();
        $c12->setContent('Vinyles !');
        $q1->addChoice($c12);
        $c12->setNextQuestion($q3);
        $manager->persist($c12);

        // Q2
        $c21 = new Choice();
        $c21->setContent('test1');
        $q2->addChoice($c21);
        $manager->persist($c21);

        $c22 = new Choice();
        $c22->setContent('test2');
        $q2->addChoice($c22);
        $manager->persist($c22);
        
        // Q3
        $c31 = new Choice();
        $c31->setContent('Vinyle1');
        $q3->addChoice($c31);
        $manager->persist($c31);

        $c32 = new Choice();
        $c32->setContent('Vinyle2');
        $q3->addChoice($c32);
        $manager->persist($c32);

        // 4) Définir la rootQuestion du questionnaire
        $questionnaire->setRootQuestion($q1);

        $manager->flush();
    }
}
