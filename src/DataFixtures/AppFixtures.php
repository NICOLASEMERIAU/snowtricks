<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create(locale: 'fr_FR');
        for($i=0; $i<=50; $i++) {
            $trick = new Trick();
            $trick->setName($faker->name);
            $trick->setDescription($faker->text);


            $manager->persist($trick);
        }


        $manager->flush();
    }
}
