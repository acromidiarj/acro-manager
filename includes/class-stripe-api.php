<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Acromidia_Stripe_API implements Acromidia_Gateway_Interface {
    private $api_key;
    private $base_url = 'https://api.stripe.com/v1';

    public function __construct() {
        $this->api_key = Acromidia_Settings::get( 'stripe_api_key' );
    }

    public function request( $endpoint, $method = 'GET', $body = [] ) {
        $url = $this->base_url . $endpoint;
        $args = [
            'method'  => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'timeout' => 45,
        ];

        if ( ! empty( $body ) && $method === 'POST' ) {
            $args['body'] = http_build_query( $body );
        }

        $response = wp_remote_request( $url, $args );
        if ( is_wp_error( $response ) ) return [ 'error' => true, 'message' => $response->get_error_message() ];
        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    public function create_customer( $name, $cpf_cnpj, $email, $phone ) {
        return $this->request( '/customers', 'POST', [
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
            'metadata' => [ 'cpfCnpj' => $cpf_cnpj ]
        ]);
    }

    public function update_customer( $customer_id, $data ) {
        return $this->request( '/customers/' . $customer_id, 'POST', $data );
    }

    public function create_subscription( $customer_id, $value, $next_due_date, $description ) {
        // Stripe usually requires a Price ID or Price Data.
        return $this->request( '/subscriptions', 'POST', [
            'customer' => $customer_id,
            'items[0][price_data][currency]' => 'BRL',
            'items[0][price_data][product_data][name]' => $description,
            'items[0][price_data][unit_amount]' => intval($value * 100),
            'items[0][price_data][recurring][interval]' => 'month',
            'collection_method' => 'send_invoice',
            'days_until_due' => 3
        ]);
    }

    public function list_payments( $customer_id ) {
        $res = $this->request( '/invoices?customer=' . $customer_id );
        // Format to interface standard
        $data = [];
        if(!empty($res['data'])) {
            foreach($res['data'] as $inv) {
                $status = $inv['status'] === 'open' && time() > $inv['due_date'] ? 'OVERDUE' : ($inv['status'] === 'paid' ? 'RECEIVED' : 'PENDING');
                $data[] = [
                    'id' => $inv['id'],
                    'status' => $status,
                    'customer' => $inv['customer'],
                    'invoiceUrl' => $inv['hosted_invoice_url']
                ];
            }
        }
        return ['data' => $data];
    }

    public function list_overdue_payments() {
        return $this->request( '/invoices?status=open' ); // FIlter manually below if needed
    }

    public function get_balance() {
        $res = $this->request( '/balance' );
        $val = 0;
        if(!empty($res['available'][0]['amount'])) {
            $val = $res['available'][0]['amount'] / 100;
        }
        return ['balance' => $val];
    }

    public function list_customers( $limit = 100, $offset = 0 ) {
        return $this->request( '/customers?limit=' . $limit );
    }

    public function get_payment_pix_qrcode( $payment_id ) {
        // Not standard in Stripe without specific payment intents, returning empty
        return ['payload' => 'Pix via Stripe (Acessar Fatura)'];
    }

    public function get_pending_payments_by_date( $date ) {
        return ['data' => []]; // Simplified
    }
}
