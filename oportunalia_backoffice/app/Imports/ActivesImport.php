<?php

namespace App\Imports;

use App\Models\Active;
use App\Models\Auction;
use App\Models\Archive;
use App\Models\ActiveImages;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class ActivesImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if(isset($row['categoria']) && $row['categoria'] !=null && $row['nombre'] !=null){
            $file = fopen('documents/uploads/importactives'.date('d-m-y').'.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'Inicio importacion activos'.PHP_EOL);
            fclose($file);

            // $fechaInicioTmp= $row['fecha_inicio'].' '.Carbon::createFromFormat('H:i:s', $row['hora_inicio'])->format('H:i:s') ;
            // $fechaFinTmp = $row['fecha_fin'].' '.Carbon::createFromFormat('H:i:s', $row['hora_fin'])->format('H:i:s') ;

            // $fechaInicio = Carbon::createFromFormat('d/m/Y H:i:s', $fechaInicioTmp)->format('Y-m-d H:i:s');
            // $fechaFin = Carbon::createFromFormat('d/m/Y H:i:s', $fechaFinTmp)->format('Y-m-d H:i:s');

            switch (strtolower($row['categoria'])) {
                case strtolower('Viviendas'): $active_category_id = 24; break;
                case strtolower('Naves Industriales'): $active_category_id = 25; break;
                case strtolower('Garajes'): $active_category_id = 26; break;
                case strtolower('Trasteros'): $active_category_id = 27; break;
                case strtolower('Locales'): $active_category_id = 28; break;
                case strtolower('Lotes de Mobiliario'): $active_category_id = 29; break;
                case strtolower('Aplicaciones informáticas'): $active_category_id = 30; break;
                case strtolower('Derechos de cobro y créditos'): $active_category_id = 31; break;
                case strtolower('Maquinaria'): $active_category_id = 32; break;
                case strtolower('Mobiliario de Oficina'): $active_category_id = 33; break;
                case strtolower('Solares'): $active_category_id = 34; break;
                case strtolower('Vehículos'): $active_category_id = 35; break;
                case strtolower('Obras de Arte y Antigüedades'): $active_category_id = 38; break;
                case strtolower('Unidad Productiva'): $active_category_id = 39; break;
                case strtolower('Oficinas'): $active_category_id = 40; break;
                case strtolower('Rústica'): $active_category_id = 42; break;
                default: $active_category_id = 43; break;
            }

            switch (strtolower($row['provincia'])) {
                case strtolower('A Coruña'): $province_id = 79; break;
                case strtolower('Álava'): $province_id = 54; break;
                case strtolower('Albacete'): $province_id = 55; break;
                case strtolower('Alicante'): $province_id = 56; break;
                case strtolower('Almería'): $province_id = 57; break;
                case strtolower('Asturias'): $province_id = 58; break;
                case strtolower('Ávila'): $province_id = 59; break;
                case strtolower('Badajoz'): $province_id = 60; break;
                case strtolower('Barcelona'): $province_id = 62; break;
                case strtolower('Burgos'): $province_id = 64; break;
                case strtolower('Cáceres'): $province_id = 69; break;
                case strtolower('Cádiz'): $province_id = 70; break;
                case strtolower('Cantabria'): $province_id = 65; break;
                case strtolower('Castellón'): $province_id = 66; break;
                case strtolower('Ceuta'): $province_id = 4330; break;
                case strtolower('Ciudad Real'): $province_id = 67; break;
                case strtolower('Córdoba'): $province_id = 71; break;
                case strtolower('Cuenca'): $province_id = 68; break;
                case strtolower('Girona'): $province_id = 72; break;
                case strtolower('Granada'): $province_id = 73; break;
                case strtolower('Guadalajara'): $province_id = 74; break;
                case strtolower('Guipúzcoa'): $province_id = 75; break;
                case strtolower('Huelva'): $province_id = 76; break;
                case strtolower('Huesca'): $province_id = 77; break;
                case strtolower('Islas Baleares'): $province_id = 61; break;
                case strtolower('Jaén'): $province_id = 78; break;
                case strtolower('La Rioja'): $province_id = 80; break;
                case strtolower('Las Palmas'): $province_id = 81; break;
                case strtolower('León'): $province_id = 82; break;
                case strtolower('Lleida'): $province_id = 84; break;
                case strtolower('Lugo'): $province_id = 83; break;
                case strtolower('Madrid'): $province_id = 85; break;
                case strtolower('Málaga'): $province_id = 87; break;
                case strtolower('Melilla'): $province_id = 4329; break;
                case strtolower('Murcia'): $province_id = 86; break;
                case strtolower('Navarra'): $province_id = 88; break;
                case strtolower('Ourense'): $province_id = 89; break;
                case strtolower('Palencia'): $province_id = 90; break;
                case strtolower('Pontevedra'): $province_id = 91; break;
                case strtolower('Salamanca'): $province_id = 92; break;
                case strtolower('Santa Cruz de Tenerife'): $province_id = 93; break;
                case strtolower('Segovia'): $province_id = 94; break;
                case strtolower('Sevilla'): $province_id = 95; break;
                case strtolower('Soria'): $province_id = 96; break;
                case strtolower('Tarragona'): $province_id = 97; break;
                case strtolower('Teruel'): $province_id = 98; break;
                case strtolower('Toledo'): $province_id = 99; break;
                case strtolower('Valencia'): $province_id = 100; break;
                case strtolower('Valladolid'): $province_id = 101; break;
                case strtolower('Vizcaya'): $province_id = 63; break;
                case strtolower('Zamora'): $province_id = 102; break;
                case strtolower('Zaragoza'): $province_id = 103; break;
                default: $province_id = 102;
                    break;
            }

            /*if($row['tipo_venta']=='Subasta') {
                $auction_type_id = 1;
            }elseif($row['tipo_venta']=='Venta Directa'){
                $auction_type_id = 2;
            }else{
                $auction_type_id = 3;
            }*/

            Schema::disableForeignKeyConstraints();

            $active = Active::find($row['referencia']);

            if (!$active) {
                $active = new Active();
            }
            $active->id = $row['referencia'];
            $active->name = substr($row['nombre'],0,250);
            $active->active_category_id = $active_category_id;
            $active->address = substr($row['direccion'],0,250);
            $active->city = substr($row['ciudad'],0,250);
            $active->province_id = $province_id;
            $active->active_condition_id = 2;
            $active->refund = 0;
            $active->area = $row['area'];
            $active->save();

            $file = fopen('documents/uploads/importactives'.date('d-m-y').'.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'Activo creado: '.$active->id.'-'.$active->name.PHP_EOL);
            fclose($file);

            /*$auction = new Auction();
            $auction->guid = Str::uuid();
            $auction->title = $row['nombre'];
            $auction->active_id = $active->id;
            $auction->auction_type_id = $auction_type_id;
            $auction->auction_status_id = 2; // Borrador
            $auction->start_date = $fechaInicio;
            $auction->end_date = $fechaFin;
            $auction->appraisal_value = $row['tasacion'];
            $auction->start_price = $row['precio_venta'];
            $auction->minimum_bid = $row['oferta_minima'];
            $auction->deposit = $row['deposito'];
            $auction->commission = $row['comision'];
            if($auction_type_id ==1){
                $auction->bid_price_interval = $row['intervalo_pujas_subasta'];
                $auction->bid_time_interval = $row['intervalo_tiempo_subasta'];
            }

            $auction->description = $row['descripcion'];
            $auction->technical_specifications = $row['especificaciones_tecnicas'];
            $auction->land_registry = $row['documentacion'];
            $auction->conditions = $row['condiciones_especificas'];
            $auction->featured = $row['destacado'];
            $auction->repercusion = $row['repercusion'];
            $auction->auto = $row['referencia'];
            $auction->juzgado = $row['referencia_cliente'];
            $auction->link_rewrite = str_replace(' ', '-', $row['nombre']);

            $auction->save();

            $file = fopen('documents/uploads/importactives'.date('d-m-y').'.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'Venta creada: '.$auction->id.'-'.$auction->title.PHP_EOL);
            fclose($file);
            // import documents
            $path = public_path().'/documents/uploads/';
            $descriptions = array('descripcion_doc', 'especificaciones_doc','documentacion_doc','condiciones_doc');

            $auctionDocuments = array('description_archive_id','description_archive_two_id', 'technical_archive_id', 'technical_archive_two_id',
                        'land_registry_archive_id', 'land_registry_archive_two_id', 'conditions_archive_id','conditions_archive_two_id');
            $x = 0;

            foreach($descriptions as $description){
                for($i=1;$i<=2;$i++){
                    if(isset($row[$description.$i]) && $row[$description.$i] != "" ){
                        if(file_exists($path.$row[$description.$i])){
                            $archiveId = Archive::createFromXls($path.$row[$description.$i]);

                            $auctionField = $auctionDocuments[$x];

                            $auction->$auctionField = $archiveId;
                            $auction->save();

                            if(isset($archiveId)){
                                unlink($path.$row[$description.$i]);
                            }
                            $file = fopen('documents/uploads/importactives'.date('d-m-y').'.log', 'a');
                            fwrite($file, date("d/m/Y H:i:s").'-'.'Documento asignado: '.$archiveId.' a venta: '.$auction->id.'-'.$auction->title.PHP_EOL);
                            fclose($file);
                        }

                        $x++;
                    }else{
                        $auctionField = $auctionDocuments[$x];

                        $x++;
                    }
                }



            }

            for($i=1;$i<=10;$i++){

                if(isset($row['activo_img'.$i]) && $row['activo_img'.$i] != "" ){
                    if(file_exists($path.$row['activo_img'.$i])){
                        $archiveId = Archive::createFromXls($path.$row['activo_img'.$i]);

                        $activeImage = new ActiveImages();
                        $activeImage->active_id = $active->id;
                        $activeImage->archive_id = $archiveId;
                        $activeImage->save();

                        if(isset($archiveId)){
                            unlink($path.$row['activo_img'.$i]);
                        }

                        $file = fopen('documents/uploads/importactives'.date('d-m-y').'.log', 'a');
                        fwrite($file, date("d/m/Y H:i:s").'-'.'Imagen asignada: '.$archiveId.' a activo: '.$active->id.'-'.$active->name.PHP_EOL);
                        fclose($file);
                    }
                }
            }
            $file = fopen('documents/uploads/importactives'.date('d-m-y').'.log', 'a');
            fwrite($file, date("d/m/Y H:i:s").'-'.'**************************************** Fin de importacion ****************************************'.PHP_EOL);
            fclose($file);*/

        }




        Schema::enableForeignKeyConstraints();

    }
}
