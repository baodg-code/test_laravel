<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'categories');

        if (!in_array($tab, ['categories', 'products', 'users'], true)) {
            $tab = 'categories';
        }

        return view('admin.index', [
            'tab' => $tab,
        ]);
    }
}
