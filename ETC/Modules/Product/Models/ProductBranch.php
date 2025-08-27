<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;

class ProductBranch extends Model
{
    protected $table = 'product_branches';

    protected $fillable = [
        'product_id',
        'branch_id',
        'stock_qty',
        'is_available'
    ];

    protected $casts = [
        'stock_qty' => 'integer',
        'is_available' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
