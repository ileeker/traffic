<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function getRanking($domain)
    {
        $data = Domain::where('domain', $domain)->first();

        if (!$data) {
            return response()->json([
                'message' => 'Domain not found'
            ], 404);
        }

        return response()->json($data);
    }
}
