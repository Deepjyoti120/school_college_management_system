<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InfoController extends Controller
{
    public function about(Request $request)
    {
        return Inertia::render('Info/About');
    }
    public function privacyPolicy(Request $request)
    {
        return Inertia::render('Info/PrivacyPolicy');
    }
}
