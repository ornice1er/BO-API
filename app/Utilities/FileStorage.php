<?php

namespace App\Utilities;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;

class FileStorage
{
    public static function setFile($disk, $file, $directory = '', $name = '')
    {

        $directory_name = $directory;

        $directory_exist = Storage::disk($disk)->exists($directory_name);

        if (! $directory_exist) {
            Storage::disk($disk)->makeDirectory($directory_name);
        }

        $pre_filename = $name == '' || $name == null ? $file->getFilename() : Str::slug($name);

        $filename = $pre_filename.'.'.$file->getClientOriginalExtension();

        if (! Storage::disk($disk)->exists($directory_name != '' || $directory_name != null ? $directory_name.'/'.$filename : $filename)) {

            Storage::disk($disk)->put($directory_name != '' || $directory_name != null ? $directory_name.'/'.$filename : $filename, File::get($file));
        }

        return $filename;
    }

    public static function set64File($disk, $file, $directory = '', $name = '')
    {

        $directory_name = $directory;

        $directory_exist = Storage::disk($disk)->exists($directory_name);

        if (! $directory_exist) {
            Storage::disk($disk)->makeDirectory($directory_name);
        }
        $image = $file;
        $extension = explode('/', mime_content_type($image))[1];
        $image = explode(',', $image);
        $image = str_replace(' ', '+', $image[1]);
        $filename = $name.'.'.$extension;
        //  $image = str_replace('data:image/png;base64,', '', $image);
        //  info($image);
        // $image = str_replace(' ', '+', $image);
        // $filename = $name.'.pdf';
        // $pre_filename= $name=="" || $name==null?$file->getFilename():Str::slug($name);

        // $filename=$pre_filename.'.'.$file->getClientOriginalExtension();

        if (! Storage::disk($disk)->exists($directory_name != '' || $directory_name != null ? $directory_name.'/'.$filename : $filename)) {
            File::put(Storage::disk($disk)->path($directory_name != '' || $directory_name != null ? $directory_name.'/'.$filename : $filename), base64_decode($image));
        }

        return $filename;
    }

    public static function deleteFile($disk, $file, $directory = '')
    {

        $file_exist = Storage::disk($disk)->exists($directory != '' || $directory != null ? $directory.'/'.$file : $file);

        if ($file_exist) {
            Storage::disk($disk)->delete($directory != '' || $directory != null ? $directory.'/'.$file : $file);
        }
    }
}
