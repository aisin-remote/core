<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Throwable;

class SignatureController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        // File wajib ada (baik upload langsung atau hasil canvas.toBlob)
        $request->validate([
            'signature' => [
                'required',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
            ],
        ]);

        // Nama file {slug-nama}-signature-{npk}.png
        $fileName     = $this->buildSignatureFilename($employee);
        $relativePath = "signatures/{$fileName}";

        // Parameter proses
        $fromDraw     = (bool) $request->boolean('from_draw', false); // <<— NEW
        $fuzz         = max(0.0, min((float) $request->input('fuzz', 0.18), 0.40));
        $forceBlack   = (bool) $request->boolean('force_black', false);
        $inkStrength  = max(0.0, min((float) $request->input('ink_strength', 0.75), 1.0));

        // Context log
        $actorId  = optional($request->user())->id;
        $clientIp = $request->ip();
        $oldPath  = $employee->signature_path;
        $oldHash  = $this->hashIfExists($oldPath);

        $newFileSaved = false;

        try {
            // 1) Baca & normalisasi orientasi
            $img = Image::make($request->file('signature')->getRealPath())->orientate();

            // Pre-resize agar proses cepat
            if ($img->height() > 1200) {
                $img->resize(null, 1200, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });
            }

            // 2) Proses: jika hasil gambar canvas (sudah transparan), lewati remove-background
            if (!$fromDraw) {
                $img = $this->removeBackgroundAdaptiveGD($img, $fuzz);
            }

            // 3) (Opsional) tegaskan tinta → hitam
            if ($forceBlack) {
                $img = $this->forceInkToBlackGD($img, $inkStrength);
            }

            // 4) Final resize untuk tampilan
            if ($img->height() > 400) {
                $img->resize(null, 400, function ($c) {
                    $c->aspectRatio();
                    $c->upsize();
                });
            }

            // Encode PNG & hash konten baru
            $png     = (string) $img->encode('png', 9);
            $newHash = hash('sha256', $png);

            // Tulis file (overwrite)
            Storage::disk('public')->put($relativePath, $png);
            $newFileSaved = true;

            // 5) Transaksi DB
            DB::beginTransaction();
            $employee->signature_path = $relativePath;
            $employee->save();
            DB::commit();

            // 6) Hapus file lama setelah commit
            if ($oldPath && $oldPath !== $relativePath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            // Log sukses
            $contentChanged = $oldHash ? ($oldHash !== $newHash) : true;
            $pathChanged    = ($oldPath !== $relativePath);
            Log::info('signature.store.success', [
                'employee_id'     => $employee->id,
                'employee_name'   => $employee->name,
                'employee_npk'    => $employee->npk,
                'actor_id'        => $actorId,
                'ip'              => $clientIp,
                'source'          => $fromDraw ? 'draw' : 'upload', // <<— NEW
                'fuzz'            => $fuzz,
                'ink_forced'      => $forceBlack,
                'ink_strength'    => $inkStrength,
                'old_path'        => $oldPath,
                'new_path'        => $relativePath,
                'old_hash'        => $oldHash,
                'new_hash'        => $newHash,
                'content_changed' => $contentChanged,
                'path_changed'    => $pathChanged,
            ]);

            return response()->json([
                'message'      => 'Signature saved',
                'url'          => asset('storage/' . $relativePath),
                'source'       => $fromDraw ? 'draw' : 'upload',
                'fuzz'         => $fuzz,
                'changed'      => $contentChanged,
                'ink_forced'   => $forceBlack,
                'ink_strength' => $inkStrength,
            ]);
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();

            if ($newFileSaved && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }

            Log::error('signature.store.error', [
                'employee_id' => $employee->id,
                'actor_id'    => $actorId,
                'ip'          => $clientIp,
                'error'       => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);

            return response()->json(['message' => 'Gagal menyimpan tanda tangan.'], 500);
        }
    }

    public function destroy(Employee $employee)
    {
        $actorId  = optional(auth()->user())->id;
        $clientIp = request()->ip();
        $oldPath  = $employee->signature_path;
        $oldHash  = $this->hashIfExists($oldPath);

        try {
            DB::beginTransaction();
            $employee->signature_path = null;
            $employee->save();
            DB::commit();

            $fileDeleted = false;
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                $fileDeleted = Storage::disk('public')->delete($oldPath);
            }

            Log::info('signature.destroy.success', [
                'employee_id'  => $employee->id,
                'employee_name' => $employee->name,
                'employee_npk' => $employee->npk,
                'actor_id'     => $actorId,
                'ip'           => $clientIp,
                'old_path'     => $oldPath,
                'old_hash'     => $oldHash,
                'file_deleted' => $fileDeleted,
            ]);

            return response()->json(['message' => 'Signature deleted']);
        } catch (Throwable $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();

            Log::error('signature.destroy.error', [
                'employee_id' => $employee->id,
                'actor_id'    => $actorId,
                'ip'          => $clientIp,
                'error'       => $e->getMessage(),
                'file'        => $e->getFile(),
                'line'        => $e->getLine(),
            ]);

            return response()->json(['message' => 'Gagal menghapus tanda tangan.'], 500);
        }
    }

    /* ===================== Helpers ===================== */

    private function buildSignatureFilename(Employee $employee): string
    {
        $nameSlug = Str::slug((string) $employee->name, '-');
        $npk      = preg_replace('/[^A-Za-z0-9\-]/', '', (string) $employee->npk);
        return "{$nameSlug}-signature-{$npk}.png";
    }

    // === Adaptive BG removal (tetap seperti versi terakhir) ===
    private function removeBackgroundAdaptiveGD(\Intervention\Image\Image $image, float $fuzz = 0.18): \Intervention\Image\Image
    {
        $src = $image->getCore();
        $w = imagesx($src);
        $h = imagesy($src);

        $scale = 8;
        $sw = max(1, (int) round($w / $scale));
        $sh = max(1, (int) round($h / $scale));
        $small = imagecreatetruecolor($sw, $sh);
        imagecopyresampled($small, $src, 0, 0, 0, 0, $sw, $sh, $w, $h);
        for ($i = 0; $i < 6; $i++) imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);

        $bg = imagecreatetruecolor($w, $h);
        imagecopyresampled($bg, $small, 0, 0, 0, 0, $w, $h, $sw, $sh);

        $dst = imagecreatetruecolor($w, $h);
        imagesavealpha($dst, true);
        imagealphablending($dst, false);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);

        $tDiff = (int) round(25 + 160 * $fuzz);
        $soft  = max(5, (int) round($tDiff * 0.6));
        $hard  = $tDiff + 30;

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $s = imagecolorsforindex($src, imagecolorat($src, $x, $y));
                $b = imagecolorsforindex($bg,  imagecolorat($bg,  $x, $y));
                $grayS = (int) round(0.299 * $s['red'] + 0.587 * $s['green'] + 0.114 * $s['blue']);
                $grayB = (int) round(0.299 * $b['red'] + 0.587 * $b['green'] + 0.114 * $b['blue']);
                $diff  = $grayB - $grayS;

                if ($diff <= $soft)      $alpha = 127;
                elseif ($diff >= $hard)  $alpha = 0;
                else {
                    $t = ($diff - $soft) / max(1, ($hard - $soft));
                    $alpha = (int) round(127 * (1 - $t));
                }
                $col = imagecolorallocatealpha($dst, $s['red'], $s['green'], $s['blue'], $alpha);
                imagesetpixel($dst, $x, $y, $col);
            }
        }

        imagedestroy($small);
        imagedestroy($bg);
        return Image::make($dst);
    }

    // === Force tinta → hitam, jaga anti-alias ===
    private function forceInkToBlackGD(\Intervention\Image\Image $image, float $strength = 0.75): \Intervention\Image\Image
    {
        $src = $image->getCore();
        $w = imagesx($src);
        $h = imagesy($src);
        $gamma = 1.0 + 1.2 * $strength;
        $darkF = 1.0 - 0.55 * $strength;

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgba  = imagecolorsforindex($src, imagecolorat($src, $x, $y));
                $alpha = $rgba['alpha'];
                if ($alpha >= 126) continue; // skip transparan

                $g = (0.299 * $rgba['red'] + 0.587 * $rgba['green'] + 0.114 * $rgba['blue']) / 255.0;
                $g2 = pow($g, $gamma) * $darkF;
                $val = (int) max(0, min(255, round($g2 * 255)));

                $col = imagecolorallocatealpha($src, $val, $val, $val, $alpha);
                imagesetpixel($src, $x, $y, $col);
            }
        }
        return Image::make($src);
    }

    private function hashIfExists(?string $path): ?string
    {
        if (!$path || !Storage::disk('public')->exists($path)) return null;
        try {
            return hash('sha256', Storage::disk('public')->get($path));
        } catch (Throwable $e) {
            Log::warning('signature.hash.read_failed', ['path' => $path, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
