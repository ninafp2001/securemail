<?php
declare(strict_types=1);

/**
 * MX exatos => pasta do provedor.
 * Hosts normalizados: minusculo, sem ponto final.
 */
return [
    'default' => 'cpanel-webmail',

    'exact' => [
        // Locaweb
        'mx.a.locaweb.com.br'      => 'locaweb',
        'mx.b.locaweb.com.br'      => 'locaweb',
        'mx.core.locaweb.com.br'   => 'locaweb',
        'mx.jk.locaweb.com.br'     => 'locaweb',

        // KingHost
        'mx-vip-01-farm64.kinghost.net'       => 'kinghost',
        'mx-vip-01.kinghost.net'              => 'kinghost',
        'mx-vip-02-farm64.kinghost.net'       => 'kinghost',
        'mx-vip-02.kinghost.net'              => 'kinghost',
        'mx-vip-02.xn--kinghost-e0a.net'      => 'kinghost',

        // Hostinger
        'mx1.hostinger.com'                              => 'hostinger',
        'mx1.hostinger.com.conecttelecom.com.br'         => 'hostinger',
        'mx2.hostinger.com'                              => 'hostinger',
        'mx2.hostinger.com.conecttelecom.com.br'         => 'hostinger',

        // BOL
        'mx3.bol.com.br' => 'bol',

        // UOL fisico
        'mx.uol.com.br' => 'uol-fisico',

        // UOL Pro (uhserver)
        'mx.uhserver.com' => 'uol-pro',
        'mx.uhsever.com'  => 'uol-pro',

        // Terra
        'mx.terra.com.br'          => 'terra-fisico',
        'mx.terraempresas.com.br'  => 'terra-juridico',
    ],

    /** Match parcial no hostname MX (ordem importa — mais especifico primeiro). */
    'contains' => [
        'locaweb.com.br'   => 'locaweb',
        'kinghost.net'     => 'kinghost',
        'kinghost-e0a.net' => 'kinghost',
        'xn--kinghost'     => 'kinghost',
        'hostinger.com'    => 'hostinger',
        'hostgator.com'    => 'hostgator',
        'hostgator.com.br' => 'hostgator',
        'gator'            => 'hostgator',
    ],

    /** Atalho por dominio do e-mail quando MX nao responde. */
    'domain' => [
        'bol.com.br'  => 'bol',
        'uol.com.br'  => 'uol-fisico',
        'terra.com.br' => 'terra-fisico',
    ],
];
