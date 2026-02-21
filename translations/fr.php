<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

// Traductions françaises — Sales Report Pro (emsalesreport)

global $_MODULE;
$_MODULE = array_merge(isset($_MODULE) ? $_MODULE : [], [

    // ── Module principal ──────────────────────────────────────────────────
    '<{emsalesreport}emsalesreport>sales_report_pro'                     => 'Sales Report Pro',
    '<{emsalesreport}emsalesreport>dashboard_ventas_avanzado'            => 'Tableau de bord des ventes avancé avec KPIs, graphiques, rapports produit/catégorie/client et export. Compatible PS 8 et 9.',
    '<{emsalesreport}emsalesreport>estas_seguro'                         => 'Êtes-vous sûr ? La configuration du module sera supprimée.',
    '<{emsalesreport}emsalesreport>configuracion_guardada'               => 'Configuration enregistrée avec succès.',
    '<{emsalesreport}emsalesreport>configuracion_sales_report'           => 'Configuration de Sales Report Pro',
    '<{emsalesreport}emsalesreport>periodo_por_defecto'                  => 'Période par défaut',
    '<{emsalesreport}emsalesreport>periodo_descripcion'                  => 'Période affichée à l\'ouverture du tableau de bord.',
    '<{emsalesreport}emsalesreport>ultimos_7_dias'                       => '7 derniers jours',
    '<{emsalesreport}emsalesreport>ultimos_14_dias'                      => '14 derniers jours',
    '<{emsalesreport}emsalesreport>ultimos_30_dias'                      => '30 derniers jours',
    '<{emsalesreport}emsalesreport>ultimos_90_dias'                      => '90 derniers jours',
    '<{emsalesreport}emsalesreport>ultimo_ano'                           => 'Dernière année',
    '<{emsalesreport}emsalesreport>estados_pedido_validos'               => 'États de commande valides (IDs séparés par des virgules)',
    '<{emsalesreport}emsalesreport>estados_pedido_desc'                  => 'IDs des états considérés comme "vente valide". Laisser vide pour utiliser les états marqués "payé" dans PrestaShop. Exemple : 2,3,4,5',
    '<{emsalesreport}emsalesreport>mostrar_precios_iva'                  => 'Afficher les prix avec TVA',
    '<{emsalesreport}emsalesreport>precios_iva_desc'                     => 'Lorsqu\'activé, les montants incluent les taxes.',
    '<{emsalesreport}emsalesreport>si'                                   => 'Oui',
    '<{emsalesreport}emsalesreport>no'                                   => 'Non',
    '<{emsalesreport}emsalesreport>elementos_por_pagina'                 => 'Éléments par page',
    '<{emsalesreport}emsalesreport>tipo_grafico_defecto'                 => 'Type de graphique par défaut',
    '<{emsalesreport}emsalesreport>linea'                                => 'Ligne',
    '<{emsalesreport}emsalesreport>barras'                               => 'Barres',
    '<{emsalesreport}emsalesreport>habilitar_comparativa'                => 'Activer la comparaison avec la période précédente',
    '<{emsalesreport}emsalesreport>guardar'                              => 'Enregistrer',

    // ── Filtres ───────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>filtros'                            => 'Filtres',
    '<{emsalesreport}admin_dashboard>desde'                              => 'Du',
    '<{emsalesreport}admin_dashboard>hasta'                              => 'Au',
    '<{emsalesreport}admin_dashboard>periodo_rapido'                     => 'Période rapide',
    '<{emsalesreport}admin_dashboard>7_dias'                             => '7 jours',
    '<{emsalesreport}admin_dashboard>14_dias'                            => '14 jours',
    '<{emsalesreport}admin_dashboard>30_dias'                            => '30 jours',
    '<{emsalesreport}admin_dashboard>90_dias'                            => '90 jours',
    '<{emsalesreport}admin_dashboard>1_ano'                              => '1 an',
    '<{emsalesreport}admin_dashboard>este_mes'                           => 'Ce mois',
    '<{emsalesreport}admin_dashboard>este_ano'                           => 'Cette année',
    '<{emsalesreport}admin_dashboard>aplicar'                            => 'Appliquer',

    // ── KPIs ──────────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>ingresos'                           => 'Chiffre d\'affaires',
    '<{emsalesreport}admin_dashboard>pedidos'                            => 'Commandes',
    '<{emsalesreport}admin_dashboard>ticket_medio'                       => 'Panier moyen',
    '<{emsalesreport}admin_dashboard>margen_bruto'                       => 'Marge brute',
    '<{emsalesreport}admin_dashboard>margen'                             => 'Marge',
    '<{emsalesreport}admin_dashboard>margen_pct'                         => 'Marge %',
    '<{emsalesreport}admin_dashboard>clientes_unicos'                    => 'Clients uniques',
    '<{emsalesreport}admin_dashboard>prod_pedido'                        => 'Art./commande',

    // ── Graphique ─────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>evolucion_ventas'                   => 'Évolution des ventes',
    '<{emsalesreport}admin_dashboard>dia'                                => 'Jour',
    '<{emsalesreport}admin_dashboard>semana'                             => 'Semaine',
    '<{emsalesreport}admin_dashboard>mes'                                => 'Mois',

    // ── Onglets ───────────────────────────────────────────────────────────
    '<{emsalesreport}admin_dashboard>productos'                          => 'Produits',
    '<{emsalesreport}admin_dashboard>categorias'                         => 'Catégories',
    '<{emsalesreport}admin_dashboard>clientes'                           => 'Clients',
    '<{emsalesreport}admin_dashboard>proveedores'                        => 'Fournisseurs',

    // ── Onglet Produits ───────────────────────────────────────────────────
    '<{emsalesreport}tab_products>todas_las_categorias'                  => 'Toutes les catégories',
    '<{emsalesreport}tab_products>total'                                 => 'Total :',
    '<{emsalesreport}tab_products>producto'                              => 'Produit',
    '<{emsalesreport}tab_products>ref'                                   => 'Réf.',
    '<{emsalesreport}tab_products>uds'                                   => 'Qté.',
    '<{emsalesreport}tab_products>ingresos'                              => 'CA',
    '<{emsalesreport}tab_products>coste'                                 => 'Coût',
    '<{emsalesreport}tab_products>margen'                                => 'Marge',
    '<{emsalesreport}tab_products>margen_pct'                            => 'Marge %',
    '<{emsalesreport}tab_products>stock'                                 => 'Stock',
    '<{emsalesreport}tab_products>sin_datos'                             => 'Aucune donnée pour la période sélectionnée.',

    // ── Onglet Catégories ─────────────────────────────────────────────────
    '<{emsalesreport}tab_categories>total_categorias'                    => 'Total catégories :',
    '<{emsalesreport}tab_categories>categoria'                           => 'Catégorie',
    '<{emsalesreport}tab_categories>productos'                           => 'Produits',
    '<{emsalesreport}tab_categories>uds'                                 => 'Qté.',
    '<{emsalesreport}tab_categories>ingresos'                            => 'CA',
    '<{emsalesreport}tab_categories>coste'                               => 'Coût',
    '<{emsalesreport}tab_categories>margen'                              => 'Marge',
    '<{emsalesreport}tab_categories>margen_pct'                          => 'Marge %',
    '<{emsalesreport}tab_categories>distribucion_ingresos'               => 'Répartition du chiffre d\'affaires',
    '<{emsalesreport}tab_categories>sin_datos_chart'                     => 'Aucune donnée',
    '<{emsalesreport}tab_categories>sin_datos'                           => 'Aucune donnée pour la période sélectionnée.',

    // ── Onglet Clients ────────────────────────────────────────────────────
    '<{emsalesreport}tab_customers>total_clientes'                       => 'Total clients :',
    '<{emsalesreport}tab_customers>cliente'                              => 'Client',
    '<{emsalesreport}tab_customers>cliente_vip'                          => 'Client VIP',
    '<{emsalesreport}tab_customers>email'                                => 'E-mail',
    '<{emsalesreport}tab_customers>empresa'                              => 'Société',
    '<{emsalesreport}tab_customers>pedidos'                              => 'Commandes',
    '<{emsalesreport}tab_customers>total'                                => 'Total',
    '<{emsalesreport}tab_customers>ticket_medio'                         => 'Panier moyen',
    '<{emsalesreport}tab_customers>ultimo_pedido'                        => 'Dernière commande',
    '<{emsalesreport}tab_customers>sin_datos'                            => 'Aucune donnée pour la période sélectionnée.',

    // ── Onglet Fournisseurs ───────────────────────────────────────────────
    '<{emsalesreport}tab_suppliers>total_proveedores'                    => 'Total fournisseurs :',
    '<{emsalesreport}tab_suppliers>proveedor'                            => 'Fournisseur',
    '<{emsalesreport}tab_suppliers>productos'                            => 'Produits',
    '<{emsalesreport}tab_suppliers>uds_vendidas'                         => 'Unités vendues',
    '<{emsalesreport}tab_suppliers>ingresos'                             => 'CA',
    '<{emsalesreport}tab_suppliers>coste'                                => 'Coût',
    '<{emsalesreport}tab_suppliers>margen'                               => 'Marge',
    '<{emsalesreport}tab_suppliers>margen_pct'                           => 'Marge %',
    '<{emsalesreport}tab_suppliers>fabricante'                           => 'Fabricant',
    '<{emsalesreport}tab_suppliers>por_fabricante'                       => 'Par fabricant',
    '<{emsalesreport}tab_suppliers>sin_datos'                            => 'Aucune donnée pour la période sélectionnée.',
]);

