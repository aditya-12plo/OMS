<?php
namespace App\Http\Middleware;
use Closure;
use Exception;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
class JwtMiddleware
{
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken();

        if(!$token) {
            // Unauthorized response if token not there
			
			return response()
				->json(['status'=>401 ,'datas' => [], 'errors' => ['message' => "Token not provided."]])
				->withHeaders([
				  'Content-Type'          => 'application/json',
				  ])
				->setStatusCode(401);
        }
        try {
            $credentials = JWT::decode($token, env('APP_KEY'), ['HS256']);
        } catch(ExpiredException $e) {
			return response()
				->json(['status'=>400 ,'datas' => [], 'errors' => ['message' => "Provided token is expired."]])
				->withHeaders([
				  'Content-Type'          => 'application/json',
				  ])
				->setStatusCode(400);
        } catch(Exception $e) {
			return response()
				->json(['status'=>400 ,'datas' => [], 'errors' => ['message' => "An error while decoding token."]])
				->withHeaders([
				  'Content-Type'          => 'application/json',
				  ])
				->setStatusCode(400);
        }
        // $user = User::find($credentials->sub->user_id);
        // Now let's put the user in the request class so that you can grab it from there
        $request->auth = $credentials->sub;
        return $next($request);
    }
	
	public function bearerToken(){
		$header	= $this->header('Authorization','');
		if(Str::startsWith($header, 'Bearer ')){
			return Str::substr($header, 7);
		}
	}
}