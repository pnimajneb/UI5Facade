ace.define("ace/mode/scss_highlight_rules",["require","exports","module","ace/lib/oop","ace/lib/lang","ace/mode/text_highlight_rules"],function(r,e,m){"use strict";var o=r("../lib/oop");var l=r("../lib/lang");var T=r("./text_highlight_rules").TextHighlightRules;var S=function(){var p=l.arrayToMap((function(){var b=("-webkit-|-moz-|-o-|-ms-|-svg-|-pie-|-khtml-").split("|");var d=("appearance|background-clip|background-inline-policy|background-origin|"+"background-size|binding|border-bottom-colors|border-left-colors|"+"border-right-colors|border-top-colors|border-end|border-end-color|"+"border-end-style|border-end-width|border-image|border-start|"+"border-start-color|border-start-style|border-start-width|box-align|"+"box-direction|box-flex|box-flexgroup|box-ordinal-group|box-orient|"+"box-pack|box-sizing|column-count|column-gap|column-width|column-rule|"+"column-rule-width|column-rule-style|column-rule-color|float-edge|"+"font-feature-settings|font-language-override|force-broken-image-icon|"+"image-region|margin-end|margin-start|opacity|outline|outline-color|"+"outline-offset|outline-radius|outline-radius-bottomleft|"+"outline-radius-bottomright|outline-radius-topleft|outline-radius-topright|"+"outline-style|outline-width|padding-end|padding-start|stack-sizing|"+"tab-size|text-blink|text-decoration-color|text-decoration-line|"+"text-decoration-style|transform|transform-origin|transition|"+"transition-delay|transition-duration|transition-property|"+"transition-timing-function|user-focus|user-input|user-modify|user-select|"+"window-shadow|border-radius").split("|");var p=("azimuth|background-attachment|background-color|background-image|"+"background-position|background-repeat|background|border-bottom-color|"+"border-bottom-style|border-bottom-width|border-bottom|border-collapse|"+"border-color|border-left-color|border-left-style|border-left-width|"+"border-left|border-right-color|border-right-style|border-right-width|"+"border-right|border-spacing|border-style|border-top-color|"+"border-top-style|border-top-width|border-top|border-width|border|bottom|"+"box-shadow|box-sizing|caption-side|clear|clip|color|content|counter-increment|"+"counter-reset|cue-after|cue-before|cue|cursor|direction|display|"+"elevation|empty-cells|float|font-family|font-size-adjust|font-size|"+"font-stretch|font-style|font-variant|font-weight|font|height|left|"+"letter-spacing|line-height|list-style-image|list-style-position|"+"list-style-type|list-style|margin-bottom|margin-left|margin-right|"+"margin-top|marker-offset|margin|marks|max-height|max-width|min-height|"+"min-width|opacity|orphans|outline-color|"+"outline-style|outline-width|outline|overflow|overflow-x|overflow-y|padding-bottom|"+"padding-left|padding-right|padding-top|padding|page-break-after|"+"page-break-before|page-break-inside|page|pause-after|pause-before|"+"pause|pitch-range|pitch|play-during|position|quotes|richness|right|"+"size|speak-header|speak-numeral|speak-punctuation|speech-rate|speak|"+"stress|table-layout|text-align|text-decoration|text-indent|"+"text-shadow|text-transform|top|unicode-bidi|vertical-align|"+"visibility|voice-family|volume|white-space|widows|width|word-spacing|"+"z-index").split("|");var g=[];for(var i=0,h=b.length;i<h;i++){Array.prototype.push.apply(g,((b[i]+d.join("|"+b[i])).split("|")));}Array.prototype.push.apply(g,d);Array.prototype.push.apply(g,p);return g;})());var f=l.arrayToMap(("hsl|hsla|rgb|rgba|url|attr|counter|counters|abs|adjust_color|adjust_hue|"+"alpha|join|blue|ceil|change_color|comparable|complement|darken|desaturate|"+"floor|grayscale|green|hue|if|invert|join|length|lighten|lightness|mix|"+"nth|opacify|opacity|percentage|quote|red|round|saturate|saturation|"+"scale_color|transparentize|type_of|unit|unitless|unqoute").split("|"));var c=l.arrayToMap(("absolute|all-scroll|always|armenian|auto|baseline|below|bidi-override|"+"block|bold|bolder|border-box|both|bottom|break-all|break-word|capitalize|center|"+"char|circle|cjk-ideographic|col-resize|collapse|content-box|crosshair|dashed|"+"decimal-leading-zero|decimal|default|disabled|disc|"+"distribute-all-lines|distribute-letter|distribute-space|"+"distribute|dotted|double|e-resize|ellipsis|fixed|georgian|groove|"+"hand|hebrew|help|hidden|hiragana-iroha|hiragana|horizontal|"+"ideograph-alpha|ideograph-numeric|ideograph-parenthesis|"+"ideograph-space|inactive|inherit|inline-block|inline|inset|inside|"+"inter-ideograph|inter-word|italic|justify|katakana-iroha|katakana|"+"keep-all|left|lighter|line-edge|line-through|line|list-item|loose|"+"lower-alpha|lower-greek|lower-latin|lower-roman|lowercase|lr-tb|ltr|"+"medium|middle|move|n-resize|ne-resize|newspaper|no-drop|no-repeat|"+"nw-resize|none|normal|not-allowed|nowrap|oblique|outset|outside|"+"overline|pointer|progress|relative|repeat-x|repeat-y|repeat|right|"+"ridge|row-resize|rtl|s-resize|scroll|se-resize|separate|small-caps|"+"solid|square|static|strict|super|sw-resize|table-footer-group|"+"table-header-group|tb-rl|text-bottom|text-top|text|thick|thin|top|"+"transparent|underline|upper-alpha|upper-latin|upper-roman|uppercase|"+"vertical-ideographic|vertical-text|visible|w-resize|wait|whitespace|"+"zero").split("|"));var a=l.arrayToMap(("aqua|black|blue|fuchsia|gray|green|lime|maroon|navy|olive|orange|"+"purple|red|silver|teal|white|yellow").split("|"));var k=l.arrayToMap(("@mixin|@extend|@include|@import|@media|@debug|@warn|@if|@for|@each|@while|@else|@font-face|@-webkit-keyframes|if|and|!default|module|def|end|declare").split("|"));var t=l.arrayToMap(("a|abbr|acronym|address|applet|area|article|aside|audio|b|base|basefont|bdo|"+"big|blockquote|body|br|button|canvas|caption|center|cite|code|col|colgroup|"+"command|datalist|dd|del|details|dfn|dir|div|dl|dt|em|embed|fieldset|"+"figcaption|figure|font|footer|form|frame|frameset|h1|h2|h3|h4|h5|h6|head|"+"header|hgroup|hr|html|i|iframe|img|input|ins|keygen|kbd|label|legend|li|"+"link|map|mark|menu|meta|meter|nav|noframes|noscript|object|ol|optgroup|"+"option|output|p|param|pre|progress|q|rp|rt|ruby|s|samp|script|section|select|"+"small|source|span|strike|strong|style|sub|summary|sup|table|tbody|td|"+"textarea|tfoot|th|thead|time|title|tr|tt|u|ul|var|video|wbr|xmp").split("|"));var n="\\-?(?:(?:[0-9]+)|(?:[0-9]*\\.[0-9]+))";this.$rules={"start":[{token:"comment",regex:"\\/\\/.*$"},{token:"comment",regex:"\\/\\*",next:"comment"},{token:"string",regex:'["](?:(?:\\\\.)|(?:[^"\\\\]))*?["]'},{token:"string",regex:'["].*\\\\$',next:"qqstring"},{token:"string",regex:"['](?:(?:\\\\.)|(?:[^'\\\\]))*?[']"},{token:"string",regex:"['].*\\\\$",next:"qstring"},{token:"constant.numeric",regex:n+"(?:em|ex|px|cm|mm|in|pt|pc|deg|rad|grad|ms|s|hz|khz|%)"},{token:"constant.numeric",regex:"#[a-f0-9]{6}"},{token:"constant.numeric",regex:"#[a-f0-9]{3}"},{token:"constant.numeric",regex:n},{token:["support.function","string","support.function"],regex:"(url\\()(.*)(\\))"},{token:function(v){if(p.hasOwnProperty(v.toLowerCase()))return"support.type";if(k.hasOwnProperty(v))return"keyword";else if(c.hasOwnProperty(v))return"constant.language";else if(f.hasOwnProperty(v))return"support.function";else if(a.hasOwnProperty(v.toLowerCase()))return"support.constant.color";else if(t.hasOwnProperty(v.toLowerCase()))return"variable.language";else return"text";},regex:"\\-?[@a-z_][@a-z0-9_\\-]*"},{token:"variable",regex:"[a-z_\\-$][a-z0-9_\\-$]*\\b"},{token:"variable.language",regex:"#[a-z0-9-_]+"},{token:"variable.language",regex:"\\.[a-z0-9-_]+"},{token:"variable.language",regex:":[a-z0-9-_]+"},{token:"constant",regex:"[a-z0-9-_]+"},{token:"keyword.operator",regex:"<|>|<=|>=|==|!=|-|%|#|\\+|\\$|\\+|\\*"},{token:"paren.lparen",regex:"[[({]"},{token:"paren.rparen",regex:"[\\])}]"},{token:"text",regex:"\\s+"},{caseInsensitive:true}],"comment":[{token:"comment",regex:".*?\\*\\/",next:"start"},{token:"comment",regex:".+"}],"qqstring":[{token:"string",regex:'(?:(?:\\\\.)|(?:[^"\\\\]))*?"',next:"start"},{token:"string",regex:'.+'}],"qstring":[{token:"string",regex:"(?:(?:\\\\.)|(?:[^'\\\\]))*?'",next:"start"},{token:"string",regex:'.+'}]};};o.inherits(S,T);e.ScssHighlightRules=S;});ace.define("ace/mode/matching_brace_outdent",["require","exports","module","ace/range"],function(r,e,m){"use strict";var R=r("../range").Range;var M=function(){};(function(){this.checkOutdent=function(l,i){if(!/^\s+$/.test(l))return false;return/^\s*\}/.test(i);};this.autoOutdent=function(d,a){var l=d.getLine(a);var b=l.match(/^(\s*\})/);if(!b)return 0;var c=b[1].length;var o=d.findMatchingBracket({row:a,column:c});if(!o||o.row==a)return 0;var i=this.$getIndent(d.getLine(o.row));d.replace(new R(a,0,a,c-1),i);};this.$getIndent=function(l){return l.match(/^\s*/)[0];};}).call(M.prototype);e.MatchingBraceOutdent=M;});ace.define("ace/mode/behaviour/css",["require","exports","module","ace/lib/oop","ace/mode/behaviour","ace/mode/behaviour/cstyle","ace/token_iterator"],function(r,e,m){"use strict";var o=r("../../lib/oop");var B=r("../behaviour").Behaviour;var C=r("./cstyle").CstyleBehaviour;var T=r("../../token_iterator").TokenIterator;var a=function(){this.inherit(C);this.add("colon","insertion",function(s,b,c,d,t){if(t===':'){var f=c.getCursorPosition();var i=new T(d,f.row,f.column);var g=i.getCurrentToken();if(g&&g.value.match(/\s+/)){g=i.stepBackward();}if(g&&g.type==='support.type'){var l=d.doc.getLine(f.row);var h=l.substring(f.column,f.column+1);if(h===':'){return{text:'',selection:[1,1]}}if(!l.substring(f.column).match(/^\s*;/)){return{text:':;',selection:[1,1]}}}}});this.add("colon","deletion",function(s,b,c,d,f){var g=d.doc.getTextRange(f);if(!f.isMultiLine()&&g===':'){var h=c.getCursorPosition();var i=new T(d,h.row,h.column);var t=i.getCurrentToken();if(t&&t.value.match(/\s+/)){t=i.stepBackward();}if(t&&t.type==='support.type'){var l=d.doc.getLine(f.start.row);var j=l.substring(f.end.column,f.end.column+1);if(j===';'){f.end.column++;return f;}}}});this.add("semicolon","insertion",function(s,b,c,d,t){if(t===';'){var f=c.getCursorPosition();var l=d.doc.getLine(f.row);var g=l.substring(f.column,f.column+1);if(g===';'){return{text:'',selection:[1,1]}}}});};o.inherits(a,C);e.CssBehaviour=a;});ace.define("ace/mode/folding/cstyle",["require","exports","module","ace/lib/oop","ace/range","ace/mode/folding/fold_mode"],function(r,e,a){"use strict";var o=r("../../lib/oop");var R=r("../../range").Range;var B=r("./fold_mode").FoldMode;var F=e.FoldMode=function(c){if(c){this.foldingStartMarker=new RegExp(this.foldingStartMarker.source.replace(/\|[^|]*?$/,"|"+c.start));this.foldingStopMarker=new RegExp(this.foldingStopMarker.source.replace(/\|[^|]*?$/,"|"+c.end));}};o.inherits(F,B);(function(){this.foldingStartMarker=/(\{|\[)[^\}\]]*$|^\s*(\/\*)/;this.foldingStopMarker=/^[^\[\{]*(\}|\])|^[\s\*]*(\*\/)/;this.singleLineBlockCommentRe=/^\s*(\/\*).*\*\/\s*$/;this.tripleStarBlockCommentRe=/^\s*(\/\*\*\*).*\*\/\s*$/;this.startRegionRe=/^\s*(\/\*|\/\/)#?region\b/;this._getFoldWidgetBase=this.getFoldWidget;this.getFoldWidget=function(s,f,b){var l=s.getLine(b);if(this.singleLineBlockCommentRe.test(l)){if(!this.startRegionRe.test(l)&&!this.tripleStarBlockCommentRe.test(l))return"";}var c=this._getFoldWidgetBase(s,f,b);if(!c&&this.startRegionRe.test(l))return"start";return c;};this.getFoldWidgetRange=function(s,f,b,c){var l=s.getLine(b);if(this.startRegionRe.test(l))return this.getCommentRegionBlock(s,l,b);var m=l.match(this.foldingStartMarker);if(m){var i=m.index;if(m[1])return this.openingBracketBlock(s,m[1],b,i);var d=s.getCommentFoldRange(b,i+m[0].length,1);if(d&&!d.isMultiLine()){if(c){d=this.getSectionRange(s,b);}else if(f!="all")d=null;}return d;}if(f==="markbegin")return;var m=l.match(this.foldingStopMarker);if(m){var i=m.index+m[0].length;if(m[1])return this.closingBracketBlock(s,m[1],b,i);return s.getCommentFoldRange(b,i,-1);}};this.getSectionRange=function(s,b){var l=s.getLine(b);var c=l.search(/\S/);var d=b;var f=l.length;b=b+1;var g=b;var m=s.getLength();while(++b<m){l=s.getLine(b);var i=l.search(/\S/);if(i===-1)continue;if(c>i)break;var h=this.getFoldWidgetRange(s,"all",b);if(h){if(h.start.row<=d){break;}else if(h.isMultiLine()){b=h.end.row;}else if(c==i){break;}}g=b;}return new R(d,f,g,s.getLine(g).length);};this.getCommentRegionBlock=function(s,l,b){var c=l.search(/\s*$/);var d=s.getLength();var f=b;var g=/^\s*(?:\/\*|\/\/|--)#?(end)?region\b/;var h=1;while(++b<d){l=s.getLine(b);var m=g.exec(l);if(!m)continue;if(m[1])h--;else h++;if(!h)break;}var i=b;if(i>f){return new R(f,c,i,l.length);}};}).call(F.prototype);});ace.define("ace/mode/scss",["require","exports","module","ace/lib/oop","ace/mode/text","ace/mode/scss_highlight_rules","ace/mode/matching_brace_outdent","ace/mode/behaviour/css","ace/mode/folding/cstyle"],function(r,e,m){"use strict";var o=r("../lib/oop");var T=r("./text").Mode;var S=r("./scss_highlight_rules").ScssHighlightRules;var M=r("./matching_brace_outdent").MatchingBraceOutdent;var C=r("./behaviour/css").CssBehaviour;var a=r("./folding/cstyle").FoldMode;var b=function(){this.HighlightRules=S;this.$outdent=new M();this.$behaviour=new C();this.foldingRules=new a();};o.inherits(b,T);(function(){this.lineCommentStart="//";this.blockComment={start:"/*",end:"*/"};this.getNextLineIndent=function(s,l,t){var i=this.$getIndent(l);var c=this.getTokenizer().getLineTokens(l,s).tokens;if(c.length&&c[c.length-1].type=="comment"){return i;}var d=l.match(/^.*\{\s*$/);if(d){i+=t;}return i;};this.checkOutdent=function(s,l,i){return this.$outdent.checkOutdent(l,i);};this.autoOutdent=function(s,d,c){this.$outdent.autoOutdent(d,c);};this.$id="ace/mode/scss";}).call(b.prototype);e.Mode=b;});