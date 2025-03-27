<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\AstraTraining;
use App\Imports\EmployeeImport;
use App\Models\ExternalTraining;
use App\Models\PromotionHistory;
use App\Models\WorkingExperience;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\EducationalBackground;
use Illuminate\Support\Facades\Storage;
use App\Models\PerformanceAppraisalHistory;

class EmployeeController extends Controller
{
    
    public function getSubordinates($employeeId, $processedIds = [])
    {
        // Cegah infinite loop dengan memeriksa apakah ID sudah diproses sebelumnya
        if (in_array($employeeId, $processedIds)) {
            return collect(); // Kembalikan collection kosong untuk menghindari loop
        }

        // Tambahkan ID saat ini ke daftar yang sudah diproses
        $processedIds[] = $employeeId;

        // Ambil hanya bawahan langsung (bukan atasan)
        $employees = Employee::where('supervisor_id', $employeeId)->get();
        $subordinates = collect($employees);

        // Lanjutkan rekursi untuk mendapatkan semua bawahan di level lebih dalam
        foreach ($employees as $employee) {
            $subordinates = $subordinates->merge($this->getSubordinates($employee->id, $processedIds));
        }

        return $subordinates;
    }
    
    public function index($company = null)
    {
        $title = 'Employee';
        $user = auth()->user();

        // Jika HRD, bisa melihat semua karyawan
        if ($user->role === 'HRD') {
            $employees = Employee::with('departments')->when($company, fn($query) => $query->where('company_name', $company))->get();
        } else {
            // Jika user biasa, hanya bisa melihat bawahannya dalam satu perusahaan
            $employee = Employee::with('departments')->where('user_id', $user->id)->first();
            if (!$employee) {
                $employees = collect();
            } else {
                $employees = $this->getSubordinates($employee->id)
                    ->where('company_name', $employee->company_name);
            }
        }
        return view('website.employee.index', compact('employees', 'title'));
    }

    public function status($id)
    {
        try {
            $employee = Employee::findOrFail($id);

            // Toggle status
            $employee->is_active = !$employee->is_active;
            $employee->save();

            return redirect()->back()->with('success', 'Employee status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update employee status: ' . $e->getMessage());
        }
    }
    /**
     * Form tambah karyawan
     */
    public function create()
    {
        $title = 'Add Employee';
        $departments = Department::all();
        return view('website.employee.create', compact('title', 'departments'));
    }

