<?php

// app/Controllers/Admin/FileController.php

namespace App\Controllers\Admin;

use App\Models\FileModel;

class FileController extends AdminBaseController
{
    private FileModel $fileModel;

    // Allowed mime types per category
    private array $allowedMimes = [
        'image'    => ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'],
        'document' => ['application/pdf','application/msword',
                       'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                       'application/vnd.ms-excel',
                       'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                       'text/plain','text/csv'],
        'video'    => ['video/mp4','video/mpeg','video/webm'],
        'audio'    => ['audio/mpeg','audio/ogg','audio/wav'],
    ];

    public function __construct()
    {
        $this->fileModel = model(FileModel::class);
    }

    // GET /admin/files
    public function index(): string
    {
        $this->authorize('files.read');

        $type   = $this->request->getGet('type');
        $search = $this->request->getGet('search');

        if ($type) $this->fileModel->where('type', $type);
        if ($search) $this->fileModel->like('original_name', $search);

        $this->fileModel->where('deleted_at', null)->orderBy('created_at', 'DESC');
        ['data' => $files, 'pager' => $pager] = $this->paginate($this->fileModel, 24);

        return $this->render('admin/files/index', [
            'page_title' => 'Files',
            'files'      => $files,
            'pager'      => $pager,
            'type'       => $type,
            'search'     => $search,
        ]);
    }

    // POST /admin/files/upload
    public function upload()
    {
        $this->authorize('files.create');

        $file = $this->request->getFile('file');

        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'No valid file uploaded.'])->setStatusCode(422);
        }

        if ($file->hasMoved()) {
            return $this->response->setJSON(['success' => false, 'message' => 'File already moved.'])->setStatusCode(422);
        }

        // Size limit: 10 MB
        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->response->setJSON(['success' => false, 'message' => 'File exceeds 10 MB limit.'])->setStatusCode(422);
        }

        $mime     = $file->getMimeType();
        $type     = $this->detectType($mime);
        $subDir   = date('Y/m');
        $destDir  = FCPATH . "uploads/{$subDir}";
        $newName  = $file->getRandomName();

        if (! is_dir($destDir)) mkdir($destDir, 0755, true);

        if (! $file->move($destDir, $newName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Upload failed.'])->setStatusCode(500);
        }

        $path = "uploads/{$subDir}/{$newName}";

        $fileId = $this->fileModel->insert([
            'user_id'       => $this->currentUserId(),
            'module'        => $this->request->getPost('module'),
            'module_id'     => $this->request->getPost('module_id'),
            'disk'          => 'local',
            'path'          => $path,
            'filename'      => $newName,
            'original_name' => $file->getClientName(),
            'mime_type'     => $mime,
            'extension'     => strtolower($file->getClientExtension()),
            'size'          => $file->getSize(),
            'type'          => $type,
            'is_public'     => 1,
        ]);

        $this->logActivity('upload_file', 'files', "Uploaded {$file->getClientName()}");

        return $this->response->setJSON([
            'success'  => true,
            'file_id'  => $fileId,
            'url'      => base_url($path),
            'path'     => $path,
            'name'     => $file->getClientName(),
            'type'     => $type,
        ]);
    }

    // POST /admin/files/:id/delete
    public function delete(int $id)
    {
        $this->authorize('files.delete');

        $file = $this->fileModel->find($id);
        if (! $file) {
            return redirect()->back()->with('error', 'File not found.');
        }

        // Remove physical file
        $fullPath = FCPATH . $file['path'];
        if (file_exists($fullPath)) unlink($fullPath);

        // Soft delete record
        $this->fileModel->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        $this->logActivity('delete_file', 'files', "Deleted file ID {$id}");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->route('admin.files.index')->with('success', 'File deleted.');
    }

    private function detectType(string $mime): string
    {
        foreach ($this->allowedMimes as $type => $mimes) {
            if (in_array($mime, $mimes)) return $type;
        }
        return 'other';
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// app/Models/FileModel.php
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table         = 'files';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes= true;
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = [
        'user_id','module','module_id','disk','path','filename',
        'original_name','mime_type','extension','size','type','is_public',
    ];

    /** Get files attached to a specific module record. */
    public function getForModule(string $module, int $moduleId, string $type = null): array
    {
        $q = $this->where('module', $module)
                  ->where('module_id', $moduleId)
                  ->where('deleted_at', null);

        if ($type) $q->where('type', $type);

        return $q->orderBy('created_at', 'ASC')->findAll();
    }

    /** Human-readable file size. */
    public function humanSize(int $bytes): string
    {
        $units = ['B','KB','MB','GB'];
        $i     = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}


// ══════════════════════════════════════════════════════════════════════════════
// app/Models/PermissionModel.php
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table         = 'permissions';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['name','module','action','label'];

    /** Seed default CRUD permissions for a module. */
    public function seedForModule(string $module): void
    {
        $actions = ['create','read','update','delete'];
        foreach ($actions as $action) {
            $name = "{$module}.{$action}";
            if ($this->where('name', $name)->first()) continue;

            $this->insert([
                'name'   => $name,
                'module' => $module,
                'action' => $action,
                'label'  => ucfirst($action) . ' ' . ucfirst($module),
            ]);
        }
    }
}
