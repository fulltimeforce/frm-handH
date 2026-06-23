<?php

class Header_Menu_Walker extends Walker_Nav_Menu
{

    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<div class=\"submenu\">\n";
        $output .= "$indent\t<div class=\"submenu_content\">\n";
    }

    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent\t</div>\n";
        $output .= "$indent</div>\n";
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {

        if ($depth === 0) {

            $classes = (array) ($item->classes ?? []);
            $has_children = in_array('menu-item-has-children', $classes, true);

            // Si tiene hijos: imprimimos <li> manual + <button>
            if ($has_children) {

                $title = ! empty($item->title) ? esc_html($item->title) : '';

                // clases del <li>
                $li_classes = array_filter(array_map('sanitize_html_class', $classes));
                $li_class_attr = !empty($li_classes) ? ' class="' . esc_attr(implode(' ', $li_classes)) . '"' : '';

                $output .= '<li id="menu-item-' . intval($item->ID) . '"' . $li_class_attr . '>';

                $output .= '<button type="button" class="type--button" aria-label="' . esc_attr($title) . '">';
                $output .= $title;

                $output .= '<svg xmlns="http://www.w3.org/2000/svg" width="8" height="4" viewBox="0 0 8 4" fill="none">';
                $output .= '<path d="M1 1L4 3L7 1" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>';
                $output .= '</svg>';

                $output .= '</button>';

                return; // IMPORTANTE: ya abrimos el <li>, se cerrará en end_el()
            }

            // Top level sin hijos: normal
            parent::start_el($output, $item, $depth, $args, $id);
            return;
        }

        $url   = ! empty($item->url) ? esc_url($item->url) : '#';
        $title = ! empty($item->title) ? esc_html($item->title) : '';

        $svg_html = '';

        if (function_exists('get_field')) {
            $icon = get_field('icon_item_header', 'menu_item_' . $item->ID);

            // 1) SVG inline (texto)
            if (is_string($icon) && trim($icon) !== '') {
                // Si parece SVG inline, lo dejamos
                if (stripos($icon, '<svg') !== false) {
                    $svg_html = $icon;
                }
                // 2) URL directa (string)
                elseif (filter_var($icon, FILTER_VALIDATE_URL)) {
                    $svg_html = '<img src="' . esc_url($icon) . '" alt="' . $title . '" class="submenu-icon" title="' . $title . '" />';
                }
            }

            // 3) Array (Image field)
            if (!$svg_html && is_array($icon) && !empty($icon['url'])) {
                $svg_html = '<img src="' . esc_url($icon['url']) . '" alt="' . $title . '" title="' . $title . '" class="submenu-icon" />';
            }

            // 4) ID (Image return format = ID)
            if (!$svg_html && is_numeric($icon)) {
                $img = wp_get_attachment_image_src((int)$icon, 'full');
                if (!empty($img[0])) {
                    $svg_html = '<img src="' . esc_url($img[0]) . '" alt="' . $title . '" title="' . $title . '" class="submenu-icon" />';
                }
            }
        }

        $output .= '<a href="' . $url . '" class="submenu-link" aria-label="' . esc_attr($title) . '">';
        $output .= $svg_html;
        $output .= '<p>' . $title . '</p>';
        $output .= '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        if ($depth === 0) {
            $classes = (array) ($item->classes ?? []);
            $has_children = in_array('menu-item-has-children', $classes, true);

            if ($has_children) {
                $output .= "</li>\n";
                return;
            }

            parent::end_el($output, $item, $depth, $args);
            return;
        }

        $output .= "\n";
    }
}

class Footer_Links_Walker extends Walker_Nav_Menu
{

    // Evita que WP imprima wrappers de submenú
    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {

        // Footer: normalmente será 1 nivel. Si quieres forzar solo top-level:
        if ($depth > 0) return;

        $url   = ! empty($item->url) ? esc_url($item->url) : '#';
        $title = ! empty($item->title) ? esc_html($item->title) : '';

        // Si quieres conservar clases del item (opcional)
        $classes = (array) ($item->classes ?? []);
        $classes = array_filter(array_map('sanitize_html_class', $classes));
        $class_attr = ! empty($classes) ? ' class="' . esc_attr(implode(' ', $classes)) . '"' : '';

        $output .= '<a href="' . $url . '"' . $class_attr . '>' . $title . '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        if ($depth > 0) return;
        $output .= "\n";
    }
}

class Footer_Terms_Walker extends Walker_Nav_Menu
{
    private $item_count = 0;

    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        // Separador |
        if ($this->item_count > 0) {
            $output .= ' | ';
        }

        $atts           = '';
        $atts_title     = !empty($item->attr_title) ? esc_attr($item->attr_title) : esc_attr($item->title);
        $atts_href      = !empty($item->url) ? esc_url($item->url) : '';

        $atts .= ' title="' . $atts_title . '"';
        $atts .= ' href="' . $atts_href . '"';

        $output .= '<a' . $atts . '>';
        $output .= esc_html($item->title);
        $output .= '</a>';

        $this->item_count++;
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {}
}

class Footer_Bold_Links_Walker extends Walker_Nav_Menu
{
    private int $item_count = 0;

    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        // Separador como <p>|</p> entre links
        if ($this->item_count > 0) {
            $output .= '<p>|</p>';
        }

        $title = !empty($item->attr_title) ? $item->attr_title : $item->title;
        $href  = !empty($item->url) ? $item->url : '#';

        $output .= sprintf(
            '<a href="%s" title="%s">%s</a>',
            esc_url($href),
            esc_attr($title),
            esc_html($item->title)
        );

        $this->item_count++;
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {}
}