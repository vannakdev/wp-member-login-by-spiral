<?php
function r_enqueue_block_editor_assets(){
    wp_register_script(
        'sml_blocks_bundle',
        plugins_url( '/custom_blocks/version_two/dist/bundle.js', SML_PLUGIN_URL ),
        sml_get_block_dependencies(),
        filemtime( plugin_dir_path( SML_PLUGIN_URL ) . '/custom_blocks/version_two/dist/bundle.js' )
    );

    wp_enqueue_script( 'sml_blocks_bundle' );
}

function r_enqueue_block_assets(){
    wp_register_style(
        'sml_blocks',
        plugins_url( '/custom_blocks/version_two/dist/blocks-main.css', SML_PLUGIN_URL )
    );

    wp_enqueue_style( 'sml_blocks' );
}
function sml_get_block_dependencies() {

    global $pagenow;
    
    if ( $pagenow === 'widgets.php' ) {
        return array( 'wp-edit-widgets',
            'wp-blocks',
            'wp-i18n',
            'wp-element', );
    }
    
    return array( 'wp-editor',
            'wp-blocks',
            'wp-i18n',
            'wp-element', );
}