<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Acromidia_MercadoPago_API implements Acromidia_Gateway_Interface {
    private $api_key;
    private $base_url = 'https://api.mercadopago.com';

    public function __construct() {
        $this->api_key = Acromidia_Settings::get( 'mp_access_token' );
    }

    public function request( $endpoint, $method = 'GET', $body = [] ) {
        $url = $this->base_url . $endpoint;
        $args = [
            'method'  => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 45,
        ];

        if ( ! empty( $body ) ) {
            $args['body'] = json_encode( $body );
        }

        $res = wp_remote_request( $url, $args );
        if ( is_wp_error( $res ) ) return [ 'error' => true, 'message' => $res->get_error_message() ];
        return json_decode( wp_remote_retrieve_body( $res ), true );
    }

    public function create_customer( $name, $cpf_cnpj, $email, $phone ) {
        $first_name = explode(' ', trim($name))[0] ?? $name;
        $last_name  = trim(strstr($name, ' '));
        return $this->request( '/v1/customers', 'POST', [
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'identification' => [
                'type' => strlen($cpf_cnpj) > 11 ? 'CNPJ' : 'CPF',
                'number' => preg_replace('/\D/', '', $cpf_cnpj)
            ]
        ]);
    }

    public function update_customer( $customer_id, $data ) {
         return $this->request( '/v1/customers/' . $customer_id, 'PUT', $data );
    }

    public function create_subscription( $customer_id, $value, $next_due_date, $description ) {
        // Mercado Pago preapproval integration (Assinaturas)
        return $this->request( '/preapproval', 'POST', [
            'payer_email' => $customer_id, // Usually MP requests email instead or payer_id
            'back_url' => get_site_url(),
            'reason' => $description,
            'auto_recurring' => [
                'frequency' => 1,
                'frequency_type' => 'months',
                'transaction_amount' => floatval($value),
                'currency_id' => 'BRL',
            ]
        ]);
    }

    public function list_payments( $customer_id ) {
        $res = $this->request( '/v1/payments/search?payer.id=' . $customer_id );
        $data = [];
        if(!empty($res['results'])) {
            foreach($res['results'] as $inv) {
                // Map MP statuses: approved, pending, rejected -> PENDING, RECEIVED, OVERDUE 
                $status = 'PENDING';
                if($inv['status'] == 'approved') $status = 'RECEIVED';
                if($inv['status'] == 'rejected' || $inv['status'] == 'cancelled') $status = 'OVERDUE';
                $data[] = [
                    'id' => $inv['id'],
                    'status' => $status,
                    'customer' => $customer_id,
                    'invoiceUrl' => $inv['transaction_details']['external_resource_url'] ?? ''
                ];
            }
        }
        return ['data' => $data];
    }

    public function list_overdue_payments() {
        return ['data' => []]; // Custom search not straightforward without iterators
    }

    public function get_balance() {
        // Balanço no MP é obtido em endpoint customizado ou dashboard do seller
        return ['balance' => 0];
    }

    public function list_customers( $limit = 100, $offset = 0 ) {
        $res = $this->request( "/v1/customers/search?limit={$limit}&offset={$offset}" );
        if(isset($res['results'])) $res['data'] = $res['results'];
        return $res;
    }

    public function get_payment_pix_qrcode( $payment_id ) {
        $res = $this->request('/v1/payments/' . $payment_id);
        $qr = $res['point_of_interaction']['transaction_data']['qr_code'] ?? '';
        return ['payload' => $qr];
    }

    public function get_pending_payments_by_date( $date ) {
        return ['data' => []];
    }
}
