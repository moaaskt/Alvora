<div class="px-space-xl py-space-lg flex flex-col gap-5 max-w-[1680px] mx-auto w-full"
     x-data="{
        previewUrl: null,
        previewLoading: false,
        previewError: null,
        showPreviewModal: false,
        showGuides: true,
        zoomScale: 0.72,
        draggingId: null,
        dragStartX: 0,
        dragStartY: 0,
        elemStartX: 0,
        elemStartY: 0,
        elemWidth: 100,
        elemHeight: 40,

        init() {
            this.recalculateZoom();
            window.addEventListener('resize', () => this.recalculateZoom());
        },

        recalculateZoom() {
            const viewport = this.$refs.canvasViewport;
            if (!viewport) return;
            const availableWidth = Math.max(360, viewport.clientWidth - 48);
            const fittedScale = availableWidth / 1122;
            this.zoomScale = Math.min(1.0, Math.max(0.32, Number(fittedScale.toFixed(3))));
        },

        startDrag(event, id, x, y, width, height, isReadOnly) {
            if (isReadOnly) return;
            this.draggingId = id;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;
            this.elemStartX = Number(x);
            this.elemStartY = Number(y);
            this.elemWidth = Number(width);
            this.elemHeight = Number(height);
        },

        onDragMove(event) {
            if (!this.draggingId) return;
            const dx = Math.round((event.clientX - this.dragStartX) / this.zoomScale);
            const dy = Math.round((event.clientY - this.dragStartY) / this.zoomScale);
            const nextX = Math.max(0, Math.min(1122 - this.elemWidth, this.elemStartX + dx));
            const nextY = Math.max(0, Math.min(793 - this.elemHeight, this.elemStartY + dy));

            const domEl = document.getElementById('canvas-node-' + this.draggingId);
            if (domEl) {
                domEl.style.left = nextX + 'px';
                domEl.style.top = nextY + 'px';
            }
        },

        stopDrag(event) {
            if (!this.draggingId) return;
            const dx = Math.round((event.clientX - this.dragStartX) / this.zoomScale);
            const dy = Math.round((event.clientY - this.dragStartY) / this.zoomScale);
            const finalX = Math.max(0, Math.min(1122 - this.elemWidth, this.elemStartX + dx));
            const finalY = Math.max(0, Math.min(793 - this.elemHeight, this.elemStartY + dy));
            const targetId = this.draggingId;
            this.draggingId = null;

            if (finalX !== this.elemStartX || finalY !== this.elemStartY) {
                $wire.updateElementPosition(targetId, finalX, finalY, this.elemWidth, this.elemHeight);
            } else {
                $wire.selectElement(targetId);
            }
        },

        async openPdfPreview() {
            this.previewLoading = true;
            this.previewError = null;
            this.showPreviewModal = true;

            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
            }

            try {
                const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                const response = await fetch('{{ route('instructor.certificate-templates.preview') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/pdf, application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({
                        schema: $wire.schema
                    }),
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    const firstErr = (errData.errors && errData.errors[0]) ? errData.errors[0] : (errData.message || 'Falha ao gerar prévia do certificado.');
                    this.previewError = firstErr;
                    this.previewLoading = false;
                    return;
                }

                const blob = await response.blob();
                this.previewUrl = URL.createObjectURL(blob);
            } catch (e) {
                this.previewError = 'Erro de comunicação ao gerar a prévia PDF no servidor.';
            } finally {
                this.previewLoading = false;
            }
        },

        closePdfPreview() {
            this.showPreviewModal = false;
            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
            }
        }
     }"
     @mousemove.window="onDragMove($event)"
     @mouseup.window="stopDrag($event)">

    <!-- TOP BAR: BREADCRUMB & VERSIONSELECTOR -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('instructor.certificate-templates.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-blue-600 transition-colors bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
                </svg>
                <span>Voltar aos Templates</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-500 font-medium">Templates de Certificados</span>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-800 font-semibold">{{ $template->name }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Seletor de Versão -->
            <div class="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">
                <span class="text-[11px] font-semibold text-slate-500">Versão:</span>
                <select wire:change="selectVersion($event.target.value)"
                        class="text-xs font-mono font-semibold text-slate-800 bg-transparent focus:outline-none cursor-pointer">
                    @foreach($versions as $ver)
                        <option value="{{ $ver->id }}" @selected($ver->id === $versionId)>
                            v{{ $ver->version }} ({{ $ver->status === 'active' ? 'Publicada' : ($ver->status === 'draft' ? 'Rascunho' : 'Arquivada') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Badge -->
            @if($versionStatus === 'active')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Versão Publicada (Imutável)
                </span>
            @elseif($versionStatus === 'draft')
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Rascunho Editável (v{{ $versionNumber }})
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    Versão Arquivada (Somente Leitura)
                </span>
            @endif
        </div>
    </div>

    <!-- ALERTAS DE VALIDAÇÃO OU SUCESSO -->
    @if($validationError)
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center justify-between shadow-2xs" role="alert">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"></path>
                </svg>
                <span>{{ $validationError }}</span>
            </div>
        </div>
    @endif

    @if($successMessage)
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between shadow-2xs" role="status">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                </svg>
                <span>{{ $successMessage }}</span>
            </div>
        </div>
    @endif

    <!-- HEADER PRINCIPAL E BARRA DE AÇÕES DO EDITOR -->
    <header class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex flex-col gap-0.5">
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-bold text-slate-900">{{ $template->name }}</h1>
                <span class="text-xs font-mono text-slate-400">A4 Landscape (1122 × 793 px)</span>
            </div>
            <p class="text-xs text-slate-500">
                @if($isReadOnlyVersion)
                    Esta versão já foi publicada ou arquivada e é estritamente imutável. Para editar o modelo, crie uma nova versão de rascunho.
                @else
                    Clique ou arraste elementos no Canvas A4 para posicioná-los e ajuste estilos no painel de propriedades lateral.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- Alternar Dados de Exemplo no Canvas -->
            <button type="button"
                    wire:click="togglePreviewData"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border transition-colors cursor-pointer {{ $showPreviewData ? 'bg-amber-50 text-amber-800 border-amber-300 ring-1 ring-amber-400' : 'bg-slate-50 text-slate-600 border-slate-200' }}"
                    title="Alternar entre visualização de tags/variáveis e dados reais de exemplo">
                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <span>{{ $showPreviewData ? 'Modo Edição (Variáveis)' : 'Simular com Dados Reais' }}</span>
            </button>

            <!-- Alternar Guias A4 -->
            <button type="button"
                    @click="showGuides = !showGuides"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border transition-colors cursor-pointer"
                    :class="showGuides ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-50 text-slate-600 border-slate-200'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"></path>
                </svg>
                <span>Margens & Área Útil A4</span>
            </button>

            <!-- Botão Prévia PDF Real (Server-Side Dompdf sem persistência) -->
            <button type="button"
                    @click="openPdfPreview()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition-all shadow-2xs cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path>
                </svg>
                <span>Prévia em PDF</span>
            </button>

            @if($isReadOnlyVersion)
                <!-- Botão Criar Nova Versão Rascunho -->
                <button type="button"
                        wire:click="createNewDraftFromCurrent"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-all shadow-sm cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"></path>
                    </svg>
                    <span>Criar Nova Versão Editável (Rascunho)</span>
                </button>
            @else
                <!-- Salvar Rascunho -->
                <button type="button"
                        wire:click="saveDraft"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 text-xs font-semibold transition-all shadow-2xs cursor-pointer">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="saveDraft">Salvar Rascunho</span>
                    <span wire:loading wire:target="saveDraft">Salvando...</span>
                </button>

                <!-- Publicar Versão -->
                <button type="button"
                        wire:click="publishVersion"
                        wire:confirm="Deseja publicar a versão v{{ $versionNumber }}? Ela se tornará a versão oficial ativa para novos certificados emitidos e não poderá mais ser alterada."
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-all shadow-sm cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="publishVersion">Publicar Versão v{{ $versionNumber }}</span>
                    <span wire:loading wire:target="publishVersion">Publicando...</span>
                </button>
            @endif
        </div>
    </header>

    <!-- WORKSPACE PRINCIPAL: BARRA DE ELEMENTOS + CANVAS A4 + PAINEL DE PROPRIEDADES -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">

        <!-- COLUNA ESQUERDA + CENTRO (8 COLUNAS): PALETA DE INSERÇÃO + CANVAS A4 PROPORCIONAL -->
        <div class="xl:col-span-8 flex flex-col gap-4">

            <!-- BARRA DE FERRAMENTAS DE ELEMENTOS E VARIÁVEIS DO DOMÍNIO -->
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col gap-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mr-1">Adicionar Elemento:</span>
                        <button type="button"
                                wire:click="addElement('text')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Texto</span>
                        </button>
                        <button type="button"
                                wire:click="addElement('signature_group')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Bloco de Assinatura</span>
                        </button>
                        <button type="button"
                                wire:click="addElement('auth_badge')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Selo de Verificação</span>
                        </button>
                        <button type="button"
                                wire:click="addElement('box')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Caixa / Moldura</span>
                        </button>
                        <button type="button"
                                wire:click="addElement('line')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Linha Divisória</span>
                        </button>
                    </div>

                    <!-- Controle de Zoom Responsivo -->
                    <div class="flex items-center gap-1.5 ml-auto">
                        <span class="text-[11px] text-slate-400 font-mono" x-text="'Escala A4: ' + Math.round(zoomScale * 100) + '%'"></span>
                        <button type="button" @click="zoomScale = Math.max(0.35, Number((zoomScale - 0.08).toFixed(2)))" class="px-2 py-0.5 text-xs bg-slate-100 hover:bg-slate-200 rounded border border-slate-200 font-bold cursor-pointer">-</button>
                        <button type="button" @click="recalculateZoom()" class="px-2 py-0.5 text-[11px] bg-slate-100 hover:bg-slate-200 rounded border border-slate-200 font-semibold cursor-pointer">Ajustar</button>
                        <button type="button" @click="zoomScale = Math.min(1.0, Number((zoomScale + 0.08).toFixed(2)))" class="px-2 py-0.5 text-xs bg-slate-100 hover:bg-slate-200 rounded border border-slate-200 font-bold cursor-pointer">+</button>
                    </div>
                </div>

                <!-- Inserção Rápida de Variáveis Suportadas pelo Domínio -->
                <div class="pt-2.5 border-t border-slate-100 flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mr-1">Variáveis do Domínio:</span>
                    @php
                        $placeholderLabels = [
                            '{{student_name}}' => 'Nome do Aluno',
                            '{{course_title}}' => 'Título do Curso',
                            '{{company_name}}' => 'Empresa',
                            '{{workload}}' => 'Carga Horária',
                            '{{issued_date}}' => 'Data de Emissão',
                            '{{validation_code}}' => 'Código ALV-YYYY-XXXXXX',
                            '{{verification_url}}' => 'URL de Verificação',
                        ];
                    @endphp
                    @foreach($allowedPlaceholders as $placeholder)
                        <button type="button"
                                wire:click="insertPlaceholder('{{ $placeholder }}')"
                                @disabled($isReadOnlyVersion || !$selectedElementId)
                                title="Inserir {{ $placeholder }} no elemento selecionado"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-mono bg-indigo-50/70 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/70 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>{{ $placeholder }}</span>
                            <span class="text-[10px] font-sans text-indigo-500">({{ $placeholderLabels[$placeholder] ?? '' }})</span>
                        </button>
                    @endforeach
                </div>

                <!-- Adicionar Caixas Rápidas de Dados Dinâmicos (Para Arte Pronta) -->
                <div class="pt-2.5 border-t border-slate-100 flex flex-col gap-1.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Adicionar Caixa Dinâmica (Posicione sobre a Arte Pronta):</span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button"
                                wire:click="addDynamicField('student_name')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Nome do Colaborador</span>
                        </button>
                        <button type="button"
                                wire:click="addDynamicField('course_title')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Curso Concluído</span>
                        </button>
                        <button type="button"
                                wire:click="addDynamicField('company_name')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Organização</span>
                        </button>
                        <button type="button"
                                wire:click="addDynamicField('workload')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Carga Horária</span>
                        </button>
                        <button type="button"
                                wire:click="addDynamicField('issued_date')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Data</span>
                        </button>
                        <button type="button"
                                wire:click="addDynamicField('validation_code')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Código ALV</span>
                        </button>
                        <button type="button"
                                wire:click="addDynamicField('verification_url')"
                                @disabled($isReadOnlyVersion)
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-colors disabled:opacity-40 cursor-pointer">
                            <span>+ Link Validação</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- VIEWPORT RESPONSIVO DO CANVAS A4 (1122 x 793 px) -->
            <div x-ref="canvasViewport"
                 class="bg-slate-200/80 rounded-2xl border border-slate-300/90 p-6 flex flex-col items-center justify-center overflow-x-auto shadow-inner select-none">

                <!-- Wrapper dimensionado pela escala para manter fluxo sem distorção -->
                <div class="relative transition-all duration-75"
                     :style="`width: ${Math.round(1122 * zoomScale)}px; height: ${Math.round(793 * zoomScale)}px;`">

                    <!-- PÁGINA A4 REAL (1122px x 793px, Landscape) -->
                    <div id="certificate-a4-canvas"
                         class="relative origin-top-left shadow-2xl"
                         style="width: 1122px; height: 793px; background-color: {{ $schema['background']['color'] ?? '#0b0f19' }}; font-family: Helvetica, Arial, sans-serif; overflow: hidden; box-sizing: border-box;"
                         :style="`transform: scale(${zoomScale}); background-color: {{ $schema['background']['color'] ?? '#0b0f19' }};`">

                        <!-- Arte de Fundo Personalizada (se carregada) -->
                        @if(!empty($backgroundImageUrl))
                            <img src="{{ $backgroundImageUrl }}"
                                 alt="Fundo do Certificado"
                                 class="pointer-events-none select-none"
                                 style="position: absolute; top: 0; left: 0; width: 1122px; height: 793px; object-fit: cover; z-index: 1;" />
                        @endif

                        <!-- Borda Externa Oficial do Certificado -->
                        @if((($schema['border']['width'] ?? 0) > 0) && (($schema['border']['style'] ?? 'solid') !== 'none'))
                            <div class="pointer-events-none"
                                 style="position: absolute; top: 25px; left: 25px; width: 1072px; height: 743px; border: {{ (int) ($schema['border']['width'] ?? 3) }}px {{ $schema['border']['style'] ?? 'solid' }} {{ $schema['border']['color'] ?? '#6366f1' }}; box-sizing: border-box; z-index: 5;">
                            </div>

                            <!-- Borda Interna Decorativa -->
                            <div class="pointer-events-none"
                                 style="position: absolute; top: 35px; left: 35px; width: 1052px; height: 723px; border: 1px dashed rgba(255, 255, 255, 0.15); box-sizing: border-box; z-index: 5;">
                            </div>
                        @endif

                        <!-- Guias de Margem e Área Útil A4 (Visíveis apenas no Editor quando showGuides = true) -->
                        <div x-show="showGuides"
                             class="pointer-events-none"
                             style="position: absolute; top: 45px; left: 50px; width: 1022px; height: 703px; border: 1px dotted rgba(56, 189, 248, 0.45); box-sizing: border-box; z-index: 6;">
                            <span style="position: absolute; top: -16px; left: 4px; font-size: 9px; font-family: monospace; color: rgba(56, 189, 248, 0.85); letter-spacing: 0.5px;">
                                ÁREA ÚTIL RECOMENDADA A4 (1022 × 703 px | Margem 50px/45px)
                            </span>
                            <!-- Linha Guia Central Vertical -->
                            <div style="position: absolute; top: 0; left: 50%; width: 1px; height: 100%; border-left: 1px dashed rgba(56, 189, 248, 0.2);"></div>
                            <!-- Linha Guia Central Horizontal -->
                            <div style="position: absolute; top: 50%; left: 0; width: 100%; height: 1px; border-top: 1px dashed rgba(56, 189, 248, 0.2);"></div>
                        </div>

                        <!-- ELEMENTOS DO SCHEMA -->
                        @foreach(($schema['elements'] ?? []) as $element)
                            @php
                                $elId = (string) ($element['id'] ?? 'elem');
                                $elType = (string) ($element['type'] ?? 'text');
                                $elX = (int) ($element['x'] ?? 0);
                                $elY = (int) ($element['y'] ?? 0);
                                $elW = (int) ($element['width'] ?? 100);
                                $elH = (int) ($element['height'] ?? 40);
                                $elStyle = $element['style'] ?? [];
                                $isSelected = $selectedElementId === $elId;

                                $displayContent = (string) ($element['content'] ?? '');
                                if ($showPreviewData) {
                                    foreach ($demoReplacements as $placeholder => $demoVal) {
                                        $displayContent = str_replace($placeholder, $demoVal, $displayContent);
                                    }
                                }

                                $inlineRules = [
                                    "position: absolute",
                                    "left: {$elX}px",
                                    "top: {$elY}px",
                                    "width: {$elW}px",
                                    "height: {$elH}px",
                                    "box-sizing: border-box",
                                    "overflow: hidden",
                                    "word-wrap: break-word",
                                ];

                                if (!empty($elStyle['font_family'])) {
                                    $inlineRules[] = "font-family: {$elStyle['font_family']}, sans-serif";
                                }
                                if (!empty($elStyle['font_size'])) {
                                    $inlineRules[] = "font-size: {$elStyle['font_size']}";
                                }
                                if (!empty($elStyle['font_weight'])) {
                                    $inlineRules[] = "font-weight: {$elStyle['font_weight']}";
                                }
                                if (!empty($elStyle['color'])) {
                                    $inlineRules[] = "color: {$elStyle['color']}";
                                }
                                if (!empty($elStyle['text_align'])) {
                                    $inlineRules[] = "text-align: {$elStyle['text_align']}";
                                }
                                if (!empty($elStyle['letter_spacing'])) {
                                    $inlineRules[] = "letter-spacing: {$elStyle['letter_spacing']}";
                                }
                                if (!empty($elStyle['line_height'])) {
                                    $inlineRules[] = "line-height: {$elStyle['line_height']}";
                                }
                                if (!empty($elStyle['text_transform'])) {
                                    $inlineRules[] = "text-transform: {$elStyle['text_transform']}";
                                }
                                if (!empty($elStyle['background_color'])) {
                                    $inlineRules[] = "background-color: {$elStyle['background_color']}";
                                }
                                if (!empty($elStyle['border_color']) && !empty($elStyle['border_width'])) {
                                    $bw = (int) $elStyle['border_width'];
                                    $bs = $elStyle['border_style'] ?? 'solid';
                                    $bc = $elStyle['border_color'];
                                    $inlineRules[] = "border: {$bw}px {$bs} {$bc}";
                                }
                                if (isset($elStyle['padding']) && is_numeric($elStyle['padding'])) {
                                    $inlineRules[] = "padding: " . ((int) $elStyle['padding']) . "px";
                                }
                                if (isset($elStyle['border_radius']) && is_numeric($elStyle['border_radius'])) {
                                    $inlineRules[] = "border-radius: " . ((int) $elStyle['border_radius']) . "px";
                                }

                                $styleAttr = implode('; ', $inlineRules);
                            @endphp

                            <div id="canvas-node-{{ $elId }}"
                                 wire:key="canvas-elem-{{ $elId }}"
                                 @mousedown.stop="startDrag($event, '{{ $elId }}', {{ $elX }}, {{ $elY }}, {{ $elW }}, {{ $elH }}, {{ $isReadOnlyVersion ? 'true' : 'false' }})"
                                 style="{{ $styleAttr }}"
                                 class="group transition-shadow {{ $isReadOnlyVersion ? 'cursor-pointer' : 'cursor-move' }} {{ $isSelected ? 'ring-2 ring-sky-400 ring-offset-1 ring-offset-transparent z-20' : 'hover:ring-1 hover:ring-sky-300/60 z-10' }}">

                                <!-- Etiqueta Identificadora do Elemento Selecionado -->
                                @if($isSelected)
                                    <div class="pointer-events-none"
                                         style="position: absolute; top: 0; right: 0; background: #0284c7; color: #fff; font-size: 9px; font-family: monospace; padding: 1px 5px; border-bottom-left-radius: 4px; line-height: 1.2; z-index: 30;">
                                        {{ $elId }} ({{ $elX }}, {{ $elY }})
                                    </div>
                                @endif

                                {!! nl2br(e($displayContent), false) !!}
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Rodapé Informativo do Canvas -->
                <div class="mt-3 flex flex-wrap items-center justify-between w-full text-[11px] text-slate-600 px-2">
                    <span>Resolução Nativa do Motor Dompdf: <strong>1122 × 793 px</strong> (A4 Paisagem)</span>
                    <span>Elementos no Documento: <strong>{{ count($schema['elements'] ?? []) }}</strong></span>
                </div>
            </div>
        </div>

        <!-- COLUNA DIREITA (4 COLUNAS): PAINEL DE PROPRIEDADES DO ELEMENTO E DO DOCUMENTO -->
        <div class="xl:col-span-4 flex flex-col gap-4">

            <!-- CARD: ARTE DE FUNDO DO CERTIFICADO (UPLOAD SEGURO) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 flex flex-col gap-3.5">
                <div class="border-b border-slate-100 pb-2.5 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Arte de Fundo do Certificado</h2>
                        <p class="text-xs text-slate-500">Envie o layout gráfico pronto em formato A4 Horizontal.</p>
                    </div>
                    @if(!empty($backgroundImageUrl))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Arte Ativa
                        </span>
                    @endif
                </div>

                @if(!empty($backgroundImageUrl))
                    <div class="flex flex-col gap-2.5">
                        <div class="relative rounded-xl overflow-hidden border border-slate-200 aspect-[1122/793] bg-slate-950 shadow-inner">
                            <img src="{{ $backgroundImageUrl }}" alt="Arte de Fundo" class="w-full h-full object-cover">
                        </div>

                        @if(!$isReadOnlyVersion)
                            <div class="flex items-center justify-between gap-2 pt-1">
                                <label class="cursor-pointer px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50 rounded-lg border border-blue-200 transition-colors">
                                    <span>Substituir Arte</span>
                                    <input type="file" wire:model="backgroundImage" accept="image/png,image/jpeg,image/webp" class="hidden">
                                </label>

                                <button type="button"
                                        wire:click="removeBackgroundImage"
                                        wire:confirm="Deseja desvincular a arte de fundo deste rascunho?"
                                        class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg border border-rose-200 transition-colors cursor-pointer">
                                    Remover Arte
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col gap-2">
                        @if(!$isReadOnlyVersion)
                            <div class="border-2 border-dashed border-slate-200 hover:border-blue-400 rounded-xl p-4 text-center transition-colors bg-slate-50/50">
                                <input type="file"
                                       id="bgImageFileInput"
                                       wire:model="backgroundImage"
                                       accept="image/png,image/jpeg,image/webp"
                                       class="hidden">

                                <label for="bgImageFileInput" class="cursor-pointer flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-slate-800">Enviar Arte Pronta do Certificado</span>
                                        <p class="text-[11px] text-slate-500 mt-0.5">PNG, JPG ou WebP (máx. 5MB, mínimo 800×500 px)</p>
                                    </div>
                                </label>
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">Nenhuma arte gráfica de fundo vinculada a esta versão.</p>
                        @endif
                    </div>
                @endif

                @if($backgroundImage)
                    <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 text-xs font-medium text-blue-800 truncate">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span class="truncate">Arquivo selecionado pronto</span>
                        </div>
                        <button type="button"
                                wire:click="uploadBackgroundImage"
                                wire:loading.attr="disabled"
                                class="px-3 py-1 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 shrink-0 cursor-pointer">
                            <span wire:loading.remove wire:target="uploadBackgroundImage">Aplicar</span>
                            <span wire:loading wire:target="uploadBackgroundImage">Salvando...</span>
                        </button>
                    </div>
                @endif

                <div wire:loading wire:target="backgroundImage" class="text-2xs text-blue-600 font-semibold text-center">
                    Carregando arquivo selecionado...
                </div>

                @error('backgroundImage')
                    <div class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-2xs text-rose-700 font-medium">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- CARD 1: PROPRIEDADES DO ELEMENTO SELECIONADO -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Painel de Propriedades</h2>
                        <p class="text-xs text-slate-500">
                            @if($selectedIndex !== null)
                                Editando: <span class="font-mono font-semibold text-blue-600">{{ $selectedElementId }}</span>
                            @else
                                Nenhum elemento selecionado
                            @endif
                        </p>
                    </div>

                    @if($selectedIndex !== null && !$isReadOnlyVersion && count($schema['elements'] ?? []) > 1)
                        <button type="button"
                                wire:click="removeElement('{{ $selectedElementId }}')"
                                class="px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors cursor-pointer"
                                title="Remover elemento selecionado">
                            Excluir Elemento
                        </button>
                    @endif
                </div>

                <!-- Seletor Rápido de Elementos (Camadas) -->
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                        Camadas / Elementos do Template
                    </label>
                    <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto p-1 bg-slate-50 rounded-xl border border-slate-200/70">
                        @foreach(($schema['elements'] ?? []) as $elItem)
                            @php $itemId = $elItem['id'] ?? ''; @endphp
                            <button type="button"
                                    wire:click="selectElement('{{ $itemId }}')"
                                    class="px-2.5 py-1 rounded-lg text-xs font-mono transition-all cursor-pointer {{ $selectedElementId === $itemId ? 'bg-blue-600 text-white font-semibold shadow-2xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                                {{ $itemId }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if($selectedIndex !== null)
                    <!-- Coordenadas e Dimensões (X, Y, Largura, Altura) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-2 border-t border-slate-100">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Eixo X (px)</label>
                            <input type="number"
                                   min="0"
                                   max="1122"
                                   @disabled($isReadOnlyVersion)
                                   wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.x"
                                   class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Eixo Y (px)</label>
                            <input type="number"
                                   min="0"
                                   max="793"
                                   @disabled($isReadOnlyVersion)
                                   wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.y"
                                   class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Largura (px)</label>
                            <input type="number"
                                   min="10"
                                   max="1122"
                                   @disabled($isReadOnlyVersion)
                                   wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.width"
                                   class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Altura (px)</label>
                            <input type="number"
                                   min="4"
                                   max="793"
                                   @disabled($isReadOnlyVersion)
                                   wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.height"
                                   class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Tipo do Elemento (Whitelist do Schema) -->
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Elemento</label>
                        <select wire:model.live="schema.elements.{{ $selectedIndex }}.type"
                                @disabled($isReadOnlyVersion)
                                class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @foreach($allowedElementTypes as $typeOption)
                                <option value="{{ $typeOption }}">{{ $typeOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Conteúdo Textual / Variáveis -->
                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Conteúdo do Elemento (Texto e Variáveis)
                        </label>
                        <textarea rows="3"
                                  @disabled($isReadOnlyVersion)
                                  wire:model.live.debounce.300ms="schema.elements.{{ $selectedIndex }}.content"
                                  placeholder="Digite o texto estático ou insira variáveis @{{student_name}}..."
                                  class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 font-sans"></textarea>
                        <span class="text-[11px] text-slate-400 block mt-0.5">
                            HTML não é permitido. Utilize apenas texto simples e marcadores oficiais.
                        </span>
                    </div>

                    <!-- Tipografia (Fonte, Tamanho, Peso, Alinhamento, Cor) -->
                    <div class="pt-2 border-t border-slate-100 flex flex-col gap-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tipografia & Estilo</span>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Família da Fonte</label>
                                <select wire:model.live="schema.elements.{{ $selectedIndex }}.style.font_family"
                                        @disabled($isReadOnlyVersion)
                                        class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                                    @foreach($allowedFonts as $font)
                                        <option value="{{ $font }}">{{ $font }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tamanho (ex: 16px)</label>
                                <input type="text"
                                       @disabled($isReadOnlyVersion)
                                       wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.style.font_size"
                                       placeholder="16px"
                                       class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Peso da Fonte</label>
                                <select wire:model.live="schema.elements.{{ $selectedIndex }}.style.font_weight"
                                        @disabled($isReadOnlyVersion)
                                        class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                                    @foreach($allowedFontWeights as $weight)
                                        <option value="{{ $weight }}">{{ $weight }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Alinhamento</label>
                                <select wire:model.live="schema.elements.{{ $selectedIndex }}.style.text_align"
                                        @disabled($isReadOnlyVersion)
                                        class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                                    @foreach($allowedTextAligns as $align)
                                        <option value="{{ $align }}">{{ $align }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cor do Texto (Hex)</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="color"
                                           @disabled($isReadOnlyVersion)
                                           wire:model.live="schema.elements.{{ $selectedIndex }}.style.color"
                                           class="w-8 h-8 rounded border border-slate-200 cursor-pointer p-0.5 bg-white">
                                    <input type="text"
                                           @disabled($isReadOnlyVersion)
                                           wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.style.color"
                                           placeholder="#ffffff"
                                           class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Transformação</label>
                                <select wire:model.live="schema.elements.{{ $selectedIndex }}.style.text_transform"
                                        @disabled($isReadOnlyVersion)
                                        class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                                    <option value="none">Normal (none)</option>
                                    <option value="uppercase">MAIÚSCULAS</option>
                                    <option value="capitalize">Capitalizado</option>
                                    <option value="lowercase">minúsculas</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Espaçamento (ex: 2px)</label>
                                <input type="text"
                                       @disabled($isReadOnlyVersion)
                                       wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.style.letter_spacing"
                                       placeholder="0px"
                                       class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Altura de Linha</label>
                                <input type="text"
                                       @disabled($isReadOnlyVersion)
                                       wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.style.line_height"
                                       placeholder="1.5"
                                       class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Fundo e Borda do Elemento -->
                    <div class="pt-2 border-t border-slate-100 flex flex-col gap-2.5">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Caixa & Borda do Elemento</span>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cor da Borda</label>
                                <input type="text"
                                       @disabled($isReadOnlyVersion)
                                       wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.style.border_color"
                                       placeholder="#475569"
                                       class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Espessura Borda (px)</label>
                                <input type="number"
                                       min="0"
                                       max="20"
                                       @disabled($isReadOnlyVersion)
                                       wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.style.border_width"
                                       class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Estilo da Borda</label>
                                <select wire:model.live="schema.elements.{{ $selectedIndex }}.style.border_style"
                                        @disabled($isReadOnlyVersion)
                                        class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                                    <option value="">Sem borda</option>
                                    @foreach($allowedBorderStyles as $bStyle)
                                        <option value="{{ $bStyle }}">{{ $bStyle }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Padding Interno (px)</label>
                                <input type="number"
                                       min="0"
                                       max="60"
                                       @disabled($isReadOnlyVersion)
                                       wire:model.live.debounce.250ms="schema.elements.{{ $selectedIndex }}.style.padding"
                                       class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- CARD 2: ESTILO GLOBAL DA PÁGINA A4 (FUNDO & MOLDURA EXTERNA) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 flex flex-col gap-3.5">
                <div class="border-b border-slate-100 pb-2.5">
                    <h3 class="text-sm font-bold text-slate-900">Fundo e Moldura da Folha A4</h3>
                    <p class="text-xs text-slate-500">Configurações globais aplicadas ao documento inteiro.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cor de Fundo A4</label>
                        <div class="flex items-center gap-1.5">
                            <input type="color"
                                   @disabled($isReadOnlyVersion)
                                   wire:model.live="schema.background.color"
                                   class="w-8 h-8 rounded border border-slate-200 cursor-pointer p-0.5 bg-white">
                            <input type="text"
                                   @disabled($isReadOnlyVersion)
                                   wire:model.live.debounce.250ms="schema.background.color"
                                   class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cor da Moldura A4</label>
                        <div class="flex items-center gap-1.5">
                            <input type="color"
                                   @disabled($isReadOnlyVersion)
                                   wire:model.live="schema.border.color"
                                   class="w-8 h-8 rounded border border-slate-200 cursor-pointer p-0.5 bg-white">
                            <input type="text"
                                   @disabled($isReadOnlyVersion)
                                   wire:model.live.debounce.250ms="schema.border.color"
                                   class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Espessura Moldura (px)</label>
                        <input type="number"
                               min="0"
                               max="15"
                               @disabled($isReadOnlyVersion)
                               wire:model.live.debounce.250ms="schema.border.width"
                               class="w-full px-2.5 py-1.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Estilo da Moldura</label>
                        <select wire:model.live="schema.border.style"
                                @disabled($isReadOnlyVersion)
                                class="w-full px-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800">
                            @foreach($allowedBorderStyles as $bStyle)
                                <option value="{{ $bStyle }}">{{ $bStyle }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE PRÉVIA PDF EM TEMPO REAL (DOMPDF SERVER-SIDE) -->
    <div x-show="showPreviewModal"
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-5xl w-full h-[85vh] flex flex-col overflow-hidden"
             @keydown.escape.window="closePdfPreview()">

            <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Prévia Oficial em PDF (Renderizador Dompdf A4)</h3>
                    <p class="text-[11px] text-slate-500">Gerado em memória com dados demonstrativos sem persistir certificados nem alterar versões publicadas.</p>
                </div>
                <button type="button"
                        @click="closePdfPreview()"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-200 transition-colors cursor-pointer">
                    Fechar Prévia
                </button>
            </div>

            <div class="flex-1 bg-slate-800 flex items-center justify-center relative overflow-hidden">
                <template x-if="previewLoading">
                    <div class="flex flex-col items-center gap-2 text-white">
                        <svg class="animate-spin h-7 w-7 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs font-medium">Renderizando PDF A4 no servidor via VersionedCertificateRenderer...</span>
                    </div>
                </template>

                <template x-if="previewError">
                    <div class="p-6 max-w-md bg-rose-950/90 border border-rose-500/40 rounded-2xl text-rose-100 text-center">
                        <p class="text-sm font-bold mb-1">Schema Rejeitado pelo Validador</p>
                        <p class="text-xs leading-relaxed" x-text="previewError"></p>
                    </div>
                </template>

                <template x-if="previewUrl && !previewLoading && !previewError">
                    <iframe :src="previewUrl" class="w-full h-full border-0" title="Prévia PDF do Certificado"></iframe>
                </template>
            </div>
        </div>
    </div>
</div>
