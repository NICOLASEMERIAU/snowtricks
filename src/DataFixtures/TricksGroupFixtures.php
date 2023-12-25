<?php

namespace App\DataFixtures;

use App\Entity\TricksGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TricksGroupFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [
            "Grab",
            "Rotation",
            "Flip",
            "Rotation désaxée",
            "Slide",
            "One Foot Trick",
            "Old school",
            "Autres tricks"
        ];

        for ($i = 0; $i < count(value: $data, mode: COUNT_NORMAL); $i++){
            $trickGroupeName = new TricksGroup();
            $trickGroupeName->setNameGroup($data[$i]);
            $manager->persist($trickGroupeName);
        }

        $manager->flush();
    }
}
