<?php

/*
 *add cron job in wordpress manually // deprecated
*/
/*
///Hook into that action that'll fire every six hours
//add_action( 'myprefix_cron_hook', 'myprefix_cron_function' );

//create your function, that runs on cron
function myprefix_cron_function() {
   wp_mail( 'luisfelipesantoszxcv@hotmail.com', 'Cron wp', 'Automatic scheduled email from WordPress to test cron');
}*/
//----------------------------------------------------


/*
 *add cron job in wordpress by rest api in https://cron-job.org a cada 10 mins
 https://webdentistas.com.br/wp-json/wp/v2/crons // active
*/
function wp_register_cron_endpoint() {
  	register_rest_route( 'wp/v2', '/active', array(
      'methods' => 'POST',
      'callback' =>  'activeAssinaturas'
  	)); 
	register_rest_route( 'wp/v2', '/update', array(
      'methods' => 'POST',
      'callback' =>  'updateAssinaturas'
  	)); 
	register_rest_route( 'wp/v2', '/cancel', array(
      'methods' => 'POST',
      'callback' =>  'cancelAssinaturas'
  	)); 
}
add_action( 'rest_api_init', 'wp_register_cron_endpoint' );
#-------------------------------------------

$dirn = array_slice(explode("/", dirname(__FILE__)),0,-1);
$dirna = implode("/",$dirn);

require_once  $dirna.'/vendor/autoload.php';
use CWG\PagSeguro\PagSeguroAssinaturas;

function verifi_status($idAssinatura) {
        $assinaturas = get_post($idAssinatura);
        $pagSeguroSettings = get_option('woocommerce_pagseguro_settings');
        //Fornece as credenciais da api observando se o plugin está no modo sandbox ou não
        if ('yes' == $pagSeguroSettings['sandbox']) {
            $accessInfo = [
                'email' => $pagSeguroSettings['sandbox_email'],
                'token' => $pagSeguroSettings['sandbox_token']
            ];
            $sandBox = true;
        } else {
            $accessInfo = [
                'email' => $pagSeguroSettings['email'],
                'token' => $pagSeguroSettings['token']
            ];
            $sandBox = false;
        }
        $pagseguro = new PagSeguroAssinaturas($accessInfo['email'], $accessInfo['token'], $sandBox);

        try {
            //$data =get_post_meta($idAssinatura, 'aprovation', true);
			//update_post_meta($idAssinatura, 'active', $enable ? 1 : 0);
			return $pagseguro->consultaAssinatura($assinaturas->post_title);
        } catch (Exception $e) {
            return $e->getMessage();
        }
}

function activeAssinaturas(){
  	//wp_mail( 'luisfelipesantoszxcv@hotmail.com', 'Cron api', 'Automatic scheduled email from WordPress to test cron');
  	//
  	$in_aprovation = array(
        'key' => 'aprovation',
        'value' => 1,
        'compare' => '='
    );
  	$assinaturas = get_posts(array(
            'posts_per_page'    => 300,
            'offset'            => 0,
            'category'          => '',
            'category_name'     => '',
            'orderby'           => 'ID',
            'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
            'order'             => 'DESC',
            'post_type'         => 'assinatura',
			'meta_query' => array(
				$in_aprovation,
			)
            /*'meta_key'        => 'partner-submission-status',
            'meta_value'        => 'goedgekeurd',*/
    ));
	$i = 0;
	foreach ($assinaturas as $assinatura){
		$meta = "";
		$meta = get_post_meta($assinatura->ID);
		$assinaturas[$i]->meta = $meta;
		
		$verification = [];
		$verification = verifi_status($assinatura->ID);
		$post_author_id = get_post_field( 'post_author', $assinatura->ID );
		
		if ($verification["status"] == "ACTIVE") {
			
			update_user_meta( $post_author_id, 'status', 1 );
			update_post_meta($assinatura->ID, 'active', 1);
			update_post_meta($assinatura->ID, 'aprovation', 0);
		}
		
		$assinaturas[$i]->data_pag = $verification;
		
		$i=$i+1;
	}
	$response=[];
	if ($assinaturas == [] || count($assinaturas) == 0){
		$response['status'] =  "Nada a atualizar!";
	}
	else {
		$response['status'] = "Atualizado!";
	}
	return new WP_REST_Response($response, 200);
}
//------------------------------------------------------
//
//
//
function updateAssinaturas(){
  	//wp_mail( 'luisfelipesantoszxcv@hotmail.com', 'Cron api', 'Automatic scheduled email from WordPress to test cron');
  	//
	$suspends = array(
        'key' => 'active',
        'value' => 0,
        'compare' => '='
    );
  	$assinaturas = get_posts(array(
            'posts_per_page'    => 300,
            'offset'            => 0,
            'category'          => '',
            'category_name'     => '',
            'orderby'           => 'ID',
            'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
            'order'             => 'DESC',
            'post_type'         => 'assinatura',
			'meta_query' => array(
				'relation' => 'OR',
				$suspends
			)
            /*'meta_key'        => 'partner-submission-status',
            'meta_value'        => 'goedgekeurd',*/
    ));
	$i = 0;
	foreach ($assinaturas as $assinatura){
		$meta = "";
		$meta = get_post_meta($assinatura->ID);
		$assinaturas[$i]->meta = $meta;
		
		$verification = [];
		$verification = verifi_status($assinatura->ID);
		$post_author_id = get_post_field( 'post_author', $assinatura->ID );
		
		$assinatura_status = get_user_meta( $post_author_id , 'status' , true );
		
		if($verification["status"] == 'ACTIVE' && get_post_meta($assinatura->ID, 'active', true) == 1 && $assinatura_status == 1){
			//			
		}else{
			if ($verification["status"] == "ACTIVE" ) {
				
				update_user_meta( $post_author_id, 'status', 1 );
				update_post_meta($assinatura->ID, 'active', 1);
				update_post_meta($assinatura->ID, 'cancelled', 0);
				update_post_meta($assinatura->ID, 'aprovation', 0);
			}
		}
		
		if($verification["status"] == 'CANCELLED' && get_post_meta($assinatura->ID, 'cancelled', true) == 1 && $assinatura_status == 0){
			//
		}else{
			if ($verification["status"] == "CANCELLED" || $verification == "pre-approval not found." ) {
				
				update_user_meta( $post_author_id, 'status', 0 );
				update_post_meta($assinatura->ID, 'active', 0);
				update_post_meta($assinatura->ID, 'cancelled', 1);
				update_post_meta($assinatura->ID, 'aprovation', 0);
			}
		}
		
		if($verification["status"] == 'SUSPENDED' && get_post_meta($assinatura->ID, 'active', true) == 0 && $assinatura_status == 2){
			//
		}else{
			if ($verification["status"] == "SUSPENDED" ) {
			
				update_user_meta( $post_author_id, 'status', 2 );
				update_post_meta($assinatura->ID, 'active', 0);
				update_post_meta($assinatura->ID, 'cancelled', 0);
				update_post_meta($assinatura->ID, 'aprovation', 0);
			}
		}
		$assinaturas[$i]->data_pag = $verification;
		
		$i=$i+1;
	}
	$response=[];
	if ($assinaturas == [] || count($assinaturas) == 0){
		$response['status'] =  "Nada a atualizar!";
	}
	else {
		$response['status'] = "Atualizado!";
	}
	return new WP_REST_Response($response, 200);
}

