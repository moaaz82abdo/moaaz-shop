<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Slide;
use App\Models\Transaction;
use Carbon\Carbon;
// use Faker\Provider\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{

    public function index()
    {
        $orders = Order::orderBy('id', 'asc')->get()->take(10);
        $dashboardData = DB::select(
            "select sum(total) as TotalAmount, 
            sum(if(status='delivered', total, 0)) as TotalDeliveredAmount, 
            sum(if(status='canceled', total, 0)) as TotalCanceledAmount, 
            sum(if(status='ordered', total, 0)) as TotalOrderedAmount,
            Count(*) as Total,
            Count(if(status='delivered', 1, null)) as TotalDelivered,
            Count(if(status='canceled', 1, null)) as TotalCanceled,
            Count(if(status='ordered', 1, null)) as TotalOrdered
             from orders");

        $monthlyDatas = DB::select("
        select M.id as MonthNo, M.name as MonthName,
        ifnull(D.TotalAmount, 0) as TotalAmount,
        ifnull(D.TotalDeliveredAmount, 0) as TotalDeliveredAmount,
        ifnull(D.TotalCanceledAmount, 0) as TotalCanceledAmount,
        ifnull(D.TotalOrderedAmount, 0) as TotalOrderedAmount from month_names M
        left join (select date_format(created_at,'%b') as MonthName,
        Month(created_at) as MonthNo,
        sum(total) as TotalAmount,
        sum(if(status='delivered', total, 0)) as TotalDeliveredAmount,
        sum(if(status='canceled', total, 0)) as TotalCanceledAmount,
        sum(if(status='ordered', total, 0)) as TotalOrderedAmount from orders
        where Year(created_at) = Year(now()) group by year(created_at),month(created_at), date_format(created_at,'%b')
        order by month(created_at)) d on d.MonthNo=M.id");

        $AmountM = implode(',',collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $orderedAmountM = implode(',',collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
        $deliveredAmountM = implode(',',collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
        $canceledAmountM = implode(',',collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        $TotalAmount = collect($monthlyDatas)->sum("TotalAmount");
        $TotalOrderedAmount = collect($monthlyDatas)->sum("TotalOrderedAmount");
        $TotalDeliveredAmount = collect($monthlyDatas)->sum("TotalDeliveredAmount");
        $TotalCanceledAmount = collect($monthlyDatas)->sum("TotalCanceledAmount");




        return view('admin.index', compact('orders', 'dashboardData','monthlyDatas', 'AmountM', 'orderedAmountM', 'deliveredAmountM', 'canceledAmountM', 'TotalAmount', 'TotalOrderedAmount', 'TotalDeliveredAmount', 'TotalCanceledAmount'));
    }
    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'

        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->generateBrandThumbnailImage($image, $file_name);
        $brand->image = $file_name;
        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand has added successfully');

    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'

        ]);

        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/brands'). '/' .  $brand->image)) {
                File::delete(public_path('uploads/brands'). '/' .  $brand->image);
                 }
            $image = $request->file('image');

            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateBrandThumbnailImage($image, $file_name);
            $brand->image = $file_name;

        }
        
        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand has updated successfully');

    }

    public function generateBrandThumbnailImage($image, $imageName)
    {

        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);

    }
    

    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        if (File::exists(public_path('uploads/brands'). '/' .  $brand->image)) {
            File::delete(public_path('uploads/brands'). '/' .  $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Brand has deleted successfully');
    }

    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'

        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->generateCategoryThumbnailImage($image, $file_name);
        $category->image = $file_name;
        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Category has added successfully');
    }

    public function generateCategoryThumbnailImage($image, $imageName)
    {

        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124, 124, "top");
        $img->resize(124, 124, function ($constraint) {
         $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'

        ]);

        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/categories') . '/' .  $category->image)) {
                File::delete(public_path('uploads/categories') . '/' .  $category->image);
            }
            $image = $request->file('image');

            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateCategoryThumbnailImage($image, $file_name);
            $category->image = $file_name;
        }

        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Category has updated successfully');
    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories') . '/' .  $category->image)) {
            File::delete(public_path('uploads/categories') . '/' .  $category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category has deleted successfully');
    }

    public function products()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products', compact('products'));
    }

    public function product_add()
    {
        $brands = Brand::select('id','name')->orderBy('name')->get();
        $categories = Category::select('id','name')->orderBy('name')->get();
        return view('admin.product-add', compact('brands', 'categories'));
    }

    public function product_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required|numeric',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price; 
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $imageName = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;
        if ($request->hasFile('images')) {
            $allawedfileExtension = ['png', 'jpg', 'jpeg'];
            foreach ($request->file('images') as $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allawedfileExtension);
                if($gcheck){
                    $gfileName = Carbon::now()->timestamp .'-' .$counter . '.' . $gextension;
                    $this->generateProductThumbnailImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter++;
                }
                // $file_extension = $image->extension();
                // $imageName = Carbon::now()->timestamp . $counter . '.' . $file_extension;
                // $this->generateProductThumbnailImage($image, $imageName);
                // $gallery_arr[] = $imageName;
                
            }
            $gallery_images = implode(',', $gallery_arr);
        }
        $product->images = $gallery_images;

        $product->save();

        return redirect()->route('admin.products')->with('status', 'Product has been added successfully');
    }

    public function generateProductThumbnailImage($image, $imageName)
    {
        $destinationPathThumbnails = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());
        $img->cover(540, 689, "top");
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnails . '/' . $imageName);
    }

    public function product_edit($id)
    {
        $product = Product::find($id);
        $brands = Brand::select('id','name')->orderBy('name')->get();
        $categories = Category::select('id','name')->orderBy('name')->get();
        return view('admin.product-edit', compact('product', 'brands', 'categories'));
    }

    public function product_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $request->id,
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required|numeric',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
        ]);

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/products') . '/' . $product->image)) {
                File::delete(public_path('uploads/products') . '/' . $product->image);
            }
            if (File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image)) {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $imageName = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;
        if ($request->hasFile('images')) {
            foreach (explode(',',$product->images) as $ofile) {
                if (File::exists(public_path('uploads/products') . '/' . $ofile)) {
                    File::delete(public_path('uploads/products') . '/' . $ofile);
                }
                if (File::exists(public_path('uploads/products/thumbnails') . '/' . $ofile)) {
                    File::delete(public_path('uploads/products/thumbnails') . '/' . $ofile);
                }
            }
            $allawedfileExtension = ['png', 'jpg', 'jpeg'];
            foreach ($request->file('images') as $file) {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allawedfileExtension);
                if ($gcheck) {
                    $gfileName = Carbon::now()->timestamp . '-' . $counter . '.' . $gextension;
                    $this->generateProductThumbnailImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter++;
                }
            }
            $gallery_images = implode(',', $gallery_arr);
                    $product->images = $gallery_images;

        }

        $product->save();

        return redirect()->route('admin.products')->with('status', 'Product has been updated successfully');
    }

    public function product_delete($id)
    {
        $product = Product::find($id);
        if (File::exists(public_path('uploads/products') . '/' . $product->image)) {
            File::delete(public_path('uploads/products') . '/' . $product->image);
        }
        if (File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image)) {
            File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
        }
        foreach (explode(',', $product->images) as $ofile) {
            if (File::exists(public_path('uploads/products') . '/' . $ofile)) {
                File::delete(public_path('uploads/products') . '/' . $ofile);
            }
            if (File::exists(public_path('uploads/products/thumbnails') . '/' . $ofile)) {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $ofile);
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('status', 'Product has been deleted successfully');
    }

    public function coupons()
    {
        $coupons = Coupon::orderBy('expiry_date', 'DESC')->paginate(10);
        return view('admin.coupons', compact('coupons'));
    }

    public function coupon_add()
    {
        return view('admin.coupon-add');
    }
    
    public function coupon_store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'Coupon has been added successfully');
    }

    public function coupon_edit($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit', compact('coupon'));
    }

    public function coupon_update(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code,' . $request->id,
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'Coupon has been updated successfully');
    }

    public function coupon_delete($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status', 'Coupon has been deleted successfully');
    }

    public function orders()
    {
        $orders = Order::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.orders', compact('orders'));
    }

    public function order_details($id)
    {
        $order = Order::find($id);
        $orderItems = OrderItem::where('order_id', $id)->orderBy('id')->paginate(10);
        $transaction = Transaction::where('order_id', $id)->first();
        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }


    public function update_order_status(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if ($request->order_status == 'delivered') {
            $order->delivered_date = Carbon::now();
        } else if ($request->order_status == 'canceled') {
            $order->canceled_date = Carbon::now();
        }
        $order->save();
        if ($request->order_status == 'delivered') {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction->status = "approved";
            $transaction->save();
        }
        return back()->with("status", "Status changed successfully!");
    }

    public function slides()
    {
        $slides = Slide::orderBy('id', 'DESC')->paginate(10);
        return view('admin.slides', compact('slides'));
    }

    public function slide_add()
    {
        return view('admin.slide-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateSlideThumbnailImage($image, $file_name);
            $slide->image = $file_name;
        
        $slide->save();

        return redirect()->route('admin.slides')->with('status', 'Slide has been added successfully');
    }

    public function generateSlideThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');
        $img = Image::read($image->path());
        $img->cover(400, 690, "top");
        $img->resize(400, 690, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }

    public function slide_edit($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide-edit', compact('slide'));
    }

    public function slide_update(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;
        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/slides') . '/' . $slide->image)) {
                File::delete(public_path('uploads/slides') . '/' . $slide->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateSlideThumbnailImage($image, $file_name);
            $slide->image = $file_name;
        }
        $slide->save();
        return redirect()->route('admin.slides')->with('status', 'Slide has been updated successfully');
    }

    public function slide_delete($id)
    {
        $slide = Slide::find($id);
        if (File::exists(public_path('uploads/slides') . '/' . $slide->image)) {
            File::delete(public_path('uploads/slides') . '/' . $slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status', 'Slide has been deleted successfully');
    }

    public function contacts()
    {
        $contacts = Contact::orderBy('created_at','desc')->paginate(10);
        return view('admin.contacts', compact('contacts'));
        
    }

    public function message_delete($id)
    {
        $message = Contact::find($id);
        $message->delete();
        return redirect()->route('admin.contacts')->with('status', 'Message has been deleted successfully');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'like', "%{$query}%")->get()->take(8);
        return response()->json($results);
    }
}
