<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $products_count = Product::count();
        $categories_count = ProductCategory::count();
        $transactions_count = Transaction::count();

        return view('admin.dashboard', compact('products_count', 'categories_count', 'transactions_count'));
    }
}
