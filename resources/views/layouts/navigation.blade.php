<nav class="bg-[#090D16] border-b border-slate-800 sticky top-0 z-40 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Side: Brand + Nav Links by Role (D-14) -->
            <div class="flex items-center space-x-8">
                <!-- Brand -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-emerald-400 p-[2px] shadow-md shadow-indigo-500/20">
                        <div class="w-full h-full bg-[#090D16] rounded-[9px] flex items-center justify-center">
                            <span class="font-extrabold text-lg text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-emerald-400">A</span>
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-bold tracking-tight text-white group-hover:text-indigo-300 transition-colors">ALVORA</span>
                        @if(auth()->user()->company)
                            <span class="text-[10px] text-slate-400 font-medium -mt-1 truncate max-w-[140px]">{{ auth()->user()->company->name }}</span>
                        @else
                            <span class="text-[10px] uppercase tracking-widest text-emerald-400 font-semibold -mt-1">Super Admin</span>
                        @endif
                    </div>
                </a>

                <!-- Navigation Links by Role (D-14) -->
                <div class="hidden md:flex space-x-6 text-sm font-medium">
                    @if(auth()->user()->isAlvoraAdmin())
                        <!-- Admin Alvora Links -->
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-indigo-400 transition-colors py-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Visão Global
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Empresas Clientes
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Leads da Landing
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Configurações IA
                        </a>
                    @elseif(auth()->user()->isCompanyAdmin())
                        <!-- Gestor de RH Links -->
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-indigo-400 transition-colors py-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Dashboard RH
                        </a>
                        <a href="{{ route('instructor.courses.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('instructor.courses.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Cursos & Conteúdos
                        </a>
                        <a href="{{ route('employees.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('employees.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Colaboradores & Convites
                        </a>
                        <a href="{{ route('company.tracks.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('company.tracks.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Trilhas Corporativas
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Assinatura & Plano
                        </a>
                    @elseif(auth()->user()->isInstructor())
                        <!-- Instrutor Links -->
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-indigo-400 transition-colors py-2 flex items-center gap-1.5">
                            Painel do Instrutor
                        </a>
                        <a href="{{ route('instructor.courses.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('instructor.courses.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Meus Cursos
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Aulas & Módulos
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Quizzes & Avaliações
                        </a>
                    @else
                        <!-- Aluno / Colaborador Links -->
                        <a href="{{ route('student.dashboard') }}" class="text-white hover:text-indigo-400 transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('student.dashboard') ? 'text-indigo-400 font-semibold' : '' }}">
                            Meu Aprendizado
                        </a>
                        <a href="{{ route('student.dashboard') }}#catalogo" class="text-slate-300 hover:text-white transition-colors py-2">
                            Catálogo de Cursos
                        </a>
                        <a href="{{ route('student.dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Minhas Trilhas
                        </a>
                        <a href="{{ route('student.dashboard') }}" class="text-slate-300 hover:text-white transition-colors py-2">
                            Meus Certificados
                        </a>
                    @endif
                </div>
            </div>

            <!-- Right Side: User Profile & Logout -->
            <div class="flex items-center space-x-4">
                <!-- Role Badge -->
                <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                    @if(auth()->user()->isAlvoraAdmin()) bg-indigo-500/20 text-indigo-300 border border-indigo-500/30
                    @elseif(auth()->user()->isCompanyAdmin()) bg-emerald-500/20 text-emerald-300 border border-emerald-500/30
                    @elseif(auth()->user()->isInstructor()) bg-purple-500/20 text-purple-300 border border-purple-500/30
                    @else bg-slate-800 text-slate-300 border border-slate-700
                    @endif">
                    {{ auth()->user()->role?->label() ?? 'Usuário' }}
                </span>

                <!-- User Info -->
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span class="text-xs font-medium text-slate-200 hidden sm:inline-block">{{ auth()->user()->name }}</span>
                </div>

                <!-- Logout Form -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-rose-400 transition-colors p-2 rounded-lg hover:bg-slate-800" title="Sair da Plataforma">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
