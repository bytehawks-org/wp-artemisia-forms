<?php
/**
 * REST API forms controller.
 *
 * @package ByteHawks\ArtemisiaForms\Api
 */

namespace ByteHawks\ArtemisiaForms\Api;

/**
 * Controller for managing Forms via REST API.
 */
class FormsController {

    /**
     * The namespace of this controller's route.
     *
     * @var string
     */
    protected $namespace = 'artemisia/v1';

    /**
     * The base of this controller's route.
     *
     * @var string
     */
    protected $rest_base = 'forms';

    /**
     * Register the routes for forms.
     */
    public function register_routes() {
        // GET /wp-json/artemisia/v1/forms (List)
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            // POST /wp-json/artemisia/v1/forms (Create)
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'create_item' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
            ),
        ) );

        // GET/POST /wp-json/artemisia/v1/forms/{id} (Single Read / Update)
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
            ),
            array(
                'methods'             => \WP_REST_Server::EDITABLE, // POST, PUT, PATCH
                'callback'            => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
            ),
        ) );
    }

    /**
     * Check permissions for reading forms.
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
        // Al momento permettiamo di leggere le configurazioni dei form pubblicamente 
        // in modo che se un utente visualizza il form nel sito frontend possa caricarlo via API.
        // TODO: Aggiungere logiche specifiche se si legge dalla dashboard o dal frontend.
        return true;
    }

    /**
     * Check permissions for creating or updating forms.
     * Only admins can edit or create forms.
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return true|\WP_Error
     */
    public function create_item_permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new \WP_Error( 'rest_forbidden', __( 'You do not have permissions to manage forms.', 'artemisia-forms' ), array( 'status' => 401 ) );
        }
        return true;
    }

    /**
     * Retrieves a single form by ID.
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return \WP_REST_Response|\WP_Error Response object on success.
     */
    public function get_item( $request ) {
        $id = (int) $request['id'];
        $form = \ByteHawks\ArtemisiaForms\Models\Form::get( $id );

        if ( ! $form ) {
            return new \WP_Error( 'rest_form_not_found', __( 'Form not found', 'artemisia-forms' ), array( 'status' => 404 ) );
        }

        $data = array(
            'id'         => $form->id,
            'title'      => $form->title,
            'status'     => $form->status,
            'config'     => $form->config,
            'created_at' => $form->created_at,
            'updated_at' => $form->updated_at,
        );

        return rest_ensure_response( $data );
    }

    /**
     * Create a new form.
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return \WP_REST_Response|\WP_Error Response object on success.
     */
    public function create_item( $request ) {
        $form = new \ByteHawks\ArtemisiaForms\Models\Form();

        $form->title  = sanitize_text_field( $request->get_param( 'title' ) ?? __( 'New Form', 'artemisia-forms' ) );
        $form->status = sanitize_text_field( $request->get_param( 'status' ) ?? 'draft' );
        $form->config = $request->get_param( 'config' ) ?? array();

        $saved_id = $form->save();

        if ( ! $saved_id ) {
            return new \WP_Error( 'rest_form_create_error', __( 'Error creating form', 'artemisia-forms' ), array( 'status' => 500 ) );
        }

        return $this->get_item( array( 'id' => $saved_id ) );
    }

    /**
     * Update an existing form.
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return \WP_REST_Response|\WP_Error Response object on success.
     */
    public function update_item( $request ) {
        $id = (int) $request['id'];
        $form = \ByteHawks\ArtemisiaForms\Models\Form::get( $id );

        if ( ! $form ) {
            return new \WP_Error( 'rest_form_not_found', __( 'Form not found', 'artemisia-forms' ), array( 'status' => 404 ) );
        }

        if ( $request->has_param( 'title' ) ) {
            $form->title = sanitize_text_field( $request->get_param( 'title' ) );
        }
        if ( $request->has_param( 'status' ) ) {
            $form->status = sanitize_text_field( $request->get_param( 'status' ) );
        }
        if ( $request->has_param( 'config' ) ) {
            // Config usually comes as array from REST JSON payload
            $form->config = $request->get_param( 'config' );
        }

        $saved_id = $form->save();

        if ( ! $saved_id ) {
            return new \WP_Error( 'rest_form_update_error', __( 'Error updating form', 'artemisia-forms' ), array( 'status' => 500 ) );
        }

        return $this->get_item( array( 'id' => $id ) );
    }

    /**
     * Retrieves a collection of forms.
     *
     * @param \WP_REST_Request $request Full details about the request.
     * @return \WP_REST_Response Response object on success.
     */
    public function get_items( $request ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'artemisia_forms';
        
        $forms = $wpdb->get_results( "SELECT * FROM {$table_name}" );

        $data = array();
        
        if ( ! empty( $forms ) ) {
            foreach ( $forms as $form ) {
                $item = array(
                    'id'         => (int) $form->id,
                    'title'      => $form->title,
                    'status'     => $form->status,
                    // Parse raw config back to JSON format for React to consume
                    'config'     => json_decode( $form->config, true ),
                    'created_at' => $form->created_at,
                    'updated_at' => $form->updated_at,
                );
                $data[] = $item;
            }
        }

        return rest_ensure_response( $data );
    }

}
