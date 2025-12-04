<?php

namespace Darkraul79\Payflow\Gateways;

use Darkraul79\Payflow\Contracts\GatewayInterface;
use DateTime;
use DateTimeZone;
use RuntimeException;

class RedsysGateway implements GatewayInterface
{
    protected array $parameters = [];

    protected string $merchantKey;

    protected string $merchantCode;

    protected string $terminal;

    protected string $currency;

    protected string $transactionType;

    protected string $tradeName;

    protected string $environment;

    protected string $version;

    public function __construct()
    {
        // Usar la configuración del proyecto principal, no del paquete
        $this->merchantKey = config('redsys.key') ?? config('payflow.gateways.redsys.key') ?? '';
        $this->merchantCode = config('redsys.merchantcode') ?? config('payflow.gateways.redsys.merchant_code') ?? '';
        $this->terminal = config('redsys.terminal') ?? config('payflow.gateways.redsys.terminal') ?? '1';
        $this->currency = config('redsys.currency') ?? config('payflow.gateways.redsys.currency') ?? '978'; // EUR
        $this->transactionType = config('redsys.transactiontype') ?? config('payflow.gateways.redsys.transaction_type') ?? '0';
        $this->tradeName = config('redsys.tradename') ?? config('payflow.gateways.redsys.trade_name') ?? 'Laravel App';
        $this->environment = config('redsys.enviroment') ?? config('payflow.gateways.redsys.environment') ?? 'test';
        $this->version = 'HMAC_SHA256_V1';

        // Validar configuración crítica
        if (empty($this->merchantKey)) {
            throw new RuntimeException('Redsys merchant key is not configured. Please set REDSYS_KEY in your .env file.');
        }

        if (empty($this->merchantCode)) {
            throw new RuntimeException('Redsys merchant code is not configured. Please set REDSYS_MERCHANT_CODE in your .env file.');
        }
    }

    /**
     * Convert amount from Redsys format to float
     */
    public static function convertAmountFromRedsys(string $amount): float
    {
        return (float) ($amount / 100);
    }

    public function createPayment(float $amount, string $orderId, array $options = []): array
    {
        $this->setCommonParameters();

        $this->setParameter('DS_MERCHANT_AMOUNT', $this->convertAmountToRedsys($amount));
        $this->setParameter('DS_MERCHANT_ORDER', $orderId);
        $this->setParameter('Ds_Order', $orderId); // duplicado para callbacks y firma

        // Optional parameters
        if (isset($options['url_ok'])) {
            $this->setParameter('DS_MERCHANT_URLOK', $options['url_ok']);
        }

        if (isset($options['url_ko'])) {
            $this->setParameter('DS_MERCHANT_URLKO', $options['url_ko']);
        }

        if (isset($options['url_notification'])) {
            $this->setParameter('DS_MERCHANT_MERCHANTURL', $options['url_notification']);
        }

        if (isset($options['payment_method']) && strtolower($options['payment_method']) === 'bizum') {
            $this->setParameter('Ds_Merchant_Paymethods', 'z');
        }

        // Recurring payment parameters
        if (isset($options['recurring'])) {
            $this->setRecurringParameters($options['recurring']);
        }

        return [
            'Ds_MerchantParameters' => $this->createMerchantParameters(),
            'Ds_Signature' => $this->createMerchantSignature(),
            'Ds_SignatureVersion' => $this->version,
            'form_url' => $this->getPaymentUrl(),
            'raw_parameters' => $this->parameters,
        ];
    }

    protected function setCommonParameters(): void
    {
        $this->setParameter('DS_MERCHANT_MERCHANTCODE', $this->merchantCode);
        $this->setParameter('DS_MERCHANT_CURRENCY', $this->currency);
        $this->setParameter('DS_MERCHANT_TRANSACTIONTYPE', $this->transactionType);
        $this->setParameter('DS_MERCHANT_MERCHANTNAME', $this->tradeName);
        $this->setParameter('DS_MERCHANT_TERMINAL', $this->terminal);
    }

    protected function setParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    protected function convertAmountToRedsys(float $amount): string
    {
        return (string) round($amount * 100);
    }

