<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/lib/sorteos.php';

$sorteos = obtenerSorteosConProgresso($pdo);

$mesesPt = [1=>'jan',2=>'fev',3=>'mar',4=>'abr',5=>'mai',6=>'jun',7=>'jul',8=>'ago',9=>'set',10=>'out',11=>'nov',12=>'dez'];

function formatarData(string $data, array $mesesPt): string {
    $ts = strtotime($data);
    return date('d', $ts) . ' ' . $mesesPt[(int)date('n', $ts)];
}

function formatarHora(string $hora): string {
    return substr($hora, 0, 2) . 'h';
}

function formatarPremio(float $valor): string {
    return 'R$ ' . number_format($valor, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apuesta com Yusme — Sorteios Ativos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,500&family=Space+Grotesk:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#1D0E3D;
    --ink-2:#2C1558;
    --hot:#FF4470;
    --hot-deep:#E22758;
    --gold:#FFCB3D;
    --lime:#C6FF5C;
    --green:#28C76F;
    --paper:#FFF6E0;
    --paper-dim:#F7E9BE;
    --text-on-ink:#FFF6E9;
    --text-on-paper:#26143F;
    --line-on-ink:rgba(255,246,233,0.24);
    --line-on-paper:#DCC98B;
  }

  *{ box-sizing:border-box; }
  html{ scroll-behavior:smooth; }
  @media (prefers-reduced-motion: reduce){
    html{ scroll-behavior:auto; }
    *{ animation:none !important; transition-duration:0.01ms !important; }
  }

  body{
    margin:0;
    background:var(--ink);
    color:var(--text-on-ink);
    font-family:'Work Sans', sans-serif;
    line-height:1.5;
    overflow-x:hidden;
  }
  a{ color:inherit; }
  :focus-visible{ outline:3px solid var(--gold); outline-offset:3px; }

  .wrap{ max-width:1080px; margin:0 auto; padding:0 24px; }

  .marquee{
    background:var(--hot); color:var(--ink); overflow:hidden; white-space:nowrap;
    font-family:'Space Grotesk', sans-serif; font-weight:600; font-size:13px; letter-spacing:0.4px; padding:9px 0;
  }
  .marquee div{ display:inline-block; animation:scroll 22s linear infinite; }
  @keyframes scroll{ from{ transform:translateX(0); } to{ transform:translateX(-50%); } }
  .marquee span{ margin:0 28px; }

  header{ padding:24px 0; border-bottom:1px solid var(--line-on-ink); }
  header .wrap{ display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; }
  .wordmark{ font-family:'Fraunces', serif; font-weight:700; font-size:21px; }
  .wordmark span{ color:var(--gold); font-style:italic; font-weight:500; }
  nav{ display:flex; gap:26px; align-items:center; }
  nav a.link{ text-decoration:none; font-size:15px; opacity:0.85; }
  nav a.link:hover{ opacity:1; }
  .btn{
    display:inline-block; background:var(--hot); color:#fff; font-weight:700; font-size:15px;
    padding:12px 24px; border-radius:999px; text-decoration:none; border:none; cursor:pointer;
    font-family:'Work Sans', sans-serif; box-shadow:0 8px 22px -8px rgba(255,68,112,0.7);
  }
  .btn:hover{ background:var(--hot-deep); }

  .hero{
    position:relative; padding:70px 0 90px;
    background:
      radial-gradient(circle at 12% 18%, rgba(255,203,61,0.16), transparent 40%),
      radial-gradient(circle at 88% 76%, rgba(255,68,112,0.22), transparent 45%);
  }
  .confetti{ position:absolute; inset:0; overflow:hidden; pointer-events:none; }
  .confetti i{ position:absolute; border-radius:50%; opacity:0.55; }

  .hero-badge{
    display:inline-flex; align-items:center; gap:8px;
    font-family:'Space Grotesk', sans-serif; font-weight:600; font-size:13px; letter-spacing:0.5px;
    background:var(--ink-2); border:1px solid var(--line-on-ink); color:var(--lime);
    padding:8px 16px; border-radius:999px; margin-bottom:26px;
  }
  .hero-badge::before{ content:"●"; font-size:10px; }

  .hero h1{
    font-family:'Fraunces', serif; font-weight:700; font-size:clamp(40px,6.4vw,64px);
    line-height:1.02; margin:0 0 20px; max-width:12ch;
  }
  .hero h1 em{ font-style:italic; color:var(--gold); }
  .hero p.lede{ font-size:18px; max-width:48ch; opacity:0.82; margin:0 0 32px; }
  .hero-cta{ display:flex; gap:14px; flex-wrap:wrap; align-items:center; }
  .hero-cta .ghost{ font-size:15px; text-decoration:underline; text-underline-offset:4px; opacity:0.85; }

  .hero-prizes{ margin-top:56px; display:grid; grid-template-columns:1fr 1fr; gap:18px; }
  @media (max-width:640px){ .hero-prizes{ grid-template-columns:1fr; } }
  .prize-chip{ background:var(--ink-2); border:1px solid var(--line-on-ink); border-radius:14px; padding:20px 22px; position:relative; }
  .prize-chip .label{ font-family:'Space Grotesk', sans-serif; font-size:12px; font-weight:600; letter-spacing:0.5px; color:var(--lime); }
  .prize-chip .amount{ font-family:'Anton', sans-serif; font-size:38px; color:var(--gold); line-height:1.1; margin-top:4px; }

  .sold-badge{
    display:inline-flex; align-items:center; gap:6px; margin-top:10px;
    font-family:'Space Grotesk', sans-serif; font-weight:700; font-size:13px;
    color:var(--green); background:rgba(40,199,111,0.14); border:1px solid rgba(40,199,111,0.4);
    padding:5px 12px; border-radius:999px; transition:transform 0.3s ease;
  }
  .sold-badge::before{ content:"●"; font-size:8px; }
  .sold-badge.atualizado{ transform:scale(1.08); }

  .section-label{ font-family:'Fraunces', serif; font-style:italic; font-weight:600; font-size:28px; margin:0 0 6px; }
  .section-sub{ font-size:15px; opacity:0.72; margin:0 0 30px; max-width:52ch; }

  #sorteios{ padding:80px 0 10px; }

  .tickets{ display:grid; grid-template-columns:1fr 1fr; gap:22px; margin-bottom:16px; }
  @media (max-width:720px){ .tickets{ grid-template-columns:1fr; } }

  .ticket{
    background:var(--paper); color:var(--text-on-paper); border-radius:10px; position:relative;
    padding:0; overflow:hidden; box-shadow:0 22px 46px -24px rgba(0,0,0,0.6);
  }
  .ticket:nth-child(odd){ transform:rotate(-1deg); }
  .ticket:nth-child(even){ transform:rotate(0.9deg); }
  .ticket::before, .ticket::after{
    content:""; position:absolute; top:128px; width:24px; height:24px; background:var(--ink); border-radius:50%; z-index:2;
  }
  .ticket::before{ left:-12px; }
  .ticket::after{ right:-12px; }

  .ticket .head{
    background:var(--hot); color:#fff; padding:16px 24px; display:flex; justify-content:space-between; align-items:center;
    font-family:'Space Grotesk', sans-serif; font-weight:600; font-size:13px; letter-spacing:0.5px;
  }
  .ticket .body{ padding:22px 24px 24px; }
  .ticket .amount{ font-family:'Anton', sans-serif; font-size:44px; color:var(--hot-deep); line-height:1; }
  .ticket .amount-label{ font-size:13px; opacity:0.65; margin-bottom:2px; }

  .perf{ border-top:1.5px dashed var(--line-on-paper); margin:18px 0 4px; }

  .ticket-btn{ display:block; text-align:center; margin-top:20px; font-size:14px; padding:12px 16px; background:var(--ink); color:var(--text-on-ink); box-shadow:none; }
  .ticket-btn:hover{ background:#000; }

  .note{ font-size:14px; opacity:0.65; max-width:64ch; margin:18px 0 0; }

  .packages{ padding:74px 0 64px; }
  .sorteio-toggle{ display:inline-flex; background:var(--ink-2); border-radius:999px; padding:4px; margin-bottom:28px; gap:4px; }
  .sorteio-toggle button{
    font-family:'Work Sans', sans-serif; font-size:14px; font-weight:500; padding:10px 18px; border-radius:999px;
    border:none; background:transparent; color:var(--text-on-ink); opacity:0.7; cursor:pointer;
  }
  .sorteio-toggle button.active{ background:var(--gold); color:var(--ink); opacity:1; font-weight:700; }

  .price-list{ border-top:1px solid var(--line-on-ink); }
  .price-row{ display:flex; align-items:baseline; gap:16px; padding:20px 4px; border-bottom:1px solid var(--line-on-ink); }
  .price-row .name{ font-family:'Fraunces', serif; font-weight:600; font-size:21px; white-space:nowrap; }
  .price-row .leader{ flex:1; border-bottom:1px dotted var(--line-on-ink); height:0; transform:translateY(-6px); }
  .price-row .price{ font-family:'Anton', sans-serif; font-size:22px; white-space:nowrap; color:var(--text-on-ink); }
  .price-row.featured{ background:linear-gradient(90deg, rgba(255,203,61,0.14), transparent); border-radius:8px; padding-left:14px; padding-right:14px; }
  .price-row.featured .name::after{
    content:"mais escolhido"; display:inline-block; margin-left:10px; font-family:'Work Sans', sans-serif; font-size:11px;
    font-weight:700; color:var(--ink); background:var(--gold); padding:4px 10px; border-radius:999px; vertical-align:middle;
  }
  .price-row.featured .price{ color:var(--gold); }

  .how{ padding:20px 0 76px; }
  .steps{ display:grid; grid-template-columns:repeat(3,1fr); gap:28px; margin-top:34px; }
  @media (max-width:720px){ .steps{ grid-template-columns:1fr; } }
  .step{ background:var(--ink-2); border:1px solid var(--line-on-ink); border-radius:12px; padding:24px; }
  .step .num{ font-family:'Anton', sans-serif; font-size:26px; color:var(--hot); margin-bottom:10px; }
  .step h3{ font-family:'Fraunces', serif; font-size:20px; margin:0 0 8px; font-weight:600; }
  .step p{ font-size:15px; opacity:0.78; margin:0; }

  footer{ background:var(--paper); color:var(--text-on-paper); padding:54px 0 40px; }
  .pix-block{ display:flex; justify-content:space-between; gap:32px; flex-wrap:wrap; padding-bottom:36px; border-bottom:1px solid var(--line-on-paper); margin-bottom:22px; }
  .pix-block h4{ font-family:'Fraunces', serif; font-size:19px; margin:0 0 8px; font-weight:600; }
  .pix-block p{ font-size:14px; opacity:0.75; max-width:38ch; margin:0; }
  .pix-key{
    font-family:'Space Grotesk', sans-serif; font-size:15px; background:var(--paper-dim); border:1px solid var(--line-on-paper);
    padding:10px 16px; border-radius:6px; display:inline-block; margin-top:10px; font-weight:600;
  }
  .foot-bottom{ display:flex; justify-content:space-between; font-size:13px; opacity:0.6; flex-wrap:wrap; gap:10px; }
</style>
</head>
<body>

<div class="marquee" aria-hidden="true">
  <div>
    <span>★ GARANTA SEU NÚMERO AGORA</span>
    <span>★ SORTEIO PELA LOTERIA FEDERAL</span>
    <span>★ PAGAMENTO NA HORA PELO PIX</span>
    <span>★ GARANTA SEU NÚMERO AGORA</span>
    <span>★ SORTEIO PELA LOTERIA FEDERAL</span>
    <span>★ PAGAMENTO NA HORA PELO PIX</span>
  </div>
</div>

<header>
  <div class="wrap">
    <div class="wordmark">Apuesta com <span>Yusme</span></div>
    <nav>
      <a class="link" href="#sorteios">Sorteios</a>
      <a class="link" href="#pacotes">Pacotes</a>
      <a class="link" href="#como-funciona">Como funciona</a>
      <a class="btn" href="#pacotes">Comprar números</a>
    </nav>
  </div>
</header>

<section class="hero">
  <div class="confetti" aria-hidden="true">
    <i style="left:6%; top:20%; width:10px; height:10px; background:var(--gold);"></i>
    <i style="left:14%; top:60%; width:7px; height:7px; background:var(--lime);"></i>
    <i style="left:24%; top:12%; width:6px; height:6px; background:var(--hot);"></i>
    <i style="left:82%; top:22%; width:12px; height:12px; background:var(--gold);"></i>
    <i style="left:90%; top:58%; width:8px; height:8px; background:var(--lime);"></i>
    <i style="left:70%; top:8%; width:6px; height:6px; background:var(--hot);"></i>
    <i style="left:50%; top:85%; width:9px; height:9px; background:var(--gold);"></i>
  </div>
  <div class="wrap">
    <span class="hero-badge"><?= count($sorteos) ?> sorteios ativos agora</span>
    <h1>Seu número da sorte pode valer <em><?= isset($sorteos[1]) ? formatarPremio($sorteos[1]['premio']) : (isset($sorteos[0]) ? formatarPremio($sorteos[0]['premio']) : 'R$ 5.000') ?></em>.</h1>
    <p class="lede">Escolha seus números, pague pelo PIX na hora e concorra aos prêmios tirados pela Loteria Federal.</p>
    <div class="hero-cta">
      <a class="btn" href="#pacotes">Quero meus números</a>
      <a class="ghost" href="#sorteios">Ver sorteios ativos ↓</a>
    </div>

    <div class="hero-prizes">
      <?php foreach ($sorteos as $i => $s): ?>
      <div class="prize-chip">
        <div class="label"><?= ($i+1) ?>º SORTEIO · <?= formatarData($s['fecha'], $mesesPt) ?> · <?= formatarHora($s['hora']) ?></div>
        <div class="amount"><?= formatarPremio($s['premio']) ?></div>
        <span class="sold-badge" data-sold-sorteio="<?= $s['id'] ?>"><?= $s['porcentaje'] ?>% vendido</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="sorteios" class="wrap">
  <p class="section-label">Sorteios ativos</p>
  <p class="section-sub">Cada sorteio tem seu próprio pool de números — comprar em um não reserva números no outro.</p>

  <div class="tickets">
    <?php foreach ($sorteos as $i => $s): ?>
    <div class="ticket">
      <div class="head"><span><?= ($i+1) ?>º SORTEIO</span><span><?= formatarData($s['fecha'], $mesesPt) ?> · <?= formatarHora($s['hora']) ?></span></div>
      <div class="body">
        <div class="amount-label">Prêmio</div>
        <div class="amount"><?= formatarPremio($s['premio']) ?></div>
        <span class="sold-badge" data-sold-sorteio="<?= $s['id'] ?>"><?= $s['porcentaje'] ?>% vendido</span>
        <div class="perf"></div>
        <a class="btn ticket-btn" href="#pacotes" data-sorteio="<?= $s['id'] ?>">Comprar números deste sorteio</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <p class="note">Os sorteios são feitos com base no resultado da Loteria Federal, nas datas e horários indicados em cada bilhete.</p>
</section>

<section id="pacotes" class="packages wrap">
  <p class="section-label">Escolha seu pacote</p>
  <p class="section-sub">Quanto mais números, maior o desconto por número. Os números valem só para o sorteio escolhido abaixo.</p>

  <div class="sorteio-toggle" role="tablist" aria-label="Escolher sorteio">
    <?php foreach ($sorteos as $i => $s): ?>
    <button type="button" class="<?= $i === 0 ? 'active' : '' ?>" role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" data-sorteio="<?= $s['id'] ?>">
      <?= ($i+1) ?>º Sorteio · <?= formatarData($s['fecha'], $mesesPt) ?>
    </button>
    <?php endforeach; ?>
  </div>

  <div class="price-list">
    <div class="price-row"><span class="name">1 número</span><span class="leader"></span><span class="price">R$ 3</span></div>
    <div class="price-row"><span class="name">5 números</span><span class="leader"></span><span class="price">R$ 12</span></div>
    <div class="price-row featured"><span class="name">10 números</span><span class="leader"></span><span class="price">R$ 20</span></div>
    <div class="price-row"><span class="name">50 números</span><span class="leader"></span><span class="price">R$ 80</span></div>
    <div class="price-row"><span class="name">100 números</span><span class="leader"></span><span class="price">R$ 140</span></div>
  </div>
</section>

<section id="como-funciona" class="how wrap">
  <p class="section-label">Como funciona</p>
  <p class="section-sub">Do pacote ao pagamento, em três passos.</p>

  <div class="steps">
    <div class="step">
      <div class="num">1</div>
      <h3>Escolha seu pacote</h3>
      <p>Selecione quantos números quer e deixe seus dados: nome completo, celular e Gmail.</p>
    </div>
    <div class="step">
      <div class="num">2</div>
      <h3>Pague pelo PIX</h3>
      <p>Copie a chave PIX, pague e envie o comprovante para confirmar seus números.</p>
    </div>
    <div class="step">
      <div class="num">3</div>
      <h3>Aguarde o sorteio</h3>
      <p>Acompanhe o resultado da Loteria Federal na data e horário do seu sorteio.</p>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <div class="pix-block">
      <div>
        <h4>Pagamento por PIX</h4>
        <p>Depois de escolher seu pacote, pague usando a chave abaixo e envie o comprovante para liberar seus números.</p>
        <span class="pix-key">yusmeliarreaza242@gmail.com</span>
      </div>
      <div>
        <h4>Dúvidas?</h4>
        <p>Fale com a gente antes de comprar se tiver qualquer dúvida sobre os sorteios ou o pagamento.</p>
      </div>
    </div>
    <div class="foot-bottom">
      <span>Apuesta com Yusme</span>
      <span>Sorteios com base no resultado da Loteria Federal</span>
    </div>
  </div>
</footer>

<script>
// Atualiza o "% vendido" direto do banco a cada 20s, sem recarregar a página.
// Só sobe: reflete exatamente quantos números daquele sorteio já foram vendidos.
async function atualizarProgresso() {
  try {
    const res = await fetch('api/progresso.php', { cache: 'no-store' });
    const dados = await res.json();
    if (!Array.isArray(dados)) return;

    dados.forEach(s => {
      document.querySelectorAll(`[data-sold-sorteio="${s.sorteo_id}"]`).forEach(el => {
        const textoNovo = `${s.porcentaje}% vendido`;
        if (el.textContent !== textoNovo) {
          el.textContent = textoNovo;
          el.classList.add('atualizado');
          setTimeout(() => el.classList.remove('atualizado'), 300);
        }
      });
    });
  } catch (e) {
    // Falha silenciosa: mantém o último valor renderizado pelo PHP.
  }
}
setInterval(atualizarProgresso, 20000);
</script>

</body>
</html>
