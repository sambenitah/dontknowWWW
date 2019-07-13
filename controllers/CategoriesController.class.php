<?php

declare(strict_types=1);

namespace DontKnow\Controllers;
use DontKnow\Core\View;
use DontKnow\Models\Categories as CategoriesModel;
use DontKnow\Dao\Categories;
use DontKnow\Core\Validator;
use DontKnow\Core\Routing;

Class CategoriesController{

    const nameClass = "Categories";

    private $categoriesDao;

    public function __construct(Categories $categories)
    {
        $this->categoriesDao = $categories;
    }

    public function addCategoryAction(){
        $addCategory = new CategoriesModel();
        $form = $this->categoriesDao->getAddCategoryForm();
        $method = strtoupper($form["config"]["method"]);
        $data = $GLOBALS["_".$method];


        if( $_SERVER['REQUEST_METHOD']==$method && !empty($data) ){

            $validator = new Validator($form,$data);
            $form["errors"] = $validator->errors;

            if(empty($form["errors"])){
                $addCategory->setName($data["name"]);
                $this->categoriesDao->insertCategory($addCategory);
                header('Location: '.Routing::getSlug("Customizer","default").'');
                exit;
            }
        }
        $v = new View("addCategory",self::nameClass, "admin");
        $v->assign("ListForm", $form);
    }

    public function listAllCategoriesAction(){
        $selectCategory = $this->categoriesDao->selectCategory();
        return $selectCategory;
    }

    public function showCategoryAction(){
        $selectCategory = $this->categoriesDao->selectCategory();
        echo json_encode($selectCategory);
        exit;
    }

    public function deleteCategoryAction(){
        $data = $GLOBALS["_POST"];
        $id = $data["id"];
        $category = $this->categoriesDao->selectSingleCategory(["id"=>$id]);
        $count = $this->categoriesDao->SelectCountArticles(["category" => $category->name]);
        if($count['Article']  == 0) {
            $this->categoriesDao->deleteCategory(["id"=>$id]);
            unlink(substr($data["url"],1));
            echo json_encode("Delete");
        }
        else
            echo json_encode("Impossible");
        exit;
    }


}