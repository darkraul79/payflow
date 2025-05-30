<?php

namespace App\Helpers;

use App\Models\Donation;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

/**
 * NOTA SOBRE LA LICENCIA DE USO DEL SOFTWARE
 *
 * El uso de este software está sujeto a las Condiciones de uso de software que
 * se incluyen en el paquete en el documento "Aviso Legal.pdf". También puede
 * obtener una copia en la siguiente url:
 * http://www.redsys.es/wps/portal/redsys/publica/areadeserviciosweb/descargaDeDocumentacionYEjecutables
 *
 * Redsys es titular de todos los derechos de propiedad intelectual e industrial
 * del software.
 *
 * Quedan expresamente prohibidas la reproducción, la distribución y la
 * comunicación pública, incluida su modalidad de puesta a disposición con fines
 * distintos a los descritos en las Condiciones de uso.
 *
 * Redsys se reserva la posibilidad de ejercer las acciones legales que le
 * correspondan para hacer valer sus derechos frente a cualquier infracción de
 * los derechos de propiedad intelectual y/o industrial.
 *
 * Redsys Servicios de Procesamiento, S.L., CIF B85955367
 */
class RedsysAPI
{
    /****** Array de DatosEntrada ******/
    public array $vars_pay = [];

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // //////////					FUNCIONES AUXILIARES:							  ////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////

    public static function getRedsysUrl(): string
    {
        return config('redsys.enviroment') == 'test' ? 'https://sis-t.redsys.es:25443/sis/realizarPago' : 'https://sis.redsys.es/sis/realizarPago';
    }

    /******  Get parameter ******/
    public function getParameter($key)
    {
        return $this->vars_pay[$key];
    }

    public function decodeMerchantParameters($datos): string
    {
        // Se decodifican los datos Base64
        $decodec = $this->base64_url_decode($datos);
        // Los datos decodificados se pasan al array de datos
        $this->stringToArray($decodec);

        return $decodec;
    }

    public function base64_url_decode($input): false|string
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // //////////	   FUNCIONES PARA LA GENERACIÓN DEL FORMULARIO DE PAGO:			  ////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////

    /******  Convertir String en Array ******/

    public function stringToArray($datosDecod): void
    {
        $this->vars_pay = json_decode((string)$datosDecod, true); // (PHP 5 >= 5.2.0)
    }

    public function getFormDirectPay(Model $model): array
    {

        $this->setCommonParameters();

        $this->setParameter('DS_MERCHANT_AMOUNT', $model->totalRedsys);
        $this->setParameter('DS_MERCHANT_ORDER', $model->number);

        if ($model instanceof Donation) {
            $this->setParameter('DS_MERCHANT_URLOK', route('donation.response'));
            $this->setParameter('DS_MERCHANT_URLKO', route('donation.response'));
        } else {
            $this->setParameter('DS_MERCHANT_URLOK', route('pedido.response'));
            $this->setParameter('DS_MERCHANT_URLKO', route('pedido.response'));
        }

        $this->setNotificationUrl($model);


        return [
            'Ds_MerchantParameters' => $this->createMerchantParameters(),
            'Ds_Signature' => $this->createMerchantSignature(config('redsys.key')),
            'Ds_SignatureVersion' => config('redsys.version'),
            'Raw' => $this->vars_pay,
        ];

    }

    public function setCommonParameters(): void
    {
        $this->setParameter('DS_MERCHANT_MERCHANTCODE', config('redsys.merchantcode'));
        $this->setParameter('DS_MERCHANT_CURRENCY', config('redsys.currency'));
        $this->setParameter('DS_MERCHANT_TRANSACTIONTYPE', config('redsys.transactiontype'));
        $this->setParameter('DS_MERCHANT_MERCHANTNAME', config('redsys.tradename'));
        $this->setParameter('DS_MERCHANT_TERMINAL', config('redsys.terminal'));
    }

