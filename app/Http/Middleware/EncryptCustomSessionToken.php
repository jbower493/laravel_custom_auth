<?php

namespace App\Http\Middleware;

use App\Traits\SessionTokenTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class EncryptCustomSessionToken
{
    use SessionTokenTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Decrypt token on the way in

        // $bearerToken = $request->bearerToken();

        // if ($bearerToken) {

        // }

        $cookie = $request->cookie('custom_session');

        if ($cookie) {
            $decryptedCookie = Crypt::decryptString($cookie);
            $request->cookies->set('my_encrypted_cookie', $decryptedCookie);
        }

        $response = $next($request);

        // Encrypt token on the way out

        if ($response instanceof \Illuminate\Http\Response) {
            $outboundCookie = $request->cookie('custom_session');
            if ($outboundCookie) {
                $encryptedCookie = Crypt::encryptString($outboundCookie);
                $response->headers->setCookie($this->createCookie($encryptedCookie)); // Re-encrypt and set the cookie
            }
        }

        return $response;
    }
}
