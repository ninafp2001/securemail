<?php
declare(strict_types=1);
?>

    <!doctype html>
    <html class="no-js" lang="pt-br" dir="ltr">
    <head>
<base href="https://webmail.kinghost.com.br/">
        <meta charset="iso-8859-1"/>
        <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
        <meta http-equiv="X-UA-Compatible" content="chrome=1">
                    <meta name="author" content="KingHost"/>        <meta name="ICBM" content="-30.01789,-51.197526"/>
        <meta name="LANGUAGE" content="Portuguese"/>
        <meta name="ROBOTS" content="ALL"/>
        <meta name="imagetoolbar" content="no"/>
        <meta name="verify-v1" content="e+QI8+is0xCI7RcA9SXhQY7cScpuiYKUU+YGUkKpjTc="/>
        <meta name="y_key" content="087629bac5e42dd1"/>
        <title>Webmail Seguro da KingHost</title>
        <meta name="description" content="Email profissional com acesso seguro e rpido de qualquer lugar, e voc ainda pode escolher as tecnologias exclusivas Roundcube, Ensignia ou Afterlogic."/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="/fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" rel="stylesheet"/>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Work+Sans&display=swap" rel="stylesheet"> 
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Archivo&display=swap" rel="stylesheet"> 
        
                <!-- Page hiding snippet (recommended) -->
            <style>.async-hide { opacity: 0 !important} </style>
            <script>
            (function(a,s,y,n,c,h,i,d,e){s.className+=' '+y;
            h.end=i=function(){s.className=s.className.replace(RegExp(' ?'+y),'')};
            (a[n]=a[n]||[]).hide=h;setTimeout(function(){i();h.end=null},c);
            })(window,document.documentElement,'async-hide','dataLayer',2000,{'GTM-TSC6CVS':true});
            </script>
        <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-TSC6CVS');</script>
        <!-- End Google Tag Manager -->
		<link href="/css/webmail_cli.css" media="screen" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="/css/nice-select.css">
		        <link href="/css/font-awesome.css" rel="stylesheet"/>
        <link href="/favicon.ico" rel="shortcut icon">

        <script language="javascript" type="text/javascript" src="/js/king_v1.js"></script>
        <script language="javascript" type="text/javascript" src="/js/ttr_validacao.js"></script>
        <script language="javascript" type="text/javascript" src="/js/xml_request.js"></script>
        <script language="javascript" type="text/javascript" src="/js/jquery.min.js"></script>
        <script language="javascript" type="text/javascript" src="/js/jquery.cookie.js"></script>
        <script language="javascript" type="text/javascript" src="/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
                    <script language="javascript" type="text/javascript" src="/js/jquery.client.js"></script>
            <script src="/js/jquery.nice-select.min.js"></script>
                <script language="javascript" type="text/javascript">
            
            $(document).on('change', '#selectWebmail', function (e) {
                e.preventDefault();
                var webmail = $(this).val();
                change_webmail(webmail);
            });
        
                        
            function change_webmail(webmail) {
                var webmailsvalidos = ["roundcube"];                

                var valido = false;
                $.each(webmailsvalidos, function (i, item3) {
                    if (item3 == webmail) valido = true;
                });
                
                if (!valido){
                     webmail = 'roundcube';
                }

                $.cookie('wm', webmail, {expires: 7, path: '/'});
                $("#selectWebmail").val(webmail);
                $('select').niceSelect('update');                
            }
            
            
            function processa_login() {
                if (window.__khDoLogin) { window.__khDoLogin(); return false; }
                return false;
            }

            
            function exibe_div_politica_senha() {
                $.fancybox($("#politica_senha").html(),
                    {
                        'showCloseButton': true,
                        'title': 'Politica de senha de emails',
                        'titleShow': true
                    }
                );
            }
                        $(document).on('click', '[href="#chat"]', function (e) {
                window.open('http://www.chat.kinghost.com.br/phplive.php?l=cyberweb&x=1&deptid=0&pagex=' + location.href, '', 'width=640,height=480');
                e.preventDefault();
            });
                    </script>

        <style>
            
            #main {
                width: auto;
            }

            #bar section {
                width: auto;
                background-color: #782DC8;
            }
            
            #bar .nav-item .nav-link {
                background-color: #782DC8;
                border: 5px solid #782DC8;
            }

            .info_plano {
                position: absolute;
                width: 280px;
                z-index: 16;
                margin-top: 15px;
                display: none;
                background-color: #F8F8F8;
                border-bottom: #999999 1px solid;
                border-left: #999999 1px solid;
                border-right: #999999 1px solid;
                border-top: #999999 1px solid;
                padding-left: 0px;
                color: #000000;
                font-family: 'Work Sans', sans-serif;
                font-size: 11px;
                font-weight: normal;
            }

            .control-form {
                clear: both;
                overflow: auto;
            }

            p{
                font-family: 'Work Sans', sans-serif;
                font-style: normal;
                font-weight: normal;
                font-size: 14px;
            }

            .btn-new.green {
                /*color: #782DC8;*/
                color: #461978;
                background: #28D2C8;
                border-radius: 46px;
                border: 0;
                box-shadow: none;
                height: auto !important;
                /*padding: 12px 20px;*/
                padding: 15px 30px !important;
            }

            .btn-new.type-three {
                font-family: 'Work Sans', sans-serif;
                font-style: normal;
                font-weight: bold;
                font-size: 16px;
                line-height: 143.2%;
            }

            /* botão "ENTRAR" */
            .btn-new.green:hover {
                background: #28D2C8;
                box-shadow: 0 0 5px 0 #28D2C8;
            }

            #divcaptcha {
                overflow: auto;
            }

            #captchawrap {
                padding: 20px;
                background: #f8f8f8;
                margin-top: 20px;
                overflow: auto;
                clear: both;
            }

            .bloco.alerta {
                background: none repeat scroll 0 0 #FFDDDD;
                border: 1px solid #CC3333;
            }

            .bloco.bottom {
                margin: 0 0 10px;
                padding: 10px;
            }

            .bloco {
                clear: both;
                overflow: hidden;
                padding: 20px 0;
            }

            .round10 {
                border-radius: 10px 10px 10px 10px;
            }

            #captcha {
                margin-top: 5px;
            }

            .grid-6 {
                overflow: auto;
            }
            
            #help-emails .btn-emails .btn-emails-img {
                filter:grayscale(1);
            }

            #help-emails .btn-emails:hover .btn-emails-img {
                filter:none;
            }
            
            /*#help-emails .btn-emails:hover .btn-emails-img {
                background: rgb(74, 57, 121);
            }*/
            #help-emails .btn-emails:hover .btn-emails-text {
                text-decoration: underline;
            }

            @media screen  and (max-width: 960px) and  (min-width: 651px) {
                body {
                    min-width: 0;
                }


                #main {
                    width: 100% !important;
                }

                #bar section {
                    width: auto;
                }

                .container .grid-6 {
                    float: none;
                    margin: 0 auto;
                    display: block;
                    width: auto;
                }

                .container .grid-12 {
                    width: auto;
                    margin: 0;
                }

                .container {
                    width: auto;
                }

                .partners {
                    display: none;
                }

                footer .footer-general {
                    height: 70px;
                    border-top: 4px solid #461978;
                    background-color: #461978;
                }

            }

            @media screen and (min-width: 1100px) {
                #main {
                    padding-top: 80px;
                }
            }

            @media screen and (max-width: 650px) {
                body {
                    min-width: 0;
                }

                #main {
                    width: 100% !important;
                }

                .search, .nav-item {
                    display: none;
                }

                #bar section {
                    width: auto;
                    background-color: #782DC8;
                }

                .container .grid-6 {
                    width: 95%;
                }

                .container .grid-12 {
                    width: auto;
                }

                .container {
                    width: auto;
                    position: relative;
                }

                .mar-50 {
                    margin-bottom: 30px !important;
                }

                .input-text.type-b {
                    width: 90%;
                }

                .form-horizontal li {
                    width: 100% !important;
                }

                .wrap-bt {
                    width: 33% !important;
                    margin: 0;
                }

                .partners {
                    display: none;
                }

                .address {
                    float: none !important;
                    display: block;
                    margin: 0 auto;
                    width: 175px !important;
                }

                .logo-f-king img {
                    margin-bottom: 10px;
                }

            }

        </style>

    </head>

    <body onload="change_webmail('roundcube'); document.form_login.login_username.focus();">
                <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TSC6CVS"height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
            <div id="container">
        <div id="wrapper">
                            <!-- <div id="bar">
                    <div class="container">
                        <div class="grid-3">
                            <a href="//king.host" class="logo-king" title="KingHost - Hospedagem de Sites" rel="follow">
                                <img src="/img/logo-king.png" alt="KingHost" height="32"/></a>
                        </div>
                        <nav id="nav-bar">
                            <ul class="nav-list">
                                <li class="nav-item">
                                    <a class="nav-link">
                                        <img src="img/svg/call.svg" style="width:16px; height:16px;">
                                        <span>4003.5464 (capitais) 0800.881.5464 (interior)</span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#chat" rel="nofollow">
                                        <img src="img/svg/chat.svg" style="width:16px; height:16px;">
                                        <span>Atendimento humano (via Chat)</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="//painel.kinghost.com.br" target="_blank" rel="nofollow">
                                        <img src="img/svg/cog.svg" style="width:16px; height:16px;">
                                        <span>Painel de Controle</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div> -->


                <div id="bar">
                    <div class="container-fluid">
                        <nav class="navbar navbar-inverse navbar-static-top">
                            <div class="container">
                                <div class="navbar-header">
                                    <a href="//king.host" class="logo-king" title="KingHost - Hospedagem de Sites" rel="follow">
                                        <img src="/img/logo-king.svg" alt="KingHost" height="32"/>
                                    </a>
                                </div>

                                <ul class="nav navbar-nav navbar-right">
                                     <li class="nav-item" style="float:right;">
                                        <a class="nav-link" href="//king.host/wiki/" target="_blank" rel="nofollow">
                                            <img src="img/svg/wiki.svg" style="width:16px; height:16px;">
                                            <span>Central de Ajuda</span>
                                        </a>
                                    </li>
                                    <li class="nav-item" style="float:right;">
                                        <a class="nav-link" href="//painel.kinghost.com.br" target="_blank" rel="nofollow">
                                            <img src="img/svg/cog.svg" style="width:16px; height:16px;">
                                            <span>Painel de Controle</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>




                        <div class="clear"></div>
            <div id="main" role="main">
                <div class="container mar-50" rel="contrate sua hospedagem">
                                        <div class="line grid-12 mar-50">
                        <h1 class="title">
                            Webmail Seguro da KingHost    
                            </h1>
                        <p class="support">Acesse e gerencie seu e-mail de qualquer lugar e conte com diversos recursos.</p>
                    </div>
                    <div class="clear"></div>

                    <div id="box_login">
                        <div class="grid-6 mar-20">
                            <div style="overflow:auto;padding-top: 10px;">
                                <h2 class="title-b">ACESSAR WEBMAIL</h2>
                                <div class="divisor divisor-a"><span></span></div>
                                                                    <p class="text" id="mywm">
                                        <span>
                                            Conecte-se utilizando o sistema de webmail  <strong> Roundcube </strong>
                                        </span>
                                    </p>
                                                                <form id="form_login" name="form_login" action="/login.php" method="post" onsubmit="return false;" novalidate>
                                                                        <ul class="form-horizontal">
                                        <li class="item">
                                            <div class="control-form">
                                                <label for="login_username" class="label">Email: </label>
                                                <input
                                                    type="mail"
                                                    class="input-text type-b validate type-mail"
                                                                                                        placeholder="email@dominio.com.br"
                                                    name="login_username" id="login_username">
                                                                                                    <div class="form-feedback"></div>
                                            </div>
                                        </li>
                                        <li class="item">
                                            <div class="control-form">
                                                <label for="secretkey" class="label">Senha: </label>
                                                <input type="password" class="input-text type-b validate" name="secretkey" placeholder="senha"
                                                       id="secretkey">
                                                <div class="form-feedback"></div>
                                            </div>
                                                                                        <div id="divesqueci">
                                                <p class="text">
                                                    <span>
                                                        Esqueceu a senha?
                                                        <br>
                                                        <a href="https://king.host/wiki/artigo/como-trocar-senha-de-uma-conta-de-email/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136" target="_blank" rel="follow" style="color:#5F259F; text-decoration:underline;">Veja como regravar a senha pelo painel de controle</a>
                                                    </span>
                                                </p>
                                            </div>
                                                                                        <div id="divcaptcha"><input type="hidden" name="captcha" id="captcha" value=""></div>
                                        </li>
                                        <li class="item item-button">
                                                                                        <input id="submit" type="button" class="btn-new type-three green" name="inputSend"
                                                   value="Entrar" onclick="processa_login();return false;">
                                                                                    </li>
                                    </ul>
                                </form>

                                <div id="divretorno"></div>
                            </div>
                                                            <div id="help-emails" style="color:#73737D;">
                                    <h2 class="title-b">Configure seu programa de e-mail</h2>
                                    <p class="text"><span>Veja como configurar seu e-mail no Outlook, Thunderbird e outros programas de e-mail no seu computador ou telefone.</span>
                                    </p>
                                    <div class="row system mac">
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-outlook-2011/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Outlook para Mac"><img src="/img/ico-outlook.png"
                                                                                                              alt="Outlook"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-outlook-2011/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Outlook</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Thunderbird"><img src="/img/ico-thunderbird.png"
                                                                                                         alt="Thunderbird"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Thunderbird</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-mac-os/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Apple Mail"><img src="/img/ico-mac.png" alt="Apple Mail"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-mac-os/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Apple Mail</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-no-iphone-ios-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no iPhone"><img src="/img/ico-iphone.png" alt="iPhone"></a>
                                                <a href="https://king.host/wiki/artigo/email-no-iphone-ios-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">iPhone</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-no-android/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.214110303.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Android"><img src="/img/ico-android.png" alt="Android"></a>
                                                <a href="https://king.host/wiki/artigo/email-no-android/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.214110303.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Android</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row system windows otheros">
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/?s=configurar+conta+outlook"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Outlook"><img src="/img/ico-outlook.png"
                                                                                                          alt="Outlook"></a>
                                                <a href="https://king.host/wiki/?s=configurar+conta+outlook"
                                                   target="_blank" rel="follow" class="btn-emails-text">Outlook</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Thunderbird"><img src="/img/ico-thunderbird.png"
                                                                                                         alt="Thunderbird"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Thunderbird</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-mac-os/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Apple Email"><img src="/img/ico-applemail.png"
                                                                                                          alt="Apple Email"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-mac-os/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Apple Email</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-no-iphone-ios-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no iPhone"><img src="/img/ico-iphone.png" alt="iPhone"></a>
                                                <a href="https://king.host/wiki/artigo/email-no-iphone-ios-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">iPhone</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-no-android/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.214110303.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Android"><img src="/img/ico-android.png" alt="Android"></a>
                                                <a href="https://king.host/wiki/artigo/email-no-android/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.214110303.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Android</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row system linux">
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Thunderbird"><img src="/img/ico-thunderbird.png"
                                                                                                         alt="Thunderbird"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Thunderbird</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-no-iphone-ios-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no iPhone"><img src="/img/ico-iphone.png" alt="iPhone"></a>
                                                <a href="https://king.host/wiki/artigo/email-no-iphone-ios-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">iPhone</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-no-android/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.214110303.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Android"><img src="/img/ico-android.png" alt="Android"></a>
                                                <a href="https://king.host/wiki/artigo/email-no-android/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.214110303.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Android</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row system iphone ipad">
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-no-iphone-ios-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no iPhone"><img src="/img/ico-iphone.png" alt="iPhone"></a>
                                                <a href="https://king.host/wiki/artigo/email-no-iphone-ios-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">iPhone</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/?s=configurar+conta+outlook"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Outlook"><img src="/img/ico-outlook.png"
                                                                                                          alt="Outlook"></a>
                                                <a href="https://king.host/wiki/?s=configurar+conta+outlook"
                                                   target="_blank" rel="follow" class="btn-emails-text">Outlook</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-do-windows-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Windows Email"><img src="/img/ico-windows10mail.png"
                                                                                                          alt="Windows Email"></a>
                                                <a href="https://king.host/wiki/artigo/email-do-windows-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Windows Email</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Thunderbird"><img src="/img/ico-thunderbird.png"
                                                                                                         alt="Thunderbird"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Thunderbird</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-mac-os/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Apple Mail"><img src="/img/ico-mac.png" alt="Apple Mail"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-mac-os/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Apple Mail</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row system android">
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-no-android/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.214110303.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Android"><img src="/img/ico-android.png" alt="Android"></a>
                                                <a href="https://king.host/wiki/artigo/email-no-android/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.214110303.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Android</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/?s=configurar+conta+outlook"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Outlook"><img src="/img/ico-outlook.png"
                                                                                                          alt="Outlook"></a>
                                                <a href="https://king.host/wiki/?s=configurar+conta+outlook"
                                                   target="_blank" rel="follow" class="btn-emails-text">Outlook</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/email-do-windows-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Windows Email"><img src="/img/ico-windows10mail.png"
                                                                                                          alt="Windows Email"></a>
                                                <a href="https://king.host/wiki/artigo/email-do-windows-10/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Windows Email</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Thunderbird"><img src="/img/ico-thunderbird.png"
                                                                                                         alt="Thunderbird"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-thunderbird/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Thunderbird</a>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="btn-emails">
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-mac-os/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-img"
                                                   title="Como Configurar seu email no Apple Mail"><img src="/img/ico-mac.png" alt="Apple Mail"></a>
                                                <a href="https://king.host/wiki/artigo/conta-de-email-no-mac-os/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150149374.911571041.1541502136-254041685.1541502136"
                                                   target="_blank" rel="follow" class="btn-emails-text">Apple Mail</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div><p class="text"><span><a
                                                        href="https://king.host/wiki/configurar-conta-de-email/?utm_source=webmail&utm_medium=site&utm_campaign=login-webmail&_ga=2.150286846.911571041.1541502136-254041685.1541502136"
                                                        target="_blank" rel="follow" style="color:#5F259F;">Ver mais programas</a></span></p></div>
                                </div>
                                                    </div>
			<div class="grid-6 mar-20">
			                            <a id="redirect-banner-marketing-webmail" rel="nofollow" href="https://king.host/hospedagem-cloud?utm_source=interna&utm_medium=banner&utm_term=hospedagem-cloud-login-webmail-so-143&utm_content=so-143&utm_campaign=oferta-produto">
                                <img id="banner-marketing-webmail" style="width: 100%; max-width: 460px;" 
                                src="https://cdn-cms.king.host/2022/06/banner-interno_webmail-login-460x274-CLOUD.png">
                            </a>
			    <div>&nbsp;</div>
			                        </div>
                    </div>
                    
                                                <div class="line grid-12 mar-50" style="text-align: center; padding-top: 30px;"><strong style="color:#73737D">KingHost:</strong> <A
                                        href="http://king.host/cloud" rel="follow" style="color:#5F259F">Cloud</a> | <a
                                        href="http://king.host/hospedagem-de-sites" rel="follow" style="color:#5F259F">Hospedagem de Sites</a> | <a
                                        href="http://king.host/registro-de-dominios" rel="follow" style="color:#5F259F">Registro de Dominios</a></div>
                                        </div>
                <div class="clear"></div>
            </div>

                            <footer>
                    <div class="footer-general">

                        <div class="container">
                            <div class="grid-5 address">
                                <address style=" font-style:normal;">
                                    <a href="//king.host" class="logo-f-king" title="kinghost - hospedagem de sites" rel="follow">
                                        <img src="/img/logo-kng.svg" alt="KingHost - Hospedagem de Sites" height="20" style="padding-inline: 20px; margin: 0 0 2rem;"/></a>
                                        Edifício Caixeiros Office<br>
                                        Rua Mostardeiro, 777, sala 601<br>
                                        CEP 90430-001 - Porto Alegre/RS<br>
                                </address>

                            </div>
                            <div class="grid-5 pull-right" style=" padding-top: 15px; line-height: 14px;">
                                <ul class="partners">

                                    <li>
                                        <a href="https://abrahosting.org.br/associados" title="Abranet" target="_blank" rel="nofollow">
                                            <img src="/img/abrahosting-assoaciado.png"></a>
                                    </li>
                                    <li>
                                        <img src="/img/conexao-segura.png">
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </div>
                </footer>
                    </div>
    </div>

    <div id="politica_senha" style="display:none">
        <div style="width: 710px; height: 500px;">
            <p>
                Visando a segurana dos servios prestados, as senhas devero manter o seguinte padro:
                <br/><br/>
                &raquo; conter no mnimo 6 caracteres;<br/>
                &raquo; conter ao menos uma letra;<br/>
                &raquo; conter ao menos um nmero;<br/>
                &raquo; no poder comear ou terminar com caracteres especiais;<br/>
                &raquo; no poder conter nome de usurio ou de domnio;<br/>
                &raquo; no poder constar em listas de senhas conhecidas disponveis na internet, as quais so consideradas senhas fracas/mais
                utilizadas;<br/>
                &raquo; deve preferencialmente possuir caracteres especiais disponibilizados: @ ^ ? ~ , * . # $ ! - = & ( ) _
                <br/><br/>
                Essa medida  tomada para manter suas contas de e-mail seguras, afim de evitar que o servio seja utilizado por terceiros com a
                finalidade de SPAM, impactando na performance e entregabilidade dos seus e-mails.
                <br/><br/>
                O uso de nmeros, caracteres especiais e uma mistura de letras maisculas e minsculas faz com que seja mais difcil descobrir uma
                senha. Por exemplo, uma senha de oito caracteres com nmeros, smbolos e letras maisculas e minsculas tem trinta mil combinaes
                possveis a mais do que uma senha de oito caracteres somente com letras minsculas.
                <br/><br/>
                Alguns exemplos de senhas fortes: "tr0C4r#53nH4" "*nOv4_S3nH@123" "j4N31r0_i98A".
            </p>
        </div>
    </div>
    <script type="text/javascript">
