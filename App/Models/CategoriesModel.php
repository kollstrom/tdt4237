<?php
namespace App\Models;

use \App\System\App;
use \App\Models\Model;

class CategoriesModel extends Model {

    protected $table = "categories";

    public function allotment() {
        $categories = $this->all($this->username);
        $categories_value = [];

        foreach($categories as $category) {
            $count = count($this->query("SELECT id FROM products WHERE category = ? AND user = '{$this->username}'", [$category->id]));

            $quantity = 0;
            foreach($this->query("SELECT quantity FROM products WHERE category = ? AND user = '{$this->username}'", [$category->id]) as $product) {
                $quantity+= $product->quantity;
            }

            $categories_value[] = [
                'name'     => $category->title,
                'count'    => $count,
                'quantity' => $quantity
            ];
        }

        return $categories_value;
    }
    
    public function hasProducts($id){
        if ($this->getProducts($id)){
            return true;
        }else{
            return false;
        }
    }

    public function userCreatedCategory($id){
        $count = 0;
        foreach($this->query("SELECT * FROM {$this->table} WHERE user = '{$this->username}' AND categories.id = {$id}") as $item) {
            $count+= 1;
        }
        return $count>0;
    }
}
