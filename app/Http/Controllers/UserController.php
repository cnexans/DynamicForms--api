<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use App\User;
use App\Models\Form as Form;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('oauth');
        $this->middleware('admin');
    }


    private function insertUser($role,$request){
        if ( ! ($request->has('email') and $request->has('password')) ){

            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'No email or password provided'
            ], 401);
        }

        if (User::alreadyExists($request->input('email')))
        {
            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'That user exists already'
            ], 401);
        }

        $user = new User;
        if ($request->has('name')) $user->name = $request->input('name');
        $user->password   = $request->input('password');
        $user->email      = $request->input('email');
        $user->membership = $role;
        $user->save();

        return response()->json([
                'success' => true,
                'message' => 'Success']);
    }
    
    /**
     * Insertar un usuario de tipo empleado en el sistema
     * solo se requiere ser adminstrador.
     *
     * @return \Illuminate\Http\Response
     */
    public function newEmployee(Request $request)
    {
        if (User::alreadyExists($request->input('email'))){
            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'That user exists already'
            ], 401);
        }

        return $this->insertUser("employee",$request);
    }
    
    /**
     * Insertar un usuario de tipo manager en el sistema
     * Se requiere ser president
     *
     * @return \Illuminate\Http\Response
     */
    public function newManager(Request $request)
    {
        
        if ( !User::isPresident($request->input('user_id')) )
            return response()->json([
                'error'   => "401",
                'success' => false,
                'message' => 'You must be a president user to do that'
            ], 401);
        
        return $this->insertUser("manager",$request);
    }

    /**
     * Listar todos los usuarios del sistema
     *
     * @return \Illuminate\Http\Response
     */
    public function listAll(Request $request)
    {
        return response()->json(User::all());
    }
    
    /**
     * Listar todos los usuarios del sistema por rol
     *
     * @param rol: tipo del rol a buscar
     * @return \Illuminate\Http\Response
     */
    public function listWithRole(Request $request)
    {
        if ( !$request->has('role') ){
            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'No role found'
            ], 401);
        }
        
        return response()->json(User::getAllWithRole($request->input('role')));
    }

    /**
     * Soft delete de un usuario dado su correo
     *
     * @param email: email del usuario
     * @return \Illuminate\Http\Response
     */
     
    public function remove(Request $request)
    {
        if ( !$request->has('email') ){
            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'No email found'
            ], 401);
        }

        if (!User::alreadyExists($request->input('email'))){
            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'That user doesn\'t exists'
            ], 401);
        }

        $user = User::where('email', $request->input('email'))->get()[0];

        if ($user->membership == "president") {
            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'You cannot delete a president'
            ], 401);
        }
        if (  $user->membership == "manager" && !User::isPresident($request->input('user_id')) )
        {

            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'Not enough privileges'
            ], 401);
            
        }

           
        $user->delete();


        if ( $result ) {
            return response()->json([
                'success' => true,
                'message' => 'Success']);
        } else {
            return response()->json([
                'success' => false,
                'error'   => 401,
                'message' => 'Something happened ¯\(o_.)/¯'
            ], 401);
        }

    }


    public function attachUserToForm(Request $request, $user_id)
    {
        if ( is_null($user_id) )
            return response()->json([
                'message' => 'user_id not found',
                'error'   => 401,
                'success' => false
            ], 401);

        if ( !$request->has('form_id') )
            return response()->json([
                'message' => 'form_id not found',
                'error'   => 401,
                'success' => false
            ], 401);

        $form_id = $request->input('form_id');

        if ( !User::find($user_id) || !Form::find($form_id) )
            return response()->json([
                'message' => 'User or form doesnt exist',
                'error'   => '401',
                'success' => false
            ], 401);


        if ( !User::find($user_id)->forms()->find($form_id) )
            User::find($user_id)->forms()->attach($form_id);


        return response()->json([
            'success' => true
        ], 200);
    }

    public function detachUserToForm(Request $request, $user_id)
    {
        if ( is_null($user_id) )
            return response()->json([
                'message' => 'user_id not found',
                'error'   => 401,
                'success' => false
            ], 401);

        if ( !$request->has('form_id') )
            return response()->json([
                'message' => 'form_id not found',
                'error'   => 401,
                'success' => false
            ], 401);

        $form_id = $request->input('form_id');

        if ( !User::find($user_id) || !Form::find($form_id) )
            return response()->json([
                'message' => 'User or form doesnt exist',
                'error'   => '401',
                'success' => false
            ], 401);


        User::find($user_id)->forms()->detach($form_id);


        return response()->json([
            'success' => true
        ], 200);
    }

    public function attachedForms(Request $request)
    {
        if ( !$request->has('requested_user_id') )
            return response()->json([
                'success' => false,
                'error'   => '401',
                'message' => 'requested_user_id not found'
            ], 401);


        $forms = User::find( $request->input('requested_user_id') )->forms()->get();

        return response()->json($forms->toArray(), 200);
    }

    public function userProfile($id)
    {
        $user = User::withTrashed()->find( $id );

        if ( !$user )
            return response()->json([
                'error'   => '401',
                'success' => false,
                'message' => 'El usuario no existe'
            ], 401);

        return response()->json($user, 200);
    }

    public function editUser(Request $request, $id)
    {
        $user = User::find( $id );

        if ( !$user )
            return response()->json([
                'error'   => '401',
                'success' => false,
                'message' => 'El usuario no existe'
            ], 401);

        if ( ($request->has('name') && $user != null) && $user->role != 'president' )
        {
            $user->name = $request->input('name');
            $user->save();
        }

        return response()->json($user, 200);
    }
}
