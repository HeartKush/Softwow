<?php
//header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: application/json; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

@ini_set('display_errors', 'off');

// Se incluye archivo de configuración


include('../config.inc.php');

// Se adiciona la librería Slim y se crea el objeto para trabajar
require '../libs/Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

//Disable debugging
$app->config('debug', false);

/** Rutas para recibir las peticiones desde la Aplicación APP */

$app->group('/app', 'debug', 'authenticate', function () use ($app) {

    $app->post('/hogar', function () use ($app) {

        $data = $app->request->post();

        if (!empty($data["numeroReferencia"]) && !empty($data["numeroFactura"]) && !empty($data["valor"])
            && !empty($data["fechaLimitePago"]) && !empty($data["nombreUsuario"]) && !empty($data["nombreCliente"])
            && !empty($data["tipoDocumento"]) && !empty($data["numeroDocumento"]) && !empty($data["direccionServicio"])
            && !empty($data["fecha"]) && !empty($data["token"])) {

            //fecha, valor, numeroCuenta, numeroReferencia, FechaLimitePago, token

            $fecha = date('YmdHis'); //YYYYMMDDHHMMSS
            $valor = $data["valor"]; //Sin signos
            $numeroCuenta = $data["numeroReferencia"];
            $numeroReferencia = $data["numeroFactura"];
            $fechaLimitePago = validarFechaVencimiento($data["fechaLimitePago"]); //YYYYMMDD
            $token = $data["token"];
            $nombreUsuario = $data["nombreUsuario"];
            $nombreCliente = utf8_decode($data["nombreCliente"]);

            //OrigenPago=4&fecha=201811190233&numeroCuenta=88782820&valor=68000&referencia=88567561&fechalimitepago=20181208&token=1232434&nombreUsuario=leon_ct@hotmail.com&nombreCliente=hola

            $app->redirect(NOMBRE_FRONT . "facturahogar?OrigenPago=4&fecha=" . $fecha .
                "&numeroCuenta=" . $numeroCuenta . "&valor=" . $valor . "&referencia=" . $numeroReferencia . "&fechalimitepago=" . $fechaLimitePago .
                "&token=" . $token . "&nombreUsuario=" . $nombreUsuario . "&nombreCliente=" . $nombreCliente . "&ipOrigen=" . $app->request->getIp() .
                "&tipoDocumentoServicio=" . $data["tipoDocumento"] . "&numeroDocumentoServicio=" . $data["numeroDocumento"] . "&direccionServicio=" . $data["direccionServicio"]);
        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
        }

    })->setName("hogar");

    $app->post('/postpago', function () use ($app) {

        $data = $app->request->post();

        if (!empty($data["numeroCelular"]) && !empty($data["numeroReferencia"]) && !empty($data["numeroFactura"])
            && !empty($data["valor"]) && !empty($data["fechaLimitePago"]) && !empty($data["nombreUsuario"])
            && !empty($data["nombreCliente"]) && !empty($data["tipoDocumento"]) && !empty($data["numeroDocumento"])
            && !empty($data["direccionServicio"]) && !empty($data["fecha"]) && !empty($data["token"])) {
            //fecha, valor, numeroCuenta, numeroReferencia, FechaLimitePago, token

            $fecha = date('YmdHis'); //YYYYMMDDHHMMSS
            $valor = $data["valor"]; //Sin signos
            $numeroCelular = $data["numeroCelular"];
            $numeroFactura = $data["numeroFactura"];
            $numeroReferencia = $data["numeroReferencia"];
            $fechaLimitePago = validarFechaVencimiento($data["fechaLimitePago"]); //YYYYMMDD
            $token = $data["token"];
            $nombreUsuario = $data["nombreUsuario"];
            $nombreCliente = utf8_decode($data["nombreCliente"]);

            //OrigenPago=4&fecha=201811190233&numeroCuenta=88782820&valor=68000&referencia=88567561&fechalimitepago=20181208&token=1232434&nombreUsuario=leon_ct@hotmail.com&nombreCliente=hola

            $app->redirect(NOMBRE_FRONT . "facturamovil?OrigenPago=4&fecha=" . $fecha .
                "&numeroCelular=" . $numeroCelular . "&valor=" . $valor . "&numeroFactura=" . $numeroFactura .
                "&referencia=" . $numeroReferencia . "&fechalimitepago=" . $fechaLimitePago .
                "&token=" . $token . "&nombreUsuario=" . $nombreUsuario . "&nombreCliente=" . $nombreCliente .
                "&ipOrigen=" . $app->request->getIp() . "&tipoDocumentoServicio=" . $data["tipoDocumento"] .
                "&numeroDocumentoServicio=" . $data["numeroDocumento"] . "&direccionServicio=" . $data["direccionServicio"]);
        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
        }

    })->setName("postpago");

    $app->post('/recarga', function () use ($app) {

        $data = $app->request->post();

        if (!empty($data["numeroCuenta"]) && !empty($data["numeroLinea"]) && !empty($data["numeroDocumento"])
            && !empty($data["nombreUsuario"]) && !empty($data["valor"]) && !empty($data["tipoTransaccion"])
            && !empty($data["fecha"]) && !empty($data["recurrente"])) {

            $numeroDocumento = $data["numeroDocumento"];
            $codigoPaquete = "";
            $descripcionCompra = "";
            $nombreUsuario = $data["nombreUsuario"];
            $numeroCuenta = $data["numeroCuenta"];
            $numeroLinea = $data["numeroLinea"];
            $tipoTransaccion = $data["tipoTransaccion"];
            $valorTotal = $data["valor"];
            $fecha = date('YmdHis'); //YYYYMMDDHHMMSS
            $recurrente = $data["recurrente"];
            $diaRecurrenciaCompra = 0;
            $tipoPaquete = "";
            $tipoProducto = "";
            $lineaCelularHE = (isset($data["lineaCelularHE"])) ? $data["lineaCelularHE"] : '';
            $numeroFactura = $numeroLinea . date("dmy") . date("His");

            $DAO = new PagoRecargaDAO();
            $insertCLTiendas = $DAO->insertCLTienda($data, $numeroFactura);

            if ($insertCLTiendas['error'] == false) {
                $app->redirect(NOMBRE_FRONT . "paqueterecarga?NumeroDocumento={$numeroDocumento}"
                    . "&codigoPaquete={$codigoPaquete}&descripcionCompra={$descripcionCompra}&fecha={$fecha}"
                    . "&nombreUsuario={$nombreUsuario}&numeroCuenta={$numeroCuenta}&numeroLinea={$numeroLinea}"
                    . "&recurrenciaCompra={$recurrente}&diaRecurrenciaCompra={$diaRecurrenciaCompra}"
                    . "&OrigenPago=4&tipoPaquete={$tipoPaquete}&tipoProducto={$tipoProducto}&numeroFactura={$numeroFactura}"
                    . "&tipoTransaccion={$tipoTransaccion}&valorCompra={$valorTotal}&LineaCelularHE={$lineaCelularHE}"
                    . "&ipOrigen=" . $_SERVER["REMOTE_ADDR"]);
            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $insertCLTiendas["mensaje"]);
            }
        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
        }

    })->setName("recarga");

    $app->post('/paquete', function () use ($app) {

        $data = $app->request->post();

        if (!empty($data["numeroCuenta"]) && !empty($data["numeroLinea"]) && !empty($data["numeroDocumento"])
            && !empty($data["nombreUsuario"]) && !empty($data["valor"]) && !empty($data["tipoTransaccion"])
            && !empty($data["codigoPaquete"]) && !empty($data["descripcionCompra"]) && !empty($data["tipoPaquete"])
            && !empty($data["tipoProducto"]) && !empty($data["fecha"]) && !empty($data["recurrente"])) {

            $numeroDocumento = $data["numeroDocumento"];
            $codigoPaquete = $data["codigoPaquete"];
            $descripcionCompra = $data["descripcionCompra"];
            $nombreUsuario = $data["nombreUsuario"];
            $numeroCuenta = $data["numeroCuenta"];
            $numeroLinea = $data["numeroLinea"];
            $tipoTransaccion = $data["tipoTransaccion"];
            $valorTotal = $data["valor"];
            $fecha = date('YmdHis'); //YYYYMMDDHHMMSS
            $recurrente = $data["recurrente"];
            $diaRecurrenciaCompra = 0;
            $tipoPaquete = $data["tipoPaquete"];
            $tipoProducto = $data["tipoProducto"];
            $lineaCelularHE = (isset($data["lineaCelularHE"])) ? $data["lineaCelularHE"] : '';
            $numeroFactura = $numeroLinea . date("dmy") . date("His");

            $logicaCompraPaquete = new LogicaPagoPaquete();
            $responseImpoconsumo = $logicaCompraPaquete->comprarPaquete($data);
            $responseImpoconsumo["error"] = false;

            if ($responseImpoconsumo["error"] == false) {
                $insertCLTienda = $logicaCompraPaquete->insercionCLTiendas($data, $numeroFactura);
            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . utf8_encode($responseImpoconsumo["mensaje"]));
            }

            if (isset($insertCLTienda['error']) && $insertCLTienda['error'] == false) {

                $app->redirect(NOMBRE_FRONT . "paqueterecarga?NumeroDocumento=" . $numeroDocumento .
                    "&codigoPaquete=" . $codigoPaquete . "&descripcionCompra=" . utf8_decode($descripcionCompra) . "&fecha=" . $fecha .
                    "&nombreUsuario=" . $nombreUsuario . "&numeroCuenta=" . $numeroCuenta . "&numeroLinea=" . $numeroLinea .
                    "&recurrenciaCompra=" . $recurrente . "&diaRecurrenciaCompra=" . $diaRecurrenciaCompra .
                    "&OrigenPago=4&tipoPaquete=" . $tipoPaquete . "&tipoProducto=" . $tipoProducto .
                    "&tipoTransaccion=" . $tipoTransaccion . "&valorCompra=" . $valorTotal . "&numeroFactura=" . $numeroFactura .
                    "&LineaCelularHE=" . $lineaCelularHE . "&ipOrigen=" . $_SERVER["REMOTE_ADDR"]);
            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . utf8_encode($insertCLTienda["mensaje"]));
            }
        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS . " " . json_encode($data));
        }

    })->setName("paquete");

    $app->post('/recargaRecurrente', function () use ($app) {

        $data = $app->request->post();

        if (!empty($data["numeroCuenta"]) && !empty($data["numeroLinea"]) && !empty($data["numeroDocumento"])
            && !empty($data["nombreUsuario"]) && !empty($data["valor"]) && !empty($data["tipoTransaccion"])
            && !empty($data["diaRecurrenciaCompra"]) && !empty($data["fecha"]) && !empty($data["recurrente"]) ) {

            $numeroDocumento = $data["numeroDocumento"];
            $nombreUsuario = $data["nombreUsuario"];
            $numeroCuenta = $data["numeroCuenta"];
            $numeroLinea = $data["numeroLinea"];
            $tipoTransaccion = $data["tipoTransaccion"];
            $valorTotal = $data["valor"];
            $fecha = date('YmdHis'); //YYYYMMDDHHMMSS
            $recurrente = $data["recurrente"];
            $diaRecurrenciaCompra = $data["diaRecurrenciaCompra"];
            $codigoPaquete = "";
            $descripcionCompra = "";
            $tipoPaquete = "";
            $tipoProducto = "";
            $numeroFactura = $numeroLinea . date("dmy") . date("His");

            $logicaPagoRecargaRecurrente = new LogicaPagoRecargaRecurrente();
            $infoCliente = $logicaPagoRecargaRecurrente->validarInformacionCliente($numeroLinea, $numeroDocumento);

            if ($infoCliente['error'] == false) {
                $DAO = new PagoRecargaRecurrenteDAO();
                $insertCLTienda = $DAO->insertCLTienda($data, $numeroFactura);
            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . utf8_encode($infoCliente["mensaje"]));
            }

            if (isset($insertCLTienda['error']) && $insertCLTienda['error'] == false) {
                $app->redirect(NOMBRE_FRONT . "paqueterecarga?NumeroDocumento=" . $numeroDocumento
                    . "&codigoPaquete=" . $codigoPaquete . "&descripcionCompra=" . utf8_decode($descripcionCompra) . "&fecha=" . $fecha
                    . "&nombreUsuario=" . $nombreUsuario . "&numeroCuenta=" . $numeroCuenta . "&numeroLinea=" . $numeroLinea
                    . "&recurrenciaCompra=" . $recurrente . "&diaRecurrenciaCompra=" . $diaRecurrenciaCompra
                    . "&OrigenPago=4&tipoPaquete=" . $tipoPaquete . "&tipoProducto=" . $tipoProducto
                    . "&tipoTransaccion=" . $tipoTransaccion . "&valorCompra=" . $valorTotal . "&numeroFactura=" . $numeroFactura
                    . "&ipOrigen=" . $_SERVER["REMOTE_ADDR"]);
            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $insertCLTienda["mensaje"]);
            }
        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
        }

    })->setName("recargaRecurrente");

    $app->post('/paqueteRecurrente', function () use ($app) {

        $data = $app->request->post();

        if (!empty($data["numeroCuenta"]) && !empty($data["numeroLinea"]) && !empty($data["numeroDocumento"])
            && !empty($data["nombreUsuario"]) && !empty($data["valor"]) && !empty($data["tipoTransaccion"])
            && !empty($data["codigoPaquete"]) && !empty($data["descripcionCompra"]) && !empty($data["tipoPaquete"])
            && !empty($data["tipoProducto"]) && !empty($data["diaRecurrenciaCompra"]) && !empty($data["fecha"])
            && !empty($data["recurrente"])) {

            $numeroDocumento = $data["numeroDocumento"];
            $codigoPaquete = $data["codigoPaquete"];
            $descripcionCompra = $data["descripcionCompra"];
            $nombreUsuario = $data["nombreUsuario"];
            $numeroCuenta = $data["numeroCuenta"];
            $numeroLinea = $data["numeroLinea"];
            $tipoTransaccion = $data["tipoTransaccion"];
            $valorTotal = $data["valor"];
            $fecha = date('YmdHis'); //YYYYMMDDHHMMSS
            $recurrente = $data["recurrente"];
            $diaRecurrenciaCompra = $data["diaRecurrenciaCompra"];
            $tipoPaquete = $data["tipoPaquete"];
            $tipoProducto = $data["tipoProducto"];
            $numeroFactura = $numeroLinea . date("dmy") . date("His");

            $logicaCompraPaqueteRecurrente = new LogicaPagoPaqueteRecurrente();

            $infoCliente = $logicaCompraPaqueteRecurrente->validarInformacionCliente($numeroLinea, $numeroDocumento);
            $responseImpoconsumo = $logicaCompraPaqueteRecurrente->comprarPaquete($data);

            if ($infoCliente ["error"] == false) {
                if ($responseImpoconsumo["error"] == false) {
                    $insertCLTienda = $logicaCompraPaqueteRecurrente->insercionCLTiendas($data, $numeroFactura);
                } else {
                    $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . utf8_encode($responseImpoconsumo["mensaje"]));
                }
            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $infoCliente["mensaje"]);
            }


            if (isset($insertCLTienda['error']) && $insertCLTienda['error'] == false) {

                $app->redirect(NOMBRE_FRONT . "paqueterecarga?NumeroDocumento=" . $numeroDocumento
                    . "&codigoPaquete=" . $codigoPaquete . "&descripcionCompra=" . utf8_decode($descripcionCompra) . "&fecha=" . $fecha
                    . "&nombreUsuario=" . $nombreUsuario . "&numeroCuenta=" . $numeroCuenta . "&numeroLinea=" . $numeroLinea
                    . "&recurrenciaCompra=" . $recurrente . "&diaRecurrenciaCompra=" . $diaRecurrenciaCompra
                    . "&OrigenPago=4&tipoPaquete=" . $tipoPaquete . "&tipoProducto=" . $tipoProducto
                    . "&tipoTransaccion=" . $tipoTransaccion . "&valorCompra=" . $valorTotal . "&numeroFactura=" . $numeroFactura
                    . "&ipOrigen=" . $_SERVER["REMOTE_ADDR"]);
            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $insertCLTienda["mensaje"]);
            }
        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
        }

    })->setName("paqueteRecurrente");

   $app->post('/paqueteRoaming', function () use ($app) {

        $data = json_decode($app->request->getBody(),true);


        if (!empty($data['data'][0]["idMiddleLayer"]) && !empty($data['data'][0]["msisdn"]) && !empty($data['data'][0]["imsi"])
            && !empty($data['data'][0]["packageCode"]) && !empty($data['data'][0]["packageName"]) && !empty($data['data'][0]["packageValue"])
            && (!empty($data['data'][0]["tokeNumero"]) || (!empty($data['data'][0]["numeroFactura"]) && !empty($data['data'][0]["nombreUsuario"])))
            && !empty($data['data'][0]["startDate"]) && !empty($data['data'][0]["endDate"])
            && !empty($data['data'][0]["idAccount"]) && !empty($data["fecha"])) {

                if (!empty($data['data'][0]["tokeNumero"]) && (!empty($data['data'][0]["numeroFactura"]) || !empty($data['data'][0]["nombreUsuario"]))){

                    $response['error'] = true;
                    $response['mensaje'] = 'Inconsistencia de datos en el medio de pago';

                }
                else
                {
                    $tokenValidacion = validarTokenSeguridad($data['fecha']);
                    $tokenValidacion['error'] = false;
                    if($tokenValidacion['error'] == false) {
                        $idMiddleLayer = $data['data'][0]["idMiddleLayer"];
                        $msisdn = $data['data'][0]["msisdn"];
                        $imsi = $data['data'][0]["imsi"];
                        $packageCode = $data['data'][0]["packageCode"];
                        $packageName = $data['data'][0]["packageName"];
                        $packageValue = $data['data'][0]["packageValue"];
                        $paymentType = 1; 
                        $numeroFactura = '';
                        $nombreUsuario = '';
                        $toke_numero = '';
                        if (!empty($data['data'][0]["tokeNumero"])){
                            $tokeNumero = str_replace('TK_', '', $data['data'][0]["tokeNumero"]);
                            $paymentType = 1; 
                            $numeroFactura = '';
                            $nombreUsuario = '';
                        
                        }
                        else if ((!empty($data['data'][0]["numeroFactura"]) || !empty($data['data'][0]["nombreUsuario"])))
                        {
                            $numeroFactura = $data['data'][0]["numeroFactura"];
                            $nombreUsuario = $data['data'][0]["nombreUsuario"];
                            $paymentType = 2; 
                            $toke_numero = ''; 
                        }                    
                        $startDate = date("Y-m-d", strtotime($data['data'][0]["startDate"]));
                        $fecha = date("Y-m-d H:i:s", strtotime($data["fecha"])); //YYYYMMDDHHMMSS
                        $endDate = date("Y-m-d", strtotime($data['data'][0]["endDate"]));
                        $idAccount = $data['data'][0]["idAccount"];
                      
                        $pagoPaqueteRoaming = new PagoPaqueteRoaming($idMiddleLayer, $msisdn, $imsi, $packageCode, $packageName,
                            $packageValue, $tokeNumero, $startDate, $fecha, $endDate, $idAccount, $numeroFactura, $nombreUsuario, $paymentType);


                        $logicaCompraPaqueteRoaming = new LogicaPagoPaqueteRoaming();

                        $validateIdMiddleLayer = $logicaCompraPaqueteRoaming->validarIdMiddleLayer($idMiddleLayer);

                        if ($validateIdMiddleLayer['error'] == false) {

                            $registroPaquete = $logicaCompraPaqueteRoaming->insertarPagoPaqueteRoaming($pagoPaqueteRoaming);

                            $response['error'] = $registroPaquete['error'];
                            $response['mensaje'] = $registroPaquete['mensaje'];
                        } else {
                            $response = $validateIdMiddleLayer;
                        }
                    }else{
                        $response = $tokenValidacion;
                    }
            }
        } else {
            $response['error'] = true;
            $response['mensaje'] = 'Parámetros vacios';
        }

        echoResponse(200,$response);

    })->setName("paqueteRoaming");

});

