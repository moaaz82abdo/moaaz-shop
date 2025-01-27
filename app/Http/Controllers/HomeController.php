<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    
    public function index()
    {
        $categories = Category::all();
        $slides = Slide::where('status', 1)->get()->take(5);
        $sproducts = Product::whereNotNull('sale_price')->where('sale_price', '<>', '')->get()->take('8');
        $fproducts = Product::where('featured', 1)->get()->take(8);
        return view('index', compact('categories', 'slides','sproducts','fproducts'));
    }

    public function contact()
    {
        return view('contact');
    }

    public function contact_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50',
            'email' => 'required|email',
            'phone'=>'required|numeric|digits:11',
            'comment'=>'required',
        ]);

        $contact = new Contact();
        $contact->name = $request->name;
        $contact->phone = $request->phone;
        $contact->email = $request->email;
        $contact->comment = $request->comment;
        $contact->save();

        return redirect()->back()->with('status', 'Your message has been submitted successfully');

    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'like', "%{$query}%")->get()->take(8);
        return response()->json($results);
    }

}
