<?php
/*
Plugin Name: Consulta CNPJ
Description: Consulta informações de empresas com base no CNPJ.
Version: 1.0
Author: Seu Nome
*/

//Adicionando Boostrap ao Plugin
function enqueue_bootstrap_from_cdn() {
    // Registrar o arquivo CSS do Bootstrap a partir de um CDN
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');

    // Registrar o arquivo JavaScript do Bootstrap a partir de um CDN
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.5.1.slim.min.js', array(), '3.5.1', true);
    wp_enqueue_script('popper', 'https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js', array(), '1.16.0', true);
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery', 'popper'), '4.5.2', true);
}

add_action('wp_enqueue_scripts', 'enqueue_bootstrap_from_cdn');

// Função para consultar CNPJ
function consulta_cnpj($atts) {
    ob_start();

    $output = '';

    if (isset($_POST['cnpj'])) {
        $cnpj = sanitize_text_field($_POST['cnpj']);
        $api_url = 'https://www.receitaws.com.br/v1/cnpj/' . $cnpj;

        $response = wp_safe_remote_get($api_url);

        if (!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($data['status']) && $data['status'] === 'OK') {
                // Exibe os dados da empresa
                $output .= '<h2>' . esc_html($data['nome']) . '</h2>';
                $output .= '<p><strong>CNPJ:</strong> ' . esc_html($data['cnpj']) . '</p>';
                $output .= '<p><strong>Telefone:</strong> ' . esc_html($data['telefone']) . '</p>';
                $output .= '<p><strong>E-mail:</strong> ' . esc_html($data['email']) . '</p>';
                $output .= '<p><strong>Endereço:</strong> ' . esc_html($data['logradouro']) . ', ' . esc_html($data['numero']) . ' - ' . esc_html($data['complemento']) . '</p>';
                $output .= '<p><strong>Município:</strong> ' . esc_html($data['municipio']) . '</p>';
                $output .= '<p><strong>Estado:</strong> ' . esc_html($data['uf']) . '</p>';
                $output .= '<p><strong>Atividade Principal:</strong> ' . esc_html($data['atividade_principal'][0]['text']) . '</p>';
                // Outros dados disponíveis podem ser adicionados aqui
            } else {
                $output .= '<p>Empresa não encontrada.</p>';
            }
        } else {
            $output .= '<p>Erro ao acessar a API.</p>';
        }
    }

    $output .= '<form method="post">';
    $output .= '<input type="text" name="cnpj" placeholder="Digite o CNPJ">';
    $output .= '<input type="submit" value="Consultar">';
    $output .= '</form>';

    $output .= ob_get_clean();
    return $output;
}
add_shortcode('consulta-cnpj', 'consulta_cnpj');
