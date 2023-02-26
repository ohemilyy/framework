<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\Blueprint;

use Illuminate\View\View;
use Illuminate\View\Factory as ViewFactory;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Console\Kernel;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Helpers\SoftwareVersionService;
use Pterodactyl\Services\Helpers\BlueprintVariableService;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Http\Requests\Admin\Extensions\Blueprint\BlueprintSettingsFormRequest;
use Illuminate\Http\RedirectResponse;

class BlueprintExtensionController extends Controller
{

    /**
     * BlueprintExtensionController constructor.
     */
    public function __construct(
        private BlueprintVariableService $bp,

        private SoftwareVersionService $version,
        private ViewFactory $view,
        private Kernel $kernel,
        private AlertsMessageBag $alert,
        private ConfigRepository $config,
        private SettingsRepositoryInterface $settings,
        ) {
    }

    /**
     * Return the admin index view.
     */
    public function index(): View
    {
        $rootPath = "/admin/extensions/blueprint";
        $license = $this->bp->licenseIsValid();
        return $this->view->make(
            'admin.extensions.blueprint.index', [
                'version' => $this->version,
                'bp' => $this->bp,
                'root' => $rootPath,
                'license' => $license
            ]
        );
    }

    /**
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function update(BlueprintSettingsFormRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('blueprint::' . $key, $value);
        }

        //$this->kernel->call('queue:restart');
        //$this->alert->success('Blueprint settings have updated successfully.')->flash();

        return redirect()->route('admin.extensions.blueprint.index');
    }
}
