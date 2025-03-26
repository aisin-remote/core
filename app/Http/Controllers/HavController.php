<?php

namespace App\Http\Controllers;

use App\Models\Alc;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Hav;
use App\Models\HavDetail;
use App\Models\HavDetailKeyBehavior;
use App\Models\KeyBehavior;
use Illuminate\Database\Events\TransactionBeginning;
use Yajra\DataTables\Facades\DataTables;

class HavController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $havGrouped = Hav::with('employee')->get()->groupBy('quadrant');

        // Quadrant ID => Judul
        $titles = [
            13 => 'Maximal Contributor',
            7  => 'Top Performer',
            3  => 'Future Star',
            1  => 'Star',
            14 => 'Contributor',
            8  => 'Strong Performer',
            4  => 'Potential Candidate',
            2  => 'Future Star',
            15 => 'Minimal Contributor',
            9  => 'Career Person',
            6  => 'Candidate',
            5  => 'Raw Diamond',
            16 => 'Dead Wood',
            12 => 'Problem Employee',
            11 => 'Unfit Employee',
            10 => 'Most Unfit Employee',
        ];

        $orderedHavGrouped = collect(array_keys($titles))->mapWithKeys(function ($quadrantId) use ($havGrouped) {
            return [$quadrantId => $havGrouped[$quadrantId] ?? collect()];
        });

        return view('website.hav.index', compact('orderedHavGrouped', 'titles'));
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $title = 'Add Employee';
        $employees = Employee::all();
        return view('website.hav.list', compact('title', 'employees'));
    }

    public function ajaxList(Request $request)
    {
        $data = Hav::with('employee')->get(); // Pastikan relasi 'employee' ada

        return DataTables::of($data)
            ->addColumn('npk', fn($row) => $row->employee->npk ?? '-')
            ->addColumn('nama', fn($row) => $row->employee->name ?? '-')
            ->addColumn('status', fn($row) => $row->status)
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generateCreate($id)
    {
        $createHav = new Hav();
        $createHav->employee_id = $id;
        $createHav->save();

        $hav_id = $createHav->id;
        $alc = Alc::all();

        foreach ($alc as $alc) {
            $createHavDetail = new HavDetail();
            $createHavDetail->alc_id = $alc->id;
            $createHavDetail->hav_id = $hav_id;
            $createHavDetail->score = 0;
            $createHavDetail->evidence = '';
            $createHavDetail->save();

            $keyBehaviors = KeyBehavior::where('alc_id', $alc->id)->get();
            foreach ($keyBehaviors as $keyBehavior) {
                $createHavDetailKeyBehavior = new HavDetailKeyBehavior();
                $createHavDetailKeyBehavior->hav_detail_id = $createHavDetail->id;
                $createHavDetailKeyBehavior->key_behavior_id = $keyBehavior->id;
                $createHavDetailKeyBehavior->score = 0;
                $createHavDetailKeyBehavior->save();
            }
        }


        $title = 'Add Employee';
        $employees = Employee::all();
        return redirect()->route('hav.update', $hav_id);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listCreate()
    {
        $title = 'Add Employee';
        $employees = Employee::all();
        return view('website.hav.list-create', compact('title', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $title = 'Add Employee';
        $hav = Hav::find($id);
        return view('website.hav.create', compact('title', 'hav'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateRating(Request $request)
    {
        $request->validate([
            'key_behavior_id' => 'required|exists:hav_detail_key_behaviors,key_behavior_id',
            'hav_detail_id' => 'required|exists:hav_detail_key_behaviors,hav_detail_id',
            'rating' => 'required|numeric|min:1|max:5'
        ]);

        $ratingUpdate = HavDetailKeyBehavior::where([
            'key_behavior_id' => $request->key_behavior_id,
            'hav_detail_id' => $request->hav_detail_id
        ])->first();

        if ($ratingUpdate) {
            $ratingUpdate->score = $request->rating;
            $ratingUpdate->save();

            // ✅ Hitung rata-rata dari semua score HavDetailKeyBehavior yang terkait dengan HavDetail ini
            $averageScore = HavDetailKeyBehavior::where('hav_detail_id', $request->hav_detail_id)
                ->avg('score'); // Menghitung rata-rata nilai

            // ✅ Update score di HavDetail dengan nilai rata-rata
            HavDetail::where('id', $request->hav_detail_id)
                ->update(['score' => $averageScore]);

            return response()->json([
                'success' => true,
                'message' => 'Rating updated successfully',
                'new_average' => floatval($averageScore) // Kirim rata-rata terbaru ke frontend
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Record not found'], 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
