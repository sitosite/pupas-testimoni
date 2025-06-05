<?php
/*
Plugin Name: Custom Form Testimonis para Pallapupas
Description: Nuevo formulario de Testimonis para Pallapupas
Version: 1.0
Author: mmkt
*/


//define plugin domain for translations
function custom_form_testimonies_load_textdomain()
{
    load_plugin_textdomain('custom-form-testimonies', false, dirname(plugin_basename(__FILE__)) . '/lang');
}


// add scripts
function custom_form_testimonies_scripts()
{

    if (is_front_page()) {

        // css
        wp_enqueue_style('custom-form-style', plugins_url('css/style.css', __FILE__));

        // external js
        wp_enqueue_script('sinergiacrm-1', 'https://pallapupas.sinergiacrm.org/cache/include/javascript/sugar_grp1_jquery.js?v=Xxf341PE2kvxgyNHZ8NMDw');
        wp_enqueue_script('sinergiacrm-2', 'https://pallapupas.sinergiacrm.org/cache/include/javascript/sugar_grp1_yui.js?v=Xxf341PE2kvxgyNHZ8NMDw');
        wp_enqueue_script('sinergiacrm-3', 'https://pallapupas.sinergiacrm.org/cache/include/javascript/sugar_grp1.js?v=Xxf341PE2kvxgyNHZ8NMDw');

        // js
        wp_enqueue_script('masonry', 'https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js', [], null, true);
        wp_enqueue_script('alpine', plugins_url('js/alpine.js', __FILE__), array(), '1.0.0', false);
        wp_enqueue_script('alpine-form', plugins_url('js/form.js', __FILE__), array(), '1.0.0', false);
    }
}
add_action('wp_enqueue_scripts', 'custom_form_testimonies_scripts');


// files required
require_once(plugin_dir_path(__FILE__) . 'shortcode/custom-form-shortcodes.php');
require_once(plugin_dir_path(__FILE__) . 'shortcode/custom-grid-shortcodes.php');

// Hago el defer porque sino Alpine no carga antes que el resto de scripts
function add_defer_testimonies($tag, $handle)
{
    if ($handle !== 'alpine') {
        return $tag;
    }
    return str_replace(' src=', ' defer src=', $tag);
}
add_filter('script_loader_tag', 'add_defer_testimonies', 10, 2);


// Registramos el CPT testimonis
add_action('init', 'my_register_testimoni_cpt');
function my_register_testimoni_cpt()
{
    register_post_type('testimoni', [
        'labels' => ['name' => 'Testimonis', 'singular_name' => 'Testimoni'],
        'public' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
    ]);
}

// Agregams las acciones para el fomurlario
add_action('admin_post_nopriv_submit_testimoni', 'handle_testimoni_submission');
add_action('admin_post_submit_testimoni', 'handle_testimoni_submission');

function handle_testimoni_submission()
{
    // 1) Validar nonce, sanitar dades
    if (! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], 'submit_testimoni_action')) {
        wp_die('Seguretat fallida');
    }

    $nom    = sanitize_text_field($_POST['Contacts___first_name']);
    $cognom = sanitize_text_field($_POST['Contacts___last_name']);
    $email  = sanitize_email($_POST['Contacts___email1']);
    $text   = sanitize_textarea_field($_POST['LBL_TESTIMONI_TEXT']);

    // 2) Pujar la imatge
    if (!empty($_FILES['testimonial_image']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploaded = wp_handle_upload($_FILES['testimonial_image'], ['test_form' => false]);
        if (isset($uploaded['file'])) {
            $wp_filetype = wp_check_filetype($uploaded['file'], null);
            $attachment = [
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => sanitize_file_name($uploaded['file']),
                'post_content'   => '',
                'post_status'    => 'inherit'
            ];
            $attach_id = wp_insert_attachment($attachment, $uploaded['file']);
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $uploaded['file']));
        }

        $max_size = 2 * 1024 * 1024; // 2MB

        if (filesize($uploaded['file']) > $max_size) {
            // Esborrem el fitxer pujat
            unlink($uploaded['file']);
            wp_redirect(esc_url_raw($_POST['redirect_ko_url']));
            exit;
        }
    }

    // 3) Crear el post “testimoni”
    $post_id = wp_insert_post([
        'post_type'    => 'testimoni',
        'post_title'   => $nom . ' ' . $cognom,
        'post_content' => $text,
        'post_status'  => 'publish',
        'meta_input'   => [
            'testimonial_type' => sanitize_text_field($_POST['testimonial_type']),
            'email' => sanitize_text_field($_POST['Contacts___email1'])
        ],
    ]);
    if (!is_wp_error($post_id) && !empty($attach_id)) {
        set_post_thumbnail($post_id, $attach_id);
    }

    // 4) Enviar al CRM amb tots els camps necessaris
    $crm_fields = [];

    // Llista de tots els camps (hiddens + inputs visibles + LBL_TESTIMONI_TEXT)
    $fields = [
        'event_id',
        'validate_identification_number',
        'assigned_user_id',
        'req_id',
        'bool_id',
        'webFormClass',
        'stic_Payment_Commitments___payment_type',
        'stic_Payment_Commitments___periodicity',
        'language',
        'defParams',
        'timeZone',
        'Contacts___first_name',
        'Contacts___last_name',
        'Contacts___email1',
        'Contacts___phone_mobile',
        'Contacts___pph_acepta_informacion_c',
        'Contacts___stic_acquisition_channel_c',
        'Contacts___pph_campana_entrada_c',
        'Contacts___stic_language_c',
        'stic_Registrations___status',
        'stic_Registrations___attendees',
        'testimonial_type',
        'Contacts___testimonial_c',
        'Contacts___testimoni_c',
        'Contacts___pph_acepta_legal_c'
    ];

    foreach ($fields as $f) {
        if ($f === 'Contacts___pph_acepta_informacion_c') {
            $crm_fields[$f] = isset($_POST[$f]) ? '1' : '0';
        } elseif ($f === 'defParams' && isset($_POST[$f])) {
            $crm_fields[$f] = trim($_POST[$f]); // sense sanititzar per no trencar el JSON codificat
        } elseif (isset($_POST[$f])) {
            $crm_fields[$f] = sanitize_text_field($_POST[$f]);
        }
    }


    $crm_response = wp_remote_post(
        'https://pallapupas.sinergiacrm.org/index.php?entryPoint=stic_Web_Forms_save',
        [
            'body' => $crm_fields,
        ]
    );

    // 5) Redirigir segons resultat
    if (is_wp_error($crm_response) || wp_remote_retrieve_response_code($crm_response) !== 200) {
        wp_redirect(esc_url_raw($_POST['redirect_ko_url']));
    } else {
        wp_redirect(esc_url_raw($_POST['redirect_url']));
    }
    exit;
}