    /**
     * Simpan data karyawan ke database
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        // ambil department_id 
        $departmentId = auth()->user()->employee->departments->first()->id;

        try {
            // Validasi data utama karyawan
            $validatedData = $request->validate([
                'npk' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'birthday_date' => 'required|date',
                'gender' => 'required|in:Male,Female',
                'company_name' => 'required|string',
                'aisin_entry_date' => 'required|date',
                'company_group' => 'required|string',
                'position' => 'required|string',
                'grade' => 'required|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'department_id' => 'nullable|exists:departments,id', // Pastikan department_id valid

                // Validasi Pendidikan
                'level' => 'array',
                'level.*' => 'nullable|string|max:255',
                'institute' => 'array',
                'institute.*' => 'nullable|string|max:255',
                'start_date' => 'array',
                'start_date.*' => 'nullable|string|max:255',
                'end_date' => 'array',
                'end_date.*' => 'nullable|string|max:255',

                // Validasi Pengalaman Kerja
                'company' => 'array',
                'company.*' => 'nullable|string|max:255',
                'work_position' => 'array',
                'work_position.*' => 'nullable|string|max:255',
                'work_start_date' => 'array',
                'work_start_date.*' => 'nullable|string|max:255',
                'work_end_date' => 'array',
                'work_end_date.*' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        try {
            // Simpan foto jika ada
            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $request->file('photo')->store('employee_photos', 'public');
            }

            // Hitung working period (jumlah tahun)
            $joinDate = Carbon::parse($validatedData['aisin_entry_date']);
            $now = Carbon::now();
            $validatedData['working_period'] = $joinDate->diffInYears($now);

            // Ambil user yang sedang login
            $loggedInUser = auth()->user();
            $loggedInEmployee = Employee::where('user_id', $loggedInUser->id)->first();

            // Cek apakah department_id diisi
            if (!empty($validatedData['department_id'])) {
                // Cari supervisor berdasarkan department dan position
                $supervisor = Employee::whereHas('departments', function ($query) use ($validatedData) {
                    $query->where('department_id', $validatedData['department_id']);
                })
                ->where('position', 'LIKE', '%' . $validatedData['position'] . '%')
                ->first();
            
                // Jika tidak ditemukan, kosongkan supervisor_id
                $validatedData['supervisor_id'] = $supervisor ? $supervisor->id : null;
            } else {
                // Jika department_id tidak ada, gunakan mekanisme supervisor lama
                $supervisor = $this->findSupervisor($validatedData['position'], $departmentId);

                // Jika tidak ditemukan, gunakan user yang login sebagai atasan
                if (!$supervisor && $loggedInEmployee) {
                    $supervisor = $loggedInEmployee;
                }

                $validatedData['supervisor_id'] = $supervisor ? $supervisor->id : null;
            }

            // Simpan employee ke database
            $employee = Employee::create($validatedData);

            // Simpan data pendidikan
            if ($request->filled('level')) {
                foreach ($request->level as $key => $level) {
                    if (!empty($level)) {
                        EducationalBackground::create([
                            'employee_id' => $employee->id,
                            'educational_level' => $level,
                            'major' => $request->major[$key] ?? null,
                            'institute' => $request->institute[$key] ?? null,
                            'start_date' => $request->start_date[$key] ?? null,
                            'end_date' => $request->end_date[$key] ?? null,
                        ]);
                    }
                }
            }

            // Simpan data pengalaman kerja
            if ($request->filled('company')) {
                foreach ($request->company as $key => $company) {
                    if (!empty($company)) {
                        WorkingExperience::create([
                            'employee_id' => $employee->id,
                            'company' => $company,
                            'position' => $request->position[$key] ?? null,
                            'start_date' => $request->work_start_date[$key] ?? null,
                            'end_date' => $request->work_end_date[$key] ?? null,
                        ]);
                    }
                }
            }

            // Simpan department jika tidak kosong
            if (!empty($validatedData['department_id'])) {
                DB::table('employee_departments')->insert([
                    'employee_id' => $employee->id,
                    'department_id' => $validatedData['department_id']
                ]);
            } elseif ($supervisor) {
                // Jika department_id tidak diisi, ambil dari supervisor
                $departments = DB::table('employee_departments')
                    ->where('employee_id', $supervisor->id)
                    ->pluck('department_id');

                foreach ($departments as $deptId) {
                    DB::table('employee_departments')->insert([
                        'employee_id' => $employee->id,
                        'department_id' => $deptId
                    ]);
                }
            }

             // **Tambahkan User jika Posisi Manager**
            if (strtolower($validatedData['position']) === 'manager') {
                $user = User::create([
                    'name' => $validatedData['name'],
                    'email' => strtolower($validatedData['name']) . '@aiia.co.id', // Gunakan NPK sebagai email
                    'password' => bcrypt('aiia'), // Atur password default
                ]);

                // Hubungkan Employee dengan User
                $employee->update(['user_id' => $user->id]);
            }

            DB::commit();

            return redirect()->route('employee.index')->with('success', 'Karyawan berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function findSupervisor($position, $departmentId)
    {
        $hierarchy = [
            'GM' => null,
            'Manager' => 'GM',
            'Coordinator' => 'Manager',
            'Section Head' => 'Coordinator',
            'Supervisor' => 'Section Head'
        ];

        $supervisorPosition = $hierarchy[$position] ?? null;

        if (!$supervisorPosition) {
            return null;
        }

        return Employee::where('position', $supervisorPosition)
            ->whereHas('departments', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->first();
    }
    /**
     * Tampilkan detail karyawan
     */
    public function show($npk)
    {
        $promotionHistories = PromotionHistory::with('employee')
                                ->whereHas('employee', function ($query) use ($npk) {
                                    $query->where('npk', $npk);
                                })->get();
                                
        $astraTrainings = AstraTraining::with('employee')
                                ->whereHas('employee', function ($query) use ($npk) {
                                    $query->where('npk', $npk);
                                })->get();
                                
        $externalTrainings = ExternalTraining::with('employee')
                                ->whereHas('employee', function ($query) use ($npk) {
                                    $query->where('npk', $npk);
                                })->get();
                                
        $educations = EducationalBackground::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('end_date', 'desc') // Urutkan berdasarkan tanggal akhir terbaru
            ->limit(3) // Ambil hanya 3 data terbaru
            ->get();
        
        $workExperiences = WorkingExperience::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('end_date', 'desc') // Urutkan berdasarkan tanggal akhir terbaru
            ->orderBy('start_date', 'desc') // Jika end_date sama, urutkan berdasarkan tanggal mulai terbaru
            ->limit(3) // Ambil hanya 3 data terbaru
            ->get();
        
        $performanceAppraisals = PerformanceAppraisalHistory::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('date', 'desc') // Urutkan berdasarkan tanggal terbaru
            ->limit(3) // Ambil hanya 3 data terbaru
            ->get();
                            
        $employee = Employee::with('departments')->where('npk', $npk)->firstOrFail();
        $departments = Department::all();
        return view('website.employee.show', compact('employee','promotionHistories', 'educations', 'workExperiences', 'performanceAppraisals', 'departments', 'astraTrainings', 'externalTrainings'));
    }

