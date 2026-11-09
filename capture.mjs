import puppeteer from 'puppeteer-core';
import path from 'path';

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const BASE_URL = 'http://127.0.0.1:8000';
const SCREENSHOT_DIR = path.resolve('screenshots');

async function run() {
    console.log('Iniciando navegador Chrome...');
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: true,
        defaultViewport: { width: 1920, height: 1080 },
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });

    // Login como Super Admin Alvora (tem acesso a todas as salas de situação e dados de IA)
    console.log('Efetuando login como Admin Alvora (admin@alvora.com.br)...');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
    await page.type('input[type="email"]', 'admin@alvora.com.br');
    await page.type('input[type="password"]', 'password123');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.keyboard.press('Enter')
    ]);

    // 1. Dashboard Executivo / Sala de Situação (Métricas preditivas de evasão/retenção)
    console.log('Capturando Screenshot 1: Sala de Situação (Dashboard de Risco e Evasão)...');
    await page.goto(`${BASE_URL}/empresa/sala-de-situacao`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2500));
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '01_dashboard_rh_situacao.png') });
    console.log('Salvo: 01_dashboard_rh_situacao.png');

    // 1b. Insights Executivos de IA (Análise estratégica por LLM)
    console.log('Capturando Screenshot 1b: Insights Executivos de IA...');
    await page.goto(`${BASE_URL}/empresa/insights-executivos`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2500));
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '01b_insights_executivos_ia.png') });
    console.log('Salvo: 01b_insights_executivos_ia.png');

    // 2. Intervenção Preditiva Automatizada / Modal de Intervenção Inteligente
    console.log('Capturando Screenshot 2: Modal de Intervenção Preditiva Aberto...');
    await page.goto(`${BASE_URL}/empresa/sala-de-situacao`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2000));
    await page.evaluate(() => {
        const btn = document.querySelector('button[wire\\:click*="openInterventionModal"]');
        if (btn) btn.click();
    });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '02_intervencao_inteligente_modal.png') });
    console.log('Salvo: 02_intervencao_inteligente_modal.png');

    // Limpar cookies e logar como Aluno
    console.log('Alternando para sessão do Aluno (aluno@acme.com.br)...');
    const client = await page.target().createCDPSession();
    await client.send('Network.clearBrowserCookies');

    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
    await page.type('input[type="email"]', 'aluno@acme.com.br');
    await page.type('input[type="password"]', 'password123');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.keyboard.press('Enter')
    ]);

    // 3. Player de Aula e Navegação do Aluno
    console.log('Capturando Screenshot 3: Player de Aula & Experiência de Aprendizado...');
    await page.goto(`${BASE_URL}/aluno/cursos/fundamentos-de-lideranca-e-alta-performance/aulas`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2500));
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '03_player_aprendizado_aluno.png') });
    console.log('Salvo: 03_player_aprendizado_aluno.png');

    // 3b. Catálogo Executivo de Cursos
    console.log('Capturando Screenshot 3b: Catálogo Executivo de Cursos...');
    await page.goto(`${BASE_URL}/aluno/catalogo`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 2500));
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '03b_catalogo_executivo.png') });
    console.log('Salvo: 03b_catalogo_executivo.png');

    await browser.close();
    console.log('Todas as capturas em 1920x1080 foram concluídas com sucesso!');
}

run().catch(err => {
    console.error('Erro na execução:', err);
    process.exit(1);
});
