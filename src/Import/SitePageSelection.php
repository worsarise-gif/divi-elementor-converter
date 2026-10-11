<?php
namespace SuiteEdge\Migrator\Import;

/** A separate single-use, owner-bound mapping session for every page selection. */
class SitePageSelection {
    private function key($token) {
        if (!is_string($token) || !preg_match('/^[a-f0-9-]{36}$/D',$token)) { throw new \InvalidArgumentException('Invalid page selection session.'); }
        return 'suiteedge_site_selection_'.$token;
    }
    public function prepare($source,$target) {
        $reader=new SitePageSource();$snapshot=$reader->capture($source);
        if ($target!=='create_new') { $target=(string)$reader->id($target); (new \SuiteEdge\Migrator\Elementor\TargetResolver())->validate((int)$target,'page'); }
        $context=$snapshot['site_page'];
        $id=(new SourceRepository())->save($snapshot['json'],'Site page '.$context['source_page_id'].'.json',$context);
        $token=wp_generate_uuid4();$record=['owner'=>get_current_user_id(),'files'=>[['source_id'=>$id,'layout_index'=>0,'name'=>$context['source_page_title'].' / Source page #'.$context['source_page_id'],'preferred_target'=>$target]]];
        if (!set_transient($this->key($token),$record,HOUR_IN_SECONDS)) { throw new \RuntimeException('Could not save the page selection session.'); }
        return $token;
    }
    public function files($token) {
        $session=get_transient($this->key($token));
        if (!current_user_can('manage_options') || !is_array($session) || ($session['owner']??0)!==get_current_user_id() || !is_array($session['files']??null) || count($session['files'])!==1) { throw new \RuntimeException('Page selection expired or is unavailable for this user. Select the pages again.'); }
        return $session['files'];
    }
    public function consume($token) { delete_transient($this->key($token)); }
}
