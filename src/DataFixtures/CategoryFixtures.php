<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->loadMainCategories($manager);
        $this->loadSubcategories($manager, 'Electronics', 1);
        $this->loadSubcategories($manager, 'Computers', 6);
        $this->loadSubcategories($manager, 'Laptops', 8);
        $this->loadSubcategories($manager, 'Books', 3);
        $this->loadSubcategories($manager, 'Movies', 4);
        $this->loadSubcategories($manager, 'Romance', 18);


    }

    private function getMainCategoriesData(): array
    {
        return [
            ['Electronics', 1],
            ['Toys', 2],
            ['Books', 3],
            ['Movies', 4]
        ];
    }

    public function getElectronicsData(): array
    {
        return [
            ['Cameras', 5],
            ['Computers', 6],
            ['Cell Phones', 7],
        ];
    }

    private function getComputersData(): array
    {
        return [['Laptops', 8],
            ['Desktops', 9]
        ];
    }

    private function getLaptopsData()
    {
        return [

            ['Apple', 10],
            ['Asus', 11],
            ['Dell', 12],
            ['Lenovo', 13],
            ['HP', 14]

        ];
    }


    private function getBooksData()
    {
        return [
            ['Children\'s Books', 15],
            ['Kindle eBooks', 16],
        ];
    }


    private function getMoviesData()
    {
        return [
            ['Family', 17],
            ['Romance', 18],
        ];
    }


    private function getRomanceData()
    {
        return [
            ['Romantic Comedy', 19],
            ['Romantic Drama', 20],
        ];
    }

    private function loadSubcategories($manager, $category, $parentId)
    {
        $parent = $manager->getRepository(Category::class)->find($parentId);

        $methodName = "get{$category}Data";

        foreach ($this->$methodName() as [$name]) {
            $category = new Category();
            $category->setName($name);
            $category->setParent($parent);
            $manager->persist($category);
        }

        $manager->flush();
    }

    private function loadMainCategories($manager)
    {
        foreach ($this->getMainCategoriesData() as [$name]) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
