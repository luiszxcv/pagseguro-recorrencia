<?php

//shortcode assinaturas
function assianturas_tables(){
	
	?>
	<style>
		table {
		  border-collapse: collapse;
		  border-spacing: 0;
		  width: 100%;
		  border: 1px solid #ddd;
		}

		th, td {
		  text-align: left;
		  padding: 8px;
		}

		tr:nth-child(even){background-color: #f2f2f2}
	</style>
	<?php
	$assinaturas = get_posts(array(
            'author'        =>  get_current_user_id(),
            'posts_per_page'    => 300,
            'offset'            => 0,
            'category'          => '',
            'category_name'     => '',
            'orderby'           => 'ID',
            'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
            'order'             => 'DESC',
            'post_type'         => 'assinatura'
            /*'meta_key'        => 'partner-submission-status',
            'meta_value'        => 'goedgekeurd',*/
        ));

        ?>
        <h4>Suas assinaturas</h4>

		<div style="overflow-x:auto;">
        <table class="wp-list-table widefat fixed striped posts" style="text-align: center;">
            <thead>
            <th>Nº Compra</th>
            <th>Valor</th>
            <th>Per. Cobrança</th>
            <th>Data de adesão</th>
            <th>Ativa?</th>
            <th>Cancelada?</th>
			<th>Em Aprovação</th>
			<th>Suspensa</th>
            <th>Ações</th>
            </thead>
            <tbody>

            <?php
            foreach ($assinaturas as $currentAssinatura)
            {
                ?>
                <tr class="iedit author-self level-0  post-password-required hentry">
                    <td>
                        <a href="<?php echo get_site_url() . '/minha-conta/view-order/' . get_post_meta($currentAssinatura->ID, 'compra', true); ?>">
                            <?php echo '#' . get_post_meta($currentAssinatura->ID, 'compra', true) ?>
                        </a>
                    </td>
                    <td><?php echo !empty(get_post_meta($currentAssinatura->ID, 'valor', true)) ? 'R$ ' . number_format(get_post_meta($currentAssinatura->ID, 'valor', true), 2, ',', '.' ) : ''; ?></td>
                    <td><?php echo get_post_meta($currentAssinatura->ID, 'periodo', true) ?></td>
                    <td><?php echo date('d/m/Y H:i:s', strtotime($currentAssinatura->post_date . ' -3 hours')); ?></td>
                    <td><?php echo get_post_meta($currentAssinatura->ID, 'active', true) == 1 ? 'Sim' : 'Não' ?></td>
                    <td><?php echo get_post_meta($currentAssinatura->ID, 'cancelled', true) == 1 ? 'Sim' : 'Não' ?></td>
					<td><?php echo get_post_meta($currentAssinatura->ID, 'aprovation', true) == 1 ? 'Sim' : 'Não' ?></td>
					<td><?php 
						$post_author_id = get_post_field( 'post_author', $currentAssinatura->ID);
						$statuss = get_user_meta( $post_author_id, 'status' , true );
						echo $statuss == 1 ? 'Sim' : 'Não'?>
					</td>
					
                    <td style="text-align: center">
                        <?php
                        if (get_post_meta($currentAssinatura->ID, 'cancelled', true) == 0) {
                            ?>
                            <div style="margin-bottom: 24px;">
                                <a class="woocommerce-button button" href="<?php echo get_site_url() . '/wp-json/admin/suspenderAssinatura/' . $currentAssinatura->ID ?>">

                                    <?php echo (get_post_meta($currentAssinatura->ID, 'active', true) == 1 ? 'Suspender' : 'Ativar'); ?>

                                </a>
                            </div>
                            <div>
                                <a class="woocommerce-button button" href="<?php echo get_site_url() . '/wp-json/admin/cancelarAssinatura/' . $currentAssinatura->ID ?>">
                                    Cancelar
                                </a>
                            </div>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            ?>

            </tbody>
        </table>
        <ul><li>Ao se inscrever, levará no máximo 10 minutos para ativar a sua assinatura;</li><li>Ao suspender a sua assinatura você deixa de aparecer nas pesquisas;</li><li>Ao reativar a sua assinatura de uma suspensão, levará cerca de 10 minutos para que esta ação seja efetivada (tempo de verificarmos se a sua assinatura está em dia);</li><li>Ao cancelar a sua assinatura você deixa de aparecer nas pesquisas;</li><li>Você deve cancelar a assinatura antes de completar 30 dias caso não queira que seja efetuada uma nova cobrança.</li></ul>
	</div>
	<?php
}
add_shortcode('dash_assinaturass', 'assianturas_tables');