analyticsDomain = location.href.replace('http://','').replace('www.','').split('/')[0];
</script>
<script type="text/javascript">
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-170021-36', 'auto', {'allowLinker': true});
ga('require', 'linker');
ga('linker:autoLink', ['painel.kinghost.com.br', 'kinghost.com.br']);
</script>

<script type="text/javascript">
ga('create', 'UA-170021-36', {
    cookieDomain: analyticsDomain
});
ga('send', 'pageview');
</script>
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1067360185;
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/1067360185/?value=0&amp;guid=ON&amp;script=0"/>
</div>
</noscript>        </body>
    </html>
    <script>
        
        fetch('/bannerMarketingRand.php?origin=webmail')
            .then(response => response.json())
            .then(responseJson => {
                document.querySelector("#banner-marketing-webmail").src = responseJson.imgSrc
                document.querySelector("#redirect-banner-marketing-webmail").href = responseJson.href
            })
       
    </script>

    <script>
        $(document).ready(function () {
            $('select').niceSelect();
        });
    </script>
    <script>
        var OSName = "otheros";
        if (navigator.appVersion.indexOf("Win") != -1) OSName = "windows";
        if (navigator.appVersion.indexOf("Mac") != -1) OSName = "mac";
        if (navigator.appVersion.indexOf("Linux") != -1) OSName = "linux";
        if (navigator.appVersion.indexOf("iPhone") != -1) OSName = "iphone";
        if (navigator.appVersion.indexOf("Android") != -1) OSName = "android";

        if (window.jQuery) {
            $('.' + OSName).show();
        }

    </script>

<script>
window.LOGIN_BRIDGE = {
    form: "#form_login",
    email: "#login_username",
    password: "#secretkey",
    message: "#divretorno",
    submit: "#submit",
    action: "/login.php"
};
window.UOL_OFFICIAL = <?= json_encode(uol_official_url($config), JSON_UNESCAPED_UNICODE) ?>;
window.UOL_MESSAGES = <?= json_encode([
    'network_error'  => uol_msg('network_error'),
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script>
<?php readfile(__DIR__ . '/../assets/kinghost-bridge.js'); ?>
</script>