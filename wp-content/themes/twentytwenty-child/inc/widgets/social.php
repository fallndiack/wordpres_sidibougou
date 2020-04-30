<?php
class Agencia_Social_Widget extends WP_Widget {

    public $fields = [];

    public function __construct()
    {
        parent::__construct('agencia_social_widget', __('Social widget', 'agencia'));
        $this->fields = [
            'title' => __('Title', 'agencia'),
            'credits' =>  __('Credits', 'agencia'),
            'twitter' => 'Twitter',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
        ];
    }

    public function widget(array $args, array $instance): void {
        echo $args['before_widget'];
        if (isset($instance['title'])) {
            $title = apply_filters('widget_title', $instance['title']);
            echo $args['before_title'] . $title . $args['after_title'];
        }
        $template = locate_template('template-parts/widgets/social.php');
        if (!empty($template)) {
            include($template);
        }
        echo $args['after_widget'];
    }

    public function form (array $instance): void {
        foreach($this->fields as $field => $label) {
            $value = $instance[$field] ?? '';
            ?>
            <p>
                <label for="<?= $this->get_field_id($field) ?>"><?= esc_html($label) ?></label>
                <input 
                    type="text"
                    class="widefat"
                    name="<?= $this->get_field_name($field) ?>"
                    id="<?= $this->get_field_id($field) ?>"
                    value="<?= esc_attr($value) ?>"
                    >
            </p>
            <?php
        }
    }

    public function update(array $newInstance, array $oldInstance): array
    {
        $output = [];
        foreach($this->fields as $field => $label) {
            if (!empty($newInstance[$field])) {
                $output[$field] = $newInstance[$field];
            }
        }
        return $output;
    }

}