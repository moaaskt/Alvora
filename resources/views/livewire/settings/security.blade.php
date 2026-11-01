<div class="{{ auth()->user()?->isStudent() ? 'flex flex-col lg:flex-row items-start gap-6 lg:gap-8' : 'max-w-4xl mx-auto' }}">
    @if(auth()->user()?->isStudent())
        <x-student.sidebar active="profile" />
    @endif

    <div class="{{ auth()->user()?->isStudent() ? 'flex-1 min-w-0 w-full' : 'w-full' }}">
        @include('livewire.settings.partials.header')

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <form wire:submit.prevent="updatePassword" class="space-y-6 max-w-xl">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h2 class="text-base font-bold text-slate-900 leading-tight">
                    Segurança e Credenciais de Acesso
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Atualize sua senha periodicamente para manter sua conta institucional segura.
                </p>
            </div>

            <!-- Callout de Segurança -->
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs flex items-start gap-3">
                <x-icon name="shield" class="w-5 h-5 shrink-0 text-amber-600 mt-0.5" />
                <div>
                    <span class="font-bold block text-sm">Diretrizes de Segurança</span>
                    <p class="mt-0.5 text-amber-800">
                        Crie uma senha forte com no mínimo 8 caracteres, combinando letras maiúsculas, minúsculas, números e caracteres especiais.
                    </p>
                </div>
            </div>

            <!-- Senha Atual -->
            <div>
                <label for="settings_current_password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Senha Atual <span class="text-rose-500">*</span>
                </label>
                <input type="password"
                       id="settings_current_password"
                       wire:model="current_password"
                       placeholder="Digite sua senha atual"
                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                @error('current_password')
                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Nova Senha -->
            <div>
                <label for="settings_new_password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Nova Senha <span class="text-rose-500">*</span>
                </label>
                <input type="password"
                       id="settings_new_password"
                       wire:model="new_password"
                       placeholder="Mínimo de 8 caracteres"
                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                @error('new_password')
                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirmar Nova Senha -->
            <div>
                <label for="settings_new_password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Confirmar Nova Senha <span class="text-rose-500">*</span>
                </label>
                <input type="password"
                       id="settings_new_password_confirmation"
                       wire:model="new_password_confirmation"
                       placeholder="Repita a nova senha"
                       class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition shadow-2xs">
                @error('new_password_confirmation')
                    <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-end">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm shadow-sm transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-50">
                    <span wire:loading.remove wire:target="updatePassword">Atualizar Senha</span>
                    <span wire:loading wire:target="updatePassword" class="flex items-center gap-2">
                        <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        Atualizando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
</div>

