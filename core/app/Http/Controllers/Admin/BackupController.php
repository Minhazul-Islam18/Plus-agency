<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;
use App\Backup;
use Session;

class BackupController extends Controller
{
    public function index() {
      $data['backups'] = Backup::orderBy('id', 'DESC')->paginate(10);
      return view('admin.backup', $data);
    }

    public function store() {
      $filename = uniqid() . '.sql';
      $backupPath = storage_path('app/public/' . $filename);

      // Ensure directory exists
      if (!file_exists(storage_path('app/public'))) {
          mkdir(storage_path('app/public'), 0775, true);
      }

      $process = Process::fromShellCommandline(sprintf(
          'mysqldump -u%s -p%s %s > %s',
          config('database.connections.mysql.username'),
          config('database.connections.mysql.password'),
          config('database.connections.mysql.database'),
          $backupPath
      ));
      $process->mustRun();

      $backup = new Backup;
      $backup->filename = $filename;
      $backup->save();

      Session::flash('success', 'Backup saved successfully');
      return back();
    }

    public function download(Request $request) {
      return response()->download(storage_path('app/public/'.$request->filename), 'backup.sql');
    }

    public function delete($id) {
      $backup = Backup::find($id);
      @unlink(storage_path('app/public/'.$backup->filename));
      $backup->delete();

      Session::flash('success', 'Database sql file deleted successfully!');
      return back();
    }
}
