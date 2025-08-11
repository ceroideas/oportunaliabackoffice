<?php

return [

    '__product' => [

        'article' => 'Artículo',
        'yourBid' => 'Tu puja',
        'yourOffer' => 'Tu oferta',
        'bidImport' => 'Precio de puja',
        'offerImport' => 'Precio de oferta',
        'bidDate' => 'Fecha de la puja',
        'offerDate' => 'Fecha de la oferta',
        'bidType' => 'Tipo de puja',
        'bid_type' => [
            'manual' => 'Manual',
            'auto' => 'Automática',
        ],
        'lastBid' => 'Última puja',
        'lastOffer' => 'Última oferta',
        'winnerBid' => 'Puja ganadora',
        'bids' => 'Nº de pujas',
        'offers' => 'Nº de ofertas',
        'representation' => 'Representación',
        'noRepresentation' => 'No has especificado ningún perfil de representación',
        'startPrice' => 'Precio mínimo',
        'salePrice' => 'Precio de venta',
        'notReached' => 'No se alcanzó el precio mínimo',
        'appraisalValue' => 'Valor de tasación',
        'commission' => 'Porcentaje de comisión (:commission%)',
        'total' => 'Total',
        'fullDate' => ':date a las :time',
        'details' => 'Ver detalles',
    ],

    'layout' => [

        'howTo' => '¿Cómo comprar?',
        'aboutUs' => 'Sobre nosotros',
        'contact' => 'Contacto',
        'anyQuestion' => '¿Tienes alguna pregunta? Puedes contactar con nuestro equipo de atención al cliente llamando al',
        'orWriting' => 'o escribiendo a',
        'phoneSpaced' => '(+34) 911 25 45 30',
        'phone' => '(+34) 911254530',
        'email' => 'info@oportunalia.com',
        'twitter' => '',
        'linkedin' => 'https://www.linkedin.com/company/oportunalia-activos/',
        'facebook' => 'https://www.facebook.com/',
        'instagram' => 'https://www.instagram.com/',
        'companyName'=> 'OPORTUNALIA ACTIVOS, S.L.',
        'address' => 'C/ General Cabrera, 11, 28020 (Madrid).',
        'slogan' => 'Oportunalia es una web de oportunidades, donde podrás encontrar de forma fácil y segura una gran diversidad de productos a precios muy competitivos.',
        'preferences' => 'Gestionar preferencias de correo',
        'disclaimer' => 'Oportunalia no se responsabiliza del envío de correos electrónicos automáticos ni del contenido de estos. Las comunicaciones que el portal envía de
                        forma automática al correo electrónico facilitado por el usuario durante el proceso de pujas son a mero título informativo sin que Oportunalia tenga
                        que responsabilizarse de su envío, recepción ni contenido. Oportunalia no es responsable de los perjuicios causados por la recepción o no recepción
                        de los e-mails informativos remitidos a los usuarios en el proceso de pujas. El usuario será el único responsable de supervisar la página web durante el
                        proceso de pujas a fin de realizar o no sus pujas, según la información publicada en la página web en el apartado de “última puja”.',
        'disclaimerWelcome' => 'Este email ha sido enviado automáticamente, por favor, no responda al mismo.
                            Es para uso exclusivo del destinatario/s, tiene carácter confidencial, y su utilización y divulgación está estrictamente prohibida.
                            Si ha recibido este correo sin ser el destinatario de este, le rogamos proceda a su inmediata eliminación, sin mantener copia ninguna del
                            mismo y, en su caso, de los archivos adjuntos al mensaje.',
        'textPhone' =>'Teléfono de información: ',
        'web'=>'https://www.oportunalia.com',
    ],

    'contact' => [

        'email_subject' => 'Formulario de contacto',
        'firstname' => 'Nombre',
        'lastname' => 'Apellidos',
        'email' => 'Email',
        'phone' => 'Teléfono',
        'subject' => 'Asunto',
        'message' => 'Mensaje',
    ],

    'verify' => [

        'welcome' => '¡BIENVENIDO A OPORTUNALIA!',
        'hello' => 'Hola, :firstname:',
        'thankForJoining' => 'A partir de ahora, podrá ver y pujar por las oportunidades inmobiliarias que siempre ha estado buscando.
        Le acompañaremos durante todo el proceso, informándole de cada uno de los movimientos que tenga la subasta o venta del activo por el que este interesad@.',
        'clickToVerify' => 'Verifique su cuenta y empiece a invertir.',
        'verifyAccount' => 'Verificar cuenta',
        'salutations' => 'Atentamente,',
        'theAdmin' => 'Equipo oportunalia.',
    ],

    'recover' => [
        'title'=>'Cambio de contraseña',
        'subject' => 'Recuperación de contraseña',
        'hello' => 'Hola, :firstname:',
        'accountRecovery' => 'Se ha solicitado correctamente la recuperación de la contraseña de su cuenta.',
        'clickToReset' => 'Si ha sido usted, por favor, pinche en el siguiente boton para cambiarla.',
        'resetPassword' => 'Cambiar contraseña',
        'ignoreThis' => 'Si por el contrario no ha sido usted, ignore este mensaje.',
        'salutations' => 'Atentamente,',
        'theAdmin' => 'Equipo oportunalia.',
    ],

    'representation' => [

        'valid' => [
            'title'=>'Representacion validada',
            'subject' => 'Poderes de representación validados',
            'hello' => 'Hola, :firstname:',
            'confirmation' => 'Le confirmamos que se ha procedido a validar los poderes para que sea representado por: ',
            'representation' => 'Representación',
            'idNumber' => 'NIF / CIF',
            'atYourService' => 'Quedamos a tu disposición para cualquier consulta o aclaración.',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'invalid' => [
            'title'=>'Representacion rechazada',
            'subject' => 'Poderes de representación rechazados',
            'hello' => 'Hola, :firstname:',
            'rejected' => 'Le confirmamos que se ha procedido a rechazar los poderes para que sea representado por: ',
            'representation' => 'Representación',
            'idNumber' => 'NIF / CIF',
            'returned' => 'En breve nos pondremos en contacto con usted para indicarle el motivo del rechazo.',
            'atYourService' => 'Quedamos a tu disposición para cualquier consulta o aclaración.',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],
    ],

    'deposits' => [

        'valid' => [
            'title'=>'Deposito validado',
            'subject' => 'Depósito validado',
            'hello' => 'Hola, :firstname:',
            'confirmation' => 'Le confirmamos que se ha procedido a validar su deposito del activo ',
            'atYourService' => 'Quedamos a su disposición para cualquier consulta o aclaración.',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'invalid' => [
            'title'=>'Deposito rechazado',
            'subject' => 'Depósito inválido',
            'hello' => 'Hola, :firstname:',
            'confirmation' => 'Le indicamos que no se ha podido validar su depósito del activo ',
            'returned' => 'En breve nos pondremos en contacto con usted para indicarle el motivo.',
            'atYourService' => 'Quedamos a su disposición para cualquier consulta o aclaración.',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],
    ],

    'bid' => [

        'success' => [

            'subject' => 'Confirmación de puja',
            'title' => 'Puja registrada correctamente',
            'hello' => 'Hola, :firstname:',
            'bidCorrect' => 'Su puja en <strong>":title"</strong> por una cantidad de ',
            'bidCorrect2'=> 'fue registrada correctamente.',
            'bestBidder' => 'Le mantendremos informado durante todo el proceso, tanto si resulta mejor postor como si su puja es superada.',
            'thanks' => 'Gracias por confiar en Oportunalia. Para consultar esta y otras subastas, :link en su cuenta.',
            'login' => 'inicie sesión',
            'commission' => 'En caso de resultar ganador de esta subasta, Oportunalia aplicará un porcentaje de <strong>comisión del :commission%</strong> sobre este precio como intermediario de la operación.',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'deny' => [

            'subject' => 'Tu puja fue superada',
            'title' => 'Puja superada',
            'hello' => 'Hola, :firstname:',
            'outbidden' => 'Su puja en <strong>":title" ha sido superada</strong>. Podrá realizar una nueva puja entrando en la <strong> web </strong>',
            'notBestBidder' => '<strong>":title"</strong> recibió una nueva puja por valor de <strong>:lastBid €</strong>, por lo que ya no eres el mejor postor en esta subasta.',
            'stillTime' => '¡Aún tienes tiempo para adjudicarte esta subasta! Puedes realizar una nueva puja iniciando sesión en :link.',
            'yourAccount' => 'tu cuenta',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'win' => [

            'subject' => 'Subasta finalizada',
            'title' => 'Subasta finalizada',
            'hello' => 'Hola, :firstname:',
            'won' => 'La subasta <strong>":title"</strong> ha finalizado comprobaremos si usted ha sido el mejor postor.',
            'bestBidder' => 'Has sido el mejor postor de esta subasta',
            'contact' => 'En breve nos pondremos en contacto con usted para informarte sobre cómo continuar el procedimiento.',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],
    ],

    'auctions' => [

        'end-win' => [

            'subject' => 'Subasta finalizada',
            'title' => 'Subasta finalizada',
            'hello' => 'Hola, :firstname:',
            'ended' => 'Esta subasta finalizó el :date a las :time',
            'soldTo' => 'Lo sentimos, <strong>":title"</strong> fue vendido por <strong>:lastBid €</strong> y su ganador fue :lastBidder',
            'lookingElse' => 'Tenemos más subastas disponibles para ti. Haz click :link para ver todas nuestras subastas.',
            'here' => 'aquí',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'end' => [

            'subject' => 'Subasta finalizada',
            'title' => 'Subasta finalizada',
            'hello' => 'Hola, :firstname:',
            'ended' => 'La subasta <strong>":title"</strong> ha finalizado.',
            'noBid' => 'Lo sentimos, su puja no ha sido la mejor postura.',
            'lookingElse' => 'Muchas gracias por confiar en nosotros.',
            'here' => 'aquí',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],
    ],

    'favs' => [

        'start' => [

            'subject' => ' ha comenzado',
            'title' => 'La :venta ha comenzado',
            'hello' => 'Hola, :firstname:',
            'hasStarted' => 'Ha comenzado la :venta de <strong>":title"</strong> en la que tienes interés.',
            'willStart' => 'La :venta finalizará el :date a las :time',
            'lookingElse' => '¿Estás buscando algo diferente? Haz click :link para ver todas nuestras subastas.',
            'here' => 'aquí',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'to_end' => [

            'subject' => ' a punto de finalizar',
            'title' => ' a punto de finalizar',
            'hello' => 'Hola, :firstname:',
            'willEnd' => 'La :venta <strong> :title </strong> finalizará el :date a las :time',
            'aboutEnd' => 'Tenga en cuenta que, si puja durante en los :lastMinutes últimos minutos, la subasta se prolongará :bidTimeInterval segundos más.',
            'lookingElse' => '¿Estás buscando algo diferente? Haz click :link para ver todas nuestras subastas.',
            'here' => 'aquí',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'end-win' => [

            'subject' => 'Subasta finalizada',
            'title' => 'Subasta finalizada',
            'hello' => 'Hola, :firstname:',
            'ended' => 'Esta subasta finalizó el :date a las :time',
            'soldTo' => 'La subasta del activo <strong>":title"</strong> ha finalizado.',
            'lookingElse' => 'Tenemos más subastas disponibles para ti. Haz click :link para ver todas nuestras subastas. few',
            'here' => 'aquí',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'end' => [

            'subject' => ' finalizada',
            'title' => ' finalizada',
            'hello' => 'Hola, :firstname:',
            'withoutBidder' => 'La :venta del activo <strong>":title"</strong> ha finalizado.',
            'withoutOffer'=>'El periodo de ofertas en la :venta <strong>":title"</strong> ha finalizado.',
            'unsoldBid'=>'Lo sentimos, su puja no ha sido la mejor postura.',
            'unsold'=>'En caso de que su oferta resulte aceptada, nos pondremos en contacto con usted para informarle sobre los siguientes pasos a seguir.',
            'ended' => 'Muchas gracias por confiar en nosotros',
            'lookingElse' => 'Tenemos más subastas disponibles para ti. Haz click :link para ver todas nuestras subastas. fe',
            'here' => 'aquí',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],
    ],

    'offers' => [

        'accepted' => [

            'subject' => '¡Enhorabuena, han aceptado tu oferta!',
            'title' => '¡Enhorabuena, han aceptado tu oferta!',
            'hello' => 'Hola, :firstname:',
            'accepted' => 'Tu oferta para ":title" ha sido aceptada',
            'bestOffer' => 'Has sido la mejor oferta de esta venta y has adquirido el activo por un total de <strong>:lastOffer &euro</strong>.',
            'contact' => 'Nos pondremos en contacto contigo en breve para informarte sobre cómo continuar el procedimiento.',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'received' => [

            'subject' => 'Oferta recibida',
            'title' => 'Oferta recibida',
            'hello' => 'Hola, :firstname:',
            'accepted' => 'Su oferta en <strong> :title </strong> por una cantidad de <strong> :import &euro;</strong> fue registrada correctamente.',
            'contact' => 'Tu oferta será revisada y podrá ser aceptada o denegada',
            'salutations' => 'Atentamente,',
            'theAdmin' => 'Equipo oportunalia.',
        ],

        'rejected' => [

            'subject' => 'Oferta rechazada',
            'hello' => 'Hola, :firstname:',
            'confirmation' => 'Le confirmamos que la oferta que has propuesto para',
            'rejected' => 'ha sido rechazada.',
            'atYourService' => 'Quedamos a tu disposición para cualquier consulta o aclaración.',
        ],
    ],

    'direct_sale' => [

        'end' => [

            'subject' => 'Una venta no ha podido ser vendida',
            'hello' => 'Hola, :firstname:',
            'ended' => 'El plazo de venta finalizó el :date a las :time',
            'notSold' => 'La venta ":title" no ha podido ser vendida porque no se ha aceptado ninguna oferta.',
        ],
    ],
    'cesion' => [

        'end' => [

            'subject' => 'Una venta no ha podido ser vendida',
            'hello' => 'Hola, :firstname:',
            'ended' => 'El plazo de venta finalizó el :date a las :time',
            'notSold' => 'La venta ":title" no ha podido ser vendida porque no se ha aceptado ninguna oferta.',
        ],
    ],
];
