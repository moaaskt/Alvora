<div class="{{ auth()->user()?->isStudent() ? 'flex flex-col lg:flex-row items-start gap-6 lg:gap-8' : 'max-w-5xl mx-auto' }}">
    @if(auth()->user()?->isStudent())
        <x-student.sidebar active="profile" />
    @endif

    <div class="{{ auth()->user()?->isStudent() ? 'flex-1 min-w-0 w-full' : 'w-full' }}">
        @include('livewire.settings.partials.header')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Coluna Esquerda: Informações da Conta & Tenant (Card Resumo) -->
        <div class="space-y-6">
            <!-- Card de Avatar & Identificação -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs text-center">
                <div class="relative inline-block mx-auto mb-4">
                    @if($user && $user->avatar_url)
                        <img src="{{ $user->avatar_url }}"
                             alt="{{ $name }}"
                             class="w-24 h-24 rounded-full object-cover shadow-sm ring-4 ring-primary/10 border-2 border-white mx-auto">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary to-blue-700 text-white flex items-center justify-center font-bold text-3xl shadow-sm ring-4 ring-primary/10 mx-auto">
                            {{ $user ? $user->initials : 'U' }}
                        </div>
                    @endif

                    <!-- Loading Spinner on Photo Upload -->
                    <div wire:loading wire:target="photo, uploadPhoto, removePhoto"
                         class="absolute inset-0 rounded-full bg-slate-900/60 flex items-center justify-center backdrop-blur-2xs">
                        <div class="w-6 h-6 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    </div>
                </div>

                <h3 class="text-base font-bold text-slate-900 leading-tight">{{ $name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $email }}</p>

                <div class="mt-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    {{ $roleLabel }}
                </div>

                <!-- Ações de Foto -->
                <div class="mt-5 pt-5 border-t border-slate-100 flex items-center justify-center gap-2">
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
                    <span class="text-xs text-rose-600 mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Card de Governança / Vínculo com a Empresa (Multi-tenant) -->
            <div class="bg-white rounded-xl border border-slate-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                    <x-icon name="building" class="w-4 h-4 text-slate-500" />
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                        Vínculo Corporativo
                    </h4>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-500 block">Organização / Tenant:</span>
                        <span class="font-semibold text-slate-800 text-sm">
                            {{ $companyName ?? 'ALVORA — Gestão Global' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-500 block">Identificador Único:</span>
                        <span class="font-mono text-slate-700 bg-slate-100 px-2 py-0.5 rounded text-[11px]">
                            USER-{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-500 block">Membro Desde:</span>
                        <span class="font-medium text-slate-800">
                            {{ $memberSince }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Formulário de Dados Pessoais & Profissionais -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
                <form wire:submit.prevent="saveProfile" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4 mb-6">
                        <h2 class="text-base font-bold text-slate-900 leading-tight">
                            Informações Pessoais
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Atualize seus dados de contato e presença no ecossistema Alvora.
                        </p>
                    </div>

                    <!-- E-mail Corporativo (Read-Only) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                            E-mail Corporativo
                        </label>
                        <div class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-700 font-medium flex items-center justify-between shadow-2xs">
                            <span class="truncate">{{ $email }}</span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-slate-200/70 px-2 py-0.5 rounded">
                                <x-icon name="lock" class="w-3 h-3 text-slate-500" />
                                Somente Leitura
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            O e-mail de acesso está vinculado à sua conta institucional e não pode ser editado diretamente.
                        </p>
                    </div>

                    <!-- Nome Completo & Telefone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="settings_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Nome Completo <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="settings_name"
                                   wire:model="name"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                            @error('name')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="settings_phone" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Telefone / WhatsApp
                            </label>
                            <input type="text"
                                   id="settings_phone"
                                   wire:model="phone"
                                   placeholder="(11) 98765-4321"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                            @error('phone')
                                <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Campos Condicionais: Cargo & Departamento (Instrutor e Company Admin) -->
                    @if($user && ($user->isInstructor() || $user->isCompanyAdmin()))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label for="settings_job_title" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Cargo / Ocupação
                                </label>
                                <input type="text"
                                       id="settings_job_title"
                                       wire:model="job_title"
                                       placeholder="Ex: Especialista em IA & Dados"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                @error('job_title')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="settings_department" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Departamento / Setor
                                </label>
                                <input type="text"
                                       id="settings_department"
                                       wire:model="department"
                                       placeholder="Ex: Educação Corporativa & Inovação"
                                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                @error('department')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <!-- Campos Condicionais: Bio & Redes Sociais (Instrutor e Aluno) -->
                    @if($user && ($user->isInstructor() || $user->isStudent()))
                        <div class="space-y-4 pt-2">
                            <div>
                                <label for="settings_bio" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Biografia / Apresentação
                                </label>
                                <textarea id="settings_bio"
                                          wire:model="bio"
                                          rows="4"
                                          placeholder="Fale brevemente sobre sua formação, experiência profissional ou interesses de aprendizado corporativo..."
                                          class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs"></textarea>
                                @error('bio')
                                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="pt-2 border-t border-slate-100 space-y-3">
                                <span class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                    Redes Profissionais & Links
                                </span>

                                <div>
                                    <label for="settings_linkedin" class="block text-xs text-slate-600 mb-1">
                                        LinkedIn (URL)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <x-icon name="linkedin" class="w-4 h-4 text-blue-600" />
                                        </span>
                                        <input type="url"
                                               id="settings_linkedin"
                                               wire:model="linkedin_url"
                                               placeholder="https://linkedin.com/in/seuperfil"
                                               class="w-full rounded-lg border border-slate-300 pl-9 pr-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                    </div>
                                    @error('linkedin_url')
                                        <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label for="settings_facebook" class="block text-xs text-slate-600 mb-1">
                                            Facebook (URL)
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <x-icon name="facebook" class="w-4 h-4 text-blue-700" />
                                            </span>
                                            <input type="url"
                                                   id="settings_facebook"
                                                   wire:model="facebook_url"
                                                   placeholder="https://facebook.com/seuperfil"
                                                   class="w-full rounded-lg border border-slate-300 pl-9 pr-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                        </div>
                                        @error('facebook_url')
                                            <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="settings_twitter" class="block text-xs text-slate-600 mb-1">
                                            Twitter / X (URL)
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                <x-icon name="twitter-x" class="w-4 h-4 text-slate-700" />
                                            </span>
                                            <input type="url"
                                                   id="settings_twitter"
                                                   wire:model="twitter_url"
                                                   placeholder="https://x.com/seuperfil"
                                                   class="w-full rounded-lg border border-slate-300 pl-9 pr-3.5 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                                        </div>
                                        @error('twitter_url')
                                            <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Submit Actions Bar -->
                    <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove wire:target="saveProfile">Salvar Alterações</span>
                            <span wire:loading wire:target="saveProfile" class="flex items-center gap-2">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Salvando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

