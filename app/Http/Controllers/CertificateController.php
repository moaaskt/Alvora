<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Services\CertificateService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * Download certificate in PDF for eligible student.
     */
    public function download(Course $course, CertificateService $service)
    {
        $user = Auth::user();

        try {
            $certificate = $service->getOrCreateCertificate($user, $course);
            $pdf = $service->generatePdf($certificate);

            return $pdf->download("certificado-{$course->slug}.pdf");
        } catch (DomainException $e) {
            session()->flash('warning', $e->getMessage());
            return redirect()->route('student.player', $course->slug);
        }
    }

    /**
     * Public verification endpoint for certificate authenticity.
     */
    public function verify(string $code)
    {
        $certificate = Certificate::withoutGlobalScopes()
            ->where('validation_code', $code)
            ->with(['user', 'course', 'company'])
            ->first();

        return view('certificates.verify', [
            'certificate' => $certificate,
            'code' => $code,
        ]);
    }
}