function cancelAssinaturas(){
  	//wp_mail( 'luisfelipesantoszxcv@hotmail.com', 'Cron api', 'Automatic scheduled email from WordPress to test cron');
  	//
  	$actives = array(
        'key' => 'active',
        'value' => 1,
        'compare' => '='
    );
	$suspends = array(
        'key' => 'active',
        'value' => 0,
        'compare' => '='
    );
  	$assinaturas = get_posts(array(
            'posts_per_page'    => 300,
            'offset'            => 0,
            'category'          => '',
            'category_name'     => '',
            'orderby'           => 'ID',
            'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
            'order'             => 'DESC',
            'post_type'         => 'assinatura',
			'meta_query' => array(
				'relation' => 'OR',
				$actives,
				$suspends
			)
            /*'meta_key'        => 'partner-submission-status',
            'meta_value'        => 'goedgekeurd',*/
    ));
	
	$i = 0;
	foreach ($assinaturas as $assinatura){
		$meta = "";
		$meta = get_post_meta($assinatura->ID);
		$assinaturas[$i]->meta = $meta;
		
		$verification = [];
		$verification = verifi_status($assinatura->ID);
		$post_author_id = get_post_field( 'post_author', $assinatura->ID );
		
		$assinatura_status = get_user_meta( $post_author_id , 'status' , true );
		
		if($verification["status"] == 'ACTIVE' && get_post_meta($assinatura->ID, 'active', true) == 1 && $assinatura_status == 1){
			//			
		}else{
			if ($verification["status"] == "ACTIVE" ) {
				
				update_user_meta( $post_author_id, 'status', 1 );
				update_post_meta($assinatura->ID, 'active', 1);
				update_post_meta($assinatura->ID, 'cancelled', 0);
				update_post_meta($assinatura->ID, 'aprovation', 0);
			}
		}


		if($verification["status"] == 'CANCELLED' && get_post_meta($assinatura->ID, 'cancelled', true) == 1 && $assinatura_status == 0){
			//
		}else{
			if ($verification["status"] == "CANCELLED" || $verification["status"] == "CANCELLED_BY_RECEIVER" || $verification["status"] == "CANCELLED_BY_SENDER" || $verification["status"] =="EXPIRED" || $verification["status"] == "PAYMENT_METHOD_CHANGE" || $verification == "pre-approval not found." ) {
				
				update_user_meta( $post_author_id, 'status', 0 );
				update_post_meta($assinatura->ID, 'active', 0);
				update_post_meta($assinatura->ID, 'cancelled', 1);
				update_post_meta($assinatura->ID, 'aprovation', 0);
			}
		}
		
		if($verification["status"] == 'SUSPENDED' && get_post_meta($assinatura->ID, 'active', true) == 0 && $assinatura_status == 0){
			//
		}else{
			if ($verification["status"] == "SUSPENDED" ) {
			
				update_user_meta( $post_author_id, 'status', 0 );
				update_post_meta($assinatura->ID, 'active', 0);
				update_post_meta($assinatura->ID, 'cancelled', 0);
				update_post_meta($assinatura->ID, 'aprovation', 0);
			}
		}
		$assinaturas[$i]->data_pag = $verification;
		
		$i=$i+1;
	}
	$response=[];
	if ($assinaturas == [] || count($assinaturas) == 0){
		$response['status'] =  "Nada a atualizar!";
	}
	else {
		$response['status'] = "Atualizado!";
	}
	return new WP_REST_Response($response, 200);
}