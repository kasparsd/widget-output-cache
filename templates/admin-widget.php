<p>
    <label>
        <input type="checkbox" name="widget-cache-exclude" value="<?php echo esc_attr( $object->id ); ?>" <?php echo checked( ( in_array( $object->id, $this->excluded_ids ) ), true, false ); ?> />
        <?php echo esc_html__( 'Exclude this widget from cache', 'widget-output-cache' ); ?>
    </label>

</p>