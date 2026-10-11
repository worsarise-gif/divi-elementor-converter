<?php
namespace SuiteEdge\Migrator\Import;

/** Explicit, source-file-scoped category mapping. Numeric source IDs never become a query. */
class BlogCategoryMapping {
    public function ids($value) {
        if ($value === '' || $value === 'all') { return []; }
        if (!is_string($value) || strlen($value)>1000 || !preg_match('/^[1-9][0-9]{0,9}(?:,[1-9][0-9]{0,9})*$/D',$value)) { throw new \UnexpectedValueException('Core Blog: unsupported category query. Choose explicit categories or all categories in Divi.'); }
        $ids=array_values(array_unique(array_map('intval',explode(',',$value))));
        if (count($ids)>50 || min($ids)<1) { throw new \UnexpectedValueException('Core Blog: category IDs must be positive and limited to 50.'); }
        return $ids;
    }
    public function pending(array $files) {
        $out=[];
        foreach($files as $file) {
            $source=$file['source_id'];$record=(new SourceRepository())->get($source);
            $export=(new DiviExportReader())->read_json($record['json']);$ids=[];
            $walk=function($nodes)use(&$walk,&$ids,$export) {
                foreach($nodes as $n) {
                    if($n['type']==='et_pb_blog') {
                        try {
                            $a=(new \SuiteEdge\Migrator\Divi\PresetResolver())->resolve_export($n['type'],$n['attributes'],$export['presets']);
                            $ids=array_merge($ids,$this->ids($a['include_categories']??''));
                        } catch(\UnexpectedValueException $e) { /* The converter reports/protects unsupported source modes. */ }
                    }
                    $walk($n['children']);
                }
            };
            $layouts=isset($record['site_page'])?$export['layouts']:[$export['layouts'][$file['layout_index']]];
            foreach($layouts as $layout){$walk((new \SuiteEdge\Migrator\Divi\ShortcodeTokenizer())->tokenize($layout['post_content']));}
            if($ids) {$out[$source]=['name'=>$record['name'],'ids'=>array_values(array_unique(array_merge($out[$source]['ids']??[],$ids)))];}
        }
        return $out;
    }
    public function target($id) {
        if (!(is_string($id)||is_int($id)) || !ctype_digit((string)$id) || (int)$id<1 || !function_exists('get_term')) { throw new \UnexpectedValueException('Core Blog: select a destination category in Blog category mappings, then reconvert. Source category IDs are not destination IDs.'); }
        $term=get_term((int)$id,'category');
        if(!$term || is_wp_error($term)) { throw new \UnexpectedValueException('Core Blog: mapped destination category no longer exists. Choose a category and analyze again.'); }
        return $term;
    }
    public function validate(array $mapping) {
        if(count($mapping)>100) {throw new \InvalidArgumentException('Too many Blog category mappings.');}
        $out=[];
        foreach($mapping as $source=>$categories) {
            if(!is_string($source)||!preg_match('/^[a-f0-9-]{36}$/D',$source)||!is_array($categories)||count($categories)>50) {throw new \InvalidArgumentException('Invalid Blog category mapping.');}
            (new SourceRepository())->get($source);
            foreach($categories as $id=>$target) {
                if(!ctype_digit((string)$id)||(int)$id<1||!(is_string($target)||is_int($target))||!ctype_digit((string)$target)) {throw new \InvalidArgumentException('Invalid Blog category selection.');}
                if((int)$target===0) {continue;}
                $term=$this->target($target);$out[$source][(int)$id]=(int)$term->term_id;
            }
        }
        return $out;
    }
}
