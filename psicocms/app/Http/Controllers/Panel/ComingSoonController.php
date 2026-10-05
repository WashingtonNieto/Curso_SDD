<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ComingSoonController extends Controller
{
    public function __invoke(Request $request): View
    {
        $key = Str::after($request->route()->getName(), 'panel.');
        $section = config('panel.coming_soon.'.$key);

        abort_if($section === null, 404);

        return view('panel.coming-soon', ['section' => $section, 'query' => $request->string('q')->trim()->toString()]);
    }
}
