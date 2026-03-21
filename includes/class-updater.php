<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Filtro hooks para actualización nativa desde GitHub.
 */
class WPER_Updater {

    private $plugin_slug;
    private $version;
    private $github_url;

    public function __construct( $plugin_file, $version, $github_url ) {
        $this->plugin_slug = plugin_basename( $plugin_file );
        $this->version     = $version;
        $this->github_url  = $github_url;
    }

    public function init() {
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
        add_filter( 'plugins_api', array( $this, 'get_plugin_info' ), 20, 3 );
        add_action( 'upgrader_process_complete', array( $this, 'clear_transient' ), 10, 2 );
    }

    /**
     * Compara versión instalada con la última versión en GitHub (Releases).
     */
    public function check_for_updates( $transient ) {
        if ( empty( $transient->checked ) ) return $transient;

        $response = $this->get_github_release();
        if ( ! $response ) return $transient;

        // Limpiamos la 'v' si el tag_name la incluye (e.g. v1.2.0)
        $remote_version = ltrim( $response->tag_name, 'v' );

        if ( version_compare( $this->version, $remote_version, '<' ) ) {
            $obj = new stdClass();
            $obj->slug        = 'wp-events-registration';
            $obj->plugin      = $this->plugin_slug;
            $obj->new_version = $remote_version;
            $obj->url         = $this->github_url;
            $obj->package     = $response->zipball_url;

            $transient->response[ $this->plugin_slug ] = $obj;
        }

        return $transient;
    }

    /**
     * Muestra información detallada en el popup de "Ver detalles".
     */
    public function get_plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) return $result;
        if ( isset($args->slug) && $args->slug !== 'wp-events-registration' ) return $result;

        $response = $this->get_github_release();
        if ( ! $response ) return $result;

        $res = new stdClass();
        $res->name           = 'WP Events Registration';
        $res->slug           = 'wp-events-registration';
        $res->version        = ltrim( $response->tag_name, 'v' );
        $res->author         = 'José Joaquín Sánchez Fernández';
        $res->author_profile = 'https://ajedrezcoimbra.com';
        $res->homepage       = $this->github_url;
        $res->download_link  = $response->zipball_url;
        $res->sections       = array(
            'description' => 'Plugin de gestión de eventos e inscripciones para sitios de WordPress.',
            'changelog'   => isset($response->body) ? wp_kses_post($response->body) : '-'
        );

        return $res;
    }

    /**
     * Limpia la caché tras una actualización exitosa.
     */
    public function clear_transient( $upgrader_object, $options ) {
        if ( $options['action'] === 'update' && $options['type'] === 'plugin' && isset( $options['plugins'] ) ) {
            foreach ( $options['plugins'] as $plugin ) {
                if ( $plugin === $this->plugin_slug ) {
                    delete_site_transient( 'update_plugins' );
                }
            }
        }
    }

    /**
     * Obtiene el último release publicado en el repositorio GitHub.
     */
    private function get_github_release() {
        $api_url = 'https://api.github.com/repos/AjedrezCoimbra/wp-events-registration/releases/latest';
        
        // Es una buena práctica cachear la respuesta para no saturar la API en cada carga de página
        $cached = get_transient( 'wper_github_release_cache' );
        if ( $cached !== false ) return $cached;

        $response = wp_remote_get( $api_url, array(
            'timeout'    => 10,
            'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' )
        ));

        if ( is_wp_error( $response ) ) return false;

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body );

        if ( empty($data) || !isset($data->tag_name) ) return false;

        // Cacheamos por 12 horas
        set_transient( 'wper_github_release_cache', $data, 12 * HOUR_IN_SECONDS );

        return $data;
    }

}
