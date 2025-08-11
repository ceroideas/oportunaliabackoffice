<?php

return [

    'backoffice' => [

        'users' => 'Usuarios',
        'deposits' => 'Depósitos',
        'representations' => 'Representaciones',
        'actives' => 'Activos',
        'auctions' => 'Subastas',
        'direct_sales' => 'Ventas Directas',
        'blog' => 'Entradas de Blog',
        'bids'=>'Pujas',
        'auction_final_report' => 'Informe final de subasta',
        'participants'=>'Participantes'
    ],

    'users' => [

        'id' => 'ID',
        'username' => 'Nombre de usuario',
        'firstname' => 'Nombre',
        'lastname' => 'Apellidos',
        'birthdate' => 'Fecha de nacimiento',
        'email' => 'Email',
        'phone' => 'Teléfono',
        'address' => 'Dirección',
        'cp' => 'Código postal',
        'city' => 'Ciudad',
        'role' => 'Rol',
        'province' => 'Provincia',
        'country' => 'País',
        'document_number' => 'NIF/CIF',
        'lang' => 'Idioma',
        'status' => 'Estado',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
        'deleted_at' => 'Fecha de eliminación',
        'notification_news' => 'Notificar noticias',
        'notification_auctions' => 'Notificar subastas',
        'notification_favorites' => 'Notificar favoritos',
        'number_login' => 'Nº de logins',
    ],

    'user_status' => [

        'confirmed' => 'Confirmado',
        'not_confirmed' => 'No confirmado',
    ],

    'deposits' => [

        'id' => 'ID',
        'username' => 'Nombre de usuario',
        'firstname' => 'Nombre',
        'lastname' => 'Apellidos',
        'user_id' => 'ID usuario',
        'document_number' => 'NIF/CIF',
        'reference' => 'Referencia de subasta',
        'status' => 'Estado',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
    ],

    'deposit_status' => [

        'valid' => 'Válido',
        'invalid' => 'No válido',
    ],

    'representations' => [

        'id' => 'ID',
        'alias' => 'Alias',
        'firstname' => 'Nombre',
        'lastname' => 'Apellidos',
        'document_number' => 'NIF/CIF',
        'representing' => 'Representado',
        'user_id' => 'ID representado',
        'address' => 'Dirección',
        'cp' => 'Código postal',
        'city' => 'Ciudad',
        'province' => 'Provincia',
        'country' => 'País',
        'type' => 'Tipo',
        'status' => 'Estado',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
    ],

    'representation_status' => [

        'valid' => 'Válido',
        'invalid' => 'No válido',
    ],

    'active_categories' => [

        'id' => 'ID',
        'name' => 'Nombre',
        'description' => 'Descripción',
        'deleted_at' => 'Fecha de eliminación',
    ],

    'actives' => [

        'id' => 'ID',
        'name' => 'Nombre',
        'category' => 'Categoría',
        'category_id' => 'ID categoría',
        'address' => 'Dirección',
        'city' => 'Ciudad',
        'province' => 'Provincia',
        'refund' => 'Reembolso',
        'condition' => 'Condición',
        'area' => 'Superficie',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
    ],

    'representation_status' => [

        'valid' => 'Válido',
        'invalid' => 'No válido',
    ],

    'auctions' => [

        'id' => 'ID',
        'title' => 'Título',
        'active' => 'Activo subastado',
        'active_id' => 'ID activo',
        'status' => 'Estado',
        'start_date' => 'Fecha de inicio',
        'end_date' => 'Fecha de fin',
        'appraisal_value' => 'Tasación (€)',
        'start_price' => 'Precio mínimo (€)',
        'deposit' => 'Depósito (€)',
        'commission' => 'Comisión (%)',
        'bid_price_interval' => 'Precio entre pujas (€)',
        'bid_time_interval' => 'Tiempo de aumentar puja (seg)',
        'favorites' => 'Veces marcado como favorito',
        'deposits' => 'Depósitos completados',
        'bids' => 'Nº de pujas',
        'last_bid' => 'Última puja (€)',
        'last_bidder' => 'Mejor postor',
        'last_bid_date' => 'Fecha de última puja',
        'views' => 'Nº de visitas',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
        'description' => 'Texto de descripción',
        'land_registry' => 'Texto de catastro',
        'technical_specifications' => 'Texto de especificaciones técnicas',
        'conditions' => 'Texto de condiciones',
        'link_rewrite'=>'Enlace',
        'auction_type_id'=>'Tipo',
        'auto'=>'Referencia'
    ],

    'direct_sales' => [

        'id' => 'ID',
        'title' => 'Título',
        'active' => 'Activo vendido',
        'active_id' => 'ID activo',
        'status' => 'Estado',
        'start_date' => 'Fecha de inicio',
        'end_date' => 'Fecha de fin',
        'appraisal_value' => 'Tasación (€)',
        'start_price' => 'Precio de venta (€)',
        'commission' => 'Comisión (%)',
        'favorites' => 'Veces marcado como favorito',
        'offers' => 'Nº de ofertas',
        'best_offer' => 'Mejor oferta (€)',
        'best_offerer' => 'Mejor ofertante',
        'best_offer_date' => 'Fecha de mejor oferta',
        'views' => 'Nº de visitas',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
        'description' => 'Texto de descripción',
        'land_registry' => 'Texto de catastro',
        'technical_specifications' => 'Texto de especificaciones técnicas',
        'conditions' => 'Texto de condiciones',
        'link_rewrite'=>'Enlace',
        'deposit'=>'Deposito',
        'auto'=>'Referencia'
    ],
    'cesions' => [

        'id' => 'ID',
        'title' => 'Título',
        'active' => 'Activo vendido',
        'active_id' => 'ID activo',
        'status' => 'Estado',
        'start_date' => 'Fecha de inicio',
        'end_date' => 'Fecha de fin',
        'appraisal_value' => 'Tasación (€)',
        'start_price' => 'Precio de venta (€)',
        'commission' => 'Comisión (%)',
        'favorites' => 'Veces marcado como favorito',
        'offers' => 'Nº de ofertas',
        'best_offer' => 'Mejor oferta (€)',
        'best_offerer' => 'Mejor ofertante',
        'best_offer_date' => 'Fecha de mejor oferta',
        'views' => 'Nº de visitas',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
        'description' => 'Texto de descripción',
        'land_registry' => 'Texto de catastro',
        'technical_specifications' => 'Texto de especificaciones técnicas',
        'conditions' => 'Texto de condiciones',
        'link_rewrite'=>'Enlace',
        'juzgado'=>'Empresa',
        'auto'=>'Referencia'
    ],

    'credit_assignments' => [

        'id' => 'ID',
        'title' => 'Título',
        'active' => 'Activo vendido',
        'active_id' => 'ID activo',
        'status' => 'Estado',
        'start_date' => 'Fecha de inicio',
        'end_date' => 'Fecha de fin',
        'appraisal_value' => 'Tasación (€)',
        'start_price' => 'Precio de venta (€)',
        'commission' => 'Comisión (%)',
        'favorites' => 'Veces marcado como favorito',
        'offers' => 'Nº de ofertas',
        'best_offer' => 'Mejor oferta (€)',
        'best_offerer' => 'Mejor ofertante',
        'best_offer_date' => 'Fecha de mejor oferta',
        'views' => 'Nº de visitas',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
        'description' => 'Texto de descripción',
        'land_registry' => 'Texto de catastro',
        'technical_specifications' => 'Texto de especificaciones técnicas',
        'conditions' => 'Texto de condiciones',
        'link_rewrite'=>'Enlace',
        'juzgado'=>'Empresa',
        'auto'=>'Referencia'
    ],

    'blog' => [

        'id' => 'ID',
        'title' => 'Título',
        'show_date' => 'Programado para',
        'publish_date' => 'Fecha de publicación',
        'content' => 'Contenido',
        'status' => 'Estado',
        'views' => 'Nº de visitas',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
    ],
    'bids' => [

        'id' => 'ID',
        'user_id' => 'Id usuario',
        'auction_id' => 'Id subasta',
        'representation_id' => 'Id tercero',
        'import' => 'Importe',
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Fecha de actualización',
        'auto' => 'Puja automática',
    ],

    'offers' => [
        'id' => 'ID',
        'user_id' => 'Id usuario',
        'username' => 'Nombre de usuario',
        'firstname' => 'Nombre',
        'lastname' => 'Apellidos',
        'birthdate' => 'Fecha de nacimiento',
        'email' => 'Email',
        'created_at' => 'Fecha de creación',
        'import' => 'Importe',
        'auction_id' => 'Id subasta',
        'active_id' => 'Id activo',
        'title'=>'Titulo',
        'start_date' => 'Fecha de inicio',
        'end_date' => 'Fecha de fin',
        'appraisal_value' => 'Tasación (€)',
        'start_price' => 'Precio de venta (€)',
        'minimum_offer'=>'Oferta mínima',
        'commission' => 'Comisión (%)',
        'venta'=>'Tipo de venta',
        'url'=>'url',
        'juzgado'=>'cliente'
    ],

    'participants' => [
        'id' => 'Campaña',
        'firstname' => 'Nombre',
        'lastname' => 'Apellidos',
        'email' => 'Email',
        'created_at' => 'Fecha de creación',
        'code'=>'Código'
    ],
];
