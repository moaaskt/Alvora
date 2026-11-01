<div>
    <div x-data="{ open: @entangle('isOpen') }"
         x-show="open"
         x-cloak
         x-on:keydown.escape.window="$wire.close()"
         class="fixed inset-0 z-50 overflow-hidden"
         aria-labelledby="profile-drawer-title"
         role="dialog"
         aria-modal="true">

        <!-- Backdrop Overlay com desfoque -->
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             wire:click="close"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity z-40 cursor-pointer"></div>

        <!-- Drawer Lateral Direito Deslizante (Slide-over Right Drawer / 100vh) -->
        <aside x-show="open"
               x-transition:enter="transform transition ease-in-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transform transition ease-in-out duration-300"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="fixed inset-y-0 right-0 z-50 w-full sm:w-[480px] md:w-[520px] max-w-full h-screen bg-white shadow-2xl flex flex-col border-l border-slate-200 overflow-hidden">

            <!-- Drawer Header -->
            <div class="px-6 py-5 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-primary/20 border border-primary/40 flex items-center justify-center text-primary-fixed-dim shrink-0">
                        <x-icon name="person" class="w-5 h-5 text-blue-400" />
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 id="profile-drawer-title" class="text-base font-semibold text-white tracking-tight leading-tight">
                                Meu Perfil
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                Drawer
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 truncate mt-0.5">
                            {{ $roleLabel }} &bull; {{ $companyName ?? 'ALVORA' }}
                        </p>
                    </div>
                </div>
                <button type="button"
                        wire:click="close"
                        class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors cursor-pointer shrink-0 ml-2"
                        title="Fechar gaveta lateral (ESC)">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <!-- User Hero & Avatar Section -->
            <div class="px-6 py-5 bg-gradient-to-b from-slate-50 to-white border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-4">
                    <!-- Avatar Frame -->
                    <div class="relative group shrink-0">
                        @if($user && $user->avatar_url)
                            <img src="{{ $user->avatar_url }}"
                                 alt="{{ $name }}"
                                 class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover shadow-sm ring-2 ring-primary/20 border border-white">
                        @else
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-primary to-blue-700 text-white flex items-center justify-center font-bold text-xl sm:text-2xl shadow-sm ring-2 ring-primary/20">
                                {{ $user ? $user->initials : 'U' }}
                            </div>
                        @endif

                        <!-- Loading indicator on photo upload -->
                        <div wire:loading wire:target="photo, uploadPhoto, removePhoto"
                             class="absolute inset-0 rounded-full bg-slate-900/60 flex items-center justify-center backdrop-blur-2xs">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        </div>
                    </div>

                    <!-- Photo Actions & User Title -->
                    <div class="flex-1 min-w-0">
                        <div class="text-base font-bold text-slate-900 truncate leading-tight">{{ $name }}</div>
                        <div class="text-xs text-slate-500 truncate mt-0.5">{{ $email }}</div>

                        <div class="mt-3 flex items-center gap-2 flex-wrap">
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary text-white hover:bg-primary/90 transition-colors cursor-pointer shadow-xs">
                                <x-icon name="camera" class="w-3.5 h-3.5" />
                                <span>Alterar Foto</span>
                                <input type="file"
                                       wire:model="photo"
                                       wire:change="uploadPhoto"
                                       accept="image/png,image/jpeg,image/webp"
                                       class="hidden">
                            </label>

                            @if($user && $user->avatar)
                                <button type="button"
                                        wire:click="removePhoto"
                                        wire:confirm="Tem certeza que deseja remover sua foto de perfil?"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-700 border border-rose-200/60 transition-colors cursor-pointer">
                                    <x-icon name="trash" class="w-3.5 h-3.5" />
                                    <span>Remover</span>
                                </button>
                            @endif
                        </div>
                        @error('photo')
                            <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs (Segmented Control) -->
            <div class="px-6 border-b border-slate-200 bg-white shrink-0">
                <nav class="flex gap-6 -mb-px" aria-label="Abas de Perfil">
                    <button type="button"
                            wire:click="switchTab('general')"
                            class="py-3 text-xs sm:text-sm font-semibold border-b-2 transition-colors cursor-pointer {{ $activeTab === 'general' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        Dados Cadastrais
                    </button>

                    <button type="button"
                            wire:click="switchTab('security')"
                            class="py-3 text-xs sm:text-sm font-semibold border-b-2 transition-colors cursor-pointer {{ $activeTab === 'security' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        Segurança & Senha
                    </button>

                    @if($user && !$user->isAlvoraAdmin())
                        <button type="button"
                                wire:click="switchTab('account')"
                                class="py-3 text-xs sm:text-sm font-semibold border-b-2 transition-colors cursor-pointer {{ $activeTab === 'account' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                            Atividades
                        </button>
                    @endif
                </nav>
            </div>

            <!-- Tab Body (Scrollable Area) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">

                <!-- TAB 1: DADOS CADASTRAIS -->
                @if($activeTab === 'general')
                    <form wire:submit.prevent="saveProfile" class="space-y-4">
                        <!-- Read-only Email Field -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">
                                E-mail Corporativo
                            </label>
                            <div class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 text-sm text-slate-700 font-medium flex items-center justify-between shadow-2xs">
                                <span class="truncate">{{ $email }}</span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400 bg-slate-200/60 px-2 py-0.5 rounded">
                                    <x-icon name="lock" class="w-2.5 h-2.5" />
                                    Leitura
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">
                                O e-mail de acesso corporativo é gerenciado pelo administrador da conta.
                            </p>
                        </div>

                        <!-- Nome Completo -->
                        <div>
                            <label for="profile_name" class="block text-xs font-semibold text-slate-700 mb-1">
                                Nome Completo <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="profile_name"
                                   wire:model="name"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                            @error('name')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Telefone / WhatsApp -->
                        <div>
                            <label for="profile_phone" class="block text-xs font-semibold text-slate-700 mb-1">
                                Telefone / WhatsApp
                            </label>
                            <input type="text"
                                   id="profile_phone"
                                   wire:model="phone"
                                   placeholder="(11) 98765-4321"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                            @error('phone')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Campos Condicionais: Cargo e Departamento (Instrutor & Company Admin) -->
                        @if($user && ($user->isInstructor() || $user->isCompanyAdmin()))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="profile_job_title" class="block text-xs font-semibold text-slate-700 mb-1">
                                        Cargo / Ocupação
                                    </label>
                                    <input type="text"
                                           id="profile_job_title"
                                           wire:model="job_title"
                                           placeholder="Ex: Engenheiro de IA"
                                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                    @error('job_title')
                                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label for="profile_department" class="block text-xs font-semibold text-slate-700 mb-1">
                                        Departamento / Setor
                                    </label>
                                    <input type="text"
                                           id="profile_department"
                                           wire:model="department"
                                           placeholder="Ex: Tecnologia / RH"
                                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                    @error('department')
                                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <!-- Campos Condicionais: Bio (Instrutor & Aluno) -->
                        @if($user && ($user->isInstructor() || $user->isStudent()))
                            <div>
                                <label for="profile_bio" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Biografia / Apresentação
                                </label>
                                <textarea id="profile_bio"
                                          wire:model="bio"
                                          rows="3"
                                          placeholder="Fale brevemente sobre sua experiência, interesses e atuação profissional..."
                                          class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs"></textarea>
                                @error('bio')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Redes Sociais Profissionais -->
                            <div class="pt-2 border-t border-slate-100 space-y-3">
                                <span class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                    Redes Profissionais & Links
                                </span>

                                <div>
                                    <label for="profile_linkedin" class="block text-xs text-slate-600 mb-1">
                                        LinkedIn (URL)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <x-icon name="linkedin" class="w-4 h-4 text-blue-600" />
                                        </span>
                                        <input type="url"
                                               id="profile_linkedin"
                                               wire:model="linkedin_url"
                                               placeholder="https://linkedin.com/in/seuperfil"
                                               class="w-full rounded-lg border border-slate-300 pl-9 pr-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                    </div>
                                    @error('linkedin_url')
                                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label for="profile_facebook" class="block text-xs text-slate-600 mb-1">
                                        Facebook (URL)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <x-icon name="facebook" class="w-4 h-4 text-blue-700" />
                                        </span>
                                        <input type="url"
                                               id="profile_facebook"
                                               wire:model="facebook_url"
                                               placeholder="https://facebook.com/seuperfil"
                                               class="w-full rounded-lg border border-slate-300 pl-9 pr-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                    </div>
                                    @error('facebook_url')
                                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label for="profile_twitter" class="block text-xs text-slate-600 mb-1">
                                        Twitter / X (URL)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <x-icon name="twitter-x" class="w-4 h-4 text-slate-700" />
                                        </span>
                                        <input type="url"
                                               id="profile_twitter"
                                               wire:model="twitter_url"
                                               placeholder="https://x.com/seuperfil"
                                               class="w-full rounded-lg border border-slate-300 pl-9 pr-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                    </div>
                                    @error('twitter_url')
                                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <!-- Submit Button -->
                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                            <button type="button"
                                    wire:click="close"
                                    class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm transition-colors cursor-pointer">
                                Cancelar
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <span wire:loading.remove wire:target="saveProfile">Salvar Alterações</span>
                                <span wire:loading wire:target="saveProfile" class="flex items-center gap-2">
                                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    Salvando...
                                </span>
                            </button>
                        </div>
                    </form>
                @endif

                <!-- TAB 2: SEGURANÇA & SENHA -->
                @if($activeTab === 'security')
                    <form wire:submit.prevent="updatePassword" class="space-y-4">
                        <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-200/80 text-amber-800 text-xs flex items-start gap-2.5">
                            <x-icon name="shield" class="w-4 h-4 shrink-0 text-amber-600 mt-0.5" />
                            <div>
                                <span class="font-semibold block">Dica de Segurança:</span>
                                Crie uma senha forte com no mínimo 8 caracteres, combinando letras maiúsculas, minúsculas, números e símbolos.
                            </div>
                        </div>

                        <div>
                            <label for="current_password" class="block text-xs font-semibold text-slate-700 mb-1">
                                Senha Atual <span class="text-rose-500">*</span>
                            </label>
                            <input type="password"
                                   id="current_password"
                                   wire:model="current_password"
                                   placeholder="Digite sua senha atual"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                            @error('current_password')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password" class="block text-xs font-semibold text-slate-700 mb-1">
                                Nova Senha <span class="text-rose-500">*</span>
                            </label>
                            <input type="password"
                                   id="new_password"
                                   wire:model="new_password"
                                   placeholder="Mínimo de 8 caracteres"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                            @error('new_password')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">
                                Confirmar Nova Senha <span class="text-rose-500">*</span>
                            </label>
                            <input type="password"
                                   id="new_password_confirmation"
                                   wire:model="new_password_confirmation"
                                   placeholder="Repita a nova senha"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                            @error('new_password_confirmation')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                            <button type="button"
                                    wire:click="close"
                                    class="px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm transition-colors cursor-pointer">
                                Cancelar
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="px-5 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <span wire:loading.remove wire:target="updatePassword">Atualizar Senha</span>
                                <span wire:loading wire:target="updatePassword" class="flex items-center gap-2">
                                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    Atualizando...
                                </span>
                            </button>
                        </div>
                    </form>
                @endif

                <!-- TAB 3: ATIVIDADES / CONTA (Oculto para Super Admin) -->
                @if($activeTab === 'account' && $user && !$user->isAlvoraAdmin())
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            @if($user->isStudent())
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                                    <div class="text-2xl font-bold text-primary">{{ $enrollmentsCount }}</div>
                                    <div class="text-xs text-slate-600 mt-1 font-medium">Matrículas Ativas</div>
                                </div>
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                                    <div class="text-2xl font-bold text-emerald-600">{{ $certificatesCount }}</div>
                                    <div class="text-xs text-slate-600 mt-1 font-medium">Certificados Emitidos</div>
                                </div>
                                <div class="col-span-2 p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider block">Aulas Concluídas</span>
                                        <span class="text-sm text-slate-700">Progresso total no aprendizado</span>
                                    </div>
                                    <span class="text-xl font-bold text-slate-900">{{ $completedLessonsCount }}</span>
                                </div>
                            @elseif($user->isInstructor())
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                                    <div class="text-2xl font-bold text-primary">{{ $coursesCount }}</div>
                                    <div class="text-xs text-slate-600 mt-1 font-medium">Cursos Criados</div>
                                </div>
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-center">
                                    <div class="text-2xl font-bold text-emerald-600">{{ $studentsTrainedCount }}</div>
                                    <div class="text-xs text-slate-600 mt-1 font-medium">Alunos Capacitados</div>
                                </div>
                            @endif
                        </div>

                        <!-- Informações Cadastrais da Conta -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5 text-xs text-slate-600">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Membro desde:</span>
                                <span class="font-semibold text-slate-800">{{ $memberSince }}</span>
                            </div>
                            @if($companyName)
                                <div class="flex items-center justify-between">
                                    <span class="font-medium">Empresa:</span>
                                    <span class="font-semibold text-slate-800">{{ $companyName }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span class="font-medium">Identificador Único:</span>
                                <span class="font-mono text-[11px] text-slate-700">#{{ $user->id }}</span>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </aside>
    </div>
</div>