global $_MODULE;
$_MODULE = [];

// emsalesreport.php - French translations
$_MODULE['<{emsalesreport}prestashop>emsalesreport_dc2fa9d6e236ddb23024e89ae1af3f06'] = 'Sales Report Pro';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_6aa5a4e3b5d2cde08f0beab9ba89b4f6'] = 'Tableau de bord des ventes avancé avec KPIs, graphiques, rapports par produit/catégorie/client et export. Compatible PS 8 et 9.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_configuracion_guardada'] = 'Configuration enregistrée avec succès.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_confirm_uninstall'] = 'Êtes-vous sûr ? La configuration du module sera supprimée.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_dashboard_en_construccion'] = 'Tableau de bord en construction. Le module a été installé avec succès.';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_periodo_defecto'] = 'Période par défaut';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_7_dias'] = '7 derniers jours';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_14_dias'] = '14 derniers jours';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_30_dias'] = '30 derniers jours';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_90_dias'] = '90 derniers jours';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_1_anio'] = 'Dernière année';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_estados_validos'] = 'Statuts de commande valides (IDs séparés par une virgule)';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_precios_con_iva'] = 'Afficher les prix avec TVA';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_elementos_pagina'] = 'Éléments par page';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_tipo_grafico'] = 'Type de graphique par défaut';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_grafico_linea'] = 'Ligne';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_grafico_barras'] = 'Barres';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_comparativa'] = 'Activer la comparaison avec la période précédente';
$_MODULE['<{emsalesreport}prestashop>emsalesreport_guardar'] = 'Enregistrer';
