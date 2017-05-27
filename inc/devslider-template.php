<div class="devslider-container">
    <div class="devslider">
        <?php
        foreach ( $slides as $slide ) :
            $description = get_post_meta( $slide->ID, '_slide_desc', true );

            $image_url = wp_get_attachment_url( get_post_thumbnail_id( $slide->ID ) );

            $style = '';

            if ( $image_url ) {
                $style = 'style="background-image: url( '. esc_url( $image_url ) .' );"';
            }

        ?>
        <div class="slide" <?php echo $style; ?>>
            <div class="title"><?php echo esc_html( $description ); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>