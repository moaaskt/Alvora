<div>
    {{-- Botão Flutuante de Acesso Rápido --}}
    @if (!$isOpen)
        <button wire:click="toggleDrawer"
                class="fixed bottom-6 right-6 z-40 flex items-center gap-2.5 px-4 py-3 rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 text-slate-950 font-bold text-xs shadow-xl shadow-emerald-500/25 hover:scale-105 active:scale-95 transition-all">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-slate-950 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-slate-950"></span>
            </span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span>Tutor Alvora AI</span>
        </button>
    @endif

    {{-- Drawer Lateral Deslizante --}}
    @if ($isOpen)
        <div class="fixed inset-0 z-50 overflow-hidden bg-slate-950/60 backdrop-blur-sm transition-opacity">
            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-md bg-slate-900 border-l border-slate-800 flex flex-col shadow-2xl">
                    {{-- Header do Drawer --}}
                    <div class="p-4 border-b border-slate-800 bg-slate-900/90 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                    <span>Alvora AI Tutor</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono">Groq Fast</span>
                                </h3>
                                @if ($currentLesson)
                                    <p class="text-[11px] text-slate-400 truncate max-w-[220px]">Contexto: {{ $currentLesson->title }}</p>
                                @else
                                    <p class="text-[11px] text-slate-400">Assistente geral de aprendizado</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button wire:click="clearConversation" title="Nova conversa" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            <button wire:click="closeDrawer" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Mensagens da Conversa --}}
                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages-container">
                        @if ($messages->isEmpty())
                            <div class="text-center py-10 px-4">
                                <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 mb-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-white mb-1">Como posso te ajudar hoje?</h4>
                                <p class="text-xs text-slate-400 mb-4">Você pode tirar dúvidas pontuais sobre a aula, pedir explicações simplificadas ou exemplos práticos.</p>
                                
                                <div class="space-y-2">
                                    <button wire:click="$set('userMessage', 'Poderia me dar um resumo dos pontos mais importantes dessa aula?')" class="w-full text-left p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 text-xs text-slate-300 hover:text-white transition-colors">
                                        💡 Resumo dos pontos principais
                                    </button>
                                    <button wire:click="$set('userMessage', 'Como esse conceito se aplica no dia a dia da minha empresa?')" class="w-full text-left p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 text-xs text-slate-300 hover:text-white transition-colors">
                                        🏢 Exemplo de aplicação no trabalho
                                    </button>
                                </div>
                            </div>
                        @else
                            @foreach ($messages as $message)
                                <div class="flex {{ $message->isFromUser() ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-[85%] rounded-2xl px-3.5 py-2.5 text-xs leading-relaxed {{ $message->isFromUser() ? 'bg-emerald-500 text-slate-950 font-medium rounded-tr-none' : 'bg-slate-800 text-slate-200 border border-slate-700/80 rounded-tl-none' }}">
                                        {!! nl2br(e($message->content)) !!}
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if ($isSending)
                            <div class="flex justify-start">
                                <div class="bg-slate-800 text-slate-300 border border-slate-700/80 rounded-2xl rounded-tl-none px-4 py-3 text-xs flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-bounce"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-bounce [animation-delay:0.2s]"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-bounce [animation-delay:0.4s]"></span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Form de Envio --}}
                    <div class="p-3 border-t border-slate-800 bg-slate-900/90">
                        <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                            <input type="text"
                                   wire:model="userMessage"
                                   placeholder="Digite sua dúvida sobre a aula..."
                                   class="flex-1 text-xs px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:border-emerald-500">
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="p-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold transition-all disabled:opacity-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
