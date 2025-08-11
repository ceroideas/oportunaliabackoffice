<!DOCTYPE html>
<html>

<head>
    <title>Importador masivo de activos</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">


    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    {{--  <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script> --}}


    <style>
        body {
            padding: 20px 0;
            font-family: Lato, sans-serif;
        }
    </style>

</head>



    <body>

        <div class="container">

            <div class="row">

                <div class="col-lg-8 col-lg-offset-2">

                    <h1>Registro de participantes</a></h1>



                    <form id="contact-form" method="post" action="{{ url('store-participant') }}" role="form">
                        @csrf
                        <div class="messages"></div>

                        <div class="controls">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="form_name">Nombre *</label>
                                        <input id="form_name" type="text" name="name" class="form-control"
                                            placeholder="Introduce tu nombre *" required="required"
                                            data-error="Nombre requerido">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="form_lastname">Apellido *</label>
                                        <input id="form_lastname" type="text" name="surname" class="form-control"
                                            placeholder="Introduce tu apellido *" required="required"
                                            data-error="Apellido requerido">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="form_email">Email *</label>
                                        <input id="form_email" type="email" name="email" class="form-control"
                                            placeholder="Introduce tu email *" required="required"
                                            data-error="Email requerido">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="form_code">Código invitación *</label>
                                        <input id="form_code" type="text" name="code" class="form-control"
                                            placeholder="Introduce tu código de invitación *" required="required"
                                            data-error="Código requerido">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="submit" class="btn btn-success btn-send" value="Registrate">
                                </div>
                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </body>

</html>
