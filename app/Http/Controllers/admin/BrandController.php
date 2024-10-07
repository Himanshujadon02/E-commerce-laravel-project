<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index(Request $request){
        $brands = Brand::latest();
        if (!empty($request->get('keyword'))){
            $brands = $brands->where('name','like','%'.$request->get('keyword').'%');
        }
        $brands = $brands->paginate(10);
        return view('admin.brands.list',compact('brands'));
    }

    public function create(){
        return view('admin.brands.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands',
        ]);
        if ($validator->passes()){
            $brand = new Brand();
            $brand->name=$request->name;
            $brand->slug=$request->slug;
            $brand->status=$request->status;
            $brand->save();

            $request->session()->flash('success','Brand added successfully');
            return response()->json([
                'status' => true,
                'message' => ('Bramd added successfully')
            ]);



        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }


    public function edit($id, Request $request){
        $brand = Brand::find($id);

        if(empty($brand)){
            $request->session()->flash('error','record not found');
            return redirect()->route('brand.list');
        }
        $data['brand'] = $brand;

        return view('admin.brands.edit',$data);
    }


    public function update($id, Request $request){
        $brand = Brand::find($id);

        // If the category is not found, return a not found response
        if (empty($brand)) {

            $request->session()->flash('error', 'Brand not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Brand not found'
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $brand->id . ',id',
        ]);
        if ($validator->passes()){

            $brand->name=$request->name;
            $brand->slug=$request->slug;
            $brand->status=$request->status;
            $brand->save();

            $request->session()->flash('success','Brand updated successfully');
            return response()->json([
                'status' => true,
                'message' => ('Bramd updated successfully')
            ]);



        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($categoryId,Request $request){
        $brand = Brand::find($categoryId);

        if (empty($brand)){
            // return redirect()->route('categories.list');
            $request->session()->flash('error','brand not found');
            return response()->json([
                'status' => true,
                'message' => 'brand not found'
            ]);
        }


        $brand->delete();

        $request->session()->flash('success','brand deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'brand deleted successfully'
        ]);
    }
}
