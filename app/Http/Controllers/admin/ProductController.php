<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    public function index(){
        $products = Product::orderBy('created_at', 'DESC')->with(['product_images', 'product_sizes'])->get();
        return response()->json([
            'status' => 200,
            'data' => $products,
        ], 200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'price' => 'required|numeric',
            'category' => 'required|integer',
            'sku' => 'required|unique:products,sku',
            'is_featured' => 'required',
            'status' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages()
            ], 400);
        }

        $product = new Product();
        $product->title = $request->title;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->category_id= $request->category;
        $product->brand_id = $request->brand;
        $product->sku = $request->sku;
        $product->qty = $request->qty;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->barcode = $request->barcode;
        $product->save();

        if(!empty($request->sizesChecked)){
            foreach($request->sizesChecked as $sizeId){
                $productSize = new ProductSize();
                $productSize->size_id = $sizeId;
                $productSize->product_id = $product->id;
                $productSize->save();
            }
        }

        if(!empty($request->gallery)){
            foreach($request->gallery as $key => $tempImageId){
                $tempImage = TempImage::find($tempImageId);

                $extArray = explode('.', $tempImage->name);
                $ext = end($extArray);

                $imageName = $product->id.'-'.time().'_'.rand(0,9).'.'.$ext;

                $img = ImageManager::gd()->read(public_path('uploads/temp/'.$tempImage->name));
                $img->scaleDown(1200);
                $img->save(public_path('uploads/products/large/'.$imageName));

                $img = ImageManager::gd()->read(public_path('uploads/temp/'.$tempImage->name));
                $img->coverDown(400, 460);
                $img->save(public_path('uploads/products/small/'.$imageName));

                $productImage = new ProductImage();
                $productImage->image = $imageName;
                $productImage->product_id = $product->id;
                $productImage->save();

                if($key == 0){
                    $product->image = $imageName;
                    $product->save();
                }
            }
        }
        

        return response()->json([
                'status' => 200,
                'message' => 'Product Has Been Created Successfully',
            ], 200);
    }

    public function show($id){
        $product = Product::with(['product_images', 'product_sizes'])->find($id);

        if($product == null){
            return response()->json([
                'status' => 404,
                'message' => 'Product Not Found',
            ], 404);
        }

        $productSizes = $product->product_sizes()->pluck('size_id');

        return response()->json([
                'status' => 200,
                'data' => $product,
                'productSizes' => $productSizes,
            ], 200);
    }

    public function update($id, Request $request){
        $product = Product::find($id);

        if($product == null){
            return response()->json([
                'status' => 404,
                'message' => 'Product Not Found',
            ], 404);
        }

        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'price' => 'required|numeric',
            'category' => 'required|integer',
            'sku' => 'required|unique:products,sku,'.$id.',id',
            'is_featured' => 'required',
            'status' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages()
            ], 400);
        }

        $product->title = $request->title;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->category_id= $request->category;
        $product->brand_id = $request->brand;
        $product->sku = $request->sku;
        $product->qty = $request->qty;
        $product->description = $request->description;
        $product->short_description = $request->short_description;
        $product->status = $request->status;
        $product->is_featured = $request->is_featured;
        $product->barcode = $request->barcode;
        $product->save();

        if(!empty($request->sizes)){
            ProductSize::where('product_id', $product->id)->delete();
            foreach($request->sizes as $sizeId){
                $productSize = new ProductSize();
                $productSize->size_id = $sizeId;
                $productSize->product_id = $product->id;
                $productSize->save();
            }
        }

        return response()->json([
                'status' => 200,
                'message' => 'Product Has Been Updated Successfully',
            ], 200);
    }

    public function destroy($id){
        $product = Product::with('product_images')->find($id);

        if($product == null){
            return response()->json([
                'status' => 404,
                'message' => 'Product Not Found',
            ], 404);
        }

        
        if($product->product_images){
            foreach($product->product_images as $productImage){
                File::delete(public_path('uploads/products/large/'.$productImage->image));
                File::delete(public_path('uploads/products/small/'.$productImage->image));
            }
        }

        $product->delete();
        
        return response()->json([
                'status' => 200,
                'message' => 'Product Has Been Deleted Successfully',
            ], 200);
    }

    public function addProductImage(Request $request){
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages()
            ], 400);
        }

        $image = $request->file('image');
        $imageName = $request->product_id.'-'.time().'_'.rand(0,9).'.'.$image->extension();

        $img = ImageManager::gd()->read($image->getPathName());
        $img->scaleDown(1200);
        $img->save(public_path('uploads/products/large/'.$imageName));

        $img = ImageManager::gd()->read($image->getPathName());
        $img->coverDown(400, 460);
        $img->save(public_path('uploads/products/small/'.$imageName));

        $productImage = new ProductImage();
        $productImage->image = $imageName;
        $productImage->product_id = $request->product_id;
        if($productImage->product->image == null){
            $productImage->product->update(['image'=> $productImage->image]);
        }
        $productImage->save();

        return response()->json([
                'status' => 200,
                'message' => 'Image Has Been Uploaded Successfully',
                'data' => $productImage,
            ], 200);
    }

    public function updateDefaultImage(Request $request){
        $product = Product::find($request->product_id);
        $product->image = $request->image;
        $product->save();

        return response()->json([
            'status' => 200,
            'message' => 'Product Default Image Updated Successfully',
        ], 200);
    }

    public function deleteProductImage($id){
        $productImage = ProductImage::find($id);
        
        if($productImage == null){
            return response()->json([
                'status' => 404,
                'message' => 'Image not found',
            ], 404);
        }
        
        if($productImage->image == $productImage->product->image){
            
            if(sizeof($productImage->product->product_images) > 1){
                $defaultImage = $productImage->product->product_images[1]->image;
                $productImage->product->update(['image' => $defaultImage]);   
            }else{
                $productImage->product->update(['image' => null]);
            }
        }

        File::delete(public_path('uploads/products/large/'.$productImage->image));
        File::delete(public_path('uploads/products/small/'.$productImage->image));
        $productImage->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Product Image Deleted Successfully',
        ], 200);
    }
}
