<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\Category;
use Image;


class CategoryController extends Controller
{
    public function index(Request $request){
        $categories = Category::latest();
        if (!empty($request->get('keyword'))){
            $categories = $categories->where('name','like','%'.$request->get('keyword').'%');
        }


        $categories = $categories->paginate(10);
        return view('admin.category.list',compact('categories'));
    }
    public function create(){
        return view('admin.category.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);
        if ($validator->passes()){
            $category = new Category();
            $category->name=$request->name;
            $category->slug=$request->slug;
            $category->status=$request->status;
            $category->save();

            // save image here
            if(!empty($request->image_id)){
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.',$tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id.'.'.$ext;
                $sPath = public_path().'/temp/'.$tempImage->name;
                $dPath = public_path().'/uploads/category/'.$newImageName;
                File::copy($sPath,$dPath);

                // generate image thumnail
                $dPath = public_path().'/uploads/category/thumb/'.$newImageName;
                $img = Image::make($sPath );
                // $img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });


                $img->save($dPath);


                $category->image = $newImageName;
                $category->save();
            }



            $request->session()->flash('success','Category added successfully');
            return response()->json([
                'status' => true,
                'message' => ('category added successfully')
            ]);



        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($categoryId,Request $request){
        $category = Category::find($categoryId);
        if(empty($category)){
            return redirect()->route('categories.list');
        }else{

            return view('admin.category.edit',compact('category'));
        }
    }

    public function update($categoryId, Request $request) {
        // Find the existing category by ID
        $category = Category::find($categoryId);

        // If the category is not found, return a not found response
        if (empty($category)) {

            $request->session()->flash('error', 'Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // Ensure unique slug, but ignore the current category's slug
            'slug' => 'required|unique:categories,slug,' . $category->id . ',id',
        ]);

        // If validation passes, update the category
        if ($validator->passes()) {
            // Update the existing category object (Removed the creation of a new category)
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            // Save the updated category data
            $category->save();

            $oldImage = $category->image;

            // Handle the image upload, if provided
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id .'-'.time().'.' . $ext;
                $sPath = public_path() . '/temp/' . $tempImage->name;
                $dPath = public_path() . '/uploads/category/' . $newImageName;

                // Copy the image to the final destination
                File::copy($sPath, $dPath);

                // Generate and save a thumbnail
                $thumbPath = public_path() . '/uploads/category/thumb/' . $newImageName;
                $img = Image::make($sPath);
                // $img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($thumbPath);

                // Save the image name in the category record
                $category->image = $newImageName;
                $category->save();

                // delete old image
                File::delete(public_path().'/uploads/category/thumb/' . $oldImage);
                File::delete(public_path().'/uploads/category/' . $oldImage);

            }

            // Flash success message and return JSON response
            $request->session()->flash('success', 'Category updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully'
            ]);
        } else {
            // Return validation errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }



    public function destroy($categoryId,Request $request){
        $category = Category::find($categoryId);

        if (empty($category)){
            // return redirect()->route('categories.list');
            $request->session()->flash('error','category not found');
            return response()->json([
                'status' => true,
                'message' => 'Category not found'
            ]);
        }

        File::delete(public_path().'/uploads/category/thumb/' . $category->image);
        File::delete(public_path().'/uploads/category/' . $category->image);

        $category->delete();

        $request->session()->flash('success','category deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);
    }


}
