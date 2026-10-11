<?php
namespace SuiteEdge\Migrator\Reverse;

/** Every omission has an element path and an explicit application consequence. */
class Diagnostics {
    public $warnings = [];
    public $blockers = [];
    public $coverage = [];
    public $issues = [];
    public function add($path, $message, $blocking = false) {
        $text = 'Elementor to Divi [' . $path . ']: ' . $message;
        $this->warnings[] = $text;
        if ($blocking) { $this->blockers[] = $text; }
        $code = $blocking ? 'unsupported_mapping' : 'review_difference';
        if (strpos($message,'Unmapped setting')===0) { $code='unimplemented_control'; }
        elseif (strpos($message,'provider')!==false || strpos($message,'Provider')!==false) { $code='provider_dependency'; }
        elseif (strpos($message,'global style')!==false || strpos($message,'Global style')!==false) { $code='global_dependency'; }
        elseif (stripos($message,'outside the captured')!==false || strpos($message,'Unbound Elementor')!==false) { $code='unbound_source_css'; }
        elseif (stripos($message,'unsafe')!==false || stripos($message,'invalid')!==false || strpos($message,'do not match')!==false) { $code='invalid_source_value'; }
        $this->issues[]=['path'=>$path,'code'=>$code,'message'=>$message,'blocking'=>$blocking];
    }
    public function element($path, $type, $target, array $settings, array $used) {
        $missing = [];
        foreach ($settings as $key => $value) {
            if (isset($used[$key]) || in_array($key, ['_title', '_element_id', 'css_classes', '_css_classes'], true) || $value === '' || $value === null || $value === [] || $value === false) { continue; }
            $missing[] = $key;
            // An enabled unsupported control can change rendering or behavior. Do not
            // make a successful write mean that it was silently ignored.
            $this->add($path, 'Unmapped setting "' . $key . '". This active source control needs a tested destination adapter; retain the original source and review the mapping before applying.', true);
        }
        $path_blocked=false;foreach($this->issues as $issue){if($issue['blocking']&&$issue['path']===$path){$path_blocked=true;break;}}
        $disposition=$missing||$path_blocked||$target==='unsupported'||$target==='retained but blocked'?'retained_but_blocked':(strpos($target,'composition')!==false?'composed':'mapped');
        $this->coverage[] = ['path' => $path, 'source' => $type, 'target' => $target, 'disposition'=>$disposition, 'mapped_settings' => array_keys($used), 'unmapped_settings' => $missing];
    }
}
