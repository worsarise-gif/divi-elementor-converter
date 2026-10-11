<?php
namespace SuiteEdge\Migrator\Reverse;

/** Font resources belong to each saved output, not a request-wide enqueue cache. */
class Fonts {
    public $dependencies=[];
    public function links(array $families, Diagnostics $diagnostics) {
        $html='';$local=(array)get_option('_elementor_local_google_fonts',[]);
        foreach(array_unique($families) as $family){
            $family=\SuiteEdge\Migrator\Style\FontFamily::normalize($family);
            if(\SuiteEdge\Migrator\Style\FontFamily::is_generic($family)){continue;}
            $type=\Elementor\Fonts::get_font_type($family);
            if($type===\Elementor\Fonts::SYSTEM){continue;}
            $registered=$local[sanitize_key($family)]['url']??'';
            if($registered!==''){$url=(new Values())->url($registered);try{$this->dependencies[]=(new Providers())->font($url);}catch(\Throwable $error){$diagnostics->add('fonts',$error->getMessage(),true);continue;}}
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Respect the source builder's public font-loading filter.
            elseif(in_array($type,[\Elementor\Fonts::GOOGLE,\Elementor\Fonts::EARLYACCESS],true)&&get_option('elementor_google_font','1')==='1'&&apply_filters('elementor/frontend/print_google_fonts',true)){
                // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Normalized font name in domain diagnostics; report HTML/JSON consumers escape at their boundary.
                if(get_option('elementor_local_google_fonts','0')){throw new \UnexpectedValueException('Local Google font "'.$family.'" is not cached. Load the original Elementor page to generate its matching local font files, then reconvert.');}
                $frontend=\Elementor\Plugin::$instance->frontend;
                $url=$type===\Elementor\Fonts::GOOGLE?$frontend->get_stable_google_fonts_url([$family]):$frontend->get_early_access_google_font_urls([$family])[0];
            }else{
                $diagnostics->add('fonts','Font "'.$family.'" needs a verified local stylesheet/provider before portable composition. Register the matching font files and reconvert.',true);continue;
            }
            // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- Persist a font resource in saved Divi content, independent of request-wide enqueue state.
            $html.='<link rel="stylesheet" href="'.esc_url($url).'" data-ace-source-font="'.esc_attr($family).'">';
        }
        return $html;
    }
}
