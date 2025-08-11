<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Archive extends Model
{
    use HasFactory;

    protected $guarded=[];

    protected $hidden=["deleted_at","created_at","updated_at","laravel_through_key"];

    public static function createFromUrl($url)
    {
        $archive_id = null;

        if (checkUrl($url))
        {
            $fullpath_parts = explode('/', $url);
            $filename = array_pop($fullpath_parts);
            $filename_parts = explode('.', $filename);
            $ext = array_pop($filename_parts);
            $name = implode($filename_parts);
            $new_name = uniqid() . uniqid();

            Storage::disk('public')->put($new_name . '.' . $ext, fopen($url, 'r'));

            $archive = Archive::create([
                "name" => $name . '.' . $ext,
                "path" => $new_name . '.' . $ext
            ]);

            $archive_id = $archive->id;
        }

        return $archive_id;
    }

    public static function createFromXls($url)
    {
        $archive_id = null;

        if (checkUrl($url))
        {
            $fullpath_parts = explode('/', $url);
            $filename = array_pop($fullpath_parts);
            $filename_parts = explode('.', $filename);
            $ext = array_pop($filename_parts);
            $name = implode($filename_parts);
            $new_name = uniqid() . uniqid();

            Storage::disk('public')->put($new_name . '.' . $ext, fopen($url, 'r'));

            $archive = new Archive();
            $archive->name = $name . '.' . $ext;
            $archive->path = $new_name . '.' . $ext;
            $archive->save();

            $archive_id = $archive->id;
        }

        return $archive_id;
    }
}