    protected function setRecurringParameters(array $recurring): void
    {
        if (isset($recurring['identifier'])) {
            $this->setParameter('DS_MERCHANT_IDENTIFIER', $recurring['identifier']);
        }

        if (isset($recurring['cof_ini'])) {
            $this->setParameter('DS_MERCHANT_COF_INI', $recurring['cof_ini']);
        }

        if (isset($recurring['cof_type'])) {
            $this->setParameter('DS_MERCHANT_COF_TYPE', $recurring['cof_type']);
        }

        if (isset($recurring['cof_txnid'])) {
            $this->setParameter('Ds_Merchant_Cof_Txnid', $recurring['cof_txnid']);
        }

        if (isset($recurring['excep_sca'])) {
            $this->setParameter('DS_MERCHANT_EXCEP_SCA', $recurring['excep_sca']);
        }

        if (isset($recurring['direct_payment'])) {
            $this->setParameter('DS_MERCHANT_DIRECTPAYMENT', $recurring['direct_payment']);
        }
    }

    protected function createMerchantParameters(): string
    {
        $json = json_encode($this->parameters);

        return base64_encode($json);
    }

    protected function createMerchantSignature(): string
    {
        $key = base64_decode($this->merchantKey);
        $merchantParameters = $this->createMerchantParameters();
        $key = $this->encrypt3DES($this->getOrder(), $key);
        $signature = $this->mac256($merchantParameters, $key);

        return $this->base64UrlEncode($signature);
    }

    protected function encrypt3DES(string $message, string $key): string
    {
        $length = ceil(strlen($message) / 8) * 8;

        return substr(
            openssl_encrypt(
                $message.str_repeat("\0", $length - strlen($message)),
                'des-ede3-cbc',
                $key,
                OPENSSL_RAW_DATA,
                "\0\0\0\0\0\0\0\0"
            ),
            0,
            $length
        );
    }

    protected function getOrder(): string
    {
        return $this->parameters['DS_MERCHANT_ORDER'] ?? $this->parameters['Ds_Merchant_Order'] ?? '';
    }

    protected function mac256(string $data, string $key): string
    {
        return hash_hmac('sha256', $data, $key, true);
    }

    protected function base64UrlEncode(string $input): string
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    public function getPaymentUrl(): string
    {
        if ($this->environment === 'test') {
            return 'https://sis-t.redsys.es:25443/sis/realizarPago';
        }

        return 'https://sis.redsys.es/sis/realizarPago';
    }

    public function processCallback(array $data): array
    {
        if (! isset($data['Ds_MerchantParameters']) || ! isset($data['Ds_Signature'])) {
            throw new RuntimeException('Invalid Redsys callback data');
        }

        $merchantParameters = $data['Ds_MerchantParameters'];
        $signatureReceived = $data['Ds_Signature'];

        $decodedData = $this->decodeMerchantParameters($merchantParameters);
        $signature = $this->createMerchantSignatureNotification($merchantParameters);

        return [
            'decoded_data' => $decodedData,
            'signature' => $signature,
            'signature_received' => $signatureReceived,
            'is_valid' => $this->verifySignature($data),
        ];
    }

    protected function decodeMerchantParameters(string $merchantParameters): array
    {
        $decoded = base64_decode(strtr($merchantParameters, '-_', '+/'), strict: true);

        if ($decoded === false) {
            throw new RuntimeException('Parámetros MerchantParameters con formato base64 inválido');
        }

        $params = json_decode($decoded, true);

        if (! is_array($params)) {
            throw new RuntimeException('Parámetros MerchantParameters con JSON inválido');
        }

        return $params;
    }

    protected function createMerchantSignatureNotification(string $merchantParameters): string
    {
        $key = base64_decode($this->merchantKey);
        $decodedData = $this->decodeMerchantParameters($merchantParameters);
        $orderId = $decodedData['Ds_Order'] ?? $decodedData['DS_ORDER'] ?? '';
        $key = $this->encrypt3DES($orderId, $key);
        $signature = $this->mac256($merchantParameters, $key);

        return $this->base64UrlEncode($signature);
    }

    public function verifySignature(array $data): bool
    {
        if (! isset($data['Ds_MerchantParameters']) || ! isset($data['Ds_Signature'])) {
            return false;
        }

        $merchantParameters = $data['Ds_MerchantParameters'];
        $signatureReceived = $data['Ds_Signature'];

        // createMerchantSignatureNotification() YA devuelve URL-safe base64
        $signature = $this->createMerchantSignatureNotification($merchantParameters);

        return hash_equals($signature, $signatureReceived);
    }

