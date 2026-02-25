<?php

namespace App\Http\Repositories;

use App\Events\ChangeStatutAgentEvent;
use App\Models\Setting;
use App\Models\Affectation;
use App\Models\UniteAdmin;
use App\Models\Requete;
use App\Models\Parcours;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use App\Utilities\Mailer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Auth;


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

        $req = Affectation::ignoreRequest(['per_page'])
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
     * Store a new Affectation
     */
    public function makeStore($data)
    {

      if ($data['sens'] == 1) {
    $ua_up = UniteAdmin::with('typeUniteAdmin')->find($data['unite_admin_id']);

    if (isset($data['unite_admin_down_id'])) {
        $ua_down = UniteAdmin::find($data['unite_admin_down_id']);
    } else {
        $ua_down = UniteAdmin::where('ua_parent_code', $data['unite_admin_id'])->first();
    }

    $check = Affectation::where('requete_id', $data['requete_id'])
        ->where('isLast', true)
        ->first();

    if ($check) {
        $check->update(['isLast' => false]);
    }

    $newReq = Requete::find($data['requete_id']);

    // Resolve current officer posts for up/down units (optional)
    $copUp = null;
    $copDown = null;
    if ($data['unite_admin_id'] ?? null) {
        $copUp = \App\Models\CurrentOfficerPost::where('unite_admin_id', $data['unite_admin_id'])
            ->whereRaw('LOWER(statut) = ?', ['active'])->first();
    }
    if ($ua_down?->id) {
        $copDown = \App\Models\CurrentOfficerPost::where('unite_admin_id', $ua_down->id)
            ->whereRaw('LOWER(statut) = ?', ['active'])->first();
    }

    Affectation::create([
        'unite_admin_up'   => $data['unite_admin_id'],
        'unite_admin_down' => $ua_down?->id,
        'requete_id'       => $data['requete_id'],
        'sens'             => $data['sens'],
        'instruction'      => $data['instruction'] ?? null,
        'delay'            => isset($data['delay']) ? date_create($data['delay']) : null,
        'cop_up'           => $copUp?->id,
        'cop_down'         => $copDown?->id,
    ]);

    Parcours::create([
        'libelle'     => "Affectation de la demande " . $newReq->code . " par le/la " . $ua_up->libelle . " au/à la " . $ua_down->libelle,
        'requete_id'  => $newReq->id,
        'user_id'     => Auth::id(),
    ]);
    if($newReq->status==0){
        $newReq->status=1;
        $newReq->save();
    }
    if (in_array($ua_up->typeUniteAdmin->libelle, ['Structure', 'Direction'])) {
        $newReq->update(['isTreated' => false]);
    }
} else {
    $ua_up = UniteAdmin::find($data['unite_admin_id']);
    $ua_down = UniteAdmin::with('typeUniteAdmin')->find($ua_up->ua_parent_code);

    $check = Affectation::where('requete_id', $data['requete_id'])
        ->where('isLast', true)
        ->first();

    if ($check) {
        $check->update(['isLast' => false]);
    }

    $newReq = Requete::find($data['requete_id']);

    // Resolve current officer posts for up/down units (optional)
    $copUp = null;
    $copDown = null;
    if ($data['unite_admin_id'] ?? null) {
        $copUp = \App\Models\CurrentOfficerPost::where('unite_admin_id', $data['unite_admin_id'])
            ->whereRaw('LOWER(statut) = ?', ['active'])->first();
    }
    if ($ua_down?->id) {
        $copDown = \App\Models\CurrentOfficerPost::where('unite_admin_id', $ua_down->id)
            ->whereRaw('LOWER(statut) = ?', ['active'])->first();
    }

    Affectation::create([
        'unite_admin_up'   => $data['unite_admin_id'],
        'unite_admin_down' => $ua_down?->id,
        'requete_id'       => $data['requete_id'],
        'sens'             => $data['sens'],
        'cop_up'           => $copUp?->id,
        'cop_down'         => $copDown?->id,
    ]);

    Parcours::create([
        'libelle'     => "Transmission du projet de réponse par le/la " . $ua_up->libelle . " au/à la " . $ua_down->libelle,
        'requete_id'  => $newReq->id,
        'user_id'     => Auth::id(),
    ]);
}
return true;
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