    public function edit($npk)
    {
        $promotionHistories = PromotionHistory::with('employee')
                                ->whereHas('employee', function ($query) use ($npk) {
                                    $query->where('npk', $npk);
                                })->get();
                                
        $astraTrainings = AstraTraining::with('employee')
                                ->whereHas('employee', function ($query) use ($npk) {
                                    $query->where('npk', $npk);
                                })->get();
                                
        $externalTrainings = ExternalTraining::with('employee')
                                ->whereHas('employee', function ($query) use ($npk) {
                                    $query->where('npk', $npk);
                                })->get();
                                
        $educations = EducationalBackground::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('end_date', 'desc') // Urutkan berdasarkan tanggal akhir terbaru
            ->limit(3) // Ambil hanya 3 data terbaru
            ->get();
        
        $workExperiences = WorkingExperience::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('end_date', 'desc') // Urutkan berdasarkan tanggal akhir terbaru
            ->orderBy('start_date', 'desc') // Jika end_date sama, urutkan berdasarkan tanggal mulai terbaru
            ->limit(3) // Ambil hanya 3 data terbaru
            ->get();
        
        $performanceAppraisals = PerformanceAppraisalHistory::with('employee')
            ->whereHas('employee', function ($query) use ($npk) {
                $query->where('npk', $npk);
            })
            ->orderBy('date', 'desc') // Urutkan berdasarkan tanggal terbaru
            ->limit(3) // Ambil hanya 3 data terbaru
            ->get();
                            
        $employee = Employee::with('departments')->where('npk', $npk)->firstOrFail();
        $departments = Department::all();
        return view('website.employee.update', compact('employee','promotionHistories', 'educations', 'workExperiences', 'performanceAppraisals', 'departments', 'astraTrainings', 'externalTrainings'));
    }

