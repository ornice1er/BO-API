<?php

namespace App\Http\Repositories;

use App\Events\ChangeStatutAgentEvent;
use App\Jobs\SendEmailJob;
use App\Models\Municipality;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserPrestation;
use App\Services\EquipeService;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Auth;

class UserRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var User
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(User::class);
    }

    /**
     * Check if user exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all users with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = User::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with(['roles.permissions','agent.uniteAdmin','userPrestations'])
            ->orderByDesc('created_at');


        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }

 

    /**
     * Get a specific user by id
     */
    public function get($id)
    {

        try {
            if (request()->has('project_id')) {
                return $this->whereHas('userProject', function ($q) {
                    $q->where('project_id', request()->project_id);
                })->findOrFail($id)->load('userProject.roles');
            } else {
                return $this->findOrFail($id);

            }
        } catch (\Throwable $th) {
            info($th->getMessage());

            return null;
        }

    }


    /**
     * Store a new user
     */
    public function makeStore($data): User
    {
      
    
        $roles = $data['roles'];
        $choices = $data['choices'];
        unset($data['roles']);
        unset($data['choices']);
        $password = Str::random(8);
        $data['password'] = Hash::make($password);
        $model = new User($data);
        $model->save();

        $role = Role::firstOrCreate(['name' => $roles[0]]);

        $model->assignRole($role);


        foreach ($choices as $value) {
            UserPrestation::create([ 'user_id'=>$user->id, 'prestation_id'=>$value]);
        }

        Mailer::sendSimple('emails.new_account', ['user' => $model, 'password' => $password], 'Identifiant de connexion', $model->name, $model->email);

        // SendEmailJob::dispatch($model, $password);
        return $model;
    }


    /**
     * Update an existing user
     */
    public function makeUpdate($id, $data): User
    {
        $user=User::find($id);

         $roles = $data['roles'];
        $choices = $data['choices'];
        unset($data['roles']);
        unset($data['choices']);
        if(Auth::user()->hasRole('Admin national')){
            $user->update($data);
        return $user;   

        }else
        if(Auth::user()->hasRole('Admin Sectoriel')){
            $prestations=   $user->userprestations;

            foreach ($prestations as $value) {
                $value->delete();
            }
            $user->update($data);
            foreach ($choices as $value) {
                UserPrestation::create([ 'user_id'=>$user->id, 'prestation_id'=>$value]);
            }
        return $user;   

        }
        return $user;   
    
    }

    /**
     * Delete a user
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest users
     */
    public function getLatest()
    {
        return $this->latest()->get();
    }

    public function setStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['is_active' => $status]);
    }

    /**
     * Search for users by name, email, or code
     */
    public function search($term)
    {
        $query = User::query(); // Start with an empty query
        $attrs = ['name', 'email', 'code']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
