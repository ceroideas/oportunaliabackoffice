<!DOCTYPE html>
<html>
    <head>
        <title>Importador repeticion de activos</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

        <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>

        <style>
        .loading {
            z-index: 20;
            position: absolute;
            top: 0;
            left:-5px;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .loading-content {
        position: absolute;
        border: 16px solid #f3f3f3; /* Light grey */
        border-top: 16px solid #e65927; /* Blue */
        border-radius: 50%;
        width: 50px;
        height: 50px;
        top: 50%;
        left:50%;
        animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }


        </style>

    </head>
    <body>

        <div class="container mt-4">

            <h2 class="text-center">Importador repetición de ventas</h2>
            @isset($message)
                <div class="alert alert-success mt-1 mb-1">{{ $message }} puedes descargar el informe en:
                    <a href={{url('documents/uploads/importauctions').date('d-m-y').'.log'}} target="_blank"> {{url('documents/uploads/importauctions').date('d-m-y').'.log'}}</a>
                </div>

            @endisset
            @isset($error)
                <div class="alert alert-danger mt-1 mb-1">{{ $error }}</div>
            @endisset
            <h4>1. Subir fichero de condiciones</h4>
            <form action="{{ url('api/upload-files') }}" class="dropzone" id="my-dropzone" method="post" enctype="multipart/form-data">
                @csrf
            </form>
            <br>
            <br>
            <h4>2. Importar fichero de ventas</h4>
            <form method="POST" enctype="multipart/form-data" id="upload-file-auctions" action="{{ url('api/store-auctions') }}" >
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <input type="file" name="file" placeholder="Subir archivo de activos" id="file">
                        </div>
                    </div>
                    <br>
                    <br>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary" id="submit" onclick="showLoading()">Importar activos</button>
                    </div>
                </div>
            </form>


            <section id="loading">
                <div id="loading-content"></div>
            </section>

        </div>

    </div>
        <script>

            function showLoading() {
                document.querySelector('#loading').classList.add('loading');
                document.querySelector('#loading-content').classList.add('loading-content');
                //setTimeout(hideLoading, 5000);
            }

            function hideLoading() {
                document.querySelector('#loading').classList.remove('loading');
                document.querySelector('#loading-content').classList.remove('loading-content');
            }

            Dropzone.options.myDropzone = {
                dictDefaultMessage: "Suelta los ficheros que se importaran",
                acceptedFiles: 'image/*,application/pdf',
                init: function() {
                    this.on('success', function() {
                        console.log('Archivo subido con éxito.');
                    });
                }
            };
        </script>
    </body>
</html>
