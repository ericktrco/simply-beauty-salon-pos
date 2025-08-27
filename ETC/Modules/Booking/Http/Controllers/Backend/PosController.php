<?php

namespace Modules\Booking\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index(Request $request, $branch)
    {
        $initialBranch = is_numeric($branch)
            ? Branch::active()->find($branch)
            : Branch::active()->where('slug', $branch)->first();

        if (! $initialBranch) {
            return abort(404);
        }

        return view('booking::backend.pos.index', compact('initialBranch'));
    }
}


