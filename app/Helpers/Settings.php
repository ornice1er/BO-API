<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('SettingData')) {
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    function SettingData($key)
    {
        return Setting::where('key', $key)->first()?->value;

    }

}

if (! function_exists('upload_files')) {
    function upload_files($request, $fileField, $path)
    {
        $file = $request->file($fileField);
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::random(20).'_'.date('His').random_int(1, 999999).'.'.$extension;

        return Storage::disk('s3')->url($file->storeAs($path, $fileName, 's3')); //
    }
}
