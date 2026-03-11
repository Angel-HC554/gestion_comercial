<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Servicio Arrendado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header1 {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-family: "Times New Roman", Times, serif;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 20px;
            margin-top: 20px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .table1 {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            margin-top: 50px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
        }
        .label {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 35%;
        }
        .value {
            width: 65%;
            text-transform: uppercase;
        }
        .section-title {
            font-size: 14px;
            text-align: center;
            padding: 5px;
            margin-top: 200px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .photo-cell {
            text-align: center;
            vertical-align: top;
            height: 300px; /* Altura fija para las fotos */
            width: 50%;
        }
        .photo-label {
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
            padding: 2px;
        }
        img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            margin-top: 5px;
        }
        .logo img{
            width: 200px;
            height: auto;
        }
        .no-photo {
            color: #999;
            margin-top: 80px;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="logo">
        <img src="{{ $paths['logo'] }}" alt="Logo">
    </div>
    <div class="header1">
        JET VAN CAR RENTAL
    </div>

    <div class="header">
        SOLICITUD DE SERVICIO DE VEHICULO ASIGNADO AL AREA DE ZONA MERIDA
    </div>

    <table class="table1">
        <tr>
            <td class="label">MUNICIPIO Y EDO DE ORIGEN</td>
            <td class="value">{{ $orden->detalleArrendado->mun_estado_origen ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">MUNICIPIO Y EDO PARA EL SERVICIO</td>
            <td class="value">{{ $orden->detalleArrendado->mun_estado_servicio ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">MARCA Y TIPO</td>
            <td class="value">{{ $orden->marca }}</td>
        </tr>
        <tr>
            <td class="label">PLACA</td>
            <td class="value">{{ $orden->placas }}</td>
        </tr>
        <tr>
            <td class="label">No. SERIE</td>
            <td class="value">{{ $orden->detalleArrendado->no_serie ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">KILOMETRAJE</td>
            <td class="value">{{ number_format($orden->kilometraje) }} KM</td>
        </tr>
        <tr>
            <td class="label">TIPO DE SERVICIO QUE SOLICITA</td>
            <td class="value">{{ $orden->detalleArrendado->tipo_servicio ?? '' }}</td>
        </tr>
    </table>

    <div class="header">EVIDENCIA FOTOGRAFICA</div>

    <table class="table">
        <tr>
            <td class="photo-cell">
                <span class="photo-label">FOTO TARJETA DE CIRCULACION</span>
                @if(!empty($paths['circulacion']))
                    <img src="{{ $paths['circulacion'] }}" alt="{{ $paths['circulacion'] }}">
                @else
                    <div class="no-photo">Sin foto</div>
                @endif
            </td>
            <td class="photo-cell">
                <span class="photo-label">FOTO DE ODOMETRO</span>
                @if(!empty($paths['odometro']))
                    <img src="{{ $paths['odometro'] }}" alt="Odómetro">
                @else
                    <div class="no-photo">Sin foto</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">SI SE REQUIEREN LLANTAS, ANEXAR FOTOS</div>

    <div class="logo">
        <img src="{{ $paths['logo'] }}" alt="Logo">
    </div>
    <div class="header1">
        JET VAN CAR RENTAL
    </div>
    <table>
        <tr>
            <td class="photo-cell">
                <span class="photo-label">LLANTA DELANTERA LADO PILOTO</span>
                @if(!empty($paths['del_pil']))
                    <img src="{{ $paths['del_pil'] }}" alt="Delantera Piloto">
                @else
                    <div class="no-photo">N/A</div>
                @endif
            </td>
            <td class="photo-cell">
                <span class="photo-label">LLANTA DELANTERA LADO COPILOTO</span>
                @if(!empty($paths['del_cop']))
                    <img src="{{ $paths['del_cop'] }}" alt="Delantera Copiloto">
                @else
                    <div class="no-photo">N/A</div>
                @endif
            </td>
        </tr>
        <tr>
            <td class="photo-cell">
                <span class="photo-label">LLANTA TRASERA LADO PILOTO</span>
                @if(!empty($paths['tra_pil']))
                    <img src="{{ $paths['tra_pil'] }}" alt="Trasera Piloto">
                @else
                    <div class="no-photo">N/A</div>
                @endif
            </td>
            <td class="photo-cell">
                <span class="photo-label">LLANTA TRASERA LADO COPILOTO</span>
                @if(!empty($paths['tra_cop']))
                    <img src="{{ $paths['tra_cop'] }}" alt="Trasera Copiloto">
                @else
                    <div class="no-photo">N/A</div>
                @endif
            </td>
        </tr>
    </table>

</body>
</html>