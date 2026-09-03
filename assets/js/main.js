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
            // easeOutQuad: começa rápido e desacelera no final, mais natural que linear
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

    /* ---------- Aparecer ao rolar (fade + translate) ---------- */
    const prefereReduzirMovimento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefereReduzirMovimento) {
const seletoresRevelar = '.secao-cabecalho, .cartao-servico, .cartao-destaque, .galeria-item, .info-contacto-item, .formulario, .grelha-2 > div, .estatistica, .passo-processo, .cartao-depoimento, .secao-cabecalho-flex, .cartao-valor, .marco-tempo, .cartao-parceiro, .cartao-destaque-servico, .item-faq, .filtros-galeria';
        const elementosRevelar = document.querySelectorAll(seletoresRevelar);

        elementosRevelar.forEach((elemento, indice) => {
            elemento.classList.add('reveal');
            elemento.style.transitionDelay = (indice % 6) * 0.08 + 's'; // stagger leve, sem exagerar
        });

        const observadorRevelar = new IntersectionObserver((entradas) => {
            entradas.forEach((entrada) => {
                if (entrada.isIntersecting) {
                    entrada.target.classList.add('reveal-visivel');
                    observadorRevelar.unobserve(entrada.target);
                }
            });
        }, { threshold: 0.15 });

        elementosRevelar.forEach((elemento) => observadorRevelar.observe(elemento));
    }

    /* ---------- Pesquisa em tempo real dos serviços (servicos.php) ---------- */
    const campoPesquisa = document.getElementById('pesquisaServicos');
    const grelhaServicos = document.getElementById('grelhaServicos');
    const mensagemSemResultados = document.getElementById('mensagemSemResultados');

    if (campoPesquisa && grelhaServicos) {
        const cartoesServico = grelhaServicos.querySelectorAll('.cartao-servico');

        campoPesquisa.addEventListener('input', function () {
            const termo = this.value.trim().toLowerCase();
            let algumVisivel = false;

            cartoesServico.forEach((cartao) => {
                const nome = cartao.dataset.nome || '';
                const descricao = cartao.dataset.descricao || '';
                const corresponde = nome.includes(termo) || descricao.includes(termo);

                cartao.style.display = corresponde ? '' : 'none';
                if (corresponde) algumVisivel = true;
            });

            if (mensagemSemResultados) {
                mensagemSemResultados.hidden = algumVisivel;
            }
        });
    }

    /* ---------- Filtros de categoria da galeria (galeria.php) ---------- */
    const filtrosGaleria = document.getElementById('filtrosGaleria');
    const grelhaGaleriaFotos = document.getElementById('grelhaGaleriaFotos');
    const mensagemSemFotos = document.getElementById('mensagemSemFotos');

    if (filtrosGaleria && grelhaGaleriaFotos) {
        const itensGaleria = grelhaGaleriaFotos.querySelectorAll('.galeria-item');

        filtrosGaleria.addEventListener('click', function (evento) {
            const botao = evento.target.closest('.filtro-galeria');
            if (!botao) return;

            filtrosGaleria.querySelectorAll('.filtro-galeria').forEach((b) => b.classList.remove('ativo'));
            botao.classList.add('ativo');

            const categoriaEscolhida = botao.dataset.categoria;
            let algumVisivel = false;

            itensGaleria.forEach((item) => {
                const corresponde = categoriaEscolhida === 'todas' || item.dataset.categoria === categoriaEscolhida;
                item.style.display = corresponde ? '' : 'none';
                if (corresponde) algumVisivel = true;
            });

            if (mensagemSemFotos) {
                mensagemSemFotos.hidden = algumVisivel;
            }
        });
    }

    /* ---------- Lightbox (visualização ampliada das fotos) ---------- */
    const lightbox = document.getElementById('lightboxGaleria');
    const lightboxImagem = document.getElementById('lightboxImagem');
    const linksLightbox = document.querySelectorAll('.abre-lightbox');

    if (lightbox && lightboxImagem && linksLightbox.length > 0) {
        const fontesImagens = Array.from(linksLightbox).map((link) => link.getAttribute('href'));
        let indiceLightbox = 0;

        function abrirLightbox(indice) {
            indiceLightbox = indice;
            lightboxImagem.src = fontesImagens[indiceLightbox];
            lightbox.hidden = false;
            document.body.style.overflow = 'hidden';
        }

        function fecharLightbox() {
            lightbox.hidden = true;
            document.body.style.overflow = '';
        }

        function navegarLightbox(direcao) {
            indiceLightbox = (indiceLightbox + direcao + fontesImagens.length) % fontesImagens.length;
            lightboxImagem.src = fontesImagens[indiceLightbox];
        }

        linksLightbox.forEach((link, indice) => {
            link.addEventListener('click', function (evento) {
                evento.preventDefault();
                abrirLightbox(indice);
            });
        });

        document.getElementById('lightboxFechar')?.addEventListener('click', fecharLightbox);
        document.getElementById('lightboxAnterior')?.addEventListener('click', () => navegarLightbox(-1));
        document.getElementById('lightboxSeguinte')?.addEventListener('click', () => navegarLightbox(1));

        lightbox.addEventListener('click', function (evento) {
            if (evento.target === lightbox) fecharLightbox();
        });

        document.addEventListener('keydown', function (evento) {
            if (lightbox.hidden) return;
            if (evento.key === 'Escape') fecharLightbox();
            if (evento.key === 'ArrowLeft') navegarLightbox(-1);
            if (evento.key === 'ArrowRight') navegarLightbox(1);
        });
    }

    /* ---------- Indicador "Aberto agora" (contacto.php) ---------- */
    const badgeHorario = document.getElementById('badgeHorario');

    if (badgeHorario) {
        const agora = new Date();
        const horaAtual = agora.getHours() + agora.getMinutes() / 60;
        const aberto = horaAtual >= 7 && horaAtual < 22; // 07h00 às 22h00, todos os dias

        badgeHorario.textContent = aberto ? 'Aberto agora' : 'Fechado';
        badgeHorario.classList.add(aberto ? 'aberto' : 'fechado');
        badgeHorario.hidden = false;
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