/**
 * Rutas para recibir las peticiones desde el Portal Cautivo
 */

$app->group('/portalCautivo', function () use ($app) {

    $app->post('/hogar', function () use ($app) {

        # Se obtienen los datos de la petición
        $data = $app->request->post();

        # Se validan los campos que son obligatorios
        if (!empty($data["numeroCuenta"]) && !empty($data["token"])) {

            //fecha, valor, numeroCuenta, numeroReferencia, FechaLimitePago, token

            $fecha = date('YmdHis'); //YYYYMMDDHHMMSS
            $numeroCuenta = $data["numeroCuenta"];
            $token = $data["token"];

            $logicaPagoPortalCautivo = new LogicaPagoPortalCautivo();
            $infoFactura = $logicaPagoPortalCautivo->consultarFacturaHogar($numeroCuenta);

            if ($infoFactura["error"] == false) {
                //OrigenPago=1&fecha=201811190233&numeroCuenta=88782820&valor=68000&referencia=88567561&fechalimitepago=20181208&token=1232434&nombreUsuario=leon_ct@hotmail.com&nombreCliente=hola

                $app->redirect(NOMBRE_FRONT . "redireccionamientopeticion?OrigenPago=1&fecha={$fecha}"
                    ."&numeroCuenta={$numeroCuenta}&valor={$infoFactura["valor"]}&referencia={$infoFactura["numeroFactura"]}"
                    ."&fechalimitepago=".validarFechaVencimiento($infoFactura["fechaLimite"])."&token={$token}&nombreUsuario= "
                    ."&nombreCliente={$infoFactura["nombreCliente"]}&ipOrigen={$_SERVER["REMOTE_ADDR"]}&tipoTrans=3");
            } elseif ($infoFactura["error"] == true) {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $infoFactura["mensaje"]);
            }
        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
        }
    })->setName('hogarPortalCautivo');
});

/** Rutas para manejar peticiones internas del front-end */

/**
 * Grupo destinado a recibir las peticiones referentes a Tarjetas Tokenizadas
 */

