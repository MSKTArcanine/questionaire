<?php

namespace App\DataFixtures;

use App\Entity\Choice;
use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UsersFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail("admin@example.com");
        $admin->setPassword('$2y$13$P9f2e5pCDfxcVRNtUCfcsuJ99iU5q2Vd9T7D0LwkOU/0vcJNG5UQy');
        $admin->setRoles(["ROLE_ADMIN"]);

        $manager->persist($admin);

        $user1 = new User();
        $user1->setEmail("usertest@example.com");
        $user1->setPassword('$13$M60yOQspumuIa3tzkV4vreGjRw0TvoQwbnybCWWHagbScj4baOX1i');
        $user1->setRoles(["ROLE_USER"]);

        $manager->persist($user1);

        $manager->flush();
    }
}
