<?php
namespace SuiteEdge\Migrator\Mapping;
class IdMapper {
    public function get_mapped_id($source,$type = 'global_module') {
        $map = get_option('suiteedge_id_map',[]); $id = (int)($map[$type][(string)$source] ?? 0);
        return $id && get_post_type($id) === 'elementor_library' ? $id : 0;
    }
    public function set_mapped_id($source,$target,$type = 'global_module') {
        if (!current_user_can('manage_options') || get_post_type((int)$target) !== 'elementor_library') { throw new \InvalidArgumentException('An editable Elementor template is required.'); }
        $map = get_option('suiteedge_id_map',[]); $map[$type][(string)$source] = (int)$target; update_option('suiteedge_id_map',$map,false);
    }
}
