<?php

namespace App\Listeners;

use App\Events\ChangeStatutAgentEvent;
use App\Http\Repositories\UserAuthRepository;
use App\Http\Repositories\UserRepository;
use App\Models\Setting;
use App\Models\User;
use App\Services\StatutAgentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;

class ChangeStatutAgentListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'notifications';  // Remplace par ta file d'attente

    public $connection = 'database';

    /**
     * The Setting repository being queried.
     *
     * @var UserAuthRepository
     */
    protected $userauthRepository;

    /**
     * The Setting repository being queried.
     *
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * Create the event listener.
     */
    public function __construct(UserAuthRepository $userAuthRepository, UserRepository $userRepository)
    {
        $this->userauthRepository = $userAuthRepository;
        $this->userRepository = $userRepository;

    }

    /**
     * Handle the event.
     */
    public function handle(ChangeStatutAgentEvent $event): void
    {

        $sas = new StatutAgentService;

        $settings = Setting::all();

        $roleForRp = optional($settings->firstWhere('key', 'role_for_rp'))->value;
        $roleForAdmin = optional($settings->firstWhere('key', 'role_for_admin'))->value;

        $project_id = 1;

        $admins = [];
        if ($roleForAdmin != null) {
            $admins = User::whereHas('userProjects', function ($q) use ($project_id, $roleForAdmin) {
                $q->where('project_id', $project_id)
                    ->whereHas('roles', function ($qu) use ($roleForAdmin) {
                        $qu->where('id', $roleForAdmin);
                    });
            })->get()->pluck('id')->toArray();
        }
        $rps = [];
        if ($roleForRp != null) {
            $rps = User::whereHas('userProjects', function ($q) use ($project_id, $roleForRp) {
                $q->where('project_id', $project_id)
                    ->whereHas('roles', function ($qu) use ($roleForRp) {
                        $qu->where('id', $roleForRp);
                    });
            })->get()->pluck('id')->toArray();
        }

        $role = $event->user?->userProject?->roles?->first()?->name;

        $status = optional($sas->getStatutAgent($event->user?->statut_agent_id))['data'];
        $response = $this->userauthRepository->notify(new Request([
            'category' => 'change-status-agent',
            'canal' => ['DATABASE', 'MAIL'],
            'title' => "Modification du {$role}",
            'content' => "Le Statut de « {$role}» « {$event->user?->name}» a été changé. Elle a désormais le statut « {$status['libelle']}».",
            'users' => array_merge($rps, $admins),
        ]));
    }
}
