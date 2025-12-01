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
        /**
         * QUESTIONNAIRE 1
         * "Vous orienter dans la médiathèque"
         */
        $orientation = new Questionnaire();
        $orientation->setTitle('Vous orienter dans la médiathèque');
        $orientation->setDescription("Aide le visiteur à trouver le bon espace ou service.");
        $manager->persist($orientation);

        // Q1 (racine)
        $q1 = new Question();
        $q1->setTitle("Quel est votre besoin principal aujourd'hui ?");
        $q1->setDescription("Sélectionnez l'option qui correspond le mieux à votre besoin.");
        $orientation->addQuestion($q1);
        $manager->persist($q1);

        // Q2 – Emprunter / rendre
        $q2 = new Question();
        $q2->setTitle('Que souhaitez-vous faire ?');
        $q2->setDescription('Précisez si vous venez emprunter ou rendre des documents.');
        $orientation->addQuestion($q2);
        $manager->persist($q2);

        // Q3 – Travailler / étudier
        $q3 = new Question();
        $q3->setTitle('Vous cherchez plutôt…');
        $q3->setDescription('Type d’espace de travail souhaité.');
        $orientation->addQuestion($q3);
        $manager->persist($q3);

        // Q4 – Ordinateur / Wi-Fi
        $q4 = new Question();
        $q4->setTitle('De quoi avez-vous besoin ?');
        $q4->setDescription('Choisissez le service numérique souhaité.');
        $orientation->addQuestion($q4);
        $manager->persist($q4);

        // Q5 – Conseils personnalisés
        $q5 = new Question();
        $q5->setTitle('Sur quoi souhaitez-vous être conseillé ?');
        $q5->setDescription('Sélectionnez le type de support pour lequel vous voulez un conseil.');
        $orientation->addQuestion($q5);
        $manager->persist($q5);

        // Q6 – Activités / animations
        $q6 = new Question();
        $q6->setTitle("Quel type d'activité vous intéresse ?");
        $q6->setDescription('Choisissez le type d’animation que vous recherchez.');
        $orientation->addQuestion($q6);
        $manager->persist($q6);

        // Q7 – Type de documents à emprunter
        $q7 = new Question();
        $q7->setTitle('Quel type de documents souhaitez-vous emprunter ?');
        $q7->setDescription('Précisez le type de documents que vous cherchez.');
        $orientation->addQuestion($q7);
        $manager->persist($q7);

        // Q8 – Carte médiathèque pour le Wi-Fi
        $q8 = new Question();
        $q8->setTitle('Avez-vous déjà une carte de la médiathèque ?');
        $q8->setDescription('La connexion Wi-Fi peut dépendre de votre statut.');
        $orientation->addQuestion($q8);
        $manager->persist($q8);

        /**
         * Choices Q1 (racine)
         */
        $c11 = new Choice();
        $c11->setContent('Emprunter ou rendre des documents');
        $q1->addChoice($c11);
        $c11->setNextQuestion($q2); // va vers Q2
        $manager->persist($c11);

        $c12 = new Choice();
        $c12->setContent('Travailler ou étudier sur place');
        $q1->addChoice($c12);
        $c12->setNextQuestion($q3); // va vers Q3
        $manager->persist($c12);

        $c13 = new Choice();
        $c13->setContent('Utiliser un ordinateur ou le Wi-Fi');
        $q1->addChoice($c13);
        $c13->setNextQuestion($q4); // va vers Q4
        $manager->persist($c13);

        $c14 = new Choice();
        $c14->setContent('Demander des conseils de lecture / musique / films');
        $q1->addChoice($c14);
        $c14->setNextQuestion($q5); // va vers Q5
        $manager->persist($c14);

        $c15 = new Choice();
        $c15->setContent('Découvrir les activités et animations');
        $q1->addChoice($c15);
        $c15->setNextQuestion($q6); // va vers Q6
        $manager->persist($c15);

        /**
         * Choices Q2 – Emprunter / rendre
         */
        $c21 = new Choice();
        $c21->setContent('Emprunter des documents');
        $q2->addChoice($c21);
        $c21->setNextQuestion($q7); // détail du type de documents
        $manager->persist($c21);

        $c22 = new Choice();
        $c22->setContent('Rendre des documents');
        $q2->addChoice($c22);
        $c22->setNextQuestion(null); // fin de parcours (retour vers automates / comptoir)
        $manager->persist($c22);

        /**
         * Choices Q3 – Travailler / étudier
         */
        $c31 = new Choice();
        $c31->setContent('Un espace calme pour travailler seul');
        $q3->addChoice($c31);
        $c31->setNextQuestion(null);
        $manager->persist($c31);

        $c32 = new Choice();
        $c32->setContent('Une table pour travailler à plusieurs');
        $q3->addChoice($c32);
        $c32->setNextQuestion(null);
        $manager->persist($c32);

        $c33 = new Choice();
        $c33->setContent('Un espace adapté aux enfants / ados');
        $q3->addChoice($c33);
        $c33->setNextQuestion(null);
        $manager->persist($c33);

        /**
         * Choices Q4 – Ordinateur / Wi-Fi
         */
        $c41 = new Choice();
        $c41->setContent('Un ordinateur avec Internet');
        $q4->addChoice($c41);
        $c41->setNextQuestion(null);
        $manager->persist($c41);

        $c42 = new Choice();
        $c42->setContent('Le Wi-Fi sur mon appareil');
        $q4->addChoice($c42);
        $c42->setNextQuestion($q8); // question sur la carte
        $manager->persist($c42);

        /**
         * Choices Q5 – Conseils personnalisés
         */
        $c51 = new Choice();
        $c51->setContent('Romans / littérature');
        $q5->addChoice($c51);
        $c51->setNextQuestion(null);
        $manager->persist($c51);

        $c52 = new Choice();
        $c52->setContent('BD / mangas');
        $q5->addChoice($c52);
        $c52->setNextQuestion(null);
        $manager->persist($c52);

        $c53 = new Choice();
        $c53->setContent('Films / séries');
        $q5->addChoice($c53);
        $c53->setNextQuestion(null);
        $manager->persist($c53);

        $c54 = new Choice();
        $c54->setContent('Musique');
        $q5->addChoice($c54);
        $c54->setNextQuestion(null);
        $manager->persist($c54);

        /**
         * Choices Q6 – Activités / animations
         */
        $c61 = new Choice();
        $c61->setContent('Ateliers numériques');
        $q6->addChoice($c61);
        $c61->setNextQuestion(null);
        $manager->persist($c61);

        $c62 = new Choice();
        $c62->setContent('Lectures / clubs de lecture');
        $q6->addChoice($c62);
        $c62->setNextQuestion(null);
        $manager->persist($c62);

        $c63 = new Choice();
        $c63->setContent('Expositions');
        $q6->addChoice($c63);
        $c63->setNextQuestion(null);
        $manager->persist($c63);

        $c64 = new Choice();
        $c64->setContent('Animations pour les enfants');
        $q6->addChoice($c64);
        $c64->setNextQuestion(null);
        $manager->persist($c64);

        /**
         * Choices Q7 – Type de documents à emprunter
         */
        $c71 = new Choice();
        $c71->setContent('Livres adultes');
        $q7->addChoice($c71);
        $c71->setNextQuestion(null);
        $manager->persist($c71);

        $c72 = new Choice();
        $c72->setContent('Livres jeunesse');
        $q7->addChoice($c72);
        $c72->setNextQuestion(null);
        $manager->persist($c72);

        $c73 = new Choice();
        $c73->setContent('BD / mangas');
        $q7->addChoice($c73);
        $c73->setNextQuestion(null);
        $manager->persist($c73);

        $c74 = new Choice();
        $c74->setContent('Films / séries / DVD');
        $q7->addChoice($c74);
        $c74->setNextQuestion(null);
        $manager->persist($c74);

        /**
         * Choices Q8 – Carte médiathèque
         */
        $c81 = new Choice();
        $c81->setContent('Oui, je suis déjà inscrit(e)');
        $q8->addChoice($c81);
        $c81->setNextQuestion(null);
        $manager->persist($c81);

        $c82 = new Choice();
        $c82->setContent("Non, je n'ai pas encore de carte");
        $q8->addChoice($c82);
        $c82->setNextQuestion(null);
        $manager->persist($c82);

        // Root question du questionnaire 1
        $orientation->setRootQuestion($q1);

        /**
         * QUESTIONNAIRE 2
         * "Retour sur votre atelier à la médiathèque"
         */
        $feedback = new Questionnaire();
        $feedback->setTitle('Retour sur votre atelier à la médiathèque');
        $feedback->setDescription('Questionnaire de satisfaction à propos des ateliers.');
        $manager->persist($feedback);

        // Q1b – Atelier concerné
        $q1b = new Question();
        $q1b->setTitle('À quel atelier avez-vous participé ?');
        $q1b->setDescription('Sélectionnez l’atelier concerné par votre retour.');
        $feedback->addQuestion($q1b);
        $manager->persist($q1b);

        // Q2b – Satisfaction globale
        $q2b = new Question();
        $q2b->setTitle('Globalement, comment évalueriez-vous cet atelier ?');
        $q2b->setDescription('Indiquez votre niveau de satisfaction.');
        $feedback->addQuestion($q2b);
        $manager->persist($q2b);

        // Q3A – Points positifs (si satisfait)
        $q3a = new Question();
        $q3a->setTitle("Qu'avez-vous le plus apprécié ?");
        $q3a->setDescription('Choisissez l’aspect que vous avez le plus aimé.');
        $feedback->addQuestion($q3a);
        $manager->persist($q3a);

        // Q3B – Points négatifs (si mitigé/pas satisfait)
        $q3b = new Question();
        $q3b->setTitle('Quelle est la principale raison de votre insatisfaction ?');
        $q3b->setDescription('Précisez ce qui vous a le plus gêné.');
        $feedback->addQuestion($q3b);
        $manager->persist($q3b);

        // Q4b – Recommandation
        $q4b = new Question();
        $q4b->setTitle('Recommanderiez-vous cet atelier à quelqu’un de votre entourage ?');
        $q4b->setDescription('Votre réponse nous aide à mesurer l’intérêt de cet atelier.');
        $feedback->addQuestion($q4b);
        $manager->persist($q4b);

        // Q5b – Fréquence / envies
        $q5b = new Question();
        $q5b->setTitle('Souhaiteriez-vous que ce type d’atelier soit proposé…');
        $q5b->setDescription('Indiquez si vous aimeriez retrouver ce type de contenu.');
        $feedback->addQuestion($q5b);
        $manager->persist($q5b);

        /**
         * Choices Q1b – Atelier concerné
         */
        $c1b1 = new Choice();
        $c1b1->setContent('Atelier numérique (initiation)');
        $q1b->addChoice($c1b1);
        $c1b1->setNextQuestion($q2b);
        $manager->persist($c1b1);

        $c1b2 = new Choice();
        $c1b2->setContent('Atelier jeux vidéo');
        $q1b->addChoice($c1b2);
        $c1b2->setNextQuestion($q2b);
        $manager->persist($c1b2);

        $c1b3 = new Choice();
        $c1b3->setContent('Atelier lecture / club');
        $q1b->addChoice($c1b3);
        $c1b3->setNextQuestion($q2b);
        $manager->persist($c1b3);

        $c1b4 = new Choice();
        $c1b4->setContent('Autre');
        $q1b->addChoice($c1b4);
        $c1b4->setNextQuestion($q2b);
        $manager->persist($c1b4);

        /**
         * Choices Q2b – Satisfaction globale
         */
        $c2b1 = new Choice();
        $c2b1->setContent('Très satisfait(e)');
        $q2b->addChoice($c2b1);
        $c2b1->setNextQuestion($q3a);
        $manager->persist($c2b1);

        $c2b2 = new Choice();
        $c2b2->setContent('Satisfait(e)');
        $q2b->addChoice($c2b2);
        $c2b2->setNextQuestion($q3a);
        $manager->persist($c2b2);

        $c2b3 = new Choice();
        $c2b3->setContent('Mitigé(e)');
        $q2b->addChoice($c2b3);
        $c2b3->setNextQuestion($q3b);
        $manager->persist($c2b3);

        $c2b4 = new Choice();
        $c2b4->setContent('Pas satisfait(e)');
        $q2b->addChoice($c2b4);
        $c2b4->setNextQuestion($q3b);
        $manager->persist($c2b4);

        /**
         * Choices Q3a – Points positifs
         */
        $c3a1 = new Choice();
        $c3a1->setContent("Les explications de l'animateur");
        $q3a->addChoice($c3a1);
        $c3a1->setNextQuestion($q4b);
        $manager->persist($c3a1);

        $c3a2 = new Choice();
        $c3a2->setContent("Le contenu / le thème de l'atelier");
        $q3a->addChoice($c3a2);
        $c3a2->setNextQuestion($q4b);
        $manager->persist($c3a2);

        $c3a3 = new Choice();
        $c3a3->setContent("L'ambiance du groupe");
        $q3a->addChoice($c3a3);
        $c3a3->setNextQuestion($q4b);
        $manager->persist($c3a3);

        $c3a4 = new Choice();
        $c3a4->setContent('Le matériel mis à disposition');
        $q3a->addChoice($c3a4);
        $c3a4->setNextQuestion($q4b);
        $manager->persist($c3a4);

        $c3a5 = new Choice();
        $c3a5->setContent('Autre');
        $q3a->addChoice($c3a5);
        $c3a5->setNextQuestion($q4b);
        $manager->persist($c3a5);

        /**
         * Choices Q3b – Points négatifs
         */
        $c3b1 = new Choice();
        $c3b1->setContent('Le niveau ne me convenait pas');
        $q3b->addChoice($c3b1);
        $c3b1->setNextQuestion($q4b);
        $manager->persist($c3b1);

        $c3b2 = new Choice();
        $c3b2->setContent('Le rythme était trop rapide / trop lent');
        $q3b->addChoice($c3b2);
        $c3b2->setNextQuestion($q4b);
        $manager->persist($c3b2);

        $c3b3 = new Choice();
        $c3b3->setContent("Le contenu ne correspondait pas à mes attentes");
        $q3b->addChoice($c3b3);
        $c3b3->setNextQuestion($q4b);
        $manager->persist($c3b3);

        $c3b4 = new Choice();
        $c3b4->setContent("Problème de matériel / d'organisation");
        $q3b->addChoice($c3b4);
        $c3b4->setNextQuestion($q4b);
        $manager->persist($c3b4);

        $c3b5 = new Choice();
        $c3b5->setContent('Autre');
        $q3b->addChoice($c3b5);
        $c3b5->setNextQuestion($q4b);
        $manager->persist($c3b5);

        /**
         * Choices Q4b – Recommandation
         */
        $c4b1 = new Choice();
        $c4b1->setContent('Oui, sans hésiter');
        $q4b->addChoice($c4b1);
        $c4b1->setNextQuestion($q5b);
        $manager->persist($c4b1);

        $c4b2 = new Choice();
        $c4b2->setContent('Oui, plutôt');
        $q4b->addChoice($c4b2);
        $c4b2->setNextQuestion($q5b);
        $manager->persist($c4b2);

        $c4b3 = new Choice();
        $c4b3->setContent('Pas vraiment');
        $q4b->addChoice($c4b3);
        $c4b3->setNextQuestion($q5b);
        $manager->persist($c4b3);

        $c4b4 = new Choice();
        $c4b4->setContent('Non');
        $q4b->addChoice($c4b4);
        $c4b4->setNextQuestion($q5b);
        $manager->persist($c4b4);

        /**
         * Choices Q5b – Fréquence / envies
         */
        $c5b1 = new Choice();
        $c5b1->setContent('Plus souvent');
        $q5b->addChoice($c5b1);
        $c5b1->setNextQuestion(null);
        $manager->persist($c5b1);

        $c5b2 = new Choice();
        $c5b2->setContent('À la même fréquence');
        $q5b->addChoice($c5b2);
        $c5b2->setNextQuestion(null);
        $manager->persist($c5b2);

        $c5b3 = new Choice();
        $c5b3->setContent('Moins souvent');
        $q5b->addChoice($c5b3);
        $c5b3->setNextQuestion(null);
        $manager->persist($c5b3);

        $c5b4 = new Choice();
        $c5b4->setContent("Je ne suis pas intéressé(e) par ce type d'atelier à l'avenir");
        $q5b->addChoice($c5b4);
        $c5b4->setNextQuestion(null);
        $manager->persist($c5b4);

        // Root question du questionnaire 2
        $feedback->setRootQuestion($q1b);

        $manager->flush();
    }
}
