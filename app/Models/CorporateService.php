<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateService extends Model
{
    use HasFactory;
     protected $table = 'corporateservices';
    protected $primaryKey = 'id';
    protected $guarded = []; // Allow all fields for mass assignment
}
