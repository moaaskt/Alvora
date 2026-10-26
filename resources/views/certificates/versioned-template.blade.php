<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Conclusão — {{ $certificate->student_name }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 1122px;
            height: 793px;
            background-color: {{ $compiled['background']['color'] ?? '#0b0f19' }};
            color: #ffffff;
            font-family: Helvetica, Arial, sans-serif;
            overflow: hidden;
            -webkit-print-color-adjust: exact;
        }
        .canvas-container {
            position: relative;
            width: 1122px;
            height: 793px;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            overflow: hidden;
        }
        .canvas-border-outer {
            position: absolute;
            top: 25px;
            left: 25px;
            width: 1072px;
            height: 743px;
            border: {{ $compiled['border']['width'] ?? 3 }}px {{ $compiled['border']['style'] ?? 'solid' }} {{ $compiled['border']['color'] ?? '#6366f1' }};
            box-sizing: border-box;
            background-color: transparent;
        }
        .canvas-border-inner {
            position: absolute;
            top: 35px;
            left: 35px;
            width: 1052px;
            height: 723px;
            border: 1px dashed rgba(255, 255, 255, 0.15);
            box-sizing: border-box;
            background-color: transparent;
        }
        .canvas-element {
            display: block;
            box-sizing: border-box;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="canvas-container">
        <div class="canvas-border-outer"></div>
        <div class="canvas-border-inner"></div>

        @foreach ($compiled['elements'] as $element)
            <div id="elem-{{ $element['id'] }}"
                 class="canvas-element"
                 style="{{ $element['position_css'] }} {{ $element['style_css'] }}">
                {!! $element['content_html'] !!}
            </div>
        @endforeach
    </div>
</body>
</html>
