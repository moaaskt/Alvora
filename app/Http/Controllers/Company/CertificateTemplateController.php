<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateVersion;
use App\Services\CertificateSchemaValidator;
use App\Services\CertificateTemplatePublishingService;
use App\Services\VersionedCertificateRenderer;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CertificateTemplateController extends Controller
{
    /**
     * Generate an in-memory PDF preview of the provided schema without any persistence.
     */
    public function preview(
        Request $request,
        CertificateSchemaValidator $validator,
        VersionedCertificateRenderer $renderer
    ): Response|JsonResponse {
        $validatedData = $request->validate([
            'schema' => 'required|array',
        ]);

        try {
            $validatedSchema = $validator->validate($validatedData['schema']);
        } catch (DomainException $e) {
            return response()->json([
                'message' => 'Schema de certificado inválido.',
                'errors' => [$e->getMessage()],
            ], 422);
        }

        $user = Auth::user();
        $companyName = $user?->company?->name;

        $pdf = $renderer->renderPreview($validatedSchema, $companyName);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview-certificado.pdf"',
        ]);
    }

    /**
     * Create a new draft version for a template.
     */
    public function createDraftVersion(
        Request $request,
        CertificateTemplate $template,
        CertificateTemplatePublishingService $service
    ): JsonResponse {
        $validatedData = $request->validate([
            'schema' => 'required|array',
        ]);

        try {
            $user = Auth::user();
            $version = $service->createDraft($template, $validatedData['schema'], $user);

            return response()->json([
                'message' => 'Rascunho de versão criado com sucesso.',
                'version' => $version,
            ], 201);
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Template não encontrado.',
            ], 404);
        }
    }

    /**
     * Update an existing draft version schema.
     */
    public function saveDraft(
        Request $request,
        CertificateTemplate $template,
        CertificateTemplateVersion $version,
        CertificateTemplatePublishingService $service
    ): JsonResponse {
        $validatedData = $request->validate([
            'schema' => 'required|array',
        ]);

        try {
            $user = Auth::user();
            $updatedVersion = $service->updateDraft($template, $version, $validatedData['schema'], $user);

            return response()->json([
                'message' => 'Rascunho atualizado com sucesso.',
                'version' => $updatedVersion,
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Template ou versão não encontrado.',
            ], 404);
        }
    }

    /**
     * Publish a template version transactionally.
     */
    public function publish(
        CertificateTemplate $template,
        CertificateTemplateVersion $version,
        CertificateTemplatePublishingService $service
    ): JsonResponse {
        try {
            $user = Auth::user();
            $publishedVersion = $service->publish($template, $version, $user);

            return response()->json([
                'message' => 'Versão do template publicada com sucesso.',
                'version' => $publishedVersion,
            ], 200);
        } catch (DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Template ou versão não encontrado.',
            ], 404);
        }
    }

    /**
     * Serve the background artwork securely with tenant authorization.
     */
    public function background(
        Request $request,
        CertificateTemplate $template,
        ?CertificateTemplateVersion $version,
        CertificateTemplatePublishingService $service,
        CertificateSchemaValidator $validator
    ): BinaryFileResponse|JsonResponse {
        $user = Auth::user();

        try {
            $service->authorizeTemplateAccess($template, $user);
        } catch (ModelNotFoundException) {
            abort(404, 'Template não encontrado.');
        } catch (\Illuminate\Auth\Access\AuthorizationException) {
            abort(403, 'Acesso não autorizado.');
        }

        // Se uma versão foi passada explicitamente via rota
        if ($version && $version->exists) {
            $targetVersion = $template->versions()->where('id', $version->id)->first();
            if (!$targetVersion) {
                abort(404, 'Versão não encontrada neste template.');
            }
        } else {
            // Busca versão informada por query param ?v= ou a versão ativa/mais recente
            $requestedVersionId = $request->query('v');
            if ($requestedVersionId) {
                $targetVersion = $template->versions()->where('id', (int) $requestedVersionId)->first();
            } else {
                $targetVersion = $template->activeVersion
                    ?? $template->versions()->where('status', 'draft')->latest('version')->first()
                    ?? $template->versions()->latest('version')->first();
            }
        }

        if (!$targetVersion) {
            abort(404, 'Nenhuma versão disponível para este template.');
        }

        $imagePath = $targetVersion->schema_json['background']['image_path'] ?? null;
        if (empty($imagePath)) {
            abort(404, 'Este template não possui imagem de fundo vinculada.');
        }

        // Valida o caminho contra path traversal e URLs remotas
        try {
            $validator->validateImagePath($imagePath);
        } catch (DomainException) {
            abort(400, 'Caminho de imagem inválido.');
        }

        // 1. Procura no disco local privado ('local' = storage/app/private)
        $disk = Storage::disk('local');
        $absolutePath = null;

        if ($disk->exists($imagePath)) {
            $absolutePath = $disk->path($imagePath);
        } elseif (Storage::disk('public')->exists($imagePath)) {
            // 2. Fallback transparente para imagens legadas no disco público
            $absolutePath = Storage::disk('public')->path($imagePath);
        }

        if (!$absolutePath || !file_exists($absolutePath)) {
            abort(404, 'Arquivo de imagem não encontrado no servidor.');
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
