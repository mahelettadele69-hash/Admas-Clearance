<?php

namespace App\Http\Controllers;

use App\Models\ClearanceRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function show(Request $request)
    {
        $serial = trim((string) $request->query('serial', ''));
        $code = trim((string) $request->query('code', ''));
        $record = null;

        if ($serial !== '') {
            $query = ClearanceRequest::with('user')->where('certificate_serial', $serial);
            if ($code !== '') {
                $query->where('verification_code', $code);
            }
            $record = $query->first();
        }

        return view('verify', compact('serial', 'code', 'record'));
    }
}