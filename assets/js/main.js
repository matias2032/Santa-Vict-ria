// Centro Médico Santa Victória - comportamento do site

document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Hero: slideshow automático das fotos da galeria ---------- */
    const slides = document.querySelectorAll('#heroSlides .hero-slide');
    const marcadores = document.querySelectorAll('#heroMarcadores .hero-marcador');
    let indiceAtual = 0;
    let temporizador = null;
    const INTERVALO = 5500; // ms entre trocas de foto

    function mostrarSlide(indice) {
        slides.forEach((slide, i) => slide.classList.toggle('ativo', i === indice));
        marcadores.forEach((marcador, i) => marcador.classList.toggle('ativo', i === indice));
        indiceAtual = indice;
    }

    function proximoSlide() {
        mostrarSlide((indiceAtual + 1) % slides.length);
    }

    function iniciarAutoplay() {
        if (slides.length > 1) {
            temporizador = setInterval(proximoSlide, INTERVALO);
        }
    }

    if (slides.length > 1) {
        iniciarAutoplay();

        marcadores.forEach((marcador) => {
            marcador.addEventListener('click', function () {
                clearInterval(temporizador);
                mostrarSlide(parseInt(this.dataset.slide, 10));
                iniciarAutoplay();
            });
        });
    }

    /* ---------- Contadores animados (anos de experiência, especialidades, etc.) ---------- */
    const contadores = document.querySelectorAll('[data-contador]');
    const DURACAO_CONTAGEM = 1600; // ms

    function animarContador(elemento) {
        const alvo = parseInt(elemento.dataset.alvo, 10) || 0;
        const prefixo = elemento.dataset.prefixo || '';
        const sufixo = elemento.dataset.sufixo || '';
        const inicio = performance.now();

        function passo(agora) {
            const progresso = Math.min((agora - inicio) / DURACAO_CONTAGEM, 1);
            const progressoSuave = 1 - Math.pow(1 - progresso, 2);
            const valorAtual = Math.round(alvo * progressoSuave);

            elemento.textContent = prefixo + valorAtual + sufixo;

            if (progresso < 1) {
                requestAnimationFrame(passo);
            }
        }

        requestAnimationFrame(passo);
    }

    if (contadores.length > 0) {
        const observador = new IntersectionObserver((entradas) => {
            entradas.forEach((entrada) => {
                if (entrada.isIntersecting) {
                    animarContador(entrada.target);
                    observador.unobserve(entrada.target); // conta uma única vez
                }
            });
        }, { threshold: 0.5 });

        contadores.forEach((elemento) => observador.observe(elemento));
    }

    /* ---------- Menu mobile ---------- */
    const botaoMenu = document.getElementById('menuAlterna');
    const navegacao = document.getElementById('navegacao');

    if (botaoMenu && navegacao) {
        botaoMenu.addEventListener('click', function () {
            const aberto = navegacao.classList.toggle('aberto');
            botaoMenu.setAttribute('aria-expanded', aberto);
        });

        // Fecha o menu ao escolher um link (útil em ecrãs pequenos)
        navegacao.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navegacao.classList.remove('aberto');
                botaoMenu.setAttribute('aria-expanded', 'false');
            });
        });
    }

});
