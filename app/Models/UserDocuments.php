<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class UserDocuments extends Model
{
    use HasFactory;
       protected $table='user_documents';
         protected $fillable = [
        'user_id',
        'document',
        'status'
      
     ];
      public function addUserDocument($inputArr)
    {
      return self::create($inputArr);
    }

    function uploadedDocument($file, $folder = "documents")
{
    $fileName = rand() . '_' . time() . '.' . $file->getClientOriginalExtension();
    Storage::disk($folder)->putFileAs('/', $file, $fileName);
    return Storage::disk($folder)->url($fileName);
}
}