    /******  Set parameter ******/
    public function setParameter($key, $value): void
    {
        $this->vars_pay[$key] = $value;
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////// FUNCIONES PARA LA RECEPCIÓN DE DATOS DE PAGO (Notif, URLOK y URLKO): ////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////
    // ////////////////////////////////////////////////////////////////////////////////////////////

    public function setNotificationUrl(Model $model): void
    {
        if (!app()->isLocal() && !app()->environment('testing')) { // Si no estoy en local, añado la URL de notificacion de redSys
            $this->setParameter('DS_MERCHANT_MERCHANTURL',
                $model instanceof Order ? route('pedido.response') : route('donation.response')
            );
        }
    }

    public function createMerchantParameters(): string
    {
        // Se transforma el array de datos en un objeto Json
        $json = $this->arrayToJson();

        // Se codifican los datos Base64
        return $this->encodeBase64($json);
    }

    /******  Convertir Array en Objeto JSON ******/
    public function arrayToJson(): false|string
    {
        // (PHP 5 >= 5.2.0)
        return json_encode($this->vars_pay);
    }

    public function encodeBase64($data): string
    {
        return base64_encode((string)$data);
    }

    public function createMerchantSignature($key): string
    {
        // Se decodifica la clave Base64
        $key = $this->decodeBase64($key);
        // Se genera el parámetro Ds_MerchantParameters
        $ent = $this->createMerchantParameters();
        // Se diversifica la clave con el Número de Pedido
        $key = $this->encrypt_3DES($this->getOrder(), $key);
        // MAC256 del parámetro Ds_MerchantParameters
        $res = $this->mac256($ent, $key);

        // Se codifican los datos Base64
        return $this->encodeBase64($res);
    }

    public function decodeBase64($data): false|string
    {
        return base64_decode((string)$data);
    }

    /******  3DES Function  ******/
    public function encrypt_3DES($message, $key): string
    {
        // Se cifra
        $l = ceil(strlen((string)$message) / 8) * 8;

        return substr(openssl_encrypt($message . str_repeat("\0", $l - strlen((string)$message)), 'des-ede3-cbc', $key,
            OPENSSL_RAW_DATA, "\0\0\0\0\0\0\0\0"), 0, $l);

    }

    /****** Obtener Número de pedido ******/
    public function getOrder()
    {
        if (empty($this->vars_pay['DS_MERCHANT_ORDER'])) {
            $numPedido = $this->vars_pay['Ds_Merchant_Order'];
        } else {
            $numPedido = $this->vars_pay['DS_MERCHANT_ORDER'];
        }

        return $numPedido;
    }

    /******  MAC Function ******/
    public function mac256($ent, $key): string
    {
        // (PHP 5 >= 5.1.2)
        return hash_hmac('sha256', (string)$ent, (string)$key, true);
    }

    public function getFormPagoRecurrente(Donation $donation, $isNew = true): array
    {

        $this->setCommonParameters();
        $this->setParameter('DS_MERCHANT_AMOUNT', $donation->totalRedsys);
        $this->setParameter('DS_MERCHANT_ORDER', $donation->number);
        $this->setParameter('DS_MERCHANT_URLOK', route('donation.response'));
        $this->setParameter('DS_MERCHANT_URLKO', route('donation.response'));
        $this->setParameter('DS_MERCHANT_IDENTIFIER', 'REQUIRED');
        $this->setParameter('DS_MERCHANT_COF_INI', 'S');
        $this->setParameter('DS_MERCHANT_COF_TYPE', 'R');
        $this->setNotificationUrl($donation);

        if (!$isNew) {
            $this->setParameter('DS_MERCHANT_IDENTIFIER', $donation->info->Ds_Merchant_Identifier);
            $this->setParameter('DS_MERCHANT_COF_TXNID', $donation->info->Ds_Merchant_Cof_Txnid);
            $this->setParameter('DS_MERCHANT_EXCEP_SCA', 'MIT');
            $this->setParameter('DS_MERCHANT_DIRECTPAYMENT', 'true');
        }


        return [
            'Ds_MerchantParameters' => $this->createMerchantParameters(),
            'Ds_Signature' => $this->createMerchantSignature(config('redsys.key')),
            'Ds_SignatureVersion' => config('redsys.version'),
            'Raw' => $this->vars_pay,
        ];

    }

    public function checkSignature($key, $postData): bool
    {

        return hash_equals($key, $postData);
    }

    public function createMerchantSignatureNotif($key, $datos): string
    {
        // Se decodifica la clave Base64
        $key = $this->decodeBase64($key);
        // Se decodifican los datos Base64
        $decodec = $this->base64_url_decode($datos);
        // Los datos decodificados se pasan al array de datos
        $this->stringToArray($decodec);
        // Se diversifica la clave con el Número de Pedido
        $key = $this->encrypt_3DES($this->getOrderNotif(), $key);
        // MAC256 del parámetro Ds_Parameters que envía Redsys
        $res = $this->mac256($datos, $key);

        // Se codifican los datos Base64
        return $this->base64_url_encode($res);
    }

    /****** Obtener Número de pedido ******/
    public function getOrderNotif()
    {
        if (empty($this->vars_pay['Ds_Order'])) {
            $numPedido = $this->vars_pay['DS_ORDER'];
        } else {
            $numPedido = $this->vars_pay['Ds_Order'];
        }

        return $numPedido;
    }

    /******  Base64 Functions  ******/
    public function base64_url_encode($input): string
    {
        return strtr(base64_encode((string)$input), '+/', '-_');
    }
}
