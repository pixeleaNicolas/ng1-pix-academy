<?php
/**
 * Plugin Name:       Ng1 Pix Academy
 * Plugin URI:        https://example.com/
 * Description:       Affiche les vidéos depuis une API externe (Video Sharing API) dans le back-office WordPress avec des options de filtrage et de visibilité.
 * Version:           1.1.0
 * Author:            GEHIN Nicolas
 * Author URI:        https://example.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ng1-pix-academy
 *
 * @package           Ng1_Pix_Academy
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class NG1_Pix_Academy {

    /**
     * Constructeur
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_pages' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
    }

    /**
     * Ajoute les pages au menu d'administration.
     */
    public function add_admin_pages() {
        add_menu_page(
            'Pix Academie',
            'Pix Academie',
            'manage_options',
            'ng1_pix_academy',
            [ $this, 'render_videos_page' ],
            'dashicons-format-video',
            25
        );
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $user_email = $current_user->user_email;
            
            // Vérifier si l'email contient "@pixelea"
            if (strpos($user_email, '@pixelea') !== false) {
                add_submenu_page(
                    'ng1_pix_academy',
                    'Réglages API',
                    'Réglages API',
                    'manage_options',
                    'ng1_pix_academy_settings',
                    [ $this, 'render_settings_page' ]
                );
            }
        }
    }

    /**
     * Enregistre les réglages du plugin.
     */
    public function register_settings() {
        register_setting( 'ng1_pix_academy_options_group', 'ng1_pix_academy_options' );
    }

    /**
     * Affiche la page des réglages.
     */
    public function render_settings_page() {
        $options = get_option( 'ng1_pix_academy_options' );
        $api_url = isset( $options['api_url'] ) ? esc_url( $options['api_url'] ) : '';
        $diffuseur_slug = isset( $options['diffuseur_slug'] ) ? sanitize_title( $options['diffuseur_slug'] ) : '';
        $visible_videos = isset( $options['visible_videos'] ) ? (array) $options['visible_videos'] : [];
        ?>
        <div class="wrap">
            <h1>Réglages de l'API Pix Academy</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'ng1_pix_academy_options_group' ); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="ng1_api_url">URL du site distant (API)</label>
                        </th>
                        <td>
                            <input type="url" id="ng1_api_url" name="ng1_pix_academy_options[api_url]" value="<?php echo $api_url; ?>" class="regular-text" placeholder="https://votre-site.com" />
                            <p class="description">Entrez l'URL de base du site WordPress où l'API "Video Sharing" est active. Ne pas inclure /wp-json/.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="ng1_diffuseur_slug">Slug du diffuseur</label>
                        </th>
                        <td>
                            <input type="text" id="ng1_diffuseur_slug" name="ng1_pix_academy_options[diffuseur_slug]" value="<?php echo esc_attr( $diffuseur_slug ); ?>" class="regular-text" placeholder="mon-partenaire" />
                            <p class="description">Slug correspondant au diffuseur (dans l'admin Vidéos > Diffuseurs). Utilisé pour filtrer les vidéos et tags.</p>
                        </td>
                    </tr>
                </table>
                
                <?php
                if (is_user_logged_in()) {
                    $current_user = wp_get_current_user();
                    $user_email = $current_user->user_email;
                    
                    // Vérifier si l'email contient "@pixelea"
                    if (strpos($user_email, '@pixelea') !== false) {
                  
                        ?>
                        <hr>
                        <h2>Visibilité des vidéos</h2>
                        <p>Cochez les vidéos que vous souhaitez rendre accessibles dans l'interface.</p>
    
                        <?php
                        if ( ! empty( $api_url ) && ! empty( $diffuseur_slug ) ) {
                            $request_url = trailingslashit( $api_url ) . 'wp-json/ng1-video-sharing-api/v1/videos/by-user/' . rawurlencode( $diffuseur_slug ) . '?per_page=100';
                            $response = wp_remote_get( $request_url );
    
                            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                                $videos = json_decode( wp_remote_retrieve_body( $response ) );
                                if ( ! empty( $videos ) ) {
                                    echo '<table class="wp-list-table widefat striped"><thead><tr><th style="width: 50px;">Activer</th><th>Titre de la vidéo</th></tr></thead><tbody>';
                                    foreach ( $videos as $video ) {
                                        $checked = in_array( $video->id, $visible_videos ) ? 'checked' : '';
                                        echo '<tr>';
                                        echo '<td><input type="checkbox" name="ng1_pix_academy_options[visible_videos][]" value="' . esc_attr( $video->id ) . '" ' . $checked . '></td>';
                                        echo '<td>' . esc_html( $video->title->rendered ) . '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                } else {
                                    echo '<p>Aucune vidéo trouvée sur l\'API.</p>';
                                }
                            } else {
                                 echo '<div class="notice notice-error"><p>Impossible de se connecter à l\'API. Vérifiez l\'URL et que le plugin "Video Sharing API" est actif sur le site distant.</p></div>';
                            }
                        } else {
                             echo '<div class="notice notice-warning"><p>Veuillez d\'abord enregistrer une URL d\'API valide et un slug de diffuseur pour voir la liste des vidéos.</p></div>';
                        }
                    } else {
                        // L'email ne contient pas "@pixelea"
                        echo '<div class="notice notice-error"><p>Cette section est réservée aux administrateur de Pixelea.</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-warning"><p>Veuillez vous connecter pour accéder aux réglages.</p></div>';
                }
                ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

public function render_videos_page() {
    ?>
    <div class="wrap" id="ng1-pix-academy-app">
        <h1>
<svg width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M19.5617 7C19.7904 5.69523 18.7863 4.5 17.4617 4.5H6.53788C5.21323 4.5 4.20922 5.69523 4.43784 7" stroke="currentColor" stroke-width="1.5"/>
<path d="M17.4999 4.5C17.5283 4.24092 17.5425 4.11135 17.5427 4.00435C17.545 2.98072 16.7739 2.12064 15.7561 2.01142C15.6497 2 15.5194 2 15.2588 2H8.74099C8.48035 2 8.35002 2 8.24362 2.01142C7.22584 2.12064 6.45481 2.98072 6.45704 4.00434C6.45727 4.11135 6.47146 4.2409 6.49983 4.5" stroke="currentColor" stroke-width="1.5"/>
<path d="M21.1935 16.793C20.8437 19.2739 20.6689 20.5143 19.7717 21.2572C18.8745 22 17.5512 22 14.9046 22H9.09536C6.44881 22 5.12553 22 4.22834 21.2572C3.33115 20.5143 3.15626 19.2739 2.80648 16.793L2.38351 13.793C1.93748 10.6294 1.71447 9.04765 2.66232 8.02383C3.61017 7 5.29758 7 8.67239 7H15.3276C18.7024 7 20.3898 7 21.3377 8.02383C22.0865 8.83268 22.1045 9.98979 21.8592 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
<path d="M14.5812 13.6159C15.1396 13.9621 15.1396 14.8582 14.5812 15.2044L11.2096 17.2945C10.6669 17.6309 10 17.1931 10 16.5003L10 12.32C10 11.6273 10.6669 11.1894 11.2096 11.5258L14.5812 13.6159Z" stroke="currentColor" stroke-width="1.5"/>
</svg> Pix Academie</h1>
        
        <div id="ng1-tag-filters-container" class="ng1-tag-filters">
            <p>Chargement des filtres...</p>
        </div>

        <div id="ng1-video-container" class="ng1-video-grid">
            <p class="ng1-loading">Chargement des vidéos...</p>
        </div>

    </div><div id="ng1-modal" class="ng1-modal-overlay">
        <div class="ng1-modal-content">
            <button class="ng1-modal-close" aria-label="Fermer">&times;</button>
            <h2 id="ng1-modal-title"></h2>
            <div id="ng1-modal-body">
                <div id="ng1-modal-video"></div>
                <div id="ng1-modal-description"></div>
            </div>
        </div>
    </div>
    <?php
}



    /**
     * Charge les scripts et styles pour l'admin.
     */
    public function enqueue_admin_scripts( $hook ) {
        // Ne charger que sur la page du plugin
        if ( 'toplevel_page_ng1_pix_academy' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'ng1-admin-styles', plugin_dir_url( __FILE__ ) . 'admin/css/admin-styles.css', [], '1.0.0' );
        wp_enqueue_script( 'ng1-admin-scripts', plugin_dir_url( __FILE__ ) . 'admin/js/admin-scripts.js', [], '1.0.0', true );

        // Passer les données de PHP à JavaScript
        $options = get_option( 'ng1_pix_academy_options' );
        $api_url = isset( $options['api_url'] ) ? trailingslashit( esc_url( $options['api_url'] ) ) : '';
        $visible_videos = isset( $options['visible_videos'] ) ? (array) $options['visible_videos'] : [];
        $diffuseur_slug = isset( $options['diffuseur_slug'] ) ? sanitize_title( $options['diffuseur_slug'] ) : '';

        wp_localize_script('ng1-admin-scripts', 'ng1_pix_academy_data', [
            'api_url'        => $api_url,
            'visible_videos' => $visible_videos,
            'diffuseur_slug' => $diffuseur_slug,
            'nonce'          => wp_create_nonce('wp_rest')
        ]);
    }
}

new NG1_Pix_Academy();