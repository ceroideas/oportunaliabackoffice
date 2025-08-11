<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Geocoder\Query\GeocodeQuery;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\Client;

use App\Models\Auction;
use App\Models\Active;

use App\Services\FotocasaMapper;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class UploadToFotocasa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:fotocasa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload bulk auctions to fotocasa';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->active_id = null;
        $this->description = nl2br("Somos OPORTUNALIA, entidad especializada en la realización de activos procedente de ejecuciones hipotecaria y procedimientos concursales.
         
        INFORMACIÓN DE INTERÉS:
        •              Inmueble procedente de ejecución hipotecaria en fase de cesión de remate
        •              Operación no hipotecable
        •              Visitas no disponibles
        •              Situación posesoria desconocida
        •              Deudas pendientes asociadas al inmueble desconocidas y asumidas por el adquirente (ejemplo IBI, tasas de basura, comunidad de propietarios…)
        •              Impuestos transmisión asumidos por el adquirente
        •              Nuestro equipo legal podrá tramitar el procedimiento judicial hasta la obtención de las llaves (consulta nuestros precios)
         
        ¿Quieres saber más? Regístrate de forma gratuita en nuestra página web para estar al día de las oportunidades de inversión que vamos publicando y poder presentar tu oferta. En la web podrás acceder a la información del activo (certificación catastral, dirección…)
         
        Contacta con nosotros y te explicaremos el procedimiento.
         
        Te acompañaremos hasta la toma de posesión.
         
        Para más información accede a nuestra página web www.oportunalia.com o mándanos un email a nuestro correo info@oportunalia.com o llámanos a nuestro teléfono 91 125 45 30.");
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $auctions = Auction::where('background',1)->limit(4)->get();

        foreach ($auctions as $key => $auction) {
        
        \Log::info('prueba');

        if ($auction) {
            $active = Active::where('id', $auction->active_id)->first();

            $this->active_id = $active->id;

            if (!$active->lat || !$active->lng || $active->lat == 0.0 || $active->lng == 0.0 || $active->lat == 'NaN' || $active->lng == 'NaN') {
                $direccion = urlencode($active->city.', '.$active->address.', '.$active->province.', España');
                $apiKey = "AIzaSyALrXOtjf-VGndljqeKZsA07bJJ8F0XwQw";
                $url = "https://maps.googleapis.com/maps/api/geocode/json?address=$direccion&key=$apiKey";

                $response = file_get_contents($url);
                $data = json_decode($response, true);

                if (!empty($data['results'])) {
                    $lat = $data['results'][0]['geometry']['location']['lat'];
                    $lng = $data['results'][0]['geometry']['location']['lng'];
                    
                    $active->lat = $lat;
                    $active->lng = $lng;
                    $active->save();

                    $this->syncWithFotocasa($auction);

                } else {
                    \Log::info('no se encontró ubicacion '.$auction->id);
                }
            }else{
                \Log::info([$active->lat,$active->lng]);
                $this->syncWithFotocasa($auction);
            }
        }
        }

        return 0;
    }

    public function syncWithFotocasa($auction) {
        try {
           
            $response = Http::get(url("api/auction/{$auction->link_rewrite}"));
            $response_active = Active::where('id', $auction->active_id)->first();
          
            
            /*logger()->debug('Datos del activo:', [
                'active' => $response_active->toArray()
            ]);*/
            
            $typeMapping = FotocasaMapper::mapCategory($response_active->active_category_id);
            if (!$response->successful()) {
                
                $auction->background = 2;
                $auction->save();

                throw new \Exception("Error al obtener auction: ".$response->status()." ".$auction->link_rewrite." | ".$auction->id);
            }
    
            $auctionData = $response->json()['response'];   
    
            $fotocasaData = [
                "ExternalId" => (string) $auctionData['id'],  
                "AgencyReference" => $auctionData['auto'] ?? "REF-".$auctionData['id'],
                'TypeId' => (int) $typeMapping['type'],
                "ContactTypeId" => 3,
                'SubTypeId' => $typeMapping['subtype'] ?? null,
                "PropertyAddress" => [
                    [
                        "Street" => $auctionData['address'] ?? 'Sin calle',
                        "x" => (float) $auctionData['lng'], 
                        "y" => (float) $auctionData['lat'], 
                        "Number" => "",
                        "VisibilityModeId" => 1
                    ]
                ],
                "PropertyFeature" => [
                    [
                        "FeatureId" => 3,
                        "TextValue" => ($auctionData['auction_type_id'] == 3 ? $this->description : $auctionData['description']) ?? 'Sin descripcion'
                    ],
                    [
                        "FeatureId" => 2,
                        "TextValue" => $auctionData['title'] ?? 'Sin titulo'
                    ],
                    [
                        "FeatureId" => 1,
                        "DecimalValue" => (int) $response_active['area'] ?? 0
                    ],[
                        "FeatureId" => 327,
                        "DecimalValue" => 1
                    ],[
                        "FeatureId" => 326,
                        "DecimalValue" => 1
                    ],[
                        "FeatureId" => 325,
                        "DecimalValue" => 1
                    ],[
                        "FeatureId" => 324,
                        "DecimalValue" => 1
                    ],[
                        "FeatureId" => 323,
                        "DecimalValue" => 1
                    ]
                ],
                "PropertyTransaction" => [
                    [
                        "TransactionTypeId" => 1,
                        "Price" => (float) ($auctionData['start_price'] ?? 0),
                        "ShowPrice" => true
                    ]
                ],
                "PropertyContactInfo" => [
                    [  
                        "TypeId" => 1, 
                        "Value" => "info@oportunalia.com" 
                    ],
                    [  
                        "TypeId" => 2, 
                        "Value" => "34911254530"
                    ]
                ],
                "PropertyDocument" => array_map(function ($image) use (&$index) {
                    return [
                        'TypeId' => 1, 
                        'Url' => $image['path'], 
                        'SortingId' => ++$index 
                    ];
                }, $auctionData['images'] ?? [])
            ];

            logger()->debug('Datos del activo Command:', [
                'activo' => $fotocasaData
            ]);

    
            $response = Http::withHeaders([
                'Api-Key' => "G921CBlEVogm16vF5DTWhQt8qtPg65Pac50ud7sdZRVPKqT1FNF8NLg9KOehnhKE",
                'X-Source' => "2af813dd-057a-4995-911a-0b4004ecbdd7",
                'Content-Type' => 'application/json'
            ])->post('https://imports.gw.fotocasa.pro/api/property', $fotocasaData);

            $response->throw(); 

            logger()->debug('Respuesta del activo Command:', [
                'respuesta' => $response->json()
            ]);

            $auction->background = 2;
            $auction->save();
    
            return $response->json();
    
        } catch (\Exception $e) {

            $active = Active::find($this->active_id);
            $active->lat = null;
            $active->lng = null;
            $active->save();

            \Log::info('Borradas coordenadas');
           
            throw new \Exception("Error al sincronizar: " . $e->getMessage());
        }
    }
}
