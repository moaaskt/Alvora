<div class="px-space-xl py-space-lg flex flex-col gap-6 max-w-[1600px] mx-auto w-full">
    <!-- BREADCRUMBS & TOP BAR -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition-colors bg-white px-2.5 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
                <svg class="w-3.5 h-3.5 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
                </svg>
                <span>Voltar ao Dashboard</span>
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-500 font-medium">Gestão Corporativa</span>
            <span class="text-slate-300">/</span>
            <span class="text-xs text-slate-800 font-semibold">Templates de Certificados</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                <span class="w-2 h-2 rounded-full {{ $isUsingGlobalDefault ? 'bg-indigo-500' : 'bg-emerald-500' }}"></span>
                <span>Modelo Vigente: <strong>{{ $templateInUse->name ?? 'Padrão ALVORA' }}</strong></span>
            </span>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"></path>
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-medium flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"></path>
                </svg>
                <span>{{ session('info') }}</span>
            </div>
        </div>
    @endif

    <!-- HEADER DA PÁGINA COM AÇÕES -->
    <header class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2 border-b border-slate-200/70">
        <div class="flex flex-col gap-1 max-w-3xl">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Templates de Certificados
            </h1>
            <p class="text-xs text-slate-500">
                Gerencie os modelos visuais e a identidade gráfica dos certificados emitidos aos colaboradores da sua organização.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition-all text-xs font-semibold shadow-sm cursor-pointer">
                <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                <span>Novo Template Corporativo</span>
            </button>
        </div>
    </header>

    <!-- BANNER DE STATUS DO TEMPLATE ATUALMENTE EM USO -->
    <section class="rounded-2xl border p-5 transition-all shadow-2xs {{ $isUsingGlobalDefault ? 'bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border-indigo-800 text-white' : 'bg-gradient-to-r from-emerald-950 via-slate-900 to-slate-900 border-emerald-800 text-white' }}">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="p-3 rounded-xl {{ $isUsingGlobalDefault ? 'bg-indigo-600/30 border border-indigo-400/30' : 'bg-emerald-600/30 border border-emerald-400/30' }}">
                    <svg class="w-6 h-6 {{ $isUsingGlobalDefault ? 'text-indigo-300' : 'text-emerald-300' }} stroke-[1.75]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-semibold uppercase tracking-wider px-2 py-0.5 rounded-md {{ $isUsingGlobalDefault ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-400/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' }}">
                            {{ $isUsingGlobalDefault ? 'Padrão Institucional ALVORA' : 'Modelo Exclusivo Ativo' }}
                        </span>
                        @if($activeVersionInUse)
                            <span class="text-xs text-slate-300 font-mono">Versão v{{ $activeVersionInUse->version }}</span>
                        @endif
                    </div>
                    <h3 class="text-base font-bold text-white">
                        {{ $templateInUse->name ?? 'Template Institucional Oficial' }}
                    </h3>
                    <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        @if($isUsingGlobalDefault)
                            Sua empresa está utilizando o design institucional padrão da ALVORA. Ao criar e publicar um template corporativo próprio, ele assumirá automaticamente a emissão dos próximos certificados.
                        @else
                            Este modelo personalizado está ativo como padrão oficial para todos os certificados emitidos na conclusão de cursos pelos seus colaboradores.
                        @endif
                    </p>
                </div>
            </div>

            @if($isUsingGlobalDefault)
                <button type="button" 
                        wire:click="openCreateModal"
                        class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-all shadow-sm shrink-0 cursor-pointer">
                    Personalizar Modelo
                </button>
            @endif
        </div>
    </section>

    <!-- BARRA DE PESQUISA E FILTROS -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs">
        <div class="relative w-full sm:w-80">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Buscar template corporativo..."
                   class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-800 placeholder-slate-400">
            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
            </svg>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <select wire:model.live="statusFilter"
                    class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 text-slate-700 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer">
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
            <div class="p-12 text-center bg-white rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col items-center justify-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 mb-4">
                    <svg class="w-8 h-8 stroke-[1.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900 mb-1">
                    {{ $search || $statusFilter ? 'Nenhum template encontrado com os filtros aplicados' : 'Nenhum template personalizado cadastrado' }}
                </h3>
                <p class="text-xs text-slate-500 max-w-md mb-5 leading-relaxed">
                    {{ $search || $statusFilter ? 'Tente ajustar os termos da pesquisa para encontrar o template desejado.' : 'Sua empresa está utilizando o modelo padrão ALVORA. Crie seu primeiro template corporativo para personalizar cores, logotipo e assinaturas.' }}
                </p>

                @if(!$search && !$statusFilter)
                    <button type="button"
                            wire:click="openCreateModal"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-xs font-semibold transition-all shadow-sm cursor-pointer">
                        <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                        </svg>
                        <span>Criar Primeiro Template Corporativo</span>
                    </button>
                @endif
            </div>
        @else
            <!-- GRID DE TEMPLATES -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($templates as $template)
                    @php
                        $isActiveInUse = !$isUsingGlobalDefault && $templateInUse && $templateInUse->id === $template->id;
                        $hasActiveVersion = (bool) $template->activeVersion;
                    @endphp
                    <div class="bg-white rounded-2xl border transition-all duration-200 hover:shadow-md flex flex-col justify-between overflow-hidden {{ $isActiveInUse ? 'border-emerald-400 ring-2 ring-emerald-500/20' : 'border-slate-200/80 shadow-2xs' }}">
                        <!-- CARD HEADER -->
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <!-- BADGE DE STATUS -->
                                    @if($template->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-2xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Ativo
                                        </span>
                                    @elseif($template->status === 'draft')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-2xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Rascunho
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-2xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            Arquivado
                                        </span>
                                    @endif

                                    <!-- BADGE DE PADRÃO -->
                                    @if($template->is_default)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-2xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            Padrão da Empresa
                                        </span>
                                    @endif

                                    @if($isActiveInUse)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-2xs font-semibold bg-emerald-600 text-white shadow-2xs">
                                            Em Uso
                                        </span>
                                    @endif
                                </div>

                                <span class="text-2xs font-mono text-slate-400">#{{ $template->id }}</span>
                            </div>

                            <h3 class="text-sm font-bold text-slate-900 line-clamp-1 mb-1">
                                {{ $template->name }}
                            </h3>

                            <p class="text-xs text-slate-500 line-clamp-2 min-h-[32px] leading-relaxed">
                                {{ $template->description ?: 'Sem descrição informada para este modelo.' }}
                            </p>

                            <!-- INFORMAÇÕES DE VERSÃO -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-2xs text-slate-500">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium text-slate-700">Versão:</span>
                                    @if($template->activeVersion)
                                        <span class="font-mono font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">
                                            v{{ $template->activeVersion->version }}
                                        </span>
                                    @else
                                        <span class="italic text-amber-600">Sem versão ativa</span>
                                    @endif
                                </div>

                                <div>
                                    <span>Atualizado: {{ $template->updated_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- CARD FOOTER / AÇÕES -->
                        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <!-- BOTÃO DO EDITOR VISUAL (FASE 3.3) -->
                                <a href="{{ route('instructor.certificate-templates.edit', $template->id) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 transition-all shadow-2xs"
                                   title="Abrir Editor Visual com Canvas A4">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"></path>
                                    </svg>
                                    <span>Abrir Editor Visual</span>
                                </a>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <!-- DUPLICAR TEMPLATE -->
                                <button type="button"
                                        wire:click="duplicateTemplate({{ $template->id }})"
                                        wire:loading.attr="disabled"
                                        class="p-1.5 text-xs text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                        title="Duplicar este template como um novo modelo">
                                    <span class="sr-only">Duplicar</span>
                                    <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"></path>
                                    </svg>
                                </button>

                                <!-- DEFINIR COMO PADRÃO -->
                                @if(!$template->is_default && $hasActiveVersion && $template->status === 'active')
                                    <button type="button"
                                            wire:click="setAsDefault({{ $template->id }})"
                                            wire:loading.attr="disabled"
                                            class="p-1.5 text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                            title="Definir este template como padrão da empresa">
                                        <span class="sr-only">Tornar Padrão</span>
                                        <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"></path>
                                        </svg>
                                    </button>
                                @endif

                                <!-- ARQUIVAR TEMPLATE -->
                                @if($template->status !== 'archived')
                                    <button type="button"
                                            wire:click="archiveTemplate({{ $template->id }})"
                                            wire:confirm="Tem certeza de que deseja arquivar este template? Ele deixará de ser emitido para novos certificados."
                                            wire:loading.attr="disabled"
                                            class="p-1.5 text-xs text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Arquivar template">
                                        <span class="sr-only">Arquivar</span>
                                        <svg class="w-4 h-4 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- MODAL DE CRIAÇÃO DE TEMPLATE -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full overflow-hidden" 
                 wire:keydown.escape="closeCreateModal">
                
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Novo Template Corporativo</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Defina as informações iniciais para seu modelo personalizado.</p>
                    </div>
                    <button type="button" 
                            wire:click="closeCreateModal"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit="createTemplate" class="p-6 flex flex-col gap-4">
                    <div>
                        <label for="newTemplateName" class="block text-xs font-semibold text-slate-700 mb-1">
                            Nome do Modelo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               id="newTemplateName"
                               wire:model="newTemplateName"
                               placeholder="Ex: Certificado Executivo 2026"
                               class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-800">
                        @error('newTemplateName')
                            <span class="text-2xs text-rose-600 font-medium mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="newTemplateDescription" class="block text-xs font-semibold text-slate-700 mb-1">
                            Descrição (Opcional)
                        </label>
                        <textarea id="newTemplateDescription"
                                  wire:model="newTemplateDescription"
                                  rows="3"
                                  placeholder="Ex: Modelo oficial com layout escuro para cursos da área de tecnologia e liderança."
                                  class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-800"></textarea>
                        @error('newTemplateDescription')
                            <span class="text-2xs text-rose-600 font-medium mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="p-3.5 rounded-xl bg-blue-50/60 border border-blue-100 text-2xs text-blue-900 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"></path>
                        </svg>
                        <p class="leading-relaxed">
                            O template será inicializado com a Versão 1 em rascunho baseada no layout institucional padrão da ALVORA, pronto para customização visual e publicação.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button"
                                wire:click="closeCreateModal"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-all shadow-sm cursor-pointer">
                            <span wire:loading.remove>Criar Template</span>
                            <span wire:loading>Criando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
