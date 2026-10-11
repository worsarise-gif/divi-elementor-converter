<?php
namespace SuiteEdge\Migrator\Reverse;

/** A balanced CSS block scanner: commas in functions/strings are not selectors. */
class CssScope {
    public function compile($css, $scope, $depth=0) {
        if($depth>40){throw new \LengthException('Composition CSS nesting exceeds 40 levels.');}
        if (!is_string($css) || strlen($css)>2097152 || strpos(strtolower($css),'</style')!==false) { throw new \UnexpectedValueException('Invalid or oversized composition CSS.'); }
        if($depth===0){$css=$this->portable($css);}
        $out='';$offset=0;$length=strlen($css);
        while($offset<$length){
            $open=$this->delimiter($css,$offset,'{;');
            if($open===null){if(trim(substr($css,$offset))!==''){throw new \UnexpectedValueException('Incomplete CSS rule.');}break;}
            $head=trim(substr($css,$offset,$open-$offset));
            if($css[$open]===';'){if($head!==''&&!preg_match('/^@charset\s/i',$head)){throw new \UnexpectedValueException('CSS imports or statement rules require a provider mapping.');}$offset=$open+1;continue;}
            $close=$this->close($css,$open);$body=substr($css,$open+1,$close-$open-1);$offset=$close+1;
            if($head===''){if(trim($body)===''){continue;}throw new \UnexpectedValueException('Empty CSS selector.');}
            if(preg_match('/^@(media|supports|layer|container)\b/i',$head)){$out.=$head.'{'.$this->compile($body,$scope,$depth+1).'}';}
            elseif(preg_match('/^@(?:-webkit-)?keyframes\b|^@font-face\b|^@property\b/i',$head)){$out.=$head.'{'.$body.'}';}
            elseif($head[0]==='@'){throw new \UnexpectedValueException('Unsupported CSS block rule.');}
            else{$selectors=$this->selectors($head);$out.=implode(',',array_map(function($s)use($scope){return $s===':root'?$scope:$scope.' '.$s;},$selectors)).'{'.$body.'}';}
        }
        return $out;
    }
    private function portable($css) {
        // Divi removes raw newlines from Code content. Preserve their CSS token
        // separation, without changing quoted values or escaped characters.
        $out='';$quote=null;
        for($i=0,$n=strlen($css);$i<$n;$i++){
            $c=$css[$i];
            if($c==='\\'){
                if($i+1===$n){throw new \UnexpectedValueException('Incomplete CSS escape.');}
                $next=$css[++$i];
                if($next==="\n"||$next==="\r"||$next==="\f"){
                    if($quote===null){throw new \UnexpectedValueException('Invalid CSS escaped newline.');}
                    if($next==="\r"&&($css[$i+1]??'')==="\n"){$i++;}continue;
                }
                $out.=$c.$next;continue;
            }
            if($quote!==null){if($c==="\n"||$c==="\r"||$c==="\f"){throw new \UnexpectedValueException('Invalid newline in CSS string.');}$out.=$c;if($c===$quote){$quote=null;}continue;}
            if($c==='"'||$c==="'"){$quote=$c;$out.=$c;continue;}
            if($c==='/'&&($css[$i+1]??'')==='*'){$end=strpos($css,'*/',$i+2);if($end===false){throw new \UnexpectedValueException('Unclosed CSS comment.');}$i=$end+1;continue;}
            if(strpos(" \t\r\n\f",$c)!==false){if($out!==''&&substr($out,-1)!==' '){$out.=' ';}continue;}
            $out.=$c;
        }
        if($quote!==null){throw new \UnexpectedValueException('Unclosed CSS string.');}return trim($out);
    }
    private function delimiter($css,$start,$wanted) {
        $quote=null;$round=0;$square=0;
        for($i=$start,$n=strlen($css);$i<$n;$i++){
            $c=$css[$i];if($c==='\\'){$i++;continue;}
            if($quote!==null){if($c===$quote){$quote=null;}continue;}
            if($c==='"'||$c==="'"){$quote=$c;continue;}
            if($c==='/'&&substr($css,$i,2)==='/*'){$end=strpos($css,'*/',$i+2);if($end===false){throw new \UnexpectedValueException('Unclosed CSS comment.');}$i=$end+1;continue;}
            if($c==='('){$round++;}elseif($c===')'){$round--;}elseif($c==='['){$square++;}elseif($c===']'){$square--;}
            if($round<0||$square<0){throw new \UnexpectedValueException('Unbalanced CSS selector.');}
            if(!$round&&!$square&&strpos($wanted,$c)!==false){return $i;}
        }
        if($quote!==null||$round||$square){throw new \UnexpectedValueException('Unclosed CSS value.');}return null;
    }
    private function close($css,$open) {
        $depth=1;$cursor=$open+1;
        while(($next=$this->delimiter($css,$cursor,'{}'))!==null){$depth+=$css[$next]==='{'?1:-1;if(!$depth){return $next;}$cursor=$next+1;}
        throw new \UnexpectedValueException('Unclosed CSS block.');
    }
    private function selectors($head) {
        $out=[];$cursor=0;
        while(($comma=$this->delimiter($head,$cursor,','))!==null){$out[]=trim(substr($head,$cursor,$comma-$cursor));$cursor=$comma+1;}
        $out[]=trim(substr($head,$cursor));if(in_array('',$out,true)){throw new \UnexpectedValueException('Invalid CSS selector list.');}return $out;
    }
}
