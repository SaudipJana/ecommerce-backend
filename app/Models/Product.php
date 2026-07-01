<?php

namespace App\Models;

use AppendIterator;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $appends = ['image_url'];
    protected $fillable = ['image'];

    public function getImageUrlAttribute(){
        if($this->image == ""){
            return null;
        }

        return asset('/uploads/products/small/'.$this->image);
    }

    function product_images(){
        return $this->hasMany(ProductImage::class);
    }

    function product_sizes(){
        return $this->hasMany(ProductSize::class);
    }
}
