<?php

namespace App\Http\Controllers;

use App\Models\IpaAchievement;
use App\Models\IpaComment;
use App\Models\IpaHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IpaApprovalController extends Controller
{
    public function approval(Request $request, $company = null)
    {
        $title  = 'Approval';
        $filter = (string) $request->query('filter', 'all');

        return view('website.approval.ipa.index', [
            'title'   => $title,
            'company' => $company,
            'filter'  => $filter,
        ]);
    }

    public function approvalJson(Request $request)
    {
        try {
            $user = auth()->user();
            $me   = optional($user)->employee;

            Log::info('IPA Approval JSON: request', [
                'by_user'   => optional($user)->id,
                'by_emp'    => optional($me)->id,
                'query'     => $request->all(),
            ]);

            if (!$me) {
                return response()->json([
                    'data' => [],
                    'meta' => ['total' => 0, 'page' => 1, 'per_page' => 10, 'last_page' => 1]
                ]);
            }

            $page    = max(1, (int) $request->query('page', 1));
            $perPage = min(100, max(1, (int) $request->query('per_page', 10)));
            $search  = trim((string) $request->query('search', ''));
            $company = trim((string) $request->query('company', ''));
            $filter  = trim((string) $request->query('filter', '')); // dipakai HRD untuk filter posisi (opsional)

            $q = IpaHeader::query()
                ->with([
                    'employee:id,npk,name,company_name,position,grade',
                    'checkedBy:id,name',
                    'approvedBy:id,name'
                ])
                ->whereNotNull('id');

            // ====== Mode antrian "milik saya"
            // - sebagai checker: status=submitted & checked_by = saya
            // - sebagai approver: status=checked  & approved_by = saya
            $empId = $me->id;
            $q->where(function ($qq) use ($empId) {
                $qq->where(function ($w) use ($empId) {
                    $w->where('status', 'submitted')
                        ->where('checked_by', $empId);
                })
                    ->orWhere(function ($w) use ($empId) {
                        $w->where('status', 'checked')
                            ->where('approved_by', $empId);
                    });
            });

            // ====== Filter search (nama / npk)
            if ($search !== '') {
                $q->whereHas('employee', function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                        ->orWhere('npk', 'like', "%{$search}%");
                });
            }

            if ($company !== '') {
                $q->whereHas('employee', function ($w) use ($company) {
                    $w->where('company_name', $company);
                });
            }

            if ($filter && $filter !== 'all') {
                $q->whereHas('employee', function ($w) use ($filter) {
                    $w->where('position', $filter);
                });
            }

            $total = (clone $q)->count();

            $rows = $q->orderBy('id', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            Log::info('IPA Approval JSON: result meta', [
                'total'     => $total,
                'page'      => $page,
                'per_page'  => $perPage,
                'returned'  => $rows->count(),
            ]);

            $data = $rows->map(function (IpaHeader $h) use ($empId) {
                $e = $h->employee;
                // izinkan approve hanya jika memang giliran saya
                $canApprove = ($h->status === 'submitted' && $h->checked_by === $empId)
                    || ($h->status === 'checked'   && $h->approved_by === $empId);

                return [
                    'id'      => $h->id,
                    'status'  => $h->status,
                    'employee' => [
                        'npk'         => optional($e)->npk,
                        'name'        => optional($e)->name,
                        'company'     => optional($e)->company_name,
                        'position'    => optional($e)->position,
                        'department'  => optional($e)->department_name,
                        'grade'       => optional($e)->grade,
                    ],
                    'can_approve' => $canApprove,
                ];
            })->values();

            return response()->json([
                'data' => $data,
                'meta' => [
                    'total'     => $total,
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'last_page' => (int) ceil($total / max(1, $perPage)),
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('IPA Approval JSON: exception', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            report($e);
            return response()->json([
                'data' => [],
                'meta' => ['total' => 0, 'page' => 1, 'per_page' => 10, 'last_page' => 1],
                'error' => 'Internal error',
            ], 500);
        }
    }

    public function approve(Request $request, IpaHeader $ipa)
    {
        $me = optional(auth()->user())->employee;
        if (!$me) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            Log::info('IPA Approve: try', ['ipa_id' => $ipa->id, 'status' => $ipa->status, 'by_emp' => $me->id]);

            if ($ipa->status === 'submitted' && (int)$ipa->checked_by === (int)$me->id) {
                $ipa->status      = 'checked';
                $ipa->checked_at  = now();
                $ipa->save();
                // ikutkan achievements (opsional)
                IpaAchievement::where('ipa_id', $ipa->id)->update(['status' => 'checked']);
            } elseif ($ipa->status === 'checked' && (int)$ipa->approved_by === (int)$me->id) {
                $ipa->status       = 'approved';
                $ipa->approved_at  = now();
                $ipa->save();
                IpaAchievement::where('ipa_id', $ipa->id)->update(['status' => 'approved']);
            } else {
                Log::warning('IPA Approve: forbidden', ['ipa_id' => $ipa->id, 'by_emp' => $me->id]);
                return response()->json(['ok' => false, 'message' => 'Not in your queue'], 403);
            }

            Log::info('IPA Approve: ok', ['ipa_id' => $ipa->id, 'new_status' => $ipa->status]);
            return response()->json(['ok' => true, 'status' => $ipa->status]);
        } catch (\Throwable $e) {
            Log::error('IPA Approve: exception', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            report($e);
            return response()->json(['ok' => false, 'message' => 'Internal error'], 500);
        }
    }

    public function revise(Request $request, IpaHeader $ipa)
    {
        $data = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $me = optional(auth()->user())->employee;
        if (!$me) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $from = (string) $ipa->status;
        $to   = 'revised';

        try {
            return DB::transaction(function () use ($ipa, $me, $from, $to, $data) {
                Log::info('IPA Revise: try', [
                    'ipa_id'  => $ipa->id,
                    'status'  => $ipa->status,
                    'by_emp'  => $me->id,
                    'note'    => $data['note'],
                ]);

                $can =
                    ($ipa->status === 'submitted' && (int) $ipa->checked_by  === (int) $me->id) ||
                    ($ipa->status === 'checked'   && (int) $ipa->approved_by === (int) $me->id);

                if (!$can) {
                    return response()->json(['ok' => false, 'message' => 'Not in your queue'], 403);
                }

                IpaComment::create([
                    'ipa_id'      => $ipa->id,
                    'employee_id' => $me->id,
                    'status_from' => $from,
                    'status_to'   => $to,
                    'comment'     => $data['note'],
                ]);

                $ipa->status = $to;
                $ipa->save();

                IpaAchievement::where('ipa_id', $ipa->id)->update(['status' => $to]);

                Log::info('IPA Revise: ok', ['ipa_id' => $ipa->id, 'status_to' => $to]);

                return response()->json(['ok' => true, 'status' => $to]);
            });
        } catch (\Throwable $e) {
            Log::error('IPA Revise: exception', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            report($e);

            return response()->json(['ok' => false, 'message' => 'Internal error'], 500);
        }
    }
}
