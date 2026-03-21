<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Acromidia_Pagarme_API implements Acromidia_Gateway_Interface {
    private $api_key;
    private $base_url = 'https://api.pagar.me/core/v5';

    public function __construct() {
        $this->api_key = Acromidia_Settings::get( 'pagarme_api_key' );
    }

    public function request( $endpoint, $method = 'GET', $body = [] ) {
        $url = $this->base_url . $endpoint;
        $args = [
            'method'  => $method,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->api_key . ':'),
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 45,
        ];

        if ( ! empty( $body ) ) {
            $args['body'] = json_encode( $body );
        }

        $response = wp_remote_request( $url, $args );
        if ( is_wp_error( $response ) ) return [ 'error' => true, 'message' => $response->get_error_message() ];
        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    public function create_customer( $name, $cpf_cnpj, $email, $phone ) {
        return $this->request( '/customers', 'POST', [
            'name'  => $name,
            'email' => $email,
            'document' => preg_replace('/\D/', '', $cpf_cnpj),
            'type' => strlen($cpf_cnpj) > 11 ? 'company' : 'individual',
            'phones' => [
                'mobile_phone' => [
                    'country_code' => '55',
                    'area_code' => substr(preg_replace('/\D/', '', $phone), 2, 2) ?? '11',
                    'number' => substr(preg_replace('/\D/', '', $phone), 4) ?? '900000000'
                ]
            ]
        ]);
    }

    public function update_customer( $customer_id, $data ) {
         return $this->request( '/customers/' . $customer_id, 'PUT', $data );
    }

    public function create_subscription( $customer_id, $value, $next_due_date, $description ) {
        return $this->request( '/subscriptions', 'POST', [
            'customer_id' => $customer_id,
            'payment_method' => 'boleto', // Ou pix quando suportado diretamente
            'interval' => 'month',
            'interval_count' => 1,
            'billing_type' => 'prepaid',
            'installments' => 1,
            'pricing_scheme' => [
                'scheme_type' => 'unit',
                'price' => intval($value * 100)
            ],
        ]);
    }

    public function list_payments( $customer_id ) {
        $res = $this->request( '/invoices?customer_id=' . $customer_id );
        $data = [];
        if(!empty($res['data'])) {
            foreach($res['data'] as $inv) {
                // Map status: pending, paid, canceled, failed
                $status = 'PENDING';
                if($inv['status'] == 'paid') $status = 'RECEIVED';
                if($inv['status'] == 'canceled' || $inv['status'] == 'failed') $status = 'OVERDUE';
                $data[] = [
                    'id' => $inv['id'],
                    'status' => $status,
                    'customer' => $customer_id,
                    'invoiceUrl' => $inv['url']
                ];
            }
        }
        return ['data' => $data];
    }

    public function list_overdue_payments() {
        return $this->request( '/invoices?status=canceled' ); 
    }

    public function get_balance() {
        $res = $this->request( '/balance/default' );
        return ['balance' => ($res['available_amount'] ?? 0) / 100];
    }

    public function list_customers( $limit = 100, $offset = 0 ) {
        // Pagar.me v5 uses page and size
        $page = ($offset / $limit) + 1;
        return $this->request( "/customers?size={$limit}&page={$page}" );
    }

    public function get_payment_pix_qrcode( $payment_id ) {
        // Fetch charge embedded in invoice if PIX was selected
        return ['payload' => 'Pix Code Não Nativo'];
    }

    public function get_pending_payments_by_date( $date ) {
        return ['data' => []];
    }
}
