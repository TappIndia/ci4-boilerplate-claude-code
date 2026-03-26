<?php

// app/Controllers/Admin/CrudController.php
// ============================================================
// GENERIC CRUD BASE CONTROLLER
// Extend this for any model that needs standard list/add/edit/delete.
//
// Usage:
//   class ProductController extends CrudController {
//       protected string $modelClass    = ProductModel::class;
//       protected string $module        = 'products';
//       protected string $routePrefix   = 'admin.products';
//       protected string $viewPrefix    = 'admin/products';
//       protected array  $searchFields  = ['name', 'sku'];
//       protected int    $perPage       = 15;
//   }
// ============================================================

namespace App\Controllers\Admin;

use CodeIgniter\Model;

abstract class CrudController extends AdminBaseController
{
    /** Fully-qualified model class name */
    protected string $modelClass;

    /** Permission module prefix (e.g. 'products' → products.read, products.create …) */
    protected string $module;

    /** Named route prefix (e.g. 'admin.products') */
    protected string $routePrefix;

    /** View directory prefix (e.g. 'admin/products') */
    protected string $viewPrefix;

    /** Columns to LIKE-search in index() */
    protected array $searchFields = [];

    /** Default rows per page */
    protected int $perPage = 15;

    /** Soft-delete support */
    protected bool $softDelete = true;

    /** Instantiated model (resolved in index/edit/etc.) */
    private Model $_model;

    // ------------------------------------------------------------------
    private function model(): Model
    {
        if (! isset($this->_model)) {
            $this->_model = model($this->modelClass);
        }
        return $this->_model;
    }

    // ── LIST ──────────────────────────────────────────────────────────
    public function index(): string
    {
        $this->authorize("{$this->module}.read");

        $m      = $this->model();
        $search = $this->request->getGet('search');

        if ($search && $this->searchFields) {
            $m->groupStart();
            foreach ($this->searchFields as $i => $field) {
                $i === 0 ? $m->like($field, $search) : $m->orLike($field, $search);
            }
            $m->groupEnd();
        }

        if ($this->softDelete) {
            $m->where('deleted_at', null);
        }

        $m->orderBy('created_at', 'DESC');
        ['data' => $rows, 'pager' => $pager] = $this->paginate($m, $this->perPage);

        return $this->render("{$this->viewPrefix}/index", compact('rows', 'pager', 'search'));
    }

    // ── CREATE FORM ───────────────────────────────────────────────────
    public function create(): string
    {
        $this->authorize("{$this->module}.create");
        return $this->render("{$this->viewPrefix}/form", ['record' => null]);
    }

    // ── STORE ─────────────────────────────────────────────────────────
    public function store()
    {
        $this->authorize("{$this->module}.create");

        if (! $this->validate($this->validationRules())) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $id = $this->model()->insert($this->formData());
        $this->logActivity("create_{$this->module}", $this->module, "Created ID {$id}");

        return redirect()->route("{$this->routePrefix}.index")
                         ->with('success', ucfirst($this->module) . ' created successfully.');
    }

    // ── EDIT FORM ─────────────────────────────────────────────────────
    public function edit(int $id): string
    {
        $this->authorize("{$this->module}.update");

        $record = $this->model()->find($id);
        if (! $record) return redirect()->route("{$this->routePrefix}.index")->with('error', 'Record not found.');

        return $this->render("{$this->viewPrefix}/form", compact('record'));
    }

    // ── UPDATE ────────────────────────────────────────────────────────
    public function update(int $id)
    {
        $this->authorize("{$this->module}.update");

        if (! $this->validate($this->validationRules($id))) {
            return redirect()->back()->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $this->model()->update($id, $this->formData());
        $this->logActivity("update_{$this->module}", $this->module, "Updated ID {$id}");

        return redirect()->route("{$this->routePrefix}.index")
                         ->with('success', ucfirst($this->module) . ' updated successfully.');
    }

    // ── DELETE ────────────────────────────────────────────────────────
    public function delete(int $id)
    {
        $this->authorize("{$this->module}.delete");

        if ($this->softDelete) {
            $this->model()->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->model()->delete($id);
        }

        $this->logActivity("delete_{$this->module}", $this->module, "Deleted ID {$id}");

        return redirect()->route("{$this->routePrefix}.index")
                         ->with('success', 'Record deleted.');
    }

    // ── SHOW ──────────────────────────────────────────────────────────
    public function show(int $id): string
    {
        $this->authorize("{$this->module}.read");

        $record = $this->model()->find($id);
        if (! $record) return redirect()->route("{$this->routePrefix}.index")->with('error', 'Record not found.');

        return $this->render("{$this->viewPrefix}/show", compact('record'));
    }

    // ------------------------------------------------------------------
    // Override in child controllers to customise validation rules.
    // Pass $id for update (is_unique exclusion).
    // ------------------------------------------------------------------
    protected function validationRules(?int $id = null): array
    {
        return [];
    }

    // ------------------------------------------------------------------
    // Override to map POST data → model fields.
    // ------------------------------------------------------------------
    protected function formData(): array
    {
        $post    = $this->request->getPost();
        $allowed = $this->model()->allowedFields ?? [];

        if (empty($allowed)) return $post;

        return array_intersect_key($post, array_flip($allowed));
    }
}
