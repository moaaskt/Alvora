<div class="px-space-xl py-space-lg flex flex-col gap-6 max-w-[1600px] mx-auto w-full">
    <!-- BREADCRUMBS & TOP BAR -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-edtech-navy transition-colors">
                <x-icon name="arrow-left" class="w-3.5 h-3.5" />
                <span>Voltar ao Dashboard</span>
            </a>
            <span class="text-slate-300">/</span>
            <span>Gestão Corporativa</span>
            <span class="text-slate-300">/</span>
            <span class="text-edtech-navy font-semibold">Templates de Certificados</span>
        </div>

        <div class="inline-flex items-center bg-slate-100 text-slate-600 text-xs px-3 py-1.5 rounded-full font-medium self-start md:self-auto">
            Modelo Vigente: {{ $templateInUse->name ?? 'Template Padrão Institucional ALVORA' }}
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <div x-data x-init="
        @if(session('success')) AlvoraSwal.fire({ icon: 'success', title: 'Pronto!', text: '{{ session('success') }}', toast: true, position: 'top-end', timer: 2500, timerProgressBar: true, showConfirmButton: false }); @endif
        @if(session('error')) AlvoraSwal.fire({ icon: 'error', title: 'Erro', text: '{{ session('error') }}', confirmButtonText: 'Fechar' }); @endif
        @if(session('warning')) AlvoraSwal.fire({ icon: 'warning', title: 'Atenção', text: '{{ session('warning') }}', confirmButtonText: 'Entendi' }); @endif
        @if(session('info')) AlvoraSwal.fire({ icon: 'info', title: 'Informação', text: '{{ session('info') }}', toast: true, position: 'top-end', timer: 2500, timerProgressBar: true, showConfirmButton: false }); @endif
    "></div>

    <!-- HEADER DA PÁGINA -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl lg:text-[28px] font-semibold text-edtech-navy tracking-tight leading-tight">
                Templates de Certificados
            </h1>
            <p class="text-sm text-slate-500">
                Gerencie os modelos visuais e a identidade gráfica dos certificados emitidos aos colaboradores da sua organização.
            </p>
        </div>

        <button type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center justify-center gap-2 bg-edtech-blue hover:bg-edtech-blue-dark text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-colors self-start sm:self-auto shrink-0 cursor-pointer">
            <x-icon name="plus" class="w-4 h-4" />
            <span>+ Novo Template Corporativo</span>
        </button>
    </div>

    <!-- ACTIVE TEMPLATE BANNER -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-edtech-border flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-edtech-blue-light flex items-center justify-center shrink-0 text-edtech-blue">
                <x-icon name="award" class="w-7 h-7" />
            </div>
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs uppercase tracking-wider font-semibold text-slate-500">Template em uso</span>
                    @if($activeVersionInUse)
                        <span class="bg-slate-100 text-slate-700 font-mono rounded px-2 py-0.5 text-xs">v{{ $activeVersionInUse->version }}</span>
                    @endif
                </div>
                <h2 class="text-lg font-semibold text-edtech-navy leading-tight">
                    {{ $templateInUse->name ?? 'Template Padrão Institucional ALVORA' }}
                </h2>
                <p class="text-sm text-slate-500 max-w-3xl line-clamp-2 leading-relaxed">
                    @if($isUsingGlobalDefault)
                        Sua empresa está utilizando o design institucional padrão da ALVORA. Ao criar e publicar um template corporativo próprio, ele assumirá automaticamente a emissão dos próximos certificados.
                    @else
                        {{ $templateInUse->description ?: 'Este modelo personalizado está ativo como padrão oficial para todos os certificados emitidos na conclusão de cursos pelos seus colaboradores.' }}
                    @endif
                </p>
            </div>
        </div>

        @if($isUsingGlobalDefault)
            <div class="flex items-center shrink-0">
                <button type="button"
                        wire:click="openCreateModal"
                        class="border border-edtech-blue text-edtech-blue hover:bg-edtech-blue-light px-4 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer">
                    Personalizar Modelo
                </button>
            </div>
        @endif
    </div>

    <!-- SEARCH AND FILTER BAR -->
    <div class="bg-white rounded-2xl p-4 border border-edtech-border flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="relative w-full flex-grow">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <x-icon name="search" class="w-4 h-4" />
            </div>
            <input type="text"
                   id="template-search"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar template corporativo..."
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-edtech-border rounded-lg text-sm text-edtech-navy placeholder-slate-400 focus:outline-none focus:border-edtech-blue focus:ring-1 focus:ring-edtech-blue transition-colors">
        </div>

        <div class="w-full md:w-64 shrink-0">
            <select wire:model.live="statusFilter"
                    id="status-filter"
                    class="w-full py-2.5 px-3.5 border border-edtech-border rounded-lg text-sm text-edtech-navy bg-white focus:outline-none focus:border-edtech-blue focus:ring-1 focus:ring-edtech-blue cursor-pointer transition-colors">
                <option value="">Todos os Status</option>
                <option value="active">Ativos</option>
                <option value="draft">Rascunhos</option>
                <option value="archived">Arquivados</option>
            </select>
        </div>
    </div>

    <!-- LISTAGEM DE TEMPLATES CORPORATIVOS -->
    <div class="flex flex-col gap-4">
        @if($templates->isEmpty())
            <!-- ESTADO VAZIO (EMPTY STATE) -->
            <div class="bg-white rounded-2xl border border-edtech-border p-12 text-center shadow-sm flex flex-col items-center justify-center">
                <div class="w-10 h-10 mx-auto mb-3 text-slate-300 flex items-center justify-center">
                    <x-icon name="file-text" class="w-10 h-10" />
                </div>

                <h3 class="text-base font-semibold text-edtech-navy mb-1">
                    {{ $search || $statusFilter ? 'Nenhum template encontrado com os filtros aplicados' : 'Nenhum template personalizado cadastrado' }}
                </h3>

                <p class="text-sm text-slate-500 mb-5 max-w-md mx-auto leading-relaxed">
                    {{ $search || $statusFilter ? 'Tente ajustar os termos da pesquisa para encontrar o template desejado.' : 'Sua empresa está utilizando o modelo padrão ALVORA. Crie seu primeiro template corporativo para personalizar cores, logotipo e assinaturas.' }}
                </p>

                @if($search || $statusFilter)
                    <button type="button"
                            wire:click="$set('search', ''); $set('statusFilter', '')"
                            class="text-sm font-semibold text-edtech-blue hover:underline cursor-pointer">
                        Limpar filtros
                    </button>
                @else
                    <button type="button"
                            wire:click="openCreateModal"
                            class="inline-flex items-center gap-2 bg-edtech-blue hover:bg-edtech-blue-dark text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-colors cursor-pointer">
                        <x-icon name="plus" class="w-4 h-4" />
                        <span>Criar Primeiro Template Corporativo</span>
                    </button>
                @endif
            </div>
        @else
            <!-- GRID DE TEMPLATES (3 COLUNAS) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($templates as $template)
                    @php
                        $isActiveInUse = !$isUsingGlobalDefault && $templateInUse && $templateInUse->id === $template->id;
                        $hasActiveVersion = (bool) $template->activeVersion;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm p-6 flex flex-col justify-between hover:-translate-y-1 hover:shadow-md transition duration-200 ease-out {{ $isActiveInUse ? 'border border-edtech-blue ring-2 ring-edtech-blue/20' : 'border border-edtech-border' }}">
                        <!-- CARD CONTENT -->
                        <div>
                            <!-- TOP ROW: BADGES & ID -->
                            <div class="flex items-center justify-between mb-4 gap-2">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if($isActiveInUse)
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-edtech-blue text-white leading-none">
                                            Em Uso
                                        </span>
                                    @endif

                                    @if($template->is_default)
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-700 leading-none">
                                            Padrão da Empresa
                                        </span>
                                    @endif

                                    @if($template->status === 'active')
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700 leading-none">
                                            Ativo
                                        </span>
                                    @elseif($template->status === 'draft')
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 leading-none">
                                            Rascunho
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-600 leading-none">
                                            Arquivado
                                        </span>
                                    @endif
                                </div>

                                <span class="font-mono text-xs text-slate-400 shrink-0">#{{ $template->id }}</span>
                            </div>

                            <!-- TITLE & DESCRIPTION -->
                            <h3 class="text-lg font-semibold text-edtech-navy leading-tight mb-2 truncate" title="{{ $template->name }}">
                                {{ $template->name }}
                            </h3>

                            <p class="text-sm text-slate-500 mb-5 line-clamp-2 min-h-[40px] leading-relaxed">
                                {{ $template->description ?: 'Sem descrição informada para este modelo.' }}
                            </p>

                            <!-- META ROW -->
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                                <div class="flex items-center gap-1.5 text-[13px]">
                                    <span>Versão:</span>
                                    @if($template->activeVersion)
                                        <span class="bg-slate-100 text-slate-700 font-mono rounded px-1.5 py-0.5 text-xs font-semibold">
                                            v{{ $template->activeVersion->version }}
                                        </span>
                                    @else
                                        <span class="italic text-amber-600 text-xs">Sem versão ativa</span>
                                    @endif
                                </div>

                                <span>Atualizado: {{ $template->updated_at->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <!-- DIVIDER & ACTIONS -->
                        <div class="border-t border-edtech-border pt-4 mt-4 flex items-center justify-between gap-2">
                            <a href="{{ route('instructor.certificate-templates.edit', $template->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-edtech-blue hover:text-white hover:bg-edtech-blue border border-edtech-blue/30 hover:border-edtech-blue transition-colors shadow-2xs"
                               title="Abrir Editor Visual com Canvas A4">
                                <x-icon name="pencil" class="w-3.5 h-3.5" />
                                <span>Abrir Editor Visual</span>
                            </a>

                            @if($template->status === 'archived')
                                <div>
                                    <button type="button"
                                            wire:click="unarchiveTemplate({{ $template->id }})"
                                            wire:loading.attr="disabled"
                                            class="text-xs font-semibold text-edtech-blue hover:underline transition-all cursor-pointer">
                                        Reativar
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center gap-1">
                                    <!-- Duplicar -->
                                    <button type="button"
                                            wire:click="duplicateTemplate({{ $template->id }})"
                                            wire:loading.attr="disabled"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-edtech-blue hover:bg-slate-50 transition-colors cursor-pointer"
                                            title="Duplicar este template como um novo modelo">
                                        <span class="sr-only">Duplicar</span>
                                        <x-icon name="copy" class="w-4 h-4" />
                                    </button>

                                    <!-- Definir como Padrão -->
                                    @if(!$template->is_default && $hasActiveVersion && $template->status === 'active')
                                        <button type="button"
                                                wire:click="setAsDefault({{ $template->id }})"
                                                wire:loading.attr="disabled"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-amber-500 hover:bg-slate-50 transition-colors cursor-pointer"
                                                title="Definir este template como padrão da empresa">
                                            <span class="sr-only">Tornar Padrão</span>
                                            <x-icon name="star" class="w-4 h-4" />
                                        </button>
                                    @endif

                                    <!-- Arquivar -->
                                    <button type="button"
                                            x-on:click="AlvoraSwal.fire({ icon: 'warning', title: 'Tem certeza?', text: 'Tem certeza de que deseja arquivar este template? Ele deixará de ser emitido para novos certificados.', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Sim, excluir', cancelButtonText: 'Cancelar' }).then(r => { if (r.isConfirmed) $wire.archiveTemplate({{ $template->id }}) })"
                                            wire:loading.attr="disabled"
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Arquivar template">
                                        <span class="sr-only">Arquivar</span>
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- MODAL DE CRIAÇÃO DE TEMPLATE -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-edtech-navy/50 backdrop-blur-xs animate-in fade-in duration-150">
            <div class="bg-white rounded-[20px] border border-edtech-border shadow-2xl max-w-lg w-full overflow-hidden" 
                 wire:keydown.escape="closeCreateModal">
                
                <div class="p-6 border-b border-edtech-border flex items-start justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-edtech-navy leading-tight">Novo Template Corporativo</h3>
                        <p class="text-sm text-slate-500 mt-0.5">Defina as informações iniciais para seu modelo personalizado.</p>
                    </div>
                    <button type="button" 
                            wire:click="closeCreateModal"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        <x-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit="createTemplate" class="p-6 flex flex-col gap-4">
                    <div>
                        <label for="newTemplateName" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nome do Modelo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                                id="newTemplateName"
                                wire:model="newTemplateName"
                                placeholder="Ex: Certificado Executivo 2026"
                                class="w-full h-10 px-3.5 bg-white border border-edtech-border rounded-lg text-sm text-edtech-navy placeholder-slate-400 focus:outline-none focus:border-edtech-blue focus:ring-1 focus:ring-edtech-blue transition-colors">
                        @error('newTemplateName')
                            <span class="text-xs text-rose-600 font-medium mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="newTemplateDescription" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Descrição (Opcional)
                        </label>
                        <textarea id="newTemplateDescription"
                                  wire:model="newTemplateDescription"
                                  rows="3"
                                  placeholder="Ex: Modelo oficial com layout escuro..."
                                  class="w-full p-3 bg-white border border-edtech-border rounded-lg text-sm text-edtech-navy placeholder-slate-400 focus:outline-none focus:border-edtech-blue focus:ring-1 focus:ring-edtech-blue resize-none transition-colors"></textarea>
                        @error('newTemplateDescription')
                            <span class="text-xs text-rose-600 font-medium mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="bg-edtech-blue-light border border-edtech-blue-border rounded-xl p-3.5 flex items-start gap-3 text-xs text-slate-600 leading-relaxed">
                        <div class="text-edtech-blue shrink-0 mt-0.5">
                            <x-icon name="info" class="w-4 h-4" />
                        </div>
                        <p>
                            O template será inicializado com a Versão 1 em rascunho baseada no layout institucional padrão da ALVORA, pronto para customização visual e publicação.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-edtech-border mt-1">
                        <button type="button"
                                wire:click="closeCreateModal"
                                class="border border-edtech-border text-slate-600 hover:bg-slate-50 px-4 py-2 rounded-lg text-sm font-semibold transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="bg-edtech-blue hover:bg-edtech-blue-dark text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors cursor-pointer">
                            <span wire:loading.remove>Criar Template</span>
                            <span wire:loading>Criando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>