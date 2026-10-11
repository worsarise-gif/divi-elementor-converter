<?php
namespace SuiteEdge\Migrator\Elementor;
use SuiteEdge\Migrator\Style\FontFamily;

/** Missing font binaries are a review item; never silently substitute another family. */
class FontAvailability {
    public function warnings(\SuiteEdge\Migrator\Model\Document $document) {
        if (!class_exists('Elementor\Fonts')) { return []; }
        $families = []; $warnings = [];
        $walk = function ($nodes) use (&$walk,&$families) {
            foreach ($nodes as $node) { foreach (FontFamily::in_node($node) as $family) { $families[$family] = true; } $walk($node->children); }
        };
        $walk($document->nodes);
        $google_disabled = function_exists('get_option') && get_option('elementor_google_font','1') !== '1';
        foreach (array_keys($families) as $family) {
            if (FontFamily::is_generic($family)) { continue; }
            $type = \Elementor\Fonts::get_font_type($family);
            // Elementor removes Google families from its catalogue when loading is disabled.
            if ($google_disabled && (!$type || in_array($type,['googlefonts','google','earlyaccess'],true))) { $warnings[] = 'Google font loading is disabled in Elementor. Source font families were preserved; enable loading or register local matching fonts before publishing.'; }
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Raw font diagnostic is escaped by admin HTML/JSON report consumers.
            elseif (!$type) { $warnings[] = 'Font "'.$family.'" is not registered in Elementor on this site. Its family was preserved; install/register the matching font files before publishing.'; }
        }
        return array_values(array_unique($warnings));
    }
}
