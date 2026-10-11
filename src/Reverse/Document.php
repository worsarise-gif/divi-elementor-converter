<?php
namespace SuiteEdge\Migrator\Reverse;

/** Build editable Divi 4 sections/rows/columns from supported Classic Elementor trees. */
class Document {
    private $d;
    private $styles;
    private $widgets;
    private $shortcodes;
    private $kit = [];
    private $count = 0;
    private $ids = [];
    public function convert(array $snapshot) {
        $this->d=new Diagnostics();$this->styles=new Styles($this->d);$this->widgets=new Widgets($this->d,$snapshot['media']??[]);$this->shortcodes=new Shortcodes();$this->kit=$snapshot['kit_settings']??[];$this->count=0;$this->ids=[];
        if(!is_array($snapshot['data']??null)||!$snapshot['data']){throw new \UnexpectedValueException('Elementor content is empty.');}
        $this->validate($snapshot['data'],0);
        if(!empty($snapshot['origin']['content'])){
            $output=['engine'=>'divi4','content'=>$snapshot['origin']['content'],'blockers'=>[],'warnings'=>[],'coverage'=>[['path'=>'page','source'=>'verified Ace local forward migration','target'=>'original Divi 4 source','mapped_settings'=>['all original source attributes and content'],'unmapped_settings'=>[]]],'information'=>['Lossless round trip: current Elementor elements, page settings, global kit and original local Divi page/presets/globals match the saved forward-migration proof. The original Divi page content is restored without inferring a different DOM.','This proof is available only for local forward migrations created with this release. Any Elementor or dependency edit invalidates it and uses the semantic reverse mapper instead.']];
            $output['coverage']=[];$walk=function($nodes,$parent)use(&$walk,&$output){foreach($nodes as $node){$path=$parent.'/'.$node['id'];$output['coverage'][]=['path'=>$path,'source'=>$node['widgetType']??$node['elType'],'target'=>'verified original Divi document','disposition'=>'verified_original','mapped_settings'=>array_keys($node['settings']),'unmapped_settings'=>[]];$walk($node['elements'],$path);}};$walk($snapshot['data'],'root');
            $output['schema_version']=2;$output['source_element_count']=$this->count;$output['covered_element_count']=$this->count;$output['issues']=[];$output['versions']=['elementor'=>$snapshot['environment']['version']??(defined('ELEMENTOR_VERSION')?ELEMENTOR_VERSION:null),'divi'=>defined('ET_BUILDER_VERSION')?ET_BUILDER_VERSION:null,'converter'=>defined('SUITEEDGE_MIGRATOR_VERSION')?SUITEEDGE_MIGRATOR_VERSION:'1.8.0-rc.1'];
            (new Shortcodes())->validate($output);return $output;
        }
        $content='';
        try{foreach($snapshot['data'] as $i=>$node){
            $path='root/'.$node['id'];
            if($node['elType']==='widget'){$content.=$this->shortcodes->module('et_pb_section',[],$this->row([$node],[], $path.'/row'));continue;}
            $s=$this->styles->globals($node['settings'],$this->kit,$path);$used=[];$attrs=$this->styles->translate($s,'container',$used,$path);
            $direction=$s['flex_direction']??($node['elType']==='section'?'row':'column');
            $body='';
            if($direction==='row'||$node['elType']==='section'){$body=$this->row($node['elements'],$s,$path.'/row',$used);}
            elseif($direction==='column'){
                $pending=[];
                foreach($node['elements'] as $child){
                    if($child['elType']!=='widget'&&($child['settings']['flex_direction']??'column')==='row'){
                        if($pending){$body.=$this->row($pending,[],$path.'/content');$pending=[];}
                        $child_path=$path.'/'.$child['id'];$child_used=[];$child_s=$this->styles->globals($child['settings'],$this->kit,$child_path);$child_attrs=$this->styles->translate($child_s,'container',$child_used,$child_path);
                        $body.=$this->row($child['elements'],$child_s,$child_path,$child_used,$child_attrs);$this->layout_controls($child_s,$child_used,$child_path);$this->d->element($child_path,$child['elType'],'et_pb_row',$child_s,$child_used);
                    }else{$pending[]=$child;}
                }
                if($pending){$body.=$this->row($pending,[],$path.'/content');}
            }else{$this->d->add($path,'Reverse/wrapped container direction requires a dedicated layout mapping.',true);}
            $this->layout_controls($s,$used,$path);$this->d->element($path,$node['elType'],'et_pb_section',$s,$used);
            $content.=$this->shortcodes->module('et_pb_section',$attrs,$body);
        }
        }catch(\UnexpectedValueException $error){$this->d->add('page','Native layout adapter cannot represent this source control: '.$error->getMessage(),true);}
        $page=$snapshot['page_settings']??[];$page_used=array_fill_keys(['post_status','post_title','post_excerpt','featured_image','hide_title'],true);
        $this->d->element('page','page settings','destination page',$page,$page_used);
        if($this->d->blockers&&Composition::available()){
            try{return $this->finish((new Composition())->convert($snapshot),$snapshot);}
            catch(\Throwable $e){$this->d->add('page','Portable composition could not be compiled: '.$e->getMessage(),true);}
        }
        // Always describe descendants of an unsupported compound widget.
        $this->audit_children($snapshot['data'],'root');
        return $this->finish(['engine'=>'divi4','content'=>$content,'blockers'=>array_values(array_unique($this->d->blockers)),'warnings'=>array_values(array_unique($this->d->warnings)),'coverage'=>$this->d->coverage,'issues'=>$this->d->issues,'information'=>['Elementor to Divi converts the captured Classic page content; the Elementor source remains unchanged until explicitly selected as the destination.','Divi 4, its font/media providers and third-party code providers must be available on the destination. Sitewide headers/footers and template conditions are separate.','Divi and Elementor have different default module geometry, breakpoints and interactions. Compare desktop, tablet and mobile before publication.']],$snapshot);
    }
    private function finish(array $output,array $snapshot) {
        $output['schema_version']=2;$output['versions']=['elementor'=>$snapshot['environment']['version']??(defined('ELEMENTOR_VERSION')?ELEMENTOR_VERSION:null),'divi'=>defined('ET_BUILDER_VERSION')?ET_BUILDER_VERSION:null,'converter'=>defined('SUITEEDGE_MIGRATOR_VERSION')?SUITEEDGE_MIGRATOR_VERSION:'1.8.0'];
        $hide=($snapshot['page_settings']['hide_title']??'')==='yes';
        if($hide){$s=new Shortcodes();$output['content'].=$s->module('et_pb_section',['custom_padding'=>'0px|0px|0px|0px'],$s->module('et_pb_row',['custom_padding'=>'0px|0px|0px|0px'],$s->module('et_pb_column',['type'=>'4_4'],$s->module('et_pb_code',[],Presentation::marker($snapshot['kit_settings']['page_title_selector']??'h1.entry-title')))));}
        $output['presentation']=['hide_title'=>$hide];
        $paths=[];foreach($output['coverage'] as $record){if(isset($this->ids[basename($record['path'])])){$paths[basename($record['path'])]=true;}}
        $output['source_element_count']=$this->count;$output['covered_element_count']=count($paths);
        if(count($paths)!==$this->count){$message='Elementor to Divi [page]: Source coverage is incomplete. Application is blocked.';$output['blockers'][]=$message;$output['warnings'][]=$message;}
        return $output;
    }
    private function audit_children(array $nodes,$parent) {
        $covered=[];foreach($this->d->coverage as $record){$covered[basename($record['path'])]=true;}
        foreach($nodes as $node){$path=$parent.'/'.$node['id'];if(!isset($covered[$node['id']])){$this->d->add($path,'Child retained but blocked by its unsupported parent composition.',true);$this->d->element($path,$node['widgetType']??$node['elType'],'retained but blocked',$node['settings'],[]);}$this->audit_children($node['elements'],$path);}
    }
    private function validate(array $nodes,$depth) {
        if($depth>40){throw new \LengthException('Elementor nesting exceeds 40 levels.');}
        foreach($nodes as $node){
            if(++$this->count>5000){throw new \LengthException('Elementor content exceeds 5000 elements.');}
            if(!is_array($node)||!is_string($node['id']??null)||!preg_match('/^[a-zA-Z0-9_-]{1,64}$/D',$node['id'])||isset($this->ids[$node['id']])||!in_array($node['elType']??'', ['container','section','column','widget'],true)||!is_array($node['settings']??null)||!is_array($node['elements']??null)){throw new \UnexpectedValueException('Malformed, duplicate or Atomic Elementor element. Only Classic elements are supported.');}
            $this->ids[$node['id']]=true;
            if($node['elType']==='widget'&&(!is_string($node['widgetType']??null)||!preg_match('/^[a-z0-9_-]{1,100}$/D',$node['widgetType']))){throw new \UnexpectedValueException('Widget type is invalid.');}
            $this->validate($node['elements'],$depth+1);
        }
    }
    private function row(array $children,array $settings,$path,array &$used = [],array $attrs = []) {
        $columns=$children && !array_filter($children,function($n){return !in_array($n['elType'],['column','container'],true);});
        $body='';$count=$columns?count($children):1;
        if($count>6){$this->d->add($path,'Divi 4 supports at most six columns per regular row.',true);$columns=false;$count=1;}
        if($columns){
            $widths=[];
            foreach($children as $child){$width=$child['settings']['width']??null;if(is_array($width)&&($width['unit']??'')!=='%'&&($width['size']??'')!==''){$this->d->add($path.'/'.$child['id'],'Pixel/custom column widths require a retained source layout composition.',true);}$widths[]=isset($child['settings']['_column_size'])?(float)$child['settings']['_column_size']:(is_array($width)&&($width['unit']??'')==='%'?(float)$width['size']:100/$count);}
            if(abs(array_sum($widths)-100)>0.05){$this->d->add($path,'Explicit column widths do not form a supported Divi row.',true);}
            foreach($children as $i=>$child){
                $child_path=$path.'/'.$child['id'];$s=$this->styles->globals($child['settings'],$this->kit,$child_path);$child_used=[];
                $col=$this->styles->translate($s,'container',$child_used,$child_path);unset($col['width']);
                $col['type']=$this->fraction($widths[$i],$child_path);$child_used['_column_size']=true;$child_used['width']=true;
                $body.=$this->shortcodes->module('et_pb_column',$col,$this->modules($child['elements'],$child_path));
                $this->layout_controls($s,$child_used,$child_path);$this->d->element($child_path,$child['elType'],'et_pb_column',$s,$child_used);
            }
        }else{$body=$this->shortcodes->module('et_pb_column',['type'=>'4_4'],$this->modules($children,$path));}
        // Synthesized structural rows fill their source parent instead of adding
        // Divi's default boxed row width and vertical padding. Authored row
        // controls, translated above, retain precedence.
        $attrs+=['width'=>'100%','max_width'=>'100%','custom_padding'=>'0px|0px|0px|0px'];
        $attrs['use_custom_gutter']='on';$attrs['gutter_width']='1';
        if(isset($settings['flex_gap'])){
            $gap=$settings['flex_gap'];$size=is_array($gap)?($gap['column']??$gap['size']??''):$gap;
            $row=is_array($gap)?($gap['row']??$gap['size']??$size):$size;
            if(is_array($gap)&&is_numeric($size)&&is_numeric($row)&&($gap['unit']??'px')==='%'&&in_array((float)$size,[0.0,3.0,5.5,8.0],true)&&(float)$row===(float)$size){$attrs['gutter_width']=(string)(array_search((float)$size,[0.0,3.0,5.5,8.0],true)+1);$used['flex_gap']=true;}
            elseif(is_numeric($size)&&is_numeric($row)&&(float)$size===0.0&&(float)$row===0.0){$used['flex_gap']=true;}
        }
        return $this->shortcodes->module('et_pb_row',$attrs,$body);
    }
    private function modules(array $nodes,$path) {
        $body='';
        foreach($nodes as $node){$child_path=$path.'/'.$node['id'];
            try{
                if($node['elType']==='widget'){$body.=$this->widgets->convert($node,$this->kit,$child_path);}
                else{
                    // Empty, unstyled vertical wrappers are safely flattened; styled or
                    // horizontal nested containers cannot be silently folded into a column.
                    $s=$node['settings'];$used=[];$this->layout_controls($s,$used,$child_path);
                    if(($s['flex_direction']??'column')!=='column'){$this->d->add($child_path,'Nested row/container needs a Divi specialty layout.',true);}
                    $this->d->element($child_path,$node['elType'],'flattened vertical group',$s,$used);$body.=$this->modules($node['elements'],$child_path);
                }
            }catch(\UnexpectedValueException $e){$this->d->add($child_path,$e->getMessage(),true);}
        }
        return $body;
    }
    private function fraction($width,$path) {
        $sizes=['4_4'=>100,'1_2'=>50,'1_3'=>100/3,'2_3'=>200/3,'1_4'=>25,'3_4'=>75,'1_5'=>20,'2_5'=>40,'3_5'=>60,'4_5'=>80,'1_6'=>100/6,'5_6'=>500/6];
        foreach($sizes as $type=>$size){if(abs($width-$size)<0.05){return $type;}}
        $this->d->add($path,'Column width '.$width.'% has no native Divi fraction.',true);return '4_4';
    }
    private function layout_controls(array $s,array &$used,$path) {
        if(isset($s['flex_direction'])){$used['flex_direction']=true;}
        // A boxed inner wrapper has a separate width from its outer surface.
        // Do not consume this control until that two-surface mapping is accepted.
        if(isset($s['content_width'])&&$s['content_width']==='full'){$used['content_width']=true;}
        if(isset($s['container_type'])&&$s['container_type']==='flexbox'){$used['container_type']=true;}
        if(isset($s['flex_align_items'])&&in_array($s['flex_align_items'],['flex-start','stretch'],true)){$used['flex_align_items']=true;}
        foreach(['flex_direction_tablet','flex_direction_mobile'] as $key){if(isset($s[$key])&&$s[$key]==='column'){$used[$key]=true;}}
        foreach(['flex_gap_tablet','flex_gap_mobile'] as $key){if(isset($s[$key])&&is_array($s[$key])){$gap=$s[$key];$column=$gap['column']??$gap['size']??null;$row=$gap['row']??$gap['size']??null;if(is_numeric($column)&&is_numeric($row)&&(float)$column===0.0&&(float)$row===0.0){$used[$key]=true;}}}
    }
}
