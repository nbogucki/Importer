<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Categories;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i<=10; $i++) {
            $category = new Categories();
            $category->setName('category_'.$i);
            
            $manager->persist($category);
        }   
        $manager->flush();
    }
}
