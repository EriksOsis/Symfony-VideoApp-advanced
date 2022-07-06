<?php

namespace App\Utils;

use App\Twig\AppExtension;
use App\Utils\AbstractClasses\CategoryTreeAbstract;

class CategoryTreeFrontPage extends CategoryTreeAbstract
{
    public $html_1 = '<ul>';
    public $html_2 = '<li>';
    public $html_3 = '<a href="';
    public $html_4 = '">';
    public $html_5 = '</a>';
    public $html_6 = '</li>';
    public $html_7 = '</ul>';
    private $slugger;

    public function getCategoryListAndParent(int $id): string
    {
        $this->slugger = new AppExtension; // twig extensions slugify url for categories
        $parentData = $this->getMainParent($id); // getting main parent id
        $this->mainParentName = $parentData['name']; // for accessing in view
        $this->mainParentId = $parentData['id'];// for accessing in view
        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));
        $this->currentCategoryName = $this->categoriesArrayFromDb[$key]['name']; // for accessing in view
        $categoriesArray = $this->buildTree($parentData['id']); // build array for nested html list

        return $this->getCategoryList($categoriesArray);
    }

    public function getCategoryList(array $categories_array): string
    {
        $this->categorylist .= $this->html_1;
        foreach ($categories_array as $value) {
            $catName = $this->slugger->slugify($value['name']);
            $url = $this->urlGenerator->generate('video_list', ['categoryname' => $catName, 'id' => $value['id']]);
            $this->categorylist .= $this->html_2 . $this->html_3 . $url . $this->html_4 . $catName . $this->html_5;
            if (!empty($value['children'])) {
                $this->getCategoryList($value['children']);
            }
            $this->categorylist .= $this->html_6;

        }
        $this->categorylist .= $this->html_7;
        return $this->categorylist;
    }

    public function getMainParent(int $id): array
    {
        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));

        if ($this->categoriesArrayFromDb[$key]['parent_id'] != null) {
            return $this->getMainParent($this->categoriesArrayFromDb[$key]['parent_id']);
        } else {
            return ['id' => $this->categoriesArrayFromDb[$key]['id'],
                'name' => $this->categoriesArrayFromDb[$key]['name']
            ];

        }
    }

    public function getChildsIds(int $parent): array // get subcategories id
    {
        static $ids = []; // array with video ids
        foreach ($this->categoriesArrayFromDb as $category) {
            if ($category['parent_id'] == $parent) // if there is a parent id for a category
            {
                $ids[] = $category['id'] . ','; // add that categories` id to the array
                $this->getChildsIds($category['id']); // get categories id
            }
        }

        return $ids;
    }
}