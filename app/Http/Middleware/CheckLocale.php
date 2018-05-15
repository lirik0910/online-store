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
        //var_dump($request); die;
        if($request->method() == 'GET' || $request->method() == 'POST'){
            $current_domain = $request->getSchemeAndHttpHost();

            $clientIp = $_SERVER['REMOTE_ADDR'];
            $locale = strtolower(geoip($clientIp)->iso_code);
            if($locale !== 'ua' && $locale !== 'ru'){
                $locale = 'en';
            }

            if($request->get('locale')){
                session()->put('locale', $request->get('locale'));
            }
            //var_dump(session()->get('locale'));// die;
/*            if(!session()->get('locale')){
                session()->put('locale', $locale);
            }*/

            if($locale == 'ua' && $current_domain !== config('app.ua_domain') || $locale == 'ru' && $current_domain !== config('app.ru_domain') || $locale =='en' && $current_domain !== config('app.en_domain')){

                if(session()->get('locale')){
                  //  var_dump('Evil!!'); die;
                    switch (session()->get('locale')){
                        case 'ua':
                            return redirect(config('app.ua_domain'));
                            break;
                        case 'ru':
                            //App::setLocale('ru');
                            return redirect(config('app.ru_domain'));
                            break;
                        case 'en':
                           // App::setLocale('en');
                            return redirect(config('app.en_domain'));
                            break;
                    }
                }
            }

            switch ($current_domain) {
                case config('app.ua_domain'):
                   // $locale = 'ua';
                    App::setLocale('ua');
                    break;

                case config('app.ru_domain'):
                    App::setLocale('ru');
                    //$locale = 'ru';
                    break;

                case config('app.en_domain'):
                    //$locale = 'en';
                    App::setLocale('en');
                    break;

                default:
                    break;
            }
        }
        //var_dump($locale); die;
        return $next($request);
    }
}
