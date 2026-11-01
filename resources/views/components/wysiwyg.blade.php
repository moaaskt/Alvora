@props([
    'placeholder' => 'Digite a descrição detalhada aqui...',
    'minHeight' => '180px',
])

@php
    $id = $attributes->get('id') ?? 'wysiwyg-' . uniqid();
    $wireModel = $attributes->wire('model')->value();
@endphp

<div wire:ignore
     x-data="{
        state: @if($wireModel) @entangle($attributes->wire('model')) @else '' @endif,
        quill: null,
        isUpdating: false,
        initEditor() {
            const self = this;
            const container = this.$refs.editor;

            const init = () => {
                if (typeof window.Quill === 'undefined') {
                    setTimeout(init, 50);
                    return;
                }

                self.quill = new window.Quill(container, {
                    theme: 'snow',
                    placeholder: @js($placeholder),
                    modules: {
                        toolbar: [
                            [{ 'font': [] }],
                            [{ 'size': ['small', false, 'large', 'huge'] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'header': [1, 2, 3, false] }],
                            [{ 'align': [] }],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            [{ 'indent': '-1' }, { 'indent': '+1' }],
                            ['blockquote', 'code-block'],
                            ['link'],
                            ['clean']
                        ]
                    }
                });

                // Carrega o conteúdo inicial se existir
                if (self.state) {
                    self.quill.root.innerHTML = self.state;
                }

                // Sincroniza do Quill para o Livewire
                self.quill.on('text-change', () => {
                    if (self.isUpdating) return;
                    let html = self.quill.root.innerHTML;
                    if (html === '<p><br></p>' || html === '<p></p>') {
                        html = '';
                    }
                    self.state = html;
                });

                // Observa alterações vindas externamente do Livewire
                self.$watch('state', (val) => {
                    const currentHtml = self.quill.root.innerHTML;
                    const cleanCurrent = (currentHtml === '<p><br></p>' || currentHtml === '<p></p>') ? '' : currentHtml;
                    if (val !== cleanCurrent) {
                        self.isUpdating = true;
                        self.quill.root.innerHTML = val || '';
                        self.isUpdating = false;
                    }
                });
            };

            init();
        }
     }"
     x-init="initEditor()"
     class="alvora-wysiwyg-wrapper border border-slate-200"
     {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'w-full']) }}>
    
    <div x-ref="editor" style="min-height: {{ $minHeight }};"></div>
</div>

