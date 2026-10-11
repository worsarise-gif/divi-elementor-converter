<?php
namespace SuiteEdge\Migrator\Forms;
class DiviContactFormParser {
    public function parse_fields(array $children) {
        $fields = []; $ids = [];
        foreach ($children as $i=>$child) {
            if ($child['type'] !== 'et_pb_contact_field') { throw new \UnexpectedValueException('Unknown form child.'); }
            $a = $child['attributes'];
            $map = ['input'=>'text','email'=>'email','text'=>'textarea','checkbox'=>'checkbox','radio'=>'radio','select'=>'select'];
            if (!isset($map[$a['field_type'] ?? 'input'])) { throw new \UnexpectedValueException('Unknown contact field type.'); }
            $id = preg_replace('/[^a-zA-Z0-9_]/','_',$a['field_id'] ?? 'field_'.$i);
        // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Domain error is escaped at admin HTML / JSON consumers; preserve raw diagnostic data here.
            if ($id === '' || isset($ids[$id])) { throw new \UnexpectedValueException('Duplicate or invalid form field ID.'); } $ids[$id] = true;
            $field = ['_id'=>substr(hash('sha256',(string)$i.$id),0,7),'field_type'=>$map[$a['field_type'] ?? 'input'],'field_label'=>$a['field_title'] ?? $id,'placeholder'=>$a['field_title'] ?? '', 'custom_id'=>$id,'required'=>($a['required_mark'] ?? 'on') === 'on' ? 'true' : '', 'width'=>($a['fullwidth_field'] ?? 'off') === 'on' ? '100' : '50'];
            if (in_array($field['field_type'],['checkbox','radio','select'],true)) {
                $options = $a['select_options'] ?? $a['radio_options'] ?? $a['checkbox_options'] ?? $child['text_content'] ?? '';
                if ($options === '') { throw new \UnexpectedValueException('Form options are missing.'); }
                if (strpos($options,'{') !== false || strpos($options,'[') !== false) { throw new \UnexpectedValueException('Serialized form options require review.'); }
                $field['field_options'] = str_replace('|',"\n",html_entity_decode($options,ENT_QUOTES,'UTF-8'));
            }
            $fields[] = $field;
        }
        if (!$fields) { throw new \UnexpectedValueException('Form has no fields.'); }
        return $fields;
    }
}
