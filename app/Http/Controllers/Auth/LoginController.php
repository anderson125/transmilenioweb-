<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\CasualException;
use Illuminate\Support\Facades\Route;

use App\Models\Parking;

use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        if(Route::getCurrentRoute()){
            $route = Route::getCurrentRoute()->uri();
            $this->role = ( preg_match("/api\//",$route)) ? "app" : "web";           
        }
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return response()->json(['message'=>'Success', 'errors'=>[]],200);
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        return response()->json(['message'=>'Success','errors'=>[]],200);
    }


     /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        try{
            $this->validateLogin($request);
       

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);

         }catch(CasualException $e){
            return response()->json(['message'=>$e->getMessage(), 'response'=>['errors'=>array_merge([],$e->errors)]],$e->code);
        }
    }


    protected function validateLogin(Request $request)
    {
        
        $rules = [
            'email' => 'required|string',
            'password' => 'required|string'
        ];

        $messages = [
            'email.required'=>'El campo email es requerido.',
            'email.string'=>'El campo email es de tipo texto.',
            'password.required'=>'El campo contraseña es requerido.',
            'password.string'=>'El campo contraseña es de tipo texto.',
        ];

        $validator = Validator::make($request->all(),$rules,$messages);
        if($validator->fails()){
            $exception = new CasualException('Bad Request');
            $exception->errors = array_merge(["No se ha conseguido validar la información del login"],$validator->errors()->all());
            $exception->code = 400;
            throw $exception;
        }
        
    }


    protected function attemptLogin(Request $request)
    {
        $status =  $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );

        if(!$status){
            $exception = new CasualException('Unprocessable Entity');
            $exception->code = 422;
            $exception->errors = ['Las credenciales ingresadas son inválidas.'];
            throw $exception;

        }else{
            return $status;
        }
    }

    protected function sendAppLoginResponse(Request $request){
        $token = $request->user()->createToken('APP_TOKEN');

        $duration = 3600;
        $_date = date("Y-m-d H:i:s",strtotime(" + $duration seconds"));
        $date = date_create($_date);
        $nDate = date_format($date, 'D M d H:i:s Y');

        $days = [   'en'=>['Mon','Tue', 'Wed', 'Thu','Fri','Sat','Sun'], 
                    'es'=>['Lun','Mar','Mie','Jue','Vie','Sab','Dom']];
        $months = [ 'en'=>['Jan','Feb', 'Mar', 'Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], 
                    'es'=>['Ene','Feb', 'Mar', 'Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dec']];
      
        $nDate = str_replace($days['en'],$days['es'],$nDate);
        $nDate = str_replace($months['en'],$months['es'],$nDate);

        $user = $request->user();

        $parking = Parking::find($user->parkings_id);

        $user->parking_name = ($parking) ?  $parking->name : null;

        return response()->json(['message'=>'Success', 'response'=>[
            'data'=>[$user],
            'token'=>['type'=>'Bearer','access_token'=>$token->plainTextToken, 'expires_in'=>$duration, 'expires_at'=>$nDate],
            'errors'=>[]]],200);
    }

    protected function sendLoginResponse(Request $request)
    {
        // $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $token = $request->user()->createToken('APP_TOKEN');
        
        if ($response = $this->authenticated($request, $this->guard()->user())) {
             return response()->json(['message'=>'Success', 'response'=>[
                    'token'=>['type'=>'Bearer','access_token'=>$token->plainTextToken],
                    'errors'=>[]]],200);
            return $response;
        }

        return response()->json(['message'=>'Success', 'response'=>[
                    'token'=>['type'=>'Bearer','access_token'=>$token->plainTextToken],
                    'errors'=>[]]],200);
    }


    public function loginAPP( Request $request){
         try{
            $this->validateLogin($request);
       

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendAppLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);

         }catch(CasualException $e){
            return response()->json(['message'=>$e->getMessage(), 'response'=>['errors'=>array_merge([],$e->errors)]],$e->code);
        }
    }


    public function logoutApp( Request $request){
        $token = PersonalAccessToken::findToken($request->bearerToken());
        $request->user()->withAccessToken($token);
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Sucess', 'response'=>['errors'=>[]]],200);
    }

}
