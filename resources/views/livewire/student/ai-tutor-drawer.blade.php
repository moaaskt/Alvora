<div>
    {{-- Botão Flutuante de Acesso Rápido (Navy + Green Dot + Message-Circle Icon) --}}
    @if (!$isOpen)
        <aside class="fixed bottom-6 right-6 z-50">
            <button type="button"
                    wire:click="toggleDrawer"
                    class="h-[48px] flex items-center gap-2.5 px-5 rounded-full bg-edtech-navy hover:bg-edtech-navy-soft text-white font-semibold text-sm shadow-xl transition-all motion-safe:hover:scale-105 active:scale-95 border border-slate-700/50 cursor-pointer">
                <span class="w-2 h-2 rounded-full bg-alvora-emerald animate-pulse"></span>
                <x-icon name="message-circle" class="w-4 h-4 text-edtech-blue-border" />
                <span>Tutor Alvora AI</span>
            </button>
        </aside>
    @endif

    {{-- Drawer Lateral Deslizante (Coerente com os tokens Light EdTech) --}}
    @if ($isOpen)
        <div class="fixed inset-0 z-50 overflow-hidden bg-edtech-navy/40 backdrop-blur-xs transition-opacity">
            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-md bg-edtech-surface border-l border-edtech-border flex flex-col shadow-2xl">
                    {{-- Header do Drawer --}}
                    <div class="p-4 border-b border-edtech-border bg-edtech-surface flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-edtech-blue-light border border-edtech-blue-border flex items-center justify-center text-edtech-blue shrink-0">
                                <x-icon name="message-circle" class="w-4 h-4" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-edtech-text flex items-center gap-2">
                                    <span>Alvora AI Tutor</span>
                                    <span class="text-[13px] font-medium text-alvora-emerald">Groq Fast</span>
                                </h3>
                                @if ($currentLesson)
                                    <p class="text-[13px] text-edtech-muted truncate max-w-[220px]">Contexto: {{ $currentLesson->title }}</p>
                                @else
                                    <p class="text-[13px] text-edtech-muted">Assistente geral de aprendizado</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                    wire:click="clearConversation"
                                    title="Nova conversa"
                                    aria-label="Nova conversa"
                                    class="p-2 rounded-lg text-edtech-muted hover:text-edtech-text hover:bg-edtech-border-light transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            <button type="button"
                                    wire:click="closeDrawer"
                                    title="Fechar"
                                    aria-label="Fechar"
                                    class="p-2 rounded-lg text-edtech-muted hover:text-edtech-text hover:bg-edtech-border-light transition-colors cursor-pointer">
                                <x-icon name="x" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    {{-- Mensagens da Conversa --}}
                    <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-edtech-bg" id="chat-messages-container">
                        @if ($messages->isEmpty())
                            <div class="text-center py-10 px-4">
                                <div class="w-12 h-12 mx-auto rounded-2xl bg-edtech-blue-light border border-edtech-blue-border flex items-center justify-center text-edtech-blue mb-3">
                                    <x-icon name="message-circle" class="w-6 h-6" />
                                </div>
                                <h4 class="text-base font-semibold text-edtech-text mb-1">Como posso te ajudar hoje?</h4>
                                <p class="text-[13px] text-edtech-muted mb-5 leading-relaxed">
                                    Você pode tirar dúvidas pontuais sobre a aula, pedir explicações simplificadas ou exemplos práticos.
                                </p>

                                <div class="space-y-2.5">
                                    <button type="button"
                                            wire:click="$set('userMessage', 'Poderia me dar um resumo dos pontos mais importantes dessa aula?')"
                                            class="w-full text-left p-3 rounded-xl bg-edtech-surface hover:bg-edtech-blue-light/60 border border-edtech-border hover:border-edtech-blue-border text-[13px] font-medium text-edtech-text transition-colors flex items-center gap-2.5 cursor-pointer">
                                        <x-icon name="book-open" class="w-4 h-4 text-edtech-blue shrink-0" />
                                        <span>Resumo dos pontos principais</span>
                                    </button>
                                    <button type="button"
                                            wire:click="$set('userMessage', 'Como esse conceito se aplica no dia a dia da minha empresa?')"
                                            class="w-full text-left p-3 rounded-xl bg-edtech-surface hover:bg-edtech-blue-light/60 border border-edtech-border hover:border-edtech-blue-border text-[13px] font-medium text-edtech-text transition-colors flex items-center gap-2.5 cursor-pointer">
                                        <x-icon name="bar-chart" class="w-4 h-4 text-edtech-blue shrink-0" />
                                        <span>Exemplo de aplicação no trabalho</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            @foreach ($messages as $message)
                                <div class="flex {{ $message->isFromUser() ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-[85%] rounded-2xl px-4 py-2.5 text-[13px] leading-relaxed {{ $message->isFromUser() ? 'bg-edtech-blue text-white font-medium rounded-tr-none shadow-xs' : 'bg-edtech-surface text-edtech-text border border-edtech-border rounded-tl-none shadow-xs' }}">
                                        {!! nl2br(e($message->content)) !!}
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if ($isSending)
                            <div class="flex justify-start">
                                <div class="bg-edtech-surface text-edtech-muted border border-edtech-border rounded-2xl rounded-tl-none px-4 py-3 text-[13px] flex items-center gap-1.5 shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-edtech-blue animate-bounce"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-edtech-blue animate-bounce [animation-delay:0.2s]"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-edtech-blue animate-bounce [animation-delay:0.4s]"></span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Form de Envio --}}
                    <div class="p-3 border-t border-edtech-border bg-edtech-surface">
                        <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                            <input type="text"
                                   wire:model="userMessage"
                                   placeholder="Digite sua dúvida sobre a aula..."
                                   class="flex-1 text-sm px-3.5 py-2.5 rounded-xl bg-edtech-bg border border-edtech-border text-edtech-text placeholder-edtech-muted focus:outline-none focus:border-edtech-blue focus:ring-1 focus:ring-edtech-blue">
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    aria-label="Enviar mensagem"
                                    class="p-2.5 rounded-xl bg-edtech-blue hover:bg-edtech-blue-hover text-white font-semibold transition-all disabled:opacity-50 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
