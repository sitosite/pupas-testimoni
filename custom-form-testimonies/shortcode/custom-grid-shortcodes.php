<?php

add_shortcode('testimoni_masonry', 'testimoni_masonry_shortcode');
function testimoni_masonry_shortcode()
{

    $args = [
        'post_type'      => 'testimoni',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];
    $query = new WP_Query($args);

    ob_start(); ?>
    <div x-data="testimoniMasonry()" x-init="init()">

        <!-- GRID MASONRY -->
        <div class="testimoni-masonry" x-ref="masonryContainer">
            <?php
            $i = 0;
            if ($query->have_posts()):
                while ($query->have_posts()): $query->the_post();
                    $img     = get_the_post_thumbnail_url(get_the_ID(), 'large') ?: '';
                    $title   = get_the_title();
                    $content = apply_filters('the_content', get_the_content());
                    $type_raw = get_post_meta(get_the_ID(), 'testimonial_type', true);
                    $type     = ucfirst(str_replace(['_', '-'], ' ', $type_raw));
            ?>
                    <?php if ($img): ?>
                        <div class="testimoni-item"
                            x-show="<?php echo $i; ?> < count"
                            @click="active = {
                            img: '<?php echo esc_js($img); ?>',
                            title: '<?php echo esc_js($title); ?>',
                            content: '<?php echo esc_js($content); ?>',
                            type: '<?php echo esc_js($type); ?>'
                        }; open = true">
                            <div class="testimoni-item-wrapper flip-card">
                                <div class="flip-card-inner">
                                    <div class="flip-card-front" style="background-image: url('<?php echo esc_url($img); ?>'); background-size: cover; background-position: center;"></div>
                                    <div class="flip-card-back">
                                        <h4><?php echo esc_html($title); ?></h4>
                                        <p class="testimonial-type"><?php echo esc_html($type); ?></p>
                                        <?php echo wp_kses_post($content); ?>
                                        <p class="testimoni-link"><?php _e('Llegir més', 'custom-form-testimonies') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="testimoni-item"
                            x-show="<?php echo $i; ?> < count"
                            @click="active = {
                            img: '',
                            title: '<?php echo esc_js($title); ?>',
                            content: '<?php echo esc_js($content); ?>',
                            type: '<?php echo esc_js($type); ?>'
                        }; open = true">
                            <div class="testimoni-item-wrapper">
                                <h4><?php echo esc_html($title); ?></h4>
                                <p class="testimonial-type"><?php echo esc_html($type); ?></p>
                                <div class="testimonial-content">
                                    <?php echo wp_kses_post($content); ?>
                                    <p class="testimoni-link"><?php _e('Llegir més', 'custom-form-testimonies') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
            <?php
                    $i++;
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>

        <!-- BOTÓ VEURE'N MÉS / MENYS -->
        <div class="load-more-wrapper">
            <a @click="toggleMore()" class="load-more" x-text="expanded ? `Veure'n menys` : `Veure'n més`"></a>
        </div>


        <!-- UN SOL POPUP GLOBAL -->
        <template x-if="open">
            <div class="modal-bg" @click="open = false">
                <div class="modal-content" @click.stop>
                    <div class="modal-content_row">
                        <span class="testimoni-modal-close" @click="open = false">X</span>
                        <template x-if="active.img">
                            <div class="testimoni-modal-img-wrapper">
                                <img class="testimoni-modal-img" :src="active.img" alt="">
                            </div>
                        </template>
                        <div class="testimoni-modal-content-wrapper">
                            <h3 class="testimoni-modal-title" x-text="active.title"></h3>
                            <p class="testimoni-modal-type" x-text="active.type"></p>
                            <div class="testimoni-modal-content" x-html="active.content"></div>
                            <div class="modal-share">
                                <p class="modal-share-title"><?php _e('Comparteix l’efecte Pallapupas', 'custom-form-testimonies'); ?>:</p>
                                <div class="modal-share-buttons">
                                    <a :href="`https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(window.location.href)}`" target="_blank" rel="noopener" class="share-icon linkedin"><i class="fab fa-linkedin"></i></a>
                                    <a :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(window.location.href)}`" target="_blank" rel="noopener" class="share-icon x"><i class="fab fa-x-twitter"></i></a>
                                    <a :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`" target="_blank" rel="noopener" class="share-icon facebook"><i class="fab fa-facebook"></i></a>
                                    <a :href="`https://t.me/share/url?url=${encodeURIComponent(window.location.href)}`" target="_blank" rel="noopener" class="share-icon telegram"><i class="fab fa-telegram"></i></a>
                                    <a :href="`https://wa.me/?text=${encodeURIComponent(window.location.href)}`" target="_blank" rel="noopener" class="share-icon whatsapp"><i class="fab fa-whatsapp"></i></a>
                                    <a :href="`mailto:?subject=Testimoni&body=${encodeURIComponent(window.location.href)}`" class="share-icon email"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-content_row" style="justify-content: center;margin: 20px 0;">
                        <a href="#formulari" @click="open = false" class="testimoni-modal-button">
                            <?php _e('Envia el teu testimoni', 'custom-form-testimonies') ?>
                        </a>
                    </div>
                </div>
            </div>
        </template>

    </div>

    <script>
        function testimoniMasonry() {
            return {
                count: 10,
                initialCount: 10,
                open: false,
                expanded: false,
                active: {
                    img: '',
                    title: '',
                    content: '',
                    type: ''
                },
                masonry: null,

                init() {
                    this.$nextTick(() => {
                        this.masonry = new Masonry(this.$refs.masonryContainer, {
                            itemSelector: '.testimoni-item',
                            columnWidth: '.testimoni-item',
                            percentPosition: true,
                        });
                    });
                },

                toggleMore() {
                    if (this.expanded) {
                        this.count = this.initialCount;
                    } else {
                        this.count += 10;
                    }
                    this.expanded = !this.expanded;
                    this.$nextTick(() => {
                        this.masonry.reloadItems();
                        this.masonry.layout();
                    });
                }
            };
        }
    </script>



<?php
    return ob_get_clean();
}
