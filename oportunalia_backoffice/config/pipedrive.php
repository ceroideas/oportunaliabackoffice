<?php

return [
    'pipeurl'=>'https://api.pipedrive.com/v1/',
    'pitk' => env('PIPE_TOKEN',0),
    'owner_id'=>'20617978',
    'cold'=>'8',
    'warm'=>'7',
    'hot'=>'6',
    'customer'=>'5',
    'bruto'=>1,
    'cualificado'=>2,
    'validado'=>3,
    'ofertado'=>4,
    'perdido'=>5,
    // Fields personalizados
    'origen_negocio'=>'5727248a7a285cfe39b54747b167295fe3157c96',  // Origen de negocio Oportunalia
    'tipo_venta'=>'c41361b07cdedeb9dbc0c0d0c5eea9aabbd082bb',  // Tipo de venta, 24 venta, 22 cesion
    'referencia_activo'=>'57f6df842a30b6c6babddd9a80326a3394cde26f', // Referencia del activo
    'tipo_activo'=>'2779b3346acbcb0b0469641b9b4a49a14511e5e6', // Tipo de activo,
    'provincia_id'=>'ef814a1aaddde881295bd841f9be93fba8e395f3', // Provincia del activo
    'precio_activo'=>'823953a6d449ef677c433808d5acce033346199b', // Precio del activo


];
