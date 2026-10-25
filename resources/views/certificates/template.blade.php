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
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #0b0f19;
            color: #ffffff;
            margin: 0;
            padding: 30px;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }
        .border-outer {
            border: 3px solid #6366f1;
            padding: 10px;
            box-sizing: border-box;
            background-color: #0d121f;
        }
        .border-inner {
            border: 1px dashed rgba(255, 255, 255, 0.2);
            padding: 30px 35px;
            box-sizing: border-box;
            text-align: center;
            position: relative;
        }
        .brand-logo {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 4px;
            color: #6366f1;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .brand-sub {
            font-size: 11px;
            letter-spacing: 2px;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .title {
            font-size: 30px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 12px;
        }
        .certifies {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .student-name {
            font-size: 28px;
            font-weight: bold;
            color: #38bdf8;
            border-bottom: 2px solid rgba(99, 102, 241, 0.4);
            display: inline-block;
            padding-bottom: 4px;
            margin-bottom: 20px;
        }
        .description {
            font-size: 13px;
            line-height: 1.7;
            color: #cbd5e1;
            max-width: 720px;
            margin: 0 auto 25px auto;
        }
        .course-title {
            color: #ffffff;
            font-weight: bold;
        }
        .footer-table {
            width: 100%;
            margin-top: 25px;
        }
        .footer-table td {
            text-align: center;
            vertical-align: bottom;
            font-size: 11px;
            color: #94a3b8;
        }
        .signature-line {
            width: 190px;
            border-top: 1px solid #475569;
            margin: 0 auto 6px auto;
        }
        .validation-code {
            position: absolute;
            bottom: 15px;
            right: 25px;
            font-size: 10px;
            color: #64748b;
            text-align: right;
            font-family: monospace;
        }
        .badge {
            color: #10b981;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="border-outer">
        <div class="border-inner">
            <div class="brand-logo">ALVORA</div>
            <div class="brand-sub">Treinamento Corporativo com Inteligência Artificial &bull; {{ $certificate->company_name }}</div>

            <div class="title">Certificado de Capacitação</div>
            <div class="certifies">Certificamos com distinção que</div>

            <div class="student-name">{{ $certificate->student_name }}</div>

            <div class="description">
                Concluiu com aproveitamento integral o curso corporativo
                <span class="course-title">"{{ $certificate->course_title }}"</span>,
                cumprindo satisfatoriamente toda a grade curricular e obtendo aprovação nas avaliações pedagógicas exigidas,
                totalizando uma carga horária estimada de <strong class="badge">{{ $certificate->formattedWorkload() }}</strong>.
            </div>

            <table class="footer-table">
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        <strong>Coordenação Pedagógica</strong><br>
                        Plataforma ALVORA B2B
                    </td>
                    <td>
                        <div class="signature-line"></div>
                        <strong>Data de Emissão</strong><br>
                        {{ $certificate->issued_at->format('d/m/Y') }}
                    </td>
                    <td>
                        <div class="signature-line"></div>
                        <strong>{{ $certificate->company_name }}</strong><br>
                        Recursos Humanos
                    </td>
                </tr>
            </table>

            <div class="validation-code">
                Código de Autenticidade:<br>
                <strong>{{ $certificate->validation_code }}</strong><br>
                Verificação: /validar-certificado/{{ $certificate->validation_code }}
            </div>
        </div>
    </div>
</body>
</html>
