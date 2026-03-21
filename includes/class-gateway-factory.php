<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Interface Acromidia_Gateway_Interface
 * Todos os provedores (Asaas, Stripe, Mercado Pago) precisam implementar este contrato.
 */
interface Acromidia_Gateway_Interface {
    
    /** Cria um cliente no Gateway */
    public function create_customer( $name, $cpf_cnpj, $email, $phone );
    
    /** Atualiza um cliente existente no Gateway. */
    public function update_customer( $gateway_customer_id, $data );
    
    /** Cria uma assinatura (recorrência) para o cliente. */
    public function create_subscription( $gateway_customer_id, $value, $next_due_date, $description );
    
    /** Lista pagamentos ou faturas de um cliente específico. */
    public function list_payments( $gateway_customer_id );
    
    /** Retorna uma lista de faturas atrasadas globais. */
    public function list_overdue_payments();
    
    /** Retorna o balanço / saldo disponível. */
    public function get_balance();
    
    /** Lista clientes paginados. */
    public function list_customers( $limit = 100, $offset = 0 );
    
    /** Resgatar QR Code PIX (se suportado pelo provedor). */
    public function get_payment_pix_qrcode( $payment_id );
    
    /** Buscar pagamentos pendentes por data de vencimento. */
    public function get_pending_payments_by_date( $date );
    
    /** Requisição customizada direta para a API do provedor. */
    public function request( $endpoint, $method = 'GET', $body = [] );
}

/**
 * Acromidia_Gateway_Factory
 * Determina qual provedor B2B acionar baseado na seleção do usuário em Configurações.
 */
class Acromidia_Gateway_Factory {

    /** Verifica se o Gateway atual possui chaves cadastradas */
    public static function is_configured() {
        $primary = Acromidia_Settings::get('primary_gateway');
        switch ( $primary ) {
            case 'mercadopago': return Acromidia_Settings::has('mp_access_token');
            case 'stripe':      return Acromidia_Settings::has('stripe_api_key');
            case 'pagarme':     return Acromidia_Settings::has('pagarme_api_key');
            case 'pagbank':     return Acromidia_Settings::has('pagbank_api_key');
            case 'asaas':
            default:            return Acromidia_Settings::has('asaas_api_key');
        }
    }

    /**
     * @return Acromidia_Gateway_Interface
     */
    public static function get_engine() {
        $primary = Acromidia_Settings::get('primary_gateway');
        
        switch ( $primary ) {
            case 'mercadopago':
                if ( ! class_exists( 'Acromidia_MercadoPago_API' ) ) {
                    require_once plugin_dir_path( __DIR__ ) . 'includes/class-mercadopago-api.php';
                }
                return new Acromidia_MercadoPago_API();
                
            case 'stripe':
                if ( ! class_exists( 'Acromidia_Stripe_API' ) ) {
                    require_once plugin_dir_path( __DIR__ ) . 'includes/class-stripe-api.php';
                }
                return new Acromidia_Stripe_API();
                
            case 'pagarme':
                if ( ! class_exists( 'Acromidia_Pagarme_API' ) ) {
                    require_once plugin_dir_path( __DIR__ ) . 'includes/class-pagarme-api.php';
                }
                return new Acromidia_Pagarme_API();
                
            case 'pagbank':
                if ( ! class_exists( 'Acromidia_PagBank_API' ) ) {
                    require_once plugin_dir_path( __DIR__ ) . 'includes/class-pagbank-api.php';
                }
                return new Acromidia_PagBank_API();
                
            case 'asaas':
            default:
                if ( ! class_exists( 'Acromidia_Asaas_API' ) ) {
                    require_once plugin_dir_path( __DIR__ ) . 'includes/class-asaas-api.php';
                }
                return new Acromidia_Asaas_API();
        }
    }
}
