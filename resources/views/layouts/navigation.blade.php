<nav class="bg-[#090D16] border-b border-slate-800 sticky top-0 z-40 backdrop-blur-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Side: Brand + Nav Links by Role (D-14) -->
            <div class="flex items-center space-x-8">
                <!-- Brand -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group py-1">
                    <img src="{{ asset('images/logo-symbol.png') }}" alt="ALVORA" class="h-8 w-auto shrink-0 transition-transform duration-200 group-hover:scale-105">
                    <div class="flex flex-col justify-center">
                        <span class="text-lg font-bold tracking-tight text-white group-hover:text-indigo-300 transition-colors leading-none">ALVORA</span>
                        @if(auth()->user()->company)
                            <span class="text-[10px] text-slate-400 font-medium mt-0.5 truncate max-w-[140px]">{{ auth()->user()->company->name }}</span>
                        @else
                            <span class="text-[10px] uppercase tracking-widest text-emerald-400 font-semibold mt-0.5">Super Admin</span>
                        @endif
                    </div>
                </a>

                <!-- Navigation Links by Role (D-14) -->
                <div class="hidden md:flex space-x-6 text-sm font-medium">
                    @if(auth()->user()->isAlvoraAdmin())
                        <!-- Admin Alvora Links -->
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-indigo-400 transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('dashboard') ? 'text-indigo-400 font-semibold' : '' }}">
                            <x-icon name="speedometer2" class="w-4 h-4 text-indigo-400" />
                            Visão Global
                        </a>
                        <a href="{{ route('admin.billing') }}" class="text-slate-300 hover:text-white transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('admin.billing*') ? 'text-indigo-400 font-semibold' : '' }}">
                            <x-icon name="credit-card-2-front-fill" class="w-4 h-4 text-emerald-400" />
                            Faturamento B2B
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
                    @elseif(auth()->user()->isInstructor() || auth()->user()->isCompanyAdmin())
                        <!-- Painel do Instrutor (O instrutor é a própria empresa) -->
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-indigo-400 transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('dashboard') ? 'text-indigo-400 font-semibold' : '' }}">
                            <x-icon name="bar-chart-fill" class="w-4 h-4 text-indigo-400" />
                            Painel do Instrutor
                        </a>
                        <a href="{{ route('instructor.courses.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('instructor.courses.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Cursos & Conteúdos
                        </a>
                        <a href="{{ route('employees.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('employees.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            <x-icon name="people-fill" class="w-4 h-4 text-emerald-400" />
                            Colaboradores & Convites
                        </a>
                        <a href="{{ route('company.tracks.index') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('company.tracks.*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Trilhas Corporativas
                        </a>
                        <a href="{{ route('company.situation-room') }}" class="text-slate-300 hover:text-white transition-colors py-2 flex items-center gap-1.5 {{ request()->routeIs('company.situation-room*') ? 'text-rose-400 font-semibold' : '' }}">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            Sala de Situação
                        </a>
                        <a href="{{ route('company.ai-insights') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('company.ai-insights*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Insights de IA
                        </a>
                        <a href="{{ route('company.billing') }}" class="text-slate-300 hover:text-white transition-colors py-2 {{ request()->routeIs('company.billing*') ? 'text-indigo-400 font-semibold' : '' }}">
                            Assinatura & Plano
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
                    @elseif(auth()->user()->isInstructor() || auth()->user()->isCompanyAdmin()) bg-purple-500/20 text-purple-300 border border-purple-500/30
                    @else bg-slate-800 text-slate-300 border border-slate-700
                    @endif">
                    {{ auth()->user()->isAlvoraAdmin() ? 'Administrador Alvora' : ((auth()->user()->isInstructor() || auth()->user()->isCompanyAdmin()) ? 'Instrutor' : 'Aluno') }}
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
                        <x-icon name="box-arrow-right" class="w-5 h-5" />
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
