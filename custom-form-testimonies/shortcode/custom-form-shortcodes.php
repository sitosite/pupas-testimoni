<?php

// cusomt-form shortcode
function custom_form_testimonies_shortcode($atts)
{

    $ok = get_permalink(apply_filters('wpml_object_id', 108, 'page'));
    $ko = get_permalink(apply_filters('wpml_object_id', 110, 'page'));

    // si wpml está en es
    if (ICL_LANGUAGE_CODE == 'es') {
        $email_template = '00000370-6218-57b9-f4d9-6810e7d8fdd6';
    } else {
        $email_template = '000008be-9e1f-10cb-4da4-6810e660c61a';
    }

    ob_start(); ?>

    <div class="form-testimonies">

        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
            enctype="multipart/form-data"
            name="WebToLeadForm"
            method="POST"
            id="WebToLeadForm"
            onsubmit="return submitForm(this);"
            x-data="{legalOpen: false}">


            <!-- Camps amagats -->
            <input type="hidden" id="event_id" name="event_id" value="00000ca9-76d2-09ec-f058-6810e6fdc7d3" />

            <!-- enviament correcte -->
            <input type="hidden" id="redirect_url" name="redirect_url" value="<?php echo $ok ?>" />
            <!-- enviament fallit -->
            <input type="hidden" id="redirect_ko_url" name="redirect_ko_url" value="<?php echo $ko ?>" />


            <input type="hidden" id="validate_identification_number" name="validate_identification_number" value="1" />
            <input type="hidden" id="assigned_user_id" name="assigned_user_id" value="00000f69-0793-03b1-cde4-681dc68a35b2" />
            <input type="hidden" id="req_id" name="req_id"
                value="Contacts___first_name;Contacts___last_name;Contacts___email1;Contacts___phone_mobile;testimonial_type;stic_Registrations___status;stic_Registrations___attendees;Contacts___pph_acepta_legal_c;" />
            <input type="hidden" id="bool_id" name="bool_id" value="Contacts___pph_acepta_informacion_c;" />
            <input type="hidden" id="webFormClass" name="webFormClass" value="EventInscription" />
            <input type="hidden" id="stic_Payment_Commitments___payment_type" name="stic_Payment_Commitments___payment_type"
                value="" />
            <input type="hidden" id="stic_Payment_Commitments___periodicity" name="stic_Payment_Commitments___periodicity"
                value="punctual" />
            <input type="hidden" id="language" name="language" value="es_ES" />
            <input type="hidden" id="defParams" name="defParams"
                value="%7B%22include_payment_commitment%22%3A0%2C%22include_organization%22%3A0%2C%22account_code_mandatory%22%3A0%2C%22include_registration%22%3A1%2C%22account_name_optional%22%3A0%2C%22email_template_id%22%3A%221e6c6e2e-c4f2-7f85-b8c7-6437dd4e1ad8%22%7D" />
            <input type="hidden" id="timeZone" name="timeZone" value="" />

            <!-- Campos ocults (estan després dels camps visibles en el original) // Esborrar en acabar -->
            <!-- Este campo debe ser oculto con el valor "web" -->
            <input type="hidden" id="Contacts___stic_acquisition_channel_c" name="Contacts___stic_acquisition_channel_c" value="web" />
            <!-- Aquí el campo que recibe la información del URL, debe ser oculto pero se debe completar con el parametro que hay tras ?accion= -->
            <?php $accion = '';
            if (isset($_GET['accion'])) {
                $accion = htmlspecialchars($_GET['accion']);
            } ?>
            <input type="hidden" id="Contacts___pph_campana_entrada_c" name="Contacts___pph_campana_entrada_c" value="<?php echo $accion ?>" />
            <!-- Este campo debe ser oculto y se completará con el valor "Catalán" si el formulario se ha cumplimentado en catalán, o el valor "spanish" si se ha cumplimentado en castellano // Valores que puede tomar: spanish; catalan; galician -->
            <input type="hidden" id="Contacts___stic_language_c" name="Contacts___stic_language_c" value="<?php _e('catalan', 'custom-form-testimonies') ?>" />
            <!-- Este campo debe ser oculto con el valor "confirmed" -->
            <input type="hidden" id="stic_Registrations___status" name="stic_Registrations___status" value="confirmed" />
            <!-- Este campo se debe ocultar con el valor 1, simplemente es obligatório en el CRM pero no aporta información en este tipo de formularios -->
            <input type="hidden" id="stic_Registrations___attendees" name="stic_Registrations___attendees" value="1" />
            <!-- Identificacor CRM para indicar que son testimonios -->
            <input type="hidden" name="Contacts___testimonial_c" value="1">

            <!-- Camps visibles -->
            <div class="fields-row">
                <div class="field-control">
                    <label id="lbl_Contacts___first_name" for="Contacts___first_name"><?php _e('Nom', 'custom-form-testimonies') ?> <span id="lbl_Contacts___last_name_required" class="required">*</span></label>
                    <input id="Contacts___first_name" name="Contacts___first_name" type="text" span="" sugar="slot" />
                </div>

                <div class="field-control">
                    <label id="lbl_Contacts___last_name" for="Contacts___last_name"><?php _e('Cognoms', 'custom-form-testimonies') ?> <span id="lbl_Contacts___last_name_required" class="required">*</span></label>
                    <input id="Contacts___last_name" name="Contacts___last_name" type="text" span="" sugar="slot" />
                </div>
            </div>

            <div class="fields-row">
                <div class="field-control">
                    <label id="lbl_Contacts___email1" for="Contacts___email1"><?php _e('Correu electrònic', 'custom-form-testimonies') ?> <span id="lbl_Contacts___email1_required" class="required">*</span></label>
                    <input id="Contacts___email1" name="Contacts___email1" type="text" onchange="validateEmailAdd(this);" span="" sugar="slot" />
                </div>

                <div class="field-control">
                    <label id="lbl_Contacts___phone_mobile" for="Contacts___phone_mobile"><?php _e('Telèfon', 'custom-form-testimonies') ?> <span id="lbl_Contacts___email1_required" class="required">*</span></label>
                    <input id="Contacts___phone_mobile" name="Contacts___phone_mobile" type="text" span="" sugar="slot" />
                </div>
            </div>

            <div class="field-control">
                <label for="testimonial_type"><?php _e('Tipus de testimoni', 'custom-form-testimonies'); ?> <span id="testimonial_type_required" class="required">*</span></label>
                <select id="testimonial_type" name="testimonial_type">
                    <option label="" value=""></option>
                    <option value="pacient"><?php _e('Pacient', 'custom-form-testimonies'); ?></option>
                    <option value="familiar"><?php _e('Familiar', 'custom-form-testimonies'); ?></option>
                    <option value="personal_sanitari"><?php _e('Personal sanitari', 'custom-form-testimonies'); ?></option>
                    <option value="altres"><?php _e('Altres', 'custom-form-testimonies'); ?></option>
                </select>
            </div>
            <div class="field-control">
                <label for="lbl_Contacts___testimoni_c"><?php _e('El teu testimoni', 'custom-form-testimonies') ?> <span id="Contacts___testimoni_c_required" class="required">*</span></label>
                <textarea id="Contacts___testimoni_c" name="Contacts___testimoni_c" rows="6"></textarea>
            </div>
            <div class="field-control">
                <label for="testimonial_image"><?php _e('Puja la teva imatge', 'custom-form-testimonies') ?></label>
                <input type="file" id="testimonial_image" name="testimonial_image" accept="image/*">
            </div>

            <div class="field-control field-control--less-margin">
                <div class="checkbox-field">
                    <input type="checkbox" id="Contacts___pph_acepta_informacion_c" name="Contacts___pph_acepta_informacion_c" />
                    <label id="lbl_Contacts___pph_acepta_informacion_c" for="Contacts___pph_acepta_informacion_c"><?php _e('Accepta rebre informació de Pallapupas', 'custom-form-testimonies') ?></label>
                </div>
            </div>

            <div class="field-control">
                <div class="checkbox-field">
                    <input type="checkbox" id="Contacts___pph_acepta_legal_c" name="Contacts___pph_acepta_legal_c" />
                    <label id="lbl_Contacts___pph_acepta_legal_c" for="Contacts___pph_acepta_legal_c"><?php _e('He llegit i accepto', 'custom-form-testimonies') ?> <span class="legal-text-link" @click.prevent="legalOpen = true"><?php _e('l\'avís legal i la política de privacitat', 'custom-form-testimonies') ?> <span id="Contacts___pph_acepta_legal_c_required" class="required">*</span></label>
                </div>
            </div>

            <?php require('partials/legal.php'); ?>

            <input type="hidden" name="action" value="submit_testimoni">
            <?php wp_nonce_field('submit_testimoni_action', '_wpnonce'); ?>

            <!-- Botó d'enviament -->
            <input class="form-btn" type="submit" name="Submit" value="<?php _e('Enviar', 'custom-form-testimonies') ?>" />

        </form>
    </div>

<?php
    return ob_get_clean();
}
add_shortcode('custom-form-testimonies', 'custom_form_testimonies_shortcode');