    public function isSuccessful(array $data): bool
    {
        if (! $this->verifySignature($data)) {
            return false;
        }

        $decodedData = $this->decodeMerchantParameters($data['Ds_MerchantParameters']);
        $responseCode = (int) ($decodedData['Ds_Response'] ?? 9999);

        return $responseCode <= 99;
    }

    public function getErrorMessage(array $data): string
    {
        if (! $this->verifySignature($data)) {
            return 'Firma no válida';
        }

        $decodedData = $this->decodeMerchantParameters($data['Ds_MerchantParameters']);
        $responseCode = $decodedData['Ds_Response'] ?? '';

        return $this->getRedsysErrorMessage($responseCode);
    }

    protected function getRedsysErrorMessage(string $code): string
    {
        $errors = [
            '0101' => 'Tarjeta caducada',
            '0102' => 'Tarjeta en excepción transitoria o bajo sospecha de fraude',
            '0104' => 'Operación no permitida para esa tarjeta o terminal',
            '0106' => 'Intentos de PIN excedidos',
            '0116' => 'Disponible insuficiente',
            '0118' => 'Tarjeta no registrada',
            '0125' => 'Tarjeta no efectiva',
            '0129' => 'Código de seguridad (CVV2/CVC2) incorrecto',
            '0180' => 'Tarjeta ajena al servicio',
            '0184' => 'Error en la autenticación del titular',
            '0190' => 'Denegación sin especificar motivo',
            '0191' => 'Fecha de caducidad errónea',
            '0202' => 'Tarjeta en excepción transitoria o bajo sospecha de fraude',
            '0904' => 'Comercio no registrado en FUC',
            '0909' => 'Error de sistema',
            '0913' => 'Pedido repetido',
            '0944' => 'Sesión incorrecta',
            '0950' => 'Operación de devolución no permitida',
            '9064' => 'Número de posiciones de la tarjeta incorrecto',
            '9078' => 'No existe método de pago válido para su tarjeta',
            '9093' => 'Tarjeta no existente',
            '9094' => 'Rechazo de los servidores internacionales',
            '9104' => 'Comercio con "titular seguro" y titular sin clave',
            '9218' => 'El comercio no permite operaciones seguras por entrada /operaciones',
            '9253' => 'Tarjeta no cumple el check-digit',
            '9256' => 'El comercio no puede realizar preautorizaciones',
            '9257' => 'Esta tarjeta no permite operativa de preautorizaciones',
            '9261' => 'Operación detenida por superar el control de restricciones',
            '9912' => 'Emisor no disponible',
            '9913' => 'Error en la confirmación que el comercio envía al TPV Virtual',
            '9914' => 'Confirmación "KO" del comercio',
            '9915' => 'A petición del usuario se ha cancelado el pago',
            '9928' => 'Anulación de autorización en diferido realizada por el SIS',
            '9929' => 'Anulación de autorización en diferido realizada por el comercio',
        ];

        return $errors[$code] ?? "Error desconocido ($code)";
    }

    public function refund(string $transactionId, float $amount): bool
    {
        // TODO: Implement refund functionality
        return false;
    }

    public function getName(): string
    {
        return 'redsys';
    }

    /**
     * Create INSITE payment parameters (for tokenized card payments)
     */
    public function createInsitePayment(float $amount, string $orderId, array $options = []): array
    {
        $this->setCommonParameters();

        $this->setParameter('DS_MERCHANT_AMOUNT', $this->convertAmountToRedsys($amount));
        $this->setParameter('DS_MERCHANT_ORDER', $orderId);
        $this->setParameter('Ds_Order', $orderId);

        // INSITE requiere EMV3DS para autenticación 3D Secure 2.0
        if (config('redsys.emv3ds_enabled', true)) {
            $emv3dsData = $this->createEMV3DSData($options);
            $this->setParameter('DS_MERCHANT_EMV3DS', json_encode($emv3dsData));
        }

        // Optional parameters
        if (isset($options['url_notification'])) {
            $this->setParameter('DS_MERCHANT_MERCHANTURL', $options['url_notification']);
        }

        // Recurring payment parameters
        if (isset($options['recurring'])) {
            $this->setRecurringParameters($options['recurring']);
        }

        return [
            'Ds_MerchantParameters' => $this->createMerchantParameters(),
            'Ds_Signature' => $this->createMerchantSignature(),
            'Ds_SignatureVersion' => $this->version,
            'raw_parameters' => $this->parameters,
        ];
    }

