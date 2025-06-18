<?php

namespace App\Http\Repositories;

use App\Events\ChangeStatutAgentEvent;
use App\Models\Setting;
use App\Models\Affectation;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AffectationRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Affectation
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
        $this->model = app(Affectation::class);
    }

    /**
     * Check if Affectation exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all Affectations with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Affectation::ignoreRequest(['per_page', 'categorie', 'project_id', 'ids', 'role'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with('projects', 'AffectationProjects.roles')
            ->orderByDesc('created_at');

        if (array_key_exists('categorie', $request->all())) {
            if ($request->categorie == 'ANIMATRICE') {
                $roleId = (int) Setting::where('key', 'role_for_animatrice')->first()?->value;
            } elseif ($request->categorie == 'RP') {
                $roleId = (int) Setting::where('key', 'role_for_rp')->first()?->value;
            } else {
                $roleId = (int) Setting::where('key', 'role_for_responsable')->first()?->value;
            }

            $req->whereHas('AffectationProjects.roles', function ($q) use ($roleId) {
                $q->where('id', $roleId);
            });
        }

        if (array_key_exists('ids', $request->all())) {
            $req = $req->whereIn('id', explode(',', $request['ids']));
        }

        if (array_key_exists('project_id', $request->all())) {
            $project_id = $request->project_id;
            $req->whereHas('AffectationProjects', function ($q) use ($project_id) {
                $q->where('project_id', $project_id);
                if (request()->has('role')) {
                    $role = request()->role;
                    $q->whereHas('roles', function ($qu) use ($role) {
                        $qu->where('id', $role);
                    });
                }
            });
        }

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }

    /**
     * Get all Affectations with filtering, pagination, and sorting
     */
    public function getAllRH($request)
    {
        $per_page = 10;
        $roleIds = [];

        $req = Affectation::orderByDesc('created_at');
        $roleIds[] = (int) Setting::where('key', 'role_for_animatrice')->first()?->value;
        $roleIds[] = (int) Setting::where('key', 'role_for_responsable')->first()?->value;
        $req->whereHas('AffectationProjects.roles', function ($q) use ($roleIds) {
            $q->whereIn('id', $roleIds);
        });
        if (array_key_exists('project_id', $request->all())) {
            $project_id = $request->project_id;
            $req->whereHas('AffectationProjects', function ($q) use ($project_id) {
                $q->where('project_id', $project_id);
            });
        }

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }

    /**
     * Get a specific Affectation by id
     */
    public function get($id)
    {

        try {
            if (request()->has('project_id')) {
                return $this->whereHas('AffectationProject', function ($q) {
                    $q->where('project_id', request()->project_id);
                })->findOrFail($id)->load('AffectationProject.roles');
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
        //         $query->whereHas('AffectationProjects', function ($q) use ($project_id) {
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
     * Get a specific Affectation by id
     */
    public function getRH($id)
    {

        $equipe = $this->es->getEquipe($id);

        if (request()->has('project_id')) {
            $result = $this->findOrFail($id)->load(['AffectationProjects.roles', 'municipality.department']);
        } else {
            $result = $this->findOrFail($id)->load(['AffectationProjects.roles', 'municipality.department']);

        }

        // foreach ($equipe['data'] as $key => $value) {
        //     $equipe['data'][$key]['municipality'] = Municipality::find($value['municipality_id'])?->load('department');
        // }

        $result->setAttribute('equipe', $equipe['data']);

        return $result;
    }


    /**
     * Store a new Affectation
     */
    public function makeStore($data): Affectation
    {

        $model = new Affectation($data);
        $model->save();

        return $model;
    }

    public function makeStorePR($data): Affectation
    {

        $role = (int) Setting::where('key', 'role_for_pr')->first()?->value;
        $statutAgentId = (int) Setting::where('key', 'statut_agent_for_pr')->first()?->value;
        $projectId = $data['project_id'];
        $data['statut_agent_id'] = $statutAgentId;
        unset($data['role']);
        unset($data['project_id']);

        $check_Affectation = Affectation::where('email', $data['email'])->first();

        if ($check_Affectation == null) {
            $password = Str::random(8);
            $data['password'] = Hash::make($password);
            $model = new Affectation($data);
            $model->save();

            $role = Role::find($role);
            $AffectationProject = AffectationProject::create([
                'Affectation_id' => $model->id,
                'project_id' => $projectId,
            ]);
            $AffectationProject->assignRole($role);

            Mailer::sendSimple('emails.new_account', ['Affectation' => $model, 'password' => $password], 'Identifiant de connexion', $model->name, $model->email);

            // SendEmailJob::dispatch($model, $password);
        } else {
            unset($data['password']);
            $model = $check_Affectation;
            $model->update($data);
        }

        return $model;
    }

    /**
     * Update an existing Affectation
     */
    public function makeUpdate($id, $data): Affectation
    {
        $model = Affectation::findOrFail($id);

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
        //     AffectationProject::create(['project_id'=>$projectId,'Affectation_id'=>$model->id]);
        // }
        return $model;
    }

    /**
     * Delete a Affectation
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest Affectations
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
     * Search for Affectations by name, email, or code
     */
    public function search($term)
    {
        $query = Affectation::query(); // Start with an empty query
        $attrs = ['name', 'email', 'code']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
