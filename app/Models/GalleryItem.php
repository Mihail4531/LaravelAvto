<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    use HasFactory;

    public const SIZE_SMALL = 'small';

    public const SIZE_WIDE = 'wide';

    public const SIZE_TALL = 'tall';

    protected $fillable = [
        'title',
        'caption',
        'image',
        'size',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
