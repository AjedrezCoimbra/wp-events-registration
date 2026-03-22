<?php
/**
 * Clase para gestionar actualizaciones automáticas desde GitHub Releases.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_Updater {

    private $plugin_file;
    private $plugin_slug;
    private $version;
    private $github_repo;

    /**
     * Constructor.
     * 
     * @param string $plugin_file Path al archivo principal del plugin.
     * @param string $version     Versión actual instalada.
     * @param string $github_url  URL completa del repositorio (ej: https://github.com/AjedrezCoimbra/wp-events-registration).
     */
    public function __construct( $plugin_file, $version, $github_url ) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename( $plugin_file );
        $this->version     = $version;
        
        // Extraer "Propietario/Repo" de la URL
        $path = parse_url( $github_url, PHP_URL_PATH );
        $this->github_repo = trim( $path, '/' );
    }

    /**
     * Registra los hooks necesarios.
     */
    public function init() {
        // Hooks para detección de actualizaciones
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
        add_filter( 'plugins_api', array( $this, 'get_plugin_info' ), 20, 3 );
        add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );
        add_filter( 'upgrader_source_selection', array( $this, 'fix_source_dir' ), 10, 4 );
        
        // Hooks para actualizaciones automáticas (toggle en la lista de plugins)
        add_filter( 'plugin_auto_update_setting_html', array( $this, 'auto_update_setting_html' ), 10, 3 );
        add_filter( 'auto_update_plugin', array( $this, 'maybe_auto_update' ), 10, 2 );
        
        // Acción para procesar el clic en el toggle
        add_action( 'admin_post_wper_toggle_auto_update', array( $this, 'handle_toggle_auto_update' ) );
    }

    /**
     * Inyecta información de actualización si hay una nueva versión disponible.
     */
    public function check_for_updates( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $release = $this->get_github_release();
        if ( ! $release ) {
            return $transient;
        }

        $remote_version = ltrim( $release->tag_name, 'v' );

        if ( version_compare( $this->version, $remote_version, '<' ) ) {
            $res = new stdClass();
            $res->slug        = 'wp-events-registration';
            $res->plugin      = $this->plugin_slug;
            $res->new_version = $remote_version;
            $res->url         = 'https://github.com/' . $this->github_repo;
            $res->package     = $release->zipball_url;

            $transient->response[ $this->plugin_slug ] = $res;
        }

        return $transient;
    }

    /**
     * Proporciona detalles del plugin para el popup de WordPress.
     */
    public function get_plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $result;
        }

        if ( ! isset( $args->slug ) || $args->slug !== 'wp-events-registration' ) {
            return $result;
        }

        $release = $this->get_github_release();
        if ( ! $release ) {
            return $result;
        }

        $res = new stdClass();
        $res->name           = 'WP Events Registration';
        $res->slug           = 'wp-events-registration';
        $res->version        = ltrim( $release->tag_name, 'v' );
        $res->author         = 'José Joaquín Sánchez Fernández';
        $res->author_profile = 'https://ajedrezcoimbra.com';
        $res->homepage       = 'https://github.com/' . $this->github_repo;
        $res->download_link  = $release->zipball_url; // Cambio 1A: Mantener zipball_url
        $res->sections       = array(
            'description' => 'Plugin de gestión de eventos e inscripciones para sitios de WordPress.',
            'changelog'   => isset( $release->body ) ? wp_kses_post( $release->body ) : '-'
        );

        return $res;
    }

    /**
     * Limpia la caché tras la actualización.
     */
    public function clear_cache( $upgrader_object, $options ) {
        if ( $options['action'] === 'update' && $options['type'] === 'plugin'
             && isset( $options['plugins'] ) && in_array( $this->plugin_slug, (array) $options['plugins'], true ) ) {
            delete_transient( 'wper_github_release_cache' );
            delete_site_transient( 'update_plugins' );
        }
    }

    /**
     * Corrige el nombre del directorio extraído desde GitHub.
     */
    public function fix_source_dir( $source, $remote_source, $upgrader, $hook_extra ) {
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
            return $source;
        }

        $target_dir = WP_PLUGIN_DIR . '/wp-events-registration/';
        $source_dir_name = basename( untrailingslashit( $source ) );

        if ( $source_dir_name !== 'wp-events-registration' ) {
            $new_source = trailingslashit( $remote_source ) . 'wp-events-registration/';
            
            global $wp_filesystem;
            if ( empty( $wp_filesystem ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }

            if ( $wp_filesystem->move( $source, $new_source ) ) {
                return $new_source;
            }
        }

        return $source;
    }

    /**
     * Genera el HTML para el toggle de actualizaciones automáticas en /wp-admin/plugins.php.
     */
    public function auto_update_setting_html( $html, $plugin_file, $plugin_data ) {
        if ( $plugin_file === $this->plugin_slug ) {
            $enabled = (int) get_option( 'wper_auto_updates', 0 );
            $text    = $enabled ? __( 'Desactivar las actualizaciones automáticas', 'wp-events-registration' ) : __( 'Activar las actualizaciones automáticas', 'wp-events-registration' );
            
            $url = wp_nonce_url( admin_url( 'admin-post.php?action=wper_toggle_auto_update' ), 'wper_toggle_auto_update' );
            
            // Usamos un estilo similar al nativo pero con nuestro propio enlace
            $html = sprintf(
                '<a href="%s">%s</a>',
                esc_url( $url ),
                esc_html( $text )
            );
            
            if ( $enabled ) {
                $html .= '<div class="auto-update-time">' . __( 'Actualizaciones automáticas activadas', 'wp-events-registration' ) . '</div>';
            }
        }
        return $html;
    }

    /**
     * Handler para el toggle de actualizaciones automáticas.
     */
    public function handle_toggle_auto_update() {
        if ( ! current_user_can( 'update_plugins' ) ) {
            wp_die( __( 'Sin permisos suficientes.', 'wp-events-registration' ) );
        }
        
        check_admin_referer( 'wper_toggle_auto_update' );
        
        $current = (int) get_option( 'wper_auto_updates', 0 );
        update_option( 'wper_auto_updates', $current ? 0 : 1 );
        
        wp_redirect( admin_url( 'plugins.php' ) );
        exit;
    }

    /**
     * Hook para habilitar la actualización automática si está activa la opción.
     */
    public function maybe_auto_update( $update, $item ) {
        if ( isset( $item->slug ) && $item->slug === 'wp-events-registration' ) {
            return (bool) get_option( 'wper_auto_updates', 0 );
        }
        return $update;
    }

    /**
     * Consulta la API de GitHub para obtener la última release.
     */
    private function get_github_release() {
        $cached = get_transient( 'wper_github_release_cache' );
        if ( false !== $cached ) {
            return $cached;
        }

        $api_url  = 'https://api.github.com/repos/' . $this->github_repo . '/releases/latest';
        
        $token = get_option( 'wper_github_token', '' );
        $args = array(
            'timeout'    => 10,
            'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' )
        );

        if ( $token ) {
            $args['headers'] = array(
                'Authorization' => 'token ' . $token
            );
        }

        $response = wp_remote_get( $api_url, $args );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );

        if ( empty( $data ) || ! isset( $data->tag_name ) ) {
            return false;
        }

        // Cachear por 12 horas
        set_transient( 'wper_github_release_cache', $data, 12 * HOUR_IN_SECONDS );

        return $data;
    }
}
