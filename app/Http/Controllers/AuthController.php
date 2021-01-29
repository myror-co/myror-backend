<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password; 
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Laravel\Socialite\Facades\Socialite;

use App\Models\User;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'email|required|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);
        $validatedData['name'] = $validatedData['email'];
        $validatedData['password'] = Hash::make($request->password);

        $user = User::create($validatedData)->sendEmailVerificationNotification();

        return response()->json(['message' => 'User successfully created, an email has been sent to your email'], 201);
    }

    /**
     * Login
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required|min:8',
        ]);

        if (!Auth::attempt($loginData)) 
        {
            //Show message to use google sign in

            return response()->json(['message' => 'Invalid Credentials'], 400);
        }

        if (!Auth::user()->hasVerifiedEmail())
        {
            return response()->json(['message' => "Email not verified."], 401);
        }

        return response()->json(['user' => Auth::user()], 200);
    }

    /**
     * Logout
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        return response()->json(['message' => 'Logout successfully'], 200);
    }


    /**
     * Verify Email
     *
     * @return \Illuminate\Http\Response
     */
    public function verify($user_id, Request $request) 
    {
        if (!$request->hasValidSignature()) 
        {
            return response()->json(['message' => "Invalid/Expired url provided."], 401);
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) 
        {
            $user->markEmailAsVerified();
        }

        //Add to mailing list
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.sendinblue.com/v3/contacts';

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'api-key' => env('SENDINBLUE_API_KEY')
            ],
            'json' => [
                'email' => $user->email,
                'attributes' => ['PRENOM' => $user->name],
                'listIds' => [2],
                'updateEnabled' => true
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $user->sendinblue_id = $data['id'];
        $user->save();

        return redirect()->away(env('APP_FRONT_LOGIN'));;
        // return response()->json(['message' => 'Email successfully verified'], 200);
    }


    /**
     * Resend Verification Email
     *
     * @return \Illuminate\Http\Response
    */
    public function resend() 
    {
        if (Auth::user()->hasVerifiedEmail()) 
        {
            return response()->json(['message' => "Email already verified."], 400);
        }

        Auth::user()->sendEmailVerificationNotification();

        return response()->json(['message' => "Email verification link sent to your email."], 200);
    }

    /**
     * Forgot Password Request
     *
     * @return \Illuminate\Http\Response
    */
    public function requestReset(Request $request) 
    {
        $credentials = $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink($credentials);

        if($status !== Password::RESET_LINK_SENT)
        {
            return response()->json(['message' => 'Email not found'], 400);
        }

        return response()->json(['message' => 'Reset password link sent to your email.'], 200);
    }

    /**
     * Reset Password
     *
     * @return \Illuminate\Http\Response
    */
    public function resetPassword(Request $request) 
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $reset_password_status = Password::reset($credentials, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return response()->json(['message' => "Invalid token provided"], 400);
        }

        return response()->json(['message' => "Password has been successfully changed"]);
    }


    /**
     * Redirect the user to the Provider authentication page.
     *
     * @param $provider
     * @return JsonResponse
     */
    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from Provider.
     *
     * @param $provider
     * @return JsonResponse
     */
    public function handleProviderCallback($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }
        try {
            $user = Socialite::driver($provider)->stateless()->user();
        } catch (ClientException $exception) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        //get user or create it
        $userCreated = User::firstWhere('email', $user->getEmail());

        if(!$userCreated)
        {
            $userCreated = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'email_verified_at' => now()
            ]);

            //Add to mailing list
            $client = new \GuzzleHttp\Client();
            $endpoint = 'https://api.sendinblue.com/v3/contacts';

            $response = $client->request('POST', $endpoint,[
                'headers' => [
                    'api-key' => env('SENDINBLUE_API_KEY')
                ],
                'json' => [
                    'email' => $userCreated->email,
                    'attributes' => ['PRENOM' => $userCreated->name],
                    'listIds' => [2],
                    'updateEnabled' => true
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $userCreated->sendinblue_id = $data['id'];
        }

        //update user avatar
        $userCreated->avatar = $user->getAvatar();
        $userCreated->save();

        $userCreated->providers()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => $user->getId(),
            ],
            [
                'avatar' => $user->getAvatar()
            ]
        );
     
        Auth::login($userCreated);

        return response()->json(['user' => Auth::user()], 200);
    }

    /**
     * @param $provider
     * @return JsonResponse
     */
    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'linkedin', 'google'])) {
            return response()->json(['error' => 'Please login using facebook, github or google'], 422);
        }
    }

}
