<?php

namespace App\Http\Repositories;

use App\Events\ChangeStatutAgentEvent;
use App\Models\Setting;
use App\Models\EntiteAdmin;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class EntiteAdminRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var EntiteAdmin
     */
    protected $model;

    /**
     * The model being queried.
     *
     * @var EquipeService
     */
    protected $es;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(EntiteAdmin::class);
    }

    /**
     * Check if EntiteAdmin exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all EntiteAdmins with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = EntiteAdmin::ignoreRequest(['per_page', 'categorie', 'project_id', 'ids', 'role'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');


        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }

    /**
     * Get all EntiteAdmins with filtering, pagination, and sorting
     */
    // public function getAllRH($request)
    // {
    //     $per_page = 10;
    //     $roleIds = [];

    //     $req = EntiteAdmin::orderByDesc('created_at');
    //     $roleIds[] = (int) Setting::where('key', 'role_for_animatrice')->first()?->value;
    //     $roleIds[] = (int) Setting::where('key', 'role_for_responsable')->first()?->value;
    //     $req->whereHas('EntiteAdminProjects.roles', function ($q) use ($roleIds) {
    //         $q->whereIn('id', $roleIds);
    //     });
    //     if (array_key_exists('project_id', $request->all())) {
    //         $project_id = $request->project_id;
    //         $req->whereHas('EntiteAdminProjects', function ($q) use ($project_id) {
    //             $q->where('project_id', $project_id);
    //         });
    //     }

    //     if (array_key_exists('per_page', $request->all())) {
    //         $per_page = $request['per_page'];

    //         return $req->paginate($per_page);
    //     } else {
    //         return $req->get();
    //     }
    // }

    /**
     * Get a specific EntiteAdmin by id
     */
    public function get($id)
    {

        try {
            if (request()->has('project_id')) {
                return $this->whereHas('EntiteAdminProject', function ($q) {
                    $q->where('project_id', request()->project_id);
                })->findOrFail($id)->load('EntiteAdminProject.roles');
            } else {
                return $this->findOrFail($id);

            }
        } catch (\Throwable $th) {
            info($th->getMessage());

            return null;
        }

        // try {
        //     $query = self::query();
        //     if (request()->has('project_id')) {
        //         $project_id = request()->project_id;
        //         $query->whereHas('EntiteAdminProjects', function ($q) use ($project_id) {
        //             $q->where('project_id', $project_id);
        //             if (request()->has('role')) {
        //                 $role = request()->role;
        //                // $q->role($role);
        //                 $q->whereHas('roles', function ($qu) use ($role) {
        //                     $qu->where('id', $role);
        //                 });
        //             }
        //         });

        //     }

        //     return $query->findOrFail($id);
        // } catch (\Throwable $th) {
        //     info($th->getMessage());

        //     return null;
        // }
    }

    /**
     * Get a specific EntiteAdmin by id
     */
    public function getRH($id)
    {

        $equipe = $this->es->getEquipe($id);

        if (request()->has('project_id')) {
            $result = $this->findOrFail($id)->load(['EntiteAdminProjects.roles', 'municipality.department']);
        } else {
            $result = $this->findOrFail($id)->load(['EntiteAdminProjects.roles', 'municipality.department']);

        }

        // foreach ($equipe['data'] as $key => $value) {
        //     $equipe['data'][$key]['municipality'] = Municipality::find($value['municipality_id'])?->load('department');
        // }

        $result->setAttribute('equipe', $equipe['data']);

        return $result;
    }


    /**
     * Store a new EntiteAdmin
     */
    public function makeStore($data): EntiteAdmin
    {

        $model = new EntiteAdmin($data);
        $model->save();

        return $model;
    }

    public function makeStorePR($data): EntiteAdmin
    {

        $role = (int) Setting::where('key', 'role_for_pr')->first()?->value;
        $statutAgentId = (int) Setting::where('key', 'statut_agent_for_pr')->first()?->value;
        $projectId = $data['project_id'];
        $data['statut_agent_id'] = $statutAgentId;
        unset($data['role']);
        unset($data['project_id']);

        $check_EntiteAdmin = EntiteAdmin::where('email', $data['email'])->first();

        if ($check_EntiteAdmin == null) {
            $password = Str::random(8);
            $data['password'] = Hash::make($password);
            $model = new EntiteAdmin($data);
            $model->save();

            $role = Role::find($role);
            $EntiteAdminProject = EntiteAdminProject::create([
                'EntiteAdmin_id' => $model->id,
                'project_id' => $projectId,
            ]);
            $EntiteAdminProject->assignRole($role);

            Mailer::sendSimple('emails.new_account', ['EntiteAdmin' => $model, 'password' => $password], 'Identifiant de connexion', $model->name, $model->email);

            // SendEmailJob::dispatch($model, $password);
        } else {
            unset($data['password']);
            $model = $check_EntiteAdmin;
            $model->update($data);
        }

        return $model;
    }

    /**
     * Update an existing EntiteAdmin
     */
    public function makeUpdate($id, $data): EntiteAdmin
    {
        $model = EntiteAdmin::findOrFail($id);

        if (request()->hasFile('photo')) {
            FileStorage::deleteFile('public', $model->photo);
            $filename = FileStorage::setFile('public', request()->file('photo'), 'avatars', Str::slug($data['lastname'].'.'.$data['firstname'].'.'.time()));
            $data['photo'] = 'avatars/'.$filename;
        }

        if (request()->hasFile('cv')) {
            $filename = FileStorage::setFile('public', request()->file('cv'), 'avatars', Str::slug($data['lastname'].'.'.$data['firstname'].'.'.time()));
            $data['cv'] = 'cv/'.$filename;
        }

        $oldStatus = $model->statut_agent_id;

        // if (array_key_exists('projects',$data)) {
        //     $project=$data['projects'];
        //     unset($data['projects']);
        // }

        $model->update($data);

        if ($oldStatus != $data['statut_agent_id']) {
            ChangeStatutAgentEvent::dispatch($model, $oldStatus, $data['statut_agent_id']);
        }

        // $model->projects()->detach();

        // foreach ($project as $projectId) {
        //     EntiteAdminProject::create(['project_id'=>$projectId,'EntiteAdmin_id'=>$model->id]);
        // }
        return $model;
    }

    /**
     * Delete a EntiteAdmin
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest EntiteAdmins
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
     * Search for EntiteAdmins by name, email, or code
     */
    public function search($term)
    {
        $query = EntiteAdmin::query(); // Start with an empty query
        $attrs = ['name', 'email', 'code']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