$app->group('/tarjetasTokenizadas', function () use ($app) {

    $app->get('/', function () use ($app) {

        $data = $app->request->get();

        if (isset($data['nombreUsuario'])) {

            $nombreUsuario = $data['nombreUsuario'];

            # Se valida si deben ser enviadas las tarjetas de franquicia Codensa
            $condicionCodensa = $app->request->get("codensa");

            if (isset($condicionCodensa)) {
                if (empty($condicionCodensa))
                    $condicionCodensa = false;
            } else {
                $condicionCodensa = false;
            }

            $tarjetasDAO = new TarjetaTokenizadaDAO();
            $response = $tarjetasDAO->searchTarjetaByEmail($nombreUsuario, $condicionCodensa);
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    });

    $app->post('/', function () use ($app) {
        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (!empty($data["IDTransaccion"]) && !empty($data["nombreTarjetaHabiente"]) && !empty($data["telefonoTarjeta"])
            && !empty($data["tipoDocumentoTarjeta"]) && !empty($data["numeroDocumentoTarjeta"])
            && !empty($data["numeroTarjeta"]) && !empty($data["mesVencimientoTarjeta"]) && !empty($data["anoVencimientoTarjeta"])
            && !empty($data["franquiciaTarjeta"]) && !empty($data["nombreUsuario"]) && !empty($data["idObjeto"])
            && isset($data["paramSetAccount"])) {

            $data["payerID"] = $data["nombreUsuario"] . "_" . $data["franquiciaTarjeta"] . "_" . substr($data['numeroTarjeta'], -4);

            $tarjetaTokenizada = new TarjetaTokenizada(2, $data["nombreUsuario"],
                $data["tipoDocumentoTarjeta"], $data["numeroDocumentoTarjeta"]);

            $response = $tarjetaTokenizada->tokenizarTarjeta($data);
        } else {
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });
});

/**
 * Grupo destinado a recibir las peticiones referentes a Domiciliacion
 */

$app->group('/domiciliacion', function () use ($app) {

    $app->post('/', function () use ($app) {

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (!empty($data["IDTransaccion"]) && !empty($data["nombreTarjetaHabiente"]) && !empty($data["telefonoTarjeta"])
            && !empty($data["tipoDocumentoTarjeta"]) && !empty($data["numeroDocumentoTarjeta"])
            && !empty($data["numeroTarjeta"]) && !empty($data["mesVencimientoTarjeta"]) && !empty($data["anoVencimientoTarjeta"])
            && !empty($data["franquiciaTarjeta"]) && !empty($data["nombreUsuario"]) && !empty($data["tipoDocumentoServicio"])
            && !empty($data["numeroDocumentoServicio"])) {

            $tarjetaTokenizada = new TarjetaTokenizada(2, $data["nombreUsuario"],
                $data["tipoDocumentoTarjeta"], $data["numeroDocumentoTarjeta"]);

            $pago = new Pago("", "", "", "", "",
                $tarjetaTokenizada, "", "", "", "4", "", "",
                "", "", "");
            $pago->setIDTransaccion($data["IDTransaccion"]);

            $response = $pago->domiciliarPago($data["tipoDocumentoServicio"], $data["numeroDocumentoServicio"], $data["telefonoTarjeta"], $data);
        } else {
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });
});

/**
 * Servicio que expone los Bancos disponibles para pagar a través de PSE
 */

$app->group('/bancos', function () use ($app) {

    $app->get('/', function () use ($app) {

        $bancosDAO = new BancoDAO();
        $response = $bancosDAO->searchBancos();

        echoResponse(200, $response);
    });
});

/**
 * Grupo destinado a recibir las peticiones referentes a Medios de Pago
 */

$app->group('/medioPago', function () use ($app) {

    $app->get('/payUTeFia', function () use ($app) {

        // idObjeto, valorTotal, nombreUsuario
        $data = $app->request->get();

        if (isset($data["idObjeto"]) && isset($data["nombreUsuario"]) && isset($data["valorTotal"])) {
            $data["nombreUsuario"] = strtolower($data["nombreUsuario"]);
            $data["formaPago"] = 9;#Se configura 9 porque es el ID de este medio de pago

            $payUTeFia = new PayUTeFia($data["formaPago"], $data["nombreUsuario"], "", "", "", "", "");

            $validacion = $payUTeFia->validarCliente($data);

            $response["error"] = $validacion["error"];
			$response["apto"] = $validacion["apto"];
            $response["mensaje"] = $validacion["mensaje"];
            if ($response["apto"] == true)
                $response["signature"] = $validacion["respuesta"]["paymentMethod"]["paymentMethodAttributes"]["signature"];
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }
			
        echoResponse(200, $response);
    });

    $app->get('/cargoFactura', function () use ($app) {

        // idObjeto, valorTotal, nombreUsuario
        $data = $app->request->get();


        if (isset($data["idObjeto"]) && isset($data["nombreUsuario"])) {
            $data["nombreUsuario"] = strtolower($data["nombreUsuario"]);
            $data["formaPago"] = 3;#Se configura 3 porque es el ID de este medio de pago

            $cargoFactura = new CargoFactura($data["formaPago"], $data["nombreUsuario"], "", "", "", "");

            $consulta = $cargoFactura->consultarInformacionCliente();

            $response["error"] = $consulta["error"];
            $response["mensaje"] = $consulta["mensaje"];

            if ($response["error"] == false) {
                $response["informacionCliente"] = $consulta["informacionCliente"];
            }

        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    });

});

/**
 * Grupo destinado a recibir las peticiones referentes a los pagos en CL_PAGOSCLARO
 */

$app->group('/pagos', function () use ($app) {

    $app->get('/hogar', function () use ($app) {

        $data = $app->request->get();

        if (isset($data["id"])) {
            $id = $data["id"];

            $pagosDAO = new PagoHogarDAO();

            $response["error"] = false;
            $response["pago"] = $pagosDAO->searchPagoById($id);
        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
        }

        echoResponse(200, $response);
    });

    $app->post('/hogar', function () use ($app) {

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (!empty($data["numeroCuenta"]) && !empty($data["numeroFactura"]) && !empty($data["fechaLimitePago"]) && !empty($data["nombreCliente"])
            && !empty($data["nombreUsuario"]) && !empty($data["formaPago"]) && !empty($data["valorTotal"])
            && !empty($data["subtotal"]) && !empty($data["origenPago"]) && !empty($data["tipoTrans"])
            && !empty($data["direccionServicio"]) && !empty($data["numeroIntentos"]) && !empty($data["fechaLimitePago"])
            && !empty($data["fechaInicio"])) {

            $numeroFactura = $data["numeroFactura"];

            $pagosDAO = new PagoHogarDAO();

            $existePagoRealizado = $pagosDAO->searchEstadoPagoByFactura($numeroFactura);

            if (is_bool($existePagoRealizado)) {

                $numeroCuenta = $data["numeroCuenta"];
                $fechaLimitePago = $data["fechaLimitePago"];
                $nombreCliente = $data["nombreCliente"];
                $nombreUsuario = $data["nombreUsuario"];
                $formaPago = substr($data["formaPago"], 0, 1);
                $valorTotal = $data["valorTotal"];
                $subTotal = $data["subtotal"];
                $iva = 0;//$app->request->post("iva");
                $origenPago = $data["origenPago"];
                $tipoTrans = $data["tipoTrans"];
                $estadoSRV = -1;//$app->request->post("estadoSRV");
                $fechaVencimiento = date("Y-m-d", strtotime($data["fechaLimitePago"]));
                $fechaInicio = date("Y-m-d H:i:s", strtotime($data["fechaInicio"]));
                $numeroIntentos = $data["numeroIntentos"];

                $metodoPago = new TarjetaCredito("", "", "",
                    "", "", "", "", "", "", "",
                    "", $formaPago, $nombreUsuario);

                $pagoHogar = new PagoHogar($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente, $nombreUsuario,
                    $metodoPago, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV, $fechaVencimiento,
                    $fechaInicio, $numeroIntentos);
                $pagoHogar->setDirrecion($data["direccionServicio"]);

                # Se extrae el Toke Numero enviado en el parámetro formaPago, en el caso que sea pago con tarjeta tokenizada
                if ($formaPago == 5) {
                    $pagoHogar->setTokeNumero(str_replace("5-TK_", "", $data["formaPago"]));
                }

                $insertClPagos = $pagosDAO->insertPago($pagoHogar);

                if (isset($insertClPagos["ID"])) {
                    $response["error"] = false;
                    $response["id"] = $insertClPagos["ID"];
                } else {
                    $response["error"] = true;
                    $response["mensaje"] = $insertClPagos["mensaje"];
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePagoRealizado["estado"];
            }
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });

    $app->post('/postpago', function () use ($app) {

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (isset($data["numeroFactura"]) && isset($data["numeroCelular"]) && isset($data["numeroReferencia"])
            && isset($data["fechaLimitePago"]) && isset($data["nombreCliente"]) && isset($data["nombreUsuario"])
            && isset($data["formaPago"]) && isset($data["valorTotal"]) && isset($data["subtotal"])
            && isset($data["origenPago"]) && isset($data["tipoTrans"]) && isset($data["fechaLimitePago"])
            && isset($data["fechaInicio"]) && !empty($data["direccionServicio"]) && isset($data["numeroIntentos"])) {

            $numeroFactura = $data["numeroFactura"];

            $pagosDAO = new PagoMovilDAO();

            $existePagoRealizado = $pagosDAO->searchEstadoPagoByFactura($numeroFactura);

            if (is_bool($existePagoRealizado)) {

                $numeroCuenta = $data["numeroCelular"];
                $numeroReferencia = $data["numeroReferencia"];
                $fechaLimitePago = $data["fechaLimitePago"];
                $nombreCliente = utf8_decode($data["nombreCliente"]);
                $nombreUsuario = $data["nombreUsuario"];
                $formaPago = substr($data["formaPago"], 0, 1);
                $valorTotal = $data["valorTotal"];
                $subTotal = $data["subtotal"];
                $iva = 0;//$app->request->post("iva");
                $origenPago = $data["origenPago"];
                $tipoTrans = $data["tipoTrans"];
                $estadoSRV = -1;//$app->request->post("estadoSRV");
                $fechaVencimiento = date("Y-m-d", strtotime($data["fechaLimitePago"]));
                $fechaInicio = date("Y-m-d H:i:s", strtotime($data["fechaInicio"]));
                $numeroIntentos = $data["numeroIntentos"];

                $metodoPago = new TarjetaCredito("", "", "",
                    "", "", "", "", "", "", "",
                    "", $formaPago, $nombreUsuario);

                $pagoPostpago = new PagoPostpago($numeroCuenta, $numeroFactura, $numeroReferencia, $fechaLimitePago, $nombreCliente, $nombreUsuario,
                    $metodoPago, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV, $fechaVencimiento,
                    $fechaInicio, $numeroIntentos);
                $pagoPostpago->setDirrecion($data["direccionServicio"]);

                # Se extrae el Toke Numero enviado en el parámetro formaPago, en el caso que sea pago con tarjeta tokenizada
                if ($formaPago == 5) {
                    $pagoPostpago->setTokeNumero(str_replace("5-TK_", "", $data["formaPago"]));
                }

                $insertClPagos = $pagosDAO->insertPago($pagoPostpago);

                if (isset($insertClPagos["ID"])) {
                    $response["error"] = false;
                    $response["id"] = $insertClPagos["ID"];
                } else {
                    $response["error"] = true;
                    $response["mensaje"] = $insertClPagos["mensaje"];
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePagoRealizado["estado"];
            }
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }
        echoResponse(200, $response);
    });

    $app->post('/recarga', function () use ($app) {

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (isset($data["numeroFactura"]) && isset($data["numeroDocumento"]) && isset($data["numeroCuenta"])
            && isset($data["numeroLinea"]) && isset($data["tipoTransaccion"]) && isset($data["recurrente"])
            && isset($data["diaRecurrencia"]) && isset($data["nombreUsuario"]) && isset($data["formaPago"])
            && isset($data["valorTotal"]) && isset($data["origenPago"]) && isset($data["tipoTrans"])
            && isset($data["fechaInicio"]) && isset($data["numeroIntentos"])) {

            $numeroFactura = $data["numeroFactura"];

            $pagosDAO = new PagoRecargaDAO();

            $existePagoRealizado = $pagosDAO->searchEstadoPagoByFactura($numeroFactura);

            if (is_bool($existePagoRealizado)) {

                $numeroDocumento = $data["numeroDocumento"];
                $numeroLinea = $data["numeroLinea"];
                $tipoTransaccion = $data["tipoTransaccion"];
                $recurrente = $data["recurrente"];
                $diaRecurrencia = $data["diaRecurrencia"];
                $nombreUsuario = $data["nombreUsuario"];
                $formaPago = substr($data["formaPago"], 0, 1);
                $valorTotal = $data["valorTotal"];
                $origenPago = $data["origenPago"];
                $tipoTrans = $data["tipoTrans"];
                $estadoSRV = -1;//$app->request->post("estadoSRV");
                $fechaInicio = date("Y-m-d H:i:s", strtotime($data["fechaInicio"]));
                $numeroIntentos = $data["numeroIntentos"];

                # Validar la forma de pago 3 (cargo a la factura) para asignar la cuenta de la factura seleccionada
                # En caso contrario es NULL
                if ($formaPago == 3) {
                    $numeroCuenta = "'" . str_replace("3-", "", $data["formaPago"]) . "'";
                } else {
                    $numeroCuenta = "NULL";
                }

                $metodoPago = new MedioPago($formaPago, $nombreUsuario, "", "");

                $pagoRecarga = new PagoRecarga($numeroCuenta, $numeroFactura, $nombreUsuario, $metodoPago, $valorTotal,
                    $origenPago, $tipoTrans, $fechaInicio, $numeroIntentos, $numeroDocumento, $numeroLinea, $tipoTransaccion,
                    $recurrente, $diaRecurrencia);

                # Se extrae el Toke Numero enviado en el parámetro formaPago, en el caso que sea pago con tarjeta tokenizada
                if ($formaPago == 5) {
                    $pagoRecarga->setTokeNumero(str_replace("5-TK_", "", $data["formaPago"]));
                }

                $insertClPagos = $pagosDAO->insertPago($pagoRecarga);

                if (isset($insertClPagos["ID"])) {
                    $response["error"] = false;
                    $response["id"] = $insertClPagos["ID"];
                } else {
                    $response["error"] = true;
                    $response["mensaje"] = $insertClPagos["mensaje"];
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePagoRealizado["estado"];
            }
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });

    $app->post('/paquete', function () use ($app) {

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (isset($data["numeroFactura"]) && isset($data["numeroDocumento"]) && isset($data["numeroCuenta"])
            && isset($data["numeroLinea"]) && isset($data["tipoTransaccion"]) && isset($data["recurrente"])
            && isset($data["diaRecurrencia"]) && isset($data["nombreUsuario"]) && isset($data["formaPago"])
            && isset($data["valorTotal"]) && isset($data["origenPago"]) && isset($data["tipoTrans"])
            && isset($data["fechaInicio"]) && isset($data["numeroIntentos"])) {

            $numeroFactura = $data["numeroFactura"];

            $pagosDAO = new PagoPaqueteDAO();

            $existePagoRealizado = $pagosDAO->searchEstadoPagoByFactura($numeroFactura);

            if (is_bool($existePagoRealizado)) {

                $numeroDocumento = $data["numeroDocumento"];
                $numeroLinea = $data["numeroLinea"];
                $tipoTransaccion = $data["tipoTransaccion"];
                $recurrente = $data["recurrente"];
                $diaRecurrencia = $data["diaRecurrencia"];
                $nombreUsuario = $data["nombreUsuario"];
                $formaPago = substr($data["formaPago"], 0, 1);
                $valorTotal = $data["valorTotal"];
                $origenPago = $data["origenPago"];
                $tipoTrans = $data["tipoTrans"];
                $descripcion = $data["descripcion"];
                $tipoPaquete = $data["tipoPaquete"];
                $codigoPaquete = $data["codigoPaquete"];
                $tipoProducto = $data["tipoProducto"];
                $estadoSRV = -1;//$app->request->post("estadoSRV");
                $fechaInicio = date("Y-m-d H:i:s", strtotime($data["fechaInicio"]));
                $numeroIntentos = $data["numeroIntentos"];

                # Validar la forma de pago 3 (cargo a la factura) para asignar la cuenta de la factura seleccionada
                # En caso contrario es NULL
                if ($formaPago == 3) {
                    $numeroCuenta = "'" . str_replace("3-", "", $data["formaPago"]) . "'";
                } else {
                    $numeroCuenta = "NULL";
                }

                $metodoPago = new MedioPago($formaPago, $nombreUsuario, "", "");

                $pagoPaquete = new PagoPaquete($numeroCuenta, $numeroFactura, $nombreUsuario, $metodoPago, $valorTotal,
                    $origenPago, $tipoTrans, $fechaInicio, $numeroIntentos, $numeroDocumento, $numeroLinea, $tipoTransaccion,
                    $recurrente, $diaRecurrencia, $tipoPaquete, $codigoPaquete, $descripcion);

                # Se extrae el Toke Numero enviado en el parámetro formaPago, en el caso que sea pago con tarjeta tokenizada
                if ($formaPago == 5) {
                    $pagoPaquete->setTokeNumero(str_replace("5-TK_", "", $data["formaPago"]));
                }

                $logicaPagoPaquete = new LogicaPagoPaquete();

                $pagoPaquete->setTipoProducto($logicaPagoPaquete->asignarTipoProducto($tipoProducto));

                $datosPaqueteCompra = $logicaPagoPaquete->getTipoPaqueteTipoCompra($tipoPaquete, $pagoPaquete->getTipoProducto());

                if ($datosPaqueteCompra["error"] == false) {

                    $pagoPaquete->setTipoPaquete($datosPaqueteCompra["tipoPaquete"]);
                    $pagoPaquete->setTipoCompra($datosPaqueteCompra["tipoCompra"]);

                    $insertClPagos = $pagosDAO->insertPago($pagoPaquete);

                    if (isset($insertClPagos["ID"])) {
                        $response["error"] = false;
                        $response["id"] = $insertClPagos["ID"];
                        $response["paramSetAccount"] = $logicaPagoPaquete->asignarParamSetAccount($pagoPaquete->getTipoCompra(),
                            $pagoPaquete->getTipoPaquete(), $pagoPaquete->getTipoProducto(), $numeroLinea, $formaPago);
                    } else {
                        $response["error"] = true;
                        $response["mensaje"] = $insertClPagos["mensaje"];
                    }
                } else {
                    $response["error"] = true;
                    $response["mensaje"] = "Error al validar la compra";
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePagoRealizado["estado"];
            }
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }
        echoResponse(200, $response);
    });

    $app->post('/actualizar', function () use ($app) {

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (isset($data["idTransaccion"]) && isset($data["estadoPago"]) && isset($data["fechaTransaccion"])) {

            $idTransaccion = $data["idTransaccion"];
            $estadoPago = $data["estadoPago"];
            $fechaTransaccion = $data["fechaTransaccion"];

            $pagosDAO = new PagoHogarDAO();

            $error = $pagosDAO->updateEstadoPago($idTransaccion, $estadoPago, $fechaTransaccion);

            $response["error"] = !$error;
            
            
            $pagosDAO = new PagoDAO();
            

            if ($estadoPago == "APPROVED") {
                $datosPago = $pagosDAO->searchPagoTransaccionById($idTransaccion);
                $valorTotalPago = "$ " . number_format($datosPago["valorTotal"], 0, ',', '.');
                
                if (is_array($datosPago)) {

                    if ($datosPago["TIPO_TRANS"] == 3) {

                        // Se debe enviar el correo electrónico informando el pago exitoso
                        $datosEmail = array("USUARIO" => $datosPago["NombreCliente"], "ESTADO_PAGO" => $estadoPago,
                            "FACTURA" => $datosPago["numeroFactura"],
                            "VALOR" => $valorTotalPago,
                            "FECHA_PAGO" => date("d/m/Y"),
                            "TELEFONO" => $datosPago["TELEFONO"], "EMAIL" => $datosPago["EMAIL"]);

                        $mail = new Mail();

                        $email = $mail->notificarPago(3, $datosEmail, $datosPago["EMAIL"]);

                        $response["mail"] = $email;
                    } elseif ($datosPago["TIPO_TRANS"] == 2) {
                        $envioSMS = new Utils();

                        $r = $envioSMS->smsPospago($datosPago["numeroFactura"], $datosPago["NumeroCelular"],
                            $datosPago["CodigoCliente"]);

                        $response["sms"] = $r;
                    } elseif ($datosPago["TIPO_SELECCION"] == 'R' && ($datosPago["TIPO_TRANS"] == 9 || $datosPago["TIPO_TRANS"] == 11)
                        && $datosPago["FORMA_PAGO"] != 3) {
                        $logicaPagoPaquete = new LogicaPagoPaquete();
                        $paramSetAccount = "";
                        $aprovisionar = $logicaPagoPaquete->aprovisionarPaquete($idTransaccion, $paramSetAccount);

                        $response["error"] = $aprovisionar["error"];

                        if ($aprovisionar["error"] == false) {
                            $response["mensaje"] = "Aprovisionado";
                        } else {
                            $response["mensaje"] = $aprovisionar["mensaje"];
                        }
                    } elseif ($datosPago["TIPO_SELECCION"] == 'P' && ($datosPago["TIPO_TRANS"] == 9 || $datosPago["TIPO_TRANS"] == 11)
                        && $datosPago["FORMA_PAGO"] != 3) {
                        $logicaPagoPaquete = new LogicaPagoPaquete();
                        $paramSetAccount = $data["paramSetAccount"];
                        $aprovisionar = $logicaPagoPaquete->aprovisionarPaquete($idTransaccion, $paramSetAccount);

                        $response["error"] = $aprovisionar["error"];

                        if ($aprovisionar["error"] == false) {
                            $response["mensaje"] = "Aprovisionado";
                        } else {
                            $response["mensaje"] = $aprovisionar["mensaje"];
                        }
                    }
                }
            }
            
            $datosPagoComprobante = $pagosDAO->searchPagoTransaccionByIdComprobante($idTransaccion);
            $valorTotalPagoComprobante = "$ " . number_format($datosPagoComprobante["valorTotal"], 0, ',', '.');
            
            $referenciaPago = '';
            
            if($datosPagoComprobante["TIPO_TRANS"] == '9'){
            
                $referenciaPago = 'Para tu Número Claro: '.$datosPagoComprobante['NumeroCelular'];
            
            }elseif($datosPagoComprobante["TIPO_TRANS"] == '3'){
            
                $referenciaPago = 'Referencia o Número Cuenta: '.$datosPagoComprobante['NRO_CUENTA'];
            
            }elseif($datosPagoComprobante["TIPO_TRANS"] == '2' || $datosPagoComprobante["TIPO_TRANS"] == '12'){
            
                if($datosPagoComprobante["TIPO_TRANS"] == '2'){
                    $referenciaPago = 'Referencia: ';
                }else{
                    $referenciaPago = 'Número de Referencia de Equipo: ';
                }
            
                $referenciaPago .= $datosPagoComprobante['CodigoCliente'];
            }
            
            
            //Color Estado Pago
    		if( COLOR_ESTADOS_PDF == 1 ){
    		
    			$pago_estado = $datosPagoComprobante["ESTADO"];
    			switch( trim($pago_estado) ){
    				
    				case 'APPROVED':
    					list($R, $G, $B, $Html) = explode(',',RGB_APROVADO_PDF);
    				break;
    				
    				case 'DECLINED':
    					list($R, $G, $B, $Html) = explode(',',RGB_RECHAZADO_PDF);
    				break;
    				
    				case 'PENDING':
    					list($R, $G, $B, $Html) = explode(',',RGB_PENDIENTE_PDF);
    				break;
    				
    				default:
    				list($R, $G, $B, $Html) = explode(',',RGB_ERROR_PDF);
    			}
    		}else{
    			// Color Gris Por Defecto
    			$R = 132;
    			$G = 132;
    			$B = 132;
    			$Html = '#848484';
    		}
            
            $colorEstadoTrans = $Html;            
            
            $estadoTransaccion = '';
            if($datosPagoComprobante["ESTADO"] == 'APPROVED'){
                $estadoTransaccion = 'Transacción Aprobada';
            }elseif($datosPagoComprobante["ESTADO"] == 'PENDING'){
                $estadoTransaccion = 'Transacción Pendiente';
            }elseif($datosPagoComprobante["ESTADO"] == 'DECLINED'){
                $estadoTransaccion = 'Transacción Rechazada';
            }else{
                $estadoTransaccion = $datosPagoComprobante["ESTADO"];
            }
            
            
            $fechaLimitePagoComprobante = '';
            if($datosPagoComprobante["TIPO_TRANS"] == 2 || $datosPagoComprobante["TIPO_TRANS"] == 3){
            
                $fechaLimitePagoComprobante = substr($datosPagoComprobante['FechaVencimiento'],0,10);
                list($year,$mes,$dia) = explode('-',$fechaLimitePagoComprobante);
                $fechaLimitePagoComprobante = $dia.'/'.$mes.'/'.$year;
            
            }elseif($datosPagoComprobante['TIPO_SELECCION'] == 'P'){            
                $fechaLimitePagoComprobante = $datosPagoComprobante['DESCRIPTOR_COMPRA'];
            }
            
            $etiquetaNumeroClaro = '';
            $numeroClaro = '';        
            if($datosPagoComprobante["TIPO_TRANS"] == 9 || $datosPagoComprobante["TIPO_TRANS"] == 2){
                $etiquetaNumeroClaro = 'Número Claro';
                $numeroClaro = $datosPagoComprobante['NumeroCelular'];
                            
            }
            
            $fechaTransaccionPago = str_replace('-','/',$datosPagoComprobante['FECHA_TRANSACCION']);
            
            $etiquetaBanco = '';
            $nombreBanco = '';            
            if($datosPagoComprobante["FORMA_PAGO"] == '1'){
                $formaPagoComprobante = 'Pago con PSE';
                $serviciosClaro = new ServiciosClaro();
                $etiquetaBanco = 'Banco';
                $banco = $serviciosClaro->buscarValorDeListas('38', $datosPagoComprobante["BANCO"]);
                $nombreBanco = $banco[0]['VALOR'];
            }else{
                $formaPagoComprobante = 'Pago con Tarjeta';
            }
            
            $nombreCliente = 'CLIENTE';
            
            if(!is_null($datosPagoComprobante["NombreCliente"]) || !empty($datosPagoComprobante["NombreCliente"])){
                $nombreCliente = $datosPagoComprobante["NombreCliente"];
            }
            
            $datosEmail = array("NOMBRE" => $nombreCliente, "NUMEROREFERENCIA" => $referenciaPago, "ESTADOTRANSACCION" => $estadoTransaccion, "COLOR_ESTADO" => $colorEstadoTrans,
                "DESCRIPCIONPAGO" => $datosPagoComprobante["DESCRIPCION"], "FECHALIMITEPAGO" => $fechaLimitePagoComprobante,"VALORTOTAL" => $valorTotalPagoComprobante, 
                "ETIQUETANUMEROCLARO" => $etiquetaNumeroClaro, "NUMEROCLARO" => $numeroClaro, "FECHA" => $fechaTransaccionPago, "MONEDA" => "COP",
                "IPORIGEN" => $datosPagoComprobante["IP_ORIGEN"], "FORMAPAGO" => $formaPagoComprobante, "EMPRESA" => "Comcel S.A", "CUS" => $datosPagoComprobante["CUS"],
                "REFERENCIATRANSACCION" => $datosPagoComprobante["ID_ORDEN"], "PASA_NUMERO" => $datosPagoComprobante["PASA_NUMERO"], 
                "ETIQUETABANCO" => $etiquetaBanco, "BANCO" => $nombreBanco);
            /*, "" => $datosPago[""], "" => $datosPago[""], "DESCRIPCION" => $datosPago["DESCRIPCION"], "MSISDN" => $datosPago["TELEFONO"],
                "FACTURA" => $datosPago["numeroFactura"],
                "VALOR" => "$ " . number_format($datosPago["valorTotal"], 0, ',', '.'),
                "FECHA" => date("Y/m/d H:m:s"));*/

            $email = new Mail();
            $emailComprobante = $email->notificarPago(20, $datosEmail, $datosPagoComprobante["EMAIL"]);
            //$response["comprobante"] = $emailComprobante;

        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    });
    
    $app->post('/imprimir', 'debug', function () use ($app) {

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (isset($data["idTransaccion"])) {

            $idTransaccion = $data["idTransaccion"];
                                                
            $pagosDAO = new PagoDAO();
            $datosPago = $pagosDAO->searchPagoTransaccionByIdComprobante($idTransaccion);
            
            if (is_array($datosPago)) {
                $serviciosClaro = new ServiciosClaro();
                $nomVars = "'VAR_SERVBACKEND_COMPROBANTE'";
                $infoVar = $serviciosClaro->getVariables($nomVars);
                //$response["mensaje"] = file_get_contents('https://pruebasclaro.maxgp.com.co/phrame.php?action=despliegue_personal&clase=VistasClaroComprobante&metodo=generarComprobanteCifrado&empresa=claro&idTransaccion=3&genPdf=D');                                                            
                $response["error"] = false;
                $response["mensaje"] = "Ok";
                $response["data"] = $datosPago["PASA_NUMERO"];
                $response["url"] = $infoVar['VAR_SERVBACKEND_COMPROBANTE'];
                                                                                                                                      
            }else {
                $response["error"] = true;
                $response["mensaje"] = $datosPago;
            }
                                     
            
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    })->setName("imprimirPagos");
    
    $app->get('/dataComprobante', 'debug', function () use ($app) {
        
        $idTransaccion = $app->request()->get('idTransaccion');        
        
        if (isset($idTransaccion)) {
            $serviciosClaro = new ServiciosClaro();
            $nomVars = "'VAR_RUTA_COMPROBANTE','VAR_URLREDIR_COMPROBANTE'";
            $infoVar = $serviciosClaro->getVariables($nomVars);
            $rutaComprobante = str_replace('#PASA_NUMERO#',$idTransaccion,$infoVar['VAR_RUTA_COMPROBANTE']);
            //$rutaComprobante = "../../comprobantepago/".$idTransaccion.".pdf"; 
            $urlComprobante = str_replace('#PASA_NUMERO#',$idTransaccion,$infoVar['VAR_URLREDIR_COMPROBANTE']);
        
            if (file_exists($rutaComprobante)) {                                                
                header("Location: ".$urlComprobante);                                                                                                                                                
                exit;
            }else{                                
                $respuesta = $serviciosClaro->obtenerComprobantePdf($idTransaccion);
                $response['pdf'] = $respuesta['respuesta'];
                
                if($respuesta['estado'] == 200){
                    
                    $archivo = fopen ($rutaComprobante, "w+");
                    if ($archivo){
                        fwrite($archivo, $respuesta['respuesta']);
                        fclose($archivo);
                                                
                        if (file_exists($rutaComprobante)) {                                                    
                            header("Location: ".$urlComprobante);                                                                                                                                                                
                            exit;
                        }
                                                 
                    }else{
                        $response["error"] = true;
                        $response["mensaje"] = "no se pudo crear PDF";                        
                    }                                                                   
                    
                }else {
                    $response["error"] = true;
                    $response["mensaje"] = "no se pudo crear PDF";
                }
                
            }
                                                                                                                                                                                                                            
        } else {            
            $response["error"] = true;
            $response["mensaje"] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    })->setName("dataComprobante");
	
	/**
* Servicio para realizar el pago desde la app
*/
	
});

/**
 * Grupo destinado a recibir las peticiones referentes a los pagos en GT_PAGO_PASARELA
 */

$app->group('/transaccion', function () use ($app) {

    $app->get('/debito', function () use ($app) {

        $datosRecibidos = $app->request->get();

        if (ACH_PSE == 1) {
            $datosTransaccion = array();

            $asArr = explode('[amp]', $datosRecibidos["retornoURL"]);

            foreach ($asArr as $val) {
                $tmp = explode('=', $val);
                $datosTransaccion[$tmp[0]] = $tmp[1];
            }

            $tarjetaDebito = new TarjetaDebito("", "", "", "",
                "", $datosTransaccion["formaPago"], $datosTransaccion["nombreUsuario"], "", "");

            $actualizarPago = $tarjetaDebito->actualizarTransaccion($datosTransaccion);

            if ($actualizarPago["error"] == false) {
                #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $actualizarPago["mensaje"]);
                $app->redirect(NOMBRE_FRONT . "confirmacionpago?idTransaccion="
                    . $actualizarPago["respuesta"]["IDTransaccion"] . "&formaPago=" . $actualizarPago["respuesta"]["formaPago"]
                    . "&nombreUsuario=" . $actualizarPago["respuesta"]["nombreUsuario"] . "&tipoTrans=" . $actualizarPago["respuesta"]["tipoTrans"]
                    . "&tipoSeleccion=" . $actualizarPago["respuesta"]["tipoSeleccion"]);
            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $actualizarPago["mensaje"]);
            }
        } else {
            if (isset($datosRecibidos["transactionState"]) && isset($datosRecibidos["cus"]) && isset($datosRecibidos["valor_llave"])
                && isset($datosRecibidos["IDTransaccion"]) && isset($datosRecibidos["formaPago"]) && isset($datosRecibidos["nombreUsuario"])
                && isset($datosRecibidos["tipoTrans"])) {
                $estado = $datosRecibidos["transactionState"];

                if ($estado == 4) {
                    $estadoPSE = 'APPROVED';
                } elseif ($estado == 5) {
                    $estadoPSE = 'EXPIRED';
                } elseif ($estado == 6) {
                    $estadoPSE = 'DECLINED';
                } else {
                    $estadoPSE = 'PENDING';
                }

                $datosUpdate = array("estado" => $estadoPSE, "CUS" => $datosRecibidos["cus"]);

                $pagosDAO = new PagoHogarDAO();
                $result = $pagosDAO->updateTransaccionDebito($datosRecibidos["valor_llave"], $datosUpdate);

                $app->redirect(NOMBRE_FRONT . "confirmacionpago?idTransaccion="
                    . $datosRecibidos["IDTransaccion"] . "&formaPago=" . $datosRecibidos["formaPago"]
                    . "&nombreUsuario=" . $datosRecibidos["nombreUsuario"] . "&tipoTrans=" . $datosRecibidos["tipoTrans"]
                    . "&tipoSeleccion=" . $datosRecibidos["tipoSeleccion"]);

            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            }
        }

        echoResponse(200, $datosUpdate);
    });

    $app->post('/debito', function () use ($app) {

        session_start();
        session_regenerate_id(true);

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (LOG_FILE) {
            $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
            Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoDebito");
        }

        if (isset($data["IDTransaccion"]) && isset($data["nombreUsuario"]) && isset($data["IDBanco"])
            && isset($data["nombreTitular"]) && isset($data["telefonoCliente"]) && isset($data["emailCliente"])
            && isset($data["tipoCliente"]) && isset($data["numeroDocumentoCliente"]) && isset($data["tipoDocumentoCliente"])) {
            // Se recupera el ID Transaccion del pago
            $idTransaccion = $data["IDTransaccion"];

            // Validar si el pago existe en la tabla CL_PAGOSCLARO
            $pagosDAO = new PagoDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);
            /*$pagosDAO = new PagoHogarDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);*/

            if (is_array($existePago)) {
                $nombreUsuario = $data["nombreUsuario"];
                $formaPago = $existePago["FORMA_PAGO"];

                // Datos de la Tarjeta Débito
                // ID Banco, NombreTitular, TipoCliente (N,J), TipoDocumento, NumeroDocumento, email, #telefono
                $numeroBanco = $data["IDBanco"];
                $nombreClientePSE = $data["nombreTitular"];//$nombreCliente;//
                $telefonoClientePSE = $data["telefonoCliente"];//$telefonoCliente;//
                $emailClientePSE = $data["emailCliente"];//$emailCliente;//
                $tipoCliente = $data["tipoCliente"];//$tipoCliente;//
                $numeroDocumento = $data["numeroDocumentoCliente"];//$numeroDocumento;//
                $tipoDocumento = $data["tipoDocumentoCliente"];//$tipoDocumento;//

                $tarjetaDebito = new TarjetaDebito($numeroBanco, $nombreClientePSE, $telefonoClientePSE, $emailClientePSE,
                    $tipoCliente, $formaPago, $nombreUsuario, $tipoDocumento, $numeroDocumento);

                $existePago["numeroIntentos"] = $tarjetaDebito->getNumeroIntentos($existePago["numeroFactura"]);
                $existePago["IDTransaccion"] = $idTransaccion;
                $existePago["descripcion"] = (isset($data["descripcionCompra"])) ? $data["descripcionCompra"] : "Pago Gateway APP";

                /*$pagoHogar = new PagoHogar($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente,
                    $nombreUsuario, $tarjetaDebito, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV,
                    $fechaVencimiento, $fechaInicio, $tarjetaDebito->getNumeroIntentos($numeroFactura));

                $pagoHogar->setIDTransaccion($idTransaccion);*/

                $insercionGT = $pagosDAO->insertTransaccionDebito($existePago, $tarjetaDebito);

                if ($insercionGT == true) {
                    $existePago["registroPago"] = "exito";
                    $existePago["formaPago"] = $formaPago;
                    $existePago["idObjeto"] = $data["idObjeto"];
                    $existePago["paramSetAccount"] = $data["paramSetAccount"];
                    $existePago["ipOrigen"] = $data["ipOrigen"];

                    $responsePayU = $tarjetaDebito->pagar($existePago);

                    $response["error"] = $responsePayU["error"];

                    if ($responsePayU["error"] == false) {
                        // Se completo la transacción sin errores
                        $response["transaccion"] = $responsePayU["transaccion"];
                        $response["respuesta"] = $responsePayU["respuesta"];
                        // CUS, Empresa, NIT, IP, Fecha, Estado, # Transacción

                        $urlBanco = $responsePayU["respuesta"]["BANK_URL"];

                        //$app->redirect($urlBanco);
                    } else {
                        // Hubo errores al completar la transacción
                        $response["mensaje"] = $responsePayU["mensaje"];
                        $response["resultado"] = $responsePayU["transaccion"];
                    }
                } else {
                    // No se completó el registro de la transacción en GT_PAGO_PASARELA
                    //$pagoHogar->setRegistroPago("fracaso");
                    $existePago["registroPago"] = "fracaso";
                    $response["error"] = true;
                    $response["mensaje"] = "Problema al procesar la transacción";
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePago;
            }
        } else {
            if (LOG_FILE) {
                $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                    . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
                Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoDebitoError");
            }
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });

    $app->post('/credito', function () use ($app) {

        $tiempo_inicio = microtime(true);

        session_start();
        session_regenerate_id(true);

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        /*if (LOG_FILE) {
            $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
            Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoCredito");
        }*/

        if (isset($data["IDTransaccion"]) && isset($data["nombreTarjeta"]) && isset($data["emailTarjeta"])
            && isset($data["telefonoTarjeta"]) && isset($data["tipoDocumentoTarjeta"]) && isset($data["numeroDocumentoTarjeta"])
            && isset($data["cuotas"]) && isset($data["numeroTarjeta"]) && isset($data["CVVTarjeta"])
            && isset($data["mesVencimientoTarjeta"]) && isset($data["anoVencimientoTarjeta"]) && isset($data["franquiciaTarjeta"])
            && isset($data["nombreUsuario"])) {
            // Se recupera el ID Transaccion del pago
            $idTransaccion = $data["IDTransaccion"];

            // Validar si el pago existe en la tabla CL_PAGOSCLARO
            $pagosDAO = new PagoDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);

            if (is_array($existePago)) {

                // Datos de la Tarjeta Crédito
                $formaPago = $existePago["FORMA_PAGO"];
                $nombreTarjetaHabiente = $data["nombreTarjeta"];//$nombreCliente;//
                $emailTarjetaHabiente = $data["emailTarjeta"];//$nombreUsuario;//
                $telefonoTarjetaHabiente = $data["telefonoTarjeta"];//"8907676";//
                $tipoDocumento = $data["tipoDocumentoTarjeta"];//"CC";//
                $numeroDocumento = $data["numeroDocumentoTarjeta"];//"101010";//
                $cuotas = $data["cuotas"];//"8";//
                $numero = $data["numeroTarjeta"];//"4111111111111111";//
                $CVV = $data["CVVTarjeta"];//"9012";//
                $mesVencimiento = $data["mesVencimientoTarjeta"];//"09";//
                $anoVencimiento = $data["anoVencimientoTarjeta"];//"2020";//
                $franquicia = $data["franquiciaTarjeta"];//"VISA";//
                $nombreUsuario = $data["nombreUsuario"];

                $tarjetaCredito = new TarjetaCredito($nombreTarjetaHabiente, $emailTarjetaHabiente, $telefonoTarjetaHabiente,
                    $tipoDocumento, $numeroDocumento, $cuotas, $numero, $CVV, $mesVencimiento, $anoVencimiento, $franquicia,
                    $formaPago, $nombreUsuario);

                $existePago["numeroIntentos"] = $tarjetaCredito->getNumeroIntentos($existePago["numeroFactura"]);
                $existePago["IDTransaccion"] = $idTransaccion;
                $existePago["descripcion"] = (isset($data["descripcionCompra"])) ? $data["descripcionCompra"] : "Pago Gateway APP";

                $insercionGT = $pagosDAO->insertTransaccionCredito($existePago, $tarjetaCredito);

                if ($insercionGT == true) {
                    $existePago["registroPago"] = "exito";
                    $existePago["formaPago"] = $formaPago;
                    $existePago["idObjeto"] = $data["idObjeto"];
                    $existePago["paramSetAccount"] = $data["paramSetAccount"];
                    $existePago["ipOrigen"] = $data["ipOrigen"];

                    $responsePayU = $tarjetaCredito->pagar($existePago);

                    $response["error"] = $responsePayU["error"];

                    if ($responsePayU["error"] == false) {
                        // Se completo la transacción sin errores
                        //$response["transaccion"] = $responsePayU["transaccion"];
                        $response["respuesta"] = $responsePayU["respuesta"];
                        // CUS, Empresa, NIT, IP, Fecha, Estado, # Transacción

                    } else {
                        // Hubo errores al completar la transacción
                        $response["mensaje"] = $responsePayU["mensaje"];
                        $response["resultado"] = $responsePayU["transaccion"];
                    }
                } else {
                    // No se completó el registro de la transacción en GT_PAGO_PASARELA
                    //$pagoHogar->setRegistroPago("fracaso");
                    $response["error"] = true;
                    $response["mensaje"] = "Problema al procesar la transacción";
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePago;
            }
        } else {
            /*if (LOG_FILE) {
                $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                    . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
                Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoCreditoError");
            }*/
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        $tiempo_fin = microtime(true);
        $response["timeEject"] = round($tiempo_fin - $tiempo_inicio, 4);

        echoResponse(200, $response);
    });

    $app->post('/tokenizadas', function () use ($app) {

        session_start();
        session_regenerate_id(true);

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (LOG_FILE) {
            $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
            Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoConTokenizadas");
            /*$file = fopen("../tmp/seguimientoPagoConTokenizadas" . date("Ymd") . ".txt", "a+");
            fwrite($file, $trace);
            fclose($file);*/
        }

        if (isset($data["IDTransaccion"]) && isset($data["tokeNumero"]) && isset($data["CVV"]) && isset($data["cuotas"])
            && isset($data["idObjeto"])) {
            // Se recupera el ID Transaccion del pago
            $idTransaccion = $data["IDTransaccion"];

            // Validar si el pago existe en la tabla CL_PAGOSCLARO
            $pagosDAO = new PagoDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);
            /*$pagosDAO = new PagoHogarDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);*/

            if (is_array($existePago)) {
                // Validar la tarjeta Tokenizada seleccionada
                $tokeNumero = str_replace("TK_", "", $data["tokeNumero"]);

                $tarjetasTKDAO = new TarjetaTokenizadaDAO();
                $existeTarjetaTK = $tarjetasTKDAO->searchTarjetaById($tokeNumero);

                if (is_array($existeTarjetaTK)) {

                    $formaPago = $existePago["FORMA_PAGO"];

                    // Extraer los datos de la tarjeta seleccionada
                    /*
                    $nombreTarjetaHabiente = $existeTarjetaTK["NOMBRE"];
                    $emailTarjetaHabiente = $existeTarjetaTK["EMAIL"];
                    $telefonoTarjetaHabiente = $existeTarjetaTK["EMAIL"];
                    $tipoDocumento = $existeTarjetaTK["TIPO_DOCUMENTO"];
                    $numeroDocumento = $existeTarjetaTK["#DOCUMENTO"];
                    $cuotas = $data["cuotas"];
                    $franquicia = $existeTarjetaTK["FRANQUICIA"];*/

                    $tarjetaTokenizada = new TarjetaTokenizada($formaPago, $existeTarjetaTK["EMAIL"],
                        $existeTarjetaTK["TIPO_DOCUMENTO"], $existeTarjetaTK["#DOCUMENTO"]);

                    $tarjetaTokenizada->setTarjetaHabiente($existeTarjetaTK["NOMBRE"]);
                    $tarjetaTokenizada->setCelularSMS($existeTarjetaTK["TELEFONO"]);
                    $tarjetaTokenizada->setCelularRef($existeTarjetaTK["TELEFONO"]);
                    $tarjetaTokenizada->setCuotas($data["cuotas"]);
                    $tarjetaTokenizada->setFranquicia($existeTarjetaTK["FRANQUICIA"]);
                    $tarjetaTokenizada->setToken($existeTarjetaTK["TOKEN"]);

                    if (strpos($existeTarjetaTK["FRANQUICIA"], "CODENSA") !== false) {
                        $tarjetaTokenizada->setCVV($data["CVV"]);
                    }

                    $existePago["numeroIntentos"] = $tarjetaTokenizada->getNumeroIntentos($existePago["numeroFactura"]);
                    $existePago["IDTransaccion"] = $idTransaccion;
                    $existePago["descripcion"] = (isset($data["descripcionCompra"])) ? $data["descripcionCompra"] : "Pago Gateway APP";

                    /*$pagoHogar = new PagoHogar($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente,
                        $nombreUsuario, $tarjetaTokenizada, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV,
                        $fechaVencimiento, $fechaInicio, $tarjetaTokenizada->getNumeroIntentos($numeroFactura));

                    $pagoHogar->setIDTransaccion($idTransaccion);*/

                    $insercionGT = $pagosDAO->insertTransaccionTokenizada($existePago, $tarjetaTokenizada);

                    if ($insercionGT == true) {
                        $existePago["registroPago"] = "exito";
                        $existePago["formaPago"] = $formaPago;
                        $existePago["idObjeto"] = $data["idObjeto"];
                        $existePago["paramSetAccount"] = $data["paramSetAccount"];
                        $existePago["ipOrigen"] = $data["ipOrigen"];
                        $responsePayU = $tarjetaTokenizada->pagar($existePago);

                        $response["error"] = $responsePayU["error"];

                        if ($responsePayU["error"] == false) {
                            // Se completo la transacción sin errores
                            $response["transaccion"] = $responsePayU["transaccion"];
                            $response["respuesta"] = $responsePayU["respuesta"];
                        } else {
                            // Hubo errores al completar la transacción
                            $response["mensaje"] = $responsePayU["mensaje"];
                        }

                        $pagosDAO->updatePagoTokenizado($idTransaccion, $responsePayU["error"], $tarjetaTokenizada->getCelularSMS(),
                            $tarjetaTokenizada->getToken(), $responsePayU["transaccion"]["transactionResponse"]["state"]);
                    } else {
                        // No se completó el registro de la transacción en GT_PAGO_PASARELA
                        //$pagoHogar->setRegistroPago("fracaso");
                        $existePago["registroPago"] = "fracaso";
                        $response["error"] = true;
                        $response["mensaje"] = "Problema al procesar la transacción";
                    }
                } else {
                    $response["error"] = true;
                    $response["mensaje"] = $existeTarjetaTK;
                }

            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePago;
            }
        } else {
            if (LOG_FILE == 1) {
                $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                    . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
                Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoConTokenizadasError");
                /*$file = fopen("../tmp/seguimientoPagoConTokenizadasError" . date("Ymd") . ".txt", "a+");
                fwrite($file, $trace);
                fclose($file);*/
            }
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });

    $app->post('/cargoFactura', function () use ($app) {

        session_start();
        session_regenerate_id(true);

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (LOG_FILE) {
            $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
            Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoCargoFactura");
        }

        if (isset($data["IDTransaccion"]) && isset($data["cuenta"]) && isset($data["numeroCelular"]) && isset($data["nombreUsuario"])
            && isset($data["idObjeto"])) {
            // Se recupera el ID Transaccion del pago
            // idObjeto, nombreUsuario, NombreCliente

            $idTransaccion = $data["IDTransaccion"];
            $cuenta = $data["cuenta"];

            // Validar si el pago existe en la tabla CL_PAGOSCLARO
            $pagosDAO = new PagoDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);

            if (is_array($existePago)) {

                if ($existePago['TIPO_SELECCION'] == 'P') {
                    $logicaPago = new LogicaPagoPaquete();

                    $linea = $logicaPago->encontrarLineaNumeroCuenta($data["numeroCelular"]);
                    if ($linea['error'] == false) {
                        $data["numeroCelular"] = $linea['mensaje'];
                    } else {
                        $errorLinea = true;
                    }

                } elseif ($existePago['TIPO_SELECCION'] == 'R') {
                    $logicaPago = new LogicaPagoRecarga();
                }

                if (!isset($errorLinea)) {

                    $nombreUsuario = $data["nombreUsuario"];
                    $formaPago = '3';

                    // Datos de PayUTeFia
                    // NombreTitular, TipoCliente (N,J), TipoDocumento, NumeroDocumento, email, #telefono
                    $nombreCliente = '';//$nombreCliente;//
                    $telefonoCliente = $data["numeroCelular"];//$telefonoCliente;//
                    $numeroDocumento = '';//$numeroDocumento;//
                    $tipoDocumento = '';//$tipoDocumento;//

                    $cargoFactura = new CargoFactura($formaPago, $nombreUsuario, $tipoDocumento, $numeroDocumento,
                        $telefonoCliente, $cuenta);

                    //$existePago["numeroIntentos"] = $cargoFactura->getNumeroIntentos($existePago["numeroFactura"]);
                    $existePago["IDTransaccion"] = $idTransaccion;
                    $existePago["idObjeto"] = $data["idObjeto"];
                    $existePago["descripcion"] = (isset($data["descripcionCompra"])) ? $data["descripcionCompra"] : "Pago Gateway APP";
                    $existePago["paramSetAccount"] = (isset($data["paramSetAccount"])) ? $data["paramSetAccount"] : '';


                    $lineaOrigen = $logicaPago->validarOrigen($cargoFactura->getCuenta());
                    $existePago['clasificacion'] = $lineaOrigen['datos'];
                    if ($lineaOrigen['error'] == false) {
                        $lineaDestino = $logicaPago->validarDestino($cargoFactura->getCuenta(), $cargoFactura->getTelefonoCliente());
                        if ($lineaDestino['error'] == false) {
                            $consultaRecarga = $logicaPago->consultaRecarga($cargoFactura->getCuenta(), $lineaOrigen['datos']);
                            $existePago['valorPermitido'] = $consultaRecarga['datos'];
                        }
                    }


                    if (isset($lineaOrigen) && isset($lineaDestino) && isset($consultaRecarga)) {
                        if ($existePago['valorTotal'] <= $consultaRecarga['datos']) {

                            $insercionGT = $pagosDAO->insertTransaccionCargoFactura($existePago, $cargoFactura);
                            $existePago['estadoFactura'] = $pagosDAO->searchEstadoPagoByFactura($cuenta);

                            if ($insercionGT == true) {
                                $existePago["registroPago"] = "exito";
                                $existePago["ipOrigen"] = $data["ipOrigen"];
                                $responseService = $cargoFactura->pagar($existePago);

                                $response["error"] = $responseService["error"];

                                if ($response["error"] == false) {
                                    // Se completó la transacción sin errores
                                    //$response["transaccion"] = $responsePayU["transaccion"];
                                    $datosPago = $pagosDAO->searchPagoTransaccionById($idTransaccion);

                                    $datosEmail = array("DESCRIPCION" => $datosPago["DESCRIPCION"], "MSISDN" => $datosPago["TELEFONO"],
                                        "FACTURA" => $datosPago["numeroFactura"],
                                        "VALOR" => "$ " . number_format($datosPago["valorTotal"], 0, ',', '.'),
                                        "FECHA" => date("Y/m/d H:m:s"));

                                    $email = new Mail();
                                    $email->notificarPago(16, $datosEmail, $nombreUsuario);

                                    $response["mensaje"] = $responseService["mensaje"];

                                    // CUS, Empresa, NIT, IP, Fecha, Estado, # Transacción
                                    $response["NIT"] = '800.153.993-7';
                                    $response["empresa"] = 'Comcel S.A';
                                    $response["CUS"] = "";
                                    $response["fecha"] = $datosEmail["FECHA"];
                                    $response['estado'] = $responseService['estado'];
                                    $response['IP'] = $data["ipOrigen"];

                                } else {
                                    // Hubo errores al completar la transacción
                                    $response["mensaje"] = $responseService["mensaje"];
                                }

                            } else {
                                // No se completó el registro de la transacción en GT_PAGO_PASARELA
                                $existePago["registroPago"] = "fracaso";
                                $response["error"] = true;
                                $response["mensaje"] = "Señor usuario en este momento no podemos finalizar su pago, la transacción no fue procesada";
                            }
                        } else {
                            $response["error"] = true;
                            $response['mensaje'] = 'Sobrepaso el cupo limite de valor permitido';
                        }
                    } else {
                        if (!isset($lineaOrigen)) {
                            $response['mensaje'] = MSG_LINEAORIGEN;
                        } else {
                            if (!isset($lineaDestino)) {
                                $response['mensaje'] = MSG_LINEADESTINO;
                            } else {
                                $response['mensaje'] = MSG_CONSULTARECARGA;
                            }
                        }
                    }
                } else {
                    $response["error"] = true;
                    $response["mensaje"] = MSG_ERROR_ENCUENTRALINEA;
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePago;
            }
        } else {
            if (LOG_FILE) {
                $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                    . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
                Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoCargoFacturaError");
            }
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);

    });

    $app->get('/payUTeFia', function () use ($app) {

        $datosRecibidos = $app->request->get();

        if (LOG_FILE == 1) {
            $trace = json_encode($datosRecibidos) . PHP_EOL . PHP_EOL;
            Utils::escribirArchivoSeguimiento($trace, "datosPayUTeFia");
        }
		
		/*$file = fopen("../tmp/datospayutefia.txt", "a+");
		fwrite($file, json_encode($datosRecibidos));
		fclose($file);*/
		
        if (isset($datosRecibidos["transactionState"]) && isset($datosRecibidos["cus"]) && isset($datosRecibidos["valor_llave"])
            && isset($datosRecibidos["IDTransaccion"]) && isset($datosRecibidos["formaPago"]) && isset($datosRecibidos["nombreUsuario"])
            && isset($datosRecibidos["tipoTrans"]) && isset($datosRecibidos["signatureValidacion"])) {

            $estado = $datosRecibidos["transactionState"];

            if ($estado == 4) {
                $estadoPayU = 'APPROVED';
            } elseif ($estado == 5) {
                $estadoPayU = 'EXPIRED';
            } elseif ($estado == 6) {
                $estadoPayU = 'DECLINED';
            } else {
                $estadoPayU = 'PENDING';
            }

            $datosUpdate = array("estado" => $estadoPayU, "CUS" => $datosRecibidos["cus"]);

            $pagosDAO = new PagoHogarDAO();
            $result = $pagosDAO->updateTransaccionPayUTeFia($datosRecibidos["valor_llave"], $datosUpdate);

            $app->redirect(NOMBRE_FRONT . "confirmacionpago?idTransaccion="
                . $datosRecibidos["IDTransaccion"] . "&formaPago=" . $datosRecibidos["formaPago"]
                . "&nombreUsuario=" . $datosRecibidos["nombreUsuario"] . "&tipoTrans=" . $datosRecibidos["tipoTrans"] . "&signature=" . $datosRecibidos["signatureValidacion"]);

        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
        }


        echoResponse(200, $datosUpdate);
    });

    $app->post('/payUTeFia', function () use ($app) {

        session_start();
        session_regenerate_id(true);

        $data = utf8Converter(json_decode($app->request->getBody(), true));

        if (LOG_FILE) {
            $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
            Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoPayUTeFia");
        }

        if (isset($data["IDTransaccion"]) && isset($data["descripcionCompra"]) && isset($data["nombreUsuario"])
            && isset($data["idObjeto"]) && isset($data["nombreTitular"]) && isset($data["tipoDocumentoCliente"]) &&
            isset($data["numeroDocumentoCliente"]) && isset($data["emailCliente"]) && isset($data["telefonoCliente"])
            && isset($data["signature"])) {
            // Se recupera el ID Transaccion del pago
            // idObjeto, nombreUsuario, NombreCliente
            $idTransaccion = $data["IDTransaccion"];

            // Validar si el pago existe en la tabla CL_PAGOSCLARO
            $pagosDAO = new PagoDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);

            if (is_array($existePago)) {
                $formaPago = $existePago["FORMA_PAGO"];

                // Datos de Cargo a la factura
                // NombreTitular, TipoCliente (N,J), TipoDocumento, NumeroDocumento, email, #telefono

                $nombreUsuario = $data["nombreUsuario"];
                $nombreCliente = $data["nombreTitular"];//$nombreCliente;//
                $telefonoCliente = $data["telefonoCliente"];//$telefonoCliente;//
                $numeroDocumento = $data["numeroDocumentoCliente"];//$numeroDocumento;//
                $tipoDocumento = $data["tipoDocumentoCliente"];//$tipoDocumento;//
                $signature = $data["signature"];

                $payUTeFia = new PayUTeFia($formaPago, $nombreUsuario, $tipoDocumento, $numeroDocumento,
                    $nombreCliente, $telefonoCliente, $signature);

                $existePago["numeroIntentos"] = $payUTeFia->getNumeroIntentos($existePago["numeroFactura"]);
                $existePago["IDTransaccion"] = $idTransaccion;
                $existePago["idObjeto"] = $data["idObjeto"];
                $existePago["descripcion"] = (isset($data["descripcionCompra"])) ? $data["descripcionCompra"] : "Pago Gateway APP";

                $insercionGT = $pagosDAO->insertTransaccionPayUTeFia($existePago, $payUTeFia);

                if ($insercionGT == true) {
                    $existePago["registroPago"] = "exito";
                    $existePago["ipOrigen"] = $data["ipOrigen"];
                    $existePago["paramSetAccount"] = $data["paramSetAccount"];
                    $responsePayUTeFia = $payUTeFia->pagar($existePago);

                    $response["error"] = $responsePayUTeFia["error"];

                    if ($response["error"] == false) {
                        // Se completó la transacción sin errores
                        //$response["transaccion"] = $responsePayU["transaccion"];
                        $response["respuesta"] = $responsePayUTeFia["respuesta"];
                        $response["respuesta"]["URL_PAYMENT_REDIRECT"] = $responsePayUTeFia["transaccion"]["transactionResponse"]["extraParameters"]["URL_PAYMENT_REDIRECT"];
                        // CUS, Empresa, NIT, IP, Fecha, Estado, # Transacción

                    } else {
                        // Hubo errores al completar la transacción
                        $response["mensaje"] = $responsePayUTeFia["mensaje"];
                        $response["resultado"] = $responsePayUTeFia["transaccion"];
                    }
                } else {
                    // No se completó el registro de la transacción en GT_PAGO_PASARELA
                    $existePago["registroPago"] = "fracaso";
                    $response["error"] = true;
                    $response["mensaje"] = "Problema al procesar la transacción";
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePago;
            }
        } else {
            if (LOG_FILE) {
                $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL
                    . "Datos Transacción para Pago: " . json_encode(utf8Converter($data)) . PHP_EOL;
                Utils::escribirArchivoSeguimiento($trace, "seguimientoPagoPayUTeFiaError");
            }
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });

     /**
     * Servicio para realizar el pago desde la app
     */
    $app->post('/pago', function () use ($app) {
        $data = $app->request->post();
        $headers = $app->request->headers();
        if ( /*isset($headers['JWT']) && */ isset($data["formaPago"]) && isset($data["tipoTrans"])) {
            
            // $jwt = $app->request->headers->get('JWT');
            // $resultValidate = validateJWT($jwt);
            // if ( $resultValidate['error'] == true ) {
            // 	$respuesta = ["error" => true, "mensaje" => $resultValidate['mensaje'], "respuesta" => "" ];
            //     echoResponse(403, $respuesta);
            //     $app->stop();
            // }
            $forma_pago = $data['formaPago'];
            $tipoTrans = $data['tipoTrans'];
            $servicesApp = new ServicesAppController();
            switch (intval($tipoTrans)) {
                // case 1:
                //     $servicio = $servicesApp->recarga($data);
                //     if (!$servicio['error']) {
                //         $data = $servicio['data'];
                //         $registroIntento = $servicesApp->registroIntentoRecarga($data);

                //         $app->redirect(
                //             NOMBRE_FRONT . "mediopagodebito?OrigenPago=". $data['origenPago'] ."&fecha=" . $data['fecha'] .
                //     "&numeroCuenta=" . $data['numeroCuenta'] . "&valorCompra=" . $data['valorCompra'] . "&descripcionCompra=" . $data['descripcionCompra'] .
                //     "&nombreUsuario=" . $data['nombreUsuario'] . "&ipOrigen=" . $data['ipOrigen'].
                //     "&tipoTrans=" . $data['tipoTrans'] . "&numeroLinea=" . $data['numeroLinea'] . "&numeroDocumento=" . $data['numeroDocumento'] . "&numeroFactura=" . $numeroFactura['numeroFactura'] .
                //     "&codigoPaquete=" . $data['tipoPaquete'] . "&tipoProducto=" . $data['tipoProducto'] . "&codigoPaquete=" . $data['codigoPaquete']
                //         );
                //     }
                //     break;
                
                case 3:
                    $servicio = $servicesApp->hogar($data);
                    if (!$servicio['error']) {
                        $data = $servicio['data'];
                        $registroIntento = $servicesApp->registroIntentoHogar($data);
                    }
                    break;

                case 2:
                    $servicio = $servicesApp->postPago($data);
                    //echo "<pre>"; print_r($servicio); echo "</pre>";exit;
                    if (!$servicio['error']) {
                        $data = $servicio['data'];
                        $registroIntento = $servicesApp->registroIntentoPostPago($data);
                    }
                    // no break
                default:
                    # code...
                    break;
            }
            if (!$servicio['error'] && !$registroIntento['error']) {
                $idTransaccion = $registroIntento['id'];
                if ($forma_pago == 1 && $tipoTrans == 3) {
                    $app->redirect(NOMBRE_FRONT . "mediopagodebito?OrigenPago=". $data['origenPago'] ."&fecha=" . $data['fechaInicio'] .
                    "&numeroCuenta=" . $data['numeroCuenta'] . "&valor=" . $data['valorTotal'] . "&referencia=" . $data['numeroFactura'] .
                    "&fechalimitepago=" . $data['fechaLimitePago'] . "&token=" . $data['token'] . "&nombreUsuario=" . $data['nombreUsuario'] .
                    "&nombreCliente=" . $data['nombreCliente'] . "&ipOrigen=" . $data['ipOrigen'] . "&direccionServicio=" . $data['direccionServicio'] .
                    "&idTransaccion=" . $idTransaccion . "&formaPago=" . $data['formaPago'] . "&tipoTrans=" . $data['tipoTrans']);
                } elseif ($forma_pago == 1 && $tipoTrans == 2) {
                    $app->redirect(NOMBRE_FRONT . "mediopagodebito?OrigenPago=". $data['origenPago'] . "&numeroCelular=" . $data['numeroCelular'] . "&fecha=" . $data['fechaInicio'] .
                    "&valor=" . $data['valorTotal'] . "&numeroFactura=" . $data['numeroFactura'] . "&referencia=" . $data['numeroReferencia'] .
                    "&fechalimitepago=" . $data['fechaLimitePago'] . "&token=" . $data['token'] . "&nombreUsuario=" . $data['nombreUsuario'] .
                    "&nombreCliente=" . $data['nombreCliente'] . "&ipOrigen=" . $data['ipOrigen'] . "&direccionServicio=" . $data['direccionServicio'] .
                    "&idTransaccion=" . $idTransaccion . "&formaPago=" . $data['formaPago'] . "&tipoTrans=" . $data['tipoTrans']);
                } elseif ($forma_pago == 1 && $tipoTrans == 1) {
                    $app->redirect(NOMBRE_FRONT . "mediopagodebito?OrigenPago=". $data['origenPago'] ."&fecha=" . $data['fechaInicio'] .
                    "&numeroCuenta=" . $data['numeroCuenta'] . "&valor=" . $data['valorTotal'] . "&referencia=" . $data['numeroFactura'] .
                    "&fechalimitepago=" . $data['fechaLimitePago'] . "&token=" . $data['token'] . "&nombreUsuario=" . $data['nombreUsuario'] .
                    "&nombreCliente=" . $data['nombreCliente'] . "&ipOrigen=" . $data['ipOrigen'] . "&direccionServicio=" . $data['direccionServicio'] .
                    "&idTransaccion=" . $idTransaccion . "&formaPago=" . $data['formaPago'] . "&tipoTrans=" . $data['tipoTrans']);
                }
            } else {
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $registroIntento['mensaje']);
            }
        } else {
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
        }
    });
    /**
     * Servicios que retornar la información del pago en la vista de confirmación dependiendo el flujo
     */

    $app->get('/hogar', function () use ($app) {

        $data = $app->request->get();

        if (isset($data['idTransaccion'])) {
            $id = $data['idTransaccion'];

            $pagosDAO = new PagoHogarDAO();

            $response["error"] = false;
            $response["pago"] = $pagosDAO->searchTransaccionById($id);
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });

    $app->get('/postpago', function () use ($app) {

        $data = $app->request->get();

        if (isset($data['idTransaccion'])) {
            $id = $data['idTransaccion'];

            $pagosDAO = new PagoMovilDAO();

            $response["error"] = false;
            $response["pago"] = $pagosDAO->searchTransaccionById($id);
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });

    $app->get('/recarga', function () use ($app) {

        $data = $app->request->get();

        if (isset($data['idTransaccion'])) {
            $id = $data['idTransaccion'];

            $pagosDAO = new PagoRecargaDAO();

            $response["error"] = false;
            $response["pago"] = $pagosDAO->searchTransaccionById($id);
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });

    $app->get('/paquete', function () use ($app) {

        $data = $app->request->get();

        if (isset($data['idTransaccion'])) {
            $id = $data['idTransaccion'];

            $pagosDAO = new PagoPaqueteDAO();

            $response["error"] = false;
            $response["pago"] = $pagosDAO->searchTransaccionById($id);
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }

        echoResponse(200, $response);
    });
	
	/**
     * Servicio que muestra ventana de error, cuando en PayU expire la sesion
     */
	$app->get('/sesionExpirada', function () use ($app) {
		$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=Su transacción ha expirado. Por favor inténtelo nuevamente");	
	});

});

$app->group('/externalServices','debug','externalAuthentication', function () use ($app) {

    $app->post('/tarjetasTokenizadas', function () use ($app) {

        $data = json_decode($app->request->getBody(),true);

        if (isset($data['nombreUsuario']) && isset($data['fecha'])) {

            $tokenValidacion = validarTokenSeguridad($data['fecha']);
            if($tokenValidacion['error'] == false) {
                $nombreUsuario = $data['nombreUsuario'];
                $condicionCodensa = true;

                # Se valida si deben ser enviadas las tarjetas de franquicia Codensa
                if (isset($data['codensa'])) {
                    if (!empty($data["codensa"])) {
                        $condicionCodensa = false;
                    }
                }


                $tarjetasDAO = new TarjetaTokenizadaDAO();
                $tarjetas = $tarjetasDAO->searchTarjetaByEmail($nombreUsuario, $condicionCodensa);

                if ($tarjetas['error'] == false) {
                    $response['error'] = false;
                    $response['mensaje'] = 'Tarjetas encontradas';
                    $response['tarjetas'] = $tarjetas['tarjetas'];
                } else {
                    $response['error'] = true;
                    $response['mensaje'] = 'No se encuentran tarjetas registradas';
                }
            }else{
                $response = $tokenValidacion;
            }
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);

    })->setName("ESTarjetasTokenizadas");
});

$app->group('/testing', function () use ($app) {

    $app->get('/testConnection', function () use ($app) {

        echo "Test Succesful";

    });
});

/**
 * Grupo para aprovisionar el paquete Gratuito PSE
 */

$app->group('/aprovisionar', function () use ($app) {

    $app->post('/PSEGratuito', function () use ($app) {

        $data = json_decode($app->request->getBody(), true);

        if (!empty($data["IDTransaccion"]) && !empty($data["tipoTrans"]) && !empty($data["lineaCelularHE"])
            && !empty($data["fechaInicio"])) {

            $logicaPagoPaquete = new LogicaPagoPaquete();

            $response = $logicaPagoPaquete->procesarAprovisionamientoGratuitoPSE($data["lineaCelularHE"],
                $data["IDTransaccion"], $data["tipoTrans"], $data["fechaInicio"]);
        } else {
            #$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_PARAMETROSVACIOS);
            $response["error"] = true;
            $response["mensaje"] = MSG_PARAMETROSVACIOS;
        }
        echoResponse(200, $response);

    });
});

/**
 * Grupo para los endpoints correspondientes al proceso de doble autenticacion.
 */
$app->group('/dobleAutenticacion', 'debug', function () use ($app) {
    /**
     * Valida si la cuenta seleccionada no ha sufrido cambios en el sistema RechargesWithInvoiceCharges
	 *
	 * @example /APIGateway/v1/dobleAutenticacion/customerData?cuenta=25745526
     */
    $app->get('/customerData', function () use ($app) {
        $data = $app->request->get();

        $response = array();

        if (isset($data['cuenta'])) {
            $cuenta = $data['cuenta'];

            $invoiceCharge = new RechargesWithInvoiceCharges();
			$invoiceCharge->setCuenta($cuenta);

            $response = $invoiceCharge->consultCustomerData();
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    })->setName('customerData');
	
	/**
     * Valida si la linea movil no ha sufrido cambios en el sistema RechargesWithInvoiceCharges
	 *
	 * @example /APIGateway/v1/dobleAutenticacion/validateChange?msisdn=31025745526
     */
	$app->get('/validateChange', function () use ($app) {
        $data = $app->request->get();

        $response = array();

        if (isset($data['msisdn'])) {
            $msisdn = $data['msisdn'];

            $invoiceCharge = new RechargesWithInvoiceCharges();
			$invoiceCharge->setLinea($msisdn);

            $response = $invoiceCharge->validateChangeInLine();
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    })->setName('validateChange');

    /**
     * Consulta los departamentos disponibles.
     *
     * @example /APIGateway/v1/dobleAutenticacion/obtenerDepartamentos
     */
    $app->get('/obtenerDepartamentos', function () use ($app) {
        $autenticacionDao = new DobleAutenticacionDAO();

        $respuesta = $autenticacionDao->obtenerDepartamentos();

        echoResponse(200, $respuesta);
    })->setName('obtenerDepartamentos');

    /**
     * Consulta los municipios disponibles de un departamento por medio del codigo dane del departamento.
     *
     * @example /APIGateway/v1/dobleAutenticacion/municipiosDepartamento?departamento=76
     */
    $app->get('/municipiosDepartamento', function () use ($app) {
        $data = $app->request->get();

        $response = array();

        if (!empty($data['departamento'])) {
            $departamento = $data['departamento'];

            $autenticacionDao = new DobleAutenticacionDAO();

            $response = $autenticacionDao->obtenerMunicipiosDepartamento($departamento);
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    })->setName('municipiosDepartamentos');

    /**
     * Realiza la autenticacion en el sistema IdVision
	 *
	 * @example /APIGateway/v1/dobleAutenticacion/authenticationIdVision
     */
    $app->post('/authenticationIdVision', function () use ($app) {
        $data = utf8Converter(json_decode($app->request->getBody(), true));
        //$data = $app->request->post();

        $response = array();

        if (!empty($data['numeroDocumento']) && !empty($data['fechaExpDocumento']) && !empty($data['codigoDaneDepto']) && !empty($data['nombreDepto'])
            && !empty($data['codigoDaneMunicipio']) && !empty($data['nombreMunicipio']) && !empty($data['primerApellido']) && !empty($data['numeroCelular'])) {

            $numeroDocumento = $data['numeroDocumento'];
            $fechaExpDocumento = $data['fechaExpDocumento'];
            $codigoDaneDepto = $data['codigoDaneDepto'];
            $nombreDepto = $data['nombreDepto'];
            $codigoDaneMunicipio = $data['codigoDaneMunicipio'];
            $nombreMunicipio = $data['nombreMunicipio'];
            $primerApellido = $data['primerApellido'];
            $numeroCelular = $data['numeroCelular'];

            $idVision = new IdVision();

            $response = $idVision->authentication($numeroDocumento, $fechaExpDocumento, $codigoDaneDepto, $nombreDepto, $codigoDaneMunicipio, $nombreMunicipio, $primerApellido, $numeroCelular);
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    })->setName('authenticationIdVision');

    /**
     * Solicitar la generacion de codigo otp por el sistema IdVision
	 *
	 * @example /APIGateway/v1/dobleAutenticacion/generarPinIdVision
     */
    $app->post('/generarPinIdVision', function () use ($app) {
		$data = utf8Converter(json_decode($app->request->getBody(), true));
        //$data = $app->request->post();

        $response = array();

        if (!empty($data['applicationId']) && !empty($data['phoneList']) && !empty($data['selectedPhoneNumber'])) {
            $applicationId = $data['applicationId'];
            $phoneList = $data['phoneList'];
            $selectedPhoneNumber = $data['selectedPhoneNumber'];

            $idVision = new IdVision();
            $idVision->setApplicationId($applicationId);
            $idVision->setPhoneList($phoneList);
            $idVision->setSelectedPhoneNumber($selectedPhoneNumber);

            $response = $idVision->generarPin();
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    })->setName('generarPinIdVision');

    /**
     * Valida el codigo otp digitado por el usuario en el sistema IdVision
	 *
	 * @example /APIGateway/v1/dobleAutenticacion/validatePinIdVision
     */
    $app->post('/validatePinIdVision', function () use ($app) {
		$data = utf8Converter(json_decode($app->request->getBody(), true));
        //$data = $app->request->post();

        $response = array();

        if (!empty($data['applicationId']) && !empty($data['pinNumber'])) {
            $applicationId = $data['applicationId'];
            $pinNumber = $data['pinNumber'];

            $idVision = new IdVision();
            $idVision->setApplicationId($applicationId);
            $idVision->setPinNumber($pinNumber);

            $response = $idVision->validarPin();
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
    })->setName('validatePinIdVision');

    /**
     * Genera y envia el pin a un correo electronico o una linea movil.
     *
     * @example /APIGateway/v1/dobleAutenticacion/generatePin
     */
	$app->post('/generatePin', function () use ($app) {
		$data = utf8Converter(json_decode($app->request->getBody(), true));
        //$data = $app->request->post();

        $response = array();

        if (!empty($data['medioEnvio']) && !empty($data['clienteId'])) {
            $medioEnvio = $data['medioEnvio'];
            $clienteId = $data['clienteId'];

            $pinGen = new PinGenerationValidate();
            $pinGen->setClienteId($clienteId);
            $pinGen->setVchDatoMedioEnvio($medioEnvio);

            $response = $pinGen->generarPin();
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
	})->setName('generatePin');
	
	/**
     * Valida el pin enviado a un correo electronico o una linea movil.
     *
     * @example /APIGateway/v1/dobleAutenticacion/validatePin
     */
	$app->post('/validatePin', function () use ($app) {
		$data = utf8Converter(json_decode($app->request->getBody(), true));
        //$data = $app->request->post();

        $response = array();

        if (!empty($data['medioEnvio']) && !empty($data['clienteId']) && !empty($data['pinCode'])) {
            $medioEnvio = $data['medioEnvio'];
            $clienteId = $data['clienteId'];
			$pinCode = $data['pinCode'];

            $pinGen = new PinGenerationValidate();
            $pinGen->setClienteId($clienteId);
            $pinGen->setVchDatoMedioEnvio($medioEnvio);
			$pinGen->setPinGenerado($pinCode);

            $response = $pinGen->validarPin();
        } else {
            $response['error'] = true;
            $response['mensaje'] = "Parametros Vacios";
        }

        echoResponse(200, $response);
	})->setName('validatePin');
});

/**
 * Grupo para notificar errores presentados en la APP
 */

$app->group('/automatizacion', function () use ($app) {

    $app->get('/notifyError', function () use ($app) {

        $data = $app->request->get();

        if(isset($data['fecha'])){

            //Se cambia header para dar response con formato html
            header('Content-Type: text/html; charset=utf-8');

            $email = isset($data['email']) ? $data['email']:'';
            $sendMail = isset($data['sendMail']) ? true : false;
            $fecha = date("Y-m-d H:i:s", strtotime($data['fecha']));
            
            $automatizacion = new Automatizacion($email, $fecha, $sendMail);
            $responseErrors = utf8Converter($automatizacion->searchErrors());

            $errors = '';

            if(is_array($responseErrors)) {
                foreach ($responseErrors as $error) {
                    $errors .= '<br>' . $error . '<br>';
                }
            }else{
                $errors .= '<br>' . $responseErrors . '<br>';
            }

            $plantilla = utf8Converter($automatizacion->getPlantilla());
            $plantilla['plantilla'] = str_replace('#FECHA#',$fecha,$plantilla['plantilla']);
            $plantilla['plantilla'] = str_replace('#ERRORES#',$errors,$plantilla['plantilla']);

            echo $plantilla['plantilla'];
        }

    });
});

$app->error(function (\Exception $e) use ($app) {
    # Escribir archivo de seguimiento

    #Se verifica si es un problema de conexión a base de datos
    if (!empty($_SESSION["FAIL_DB"])) {
        $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL . "Error: No se pudo crear la conexión con la base de datos." . PHP_EOL
            . "Detalle: " . $e->getMessage() . PHP_EOL
            . $e->getPrevious() . "|--- :( ---|" . PHP_EOL;
        Utils::escribirArchivoSeguimiento($trace, "errorDeConexion");
        /*$file = fopen("../tmp/errorDeConexion.txt", "a+");
        fwrite($file, $trace);
        fclose($file);*/

        echoResponse(202, array("error" => true, "mensaje" => MSG_CONEXION_DB));
    } else {
        $trace = PHP_EOL . 'Fecha: ' . date('d-m-Y H:i:s') . PHP_EOL . "Error: " . $e->getMessage() . PHP_EOL
            . "Linea: " . $e->getLine() . PHP_EOL . "Trace: " . $e->getTraceAsString() . PHP_EOL . "File: " . $e->getFile()
            . PHP_EOL . $e->getPrevious() . "|--- :( ---|" . PHP_EOL;
        Utils::escribirArchivoSeguimiento($trace, "error");
        /*$file = fopen("../tmp/error.txt", "a+");
        fwrite($file, $trace);
        fclose($file);*/

        #Insertamos el error en GT_ERRORES

        $errorDAO = new PagoDAO();
        $errorDAO->insertError("Error en la ejecución", "Error: " . $e->getMessage() . ". Archivo: " . $e->getFile() . ". Linea: " . $e->getLine());

        #Se valida si la petición es desde el front para responder, en caso contrario se redirecciona la vista
        if ($app->router->getCurrentRoute()->getName() == "" || $app->router->getCurrentRoute()->getName() == "paqueteRoaming" || $app->router->getCurrentRoute()->getName() == "ESTarjetasTokenizadas" )
            echoResponse(202, array("error" => true, "mensaje" => MSG_ERROR_EJECUCION));
        else
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . MSG_ERROR_EJECUCION);
    }

});

$app->notFound(function () use ($app) {

    $trace = PHP_EOL . 'Fecha petición ' . date('d-m-Y H:i:s') . PHP_EOL .
        "URL " . $app->request->getResourceUri() . PHP_EOL .
        "Method: " . $app->request->getMethod() . PHP_EOL .
        "Request " . json_encode($app->request->get()) . PHP_EOL;
    Utils::escribirArchivoSeguimiento($trace, "Peticion 404");

    header('Content-Type: text/html; charset=utf-8');
    $mensajeError = "Por favor realizar el consumo de manera correcta!";
	$app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $mensajeError);
});

$app->run();

/********** Funciones Utilitarias para procesar peticiones **********/

function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Código de la respuesta http
    $app->status($status_code);

    // Se retorna la respuesta en formato JSON
    echo json_encode(utf8Converter($response));
}

/**
 * Validación de Token enviado en la petición
 * @param \Slim\Route $route
 * @throws \Slim\Exception\Stop
 */

function authenticate(\Slim\Route $route)
{
    // Obteniendo cabeceras de la peticion
    #$headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    $data = $app->request->post();
    $hash = '';


    switch ($route->getName()) {
        case "hogar":
        case "postpago":
            if(isset($data["valor"]) && isset($data["numeroReferencia"]) && isset($data["fecha"])) {
                $hash = $data["valor"] . $data["numeroReferencia"] . $data["fecha"];
            }
            break;
        case "recarga":
        case "recargaRecurrente":
            if(isset($data["valor"]) && isset($data["numeroLinea"]) && isset($data["fecha"])) {
                $hash = $data["valor"] . $data["numeroLinea"] . $data["fecha"];
            }
            break;
        case "paquete":
        case "paqueteRecurrente":
            if(isset($data["valor"]) && isset($data["numeroLinea"]) && isset($data["fecha"])) {
                $hash = $data["valor"] . $data["numeroLinea"] . $data["codigoPaquete"] . $data["fecha"];
            }
            break;
        case "paqueteRoaming":
            $data = json_decode($app->request->getBody(),true);
            if(isset($data["fecha"]) && isset($data['data'][0]["msisdn"])) {
                $hash = $data["fecha"] . $data['data'][0]["msisdn"];
            }
            break;
        default:
            $hash = "Token no valido";
            break;
    }


    // Verificando la cabecera con la Autorizacion
    if (isset($data['token'])) {
        #$token = $headers['Token'];
        $token = strtoupper($data["token"]);

        // Validando KEY
        if (!($token == strtoupper(hash('sha256', $hash)))) {
            // Caso negativo
            $response['error'] = true;
            $response['mensaje'] = "Acceso denegado. Token inválido.";
            if($route->getName() == 'paqueteRoaming'){
                echoResponse(401, $response);
            }else{
                $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $response['mensaje']);
            }
            //echoResponse(401, $response);I

            $app->stop();
        } else {
            // Se ejecuta la petición
        }
    } else {
        // No viene Token en la petición
        $response['error'] = true;
        $response['mensaje'] = "Falta token de autorización";
        if($route->getName() == 'paqueteRoaming'){
            echoResponse(400, $response);
        }else{
            $app->redirect(NOMBRE_FRONT . "msgerror?mensaje=" . $response['mensaje']);
        }
        //echoResponse(400, $response);

        $app->stop();
    }
}

function externalAuthentication(\Slim\Route $route)
{
    // Obteniendo cabeceras de la peticion
    #$headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    $data = json_decode($app->request->getBody(),true);
    $hash = '';

    switch ($route->getName()) {
        case "ESTarjetasTokenizadas":
            if(isset($data["fecha"]) && isset($data["nombreUsuario"])){
                $hash = $data["fecha"] . $data["nombreUsuario"];
            }
            break;
    }

    // Verificando la cabecera con la Autorizacion
    if (isset($data['token'])) {
        #$token = $headers['Token'];
        $token = strtoupper($data["token"]);

        // Validando KEY
        if (!($token == strtoupper(hash('sha256', $hash)))) {
            // Caso negativo
            $response['error'] = true;
            $response['mensaje'] = "Acceso denegado. Token inválido.";
            echoResponse(401, $response);
            $app->stop();
        } else {
            // Se ejecuta la petición
        }
    } else {
        // No viene Token en la petición
        $response['error'] = true;
        $response['mensaje'] = "Falta token de autorización";
        echoResponse(400, $response);
        $app->stop();
    }
}

function debug(\Slim\Route $route)
{
    // Obteniendo cabeceras de la peticion
    #$headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    switch ($app->request->getMethod()) {
        case "POST":
            $data = $app->request->post();
            break;
        case "GET":
            $data = $app->request->get();
            break;
    }

    $data = empty($data) ? utf8Converter(json_decode($app->request->getBody(), true)) : $data;

    if (isset($data["test"])) {
        $trace = PHP_EOL . 'Fecha petición ' . date('d-m-Y H:i:s') . PHP_EOL . 'Parametros APP: ' . PHP_EOL . json_encode($data) . PHP_EOL;
        Utils::escribirArchivoSeguimiento($trace, "Peticion" . ucfirst($route->getName()));
        /*$file = fopen("../tmp/Peticion" . ucfirst($route->getName()) . ".txt", "a+");
        fwrite($file, $trace);
        fclose($file);*/

        /*echo '<pre>';
        print_r($data);
        print_r($app->request());
        echo '</pre>';
        $app->stop();*/
    } else if (MANTENIMIENTO == 1) {
        $app->redirect(NOMBRE_FRONT . "mantenimiento");
    } else if (DEBUG == 1) {

        $trace = PHP_EOL . 'Fecha petición ' . date('d-m-Y H:i:s') . PHP_EOL . 'Parametros APP: ' . PHP_EOL . json_encode($data) . PHP_EOL;
        Utils::escribirArchivoSeguimiento($trace, "Peticion" . ucfirst($route->getName()));
        /*$file = fopen("../tmp/Peticion" . ucfirst($route->getName()) . ".txt", "a+");
        fwrite($file, $trace);
        fclose($file);*/

        switch ($route->getName()) {
            case "hogar":
                //echoArray($data);
                break;
            case "postpago":
                //echoArray($data);
                break;
            case "recarga":
                //echoArray($data);
                break;
            case "recargaRecurrente":
                //echoArray($data);
                break;
            case "paquete":
                //echoArray($data);
                break;
            case "paqueteRecurrente":
                //echoArray($data);
               break;
            case "paqueteRoaming":
                //$data = json_decode($app->request->getBody(),true);
                //echoArray($data);
                break;
            default:
                break;
        }
    }
}

function echoArray($array)
{
    #Se obtiene el objeto de App para terminar la ejecución despues
    $app = \Slim\Slim::getInstance();

    #Se imprime el array
    echo '<pre>';
    print_r($array);
    echo '</pre>';

    $app->stop();
}

function validarFechaVencimiento($fechaLimite)
{
    # Vallida el formato ej. Feb 01/19
    if (strpos($fechaLimite, "/") !== false) {

        # Se define un array llave valor con los meses
        $meses = array("Ene" => "01", "Feb" => "02", "Mar" => "03", "Abr" => "04", "May" => "05", "Jun" => "06",
            "Jul" => "07", "Ago" => "08", "Sep" => "09", "Oct" => "10", "Nov" => "11", "Dic" => "12");

        # Se extrae informaciónn de la fecha para el formato //Feb 20/17
        $mes_diaAno = explode(" ", $fechaLimite);
        $diaAno = explode("/", $mes_diaAno[1]);

        $fecha_vencimiento = "20" . $diaAno[1] . $meses[$mes_diaAno[0]] . $diaAno[0];
    } else if($fechaLimite == 'INMEDIATO' || $fechaLimite == 'INMEDIATA'){
        $fecha_vencimiento = date("Ymd");
    } else {
        #Valida el formato 18-Feb-2019 y 18Feb2019

        if (strpos($fechaLimite, "-") !== false) {
            #Si tiene guiones se los quita
            $fechaLimite = str_replace("-", "", $fechaLimite);
        }
        $dia = substr($fechaLimite, 0, 2);
        $mes = substr($fechaLimite, 2, 3);
        $anio = substr($fechaLimite, 5, 4);

        $meses = array("Ene" => "01", "Feb" => "02", "Mar" => "03", "Abr" => "04", "May" => "05", "Jun" => "06",
            "Jul" => "07", "Ago" => "08", "Sep" => "09", "Oct" => "10", "Nov" => "11", "Dic" => "12");

        $fecha_vencimiento = $anio . $meses[$mes] . $dia;
    }

    return $fecha_vencimiento;
}

function utf8Converter($array)
{
	if (is_array($array) == true && $array != NULL) {
	    array_walk_recursive($array, function (&$item, $key) {
	        if (!mb_detect_encoding($item, 'utf-8', true)) {
	            $item = utf8_encode($item);
	        }
	    });

	    return $array;
	}else{
	    return utf8_encode($array);
    }
}

function validarTokenSeguridad($fechaToken)
{
    $response = array();

    // Validación de la Fecha
    if (!empty($fechaToken)) {
        $fechaPeticion = strtotime($fechaToken);
        $fechaLimite = (time() - (60 * LIMIT_TIME_RESPONSE));
        $fechaLimiteuP = (time() + (60 * LIMIT_TIME_RESPONSE));

        if ($fechaPeticion > $fechaLimiteuP || $fechaPeticion < $fechaLimite) {
            $response["error"] = true;
            $response["mensaje"] = "Tiempo límite excedido.";
        } else {
            $response["error"] = false;
        }
    }else{
        $response["error"] = true;
        $response["mensaje"] = "Parámetro Vacío";
    }

    return $response;
}

?>