<?php

return [

    'register' => [

        'title' => 'Nuevo usuario - :username',
        'subtitle' => ':firstname :lastname - :email',
    ],

    'document' => [

        'title' => ':firstname :lastname ha subido su documento de identidad',
        'subtitle' => ':document_number',
    ],

    'deposit' => [

        'title' => ':firstname :lastname ha abonado un depósito',
        'subtitle' => ':title - :reference',
    ],

    'representation' => [

        'title' => 'Nuevos poderes de :firstname :lastname',
        'subtitle' => 'Añadidos poderes de :alias',
    ],

    'bid' => [

        'title' => 'Nueva puja de :firstname :lastname',
        'subtitle' => ':amount €',
    ],

    'offer' => [

        'title' => 'Nueva oferta de :firstname :lastname',
        'subtitle' => ':amount €',
    ],

    'auction_end_win' => [

        'title' => 'La subasta finalizó y fue ganada por :firstname :lastname',
        'subtitle' => ':import €',
    ],

    'auction_end' => [

        'title' => 'La subasta finalizó sin ninguna puja',
    ],

    'direct_sale_end_win' => [

        'title' => 'La venta directa finalizó y fue ganada por :firstname :lastname',
        'subtitle' => ':import €',
    ],

    'direct_sale_end' => [

        'title' => 'La venta directa finalizó sin ganador',
    ],

    'cesion_end_win' => [

        'title' => 'La cesión de remate finalizó y fue ganada por :firstname :lastname',
        'subtitle' => ':import €',
    ],

    'cesion_end' => [

        'title' => 'La cesión de remate finalizó sin ganador',
    ],
];
