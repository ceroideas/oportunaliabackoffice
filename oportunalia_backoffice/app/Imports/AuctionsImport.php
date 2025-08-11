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
use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Str;

use PhpOffice\PhpSpreadsheet\Shared\Date;

class AuctionsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    protected $tipo_venta;
    public function __construct($type)
    {
        $this->tipo_venta = $type;
    }

    public function model(array $row)
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (!Schema::hasColumn('auctions', 'background')) {
                $table->string('background')->nullable();
            }
        });
        if($row['activo'] !=null && $row['nombre'] !=null){

            /*$auction = Auction::where("active_id", $row['activo_id'])
                ->first();*/

            // if(isset($auction) && ($auction->auction_status_id == 3 || $auction->auction_status_id == 4|| $auction->auction_status_id == 6)){

                $activo = Active::where('name',$row['activo'])->first();

                $file = fopen('documents/uploads/importauctions'.date('d-m-y').'.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'Inicio importacion repeticion ventas, activo: '.$activo->id.PHP_EOL);
                fclose($file);

                /*$fechaInicioTmp= $row['fecha_inicio'].' '.Carbon::createFromFormat('H:i:s', $row['hora_inicio'])->format('H:i:s') ;
                $fechaFinTmp = $row['fecha_fin'].' '.Carbon::createFromFormat('H:i:s', $row['hora_fin'])->format('H:i:s') ;*/

                $fechaInicio = $this->transformDate($row['fecha_inicio']).' '.$this->transformTime($row['hora_inicio']);
                $fechaFin = $this->transformDate($row['fecha_fin']).' '.$this->transformTime($row['hora_fin']);

                if ($this->tipo_venta == 'subasta') {
                    $auction_type_id = 1;
                }else if($this->tipo_venta == 'venta directa') {
                    $auction_type_id = 2;
                }else if($this->tipo_venta == 'cesion') {
                    $auction_type_id = 3;
                }else {
                    $auction_type_id = 4;
                }

                /*if (isset($row['tipo_venta'])) {
                    if($row['tipo_venta']=='Subasta') {
                        $auction_type_id = 1;
                    }elseif($row['tipo_venta']=='Venta Directa'){
                        $auction_type_id = 2;
                    }else{
                        $auction_type_id = 3;
                    }
                }else{
                    $auction_type_id = 3;
                }*/

                Schema::disableForeignKeyConstraints();

                if ($activo) {

                    $auctionNew = Auction::where('title',$row['nombre'])->first();

                    if (!$auctionNew) {
                        $auctionNew = new Auction();
                        $auctionNew->guid = Str::uuid();
                        $auctionNew->title = $row['nombre'];
                        $auctionNew->active_id = $activo->id;
                        $auctionNew->auction_type_id = $auction_type_id;
                        $auctionNew->auction_status_id = 2; // Borrador
                        $auctionNew->start_date = $fechaInicio;
                        $auctionNew->end_date = $fechaFin;
                        $auctionNew->appraisal_value = $row['valor_tasacion'];
                        $auctionNew->start_price = $row['precio_venta'];
                        $auctionNew->minimum_bid = $row['oferta_minima'];
                        $auctionNew->deposit = $row['deposito'];
                        $auctionNew->commission = $row['comision'];
                        $auctionNew->background = null;
                        /*if($auction_type_id ==1){
                            $auctionNew->bid_price_interval = $row['intervalo_pujas_subasta'];
                            $auctionNew->bid_time_interval = $row['intervalo_tiempo_subasta'];
                        }*/

                        $auctionNew->description = nl2br($row['descripcion']);
                        $auctionNew->technical_specifications = $row['especificaciones_tecnicas'];
                        $auctionNew->land_registry = $row['documentacion'];
                        $auctionNew->conditions = $row['condiciones_especificas'];
                        $auctionNew->featured = 0;
                        $auctionNew->repercusion = "";//$row['repercusion'];
                        $auctionNew->auto = $row['referencia'];
                        $auctionNew->juzgado = $row['referencia_cliente'];
                        $auctionNew->link_rewrite = str_replace(' ', '-', $row['nombre']);

                        /* Recuperamos documentos anteriores si existen */

                        /*$auctionNew->technical_archive_id = $auction->technical_archive_id;
                        $auctionNew->technical_archive_two_id = $auction->technical_archive_two_id;
                        $auctionNew->land_registry_archive_id = $auction->land_registry_archive_id;
                        $auctionNew->land_registry_archive_two_id = $auction->land_registry_archive_two_id;
                        $auctionNew->description_archive_two_id = $auction->description_archive_two_id;
                        $auctionNew->description_archive_id = $auction->description_archive_id;*/

                        $auctionNew->save();

                        $file = fopen('documents/uploads/importauctions'.date('d-m-y').'.log', 'a');
                        fwrite($file, date("d/m/Y H:i:s").'-'.'Venta creada: '.$auctionNew->id.'-'.$auctionNew->title.PHP_EOL);
                        fclose($file);
                        // import documents
                        /*$path = public_path().'/documents/uploads/';
                        $descriptions = array('condiciones_doc');

                        $auctionDocuments = array('conditions_archive_id','conditions_archive_two_id');
                        $x = 0;

                        foreach($descriptions as $description){
                            for($i=1;$i<=2;$i++){
                                if(isset($row[$description.$i]) && $row[$description.$i] != "" ){
                                    if(file_exists($path.$row[$description.$i])){
                                        $archiveId = Archive::createFromXls($path.$row[$description.$i]);

                                        $auctionField = $auctionDocuments[$x];

                                        $auctionNew->$auctionField = $archiveId;
                                        $auctionNew->save();

                                        if(isset($archiveId)){
                                            unlink($path.$row[$description.$i]);
                                        }
                                        $file = fopen('documents/uploads/importauctions'.date('d-m-y').'.log', 'a');
                                        fwrite($file, date("d/m/Y H:i:s").'-'.'Documento asignado: '.$archiveId.' a venta: '.$auctionNew->id.'-'.$auctionNew->title.PHP_EOL);
                                        fclose($file);
                                    }
                                    $x++;
                                }else{
                                    $auctionField = $auctionDocuments[$x];
                                    $x++;
                                }
                            }

                        }*/
                    }
                }



            /*}else{

                $file = fopen('documents/uploads/importauctions'.date('d-m-y').'.log', 'a');

                if(isset($auction)){

                    fwrite($file, date("d/m/Y H:i:s").'-'.'El activo: '.$row['activo_id'].' tiene el status: '.$auction->auction_status_id.', no se puede exportar'.PHP_EOL);

                }else{
                    fwrite($file, date("d/m/Y H:i:s").'-'.'El activo: '.$row['activo_id'].' no existe'.PHP_EOL);
                }

                fclose($file);
            }*/
                $file = fopen('documents/uploads/importauctions'.date('d-m-y').'.log', 'a');
                fwrite($file, date("d/m/Y H:i:s").'-'.'**************************************** Fin de importacion ****************************************'.PHP_EOL);
                fclose($file);

        }

        Schema::enableForeignKeyConstraints();

    }

    private function transformDate($value)
    {
        try {
            // Convertir el número de serie de Excel a un objeto DateTime
            $date = Date::excelToDateTimeObject($value);
            // Formatear la fecha como 'Y-m-d'
            return Carbon::instance($date)->format('Y-m-d');
        } catch (\Exception $e) {
            // Manejar el error si la conversión falla
            return null;
        }
    }

    private function transformTime($value)
    {
        try {
            // Convertir el número de serie de Excel a un objeto DateTime
            $time = Date::excelToDateTimeObject($value);
            // Formatear la hora como 'H:i:s'
            return Carbon::instance($time)->format('H:i:s');
        } catch (\Exception $e) {
            // Manejar el error si la conversión falla
            return null;
        }
    }
}