    public function update(Request $request, $npk)
    {
        try {
            $employee = Employee::where('npk', $npk)->firstOrFail();

            // Simpan data sebelum update untuk perbandingan
            $oldGrade = $employee->grade;
            $oldPosition = $employee->position;

            try {
                $validatedData = $request->validate([
                    'npk' => 'required|string|max:255|unique:employees,npk,' . $employee->id,
                    'name' => 'required|string|max:255',
                    'birthday_date' => 'required|date',
                    'gender' => 'required|in:Male,Female',
                    'company_name' => 'required|string',
                    'aisin_entry_date' => 'required|date',
                    'working_period' => 'required',
                    'company_group' => 'required|string',
                    'position' => 'required|string',
                    'grade' => 'required|string',
                    'department_id' => 'required|exists:departments,id',
                    'last_promote_date' => 'nullable|date',
                    'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('employee.master.index')->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
            }

            DB::transaction(function () use ($validatedData, $employee, $request, $oldGrade, $oldPosition) {
                // Update data employee kecuali `photo`
                $employee->update(collect($validatedData)->except(['photo', 'department_id'])->toArray());

                // Update department di tabel pivot `employee_departments`
                $employee->departments()->sync([$validatedData['department_id']]);

                // Cari Supervisor berdasarkan department yang sama
                $supervisor = Employee::whereHas('departments', function ($query) use ($validatedData) {
                    $query->where('departments.id', $validatedData['department_id']);
                })->where('position', 'Supervisor')->first();

                // Update `supervisor_id`
                $employee->update(['supervisor_id' => $supervisor ? $supervisor->id : null]);

                // Jika ada file foto baru, hapus yang lama lalu simpan yang baru
                if ($request->hasFile('photo')) {
                    if ($employee->photo) {
                        Storage::delete('public/' . $employee->photo);
                    }
                    $newPhotoPath = $request->file('photo')->store('employee_photos', 'public');
                    $employee->update(['photo' => $newPhotoPath]);
                }

                // Cek apakah ada perubahan pada grade atau position
                if ($oldGrade !== $validatedData['grade'] || $oldPosition !== $validatedData['position']) {
                    PromotionHistory::create([
                        'employee_id' => $employee->id,
                        'previous_grade' => $oldGrade,
                        'previous_position' => $oldPosition,
                        'current_grade' => $validatedData['grade'],
                        'current_position' => $validatedData['position'],
                        'last_promotion_date' => now(),
                    ]);
                }
            });

            return redirect()->route('employee.master.index')->with('success', 'Data karyawan berhasil diperbarui!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('employee.master.index')->with('error', 'Karyawan tidak ditemukan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return redirect()->route('employee.master.index')->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail();

        if ($employee->photo) {
            Storage::delete('public/' . $employee->photo);
        }

        $employee->delete();

        return redirect()->back()->with('success', 'Karyawan berhasil dihapus!');
    }

    public function profile($npk)
    {
        $employee = Employee::where('npk', $npk)->firstOrFail();
        return view('website.employee.profile.index', compact('employee'));
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        try {
            $import = Excel::import(new EmployeeImport, $request->file('file'));
            // dd($import);
            // session()->flash('success', 'Data karyawan berhasil diimport!');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function workExperienceStore(Request $request)
    {
        $employee = DB::table('employees')->where('id', $request->employee_id)->exists();

        if (!$employee) {
            return back()->with('error', 'Employee tidak ditemukan!');
        }
        
        $request->validate([
            'position' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            WorkingExperience::create([
                'employee_id' => $request->employee_id, // Sesuaikan dengan sistem autentikasi
                'position' => $request->position,
                'company' => $request->company,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'description' => $request->description,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pengalaman kerja berhasil ditambahkan.');
        } catch (\Throwable $th) {

            dd($th);
            DB::rollback();
            return redirect()->back()->with('error' , 'Pengalaman kerja gagal ditambahkan!');
        }
    }

    public function workExperienceUpdate(Request $request, $id)
    {
        $experience = WorkingExperience::findOrFail($id);

        $request->validate([
            'position'   => 'required|string|max:255',
            'company'    => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'description'=> 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $experience->update([
                'position'    => $request->position,
                'company'     => $request->company,
                'start_date'  => Carbon::parse($request->start_date),
                'end_date'    => $request->end_date ? Carbon::parse($request->end_date) : null,
                'description' => $request->description,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pengalaman kerja berhasil diupdate.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Pengalaman kerja gagal diupdate.');
        }
    }

    public function workExperienceDestroy($id)
    {
        $experience = WorkingExperience::findOrFail($id);

        try {
            DB::beginTransaction();

            $experience->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'Pengalaman kerja berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Pengalaman kerja gagal dihapus.');
        }
    }

    public function educationStore(Request $request)
    {
        try {
            $employeeExists = DB::table('employees')->where('id', $request->employee_id)->exists();
    
            if (!$employeeExists) {
                return back()->with('error', 'Employee tidak ditemukan!');
            }
    
            $request->validate([
                'level' => 'required',
                'major' => 'required',
                'institute' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date',
            ]);
    
            // Debugging setelah validasi berhasil    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangkap error validasi dan tampilkan dengan back()
            return redirect()->back()->with('error', $e->getMessage());
        }
        
        try {
            DB::beginTransaction();

            EducationalBackground::create([
                'employee_id' => $request->employee_id,
                'educational_level' => $request->level,
                'major' => $request->major,
                'institute' => $request->institute,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Riwayat pendidikan gagal ditambahkan!');
        }
    }

    public function educationUpdate(Request $request, $id)
    {
        $education = EducationalBackground::findOrFail($id);

        $validatedData = $request->validate([
            'level' => 'required',
            'major' => 'required',
            'institute' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $education->update([
                'educational_level'       => $validatedData['level'],
                'major'       => $validatedData['major'],
                'institute'   => $validatedData['institute'],
                'start_date'  => Carbon::parse($validatedData['start_date']),
                'end_date'    => $validatedData['end_date'] ? Carbon::parse($validatedData['end_date']) : null,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Riwayat pendidikan berhasil diupdate.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Riwayat pendidikan gagal diupdate.');
        }
    }

    public function educationDestroy($id)
    {
        $experience = EducationalBackground::findOrFail($id);

        try {
            DB::beginTransaction();

            $experience->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'Riwayat pendidikan berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Riwayat pendidikan gagal dihapus.');
        }
    }

    public function appraisalStore(Request $request)
    {
        try {
            $employeeExists = DB::table('employees')->where('id', $request->employee_id)->exists();
    
            if (!$employeeExists) {
                return back()->with('error', 'Employee tidak ditemukan!');
            }
    
            $validatedData = $request->validate([
                'score' => 'required',
                'description' => 'required',
                'date' => 'required|date',
            ]);
    
            // Debugging setelah validasi berhasil    
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangkap error validasi dan tampilkan dengan back()
            return redirect()->back()->with('error', $e->getMessage());
        }
        
        try {
            DB::beginTransaction();
    
            // Simpan data appraisal
            PerformanceAppraisalHistory::create([
                'employee_id' => $request->employee_id,
                'score'       => $validatedData['score'],
                'description' => $validatedData['description'],
                'date'        => Carbon::parse($validatedData['date']),
            ]);
    
            DB::commit();
            return redirect()->back()->with('success', 'Performance appraisal berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th);
            return redirect()->back()->with('error', 'Performance appraisal gagal ditambahkan : ' . $th->getMessage());
        }
    }

    public function appraisalUpdate(Request $request, $id)
    {
        $appraisal = PerformanceAppraisalHistory::findOrFail($id);

        $validatedData = $request->validate([
            'score' => 'required',
            'description' => 'required',
            'date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();
        
            // Update dengan field yang benar
            $appraisal->update([
                'score'       => $validatedData['score'],
                'description' => $validatedData['description'],
                'date'        => Carbon::parse($validatedData['date']), // Pastikan format tanggal benar
            ]);
        
            DB::commit();
            return redirect()->back()->with('success', 'Performance appraisal berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Performance appraisal gagal diperbarui.');
        }
    }

    public function appraisalDestroy($id)
    {
        $appraisal = PerformanceAppraisalHistory::findOrFail($id);

        try {
            DB::beginTransaction();

            $appraisal->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'Performance appraisal berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Performance appraisal gagal dihapus.');
        }
    }
    
    public function promotionDestroy($id)
    {
        $promotion = PromotionHistory::findOrFail($id);

        try {
            DB::beginTransaction();

            // Ambil karyawan yang dipromosikan
            $employee = Employee::findOrFail($promotion->employee_id);

            // Cari promotion history sebelumnya sebelum yang dihapus
            $lastPromotion = PromotionHistory::where('employee_id', $employee->id)
                ->where('id', '<', $promotion->id) // Hanya cari promosi sebelum yang sedang dihapus
                ->orderBy('id', 'desc')
                ->first();

            if ($lastPromotion) {
                // Jika ada history sebelumnya, rollback grade & position ke data tersebut
                $employee->position = $lastPromotion->previous_position;
                $employee->grade = $lastPromotion->previous_grade;
            } else {
                // Jika tidak ada promotion history sebelumnya, reset ke nilai default awal
                $employee->position = $promotion->previous_position;
                $employee->grade = $promotion->previous_grade;
            }

            $employee->save();

            // Hapus promotion history
            $promotion->delete();
            
            DB::commit();
            return redirect()->back()->with('success', 'Promotion history berhasil dihapus dan posisi/grade dikembalikan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Promotion history gagal dihapus.');
        }
    }

    public function astraTrainingStore(Request $request)
    {
        try {
            // Cek apakah employee_id ada di database
            $employeeExists = DB::table('employees')->where('id', $request->employee_id)->exists();
            if (!$employeeExists) {
                return back()->with('error', 'Employee tidak ditemukan!');
            }
    
            // Validasi input
            $validatedData = $request->validate([
                'year'           => 'required|digits:4|integer',
                'program'        => 'required|string|max:255',
                'ict_score'      => 'required',
                'project_score'  => 'required',
                'total_score'    => 'required',
            ]);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
    
        try {
            DB::beginTransaction();
    
            // Simpan data ke AstraTraining
            AstraTraining::create([
                'employee_id'   => $request->employee_id,
                'year'          => $validatedData['year'],
                'program'       => $validatedData['program'],
                'ict_score'     => $validatedData['ict_score'],
                'project_score' => $validatedData['project_score'],
                'total_score'   => $validatedData['total_score'],
            ]);
    
            DB::commit();
            return redirect()->back()->with('success', 'Data Astra Training berhasil ditambahkan.');
    
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan data Astra Training: ' . $th->getMessage());
        }
    }

    public function astraTrainingUpdate(Request $request, $id)
    {
        try {
            // Cek apakah data AstraTraining dengan ID yang diberikan ada
            $astraTraining = AstraTraining::find($id);
            if (!$astraTraining) {
                return back()->with('error', 'Data Astra Training tidak ditemukan!');
            }

            // Validasi input
            $validatedData = $request->validate([
                'year'           => 'required|digits:4|integer',
                'program'        => 'required|string|max:255',
                'ict_score'      => 'required',
                'project_score'  => 'required',
                'total_score'    => 'required',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update data AstraTraining
            $astraTraining->update([
                'year'          => $validatedData['year'],
                'program'       => $validatedData['program'],
                'ict_score'     => $validatedData['ict_score'],
                'project_score' => $validatedData['project_score'],
                'total_score'   => $validatedData['total_score'],
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Data Astra Training berhasil diperbarui.');

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui data Astra Training: ' . $th->getMessage());
        }
    }

    public function astraTrainingDestroy($id)
    {
        try {
            // Cek apakah data AstraTraining dengan ID yang diberikan ada
            $astraTraining = AstraTraining::find($id);
            if (!$astraTraining) {
                return back()->with('error', 'Data Astra Training tidak ditemukan!');
            }

            DB::beginTransaction();

            // Hapus data AstraTraining
            $astraTraining->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Data Astra Training berhasil dihapus.');

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus data Astra Training: ' . $th->getMessage());
        }
    }

    public function externalTrainingStore(Request $request)
    {
        try {
            // Cek apakah employee_id ada di database
            $employeeExists = DB::table('employees')->where('id', $request->employee_id)->exists();
            if (!$employeeExists) {
                return back()->with('error', 'Employee tidak ditemukan!');
            }

            // Validasi input
            $validatedData = $request->validate([
                'program' => 'required|string|max:255',
                'year'    => 'required|digits:4|integer',
                'vendor'  => 'required|string|max:255',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Simpan data ke ExternalTraining
            ExternalTraining::create([
                'employee_id' => $request->employee_id,
                'year'        => $validatedData['year'],
                'program'     => $validatedData['program'],
                'vendor'      => $validatedData['vendor'],
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Data External Training berhasil ditambahkan.');

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan data External Training: ' . $th->getMessage());
        }
    }

    public function externalTrainingUpdate(Request $request, $id)
    {
        try {
            // Cek apakah data training dengan ID tersebut ada
            $externalTraining = ExternalTraining::findOrFail($id);

            // Validasi input
            $validatedData = $request->validate([
                'program' => 'required|string|max:255',
                'year'    => 'required|digits:4|integer',
                'vendor'  => 'required|string|max:255',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update data di database
            $externalTraining->update([
                'year'    => $validatedData['year'],
                'program' => $validatedData['program'],
                'vendor'  => $validatedData['vendor'],
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Data External Training berhasil diperbarui.');

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui data External Training: ' . $th->getMessage());
        }
    }


    public function externalTrainingDestroy($id)
    {
        try {
            // Cek apakah data training dengan ID tersebut ada
            $externalTraining = ExternalTraining::findOrFail($id);

            DB::beginTransaction();

            // Hapus data dari database
            $externalTraining->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Data External Training berhasil dihapus.');

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus data External Training: ' . $th->getMessage());
        }
    }

}
