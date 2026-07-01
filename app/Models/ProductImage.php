<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $appends = ['image_url'];
    
    public function getImageUrlAttribute(){
        if($this->image == ""){
            return null;
        }

        return asset('/uploads/products/small/'.$this->image);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }  
}
