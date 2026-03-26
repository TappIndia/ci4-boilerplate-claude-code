<?php

// app/Controllers/Admin/SettingController.php

namespace App\Controllers\Admin;

use App\Models\SettingModel;

class SettingController extends AdminBaseController
{
    private SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = model(SettingModel::class);
    }

    // GET /admin/settings
    public function index(): string
    {
        $this->authorize('settings.read');

        $groups = [
            'general'  => $this->settingModel->where('group', 'general')->findAll(),
            'email'    => $this->settingModel->where('group', 'email')->findAll(),
            'security' => $this->settingModel->where('group', 'security')->findAll(),
        ];

        return $this->render('admin/settings/index', [
            'page_title' => 'Settings',
            'groups'     => $groups,
        ]);
    }

    // POST /admin/settings/save
    public function save()
    {
        $this->authorize('settings.update');

        $data = $this->request->getPost();
        unset($data[csrf_token()]);  // Remove CSRF field

        foreach ($data as $key => $value) {
            // Handle file uploads separately via FileController
            if (is_array($value)) continue;
            $this->settingModel->where('key', $key)->set(['value' => $value])->update();
        }

        $this->logActivity('update_settings', 'settings');

        return redirect()->route('admin.settings')
                         ->with('success', 'Settings saved successfully.');
    }
}
