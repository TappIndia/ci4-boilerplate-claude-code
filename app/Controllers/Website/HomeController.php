<?php

// app/Controllers/Website/HomeController.php

namespace App\Controllers\Website;

use App\Controllers\BaseController;
use App\Models\SettingModel;

class HomeController extends BaseController
{
    private SettingModel $settingModel;

    protected array $helpers = ['url', 'form', 'session'];

    public function __construct()
    {
        $this->settingModel = model(SettingModel::class);
    }

    private function siteData(): array
    {
        return $this->settingModel->getGroup('general');
    }

    // GET /
    public function index(): string
    {
        return view('website/home', ['site' => $this->siteData()]);
    }

    // GET /about
    public function about(): string
    {
        return view('website/about', ['site' => $this->siteData()]);
    }

    // GET /contact
    public function contact(): string
    {
        return view('website/contact', ['site' => $this->siteData()]);
    }

    // POST /contact
    public function contactSubmit()
    {
        $rules = [
            'name'    => 'required|max_length[100]',
            'email'   => 'required|valid_email',
            'message' => 'required|min_length[10]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        // TODO: Send email / store in DB
        $this->logActivity('contact_form', 'website', 'Contact form submitted');

        return redirect()->route('contact')
                         ->with('success', 'Thanks! We will get back to you soon.');
    }
}
