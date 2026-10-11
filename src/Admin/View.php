<?php
namespace SuiteEdge\Migrator\Admin;

/** Shared, server-rendered navigation. Usable without JavaScript. */
class View {
    public static function url($page) { return admin_url('admin.php?page=suiteedge-migrator'.$page); }
    public static function start($title, $description = '', $section = 'overview', $step = 0, array $steps = null) {
        $navigation = [
            'overview'=>['',__('Overview','suiteedge-divi-elementor-migrator')],
            'pages'=>['-import',__('Pages & Posts','suiteedge-divi-elementor-migrator')],
            'site-pages'=>['-site-page',__('Convert Site Page','suiteedge-divi-elementor-migrator')],
            'reverse'=>['-reverse',__('Elementor to Divi','suiteedge-divi-elementor-migrator')],
            'templates'=>['-theme-builder',__('Headers & Footers','suiteedge-divi-elementor-migrator')],
            'jobs'=>['-jobs',__('Migration Jobs','suiteedge-divi-elementor-migrator')],
            'guide'=>['-guide',__('User Guide','suiteedge-divi-elementor-migrator')],
            'settings'=>['-settings',__('Settings','suiteedge-divi-elementor-migrator')],
        ];
        echo '<div class="wrap suiteedge-app"><header class="suiteedge-masthead"><div class="suiteedge-brand"><span class="suiteedge-mark" aria-hidden="true"><img src="'.esc_url(SUITEEDGE_MIGRATOR_PLUGIN_URL.'assets/icons/logo.png').'" alt="" width="72" height="72" decoding="async"></span><div><span class="suiteedge-brand-name">Ace</span><span class="suiteedge-brand-purpose">'.esc_html__('Divi and Elementor migration','suiteedge-divi-elementor-migrator').'</span></div></div><span class="suiteedge-release">'.esc_html(SUITEEDGE_MIGRATOR_VERSION).'</span></header>';
        echo '<nav class="suiteedge-nav" aria-label="'.esc_attr__('Migrator navigation','suiteedge-divi-elementor-migrator').'">';
        foreach ($navigation as $key=>$entry) { echo '<a href="'.esc_url(self::url($entry[0])).'"'.($section === $key ? ' aria-current="page"' : '').'>'.esc_html($entry[1]).'</a>'; }
        echo '</nav><div class="suiteedge-page-heading"><h1>'.esc_html($title).'</h1>';
        if ($description !== '') { echo '<p class="suiteedge-lead">'.esc_html($description).'</p>'; }
        echo '</div>';
        if ($step) {
            echo '<ol class="suiteedge-workflow" aria-label="'.esc_attr__('Migration progress','suiteedge-divi-elementor-migrator').'">';
            foreach ($steps ?? [__('Upload','suiteedge-divi-elementor-migrator'),__('Map & convert','suiteedge-divi-elementor-migrator'),__('Review results','suiteedge-divi-elementor-migrator'),__('Apply & verify','suiteedge-divi-elementor-migrator')] as $index=>$label) {
                $position = $index + 1;
                echo '<li'.($position === $step ? ' aria-current="step"' : '').'><span aria-hidden="true">'.(int)$position.'</span>'.esc_html($label).'</li>';
            }
            echo '</ol>';
        }
    }
    public static function empty_state($title, $description, $page, $label) {
        echo '<div class="suiteedge-empty"><h2>'.esc_html($title).'</h2><p>'.esc_html($description).'</p><a class="button button-primary" href="'.esc_url(self::url($page)).'">'.esc_html($label).'</a></div>';
    }
}
