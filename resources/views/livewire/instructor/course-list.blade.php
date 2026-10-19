<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Catálogo de Treinamentos</h1>
            <p class="text-sm text-slate-400 mt-1">
                Crie e gerencie os cursos corporativos disponíveis para capacitação do time.
            </p>
        </div>

        <a href="{{ route('instructor.courses.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 shadow-md shadow-indigo-600/30 transition-all hover:scale-[1.02]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Criar Novo Curso</span>
        </a>
    </div>

    <!-- Search & Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative max-w-md w-full">
            <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar cursos por título ou descrição..."
                   class="w-full bg-slate-900/80 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-900/80 border border-slate-800 rounded-xl text-xs">
            <button wire:click="$set('statusFilter', 'all')" type="button"
                    class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ $statusFilter === 'all' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white' }}">
                Todos
            </button>
            <button wire:click="$set('statusFilter', 'published')" type="button"
                    class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ $statusFilter === 'published' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white' }}">
                Publicados
            </button>
            <button wire:click="$set('statusFilter', 'draft')" type="button"
                    class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ $statusFilter === 'draft' ? 'bg-amber-600 text-white' : 'text-slate-400 hover:text-white' }}">
                Rascunhos
            </button>
        </div>
    </div>

    <!-- Course Grid -->
    @if($courses->isEmpty())
        <div class="p-12 text-center rounded-2xl bg-slate-900/40 border border-slate-800/80 space-y-4">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-800/80 border border-slate-700 text-slate-400 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white">Nenhum curso cadastrado ainda</h3>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">
                Comece criando o primeiro curso corporativo para capacitar sua equipe.
            </p>
            <a href="{{ route('instructor.courses.create') }}" 
               class="inline-flex items-center text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                Criar o primeiro curso &rarr;
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $course)
                <div class="flex flex-col justify-between rounded-2xl bg-slate-900/70 border border-slate-800 hover:border-slate-700 overflow-hidden shadow-xl transition-all group">
                    <div>
                        <!-- Thumbnail / Banner -->
                        <div class="relative h-44 bg-gradient-to-tr from-slate-900 via-indigo-950/60 to-slate-900 border-b border-slate-800/80 flex items-center justify-center overflow-hidden">
                            @if($course->thumbnail)
                                <img src="{{ $course->thumbnail }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="text-center p-4">
                                    <span class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">
                                        {{ substr($course->title, 0, 2) }}
                                    </span>
                                    <span class="block text-[11px] text-slate-500 uppercase tracking-widest mt-1 font-semibold">Curso Corporativo</span>
                                </div>
                            @endif

                            <!-- Status Badge -->
                            <div class="absolute top-3 right-3">
                                @if($course->isPublished())
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1.5 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Publicado
                                    </span>
                                @elseif($course->isDraft())
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1.5 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Rascunho
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                        Arquivado
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Content Info -->
                        <div class="p-6 space-y-3">
                            <h3 class="text-base font-bold text-white group-hover:text-indigo-300 transition-colors line-clamp-1">
                                {{ $course->title }}
                            </h3>
                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                {{ $course->description ?: 'Sem descrição informada.' }}
                            </p>

                            <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $course->instructor?->name ?? 'Instrutor' }}
                                </span>
                                <span>
                                    {{ $course->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="p-4 bg-slate-950/40 border-t border-slate-800/80 flex items-center justify-between gap-2">
                        <!-- Gerenciar Aulas -->
                        <a href="{{ route('instructor.courses.curriculum', $course) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 text-xs font-semibold border border-indigo-500/30 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            <span>Aulas</span>
                        </a>

                        <div class="flex items-center gap-1.5">
                            <!-- Toggle Publish -->
                            <button wire:click="togglePublish({{ $course->id }})" type="button"
                                    class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors text-xs"
                                    title="{{ $course->isPublished() ? 'Reverter para rascunho' : 'Publicar curso' }}">
                                @if($course->isPublished())
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                @endif
                            </button>

                            <!-- Edit -->
                            <a href="{{ route('instructor.courses.edit', $course) }}" 
                               class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors"
                               title="Editar informações do curso">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>

                            <!-- Delete -->
                            <button wire:click="deleteCourse({{ $course->id }})" 
                                    wire:confirm="Tem certeza de que deseja excluir o curso '{{ $course->title }}'? Todas as aulas e módulos associados serão removidos."
                                    type="button"
                                    class="p-1.5 rounded-lg bg-rose-950/40 hover:bg-rose-900/60 text-rose-300 transition-colors"
                                    title="Excluir curso">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $courses->links() }}
        </div>
    @endif
</div>