    /**
     * Create EMV3DS data for 3D Secure 2.0 authentication
     */
    protected function createEMV3DSData(array $options): array
    {
        return [
            'threeDSInfo' => 'CardData',
            'browserAcceptHeader' => $options['browser_accept_header'] ?? $_SERVER['HTTP_ACCEPT'] ?? 'text/html',
            'browserUserAgent' => $options['browser_user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '',
            'browserJavaEnabled' => $options['browser_java_enabled'] ?? false,
            'browserLanguage' => $options['browser_language'] ?? 'es-ES',
            'browserColorDepth' => $options['browser_color_depth'] ?? '24',
            'browserScreenHeight' => $options['browser_screen_height'] ?? '1080',
            'browserScreenWidth' => $options['browser_screen_width'] ?? '1920',
            'browserTZ' => $options['browser_tz'] ?? (string) (new DateTimeZone(date_default_timezone_get()))->getOffset(new DateTime()),
        ];
    }

    /**
     * Send REST payment with tokenized card data from INSITE
     */
    public function sendRestPaymentWithToken(string $token, array $emv3dsData = []): array
    {
        // Añadir el token a los parámetros
        $this->setParameter('DS_MERCHANT_IDOPER', $token);

        // Si hay datos EMV3DS adicionales del challenge, añadirlos
        if (! empty($emv3dsData)) {
            $currentEmv3ds = json_decode($this->parameters['DS_MERCHANT_EMV3DS'] ?? '{}', true);
            $mergedEmv3ds = array_merge($currentEmv3ds, $emv3dsData);
            $this->setParameter('DS_MERCHANT_EMV3DS', json_encode($mergedEmv3ds));
        }

        return $this->sendRestPayment();
    }

    /**
     * Send payment request via REST API (for direct payments)
     */
    public function sendRestPayment(): array
    {
        $data = [
            'Ds_MerchantParameters' => $this->createMerchantParameters(),
            'Ds_Signature' => $this->createMerchantSignature(),
            'Ds_SignatureVersion' => $this->version,
        ];

        $url = $this->environment === 'test'
            ? 'https://sis-t.redsys.es:25443/sis/rest/trataPeticionREST'
            : 'https://sis.redsys.es/sis/rest/trataPeticionREST';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new RuntimeException("Redsys REST API error: $error (HTTP $httpCode)");
        }

        return json_decode($response, true);
    }

    /**
     * Get INSITE JavaScript library URL
     */
    public function getInsiteLibraryUrl(): string
    {
        if ($this->environment === 'test') {
            return config('redsys.insite_library_url_test', 'https://sis-t.redsys.es:25443/sis/NC/sandbox/redsysV3.js');
        }

        return config('redsys.insite_library_url_prod', 'https://sis.redsys.es/sis/NC/redsysV3.js');
    }

    /**
     * Process INSITE callback/response
     */
    public function processInsiteResponse(array $response): array
    {
        if (! isset($response['Ds_MerchantParameters'])) {
            throw new RuntimeException('Invalid INSITE response data');
        }

        $merchantParameters = $response['Ds_MerchantParameters'];
        $decodedData = $this->decodeMerchantParameters($merchantParameters);

        // Verificar si requiere challenge 3DS
        if (isset($decodedData['protocolVersion']) && isset($decodedData['acsURL'])) {
            return [
                'requires_challenge' => true,
                'acs_url' => $decodedData['acsURL'],
                'creq' => $decodedData['creq'] ?? '',
                'protocol_version' => $decodedData['protocolVersion'],
                'decoded_data' => $decodedData,
            ];
        }

        // Pago completado
        return [
            'requires_challenge' => false,
            'decoded_data' => $decodedData,
            'is_successful' => $this->isSuccessfulResponse($decodedData),
            'error_message' => $this->isSuccessfulResponse($decodedData) ? null : $this->getRedsysErrorMessage($decodedData['Ds_Response'] ?? '9999'),
        ];
    }

    /**
     * Check if response is successful based on decoded data
     */
    protected function isSuccessfulResponse(array $decodedData): bool
    {
        $responseCode = (int) ($decodedData['Ds_Response'] ?? 9999);

        return $responseCode <= 99;
    }
}
