<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
class CheckLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->method() == 'GET'){
            //var_dump($request->all()); die;
            if(!Cache::get('locale')){
                $clientIp = $_SERVER['REMOTE_ADDR'];
                $locale = geoip($clientIp)->iso_code;
                //var_dump($locale); die;
                if($locale != 'UA' &&  $locale != 'RU'){
                    $locale = 'en';
                } else{
                    $locale = strtolower($locale);
                }
                Cache::put('locale', $locale, 86400);
                App::setlocale($locale);
            } else{

            }
        }

        return $next($request);
    }
}
