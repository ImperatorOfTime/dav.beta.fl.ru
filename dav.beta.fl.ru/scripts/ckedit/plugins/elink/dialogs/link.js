﻿CKEDITOR.dialog.add("elink",function(j){var k=CKEDITOR.plugins.elink,i=j.lang.elink,l=/^javascript:/,m=/^mailto:([^?]+)(?:\?(.+))?$/,n=/subject=([^;?:@&=$,\/]*)/,o=/body=([^;?:@&=$,\/]*)/,p=/^#(.*)$/,q=/^((?:http|https|ftp|news):\/\/)?(.*)$/,r=/^(_(?:self|top|parent|blank))$/,s=/^javascript:void\(location\.href='mailto:'\+String\.fromCharCode\(([^)]+)\)(?:\+'(.*)')?\)$/,t=/^javascript:([^(]+)\(([^)]+)\)$/,u=/\s*window.open\(\s*this\.href\s*,\s*(?:'([^']*)'|null)\s*,\s*'([^']*)'\s*\)\s*;\s*return\s*false;*\s*/,
v=/(?:^|,)([^=]+)=(\d+|yes|no)/gi,w=function(d,e){var a=e&&(e.data("cke-saved-href")||e.getAttribute("href"))||"",b,f,c={};a.match(l)&&("encode"==emailProtection?a=a.replace(s,function(a,b,c){return"mailto:"+String.fromCharCode.apply(String,b.split(","))+(c&&unescapeSingleQuote(c))}):emailProtection&&a.replace(t,function(a,b,d){if(b==compiledProtectionFunction.name){c.type="email";for(var a=c.email={},b=/(^')|('$)/g,d=d.match(/[^,\s]+/g),e=d.length,f,g,h=0;h<e;h++)g=decodeURIComponent(unescapeSingleQuote(d[h].replace(b,
""))),f=compiledProtectionFunction.params[h].toLowerCase(),a[f]=g;a.address=[a.name,a.domain].join("@")}}));if(!c.type)if(b=a.match(p))c.type="anchor",c.anchor={},c.anchor.name=c.anchor.id=b[1];else if(b=a.match(m)){f=a.match(n);a=a.match(o);c.type="email";var g=c.email={};g.address=b[1];f&&(g.subject=decodeURIComponent(f[1]));a&&(g.body=decodeURIComponent(a[1]))}else a&&(f=a.match(q))?(c.type="url",c.url={},c.url.protocol=f[1],c.url.url=f[2]):c.type="url";if(e){b=e.getAttribute("target");c.target=
{};c.adv={};if(b)b.match(r)?c.target.type=c.target.name=b:(c.target.type="frame",c.target.name=b);else if(b=(b=e.data("cke-pa-onclick")||e.getAttribute("onclick"))&&b.match(u)){c.target.type="popup";for(c.target.name=b[1];a=v.exec(b[2]);)("yes"==a[2]||"1"==a[2])&&!(a[1]in{height:1,width:1,top:1,left:1})?c.target[a[1]]=!0:isFinite(a[2])&&(c.target[a[1]]=a[2])}b=function(a,b){var d=e.getAttribute(b);null!==d&&(c.adv[a]=d||"")};b("advId","id");b("advLangDir","dir");b("advAccessKey","accessKey");c.adv.advName=
e.data("cke-saved-name")||e.getAttribute("name")||"";b("advLangCode","lang");b("advTabIndex","tabindex");b("advTitle","title");b("advContentType","type");b("advCharset","charset");b("advStyles","style");b("advRel","rel")}c.anchors=[];this._.selectedElement=e;return c};return{title:i.title,resizable:CKEDITOR.DIALOG_RESIZE_NONE,minWidth:350,minHeight:50,contents:[{id:"popup-link",label:"",elements:[{type:"text",id:"elink",label:"",onLoad:function(){this.allowOnChange=!0},validate:function(){return CKEDITOR.dialog.validate.notEmpty(i.noUrl).apply(this)},
setup:function(d){this.allowOnChange=!1;d.url&&this.setValue(d.url.url);this.allowOnChange=!0},commit:function(d){d.url||(d.url={});d.url.url=this.getValue();this.allowOnChange=!1}}]}],onShow:function(){var d=this.getParentEditor(),e=d.getSelection(),a=null;(a=k.getSelectedLink(d))&&a.hasAttribute("href")?e.selectElement(a):a=null;this.setupContent(w.apply(this,[d,a]))},onOk:function(){var d={},e=[],a={},b=this.getParentEditor();this.commitContent(a);var f=a.url&&CKEDITOR.tools.trim(a.url.url)||"";
d["data-cke-saved-href"]=0===f.indexOf("http")?f:"http://"+f;f=b.getSelection();d.href=d["data-cke-saved-href"];if(this._.selectedElement){var b=this._.selectedElement,c=b.data("cke-saved-href"),g=b.getHtml();b.setAttributes(d);b.removeAttributes(e);a.adv&&(a.adv.advName&&CKEDITOR.plugins.link.synAnchorSelector)&&b.addClass(b.getChildCount()?"cke_anchor":"cke_anchor_empty");if(c==g||"email"==a.type&&-1!=g.indexOf("@"))b.setHtml("email"==a.type?a.email.address:d["data-cke-saved-href"]);f.selectElement(b);
delete this._.selectedElement}else e=f.getRanges(1)[0],e.collapsed&&(a=new CKEDITOR.dom.text("email"==a.type?a.email.address:d["data-cke-saved-href"],b.document),e.insertNode(a),e.selectNodeContents(a)),d=new CKEDITOR.style({element:"a",attributes:d}),d.type=CKEDITOR.STYLE_INLINE,d.applyToRange(e),e.select()}}});