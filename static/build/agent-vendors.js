(function(a,b){function cy(a){return f.isWindow(a)?a:a.nodeType===9?a.defaultView||a.parentWindow:!1}function cv(a){if(!cj[a]){var b=f("<"+a+">").appendTo("body"),d=b.css("display");
b.remove();if(d==="none"||d===""){ck||(ck=c.createElement("iframe"),ck.frameBorder=ck.width=ck.height=0),c.body.appendChild(ck);
if(!cl||!ck.createElement){cl=(ck.contentWindow||ck.contentDocument).document,cl.write("<!doctype><html><body></body></html>")
}b=cl.createElement(a),cl.body.appendChild(b),d=f.css(b,"display"),c.body.removeChild(ck)}cj[a]=d}return cj[a]}function cu(a,b){var c={};
f.each(cp.concat.apply([],cp.slice(0,b)),function(){c[this]=a});return c}function ct(){cq=b}function cs(){setTimeout(ct,0);
return cq=f.now()}function ci(){try{return new a.ActiveXObject("Microsoft.XMLHTTP")}catch(b){}}function ch(){try{return new a.XMLHttpRequest
}catch(b){}}function cb(a,c){a.dataFilter&&(c=a.dataFilter(c,a.dataType));var d=a.dataTypes,e={},g,h,i=d.length,j,k=d[0],l,m,n,o,p;
for(g=1;g<i;g++){if(g===1){for(h in a.converters){typeof h=="string"&&(e[h.toLowerCase()]=a.converters[h])}}l=k,k=d[g];if(k==="*"){k=l
}else{if(l!=="*"&&l!==k){m=l+" "+k,n=e[m]||e["* "+k];if(!n){p=b;for(o in e){j=o.split(" ");if(j[0]===l||j[0]==="*"){p=e[j[1]+" "+k];
if(p){o=e[o],o===!0?n=p:p===!0&&(n=o);break}}}}!n&&!p&&f.error("No conversion from "+m.replace(" "," to ")),n!==!0&&(c=n?n(c):p(o(c)))
}}}return c}function ca(a,c,d){var e=a.contents,f=a.dataTypes,g=a.responseFields,h,i,j,k;for(i in g){i in d&&(c[g[i]]=d[i])
}while(f[0]==="*"){f.shift(),h===b&&(h=a.mimeType||c.getResponseHeader("content-type"))}if(h){for(i in e){if(e[i]&&e[i].test(h)){f.unshift(i);
break}}}if(f[0] in d){j=f[0]}else{for(i in d){if(!f[0]||a.converters[i+" "+f[0]]){j=i;break}k||(k=i)}j=j||k}if(j){j!==f[0]&&f.unshift(j);
return d[j]}}function b_(a,b,c,d){if(f.isArray(b)){f.each(b,function(b,e){c||bF.test(a)?d(a,e):b_(a+"["+(typeof e=="object"||f.isArray(e)?b:"")+"]",e,c,d)
})}else{if(!c&&b!=null&&typeof b=="object"){for(var e in b){b_(a+"["+e+"]",b[e],c,d)}}else{d(a,b)}}}function b$(a,c,d,e,f,g){f=f||c.dataTypes[0],g=g||{},g[f]=!0;
var h=a[f],i=0,j=h?h.length:0,k=a===bU,l;for(;i<j&&(k||!l);i++){l=h[i](c,d,e),typeof l=="string"&&(!k||g[l]?l=b:(c.dataTypes.unshift(l),l=b$(a,c,d,e,l,g)))
}(k||!l)&&!g["*"]&&(l=b$(a,c,d,e,"*",g));return l}function bZ(a){return function(b,c){typeof b!="string"&&(c=b,b="*");if(f.isFunction(c)){var d=b.toLowerCase().split(bQ),e=0,g=d.length,h,i,j;
for(;e<g;e++){h=d[e],j=/^\+/.test(h),j&&(h=h.substr(1)||"*"),i=a[h]=a[h]||[],i[j?"unshift":"push"](c)}}}}function bD(a,b,c){var d=b==="width"?bx:by,e=b==="width"?a.offsetWidth:a.offsetHeight;
if(c==="border"){return e}f.each(d,function(){c||(e-=parseFloat(f.css(a,"padding"+this))||0),c==="margin"?e+=parseFloat(f.css(a,"margin"+this))||0:e-=parseFloat(f.css(a,"border"+this+"Width"))||0
});return e}function bn(a,b){b.src?f.ajax({url:b.src,async:!1,dataType:"script"}):f.globalEval((b.text||b.textContent||b.innerHTML||"").replace(bf,"/*$0*/")),b.parentNode&&b.parentNode.removeChild(b)
}function bm(a){f.nodeName(a,"input")?bl(a):a.getElementsByTagName&&f.grep(a.getElementsByTagName("input"),bl)}function bl(a){if(a.type==="checkbox"||a.type==="radio"){a.defaultChecked=a.checked
}}function bk(a){return"getElementsByTagName" in a?a.getElementsByTagName("*"):"querySelectorAll" in a?a.querySelectorAll("*"):[]
}function bj(a,b){var c;if(b.nodeType===1){b.clearAttributes&&b.clearAttributes(),b.mergeAttributes&&b.mergeAttributes(a),c=b.nodeName.toLowerCase();
if(c==="object"){b.outerHTML=a.outerHTML}else{if(c!=="input"||a.type!=="checkbox"&&a.type!=="radio"){if(c==="option"){b.selected=a.defaultSelected
}else{if(c==="input"||c==="textarea"){b.defaultValue=a.defaultValue}}}else{a.checked&&(b.defaultChecked=b.checked=a.checked),b.value!==a.value&&(b.value=a.value)
}}b.removeAttribute(f.expando)}}function bi(a,b){if(b.nodeType===1&&!!f.hasData(a)){var c=f.expando,d=f.data(a),e=f.data(b,d);
if(d=d[c]){var g=d.events;e=e[c]=f.extend({},d);if(g){delete e.handle,e.events={};for(var h in g){for(var i=0,j=g[h].length;
i<j;i++){f.event.add(b,h+(g[h][i].namespace?".":"")+g[h][i].namespace,g[h][i],g[h][i].data)}}}}}}function bh(a,b){return f.nodeName(a,"table")?a.getElementsByTagName("tbody")[0]||a.appendChild(a.ownerDocument.createElement("tbody")):a
}function X(a,b,c){b=b||0;if(f.isFunction(b)){return f.grep(a,function(a,d){var e=!!b.call(a,d,a);return e===c})}if(b.nodeType){return f.grep(a,function(a,d){return a===b===c
})}if(typeof b=="string"){var d=f.grep(a,function(a){return a.nodeType===1});if(S.test(b)){return f.filter(b,d,!c)}b=f.filter(b,d)
}return f.grep(a,function(a,d){return f.inArray(a,b)>=0===c})}function W(a){return !a||!a.parentNode||a.parentNode.nodeType===11
}function O(a,b){return(a&&a!=="*"?a+".":"")+b.replace(A,"`").replace(B,"&")}function N(a){var b,c,d,e,g,h,i,j,k,l,m,n,o,p=[],q=[],r=f._data(this,"events");
if(!(a.liveFired===this||!r||!r.live||a.target.disabled||a.button&&a.type==="click")){a.namespace&&(n=new RegExp("(^|\\.)"+a.namespace.split(".").join("\\.(?:.*\\.)?")+"(\\.|$)")),a.liveFired=this;
var s=r.live.slice(0);for(i=0;i<s.length;i++){g=s[i],g.origType.replace(y,"")===a.type?q.push(g.selector):s.splice(i--,1)
}e=f(a.target).closest(q,a.currentTarget);for(j=0,k=e.length;j<k;j++){m=e[j];for(i=0;i<s.length;i++){g=s[i];if(m.selector===g.selector&&(!n||n.test(g.namespace))&&!m.elem.disabled){h=m.elem,d=null;
if(g.preType==="mouseenter"||g.preType==="mouseleave"){a.type=g.preType,d=f(a.relatedTarget).closest(g.selector)[0],d&&f.contains(h,d)&&(d=h)
}(!d||d!==h)&&p.push({elem:h,handleObj:g,level:m.level})}}}for(j=0,k=p.length;j<k;j++){e=p[j];if(c&&e.level>c){break}a.currentTarget=e.elem,a.data=e.handleObj.data,a.handleObj=e.handleObj,o=e.handleObj.origHandler.apply(e.elem,arguments);
if(o===!1||a.isPropagationStopped()){c=e.level,o===!1&&(b=!1);if(a.isImmediatePropagationStopped()){break}}}return b}}function L(a,c,d){var e=f.extend({},d[0]);
e.type=a,e.originalEvent={},e.liveFired=b,f.event.handle.call(c,e),e.isDefaultPrevented()&&d[0].preventDefault()}function F(){return !0
}function E(){return !1}function m(a,c,d){var e=c+"defer",g=c+"queue",h=c+"mark",i=f.data(a,e,b,!0);i&&(d==="queue"||!f.data(a,g,b,!0))&&(d==="mark"||!f.data(a,h,b,!0))&&setTimeout(function(){!f.data(a,g,b,!0)&&!f.data(a,h,b,!0)&&(f.removeData(a,e,!0),i.resolve())
},0)}function l(a){for(var b in a){if(b!=="toJSON"){return !1}}return !0}function k(a,c,d){if(d===b&&a.nodeType===1){var e="data-"+c.replace(j,"$1-$2").toLowerCase();
d=a.getAttribute(e);if(typeof d=="string"){try{d=d==="true"?!0:d==="false"?!1:d==="null"?null:f.isNaN(d)?i.test(d)?f.parseJSON(d):d:parseFloat(d)
}catch(g){}f.data(a,c,d)}else{d=b}}return d}var c=a.document,d=a.navigator,e=a.location,f=function(){function H(){if(!e.isReady){try{c.documentElement.doScroll("left")
}catch(a){setTimeout(H,1);return}e.ready()}}var e=function(a,b){return new e.fn.init(a,b,h)},f=a.jQuery,g=a.$,h,i=/^(?:[^<]*(<[\w\W]+>)[^>]*$|#([\w\-]*)$)/,j=/\S/,k=/^\s+/,l=/\s+$/,m=/\d/,n=/^<(\w+)\s*\/?>(?:<\/\1>)?$/,o=/^[\],:{}\s]*$/,p=/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,q=/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,r=/(?:^|:|,)(?:\s*\[)+/g,s=/(webkit)[ \/]([\w.]+)/,t=/(opera)(?:.*version)?[ \/]([\w.]+)/,u=/(msie) ([\w.]+)/,v=/(mozilla)(?:.*? rv:([\w.]+))?/,w=d.userAgent,x,y,z,A=Object.prototype.toString,B=Object.prototype.hasOwnProperty,C=Array.prototype.push,D=Array.prototype.slice,E=String.prototype.trim,F=Array.prototype.indexOf,G={};
e.fn=e.prototype={constructor:e,init:function(a,d,f){var g,h,j,k;if(!a){return this}if(a.nodeType){this.context=this[0]=a,this.length=1;
return this}if(a==="body"&&!d&&c.body){this.context=c,this[0]=c.body,this.selector=a,this.length=1;return this}if(typeof a=="string"){a.charAt(0)!=="<"||a.charAt(a.length-1)!==">"||a.length<3?g=i.exec(a):g=[null,a,null];
if(g&&(g[1]||!d)){if(g[1]){d=d instanceof e?d[0]:d,k=d?d.ownerDocument||d:c,j=n.exec(a),j?e.isPlainObject(d)?(a=[c.createElement(j[1])],e.fn.attr.call(a,d,!0)):a=[k.createElement(j[1])]:(j=e.buildFragment([g[1]],[k]),a=(j.cacheable?e.clone(j.fragment):j.fragment).childNodes);
return e.merge(this,a)}h=c.getElementById(g[2]);if(h&&h.parentNode){if(h.id!==g[2]){return f.find(a)}this.length=1,this[0]=h
}this.context=c,this.selector=a;return this}return !d||d.jquery?(d||f).find(a):this.constructor(d).find(a)}if(e.isFunction(a)){return f.ready(a)
}a.selector!==b&&(this.selector=a.selector,this.context=a.context);return e.makeArray(a,this)},selector:"",jquery:"1.6.1",length:0,size:function(){return this.length
},toArray:function(){return D.call(this,0)},get:function(a){return a==null?this.toArray():a<0?this[this.length+a]:this[a]
},pushStack:function(a,b,c){var d=this.constructor();e.isArray(a)?C.apply(d,a):e.merge(d,a),d.prevObject=this,d.context=this.context,b==="find"?d.selector=this.selector+(this.selector?" ":"")+c:b&&(d.selector=this.selector+"."+b+"("+c+")");
return d},each:function(a,b){return e.each(this,a,b)},ready:function(a){e.bindReady(),y.done(a);return this},eq:function(a){return a===-1?this.slice(a):this.slice(a,+a+1)
},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},slice:function(){return this.pushStack(D.apply(this,arguments),"slice",D.call(arguments).join(","))
},map:function(a){return this.pushStack(e.map(this,function(b,c){return a.call(b,c,b)}))},end:function(){return this.prevObject||this.constructor(null)
},push:C,sort:[].sort,splice:[].splice},e.fn.init.prototype=e.fn,e.extend=e.fn.extend=function(){var a,c,d,f,g,h,i=arguments[0]||{},j=1,k=arguments.length,l=!1;
typeof i=="boolean"&&(l=i,i=arguments[1]||{},j=2),typeof i!="object"&&!e.isFunction(i)&&(i={}),k===j&&(i=this,--j);for(;j<k;
j++){if((a=arguments[j])!=null){for(c in a){d=i[c],f=a[c];if(i===f){continue}l&&f&&(e.isPlainObject(f)||(g=e.isArray(f)))?(g?(g=!1,h=d&&e.isArray(d)?d:[]):h=d&&e.isPlainObject(d)?d:{},i[c]=e.extend(l,h,f)):f!==b&&(i[c]=f)
}}}return i},e.extend({noConflict:function(b){a.$===e&&(a.$=g),b&&a.jQuery===e&&(a.jQuery=f);return e},isReady:!1,readyWait:1,holdReady:function(a){a?e.readyWait++:e.ready(!0)
},ready:function(a){if(a===!0&&!--e.readyWait||a!==!0&&!e.isReady){if(!c.body){return setTimeout(e.ready,1)}e.isReady=!0;
if(a!==!0&&--e.readyWait>0){return}y.resolveWith(c,[e]),e.fn.trigger&&e(c).trigger("ready").unbind("ready")}},bindReady:function(){if(!y){y=e._Deferred();
if(c.readyState==="complete"){return setTimeout(e.ready,1)}if(c.addEventListener){c.addEventListener("DOMContentLoaded",z,!1),a.addEventListener("load",e.ready,!1)
}else{if(c.attachEvent){c.attachEvent("onreadystatechange",z),a.attachEvent("onload",e.ready);var b=!1;try{b=a.frameElement==null
}catch(d){}c.documentElement.doScroll&&b&&H()}}}},isFunction:function(a){return e.type(a)==="function"},isArray:Array.isArray||function(a){return e.type(a)==="array"
},isWindow:function(a){return a&&typeof a=="object"&&"setInterval" in a},isNaN:function(a){return a==null||!m.test(a)||isNaN(a)
},type:function(a){return a==null?String(a):G[A.call(a)]||"object"},isPlainObject:function(a){if(!a||e.type(a)!=="object"||a.nodeType||e.isWindow(a)){return !1
}if(a.constructor&&!B.call(a,"constructor")&&!B.call(a.constructor.prototype,"isPrototypeOf")){return !1}var c;for(c in a){}return c===b||B.call(a,c)
},isEmptyObject:function(a){for(var b in a){return !1}return !0},error:function(a){throw a},parseJSON:function(b){if(typeof b!="string"||!b){return null
}b=e.trim(b);if(a.JSON&&a.JSON.parse){return a.JSON.parse(b)}if(o.test(b.replace(p,"@").replace(q,"]").replace(r,""))){return(new Function("return "+b))()
}e.error("Invalid JSON: "+b)},parseXML:function(b,c,d){a.DOMParser?(d=new DOMParser,c=d.parseFromString(b,"text/xml")):(c=new ActiveXObject("Microsoft.XMLDOM"),c.async="false",c.loadXML(b)),d=c.documentElement,(!d||!d.nodeName||d.nodeName==="parsererror")&&e.error("Invalid XML: "+b);
return c},noop:function(){},globalEval:function(b){b&&j.test(b)&&(a.execScript||function(b){a.eval.call(a,b)})(b)},nodeName:function(a,b){return a.nodeName&&a.nodeName.toUpperCase()===b.toUpperCase()
},each:function(a,c,d){var f,g=0,h=a.length,i=h===b||e.isFunction(a);if(d){if(i){for(f in a){if(c.apply(a[f],d)===!1){break
}}}else{for(;g<h;){if(c.apply(a[g++],d)===!1){break}}}}else{if(i){for(f in a){if(c.call(a[f],f,a[f])===!1){break}}}else{for(;
g<h;){if(c.call(a[g],g,a[g++])===!1){break}}}}return a},trim:E?function(a){return a==null?"":E.call(a)}:function(a){return a==null?"":(a+"").replace(k,"").replace(l,"")
},makeArray:function(a,b){var c=b||[];if(a!=null){var d=e.type(a);a.length==null||d==="string"||d==="function"||d==="regexp"||e.isWindow(a)?C.call(c,a):e.merge(c,a)
}return c},inArray:function(a,b){if(F){return F.call(b,a)}for(var c=0,d=b.length;c<d;c++){if(b[c]===a){return c}}return -1
},merge:function(a,c){var d=a.length,e=0;if(typeof c.length=="number"){for(var f=c.length;e<f;e++){a[d++]=c[e]}}else{while(c[e]!==b){a[d++]=c[e++]
}}a.length=d;return a},grep:function(a,b,c){var d=[],e;c=!!c;for(var f=0,g=a.length;f<g;f++){e=!!b(a[f],f),c!==e&&d.push(a[f])
}return d},map:function(a,c,d){var f,g,h=[],i=0,j=a.length,k=a instanceof e||j!==b&&typeof j=="number"&&(j>0&&a[0]&&a[j-1]||j===0||e.isArray(a));
if(k){for(;i<j;i++){f=c(a[i],i,d),f!=null&&(h[h.length]=f)}}else{for(g in a){f=c(a[g],g,d),f!=null&&(h[h.length]=f)}}return h.concat.apply([],h)
},guid:1,proxy:function(a,c){if(typeof c=="string"){var d=a[c];c=a,a=d}if(!e.isFunction(a)){return b}var f=D.call(arguments,2),g=function(){return a.apply(c,f.concat(D.call(arguments)))
};g.guid=a.guid=a.guid||g.guid||e.guid++;return g},access:function(a,c,d,f,g,h){var i=a.length;if(typeof c=="object"){for(var j in c){e.access(a,j,c[j],f,g,d)
}return a}if(d!==b){f=!h&&f&&e.isFunction(d);for(var k=0;k<i;k++){g(a[k],c,f?d.call(a[k],k,g(a[k],c)):d,h)}return a}return i?g(a[0],c):b
},now:function(){return(new Date).getTime()},uaMatch:function(a){a=a.toLowerCase();var b=s.exec(a)||t.exec(a)||u.exec(a)||a.indexOf("compatible")<0&&v.exec(a)||[];
return{browser:b[1]||"",version:b[2]||"0"}},sub:function(){function a(b,c){return new a.fn.init(b,c)}e.extend(!0,a,this),a.superclass=this,a.fn=a.prototype=this(),a.fn.constructor=a,a.sub=this.sub,a.fn.init=function(d,f){f&&f instanceof e&&!(f instanceof a)&&(f=a(f));
return e.fn.init.call(this,d,f,b)},a.fn.init.prototype=a.fn;var b=a(c);return a},browser:{}}),e.each("Boolean Number String Function Array Date RegExp Object".split(" "),function(a,b){G["[object "+b+"]"]=b.toLowerCase()
}),x=e.uaMatch(w),x.browser&&(e.browser[x.browser]=!0,e.browser.version=x.version),e.browser.webkit&&(e.browser.safari=!0),j.test(" ")&&(k=/^[\s\xA0]+/,l=/[\s\xA0]+$/),h=e(c),c.addEventListener?z=function(){c.removeEventListener("DOMContentLoaded",z,!1),e.ready()
}:c.attachEvent&&(z=function(){c.readyState==="complete"&&(c.detachEvent("onreadystatechange",z),e.ready())});return e}(),g="done fail isResolved isRejected promise then always pipe".split(" "),h=[].slice;
f.extend({_Deferred:function(){var a=[],b,c,d,e={done:function(){if(!d){var c=arguments,g,h,i,j,k;b&&(k=b,b=0);for(g=0,h=c.length;
g<h;g++){i=c[g],j=f.type(i),j==="array"?e.done.apply(e,i):j==="function"&&a.push(i)}k&&e.resolveWith(k[0],k[1])}return this
},resolveWith:function(e,f){if(!d&&!b&&!c){f=f||[],c=1;try{while(a[0]){a.shift().apply(e,f)}}finally{b=[e,f],c=0}}return this
},resolve:function(){e.resolveWith(this,arguments);return this},isResolved:function(){return !!c||!!b},cancel:function(){d=1,a=[];
return this}};return e},Deferred:function(a){var b=f._Deferred(),c=f._Deferred(),d;f.extend(b,{then:function(a,c){b.done(a).fail(c);
return this},always:function(){return b.done.apply(b,arguments).fail.apply(this,arguments)},fail:c.done,rejectWith:c.resolveWith,reject:c.resolve,isRejected:c.isResolved,pipe:function(a,c){return f.Deferred(function(d){f.each({done:[a,"resolve"],fail:[c,"reject"]},function(a,c){var e=c[0],g=c[1],h;
f.isFunction(e)?b[a](function(){h=e.apply(this,arguments),h&&f.isFunction(h.promise)?h.promise().then(d.resolve,d.reject):d[g](h)
}):b[a](d[g])})}).promise()},promise:function(a){if(a==null){if(d){return d}d=a={}}var c=g.length;while(c--){a[g[c]]=b[g[c]]
}return a}}),b.done(c.cancel).fail(b.cancel),delete b.cancel,a&&a.call(b,b);return b},when:function(a){function i(a){return function(c){b[a]=arguments.length>1?h.call(arguments,0):c,--e||g.resolveWith(g,h.call(b,0))
}}var b=arguments,c=0,d=b.length,e=d,g=d<=1&&a&&f.isFunction(a.promise)?a:f.Deferred();if(d>1){for(;c<d;c++){b[c]&&f.isFunction(b[c].promise)?b[c].promise().then(i(c),g.reject):--e
}e||g.resolveWith(g,b)}else{g!==a&&g.resolveWith(g,d?[a]:[])}return g.promise()}}),f.support=function(){var a=c.createElement("div"),b=c.documentElement,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r;
a.setAttribute("className","t"),a.innerHTML="   <link/><table></table><a href='/a' style='top:1px;float:left;opacity:.55;'>a</a><input type='checkbox'/>",d=a.getElementsByTagName("*"),e=a.getElementsByTagName("a")[0];
if(!d||!d.length||!e){return{}}f=c.createElement("select"),g=f.appendChild(c.createElement("option")),h=a.getElementsByTagName("input")[0],j={leadingWhitespace:a.firstChild.nodeType===3,tbody:!a.getElementsByTagName("tbody").length,htmlSerialize:!!a.getElementsByTagName("link").length,style:/top/.test(e.getAttribute("style")),hrefNormalized:e.getAttribute("href")==="/a",opacity:/^0.55$/.test(e.style.opacity),cssFloat:!!e.style.cssFloat,checkOn:h.value==="on",optSelected:g.selected,getSetAttribute:a.className!=="t",submitBubbles:!0,changeBubbles:!0,focusinBubbles:!1,deleteExpando:!0,noCloneEvent:!0,inlineBlockNeedsLayout:!1,shrinkWrapBlocks:!1,reliableMarginRight:!0},h.checked=!0,j.noCloneChecked=h.cloneNode(!0).checked,f.disabled=!0,j.optDisabled=!g.disabled;
try{delete a.test}catch(s){j.deleteExpando=!1}!a.addEventListener&&a.attachEvent&&a.fireEvent&&(a.attachEvent("onclick",function b(){j.noCloneEvent=!1,a.detachEvent("onclick",b)
}),a.cloneNode(!0).fireEvent("onclick")),h=c.createElement("input"),h.value="t",h.setAttribute("type","radio"),j.radioValue=h.value==="t",h.setAttribute("checked","checked"),a.appendChild(h),k=c.createDocumentFragment(),k.appendChild(a.firstChild),j.checkClone=k.cloneNode(!0).cloneNode(!0).lastChild.checked,a.innerHTML="",a.style.width=a.style.paddingLeft="1px",l=c.createElement("body"),m={visibility:"hidden",width:0,height:0,border:0,margin:0,background:"none"};
for(q in m){l.style[q]=m[q]}l.appendChild(a),b.insertBefore(l,b.firstChild),j.appendChecked=h.checked,j.boxModel=a.offsetWidth===2,"zoom" in a.style&&(a.style.display="inline",a.style.zoom=1,j.inlineBlockNeedsLayout=a.offsetWidth===2,a.style.display="",a.innerHTML="<div style='width:4px;'></div>",j.shrinkWrapBlocks=a.offsetWidth!==2),a.innerHTML="<table><tr><td style='padding:0;border:0;display:none'></td><td>t</td></tr></table>",n=a.getElementsByTagName("td"),r=n[0].offsetHeight===0,n[0].style.display="",n[1].style.display="none",j.reliableHiddenOffsets=r&&n[0].offsetHeight===0,a.innerHTML="",c.defaultView&&c.defaultView.getComputedStyle&&(i=c.createElement("div"),i.style.width="0",i.style.marginRight="0",a.appendChild(i),j.reliableMarginRight=(parseInt((c.defaultView.getComputedStyle(i,null)||{marginRight:0}).marginRight,10)||0)===0),l.innerHTML="",b.removeChild(l);
if(a.attachEvent){for(q in {submit:1,change:1,focusin:1}){p="on"+q,r=p in a,r||(a.setAttribute(p,"return;"),r=typeof a[p]=="function"),j[q+"Bubbles"]=r
}}return j}(),f.boxModel=f.support.boxModel;var i=/^(?:\{.*\}|\[.*\])$/,j=/([a-z])([A-Z])/g;f.extend({cache:{},uuid:0,expando:"jQuery"+(f.fn.jquery+Math.random()).replace(/\D/g,""),noData:{embed:!0,object:"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",applet:!0},hasData:function(a){a=a.nodeType?f.cache[a[f.expando]]:a[f.expando];
return !!a&&!l(a)},data:function(a,c,d,e){if(!!f.acceptData(a)){var g=f.expando,h=typeof c=="string",i,j=a.nodeType,k=j?f.cache:a,l=j?a[f.expando]:a[f.expando]&&f.expando;
if((!l||e&&l&&!k[l][g])&&h&&d===b){return}l||(j?a[f.expando]=l=++f.uuid:l=f.expando),k[l]||(k[l]={},j||(k[l].toJSON=f.noop));
if(typeof c=="object"||typeof c=="function"){e?k[l][g]=f.extend(k[l][g],c):k[l]=f.extend(k[l],c)}i=k[l],e&&(i[g]||(i[g]={}),i=i[g]),d!==b&&(i[f.camelCase(c)]=d);
if(c==="events"&&!i[c]){return i[g]&&i[g].events}return h?i[f.camelCase(c)]:i}},removeData:function(b,c,d){if(!!f.acceptData(b)){var e=f.expando,g=b.nodeType,h=g?f.cache:b,i=g?b[f.expando]:f.expando;
if(!h[i]){return}if(c){var j=d?h[i][e]:h[i];if(j){delete j[c];if(!l(j)){return}}}if(d){delete h[i][e];if(!l(h[i])){return
}}var k=h[i][e];f.support.deleteExpando||h!=a?delete h[i]:h[i]=null,k?(h[i]={},g||(h[i].toJSON=f.noop),h[i][e]=k):g&&(f.support.deleteExpando?delete b[f.expando]:b.removeAttribute?b.removeAttribute(f.expando):b[f.expando]=null)
}},_data:function(a,b,c){return f.data(a,b,c,!0)},acceptData:function(a){if(a.nodeName){var b=f.noData[a.nodeName.toLowerCase()];
if(b){return b!==!0&&a.getAttribute("classid")===b}}return !0}}),f.fn.extend({data:function(a,c){var d=null;if(typeof a=="undefined"){if(this.length){d=f.data(this[0]);
if(this[0].nodeType===1){var e=this[0].attributes,g;for(var h=0,i=e.length;h<i;h++){g=e[h].name,g.indexOf("data-")===0&&(g=f.camelCase(g.substring(5)),k(this[0],g,d[g]))
}}}return d}if(typeof a=="object"){return this.each(function(){f.data(this,a)})}var j=a.split(".");j[1]=j[1]?"."+j[1]:"";
if(c===b){d=this.triggerHandler("getData"+j[1]+"!",[j[0]]),d===b&&this.length&&(d=f.data(this[0],a),d=k(this[0],a,d));return d===b&&j[1]?this.data(j[0]):d
}return this.each(function(){var b=f(this),d=[j[0],c];b.triggerHandler("setData"+j[1]+"!",d),f.data(this,a,c),b.triggerHandler("changeData"+j[1]+"!",d)
})},removeData:function(a){return this.each(function(){f.removeData(this,a)})}}),f.extend({_mark:function(a,c){a&&(c=(c||"fx")+"mark",f.data(a,c,(f.data(a,c,b,!0)||0)+1,!0))
},_unmark:function(a,c,d){a!==!0&&(d=c,c=a,a=!1);if(c){d=d||"fx";var e=d+"mark",g=a?0:(f.data(c,e,b,!0)||1)-1;g?f.data(c,e,g,!0):(f.removeData(c,e,!0),m(c,d,"mark"))
}},queue:function(a,c,d){if(a){c=(c||"fx")+"queue";var e=f.data(a,c,b,!0);d&&(!e||f.isArray(d)?e=f.data(a,c,f.makeArray(d),!0):e.push(d));
return e||[]}},dequeue:function(a,b){b=b||"fx";var c=f.queue(a,b),d=c.shift(),e;d==="inprogress"&&(d=c.shift()),d&&(b==="fx"&&c.unshift("inprogress"),d.call(a,function(){f.dequeue(a,b)
})),c.length||(f.removeData(a,b+"queue",!0),m(a,b,"queue"))}}),f.fn.extend({queue:function(a,c){typeof a!="string"&&(c=a,a="fx");
if(c===b){return f.queue(this[0],a)}return this.each(function(){var b=f.queue(this,a,c);a==="fx"&&b[0]!=="inprogress"&&f.dequeue(this,a)
})},dequeue:function(a){return this.each(function(){f.dequeue(this,a)})},delay:function(a,b){a=f.fx?f.fx.speeds[a]||a:a,b=b||"fx";
return this.queue(b,function(){var c=this;setTimeout(function(){f.dequeue(c,b)},a)})},clearQueue:function(a){return this.queue(a||"fx",[])
},promise:function(a,c){function m(){--h||d.resolveWith(e,[e])}typeof a!="string"&&(c=a,a=b),a=a||"fx";var d=f.Deferred(),e=this,g=e.length,h=1,i=a+"defer",j=a+"queue",k=a+"mark",l;
while(g--){if(l=f.data(e[g],i,b,!0)||(f.data(e[g],j,b,!0)||f.data(e[g],k,b,!0))&&f.data(e[g],i,f._Deferred(),!0)){h++,l.done(m)
}}m();return d.promise()}});var n=/[\n\t\r]/g,o=/\s+/,p=/\r/g,q=/^(?:button|input)$/i,r=/^(?:button|input|object|select|textarea)$/i,s=/^a(?:rea)?$/i,t=/^(?:autofocus|autoplay|async|checked|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped|selected)$/i,u=/\:/,v,w;
f.fn.extend({attr:function(a,b){return f.access(this,a,b,!0,f.attr)},removeAttr:function(a){return this.each(function(){f.removeAttr(this,a)
})},prop:function(a,b){return f.access(this,a,b,!0,f.prop)},removeProp:function(a){a=f.propFix[a]||a;return this.each(function(){try{this[a]=b,delete this[a]
}catch(c){}})},addClass:function(a){if(f.isFunction(a)){return this.each(function(b){var c=f(this);c.addClass(a.call(this,b,c.attr("class")||""))
})}if(a&&typeof a=="string"){var b=(a||"").split(o);for(var c=0,d=this.length;c<d;c++){var e=this[c];if(e.nodeType===1){if(!e.className){e.className=a
}else{var g=" "+e.className+" ",h=e.className;for(var i=0,j=b.length;i<j;i++){g.indexOf(" "+b[i]+" ")<0&&(h+=" "+b[i])}e.className=f.trim(h)
}}}}return this},removeClass:function(a){if(f.isFunction(a)){return this.each(function(b){var c=f(this);c.removeClass(a.call(this,b,c.attr("class")))
})}if(a&&typeof a=="string"||a===b){var c=(a||"").split(o);for(var d=0,e=this.length;d<e;d++){var g=this[d];if(g.nodeType===1&&g.className){if(a){var h=(" "+g.className+" ").replace(n," ");
for(var i=0,j=c.length;i<j;i++){h=h.replace(" "+c[i]+" "," ")}g.className=f.trim(h)}else{g.className=""}}}}return this},toggleClass:function(a,b){var c=typeof a,d=typeof b=="boolean";
if(f.isFunction(a)){return this.each(function(c){var d=f(this);d.toggleClass(a.call(this,c,d.attr("class"),b),b)})}return this.each(function(){if(c==="string"){var e,g=0,h=f(this),i=b,j=a.split(o);
while(e=j[g++]){i=d?i:!h.hasClass(e),h[i?"addClass":"removeClass"](e)}}else{if(c==="undefined"||c==="boolean"){this.className&&f._data(this,"__className__",this.className),this.className=this.className||a===!1?"":f._data(this,"__className__")||""
}}})},hasClass:function(a){var b=" "+a+" ";for(var c=0,d=this.length;c<d;c++){if((" "+this[c].className+" ").replace(n," ").indexOf(b)>-1){return !0
}}return !1},val:function(a){var c,d,e=this[0];if(!arguments.length){if(e){c=f.valHooks[e.nodeName.toLowerCase()]||f.valHooks[e.type];
if(c&&"get" in c&&(d=c.get(e,"value"))!==b){return d}return(e.value||"").replace(p,"")}return b}var g=f.isFunction(a);return this.each(function(d){var e=f(this),h;
if(this.nodeType===1){g?h=a.call(this,d,e.val()):h=a,h==null?h="":typeof h=="number"?h+="":f.isArray(h)&&(h=f.map(h,function(a){return a==null?"":a+""
})),c=f.valHooks[this.nodeName.toLowerCase()]||f.valHooks[this.type];if(!c||!("set" in c)||c.set(this,h,"value")===b){this.value=h
}}})}}),f.extend({valHooks:{option:{get:function(a){var b=a.attributes.value;return !b||b.specified?a.value:a.text}},select:{get:function(a){var b,c=a.selectedIndex,d=[],e=a.options,g=a.type==="select-one";
if(c<0){return null}for(var h=g?c:0,i=g?c+1:e.length;h<i;h++){var j=e[h];if(j.selected&&(f.support.optDisabled?!j.disabled:j.getAttribute("disabled")===null)&&(!j.parentNode.disabled||!f.nodeName(j.parentNode,"optgroup"))){b=f(j).val();
if(g){return b}d.push(b)}}if(g&&!d.length&&e.length){return f(e[c]).val()}return d},set:function(a,b){var c=f.makeArray(b);
f(a).find("option").each(function(){this.selected=f.inArray(f(this).val(),c)>=0}),c.length||(a.selectedIndex=-1);return c
}}},attrFn:{val:!0,css:!0,html:!0,text:!0,data:!0,width:!0,height:!0,offset:!0},attrFix:{tabindex:"tabIndex"},attr:function(a,c,d,e){var g=a.nodeType;
if(!a||g===3||g===8||g===2){return b}if(e&&c in f.attrFn){return f(a)[c](d)}if(!("getAttribute" in a)){return f.prop(a,c,d)
}var h,i,j=g!==1||!f.isXMLDoc(a);c=j&&f.attrFix[c]||c,i=f.attrHooks[c],i||(!t.test(c)||typeof d!="boolean"&&d!==b&&d.toLowerCase()!==c.toLowerCase()?v&&(f.nodeName(a,"form")||u.test(c))&&(i=v):i=w);
if(d!==b){if(d===null){f.removeAttr(a,c);return b}if(i&&"set" in i&&j&&(h=i.set(a,d,c))!==b){return h}a.setAttribute(c,""+d);
return d}if(i&&"get" in i&&j){return i.get(a,c)}h=a.getAttribute(c);return h===null?b:h},removeAttr:function(a,b){var c;a.nodeType===1&&(b=f.attrFix[b]||b,f.support.getSetAttribute?a.removeAttribute(b):(f.attr(a,b,""),a.removeAttributeNode(a.getAttributeNode(b))),t.test(b)&&(c=f.propFix[b]||b) in a&&(a[c]=!1))
},attrHooks:{type:{set:function(a,b){if(q.test(a.nodeName)&&a.parentNode){f.error("type property can't be changed")}else{if(!f.support.radioValue&&b==="radio"&&f.nodeName(a,"input")){var c=a.value;
a.setAttribute("type",b),c&&(a.value=c);return b}}}},tabIndex:{get:function(a){var c=a.getAttributeNode("tabIndex");return c&&c.specified?parseInt(c.value,10):r.test(a.nodeName)||s.test(a.nodeName)&&a.href?0:b
}}},propFix:{tabindex:"tabIndex",readonly:"readOnly","for":"htmlFor","class":"className",maxlength:"maxLength",cellspacing:"cellSpacing",cellpadding:"cellPadding",rowspan:"rowSpan",colspan:"colSpan",usemap:"useMap",frameborder:"frameBorder",contenteditable:"contentEditable"},prop:function(a,c,d){var e=a.nodeType;
if(!a||e===3||e===8||e===2){return b}var g,h,i=e!==1||!f.isXMLDoc(a);c=i&&f.propFix[c]||c,h=f.propHooks[c];return d!==b?h&&"set" in h&&(g=h.set(a,d,c))!==b?g:a[c]=d:h&&"get" in h&&(g=h.get(a,c))!==b?g:a[c]
},propHooks:{}}),w={get:function(a,c){return a[f.propFix[c]||c]?c.toLowerCase():b},set:function(a,b,c){var d;b===!1?f.removeAttr(a,c):(d=f.propFix[c]||c,d in a&&(a[d]=b),a.setAttribute(c,c.toLowerCase()));
return c}},f.attrHooks.value={get:function(a,b){if(v&&f.nodeName(a,"button")){return v.get(a,b)}return a.value},set:function(a,b,c){if(v&&f.nodeName(a,"button")){return v.set(a,b,c)
}a.value=b}},f.support.getSetAttribute||(f.attrFix=f.propFix,v=f.attrHooks.name=f.valHooks.button={get:function(a,c){var d;
d=a.getAttributeNode(c);return d&&d.nodeValue!==""?d.nodeValue:b},set:function(a,b,c){var d=a.getAttributeNode(c);if(d){d.nodeValue=b;
return b}}},f.each(["width","height"],function(a,b){f.attrHooks[b]=f.extend(f.attrHooks[b],{set:function(a,c){if(c===""){a.setAttribute(b,"auto");
return c}}})})),f.support.hrefNormalized||f.each(["href","src","width","height"],function(a,c){f.attrHooks[c]=f.extend(f.attrHooks[c],{get:function(a){var d=a.getAttribute(c,2);
return d===null?b:d}})}),f.support.style||(f.attrHooks.style={get:function(a){return a.style.cssText.toLowerCase()||b},set:function(a,b){return a.style.cssText=""+b
}}),f.support.optSelected||(f.propHooks.selected=f.extend(f.propHooks.selected,{get:function(a){var b=a.parentNode;b&&(b.selectedIndex,b.parentNode&&b.parentNode.selectedIndex)
}})),f.support.checkOn||f.each(["radio","checkbox"],function(){f.valHooks[this]={get:function(a){return a.getAttribute("value")===null?"on":a.value
}}}),f.each(["radio","checkbox"],function(){f.valHooks[this]=f.extend(f.valHooks[this],{set:function(a,b){if(f.isArray(b)){return a.checked=f.inArray(f(a).val(),b)>=0
}}})});var x=Object.prototype.hasOwnProperty,y=/\.(.*)$/,z=/^(?:textarea|input|select)$/i,A=/\./g,B=/ /g,C=/[^\w\s.|`]/g,D=function(a){return a.replace(C,"\\$&")
};f.event={add:function(a,c,d,e){if(a.nodeType!==3&&a.nodeType!==8){if(d===!1){d=E}else{if(!d){return}}var g,h;d.handler&&(g=d,d=g.handler),d.guid||(d.guid=f.guid++);
var i=f._data(a);if(!i){return}var j=i.events,k=i.handle;j||(i.events=j={}),k||(i.handle=k=function(a){return typeof f!="undefined"&&(!a||f.event.triggered!==a.type)?f.event.handle.apply(k.elem,arguments):b
}),k.elem=a,c=c.split(" ");var l,m=0,n;while(l=c[m++]){h=g?f.extend({},g):{handler:d,data:e},l.indexOf(".")>-1?(n=l.split("."),l=n.shift(),h.namespace=n.slice(0).sort().join(".")):(n=[],h.namespace=""),h.type=l,h.guid||(h.guid=d.guid);
var o=j[l],p=f.event.special[l]||{};if(!o){o=j[l]=[];if(!p.setup||p.setup.call(a,e,n,k)===!1){a.addEventListener?a.addEventListener(l,k,!1):a.attachEvent&&a.attachEvent("on"+l,k)
}}p.add&&(p.add.call(a,h),h.handler.guid||(h.handler.guid=d.guid)),o.push(h),f.event.global[l]=!0}a=null}},global:{},remove:function(a,c,d,e){if(a.nodeType!==3&&a.nodeType!==8){d===!1&&(d=E);
var g,h,i,j,k=0,l,m,n,o,p,q,r,s=f.hasData(a)&&f._data(a),t=s&&s.events;if(!s||!t){return}c&&c.type&&(d=c.handler,c=c.type);
if(!c||typeof c=="string"&&c.charAt(0)==="."){c=c||"";for(h in t){f.event.remove(a,h+c)}return}c=c.split(" ");while(h=c[k++]){r=h,q=null,l=h.indexOf(".")<0,m=[],l||(m=h.split("."),h=m.shift(),n=new RegExp("(^|\\.)"+f.map(m.slice(0).sort(),D).join("\\.(?:.*\\.)?")+"(\\.|$)")),p=t[h];
if(!p){continue}if(!d){for(j=0;j<p.length;j++){q=p[j];if(l||n.test(q.namespace)){f.event.remove(a,r,q.handler,j),p.splice(j--,1)
}}continue}o=f.event.special[h]||{};for(j=e||0;j<p.length;j++){q=p[j];if(d.guid===q.guid){if(l||n.test(q.namespace)){e==null&&p.splice(j--,1),o.remove&&o.remove.call(a,q)
}if(e!=null){break}}}if(p.length===0||e!=null&&p.length===1){(!o.teardown||o.teardown.call(a,m)===!1)&&f.removeEvent(a,h,s.handle),g=null,delete t[h]
}}if(f.isEmptyObject(t)){var u=s.handle;u&&(u.elem=null),delete s.events,delete s.handle,f.isEmptyObject(s)&&f.removeData(a,b,!0)
}}},customEvent:{getData:!0,setData:!0,changeData:!0},trigger:function(c,d,e,g){var h=c.type||c,i=[],j;h.indexOf("!")>=0&&(h=h.slice(0,-1),j=!0),h.indexOf(".")>=0&&(i=h.split("."),h=i.shift(),i.sort());
if(!!e&&!f.event.customEvent[h]||!!f.event.global[h]){c=typeof c=="object"?c[f.expando]?c:new f.Event(h,c):new f.Event(h),c.type=h,c.exclusive=j,c.namespace=i.join("."),c.namespace_re=new RegExp("(^|\\.)"+i.join("\\.(?:.*\\.)?")+"(\\.|$)");
if(g||!e){c.preventDefault(),c.stopPropagation()}if(!e){f.each(f.cache,function(){var a=f.expando,b=this[a];b&&b.events&&b.events[h]&&f.event.trigger(c,d,b.handle.elem)
});return}if(e.nodeType===3||e.nodeType===8){return}c.result=b,c.target=e,d=d?f.makeArray(d):[],d.unshift(c);var k=e,l=h.indexOf(":")<0?"on"+h:"";
do{var m=f._data(k,"handle");c.currentTarget=k,m&&m.apply(k,d),l&&f.acceptData(k)&&k[l]&&k[l].apply(k,d)===!1&&(c.result=!1,c.preventDefault()),k=k.parentNode||k.ownerDocument||k===c.target.ownerDocument&&a
}while(k&&!c.isPropagationStopped());if(!c.isDefaultPrevented()){var n,o=f.event.special[h]||{};if((!o._default||o._default.call(e.ownerDocument,c)===!1)&&(h!=="click"||!f.nodeName(e,"a"))&&f.acceptData(e)){try{l&&e[h]&&(n=e[l],n&&(e[l]=null),f.event.triggered=h,e[h]())
}catch(p){}n&&(e[l]=n),f.event.triggered=b}}return c.result}},handle:function(c){c=f.event.fix(c||a.event);var d=((f._data(this,"events")||{})[c.type]||[]).slice(0),e=!c.exclusive&&!c.namespace,g=Array.prototype.slice.call(arguments,0);
g[0]=c,c.currentTarget=this;for(var h=0,i=d.length;h<i;h++){var j=d[h];if(e||c.namespace_re.test(j.namespace)){c.handler=j.handler,c.data=j.data,c.handleObj=j;
var k=j.handler.apply(this,g);k!==b&&(c.result=k,k===!1&&(c.preventDefault(),c.stopPropagation()));if(c.isImmediatePropagationStopped()){break
}}}return c.result},props:"altKey attrChange attrName bubbles button cancelable charCode clientX clientY ctrlKey currentTarget data detail eventPhase fromElement handler keyCode layerX layerY metaKey newValue offsetX offsetY pageX pageY prevValue relatedNode relatedTarget screenX screenY shiftKey srcElement target toElement view wheelDelta which".split(" "),fix:function(a){if(a[f.expando]){return a
}var d=a;a=f.Event(d);for(var e=this.props.length,g;e;){g=this.props[--e],a[g]=d[g]}a.target||(a.target=a.srcElement||c),a.target.nodeType===3&&(a.target=a.target.parentNode),!a.relatedTarget&&a.fromElement&&(a.relatedTarget=a.fromElement===a.target?a.toElement:a.fromElement);
if(a.pageX==null&&a.clientX!=null){var h=a.target.ownerDocument||c,i=h.documentElement,j=h.body;a.pageX=a.clientX+(i&&i.scrollLeft||j&&j.scrollLeft||0)-(i&&i.clientLeft||j&&j.clientLeft||0),a.pageY=a.clientY+(i&&i.scrollTop||j&&j.scrollTop||0)-(i&&i.clientTop||j&&j.clientTop||0)
}a.which==null&&(a.charCode!=null||a.keyCode!=null)&&(a.which=a.charCode!=null?a.charCode:a.keyCode),!a.metaKey&&a.ctrlKey&&(a.metaKey=a.ctrlKey),!a.which&&a.button!==b&&(a.which=a.button&1?1:a.button&2?3:a.button&4?2:0);
return a},guid:100000000,proxy:f.proxy,special:{ready:{setup:f.bindReady,teardown:f.noop},live:{add:function(a){f.event.add(this,O(a.origType,a.selector),f.extend({},a,{handler:N,guid:a.handler.guid}))
},remove:function(a){f.event.remove(this,O(a.origType,a.selector),a)}},beforeunload:{setup:function(a,b,c){f.isWindow(this)&&(this.onbeforeunload=c)
},teardown:function(a,b){this.onbeforeunload===b&&(this.onbeforeunload=null)}}}},f.removeEvent=c.removeEventListener?function(a,b,c){a.removeEventListener&&a.removeEventListener(b,c,!1)
}:function(a,b,c){a.detachEvent&&a.detachEvent("on"+b,c)},f.Event=function(a,b){if(!this.preventDefault){return new f.Event(a,b)
}a&&a.type?(this.originalEvent=a,this.type=a.type,this.isDefaultPrevented=a.defaultPrevented||a.returnValue===!1||a.getPreventDefault&&a.getPreventDefault()?F:E):this.type=a,b&&f.extend(this,b),this.timeStamp=f.now(),this[f.expando]=!0
},f.Event.prototype={preventDefault:function(){this.isDefaultPrevented=F;var a=this.originalEvent;!a||(a.preventDefault?a.preventDefault():a.returnValue=!1)
},stopPropagation:function(){this.isPropagationStopped=F;var a=this.originalEvent;!a||(a.stopPropagation&&a.stopPropagation(),a.cancelBubble=!0)
},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=F,this.stopPropagation()},isDefaultPrevented:E,isPropagationStopped:E,isImmediatePropagationStopped:E};
var G=function(a){var b=a.relatedTarget;a.type=a.data;try{if(b&&b!==c&&!b.parentNode){return}while(b&&b!==this){b=b.parentNode
}b!==this&&f.event.handle.apply(this,arguments)}catch(d){}},H=function(a){a.type=a.data,f.event.handle.apply(this,arguments)
};f.each({mouseenter:"mouseover",mouseleave:"mouseout"},function(a,b){f.event.special[a]={setup:function(c){f.event.add(this,b,c&&c.selector?H:G,a)
},teardown:function(a){f.event.remove(this,b,a&&a.selector?H:G)}}}),f.support.submitBubbles||(f.event.special.submit={setup:function(a,b){if(!f.nodeName(this,"form")){f.event.add(this,"click.specialSubmit",function(a){var b=a.target,c=b.type;
(c==="submit"||c==="image")&&f(b).closest("form").length&&L("submit",this,arguments)}),f.event.add(this,"keypress.specialSubmit",function(a){var b=a.target,c=b.type;
(c==="text"||c==="password")&&f(b).closest("form").length&&a.keyCode===13&&L("submit",this,arguments)})}else{return !1}},teardown:function(a){f.event.remove(this,".specialSubmit")
}});if(!f.support.changeBubbles){var I,J=function(a){var b=a.type,c=a.value;b==="radio"||b==="checkbox"?c=a.checked:b==="select-multiple"?c=a.selectedIndex>-1?f.map(a.options,function(a){return a.selected
}).join("-"):"":f.nodeName(a,"select")&&(c=a.selectedIndex);return c},K=function(c){var d=c.target,e,g;if(!!z.test(d.nodeName)&&!d.readOnly){e=f._data(d,"_change_data"),g=J(d),(c.type!=="focusout"||d.type!=="radio")&&f._data(d,"_change_data",g);
if(e===b||g===e){return}if(e!=null||g){c.type="change",c.liveFired=b,f.event.trigger(c,arguments[1],d)}}};f.event.special.change={filters:{focusout:K,beforedeactivate:K,click:function(a){var b=a.target,c=f.nodeName(b,"input")?b.type:"";
(c==="radio"||c==="checkbox"||f.nodeName(b,"select"))&&K.call(this,a)},keydown:function(a){var b=a.target,c=f.nodeName(b,"input")?b.type:"";
(a.keyCode===13&&!f.nodeName(b,"textarea")||a.keyCode===32&&(c==="checkbox"||c==="radio")||c==="select-multiple")&&K.call(this,a)
},beforeactivate:function(a){var b=a.target;f._data(b,"_change_data",J(b))}},setup:function(a,b){if(this.type==="file"){return !1
}for(var c in I){f.event.add(this,c+".specialChange",I[c])}return z.test(this.nodeName)},teardown:function(a){f.event.remove(this,".specialChange");
return z.test(this.nodeName)}},I=f.event.special.change.filters,I.focus=I.beforeactivate}f.support.focusinBubbles||f.each({focus:"focusin",blur:"focusout"},function(a,b){function e(a){var c=f.event.fix(a);
c.type=b,c.originalEvent={},f.event.trigger(c,null,c.target),c.isDefaultPrevented()&&a.preventDefault()}var d=0;f.event.special[b]={setup:function(){d++===0&&c.addEventListener(a,e,!0)
},teardown:function(){--d===0&&c.removeEventListener(a,e,!0)}}}),f.each(["bind","one"],function(a,c){f.fn[c]=function(a,d,e){var g;
if(typeof a=="object"){for(var h in a){this[c](h,d,a[h],e)}return this}if(arguments.length===2||d===!1){e=d,d=b}c==="one"?(g=function(a){f(this).unbind(a,g);
return e.apply(this,arguments)},g.guid=e.guid||f.guid++):g=e;if(a==="unload"&&c!=="one"){this.one(a,d,e)}else{for(var i=0,j=this.length;
i<j;i++){f.event.add(this[i],a,g,d)}}return this}}),f.fn.extend({unbind:function(a,b){if(typeof a=="object"&&!a.preventDefault){for(var c in a){this.unbind(c,a[c])
}}else{for(var d=0,e=this.length;d<e;d++){f.event.remove(this[d],a,b)}}return this},delegate:function(a,b,c,d){return this.live(b,c,d,a)
},undelegate:function(a,b,c){return arguments.length===0?this.unbind("live"):this.die(b,null,c,a)},trigger:function(a,b){return this.each(function(){f.event.trigger(a,b,this)
})},triggerHandler:function(a,b){if(this[0]){return f.event.trigger(a,b,this[0],!0)}},toggle:function(a){var b=arguments,c=a.guid||f.guid++,d=0,e=function(c){var e=(f.data(this,"lastToggle"+a.guid)||0)%d;
f.data(this,"lastToggle"+a.guid,e+1),c.preventDefault();return b[e].apply(this,arguments)||!1};e.guid=c;while(d<b.length){b[d++].guid=c
}return this.click(e)},hover:function(a,b){return this.mouseenter(a).mouseleave(b||a)}});var M={focus:"focusin",blur:"focusout",mouseenter:"mouseover",mouseleave:"mouseout"};
f.each(["live","die"],function(a,c){f.fn[c]=function(a,d,e,g){var h,i=0,j,k,l,m=g||this.selector,n=g?this:f(this.context);
if(typeof a=="object"&&!a.preventDefault){for(var o in a){n[c](o,d,a[o],m)}return this}if(c==="die"&&!a&&g&&g.charAt(0)==="."){n.unbind(g);
return this}if(d===!1||f.isFunction(d)){e=d||E,d=b}a=(a||"").split(" ");while((h=a[i++])!=null){j=y.exec(h),k="",j&&(k=j[0],h=h.replace(y,""));
if(h==="hover"){a.push("mouseenter"+k,"mouseleave"+k);continue}l=h,M[h]?(a.push(M[h]+k),h=h+k):h=(M[h]||h)+k;if(c==="live"){for(var p=0,q=n.length;
p<q;p++){f.event.add(n[p],"live."+O(h,m),{data:d,selector:m,handler:e,origType:h,origHandler:e,preType:l})}}else{n.unbind("live."+O(h,m),e)
}}return this}}),f.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error".split(" "),function(a,b){f.fn[b]=function(a,c){c==null&&(c=a,a=null);
return arguments.length>0?this.bind(b,a,c):this.trigger(b)},f.attrFn&&(f.attrFn[b]=!0)}),function(){function u(a,b,c,d,e,f){for(var g=0,h=d.length;
g<h;g++){var i=d[g];if(i){var j=!1;i=i[a];while(i){if(i.sizcache===c){j=d[i.sizset];break}if(i.nodeType===1){f||(i.sizcache=c,i.sizset=g);
if(typeof b!="string"){if(i===b){j=!0;break}}else{if(k.filter(b,[i]).length>0){j=i;break}}}i=i[a]}d[g]=j}}}function t(a,b,c,d,e,f){for(var g=0,h=d.length;
g<h;g++){var i=d[g];if(i){var j=!1;i=i[a];while(i){if(i.sizcache===c){j=d[i.sizset];break}i.nodeType===1&&!f&&(i.sizcache=c,i.sizset=g);
if(i.nodeName.toLowerCase()===b){j=i;break}i=i[a]}d[g]=j}}}var a=/((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^\[\]]*\]|['"][^'"]*['"]|[^\[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?((?:.|\r|\n)*)/g,d=0,e=Object.prototype.toString,g=!1,h=!0,i=/\\/g,j=/\W/;
[0,0].sort(function(){h=!1;return 0});var k=function(b,d,f,g){f=f||[],d=d||c;var h=d;if(d.nodeType!==1&&d.nodeType!==9){return[]
}if(!b||typeof b!="string"){return f}var i,j,n,o,q,r,s,t,u=!0,w=k.isXML(d),x=[],y=b;do{a.exec(""),i=a.exec(y);if(i){y=i[3],x.push(i[1]);
if(i[2]){o=i[3];break}}}while(i);if(x.length>1&&m.exec(b)){if(x.length===2&&l.relative[x[0]]){j=v(x[0]+x[1],d)}else{j=l.relative[x[0]]?[d]:k(x.shift(),d);
while(x.length){b=x.shift(),l.relative[b]&&(b+=x.shift()),j=v(b,j)}}}else{!g&&x.length>1&&d.nodeType===9&&!w&&l.match.ID.test(x[0])&&!l.match.ID.test(x[x.length-1])&&(q=k.find(x.shift(),d,w),d=q.expr?k.filter(q.expr,q.set)[0]:q.set[0]);
if(d){q=g?{expr:x.pop(),set:p(g)}:k.find(x.pop(),x.length===1&&(x[0]==="~"||x[0]==="+")&&d.parentNode?d.parentNode:d,w),j=q.expr?k.filter(q.expr,q.set):q.set,x.length>0?n=p(j):u=!1;
while(x.length){r=x.pop(),s=r,l.relative[r]?s=x.pop():r="",s==null&&(s=d),l.relative[r](n,s,w)}}else{n=x=[]}}n||(n=j),n||k.error(r||b);
if(e.call(n)==="[object Array]"){if(!u){f.push.apply(f,n)}else{if(d&&d.nodeType===1){for(t=0;n[t]!=null;t++){n[t]&&(n[t]===!0||n[t].nodeType===1&&k.contains(d,n[t]))&&f.push(j[t])
}}else{for(t=0;n[t]!=null;t++){n[t]&&n[t].nodeType===1&&f.push(j[t])}}}}else{p(n,f)}o&&(k(o,h,f,g),k.uniqueSort(f));return f
};k.uniqueSort=function(a){if(r){g=h,a.sort(r);if(g){for(var b=1;b<a.length;b++){a[b]===a[b-1]&&a.splice(b--,1)}}}return a
},k.matches=function(a,b){return k(a,null,null,b)},k.matchesSelector=function(a,b){return k(b,null,null,[a]).length>0},k.find=function(a,b,c){var d;
if(!a){return[]}for(var e=0,f=l.order.length;e<f;e++){var g,h=l.order[e];if(g=l.leftMatch[h].exec(a)){var j=g[1];g.splice(1,1);
if(j.substr(j.length-1)!=="\\"){g[1]=(g[1]||"").replace(i,""),d=l.find[h](g,b,c);if(d!=null){a=a.replace(l.match[h],"");break
}}}}d||(d=typeof b.getElementsByTagName!="undefined"?b.getElementsByTagName("*"):[]);return{set:d,expr:a}},k.filter=function(a,c,d,e){var f,g,h=a,i=[],j=c,m=c&&c[0]&&k.isXML(c[0]);
while(a&&c.length){for(var n in l.filter){if((f=l.leftMatch[n].exec(a))!=null&&f[2]){var o,p,q=l.filter[n],r=f[1];g=!1,f.splice(1,1);
if(r.substr(r.length-1)==="\\"){continue}j===i&&(i=[]);if(l.preFilter[n]){f=l.preFilter[n](f,j,d,i,e,m);if(!f){g=o=!0}else{if(f===!0){continue
}}}if(f){for(var s=0;(p=j[s])!=null;s++){if(p){o=q(p,f,s,j);var t=e^!!o;d&&o!=null?t?g=!0:j[s]=!1:t&&(i.push(p),g=!0)}}}if(o!==b){d||(j=i),a=a.replace(l.match[n],"");
if(!g){return[]}break}}}if(a===h){if(g==null){k.error(a)}else{break}}h=a}return j},k.error=function(a){throw"Syntax error, unrecognized expression: "+a
};var l=k.selectors={order:["ID","NAME","TAG"],match:{ID:/#((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,CLASS:/\.((?:[\w\u00c0-\uFFFF\-]|\\.)+)/,NAME:/\[name=['"]*((?:[\w\u00c0-\uFFFF\-]|\\.)+)['"]*\]/,ATTR:/\[\s*((?:[\w\u00c0-\uFFFF\-]|\\.)+)\s*(?:(\S?=)\s*(?:(['"])(.*?)\3|(#?(?:[\w\u00c0-\uFFFF\-]|\\.)*)|)|)\s*\]/,TAG:/^((?:[\w\u00c0-\uFFFF\*\-]|\\.)+)/,CHILD:/:(only|nth|last|first)-child(?:\(\s*(even|odd|(?:[+\-]?\d+|(?:[+\-]?\d*)?n\s*(?:[+\-]\s*\d+)?))\s*\))?/,POS:/:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^\-]|$)/,PSEUDO:/:((?:[\w\u00c0-\uFFFF\-]|\\.)+)(?:\((['"]?)((?:\([^\)]+\)|[^\(\)]*)+)\2\))?/},leftMatch:{},attrMap:{"class":"className","for":"htmlFor"},attrHandle:{href:function(a){return a.getAttribute("href")
},type:function(a){return a.getAttribute("type")}},relative:{"+":function(a,b){var c=typeof b=="string",d=c&&!j.test(b),e=c&&!d;
d&&(b=b.toLowerCase());for(var f=0,g=a.length,h;f<g;f++){if(h=a[f]){while((h=h.previousSibling)&&h.nodeType!==1){}a[f]=e||h&&h.nodeName.toLowerCase()===b?h||!1:h===b
}}e&&k.filter(b,a,!0)},">":function(a,b){var c,d=typeof b=="string",e=0,f=a.length;if(d&&!j.test(b)){b=b.toLowerCase();for(;
e<f;e++){c=a[e];if(c){var g=c.parentNode;a[e]=g.nodeName.toLowerCase()===b?g:!1}}}else{for(;e<f;e++){c=a[e],c&&(a[e]=d?c.parentNode:c.parentNode===b)
}d&&k.filter(b,a,!0)}},"":function(a,b,c){var e,f=d++,g=u;typeof b=="string"&&!j.test(b)&&(b=b.toLowerCase(),e=b,g=t),g("parentNode",b,f,a,e,c)
},"~":function(a,b,c){var e,f=d++,g=u;typeof b=="string"&&!j.test(b)&&(b=b.toLowerCase(),e=b,g=t),g("previousSibling",b,f,a,e,c)
}},find:{ID:function(a,b,c){if(typeof b.getElementById!="undefined"&&!c){var d=b.getElementById(a[1]);return d&&d.parentNode?[d]:[]
}},NAME:function(a,b){if(typeof b.getElementsByName!="undefined"){var c=[],d=b.getElementsByName(a[1]);for(var e=0,f=d.length;
e<f;e++){d[e].getAttribute("name")===a[1]&&c.push(d[e])}return c.length===0?null:c}},TAG:function(a,b){if(typeof b.getElementsByTagName!="undefined"){return b.getElementsByTagName(a[1])
}}},preFilter:{CLASS:function(a,b,c,d,e,f){a=" "+a[1].replace(i,"")+" ";if(f){return a}for(var g=0,h;(h=b[g])!=null;g++){h&&(e^(h.className&&(" "+h.className+" ").replace(/[\t\n\r]/g," ").indexOf(a)>=0)?c||d.push(h):c&&(b[g]=!1))
}return !1},ID:function(a){return a[1].replace(i,"")},TAG:function(a,b){return a[1].replace(i,"").toLowerCase()},CHILD:function(a){if(a[1]==="nth"){a[2]||k.error(a[0]),a[2]=a[2].replace(/^\+|\s*/g,"");
var b=/(-?)(\d*)(?:n([+\-]?\d*))?/.exec(a[2]==="even"&&"2n"||a[2]==="odd"&&"2n+1"||!/\D/.test(a[2])&&"0n+"+a[2]||a[2]);a[2]=b[1]+(b[2]||1)-0,a[3]=b[3]-0
}else{a[2]&&k.error(a[0])}a[0]=d++;return a},ATTR:function(a,b,c,d,e,f){var g=a[1]=a[1].replace(i,"");!f&&l.attrMap[g]&&(a[1]=l.attrMap[g]),a[4]=(a[4]||a[5]||"").replace(i,""),a[2]==="~="&&(a[4]=" "+a[4]+" ");
return a},PSEUDO:function(b,c,d,e,f){if(b[1]==="not"){if((a.exec(b[3])||"").length>1||/^\w/.test(b[3])){b[3]=k(b[3],null,null,c)
}else{var g=k.filter(b[3],c,d,!0^f);d||e.push.apply(e,g);return !1}}else{if(l.match.POS.test(b[0])||l.match.CHILD.test(b[0])){return !0
}}return b},POS:function(a){a.unshift(!0);return a}},filters:{enabled:function(a){return a.disabled===!1&&a.type!=="hidden"
},disabled:function(a){return a.disabled===!0},checked:function(a){return a.checked===!0},selected:function(a){a.parentNode&&a.parentNode.selectedIndex;
return a.selected===!0},parent:function(a){return !!a.firstChild},empty:function(a){return !a.firstChild},has:function(a,b,c){return !!k(c[3],a).length
},header:function(a){return/h\d/i.test(a.nodeName)},text:function(a){var b=a.getAttribute("type"),c=a.type;return a.nodeName.toLowerCase()==="input"&&"text"===c&&(b===c||b===null)
},radio:function(a){return a.nodeName.toLowerCase()==="input"&&"radio"===a.type},checkbox:function(a){return a.nodeName.toLowerCase()==="input"&&"checkbox"===a.type
},file:function(a){return a.nodeName.toLowerCase()==="input"&&"file"===a.type},password:function(a){return a.nodeName.toLowerCase()==="input"&&"password"===a.type
},submit:function(a){var b=a.nodeName.toLowerCase();return(b==="input"||b==="button")&&"submit"===a.type},image:function(a){return a.nodeName.toLowerCase()==="input"&&"image"===a.type
},reset:function(a){var b=a.nodeName.toLowerCase();return(b==="input"||b==="button")&&"reset"===a.type},button:function(a){var b=a.nodeName.toLowerCase();
return b==="input"&&"button"===a.type||b==="button"},input:function(a){return/input|select|textarea|button/i.test(a.nodeName)
},focus:function(a){return a===a.ownerDocument.activeElement}},setFilters:{first:function(a,b){return b===0},last:function(a,b,c,d){return b===d.length-1
},even:function(a,b){return b%2===0},odd:function(a,b){return b%2===1},lt:function(a,b,c){return b<c[3]-0},gt:function(a,b,c){return b>c[3]-0
},nth:function(a,b,c){return c[3]-0===b},eq:function(a,b,c){return c[3]-0===b}},filter:{PSEUDO:function(a,b,c,d){var e=b[1],f=l.filters[e];
if(f){return f(a,c,b,d)}if(e==="contains"){return(a.textContent||a.innerText||k.getText([a])||"").indexOf(b[3])>=0}if(e==="not"){var g=b[3];
for(var h=0,i=g.length;h<i;h++){if(g[h]===a){return !1}}return !0}k.error(e)},CHILD:function(a,b){var c=b[1],d=a;switch(c){case"only":case"first":while(d=d.previousSibling){if(d.nodeType===1){return !1
}}if(c==="first"){return !0}d=a;case"last":while(d=d.nextSibling){if(d.nodeType===1){return !1}}return !0;case"nth":var e=b[2],f=b[3];
if(e===1&&f===0){return !0}var g=b[0],h=a.parentNode;if(h&&(h.sizcache!==g||!a.nodeIndex)){var i=0;for(d=h.firstChild;d;d=d.nextSibling){d.nodeType===1&&(d.nodeIndex=++i)
}h.sizcache=g}var j=a.nodeIndex-f;return e===0?j===0:j%e===0&&j/e>=0}},ID:function(a,b){return a.nodeType===1&&a.getAttribute("id")===b
},TAG:function(a,b){return b==="*"&&a.nodeType===1||a.nodeName.toLowerCase()===b},CLASS:function(a,b){return(" "+(a.className||a.getAttribute("class"))+" ").indexOf(b)>-1
},ATTR:function(a,b){var c=b[1],d=l.attrHandle[c]?l.attrHandle[c](a):a[c]!=null?a[c]:a.getAttribute(c),e=d+"",f=b[2],g=b[4];
return d==null?f==="!=":f==="="?e===g:f==="*="?e.indexOf(g)>=0:f==="~="?(" "+e+" ").indexOf(g)>=0:g?f==="!="?e!==g:f==="^="?e.indexOf(g)===0:f==="$="?e.substr(e.length-g.length)===g:f==="|="?e===g||e.substr(0,g.length+1)===g+"-":!1:e&&d!==!1
},POS:function(a,b,c,d){var e=b[2],f=l.setFilters[e];if(f){return f(a,c,b,d)}}}},m=l.match.POS,n=function(a,b){return"\\"+(b-0+1)
};for(var o in l.match){l.match[o]=new RegExp(l.match[o].source+/(?![^\[]*\])(?![^\(]*\))/.source),l.leftMatch[o]=new RegExp(/(^(?:.|\r|\n)*?)/.source+l.match[o].source.replace(/\\(\d+)/g,n))
}var p=function(a,b){a=Array.prototype.slice.call(a,0);if(b){b.push.apply(b,a);return b}return a};try{Array.prototype.slice.call(c.documentElement.childNodes,0)[0].nodeType
}catch(q){p=function(a,b){var c=0,d=b||[];if(e.call(a)==="[object Array]"){Array.prototype.push.apply(d,a)}else{if(typeof a.length=="number"){for(var f=a.length;
c<f;c++){d.push(a[c])}}else{for(;a[c];c++){d.push(a[c])}}}return d}}var r,s;c.documentElement.compareDocumentPosition?r=function(a,b){if(a===b){g=!0;
return 0}if(!a.compareDocumentPosition||!b.compareDocumentPosition){return a.compareDocumentPosition?-1:1}return a.compareDocumentPosition(b)&4?-1:1
}:(r=function(a,b){if(a===b){g=!0;return 0}if(a.sourceIndex&&b.sourceIndex){return a.sourceIndex-b.sourceIndex}var c,d,e=[],f=[],h=a.parentNode,i=b.parentNode,j=h;
if(h===i){return s(a,b)}if(!h){return -1}if(!i){return 1}while(j){e.unshift(j),j=j.parentNode}j=i;while(j){f.unshift(j),j=j.parentNode
}c=e.length,d=f.length;for(var k=0;k<c&&k<d;k++){if(e[k]!==f[k]){return s(e[k],f[k])}}return k===c?s(a,f[k],-1):s(e[k],b,1)
},s=function(a,b,c){if(a===b){return c}var d=a.nextSibling;while(d){if(d===b){return -1}d=d.nextSibling}return 1}),k.getText=function(a){var b="",c;
for(var d=0;a[d];d++){c=a[d],c.nodeType===3||c.nodeType===4?b+=c.nodeValue:c.nodeType!==8&&(b+=k.getText(c.childNodes))}return b
},function(){var a=c.createElement("div"),d="script"+(new Date).getTime(),e=c.documentElement;a.innerHTML="<a name='"+d+"'/>",e.insertBefore(a,e.firstChild),c.getElementById(d)&&(l.find.ID=function(a,c,d){if(typeof c.getElementById!="undefined"&&!d){var e=c.getElementById(a[1]);
return e?e.id===a[1]||typeof e.getAttributeNode!="undefined"&&e.getAttributeNode("id").nodeValue===a[1]?[e]:b:[]}},l.filter.ID=function(a,b){var c=typeof a.getAttributeNode!="undefined"&&a.getAttributeNode("id");
return a.nodeType===1&&c&&c.nodeValue===b}),e.removeChild(a),e=a=null}(),function(){var a=c.createElement("div");a.appendChild(c.createComment("")),a.getElementsByTagName("*").length>0&&(l.find.TAG=function(a,b){var c=b.getElementsByTagName(a[1]);
if(a[1]==="*"){var d=[];for(var e=0;c[e];e++){c[e].nodeType===1&&d.push(c[e])}c=d}return c}),a.innerHTML="<a href='#'></a>",a.firstChild&&typeof a.firstChild.getAttribute!="undefined"&&a.firstChild.getAttribute("href")!=="#"&&(l.attrHandle.href=function(a){return a.getAttribute("href",2)
}),a=null}(),c.querySelectorAll&&function(){var a=k,b=c.createElement("div"),d="__sizzle__";b.innerHTML="<p class='TEST'></p>";
if(!b.querySelectorAll||b.querySelectorAll(".TEST").length!==0){k=function(b,e,f,g){e=e||c;if(!g&&!k.isXML(e)){var h=/^(\w+$)|^\.([\w\-]+$)|^#([\w\-]+$)/.exec(b);
if(h&&(e.nodeType===1||e.nodeType===9)){if(h[1]){return p(e.getElementsByTagName(b),f)}if(h[2]&&l.find.CLASS&&e.getElementsByClassName){return p(e.getElementsByClassName(h[2]),f)
}}if(e.nodeType===9){if(b==="body"&&e.body){return p([e.body],f)}if(h&&h[3]){var i=e.getElementById(h[3]);if(!i||!i.parentNode){return p([],f)
}if(i.id===h[3]){return p([i],f)}}try{return p(e.querySelectorAll(b),f)}catch(j){}}else{if(e.nodeType===1&&e.nodeName.toLowerCase()!=="object"){var m=e,n=e.getAttribute("id"),o=n||d,q=e.parentNode,r=/^\s*[+~]/.test(b);
n?o=o.replace(/'/g,"\\$&"):e.setAttribute("id",o),r&&q&&(e=e.parentNode);try{if(!r||q){return p(e.querySelectorAll("[id='"+o+"'] "+b),f)
}}catch(s){}finally{n||m.removeAttribute("id")}}}}return a(b,e,f,g)};for(var e in a){k[e]=a[e]}b=null}}(),function(){var a=c.documentElement,b=a.matchesSelector||a.mozMatchesSelector||a.webkitMatchesSelector||a.msMatchesSelector;
if(b){var d=!b.call(c.createElement("div"),"div"),e=!1;try{b.call(c.documentElement,"[test!='']:sizzle")}catch(f){e=!0}k.matchesSelector=function(a,c){c=c.replace(/\=\s*([^'"\]]*)\s*\]/g,"='$1']");
if(!k.isXML(a)){try{if(e||!l.match.PSEUDO.test(c)&&!/!=/.test(c)){var f=b.call(a,c);if(f||!d||a.document&&a.document.nodeType!==11){return f
}}}catch(g){}}return k(c,null,null,[a]).length>0}}}(),function(){var a=c.createElement("div");a.innerHTML="<div class='test e'></div><div class='test'></div>";
if(!!a.getElementsByClassName&&a.getElementsByClassName("e").length!==0){a.lastChild.className="e";if(a.getElementsByClassName("e").length===1){return
}l.order.splice(1,0,"CLASS"),l.find.CLASS=function(a,b,c){if(typeof b.getElementsByClassName!="undefined"&&!c){return b.getElementsByClassName(a[1])
}},a=null}}(),c.documentElement.contains?k.contains=function(a,b){return a!==b&&(a.contains?a.contains(b):!0)}:c.documentElement.compareDocumentPosition?k.contains=function(a,b){return !!(a.compareDocumentPosition(b)&16)
}:k.contains=function(){return !1},k.isXML=function(a){var b=(a?a.ownerDocument||a:0).documentElement;return b?b.nodeName!=="HTML":!1
};var v=function(a,b){var c,d=[],e="",f=b.nodeType?[b]:b;while(c=l.match.PSEUDO.exec(a)){e+=c[0],a=a.replace(l.match.PSEUDO,"")
}a=l.relative[a]?a+"*":a;for(var g=0,h=f.length;g<h;g++){k(a,f[g],d)}return k.filter(e,d)};f.find=k,f.expr=k.selectors,f.expr[":"]=f.expr.filters,f.unique=k.uniqueSort,f.text=k.getText,f.isXMLDoc=k.isXML,f.contains=k.contains
}();var P=/Until$/,Q=/^(?:parents|prevUntil|prevAll)/,R=/,/,S=/^.[^:#\[\.,]*$/,T=Array.prototype.slice,U=f.expr.match.POS,V={children:!0,contents:!0,next:!0,prev:!0};
f.fn.extend({find:function(a){var b=this,c,d;if(typeof a!="string"){return f(a).filter(function(){for(c=0,d=b.length;c<d;
c++){if(f.contains(b[c],this)){return !0}}})}var e=this.pushStack("","find",a),g,h,i;for(c=0,d=this.length;c<d;c++){g=e.length,f.find(a,this[c],e);
if(c>0){for(h=g;h<e.length;h++){for(i=0;i<g;i++){if(e[i]===e[h]){e.splice(h--,1);break}}}}}return e},has:function(a){var b=f(a);
return this.filter(function(){for(var a=0,c=b.length;a<c;a++){if(f.contains(this,b[a])){return !0}}})},not:function(a){return this.pushStack(X(this,a,!1),"not",a)
},filter:function(a){return this.pushStack(X(this,a,!0),"filter",a)},is:function(a){return !!a&&(typeof a=="string"?f.filter(a,this).length>0:this.filter(a).length>0)
},closest:function(a,b){var c=[],d,e,g=this[0];if(f.isArray(a)){var h,i,j={},k=1;if(g&&a.length){for(d=0,e=a.length;d<e;d++){i=a[d],j[i]||(j[i]=U.test(i)?f(i,b||this.context):i)
}while(g&&g.ownerDocument&&g!==b){for(i in j){h=j[i],(h.jquery?h.index(g)>-1:f(g).is(h))&&c.push({selector:i,elem:g,level:k})
}g=g.parentNode,k++}}return c}var l=U.test(a)||typeof a!="string"?f(a,b||this.context):0;for(d=0,e=this.length;d<e;d++){g=this[d];
while(g){if(l?l.index(g)>-1:f.find.matchesSelector(g,a)){c.push(g);break}g=g.parentNode;if(!g||!g.ownerDocument||g===b||g.nodeType===11){break
}}}c=c.length>1?f.unique(c):c;return this.pushStack(c,"closest",a)},index:function(a){if(!a||typeof a=="string"){return f.inArray(this[0],a?f(a):this.parent().children())
}return f.inArray(a.jquery?a[0]:a,this)},add:function(a,b){var c=typeof a=="string"?f(a,b):f.makeArray(a&&a.nodeType?[a]:a),d=f.merge(this.get(),c);
return this.pushStack(W(c[0])||W(d[0])?d:f.unique(d))},andSelf:function(){return this.add(this.prevObject)}}),f.each({parent:function(a){var b=a.parentNode;
return b&&b.nodeType!==11?b:null},parents:function(a){return f.dir(a,"parentNode")},parentsUntil:function(a,b,c){return f.dir(a,"parentNode",c)
},next:function(a){return f.nth(a,2,"nextSibling")},prev:function(a){return f.nth(a,2,"previousSibling")},nextAll:function(a){return f.dir(a,"nextSibling")
},prevAll:function(a){return f.dir(a,"previousSibling")},nextUntil:function(a,b,c){return f.dir(a,"nextSibling",c)},prevUntil:function(a,b,c){return f.dir(a,"previousSibling",c)
},siblings:function(a){return f.sibling(a.parentNode.firstChild,a)},children:function(a){return f.sibling(a.firstChild)},contents:function(a){return f.nodeName(a,"iframe")?a.contentDocument||a.contentWindow.document:f.makeArray(a.childNodes)
}},function(a,b){f.fn[a]=function(c,d){var e=f.map(this,b,c),g=T.call(arguments);P.test(a)||(d=c),d&&typeof d=="string"&&(e=f.filter(d,e)),e=this.length>1&&!V[a]?f.unique(e):e,(this.length>1||R.test(d))&&Q.test(a)&&(e=e.reverse());
return this.pushStack(e,a,g.join(","))}}),f.extend({filter:function(a,b,c){c&&(a=":not("+a+")");return b.length===1?f.find.matchesSelector(b[0],a)?[b[0]]:[]:f.find.matches(a,b)
},dir:function(a,c,d){var e=[],g=a[c];while(g&&g.nodeType!==9&&(d===b||g.nodeType!==1||!f(g).is(d))){g.nodeType===1&&e.push(g),g=g[c]
}return e},nth:function(a,b,c,d){b=b||1;var e=0;for(;a;a=a[c]){if(a.nodeType===1&&++e===b){break}}return a},sibling:function(a,b){var c=[];
for(;a;a=a.nextSibling){a.nodeType===1&&a!==b&&c.push(a)}return c}});var Y=/ jQuery\d+="(?:\d+|null)"/g,Z=/^\s+/,$=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/ig,_=/<([\w:]+)/,ba=/<tbody/i,bb=/<|&#?\w+;/,bc=/<(?:script|object|embed|option|style)/i,bd=/checked\s*(?:[^=]|=\s*.checked.)/i,be=/\/(java|ecma)script/i,bf=/^\s*<!(?:\[CDATA\[|\-\-)/,bg={option:[1,"<select multiple='multiple'>","</select>"],legend:[1,"<fieldset>","</fieldset>"],thead:[1,"<table>","</table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],col:[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"],area:[1,"<map>","</map>"],_default:[0,"",""]};
bg.optgroup=bg.option,bg.tbody=bg.tfoot=bg.colgroup=bg.caption=bg.thead,bg.th=bg.td,f.support.htmlSerialize||(bg._default=[1,"div<div>","</div>"]),f.fn.extend({text:function(a){if(f.isFunction(a)){return this.each(function(b){var c=f(this);
c.text(a.call(this,b,c.text()))})}if(typeof a!="object"&&a!==b){return this.empty().append((this[0]&&this[0].ownerDocument||c).createTextNode(a))
}return f.text(this)},wrapAll:function(a){if(f.isFunction(a)){return this.each(function(b){f(this).wrapAll(a.call(this,b))
})}if(this[0]){var b=f(a,this[0].ownerDocument).eq(0).clone(!0);this[0].parentNode&&b.insertBefore(this[0]),b.map(function(){var a=this;
while(a.firstChild&&a.firstChild.nodeType===1){a=a.firstChild}return a}).append(this)}return this},wrapInner:function(a){if(f.isFunction(a)){return this.each(function(b){f(this).wrapInner(a.call(this,b))
})}return this.each(function(){var b=f(this),c=b.contents();c.length?c.wrapAll(a):b.append(a)})},wrap:function(a){return this.each(function(){f(this).wrapAll(a)
})},unwrap:function(){return this.parent().each(function(){f.nodeName(this,"body")||f(this).replaceWith(this.childNodes)}).end()
},append:function(){return this.domManip(arguments,!0,function(a){this.nodeType===1&&this.appendChild(a)})},prepend:function(){return this.domManip(arguments,!0,function(a){this.nodeType===1&&this.insertBefore(a,this.firstChild)
})},before:function(){if(this[0]&&this[0].parentNode){return this.domManip(arguments,!1,function(a){this.parentNode.insertBefore(a,this)
})}if(arguments.length){var a=f(arguments[0]);a.push.apply(a,this.toArray());return this.pushStack(a,"before",arguments)}},after:function(){if(this[0]&&this[0].parentNode){return this.domManip(arguments,!1,function(a){this.parentNode.insertBefore(a,this.nextSibling)
})}if(arguments.length){var a=this.pushStack(this,"after",arguments);a.push.apply(a,f(arguments[0]).toArray());return a}},remove:function(a,b){for(var c=0,d;
(d=this[c])!=null;c++){if(!a||f.filter(a,[d]).length){!b&&d.nodeType===1&&(f.cleanData(d.getElementsByTagName("*")),f.cleanData([d])),d.parentNode&&d.parentNode.removeChild(d)
}}return this},empty:function(){for(var a=0,b;(b=this[a])!=null;a++){b.nodeType===1&&f.cleanData(b.getElementsByTagName("*"));
while(b.firstChild){b.removeChild(b.firstChild)}}return this},clone:function(a,b){a=a==null?!1:a,b=b==null?a:b;return this.map(function(){return f.clone(this,a,b)
})},html:function(a){if(a===b){return this[0]&&this[0].nodeType===1?this[0].innerHTML.replace(Y,""):null}if(typeof a=="string"&&!bc.test(a)&&(f.support.leadingWhitespace||!Z.test(a))&&!bg[(_.exec(a)||["",""])[1].toLowerCase()]){a=a.replace($,"<$1></$2>");
try{for(var c=0,d=this.length;c<d;c++){this[c].nodeType===1&&(f.cleanData(this[c].getElementsByTagName("*")),this[c].innerHTML=a)
}}catch(e){this.empty().append(a)}}else{f.isFunction(a)?this.each(function(b){var c=f(this);c.html(a.call(this,b,c.html()))
}):this.empty().append(a)}return this},replaceWith:function(a){if(this[0]&&this[0].parentNode){if(f.isFunction(a)){return this.each(function(b){var c=f(this),d=c.html();
c.replaceWith(a.call(this,b,d))})}typeof a!="string"&&(a=f(a).detach());return this.each(function(){var b=this.nextSibling,c=this.parentNode;
f(this).remove(),b?f(b).before(a):f(c).append(a)})}return this.length?this.pushStack(f(f.isFunction(a)?a():a),"replaceWith",a):this
},detach:function(a){return this.remove(a,!0)},domManip:function(a,c,d){var e,g,h,i,j=a[0],k=[];if(!f.support.checkClone&&arguments.length===3&&typeof j=="string"&&bd.test(j)){return this.each(function(){f(this).domManip(a,c,d,!0)
})}if(f.isFunction(j)){return this.each(function(e){var g=f(this);a[0]=j.call(this,e,c?g.html():b),g.domManip(a,c,d)})}if(this[0]){i=j&&j.parentNode,f.support.parentNode&&i&&i.nodeType===11&&i.childNodes.length===this.length?e={fragment:i}:e=f.buildFragment(a,this,k),h=e.fragment,h.childNodes.length===1?g=h=h.firstChild:g=h.firstChild;
if(g){c=c&&f.nodeName(g,"tr");for(var l=0,m=this.length,n=m-1;l<m;l++){d.call(c?bh(this[l],g):this[l],e.cacheable||m>1&&l<n?f.clone(h,!0,!0):h)
}}k.length&&f.each(k,bn)}return this}}),f.buildFragment=function(a,b,d){var e,g,h,i=b&&b[0]?b[0].ownerDocument||b[0]:c;a.length===1&&typeof a[0]=="string"&&a[0].length<512&&i===c&&a[0].charAt(0)==="<"&&!bc.test(a[0])&&(f.support.checkClone||!bd.test(a[0]))&&(g=!0,h=f.fragments[a[0]],h&&h!==1&&(e=h)),e||(e=i.createDocumentFragment(),f.clean(a,i,e,d)),g&&(f.fragments[a[0]]=h?e:1);
return{fragment:e,cacheable:g}},f.fragments={},f.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(a,b){f.fn[a]=function(c){var d=[],e=f(c),g=this.length===1&&this[0].parentNode;
if(g&&g.nodeType===11&&g.childNodes.length===1&&e.length===1){e[b](this[0]);return this}for(var h=0,i=e.length;h<i;h++){var j=(h>0?this.clone(!0):this).get();
f(e[h])[b](j),d=d.concat(j)}return this.pushStack(d,a,e.selector)}}),f.extend({clone:function(a,b,c){var d=a.cloneNode(!0),e,g,h;
if((!f.support.noCloneEvent||!f.support.noCloneChecked)&&(a.nodeType===1||a.nodeType===11)&&!f.isXMLDoc(a)){bj(a,d),e=bk(a),g=bk(d);
for(h=0;e[h];++h){bj(e[h],g[h])}}if(b){bi(a,d);if(c){e=bk(a),g=bk(d);for(h=0;e[h];++h){bi(e[h],g[h])}}}return d},clean:function(a,b,d,e){var g;
b=b||c,typeof b.createElement=="undefined"&&(b=b.ownerDocument||b[0]&&b[0].ownerDocument||c);var h=[],i;for(var j=0,k;(k=a[j])!=null;
j++){typeof k=="number"&&(k+="");if(!k){continue}if(typeof k=="string"){if(!bb.test(k)){k=b.createTextNode(k)}else{k=k.replace($,"<$1></$2>");
var l=(_.exec(k)||["",""])[1].toLowerCase(),m=bg[l]||bg._default,n=m[0],o=b.createElement("div");o.innerHTML=m[1]+k+m[2];
while(n--){o=o.lastChild}if(!f.support.tbody){var p=ba.test(k),q=l==="table"&&!p?o.firstChild&&o.firstChild.childNodes:m[1]==="<table>"&&!p?o.childNodes:[];
for(i=q.length-1;i>=0;--i){f.nodeName(q[i],"tbody")&&!q[i].childNodes.length&&q[i].parentNode.removeChild(q[i])}}!f.support.leadingWhitespace&&Z.test(k)&&o.insertBefore(b.createTextNode(Z.exec(k)[0]),o.firstChild),k=o.childNodes
}}var r;if(!f.support.appendChecked){if(k[0]&&typeof(r=k.length)=="number"){for(i=0;i<r;i++){bm(k[i])}}else{bm(k)}}k.nodeType?h.push(k):h=f.merge(h,k)
}if(d){g=function(a){return !a.type||be.test(a.type)};for(j=0;h[j];j++){if(e&&f.nodeName(h[j],"script")&&(!h[j].type||h[j].type.toLowerCase()==="text/javascript")){e.push(h[j].parentNode?h[j].parentNode.removeChild(h[j]):h[j])
}else{if(h[j].nodeType===1){var s=f.grep(h[j].getElementsByTagName("script"),g);h.splice.apply(h,[j+1,0].concat(s))}d.appendChild(h[j])
}}}return h},cleanData:function(a){var b,c,d=f.cache,e=f.expando,g=f.event.special,h=f.support.deleteExpando;for(var i=0,j;
(j=a[i])!=null;i++){if(j.nodeName&&f.noData[j.nodeName.toLowerCase()]){continue}c=j[f.expando];if(c){b=d[c]&&d[c][e];if(b&&b.events){for(var k in b.events){g[k]?f.event.remove(j,k):f.removeEvent(j,k,b.handle)
}b.handle&&(b.handle.elem=null)}h?delete j[f.expando]:j.removeAttribute&&j.removeAttribute(f.expando),delete d[c]}}}});var bo=/alpha\([^)]*\)/i,bp=/opacity=([^)]*)/,bq=/-([a-z])/ig,br=/([A-Z]|^ms)/g,bs=/^-?\d+(?:px)?$/i,bt=/^-?\d/,bu=/^[+\-]=/,bv=/[^+\-\.\de]+/g,bw={position:"absolute",visibility:"hidden",display:"block"},bx=["Left","Right"],by=["Top","Bottom"],bz,bA,bB,bC=function(a,b){return b.toUpperCase()
};f.fn.css=function(a,c){if(arguments.length===2&&c===b){return this}return f.access(this,a,c,!0,function(a,c,d){return d!==b?f.style(a,c,d):f.css(a,c)
})},f.extend({cssHooks:{opacity:{get:function(a,b){if(b){var c=bz(a,"opacity","opacity");return c===""?"1":c}return a.style.opacity
}}},cssNumber:{zIndex:!0,fontWeight:!0,opacity:!0,zoom:!0,lineHeight:!0,widows:!0,orphans:!0},cssProps:{"float":f.support.cssFloat?"cssFloat":"styleFloat"},style:function(a,c,d,e){if(!!a&&a.nodeType!==3&&a.nodeType!==8&&!!a.style){var g,h,i=f.camelCase(c),j=a.style,k=f.cssHooks[i];
c=f.cssProps[i]||i;if(d===b){if(k&&"get" in k&&(g=k.get(a,!1,e))!==b){return g}return j[c]}h=typeof d;if(h==="number"&&isNaN(d)||d==null){return
}h==="string"&&bu.test(d)&&(d=+d.replace(bv,"")+parseFloat(f.css(a,c))),h==="number"&&!f.cssNumber[i]&&(d+="px");if(!k||!("set" in k)||(d=k.set(a,d))!==b){try{j[c]=d
}catch(l){}}}},css:function(a,c,d){var e,g;c=f.camelCase(c),g=f.cssHooks[c],c=f.cssProps[c]||c,c==="cssFloat"&&(c="float");
if(g&&"get" in g&&(e=g.get(a,!0,d))!==b){return e}if(bz){return bz(a,c)}},swap:function(a,b,c){var d={};for(var e in b){d[e]=a.style[e],a.style[e]=b[e]
}c.call(a);for(e in b){a.style[e]=d[e]}},camelCase:function(a){return a.replace(bq,bC)}}),f.curCSS=f.css,f.each(["height","width"],function(a,b){f.cssHooks[b]={get:function(a,c,d){var e;
if(c){a.offsetWidth!==0?e=bD(a,b,d):f.swap(a,bw,function(){e=bD(a,b,d)});if(e<=0){e=bz(a,b,b),e==="0px"&&bB&&(e=bB(a,b,b));
if(e!=null){return e===""||e==="auto"?"0px":e}}if(e<0||e==null){e=a.style[b];return e===""||e==="auto"?"0px":e}return typeof e=="string"?e:e+"px"
}},set:function(a,b){if(!bs.test(b)){return b}b=parseFloat(b);if(b>=0){return b+"px"}}}}),f.support.opacity||(f.cssHooks.opacity={get:function(a,b){return bp.test((b&&a.currentStyle?a.currentStyle.filter:a.style.filter)||"")?parseFloat(RegExp.$1)/100+"":b?"1":""
},set:function(a,b){var c=a.style,d=a.currentStyle;c.zoom=1;var e=f.isNaN(b)?"":"alpha(opacity="+b*100+")",g=d&&d.filter||c.filter||"";
c.filter=bo.test(g)?g.replace(bo,e):g+" "+e}}),f(function(){f.support.reliableMarginRight||(f.cssHooks.marginRight={get:function(a,b){var c;
f.swap(a,{display:"inline-block"},function(){b?c=bz(a,"margin-right","marginRight"):c=a.style.marginRight});return c}})}),c.defaultView&&c.defaultView.getComputedStyle&&(bA=function(a,c){var d,e,g;
c=c.replace(br,"-$1").toLowerCase();if(!(e=a.ownerDocument.defaultView)){return b}if(g=e.getComputedStyle(a,null)){d=g.getPropertyValue(c),d===""&&!f.contains(a.ownerDocument.documentElement,a)&&(d=f.style(a,c))
}return d}),c.documentElement.currentStyle&&(bB=function(a,b){var c,d=a.currentStyle&&a.currentStyle[b],e=a.runtimeStyle&&a.runtimeStyle[b],f=a.style;
!bs.test(d)&&bt.test(d)&&(c=f.left,e&&(a.runtimeStyle.left=a.currentStyle.left),f.left=b==="fontSize"?"1em":d||0,d=f.pixelLeft+"px",f.left=c,e&&(a.runtimeStyle.left=e));
return d===""?"auto":d}),bz=bA||bB,f.expr&&f.expr.filters&&(f.expr.filters.hidden=function(a){var b=a.offsetWidth,c=a.offsetHeight;
return b===0&&c===0||!f.support.reliableHiddenOffsets&&(a.style.display||f.css(a,"display"))==="none"},f.expr.filters.visible=function(a){return !f.expr.filters.hidden(a)
});var bE=/%20/g,bF=/\[\]$/,bG=/\r?\n/g,bH=/#.*$/,bI=/^(.*?):[ \t]*([^\r\n]*)\r?$/mg,bJ=/^(?:color|date|datetime|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i,bK=/^(?:about|app|app\-storage|.+\-extension|file|widget):$/,bL=/^(?:GET|HEAD)$/,bM=/^\/\//,bN=/\?/,bO=/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,bP=/^(?:select|textarea)/i,bQ=/\s+/,bR=/([?&])_=[^&]*/,bS=/^([\w\+\.\-]+:)(?:\/\/([^\/?#:]*)(?::(\d+))?)?/,bT=f.fn.load,bU={},bV={},bW,bX;
try{bW=e.href}catch(bY){bW=c.createElement("a"),bW.href="",bW=bW.href}bX=bS.exec(bW.toLowerCase())||[],f.fn.extend({load:function(a,c,d){if(typeof a!="string"&&bT){return bT.apply(this,arguments)
}if(!this.length){return this}var e=a.indexOf(" ");if(e>=0){var g=a.slice(e,a.length);a=a.slice(0,e)}var h="GET";c&&(f.isFunction(c)?(d=c,c=b):typeof c=="object"&&(c=f.param(c,f.ajaxSettings.traditional),h="POST"));
var i=this;f.ajax({url:a,type:h,dataType:"html",data:c,complete:function(a,b,c){c=a.responseText,a.isResolved()&&(a.done(function(a){c=a
}),i.html(g?f("<div>").append(c.replace(bO,"")).find(g):c)),d&&i.each(d,[c,b,a])}});return this},serialize:function(){return f.param(this.serializeArray())
},serializeArray:function(){return this.map(function(){return this.elements?f.makeArray(this.elements):this}).filter(function(){return this.name&&!this.disabled&&(this.checked||bP.test(this.nodeName)||bJ.test(this.type))
}).map(function(a,b){var c=f(this).val();return c==null?null:f.isArray(c)?f.map(c,function(a,c){return{name:b.name,value:a.replace(bG,"\r\n")}
}):{name:b.name,value:c.replace(bG,"\r\n")}}).get()}}),f.each("ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split(" "),function(a,b){f.fn[b]=function(a){return this.bind(b,a)
}}),f.each(["get","post"],function(a,c){f[c]=function(a,d,e,g){f.isFunction(d)&&(g=g||e,e=d,d=b);return f.ajax({type:c,url:a,data:d,success:e,dataType:g})
}}),f.extend({getScript:function(a,c){return f.get(a,b,c,"script")},getJSON:function(a,b,c){return f.get(a,b,c,"json")},ajaxSetup:function(a,b){b?f.extend(!0,a,f.ajaxSettings,b):(b=a,a=f.extend(!0,f.ajaxSettings,b));
for(var c in {context:1,url:1}){c in b?a[c]=b[c]:c in f.ajaxSettings&&(a[c]=f.ajaxSettings[c])}return a},ajaxSettings:{url:bW,isLocal:bK.test(bX[1]),global:!0,type:"GET",contentType:"application/x-www-form-urlencoded",processData:!0,async:!0,accepts:{xml:"application/xml, text/xml",html:"text/html",text:"text/plain",json:"application/json, text/javascript","*":"*/*"},contents:{xml:/xml/,html:/html/,json:/json/},responseFields:{xml:"responseXML",text:"responseText"},converters:{"* text":a.String,"text html":!0,"text json":f.parseJSON,"text xml":f.parseXML}},ajaxPrefilter:bZ(bU),ajaxTransport:bZ(bV),ajax:function(a,c){function w(a,c,l,m){if(s!==2){s=2,q&&clearTimeout(q),p=b,n=m||"",v.readyState=a?4:0;
var o,r,u,w=l?ca(d,v,l):b,x,y;if(a>=200&&a<300||a===304){if(d.ifModified){if(x=v.getResponseHeader("Last-Modified")){f.lastModified[k]=x
}if(y=v.getResponseHeader("Etag")){f.etag[k]=y}}if(a===304){c="notmodified",o=!0}else{try{r=cb(d,w),c="success",o=!0}catch(z){c="parsererror",u=z
}}}else{u=c;if(!c||a){c="error",a<0&&(a=0)}}v.status=a,v.statusText=c,o?h.resolveWith(e,[r,c,v]):h.rejectWith(e,[v,c,u]),v.statusCode(j),j=b,t&&g.trigger("ajax"+(o?"Success":"Error"),[v,d,o?r:u]),i.resolveWith(e,[v,c]),t&&(g.trigger("ajaxComplete",[v,d]),--f.active||f.event.trigger("ajaxStop"))
}}typeof a=="object"&&(c=a,a=b),c=c||{};var d=f.ajaxSetup({},c),e=d.context||d,g=e!==d&&(e.nodeType||e instanceof f)?f(e):f.event,h=f.Deferred(),i=f._Deferred(),j=d.statusCode||{},k,l={},m={},n,o,p,q,r,s=0,t,u,v={readyState:0,setRequestHeader:function(a,b){if(!s){var c=a.toLowerCase();
a=m[c]=m[c]||a,l[a]=b}return this},getAllResponseHeaders:function(){return s===2?n:null},getResponseHeader:function(a){var c;
if(s===2){if(!o){o={};while(c=bI.exec(n)){o[c[1].toLowerCase()]=c[2]}}c=o[a.toLowerCase()]}return c===b?null:c},overrideMimeType:function(a){s||(d.mimeType=a);
return this},abort:function(a){a=a||"abort",p&&p.abort(a),w(0,a);return this}};h.promise(v),v.success=v.done,v.error=v.fail,v.complete=i.done,v.statusCode=function(a){if(a){var b;
if(s<2){for(b in a){j[b]=[j[b],a[b]]}}else{b=a[v.status],v.then(b,b)}}return this},d.url=((a||d.url)+"").replace(bH,"").replace(bM,bX[1]+"//"),d.dataTypes=f.trim(d.dataType||"*").toLowerCase().split(bQ),d.crossDomain==null&&(r=bS.exec(d.url.toLowerCase()),d.crossDomain=!(!r||r[1]==bX[1]&&r[2]==bX[2]&&(r[3]||(r[1]==="http:"?80:443))==(bX[3]||(bX[1]==="http:"?80:443)))),d.data&&d.processData&&typeof d.data!="string"&&(d.data=f.param(d.data,d.traditional)),b$(bU,d,c,v);
if(s===2){return !1}t=d.global,d.type=d.type.toUpperCase(),d.hasContent=!bL.test(d.type),t&&f.active++===0&&f.event.trigger("ajaxStart");
if(!d.hasContent){d.data&&(d.url+=(bN.test(d.url)?"&":"?")+d.data),k=d.url;if(d.cache===!1){var x=f.now(),y=d.url.replace(bR,"$1_="+x);
d.url=y+(y===d.url?(bN.test(d.url)?"&":"?")+"_="+x:"")}}(d.data&&d.hasContent&&d.contentType!==!1||c.contentType)&&v.setRequestHeader("Content-Type",d.contentType),d.ifModified&&(k=k||d.url,f.lastModified[k]&&v.setRequestHeader("If-Modified-Since",f.lastModified[k]),f.etag[k]&&v.setRequestHeader("If-None-Match",f.etag[k])),v.setRequestHeader("Accept",d.dataTypes[0]&&d.accepts[d.dataTypes[0]]?d.accepts[d.dataTypes[0]]+(d.dataTypes[0]!=="*"?", */*; q=0.01":""):d.accepts["*"]);
for(u in d.headers){v.setRequestHeader(u,d.headers[u])}if(d.beforeSend&&(d.beforeSend.call(e,v,d)===!1||s===2)){v.abort();
return !1}for(u in {success:1,error:1,complete:1}){v[u](d[u])}p=b$(bV,d,c,v);if(!p){w(-1,"No Transport")}else{v.readyState=1,t&&g.trigger("ajaxSend",[v,d]),d.async&&d.timeout>0&&(q=setTimeout(function(){v.abort("timeout")
},d.timeout));try{s=1,p.send(l,w)}catch(z){status<2?w(-1,z):f.error(z)}}return v},param:function(a,c){var d=[],e=function(a,b){b=f.isFunction(b)?b():b,d[d.length]=encodeURIComponent(a)+"="+encodeURIComponent(b)
};c===b&&(c=f.ajaxSettings.traditional);if(f.isArray(a)||a.jquery&&!f.isPlainObject(a)){f.each(a,function(){e(this.name,this.value)
})}else{for(var g in a){b_(g,a[g],c,e)}}return d.join("&").replace(bE,"+")}}),f.extend({active:0,lastModified:{},etag:{}});
var cc=f.now(),cd=/(\=)\?(&|$)|\?\?/i;f.ajaxSetup({jsonp:"callback",jsonpCallback:function(){return f.expando+"_"+cc++}}),f.ajaxPrefilter("json jsonp",function(b,c,d){var e=b.contentType==="application/x-www-form-urlencoded"&&typeof b.data=="string";
if(b.dataTypes[0]==="jsonp"||b.jsonp!==!1&&(cd.test(b.url)||e&&cd.test(b.data))){var g,h=b.jsonpCallback=f.isFunction(b.jsonpCallback)?b.jsonpCallback():b.jsonpCallback,i=a[h],j=b.url,k=b.data,l="$1"+h+"$2";
b.jsonp!==!1&&(j=j.replace(cd,l),b.url===j&&(e&&(k=k.replace(cd,l)),b.data===k&&(j+=(/\?/.test(j)?"&":"?")+b.jsonp+"="+h))),b.url=j,b.data=k,a[h]=function(a){g=[a]
},d.always(function(){a[h]=i,g&&f.isFunction(i)&&a[h](g[0])}),b.converters["script json"]=function(){g||f.error(h+" was not called");
return g[0]},b.dataTypes[0]="json";return"script"}}),f.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/javascript|ecmascript/},converters:{"text script":function(a){f.globalEval(a);
return a}}}),f.ajaxPrefilter("script",function(a){a.cache===b&&(a.cache=!1),a.crossDomain&&(a.type="GET",a.global=!1)}),f.ajaxTransport("script",function(a){if(a.crossDomain){var d,e=c.head||c.getElementsByTagName("head")[0]||c.documentElement;
return{send:function(f,g){d=c.createElement("script"),d.async="async",a.scriptCharset&&(d.charset=a.scriptCharset),d.src=a.url,d.onload=d.onreadystatechange=function(a,c){if(c||!d.readyState||/loaded|complete/.test(d.readyState)){d.onload=d.onreadystatechange=null,e&&d.parentNode&&e.removeChild(d),d=b,c||g(200,"success")
}},e.insertBefore(d,e.firstChild)},abort:function(){d&&d.onload(0,1)}}}});var ce=a.ActiveXObject?function(){for(var a in cg){cg[a](0,1)
}}:!1,cf=0,cg;f.ajaxSettings.xhr=a.ActiveXObject?function(){return !this.isLocal&&ch()||ci()}:ch,function(a){f.extend(f.support,{ajax:!!a,cors:!!a&&"withCredentials" in a})
}(f.ajaxSettings.xhr()),f.support.ajax&&f.ajaxTransport(function(c){if(!c.crossDomain||f.support.cors){var d;return{send:function(e,g){var h=c.xhr(),i,j;
c.username?h.open(c.type,c.url,c.async,c.username,c.password):h.open(c.type,c.url,c.async);if(c.xhrFields){for(j in c.xhrFields){h[j]=c.xhrFields[j]
}}c.mimeType&&h.overrideMimeType&&h.overrideMimeType(c.mimeType),!c.crossDomain&&!e["X-Requested-With"]&&(e["X-Requested-With"]="XMLHttpRequest");
try{for(j in e){h.setRequestHeader(j,e[j])}}catch(k){}h.send(c.hasContent&&c.data||null),d=function(a,e){var j,k,l,m,n;try{if(d&&(e||h.readyState===4)){d=b,i&&(h.onreadystatechange=f.noop,ce&&delete cg[i]);
if(e){h.readyState!==4&&h.abort()}else{j=h.status,l=h.getAllResponseHeaders(),m={},n=h.responseXML,n&&n.documentElement&&(m.xml=n),m.text=h.responseText;
try{k=h.statusText}catch(o){k=""}!j&&c.isLocal&&!c.crossDomain?j=m.text?200:404:j===1223&&(j=204)}}}catch(p){e||g(-1,p)}m&&g(j,k,m,l)
},!c.async||h.readyState===4?d():(i=++cf,ce&&(cg||(cg={},f(a).unload(ce)),cg[i]=d),h.onreadystatechange=d)},abort:function(){d&&d(0,1)
}}}});var cj={},ck,cl,cm=/^(?:toggle|show|hide)$/,cn=/^([+\-]=)?([\d+.\-]+)([a-z%]*)$/i,co,cp=[["height","marginTop","marginBottom","paddingTop","paddingBottom"],["width","marginLeft","marginRight","paddingLeft","paddingRight"],["opacity"]],cq,cr=a.webkitRequestAnimationFrame||a.mozRequestAnimationFrame||a.oRequestAnimationFrame;
f.fn.extend({show:function(a,b,c){var d,e;if(a||a===0){return this.animate(cu("show",3),a,b,c)}for(var g=0,h=this.length;
g<h;g++){d=this[g],d.style&&(e=d.style.display,!f._data(d,"olddisplay")&&e==="none"&&(e=d.style.display=""),e===""&&f.css(d,"display")==="none"&&f._data(d,"olddisplay",cv(d.nodeName)))
}for(g=0;g<h;g++){d=this[g];if(d.style){e=d.style.display;if(e===""||e==="none"){d.style.display=f._data(d,"olddisplay")||""
}}}return this},hide:function(a,b,c){if(a||a===0){return this.animate(cu("hide",3),a,b,c)}for(var d=0,e=this.length;d<e;d++){if(this[d].style){var g=f.css(this[d],"display");
g!=="none"&&!f._data(this[d],"olddisplay")&&f._data(this[d],"olddisplay",g)}}for(d=0;d<e;d++){this[d].style&&(this[d].style.display="none")
}return this},_toggle:f.fn.toggle,toggle:function(a,b,c){var d=typeof a=="boolean";f.isFunction(a)&&f.isFunction(b)?this._toggle.apply(this,arguments):a==null||d?this.each(function(){var b=d?a:f(this).is(":hidden");
f(this)[b?"show":"hide"]()}):this.animate(cu("toggle",3),a,b,c);return this},fadeTo:function(a,b,c,d){return this.filter(":hidden").css("opacity",0).show().end().animate({opacity:b},a,c,d)
},animate:function(a,b,c,d){var e=f.speed(b,c,d);if(f.isEmptyObject(a)){return this.each(e.complete,[!1])}a=f.extend({},a);
return this[e.queue===!1?"each":"queue"](function(){e.queue===!1&&f._mark(this);var b=f.extend({},e),c=this.nodeType===1,d=c&&f(this).is(":hidden"),g,h,i,j,k,l,m,n,o;
b.animatedProperties={};for(i in a){g=f.camelCase(i),i!==g&&(a[g]=a[i],delete a[i]),h=a[g],f.isArray(h)?(b.animatedProperties[g]=h[1],h=a[g]=h[0]):b.animatedProperties[g]=b.specialEasing&&b.specialEasing[g]||b.easing||"swing";
if(h==="hide"&&d||h==="show"&&!d){return b.complete.call(this)}c&&(g==="height"||g==="width")&&(b.overflow=[this.style.overflow,this.style.overflowX,this.style.overflowY],f.css(this,"display")==="inline"&&f.css(this,"float")==="none"&&(f.support.inlineBlockNeedsLayout?(j=cv(this.nodeName),j==="inline"?this.style.display="inline-block":(this.style.display="inline",this.style.zoom=1)):this.style.display="inline-block"))
}b.overflow!=null&&(this.style.overflow="hidden");for(i in a){k=new f.fx(this,b,i),h=a[i],cm.test(h)?k[h==="toggle"?d?"show":"hide":h]():(l=cn.exec(h),m=k.cur(),l?(n=parseFloat(l[2]),o=l[3]||(f.cssNumber[i]?"":"px"),o!=="px"&&(f.style(this,i,(n||1)+o),m=(n||1)/k.cur()*m,f.style(this,i,m+o)),l[1]&&(n=(l[1]==="-="?-1:1)*n+m),k.custom(m,n,o)):k.custom(m,h,""))
}return !0})},stop:function(a,b){a&&this.queue([]),this.each(function(){var a=f.timers,c=a.length;b||f._unmark(!0,this);while(c--){a[c].elem===this&&(b&&a[c](!0),a.splice(c,1))
}}),b||this.dequeue();return this}}),f.each({slideDown:cu("show",1),slideUp:cu("hide",1),slideToggle:cu("toggle",1),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(a,b){f.fn[a]=function(a,c,d){return this.animate(b,a,c,d)
}}),f.extend({speed:function(a,b,c){var d=a&&typeof a=="object"?f.extend({},a):{complete:c||!c&&b||f.isFunction(a)&&a,duration:a,easing:c&&b||b&&!f.isFunction(b)&&b};
d.duration=f.fx.off?0:typeof d.duration=="number"?d.duration:d.duration in f.fx.speeds?f.fx.speeds[d.duration]:f.fx.speeds._default,d.old=d.complete,d.complete=function(a){d.queue!==!1?f.dequeue(this):a!==!1&&f._unmark(this),f.isFunction(d.old)&&d.old.call(this)
};return d},easing:{linear:function(a,b,c,d){return c+d*a},swing:function(a,b,c,d){return(-Math.cos(a*Math.PI)/2+0.5)*d+c
}},timers:[],fx:function(a,b,c){this.options=b,this.elem=a,this.prop=c,b.orig=b.orig||{}}}),f.fx.prototype={update:function(){this.options.step&&this.options.step.call(this.elem,this.now,this),(f.fx.step[this.prop]||f.fx.step._default)(this)
},cur:function(){if(this.elem[this.prop]!=null&&(!this.elem.style||this.elem.style[this.prop]==null)){return this.elem[this.prop]
}var a,b=f.css(this.elem,this.prop);return isNaN(a=parseFloat(b))?!b||b==="auto"?0:b:a},custom:function(a,b,c){function h(a){return d.step(a)
}var d=this,e=f.fx,g;this.startTime=cq||cs(),this.start=a,this.end=b,this.unit=c||this.unit||(f.cssNumber[this.prop]?"":"px"),this.now=this.start,this.pos=this.state=0,h.elem=this.elem,h()&&f.timers.push(h)&&!co&&(cr?(co=1,g=function(){co&&(cr(g),e.tick())
},cr(g)):co=setInterval(e.tick,e.interval))},show:function(){this.options.orig[this.prop]=f.style(this.elem,this.prop),this.options.show=!0,this.custom(this.prop==="width"||this.prop==="height"?1:0,this.cur()),f(this.elem).show()
},hide:function(){this.options.orig[this.prop]=f.style(this.elem,this.prop),this.options.hide=!0,this.custom(this.cur(),0)
},step:function(a){var b=cq||cs(),c=!0,d=this.elem,e=this.options,g,h;if(a||b>=e.duration+this.startTime){this.now=this.end,this.pos=this.state=1,this.update(),e.animatedProperties[this.prop]=!0;
for(g in e.animatedProperties){e.animatedProperties[g]!==!0&&(c=!1)}if(c){e.overflow!=null&&!f.support.shrinkWrapBlocks&&f.each(["","X","Y"],function(a,b){d.style["overflow"+b]=e.overflow[a]
}),e.hide&&f(d).hide();if(e.hide||e.show){for(var i in e.animatedProperties){f.style(d,i,e.orig[i])}}e.complete.call(d)}return !1
}e.duration==Infinity?this.now=b:(h=b-this.startTime,this.state=h/e.duration,this.pos=f.easing[e.animatedProperties[this.prop]](this.state,h,0,1,e.duration),this.now=this.start+(this.end-this.start)*this.pos),this.update();
return !0}},f.extend(f.fx,{tick:function(){for(var a=f.timers,b=0;b<a.length;++b){a[b]()||a.splice(b--,1)}a.length||f.fx.stop()
},interval:13,stop:function(){clearInterval(co),co=null},speeds:{slow:600,fast:200,_default:400},step:{opacity:function(a){f.style(a.elem,"opacity",a.now)
},_default:function(a){a.elem.style&&a.elem.style[a.prop]!=null?a.elem.style[a.prop]=(a.prop==="width"||a.prop==="height"?Math.max(0,a.now):a.now)+a.unit:a.elem[a.prop]=a.now
}}}),f.expr&&f.expr.filters&&(f.expr.filters.animated=function(a){return f.grep(f.timers,function(b){return a===b.elem}).length
});var cw=/^t(?:able|d|h)$/i,cx=/^(?:body|html)$/i;"getBoundingClientRect" in c.documentElement?f.fn.offset=function(a){var b=this[0],c;
if(a){return this.each(function(b){f.offset.setOffset(this,a,b)})}if(!b||!b.ownerDocument){return null}if(b===b.ownerDocument.body){return f.offset.bodyOffset(b)
}try{c=b.getBoundingClientRect()}catch(d){}var e=b.ownerDocument,g=e.documentElement;if(!c||!f.contains(g,b)){return c?{top:c.top,left:c.left}:{top:0,left:0}
}var h=e.body,i=cy(e),j=g.clientTop||h.clientTop||0,k=g.clientLeft||h.clientLeft||0,l=i.pageYOffset||f.support.boxModel&&g.scrollTop||h.scrollTop,m=i.pageXOffset||f.support.boxModel&&g.scrollLeft||h.scrollLeft,n=c.top+l-j,o=c.left+m-k;
return{top:n,left:o}}:f.fn.offset=function(a){var b=this[0];if(a){return this.each(function(b){f.offset.setOffset(this,a,b)
})}if(!b||!b.ownerDocument){return null}if(b===b.ownerDocument.body){return f.offset.bodyOffset(b)}f.offset.initialize();
var c,d=b.offsetParent,e=b,g=b.ownerDocument,h=g.documentElement,i=g.body,j=g.defaultView,k=j?j.getComputedStyle(b,null):b.currentStyle,l=b.offsetTop,m=b.offsetLeft;
while((b=b.parentNode)&&b!==i&&b!==h){if(f.offset.supportsFixedPosition&&k.position==="fixed"){break}c=j?j.getComputedStyle(b,null):b.currentStyle,l-=b.scrollTop,m-=b.scrollLeft,b===d&&(l+=b.offsetTop,m+=b.offsetLeft,f.offset.doesNotAddBorder&&(!f.offset.doesAddBorderForTableAndCells||!cw.test(b.nodeName))&&(l+=parseFloat(c.borderTopWidth)||0,m+=parseFloat(c.borderLeftWidth)||0),e=d,d=b.offsetParent),f.offset.subtractsBorderForOverflowNotVisible&&c.overflow!=="visible"&&(l+=parseFloat(c.borderTopWidth)||0,m+=parseFloat(c.borderLeftWidth)||0),k=c
}if(k.position==="relative"||k.position==="static"){l+=i.offsetTop,m+=i.offsetLeft}f.offset.supportsFixedPosition&&k.position==="fixed"&&(l+=Math.max(h.scrollTop,i.scrollTop),m+=Math.max(h.scrollLeft,i.scrollLeft));
return{top:l,left:m}},f.offset={initialize:function(){var a=c.body,b=c.createElement("div"),d,e,g,h,i=parseFloat(f.css(a,"marginTop"))||0,j="<div style='position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;'><div></div></div><table style='position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;' cellpadding='0' cellspacing='0'><tr><td></td></tr></table>";
f.extend(b.style,{position:"absolute",top:0,left:0,margin:0,border:0,width:"1px",height:"1px",visibility:"hidden"}),b.innerHTML=j,a.insertBefore(b,a.firstChild),d=b.firstChild,e=d.firstChild,h=d.nextSibling.firstChild.firstChild,this.doesNotAddBorder=e.offsetTop!==5,this.doesAddBorderForTableAndCells=h.offsetTop===5,e.style.position="fixed",e.style.top="20px",this.supportsFixedPosition=e.offsetTop===20||e.offsetTop===15,e.style.position=e.style.top="",d.style.overflow="hidden",d.style.position="relative",this.subtractsBorderForOverflowNotVisible=e.offsetTop===-5,this.doesNotIncludeMarginInBodyOffset=a.offsetTop!==i,a.removeChild(b),f.offset.initialize=f.noop
},bodyOffset:function(a){var b=a.offsetTop,c=a.offsetLeft;f.offset.initialize(),f.offset.doesNotIncludeMarginInBodyOffset&&(b+=parseFloat(f.css(a,"marginTop"))||0,c+=parseFloat(f.css(a,"marginLeft"))||0);
return{top:b,left:c}},setOffset:function(a,b,c){var d=f.css(a,"position");d==="static"&&(a.style.position="relative");var e=f(a),g=e.offset(),h=f.css(a,"top"),i=f.css(a,"left"),j=(d==="absolute"||d==="fixed")&&f.inArray("auto",[h,i])>-1,k={},l={},m,n;
j?(l=e.position(),m=l.top,n=l.left):(m=parseFloat(h)||0,n=parseFloat(i)||0),f.isFunction(b)&&(b=b.call(a,c,g)),b.top!=null&&(k.top=b.top-g.top+m),b.left!=null&&(k.left=b.left-g.left+n),"using" in b?b.using.call(a,k):e.css(k)
}},f.fn.extend({position:function(){if(!this[0]){return null}var a=this[0],b=this.offsetParent(),c=this.offset(),d=cx.test(b[0].nodeName)?{top:0,left:0}:b.offset();
c.top-=parseFloat(f.css(a,"marginTop"))||0,c.left-=parseFloat(f.css(a,"marginLeft"))||0,d.top+=parseFloat(f.css(b[0],"borderTopWidth"))||0,d.left+=parseFloat(f.css(b[0],"borderLeftWidth"))||0;
return{top:c.top-d.top,left:c.left-d.left}},offsetParent:function(){return this.map(function(){var a=this.offsetParent||c.body;
while(a&&!cx.test(a.nodeName)&&f.css(a,"position")==="static"){a=a.offsetParent}return a})}}),f.each(["Left","Top"],function(a,c){var d="scroll"+c;
f.fn[d]=function(c){var e,g;if(c===b){e=this[0];if(!e){return null}g=cy(e);return g?"pageXOffset" in g?g[a?"pageYOffset":"pageXOffset"]:f.support.boxModel&&g.document.documentElement[d]||g.document.body[d]:e[d]
}return this.each(function(){g=cy(this),g?g.scrollTo(a?f(g).scrollLeft():c,a?c:f(g).scrollTop()):this[d]=c})}}),f.each(["Height","Width"],function(a,c){var d=c.toLowerCase();
f.fn["inner"+c]=function(){return this[0]?parseFloat(f.css(this[0],d,"padding")):null},f.fn["outer"+c]=function(a){return this[0]?parseFloat(f.css(this[0],d,a?"margin":"border")):null
},f.fn[d]=function(a){var e=this[0];if(!e){return a==null?null:this}if(f.isFunction(a)){return this.each(function(b){var c=f(this);
c[d](a.call(this,b,c[d]()))})}if(f.isWindow(e)){var g=e.document.documentElement["client"+c];return e.document.compatMode==="CSS1Compat"&&g||e.document.body["client"+c]||g
}if(e.nodeType===9){return Math.max(e.documentElement["client"+c],e.body["scroll"+c],e.documentElement["scroll"+c],e.body["offset"+c],e.documentElement["offset"+c])
}if(a===b){var h=f.css(e,d),i=parseFloat(h);return f.isNaN(i)?h:i}return this.css(d,typeof a=="string"?a:a+"px")}}),a.jQuery=a.$=f
})(window);(function(b,g){function j(c,d){var a=c.nodeName.toLowerCase();if("area"===a){d=c.parentNode;a=d.name;if(!c.href||!a||d.nodeName.toLowerCase()!=="map"){return false
}c=b("img[usemap=#"+a+"]")[0];return !!c&&e(c)}return(/input|select|textarea|button|object/.test(a)?!c.disabled:"a"==a?c.href||d:d)&&e(c)
}function e(a){return !b(a).parents().andSelf().filter(function(){return b.curCSS(this,"visibility")==="hidden"||b.expr.filters.hidden(this)
}).length}b.ui=b.ui||{};if(!b.ui.version){b.extend(b.ui,{version:"1.8.13",keyCode:{ALT:18,BACKSPACE:8,CAPS_LOCK:20,COMMA:188,COMMAND:91,COMMAND_LEFT:91,COMMAND_RIGHT:93,CONTROL:17,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,INSERT:45,LEFT:37,MENU:93,NUMPAD_ADD:107,NUMPAD_DECIMAL:110,NUMPAD_DIVIDE:111,NUMPAD_ENTER:108,NUMPAD_MULTIPLY:106,NUMPAD_SUBTRACT:109,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190,RIGHT:39,SHIFT:16,SPACE:32,TAB:9,UP:38,WINDOWS:91}});
b.fn.extend({_focus:b.fn.focus,focus:function(a,c){return typeof a==="number"?this.each(function(){var d=this;setTimeout(function(){b(d).focus();
c&&c.call(d)},a)}):this._focus.apply(this,arguments)},scrollParent:function(){var a;a=b.browser.msie&&/(static|relative)/.test(this.css("position"))||/absolute/.test(this.css("position"))?this.parents().filter(function(){return/(relative|absolute|fixed)/.test(b.curCSS(this,"position",1))&&/(auto|scroll)/.test(b.curCSS(this,"overflow",1)+b.curCSS(this,"overflow-y",1)+b.curCSS(this,"overflow-x",1))
}).eq(0):this.parents().filter(function(){return/(auto|scroll)/.test(b.curCSS(this,"overflow",1)+b.curCSS(this,"overflow-y",1)+b.curCSS(this,"overflow-x",1))
}).eq(0);return/fixed/.test(this.css("position"))||!a.length?b(document):a},zIndex:function(a){if(a!==g){return this.css("zIndex",a)
}if(this.length){a=b(this[0]);for(var c;a.length&&a[0]!==document;){c=a.css("position");if(c==="absolute"||c==="relative"||c==="fixed"){c=parseInt(a.css("zIndex"),10);
if(!isNaN(c)&&c!==0){return c}}a=a.parent()}}return 0},disableSelection:function(){return this.bind((b.support.selectstart?"selectstart":"mousedown")+".ui-disableSelection",function(a){a.preventDefault()
})},enableSelection:function(){return this.unbind(".ui-disableSelection")}});b.each(["Width","Height"],function(k,l){function d(m,q,r,p){b.each(a,function(){q-=parseFloat(b.curCSS(m,"padding"+this,true))||0;
if(r){q-=parseFloat(b.curCSS(m,"border"+this+"Width",true))||0}if(p){q-=parseFloat(b.curCSS(m,"margin"+this,true))||0}});
return q}var a=l==="Width"?["Left","Right"]:["Top","Bottom"],f=l.toLowerCase(),c={innerWidth:b.fn.innerWidth,innerHeight:b.fn.innerHeight,outerWidth:b.fn.outerWidth,outerHeight:b.fn.outerHeight};
b.fn["inner"+l]=function(m){if(m===g){return c["inner"+l].call(this)}return this.each(function(){b(this).css(f,d(this,m)+"px")
})};b.fn["outer"+l]=function(m,p){if(typeof m!=="number"){return c["outer"+l].call(this,m)}return this.each(function(){b(this).css(f,d(this,m,true,p)+"px")
})}});b.extend(b.expr[":"],{data:function(c,d,a){return !!b.data(c,a[3])},focusable:function(a){return j(a,!isNaN(b.attr(a,"tabindex")))
},tabbable:function(c){var d=b.attr(c,"tabindex"),a=isNaN(d);return(a||d>=0)&&j(c,!a)}});b(function(){var a=document.body,c=a.appendChild(c=document.createElement("div"));
b.extend(c.style,{minHeight:"100px",height:"auto",padding:0,borderWidth:0});b.support.minHeight=c.offsetHeight===100;b.support.selectstart="onselectstart" in c;
a.removeChild(c).style.display="none"});b.extend(b.ui,{plugin:{add:function(d,f,c){d=b.ui[d].prototype;for(var a in c){d.plugins[a]=d.plugins[a]||[];
d.plugins[a].push([f,c[a]])}},call:function(d,f,c){if((f=d.plugins[f])&&d.element[0].parentNode){for(var a=0;a<f.length;a++){d.options[f[a][0]]&&f[a][1].apply(d.element,c)
}}}},contains:function(a,c){return document.compareDocumentPosition?a.compareDocumentPosition(c)&16:a!==c&&a.contains(c)},hasScroll:function(c,d){if(b(c).css("overflow")==="hidden"){return false
}d=d&&d==="left"?"scrollLeft":"scrollTop";var a=false;if(c[d]>0){return true}c[d]=1;a=c[d]>0;c[d]=0;return a},isOverAxis:function(c,d,a){return c>d&&c<d+a
},isOver:function(k,l,d,a,f,c){return b.ui.isOverAxis(k,d,f)&&b.ui.isOverAxis(l,a,c)}})}})(jQuery);(function(b,g){if(b.cleanData){var j=b.cleanData;
b.cleanData=function(c){for(var d=0,a;(a=c[d])!=null;d++){b(a).triggerHandler("remove")}j(c)}}else{var e=b.fn.remove;b.fn.remove=function(a,c){return this.each(function(){if(!c){if(!a||b.filter(a,[this]).length){b("*",this).add([this]).each(function(){b(this).triggerHandler("remove")
})}}return e.call(b(this),a,c)})}}b.widget=function(f,k,c){var a=f.split(".")[0],d;f=f.split(".")[1];d=a+"-"+f;if(!c){c=k;
k=b.Widget}b.expr[":"][d]=function(l){return !!b.data(l,f)};b[a]=b[a]||{};b[a][f]=function(p,m){arguments.length&&this._createWidget(p,m)
};k=new k;k.options=b.extend(true,{},k.options);b[a][f].prototype=b.extend(true,k,{namespace:a,widgetName:f,widgetEventPrefix:b[a][f].prototype.widgetEventPrefix||f,widgetBaseClass:d},c);
b.widget.bridge(f,b[a][f])};b.widget.bridge=function(a,c){b.fn[a]=function(k){var d=typeof k==="string",l=Array.prototype.slice.call(arguments,1),f=this;
k=!d&&l.length?b.extend.apply(null,[true,k].concat(l)):k;if(d&&k.charAt(0)==="_"){return f}d?this.each(function(){var m=b.data(this,a),p=m&&b.isFunction(m[k])?m[k].apply(m,l):m;
if(p!==m&&p!==g){f=p;return false}}):this.each(function(){var m=b.data(this,a);m?m.option(k||{})._init():b.data(this,a,new c(k,this))
});return f}};b.Widget=function(a,c){arguments.length&&this._createWidget(a,c)};b.Widget.prototype={widgetName:"widget",widgetEventPrefix:"",options:{disabled:false},_createWidget:function(c,d){b.data(d,this.widgetName,this);
this.element=b(d);this.options=b.extend(true,{},this.options,this._getCreateOptions(),c);var a=this;this.element.bind("remove."+this.widgetName,function(){a.destroy()
});this._create();this._trigger("create");this._init()},_getCreateOptions:function(){return b.metadata&&b.metadata.get(this.element[0])[this.widgetName]
},_create:function(){},_init:function(){},destroy:function(){this.element.unbind("."+this.widgetName).removeData(this.widgetName);
this.widget().unbind("."+this.widgetName).removeAttr("aria-disabled").removeClass(this.widgetBaseClass+"-disabled ui-state-disabled")
},widget:function(){return this.element},option:function(c,d){var a=c;if(arguments.length===0){return b.extend({},this.options)
}if(typeof c==="string"){if(d===g){return this.options[c]}a={};a[c]=d}this._setOptions(a);return this},_setOptions:function(a){var c=this;
b.each(a,function(f,d){c._setOption(f,d)});return this},_setOption:function(a,c){this.options[a]=c;if(a==="disabled"){this.widget()[c?"addClass":"removeClass"](this.widgetBaseClass+"-disabled ui-state-disabled").attr("aria-disabled",c)
}return this},enable:function(){return this._setOption("disabled",false)},disable:function(){return this._setOption("disabled",true)
},_trigger:function(f,k,c){var a=this.options[f];k=b.Event(k);k.type=(f===this.widgetEventPrefix?f:this.widgetEventPrefix+f).toLowerCase();
c=c||{};if(k.originalEvent){f=b.event.props.length;for(var d;f;){d=b.event.props[--f];k[d]=k.originalEvent[d]}}this.element.trigger(k,c);
return !(b.isFunction(a)&&a.call(this.element[0],k,c)===false||k.isDefaultPrevented())}}})(jQuery);(function(b){var c=false;
b(document).mousedown(function(){c=false});b.widget("ui.mouse",{options:{cancel:":input,option",distance:1,delay:0},_mouseInit:function(){var a=this;
this.element.bind("mousedown."+this.widgetName,function(d){return a._mouseDown(d)}).bind("click."+this.widgetName,function(d){if(true===b.data(d.target,a.widgetName+".preventClickEvent")){b.removeData(d.target,a.widgetName+".preventClickEvent");
d.stopImmediatePropagation();return false}});this.started=false},_mouseDestroy:function(){this.element.unbind("."+this.widgetName)
},_mouseDown:function(k){if(!c){this._mouseStarted&&this._mouseUp(k);this._mouseDownEvent=k;var d=this,a=k.which==1,j=typeof this.options.cancel=="string"?b(k.target).parents().add(k.target).filter(this.options.cancel).length:false;
if(!a||j||!this._mouseCapture(k)){return true}this.mouseDelayMet=!this.options.delay;if(!this.mouseDelayMet){this._mouseDelayTimer=setTimeout(function(){d.mouseDelayMet=true
},this.options.delay)}if(this._mouseDistanceMet(k)&&this._mouseDelayMet(k)){this._mouseStarted=this._mouseStart(k)!==false;
if(!this._mouseStarted){k.preventDefault();return true}}true===b.data(k.target,this.widgetName+".preventClickEvent")&&b.removeData(k.target,this.widgetName+".preventClickEvent");
this._mouseMoveDelegate=function(e){return d._mouseMove(e)};this._mouseUpDelegate=function(e){return d._mouseUp(e)};b(document).bind("mousemove."+this.widgetName,this._mouseMoveDelegate).bind("mouseup."+this.widgetName,this._mouseUpDelegate);
k.preventDefault();return c=true}},_mouseMove:function(a){if(b.browser.msie&&!(document.documentMode>=9)&&!a.button){return this._mouseUp(a)
}if(this._mouseStarted){this._mouseDrag(a);return a.preventDefault()}if(this._mouseDistanceMet(a)&&this._mouseDelayMet(a)){(this._mouseStarted=this._mouseStart(this._mouseDownEvent,a)!==false)?this._mouseDrag(a):this._mouseUp(a)
}return !this._mouseStarted},_mouseUp:function(a){b(document).unbind("mousemove."+this.widgetName,this._mouseMoveDelegate).unbind("mouseup."+this.widgetName,this._mouseUpDelegate);
if(this._mouseStarted){this._mouseStarted=false;a.target==this._mouseDownEvent.target&&b.data(a.target,this.widgetName+".preventClickEvent",true);
this._mouseStop(a)}return false},_mouseDistanceMet:function(a){return Math.max(Math.abs(this._mouseDownEvent.pageX-a.pageX),Math.abs(this._mouseDownEvent.pageY-a.pageY))>=this.options.distance
},_mouseDelayMet:function(){return this.mouseDelayMet},_mouseStart:function(){},_mouseDrag:function(){},_mouseStop:function(){},_mouseCapture:function(){return true
}})})(jQuery);(function(b){b.widget("ui.draggable",b.ui.mouse,{widgetEventPrefix:"drag",options:{addClasses:true,appendTo:"parent",axis:false,connectToSortable:false,containment:false,cursor:"auto",cursorAt:false,grid:false,handle:false,helper:"original",iframeFix:false,opacity:false,refreshPositions:false,revert:false,revertDuration:500,scope:"default",scroll:true,scrollSensitivity:20,scrollSpeed:20,snap:false,snapMode:"both",snapTolerance:20,stack:false,zIndex:false},_create:function(){if(this.options.helper=="original"&&!/^(?:r|a|f)/.test(this.element.css("position"))){this.element[0].style.position="relative"
}this.options.addClasses&&this.element.addClass("ui-draggable");this.options.disabled&&this.element.addClass("ui-draggable-disabled");
this._mouseInit()},destroy:function(){if(this.element.data("draggable")){this.element.removeData("draggable").unbind(".draggable").removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled");
this._mouseDestroy();return this}},_mouseCapture:function(a){var e=this.options;if(this.helper||e.disabled||b(a.target).is(".ui-resizable-handle")){return false
}this.handle=this._getHandle(a);if(!this.handle){return false}b(e.iframeFix===true?"iframe":e.iframeFix).each(function(){b('<div class="ui-draggable-iframeFix" style="background: #fff;"></div>').css({width:this.offsetWidth+"px",height:this.offsetHeight+"px",position:"absolute",opacity:"0.001",zIndex:1000}).css(b(this).offset()).appendTo("body")
});return true},_mouseStart:function(a){var e=this.options;this.helper=this._createHelper(a);this._cacheHelperProportions();
if(b.ui.ddmanager){b.ui.ddmanager.current=this}this._cacheMargins();this.cssPosition=this.helper.css("position");this.scrollParent=this.helper.scrollParent();
this.offset=this.positionAbs=this.element.offset();this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left};
b.extend(this.offset,{click:{left:a.pageX-this.offset.left,top:a.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()});
this.originalPosition=this.position=this._generatePosition(a);this.originalPageX=a.pageX;this.originalPageY=a.pageY;e.cursorAt&&this._adjustOffsetFromHelper(e.cursorAt);
e.containment&&this._setContainment();if(this._trigger("start",a)===false){this._clear();return false}this._cacheHelperProportions();
b.ui.ddmanager&&!e.dropBehaviour&&b.ui.ddmanager.prepareOffsets(this,a);this.helper.addClass("ui-draggable-dragging");this._mouseDrag(a,true);
return true},_mouseDrag:function(a,e){this.position=this._generatePosition(a);this.positionAbs=this._convertPositionTo("absolute");
if(!e){e=this._uiHash();if(this._trigger("drag",a,e)===false){this._mouseUp({});return false}this.position=e.position}if(!this.options.axis||this.options.axis!="y"){this.helper[0].style.left=this.position.left+"px"
}if(!this.options.axis||this.options.axis!="x"){this.helper[0].style.top=this.position.top+"px"}b.ui.ddmanager&&b.ui.ddmanager.drag(this,a);
return false},_mouseStop:function(e){var g=false;if(b.ui.ddmanager&&!this.options.dropBehaviour){g=b.ui.ddmanager.drop(this,e)
}if(this.dropped){g=this.dropped;this.dropped=false}if((!this.element[0]||!this.element[0].parentNode)&&this.options.helper=="original"){return false
}if(this.options.revert=="invalid"&&!g||this.options.revert=="valid"&&g||this.options.revert===true||b.isFunction(this.options.revert)&&this.options.revert.call(this.element,g)){var a=this;
b(this.helper).animate(this.originalPosition,parseInt(this.options.revertDuration,10),function(){a._trigger("stop",e)!==false&&a._clear()
})}else{this._trigger("stop",e)!==false&&this._clear()}return false},_mouseUp:function(a){this.options.iframeFix===true&&b("div.ui-draggable-iframeFix").each(function(){this.parentNode.removeChild(this)
});return b.ui.mouse.prototype._mouseUp.call(this,a)},cancel:function(){this.helper.is(".ui-draggable-dragging")?this._mouseUp({}):this._clear();
return this},_getHandle:function(a){var e=!this.options.handle||!b(this.options.handle,this.element).length?true:false;b(this.options.handle,this.element).find("*").andSelf().each(function(){if(this==a.target){e=true
}});return e},_createHelper:function(a){var e=this.options;a=b.isFunction(e.helper)?b(e.helper.apply(this.element[0],[a])):e.helper=="clone"?this.element.clone().removeAttr("id"):this.element;
a.parents("body").length||a.appendTo(e.appendTo=="parent"?this.element[0].parentNode:e.appendTo);a[0]!=this.element[0]&&!/(fixed|absolute)/.test(a.css("position"))&&a.css("position","absolute");
return a},_adjustOffsetFromHelper:function(a){if(typeof a=="string"){a=a.split(" ")}if(b.isArray(a)){a={left:+a[0],top:+a[1]||0}
}if("left" in a){this.offset.click.left=a.left+this.margins.left}if("right" in a){this.offset.click.left=this.helperProportions.width-a.right+this.margins.left
}if("top" in a){this.offset.click.top=a.top+this.margins.top}if("bottom" in a){this.offset.click.top=this.helperProportions.height-a.bottom+this.margins.top
}},_getParentOffset:function(){this.offsetParent=this.helper.offsetParent();var a=this.offsetParent.offset();if(this.cssPosition=="absolute"&&this.scrollParent[0]!=document&&b.ui.contains(this.scrollParent[0],this.offsetParent[0])){a.left+=this.scrollParent.scrollLeft();
a.top+=this.scrollParent.scrollTop()}if(this.offsetParent[0]==document.body||this.offsetParent[0].tagName&&this.offsetParent[0].tagName.toLowerCase()=="html"&&b.browser.msie){a={top:0,left:0}
}return{top:a.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:a.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}
},_getRelativeOffset:function(){if(this.cssPosition=="relative"){var a=this.element.position();return{top:a.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:a.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}
}else{return{top:0,left:0}}},_cacheMargins:function(){this.margins={left:parseInt(this.element.css("marginLeft"),10)||0,top:parseInt(this.element.css("marginTop"),10)||0,right:parseInt(this.element.css("marginRight"),10)||0,bottom:parseInt(this.element.css("marginBottom"),10)||0}
},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}
},_setContainment:function(){var e=this.options;if(e.containment=="parent"){e.containment=this.helper[0].parentNode}if(e.containment=="document"||e.containment=="window"){this.containment=[(e.containment=="document"?0:b(window).scrollLeft())-this.offset.relative.left-this.offset.parent.left,(e.containment=="document"?0:b(window).scrollTop())-this.offset.relative.top-this.offset.parent.top,(e.containment=="document"?0:b(window).scrollLeft())+b(e.containment=="document"?document:window).width()-this.helperProportions.width-this.margins.left,(e.containment=="document"?0:b(window).scrollTop())+(b(e.containment=="document"?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top]
}if(!/^(document|window|parent)$/.test(e.containment)&&e.containment.constructor!=Array){e=b(e.containment);var g=e[0];if(g){e.offset();
var a=b(g).css("overflow")!="hidden";this.containment=[(parseInt(b(g).css("borderLeftWidth"),10)||0)+(parseInt(b(g).css("paddingLeft"),10)||0),(parseInt(b(g).css("borderTopWidth"),10)||0)+(parseInt(b(g).css("paddingTop"),10)||0),(a?Math.max(g.scrollWidth,g.offsetWidth):g.offsetWidth)-(parseInt(b(g).css("borderLeftWidth"),10)||0)-(parseInt(b(g).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left-this.margins.right,(a?Math.max(g.scrollHeight,g.offsetHeight):g.offsetHeight)-(parseInt(b(g).css("borderTopWidth"),10)||0)-(parseInt(b(g).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top-this.margins.bottom];
this.relative_container=e}}else{if(e.containment.constructor==Array){this.containment=e.containment}}},_convertPositionTo:function(j,k){if(!k){k=this.position
}j=j=="absolute"?1:-1;var e=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&b.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,a=/(html|body)/i.test(e[0].tagName);
return{top:k.top+this.offset.relative.top*j+this.offset.parent.top*j-(b.browser.safari&&b.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():a?0:e.scrollTop())*j),left:k.left+this.offset.relative.left*j+this.offset.parent.left*j-(b.browser.safari&&b.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():a?0:e.scrollLeft())*j)}
},_generatePosition:function(p){var q=this.options,l=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&b.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,k=/(html|body)/i.test(l[0].tagName),m=p.pageX,j=p.pageY;
if(this.originalPosition){var a;if(this.containment){if(this.relative_container){a=this.relative_container.offset();a=[this.containment[0]+a.left,this.containment[1]+a.top,this.containment[2]+a.left,this.containment[3]+a.top]
}else{a=this.containment}if(p.pageX-this.offset.click.left<a[0]){m=a[0]+this.offset.click.left}if(p.pageY-this.offset.click.top<a[1]){j=a[1]+this.offset.click.top
}if(p.pageX-this.offset.click.left>a[2]){m=a[2]+this.offset.click.left}if(p.pageY-this.offset.click.top>a[3]){j=a[3]+this.offset.click.top
}}if(q.grid){j=this.originalPageY+Math.round((j-this.originalPageY)/q.grid[1])*q.grid[1];j=a?!(j-this.offset.click.top<a[1]||j-this.offset.click.top>a[3])?j:!(j-this.offset.click.top<a[1])?j-q.grid[1]:j+q.grid[1]:j;
m=this.originalPageX+Math.round((m-this.originalPageX)/q.grid[0])*q.grid[0];m=a?!(m-this.offset.click.left<a[0]||m-this.offset.click.left>a[2])?m:!(m-this.offset.click.left<a[0])?m-q.grid[0]:m+q.grid[0]:m
}}return{top:j-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+(b.browser.safari&&b.browser.version<526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollTop():k?0:l.scrollTop()),left:m-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(b.browser.safari&&b.browser.version<526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():k?0:l.scrollLeft())}
},_clear:function(){this.helper.removeClass("ui-draggable-dragging");this.helper[0]!=this.element[0]&&!this.cancelHelperRemoval&&this.helper.remove();
this.helper=null;this.cancelHelperRemoval=false},_trigger:function(e,g,a){a=a||this._uiHash();b.ui.plugin.call(this,e,[g,a]);
if(e=="drag"){this.positionAbs=this._convertPositionTo("absolute")}return b.Widget.prototype._trigger.call(this,e,g,a)},plugins:{},_uiHash:function(){return{helper:this.helper,position:this.position,originalPosition:this.originalPosition,offset:this.positionAbs}
}});b.extend(b.ui.draggable,{version:"1.8.13"});b.ui.plugin.add("draggable","connectToSortable",{start:function(l,m){var j=b(this).data("draggable"),a=j.options,k=b.extend({},m,{item:j.element});
j.sortables=[];b(a.connectToSortable).each(function(){var c=b.data(this,"sortable");if(c&&!c.options.disabled){j.sortables.push({instance:c,shouldRevert:c.options.revert});
c.refreshPositions();c._trigger("activate",l,k)}})},stop:function(j,k){var e=b(this).data("draggable"),a=b.extend({},k,{item:e.element});
b.each(e.sortables,function(){if(this.instance.isOver){this.instance.isOver=0;e.cancelHelperRemoval=true;this.instance.cancelHelperRemoval=false;
if(this.shouldRevert){this.instance.options.revert=true}this.instance._mouseStop(j);this.instance.options.helper=this.instance.options._helper;
e.options.helper=="original"&&this.instance.currentItem.css({top:"auto",left:"auto"})}else{this.instance.cancelHelperRemoval=false;
this.instance._trigger("deactivate",j,a)}})},drag:function(j,k){var e=b(this).data("draggable"),a=this;b.each(e.sortables,function(){this.instance.positionAbs=e.positionAbs;
this.instance.helperProportions=e.helperProportions;this.instance.offset.click=e.offset.click;if(this.instance._intersectsWith(this.instance.containerCache)){if(!this.instance.isOver){this.instance.isOver=1;
this.instance.currentItem=b(a).clone().removeAttr("id").appendTo(this.instance.element).data("sortable-item",true);this.instance.options._helper=this.instance.options.helper;
this.instance.options.helper=function(){return k.helper[0]};j.target=this.instance.currentItem[0];this.instance._mouseCapture(j,true);
this.instance._mouseStart(j,true,true);this.instance.offset.click.top=e.offset.click.top;this.instance.offset.click.left=e.offset.click.left;
this.instance.offset.parent.left-=e.offset.parent.left-this.instance.offset.parent.left;this.instance.offset.parent.top-=e.offset.parent.top-this.instance.offset.parent.top;
e._trigger("toSortable",j);e.dropped=this.instance.element;e.currentItem=e.element;this.instance.fromOutside=e}this.instance.currentItem&&this.instance._mouseDrag(j)
}else{if(this.instance.isOver){this.instance.isOver=0;this.instance.cancelHelperRemoval=true;this.instance.options.revert=false;
this.instance._trigger("out",j,this.instance._uiHash(this.instance));this.instance._mouseStop(j,true);this.instance.options.helper=this.instance.options._helper;
this.instance.currentItem.remove();this.instance.placeholder&&this.instance.placeholder.remove();e._trigger("fromSortable",j);
e.dropped=false}}})}});b.ui.plugin.add("draggable","cursor",{start:function(){var a=b("body"),e=b(this).data("draggable").options;
if(a.css("cursor")){e._cursor=a.css("cursor")}a.css("cursor",e.cursor)},stop:function(){var a=b(this).data("draggable").options;
a._cursor&&b("body").css("cursor",a._cursor)}});b.ui.plugin.add("draggable","opacity",{start:function(a,e){a=b(e.helper);
e=b(this).data("draggable").options;if(a.css("opacity")){e._opacity=a.css("opacity")}a.css("opacity",e.opacity)},stop:function(a,e){a=b(this).data("draggable").options;
a._opacity&&b(e.helper).css("opacity",a._opacity)}});b.ui.plugin.add("draggable","scroll",{start:function(){var a=b(this).data("draggable");
if(a.scrollParent[0]!=document&&a.scrollParent[0].tagName!="HTML"){a.overflowOffset=a.scrollParent.offset()}},drag:function(j){var k=b(this).data("draggable"),e=k.options,a=false;
if(k.scrollParent[0]!=document&&k.scrollParent[0].tagName!="HTML"){if(!e.axis||e.axis!="x"){if(k.overflowOffset.top+k.scrollParent[0].offsetHeight-j.pageY<e.scrollSensitivity){k.scrollParent[0].scrollTop=a=k.scrollParent[0].scrollTop+e.scrollSpeed
}else{if(j.pageY-k.overflowOffset.top<e.scrollSensitivity){k.scrollParent[0].scrollTop=a=k.scrollParent[0].scrollTop-e.scrollSpeed
}}}if(!e.axis||e.axis!="y"){if(k.overflowOffset.left+k.scrollParent[0].offsetWidth-j.pageX<e.scrollSensitivity){k.scrollParent[0].scrollLeft=a=k.scrollParent[0].scrollLeft+e.scrollSpeed
}else{if(j.pageX-k.overflowOffset.left<e.scrollSensitivity){k.scrollParent[0].scrollLeft=a=k.scrollParent[0].scrollLeft-e.scrollSpeed
}}}}else{if(!e.axis||e.axis!="x"){if(j.pageY-b(document).scrollTop()<e.scrollSensitivity){a=b(document).scrollTop(b(document).scrollTop()-e.scrollSpeed)
}else{if(b(window).height()-(j.pageY-b(document).scrollTop())<e.scrollSensitivity){a=b(document).scrollTop(b(document).scrollTop()+e.scrollSpeed)
}}}if(!e.axis||e.axis!="y"){if(j.pageX-b(document).scrollLeft()<e.scrollSensitivity){a=b(document).scrollLeft(b(document).scrollLeft()-e.scrollSpeed)
}else{if(b(window).width()-(j.pageX-b(document).scrollLeft())<e.scrollSensitivity){a=b(document).scrollLeft(b(document).scrollLeft()+e.scrollSpeed)
}}}}a!==false&&b.ui.ddmanager&&!e.dropBehaviour&&b.ui.ddmanager.prepareOffsets(k,j)}});b.ui.plugin.add("draggable","snap",{start:function(){var a=b(this).data("draggable"),e=a.options;
a.snapElements=[];b(e.snap.constructor!=String?e.snap.items||":data(draggable)":e.snap).each(function(){var d=b(this),c=d.offset();
this!=a.element[0]&&a.snapElements.push({item:this,width:d.outerWidth(),height:d.outerHeight(),top:c.top,left:c.left})})},drag:function(L,M){for(var J=b(this).data("draggable"),I=J.options,K=I.snapTolerance,G=M.offset.left,N=G+J.helperProportions.width,H=M.offset.top,F=H+J.helperProportions.height,D=J.snapElements.length-1;
D>=0;D--){var A=J.snapElements[D].left,B=A+J.snapElements[D].width,E=J.snapElements[D].top,C=E+J.snapElements[D].height;if(A-K<G&&G<B+K&&E-K<H&&H<C+K||A-K<G&&G<B+K&&E-K<F&&F<C+K||A-K<N&&N<B+K&&E-K<H&&H<C+K||A-K<N&&N<B+K&&E-K<F&&F<C+K){if(I.snapMode!="inner"){var z=Math.abs(E-F)<=K,y=Math.abs(C-H)<=K,v=Math.abs(A-N)<=K,x=Math.abs(B-G)<=K;
if(z){M.position.top=J._convertPositionTo("relative",{top:E-J.helperProportions.height,left:0}).top-J.margins.top}if(y){M.position.top=J._convertPositionTo("relative",{top:C,left:0}).top-J.margins.top
}if(v){M.position.left=J._convertPositionTo("relative",{top:0,left:A-J.helperProportions.width}).left-J.margins.left}if(x){M.position.left=J._convertPositionTo("relative",{top:0,left:B}).left-J.margins.left
}}var a=z||y||v||x;if(I.snapMode!="outer"){z=Math.abs(E-H)<=K;y=Math.abs(C-F)<=K;v=Math.abs(A-G)<=K;x=Math.abs(B-N)<=K;if(z){M.position.top=J._convertPositionTo("relative",{top:E,left:0}).top-J.margins.top
}if(y){M.position.top=J._convertPositionTo("relative",{top:C-J.helperProportions.height,left:0}).top-J.margins.top}if(v){M.position.left=J._convertPositionTo("relative",{top:0,left:A}).left-J.margins.left
}if(x){M.position.left=J._convertPositionTo("relative",{top:0,left:B-J.helperProportions.width}).left-J.margins.left}}if(!J.snapElements[D].snapping&&(z||y||v||x||a)){J.options.snap.snap&&J.options.snap.snap.call(J.element,L,b.extend(J._uiHash(),{snapItem:J.snapElements[D].item}))
}J.snapElements[D].snapping=z||y||v||x||a}else{J.snapElements[D].snapping&&J.options.snap.release&&J.options.snap.release.call(J.element,L,b.extend(J._uiHash(),{snapItem:J.snapElements[D].item}));
J.snapElements[D].snapping=false}}}});b.ui.plugin.add("draggable","stack",{start:function(){var a=b(this).data("draggable").options;
a=b.makeArray(b(a.stack)).sort(function(d,c){return(parseInt(b(d).css("zIndex"),10)||0)-(parseInt(b(c).css("zIndex"),10)||0)
});if(a.length){var e=parseInt(a[0].style.zIndex)||0;b(a).each(function(c){this.style.zIndex=e+c});this[0].style.zIndex=e+a.length
}}});b.ui.plugin.add("draggable","zIndex",{start:function(a,e){a=b(e.helper);e=b(this).data("draggable").options;if(a.css("zIndex")){e._zIndex=a.css("zIndex")
}a.css("zIndex",e.zIndex)},stop:function(a,e){a=b(this).data("draggable").options;a._zIndex&&b(e.helper).css("zIndex",a._zIndex)
}})})(jQuery);(function(b){b.widget("ui.droppable",{widgetEventPrefix:"drop",options:{accept:"*",activeClass:false,addClasses:true,greedy:false,hoverClass:false,scope:"default",tolerance:"intersect"},_create:function(){var a=this.options,e=a.accept;
this.isover=0;this.isout=1;this.accept=b.isFunction(e)?e:function(c){return c.is(e)};this.proportions={width:this.element[0].offsetWidth,height:this.element[0].offsetHeight};
b.ui.ddmanager.droppables[a.scope]=b.ui.ddmanager.droppables[a.scope]||[];b.ui.ddmanager.droppables[a.scope].push(this);a.addClasses&&this.element.addClass("ui-droppable")
},destroy:function(){for(var a=b.ui.ddmanager.droppables[this.options.scope],e=0;e<a.length;e++){a[e]==this&&a.splice(e,1)
}this.element.removeClass("ui-droppable ui-droppable-disabled").removeData("droppable").unbind(".droppable");return this},_setOption:function(a,e){if(a=="accept"){this.accept=b.isFunction(e)?e:function(c){return c.is(e)
}}b.Widget.prototype._setOption.apply(this,arguments)},_activate:function(a){var e=b.ui.ddmanager.current;this.options.activeClass&&this.element.addClass(this.options.activeClass);
e&&this._trigger("activate",a,this.ui(e))},_deactivate:function(a){var e=b.ui.ddmanager.current;this.options.activeClass&&this.element.removeClass(this.options.activeClass);
e&&this._trigger("deactivate",a,this.ui(e))},_over:function(a){var e=b.ui.ddmanager.current;if(!(!e||(e.currentItem||e.element)[0]==this.element[0])){if(this.accept.call(this.element[0],e.currentItem||e.element)){this.options.hoverClass&&this.element.addClass(this.options.hoverClass);
this._trigger("over",a,this.ui(e))}}},_out:function(a){var e=b.ui.ddmanager.current;if(!(!e||(e.currentItem||e.element)[0]==this.element[0])){if(this.accept.call(this.element[0],e.currentItem||e.element)){this.options.hoverClass&&this.element.removeClass(this.options.hoverClass);
this._trigger("out",a,this.ui(e))}}},_drop:function(j,k){var e=k||b.ui.ddmanager.current;if(!e||(e.currentItem||e.element)[0]==this.element[0]){return false
}var a=false;this.element.find(":data(droppable)").not(".ui-draggable-dragging").each(function(){var c=b.data(this,"droppable");
if(c.options.greedy&&!c.options.disabled&&c.options.scope==e.options.scope&&c.accept.call(c.element[0],e.currentItem||e.element)&&b.ui.intersect(e,b.extend(c,{offset:c.element.offset()}),c.options.tolerance)){a=true;
return false}});if(a){return false}if(this.accept.call(this.element[0],e.currentItem||e.element)){this.options.activeClass&&this.element.removeClass(this.options.activeClass);
this.options.hoverClass&&this.element.removeClass(this.options.hoverClass);this._trigger("drop",j,this.ui(e));return this.element
}return false},ui:function(a){return{draggable:a.currentItem||a.element,helper:a.helper,position:a.position,offset:a.positionAbs}
}});b.extend(b.ui.droppable,{version:"1.8.13"});b.ui.intersect=function(x,y,u){if(!y.offset){return false}var r=(x.positionAbs||x.position.absolute).left,v=r+x.helperProportions.width,p=(x.positionAbs||x.position.absolute).top,z=p+x.helperProportions.height,q=y.offset.left,m=q+y.proportions.width,k=y.offset.top,a=k+y.proportions.height;
switch(u){case"fit":return q<=r&&v<=m&&k<=p&&z<=a;case"intersect":return q<r+x.helperProportions.width/2&&v-x.helperProportions.width/2<m&&k<p+x.helperProportions.height/2&&z-x.helperProportions.height/2<a;
case"pointer":return b.ui.isOver((x.positionAbs||x.position.absolute).top+(x.clickOffset||x.offset.click).top,(x.positionAbs||x.position.absolute).left+(x.clickOffset||x.offset.click).left,k,q,y.proportions.height,y.proportions.width);
case"touch":return(p>=k&&p<=a||z>=k&&z<=a||p<k&&z>a)&&(r>=q&&r<=m||v>=q&&v<=m||r<q&&v>m);default:return false}};b.ui.ddmanager={current:null,droppables:{"default":[]},prepareOffsets:function(p,q){var l=b.ui.ddmanager.droppables[p.options.scope]||[],k=q?q.type:null,m=(p.currentItem||p.element).find(":data(droppable)").andSelf(),j=0;
b:for(;j<l.length;j++){if(!(l[j].options.disabled||p&&!l[j].accept.call(l[j].element[0],p.currentItem||p.element))){for(var a=0;
a<m.length;a++){if(m[a]==l[j].element[0]){l[j].proportions.height=0;continue b}}l[j].visible=l[j].element.css("display")!="none";
if(l[j].visible){k=="mousedown"&&l[j]._activate.call(l[j],q);l[j].offset=l[j].element.offset();l[j].proportions={width:l[j].element[0].offsetWidth,height:l[j].element[0].offsetHeight}
}}}},drop:function(e,g){var a=false;b.each(b.ui.ddmanager.droppables[e.options.scope]||[],function(){if(this.options){if(!this.options.disabled&&this.visible&&b.ui.intersect(e,this,this.options.tolerance)){a=a||this._drop.call(this,g)
}if(!this.options.disabled&&this.visible&&this.accept.call(this.element[0],e.currentItem||e.element)){this.isout=1;this.isover=0;
this._deactivate.call(this,g)}}});return a},drag:function(a,e){a.options.refreshPositions&&b.ui.ddmanager.prepareOffsets(a,e);
b.each(b.ui.ddmanager.droppables[a.options.scope]||[],function(){if(!(this.options.disabled||this.greedyChild||!this.visible)){var d=b.ui.intersect(a,this,this.options.tolerance);
if(d=!d&&this.isover==1?"isout":d&&this.isover==0?"isover":null){var c;if(this.options.greedy){var j=this.element.parents(":data(droppable):eq(0)");
if(j.length){c=b.data(j[0],"droppable");c.greedyChild=d=="isover"?1:0}}if(c&&d=="isover"){c.isover=0;c.isout=1;c._out.call(c,e)
}this[d]=1;this[d=="isout"?"isover":"isout"]=0;this[d=="isover"?"_over":"_out"].call(this,e);if(c&&d=="isout"){c.isout=0;
c.isover=1;c._over.call(c,e)}}}})}}})(jQuery);(function(b){b.widget("ui.resizable",b.ui.mouse,{widgetEventPrefix:"resize",options:{alsoResize:false,animate:false,animateDuration:"slow",animateEasing:"swing",aspectRatio:false,autoHide:false,containment:false,ghost:false,grid:false,handles:"e,s,se",helper:false,maxHeight:null,maxWidth:null,minHeight:10,minWidth:10,zIndex:1000},_create:function(){var k=this,j=this.options;
this.element.addClass("ui-resizable");b.extend(this,{_aspectRatio:!!j.aspectRatio,aspectRatio:j.aspectRatio,originalElement:this.element,_proportionallyResizeElements:[],_helper:j.helper||j.ghost||j.animate?j.helper||"ui-resizable-helper":null});
if(this.element[0].nodeName.match(/canvas|textarea|input|select|button|img/i)){/relative/.test(this.element.css("position"))&&b.browser.opera&&this.element.css({position:"relative",top:"auto",left:"auto"});
this.element.wrap(b('<div class="ui-wrapper" style="overflow: hidden;"></div>').css({position:this.element.css("position"),width:this.element.outerWidth(),height:this.element.outerHeight(),top:this.element.css("top"),left:this.element.css("left")}));
this.element=this.element.parent().data("resizable",this.element.data("resizable"));this.elementIsWrapper=true;this.element.css({marginLeft:this.originalElement.css("marginLeft"),marginTop:this.originalElement.css("marginTop"),marginRight:this.originalElement.css("marginRight"),marginBottom:this.originalElement.css("marginBottom")});
this.originalElement.css({marginLeft:0,marginTop:0,marginRight:0,marginBottom:0});this.originalResizeStyle=this.originalElement.css("resize");
this.originalElement.css("resize","none");this._proportionallyResizeElements.push(this.originalElement.css({position:"static",zoom:1,display:"block"}));
this.originalElement.css({margin:this.originalElement.css("margin")});this._proportionallyResize()}this.handles=j.handles||(!b(".ui-resizable-handle",this.element).length?"e,s,se":{n:".ui-resizable-n",e:".ui-resizable-e",s:".ui-resizable-s",w:".ui-resizable-w",se:".ui-resizable-se",sw:".ui-resizable-sw",ne:".ui-resizable-ne",nw:".ui-resizable-nw"});
if(this.handles.constructor==String){if(this.handles=="all"){this.handles="n,e,s,w,se,sw,ne,nw"}var l=this.handles.split(",");
this.handles={};for(var c=0;c<l.length;c++){var a=b.trim(l[c]),d=b('<div class="ui-resizable-handle '+("ui-resizable-"+a)+'"></div>');
/sw|se|ne|nw/.test(a)&&d.css({zIndex:++j.zIndex});"se"==a&&d.addClass("ui-icon ui-icon-gripsmall-diagonal-se");this.handles[a]=".ui-resizable-"+a;
this.element.append(d)}}this._renderAxis=function(m){m=m||this.element;for(var g in this.handles){if(this.handles[g].constructor==String){this.handles[g]=b(this.handles[g],this.element).show()
}if(this.elementIsWrapper&&this.originalElement[0].nodeName.match(/textarea|input|select|button/i)){var p=b(this.handles[g],this.element),q=0;
q=/sw|ne|nw|se|n|s/.test(g)?p.outerHeight():p.outerWidth();p=["padding",/ne|nw|n/.test(g)?"Top":/se|sw|s/.test(g)?"Bottom":/^e$/.test(g)?"Right":"Left"].join("");
m.css(p,q);this._proportionallyResize()}b(this.handles[g])}};this._renderAxis(this.element);this._handles=b(".ui-resizable-handle",this.element).disableSelection();
this._handles.mouseover(function(){if(!k.resizing){if(this.className){var g=this.className.match(/ui-resizable-(se|sw|ne|nw|n|e|s|w)/i)
}k.axis=g&&g[1]?g[1]:"se"}});if(j.autoHide){this._handles.hide();b(this.element).addClass("ui-resizable-autohide").hover(function(){if(!j.disabled){b(this).removeClass("ui-resizable-autohide");
k._handles.show()}},function(){if(!j.disabled){if(!k.resizing){b(this).addClass("ui-resizable-autohide");k._handles.hide()
}}})}this._mouseInit()},destroy:function(){this._mouseDestroy();var c=function(d){b(d).removeClass("ui-resizable ui-resizable-disabled ui-resizable-resizing").removeData("resizable").unbind(".resizable").find(".ui-resizable-handle").remove()
};if(this.elementIsWrapper){c(this.element);var a=this.element;a.after(this.originalElement.css({position:a.css("position"),width:a.outerWidth(),height:a.outerHeight(),top:a.css("top"),left:a.css("left")})).remove()
}this.originalElement.css("resize",this.originalResizeStyle);c(this.originalElement);return this},_mouseCapture:function(c){var a=false;
for(var d in this.handles){if(b(this.handles[d])[0]==c.target){a=true}}return !this.options.disabled&&a},_mouseStart:function(j){var d=this.options,k=this.element.position(),c=this.element;
this.resizing=true;this.documentScroll={top:b(document).scrollTop(),left:b(document).scrollLeft()};if(c.is(".ui-draggable")||/absolute/.test(c.css("position"))){c.css({position:"absolute",top:k.top,left:k.left})
}b.browser.opera&&/relative/.test(c.css("position"))&&c.css({position:"relative",top:"auto",left:"auto"});this._renderProxy();
k=e(this.helper.css("left"));var a=e(this.helper.css("top"));if(d.containment){k+=b(d.containment).scrollLeft()||0;a+=b(d.containment).scrollTop()||0
}this.offset=this.helper.offset();this.position={left:k,top:a};this.size=this._helper?{width:c.outerWidth(),height:c.outerHeight()}:{width:c.width(),height:c.height()};
this.originalSize=this._helper?{width:c.outerWidth(),height:c.outerHeight()}:{width:c.width(),height:c.height()};this.originalPosition={left:k,top:a};
this.sizeDiff={width:c.outerWidth()-c.width(),height:c.outerHeight()-c.height()};this.originalMousePosition={left:j.pageX,top:j.pageY};
this.aspectRatio=typeof d.aspectRatio=="number"?d.aspectRatio:this.originalSize.width/this.originalSize.height||1;d=b(".ui-resizable-"+this.axis).css("cursor");
b("body").css("cursor",d=="auto"?this.axis+"-resize":d);c.addClass("ui-resizable-resizing");this._propagate("start",j);return true
},_mouseDrag:function(d){var c=this.helper,j=this.originalMousePosition,a=this._change[this.axis];if(!a){return false}j=a.apply(this,[d,d.pageX-j.left||0,d.pageY-j.top||0]);
if(this._aspectRatio||d.shiftKey){j=this._updateRatio(j,d)}j=this._respectSize(j,d);this._propagate("resize",d);c.css({top:this.position.top+"px",left:this.position.left+"px",width:this.size.width+"px",height:this.size.height+"px"});
!this._helper&&this._proportionallyResizeElements.length&&this._proportionallyResize();this._updateCache(j);this._trigger("resize",d,this.ui());
return false},_mouseStop:function(k){this.resizing=false;var j=this.options,l=this;if(this._helper){var c=this._proportionallyResizeElements,a=c.length&&/textarea/i.test(c[0].nodeName);
c=a&&b.ui.hasScroll(c[0],"left")?0:l.sizeDiff.height;a=a?0:l.sizeDiff.width;a={width:l.helper.width()-a,height:l.helper.height()-c};
c=parseInt(l.element.css("left"),10)+(l.position.left-l.originalPosition.left)||null;var d=parseInt(l.element.css("top"),10)+(l.position.top-l.originalPosition.top)||null;
j.animate||this.element.css(b.extend(a,{top:d,left:c}));l.helper.height(l.size.height);l.helper.width(l.size.width);this._helper&&!j.animate&&this._proportionallyResize()
}b("body").css("cursor","auto");this.element.removeClass("ui-resizable-resizing");this._propagate("stop",k);this._helper&&this.helper.remove();
return false},_updateCache:function(a){this.offset=this.helper.offset();if(f(a.left)){this.position.left=a.left}if(f(a.top)){this.position.top=a.top
}if(f(a.height)){this.size.height=a.height}if(f(a.width)){this.size.width=a.width}},_updateRatio:function(d){var c=this.position,j=this.size,a=this.axis;
if(d.height){d.width=j.height*this.aspectRatio}else{if(d.width){d.height=j.width/this.aspectRatio}}if(a=="sw"){d.left=c.left+(j.width-d.width);
d.top=null}if(a=="nw"){d.top=c.top+(j.height-d.height);d.left=c.left+(j.width-d.width)}return d},_respectSize:function(r){var q=this.options,u=this.axis,m=f(r.width)&&q.maxWidth&&q.maxWidth<r.width,v=f(r.height)&&q.maxHeight&&q.maxHeight<r.height,p=f(r.width)&&q.minWidth&&q.minWidth>r.width,k=f(r.height)&&q.minHeight&&q.minHeight>r.height;
if(p){r.width=q.minWidth}if(k){r.height=q.minHeight}if(m){r.width=q.maxWidth}if(v){r.height=q.maxHeight}var d=this.originalPosition.left+this.originalSize.width,a=this.position.top+this.size.height,c=/sw|nw|w/.test(u);
u=/nw|ne|n/.test(u);if(p&&c){r.left=d-q.minWidth}if(m&&c){r.left=d-q.maxWidth}if(k&&u){r.top=a-q.minHeight}if(v&&u){r.top=a-q.maxHeight
}if((q=!r.width&&!r.height)&&!r.left&&r.top){r.top=null}else{if(q&&!r.top&&r.left){r.left=null}}return r},_proportionallyResize:function(){if(this._proportionallyResizeElements.length){for(var j=this.helper||this.element,d=0;
d<this._proportionallyResizeElements.length;d++){var k=this._proportionallyResizeElements[d];if(!this.borderDif){var c=[k.css("borderTopWidth"),k.css("borderRightWidth"),k.css("borderBottomWidth"),k.css("borderLeftWidth")],a=[k.css("paddingTop"),k.css("paddingRight"),k.css("paddingBottom"),k.css("paddingLeft")];
this.borderDif=b.map(c,function(l,g){l=parseInt(l,10)||0;g=parseInt(a[g],10)||0;return l+g})}b.browser.msie&&(b(j).is(":hidden")||b(j).parents(":hidden").length)||k.css({height:j.height()-this.borderDif[0]-this.borderDif[2]||0,width:j.width()-this.borderDif[1]-this.borderDif[3]||0})
}}},_renderProxy:function(){var c=this.options;this.elementOffset=this.element.offset();if(this._helper){this.helper=this.helper||b('<div style="overflow:hidden;"></div>');
var a=b.browser.msie&&b.browser.version<7,d=a?1:0;a=a?2:-1;this.helper.addClass(this._helper).css({width:this.element.outerWidth()+a,height:this.element.outerHeight()+a,position:"absolute",left:this.elementOffset.left-d+"px",top:this.elementOffset.top-d+"px",zIndex:++c.zIndex});
this.helper.appendTo("body").disableSelection()}else{this.helper=this.element}},_change:{e:function(c,a){return{width:this.originalSize.width+a}
},w:function(c,a){return{left:this.originalPosition.left+a,width:this.originalSize.width-a}},n:function(c,a,d){return{top:this.originalPosition.top+d,height:this.originalSize.height-d}
},s:function(c,a,d){return{height:this.originalSize.height+d}},se:function(c,a,d){return b.extend(this._change.s.apply(this,arguments),this._change.e.apply(this,[c,a,d]))
},sw:function(c,a,d){return b.extend(this._change.s.apply(this,arguments),this._change.w.apply(this,[c,a,d]))},ne:function(c,a,d){return b.extend(this._change.n.apply(this,arguments),this._change.e.apply(this,[c,a,d]))
},nw:function(c,a,d){return b.extend(this._change.n.apply(this,arguments),this._change.w.apply(this,[c,a,d]))}},_propagate:function(c,a){b.ui.plugin.call(this,c,[a,this.ui()]);
c!="resize"&&this._trigger(c,a,this.ui())},plugins:{},ui:function(){return{originalElement:this.originalElement,element:this.element,helper:this.helper,position:this.position,size:this.size,originalSize:this.originalSize,originalPosition:this.originalPosition}
}});b.extend(b.ui.resizable,{version:"1.8.13"});b.ui.plugin.add("resizable","alsoResize",{start:function(){var c=b(this).data("resizable").options,a=function(d){b(d).each(function(){var g=b(this);
g.data("resizable-alsoresize",{width:parseInt(g.width(),10),height:parseInt(g.height(),10),left:parseInt(g.css("left"),10),top:parseInt(g.css("top"),10),position:g.css("position")})
})};if(typeof c.alsoResize=="object"&&!c.alsoResize.parentNode){if(c.alsoResize.length){c.alsoResize=c.alsoResize[0];a(c.alsoResize)
}else{b.each(c.alsoResize,function(d){a(d)})}}else{a(c.alsoResize)}},resize:function(m,l){var p=b(this).data("resizable");
m=p.options;var d=p.originalSize,a=p.originalPosition,k={height:p.size.height-d.height||0,width:p.size.width-d.width||0,top:p.position.top-a.top||0,left:p.position.left-a.left||0},c=function(g,j){b(g).each(function(){var v=b(this),r=b(this).data("resizable-alsoresize"),q={},u=j&&j.length?j:v.parents(l.originalElement[0]).length?["width","height"]:["width","height","top","left"];
b.each(u,function(y,x){if((y=(r[x]||0)+(k[x]||0))&&y>=0){q[x]=y||null}});if(b.browser.opera&&/relative/.test(v.css("position"))){p._revertToRelativePosition=true;
v.css({position:"absolute",top:"auto",left:"auto"})}v.css(q)})};typeof m.alsoResize=="object"&&!m.alsoResize.nodeType?b.each(m.alsoResize,function(g,j){c(g,j)
}):c(m.alsoResize)},stop:function(){var c=b(this).data("resizable"),a=c.options,d=function(g){b(g).each(function(){var j=b(this);
j.css({position:j.data("resizable-alsoresize").position})})};if(c._revertToRelativePosition){c._revertToRelativePosition=false;
typeof a.alsoResize=="object"&&!a.alsoResize.nodeType?b.each(a.alsoResize,function(g){d(g)}):d(a.alsoResize)}b(this).removeData("resizable-alsoresize")
}});b.ui.plugin.add("resizable","animate",{stop:function(m){var l=b(this).data("resizable"),p=l.options,d=l._proportionallyResizeElements,a=d.length&&/textarea/i.test(d[0].nodeName),k=a&&b.ui.hasScroll(d[0],"left")?0:l.sizeDiff.height;
a={width:l.size.width-(a?0:l.sizeDiff.width),height:l.size.height-k};k=parseInt(l.element.css("left"),10)+(l.position.left-l.originalPosition.left)||null;
var c=parseInt(l.element.css("top"),10)+(l.position.top-l.originalPosition.top)||null;l.element.animate(b.extend(a,c&&k?{top:c,left:k}:{}),{duration:p.animateDuration,easing:p.animateEasing,step:function(){var g={width:parseInt(l.element.css("width"),10),height:parseInt(l.element.css("height"),10),top:parseInt(l.element.css("top"),10),left:parseInt(l.element.css("left"),10)};
d&&d.length&&b(d[0]).css({width:g.width,height:g.height});l._updateCache(g);l._propagate("resize",m)}})}});b.ui.plugin.add("resizable","containment",{start:function(){var m=b(this).data("resizable"),l=m.element,p=m.options.containment;
if(l=p instanceof b?p.get(0):/parent/.test(p)?l.parent().get(0):p){m.containerElement=b(l);if(/document/.test(p)||p==document){m.containerOffset={left:0,top:0};
m.containerPosition={left:0,top:0};m.parentData={element:b(document),left:0,top:0,width:b(document).width(),height:b(document).height()||document.body.parentNode.scrollHeight}
}else{var d=b(l),a=[];b(["Top","Right","Left","Bottom"]).each(function(g,j){a[g]=e(d.css("padding"+j))});m.containerOffset=d.offset();
m.containerPosition=d.position();m.containerSize={height:d.innerHeight()-a[3],width:d.innerWidth()-a[1]};p=m.containerOffset;
var k=m.containerSize.height,c=m.containerSize.width;c=b.ui.hasScroll(l,"left")?l.scrollWidth:c;k=b.ui.hasScroll(l)?l.scrollHeight:k;
m.parentData={element:l,left:p.left,top:p.top,width:c,height:k}}}},resize:function(m){var l=b(this).data("resizable"),p=l.options,d=l.containerOffset,a=l.position;
m=l._aspectRatio||m.shiftKey;var k={top:0,left:0},c=l.containerElement;if(c[0]!=document&&/static/.test(c.css("position"))){k=d
}if(a.left<(l._helper?d.left:0)){l.size.width+=l._helper?l.position.left-d.left:l.position.left-k.left;if(m){l.size.height=l.size.width/p.aspectRatio
}l.position.left=p.helper?d.left:0}if(a.top<(l._helper?d.top:0)){l.size.height+=l._helper?l.position.top-d.top:l.position.top;
if(m){l.size.width=l.size.height*p.aspectRatio}l.position.top=l._helper?d.top:0}l.offset.left=l.parentData.left+l.position.left;
l.offset.top=l.parentData.top+l.position.top;p=Math.abs((l._helper?l.offset.left-k.left:l.offset.left-k.left)+l.sizeDiff.width);
d=Math.abs((l._helper?l.offset.top-k.top:l.offset.top-d.top)+l.sizeDiff.height);a=l.containerElement.get(0)==l.element.parent().get(0);
k=/relative|absolute/.test(l.containerElement.css("position"));if(a&&k){p-=l.parentData.left}if(p+l.size.width>=l.parentData.width){l.size.width=l.parentData.width-p;
if(m){l.size.height=l.size.width/l.aspectRatio}}if(d+l.size.height>=l.parentData.height){l.size.height=l.parentData.height-d;
if(m){l.size.width=l.size.height*l.aspectRatio}}},stop:function(){var q=b(this).data("resizable"),p=q.options,r=q.containerOffset,k=q.containerPosition,a=q.containerElement,m=b(q.helper),d=m.offset(),c=m.outerWidth()-q.sizeDiff.width;
m=m.outerHeight()-q.sizeDiff.height;q._helper&&!p.animate&&/relative/.test(a.css("position"))&&b(this).css({left:d.left-k.left-r.left,width:c,height:m});
q._helper&&!p.animate&&/static/.test(a.css("position"))&&b(this).css({left:d.left-k.left-r.left,width:c,height:m})}});b.ui.plugin.add("resizable","ghost",{start:function(){var c=b(this).data("resizable"),a=c.options,d=c.size;
c.ghost=c.originalElement.clone();c.ghost.css({opacity:0.25,display:"block",position:"relative",height:d.height,width:d.width,margin:0,left:0,top:0}).addClass("ui-resizable-ghost").addClass(typeof a.ghost=="string"?a.ghost:"");
c.ghost.appendTo(c.helper)},resize:function(){var a=b(this).data("resizable");a.ghost&&a.ghost.css({position:"relative",height:a.size.height,width:a.size.width})
},stop:function(){var a=b(this).data("resizable");a.ghost&&a.helper&&a.helper.get(0).removeChild(a.ghost.get(0))}});b.ui.plugin.add("resizable","grid",{resize:function(){var m=b(this).data("resizable"),l=m.options,p=m.size,d=m.originalSize,a=m.originalPosition,k=m.axis;
l.grid=typeof l.grid=="number"?[l.grid,l.grid]:l.grid;var c=Math.round((p.width-d.width)/(l.grid[0]||1))*(l.grid[0]||1);l=Math.round((p.height-d.height)/(l.grid[1]||1))*(l.grid[1]||1);
if(/^(se|s|e)$/.test(k)){m.size.width=d.width+c;m.size.height=d.height+l}else{if(/^(ne)$/.test(k)){m.size.width=d.width+c;
m.size.height=d.height+l;m.position.top=a.top-l}else{if(/^(sw)$/.test(k)){m.size.width=d.width+c;m.size.height=d.height+l
}else{m.size.width=d.width+c;m.size.height=d.height+l;m.position.top=a.top-l}m.position.left=a.left-c}}}});var e=function(a){return parseInt(a,10)||0
},f=function(a){return !isNaN(parseInt(a,10))}})(jQuery);(function(b){b.widget("ui.selectable",b.ui.mouse,{options:{appendTo:"body",autoRefresh:true,distance:0,filter:"*",tolerance:"touch"},_create:function(){var a=this;
this.element.addClass("ui-selectable");this.dragged=false;var e;this.refresh=function(){e=b(a.options.filter,a.element[0]);
e.each(function(){var d=b(this),c=d.offset();b.data(this,"selectable-item",{element:this,$element:d,left:c.left,top:c.top,right:c.left+d.outerWidth(),bottom:c.top+d.outerHeight(),startselected:false,selected:d.hasClass("ui-selected"),selecting:d.hasClass("ui-selecting"),unselecting:d.hasClass("ui-unselecting")})
})};this.refresh();this.selectees=e.addClass("ui-selectee");this._mouseInit();this.helper=b("<div class='ui-selectable-helper'></div>")
},destroy:function(){this.selectees.removeClass("ui-selectee").removeData("selectable-item");this.element.removeClass("ui-selectable ui-selectable-disabled").removeData("selectable").unbind(".selectable");
this._mouseDestroy();return this},_mouseStart:function(e){var g=this;this.opos=[e.pageX,e.pageY];if(!this.options.disabled){var a=this.options;
this.selectees=b(a.filter,this.element[0]);this._trigger("start",e);b(a.appendTo).append(this.helper);this.helper.css({left:e.clientX,top:e.clientY,width:0,height:0});
a.autoRefresh&&this.refresh();this.selectees.filter(".ui-selected").each(function(){var c=b.data(this,"selectable-item");
c.startselected=true;if(!e.metaKey){c.$element.removeClass("ui-selected");c.selected=false;c.$element.addClass("ui-unselecting");
c.unselecting=true;g._trigger("unselecting",e,{unselecting:c.element})}});b(e.target).parents().andSelf().each(function(){var c=b.data(this,"selectable-item");
if(c){var d=!e.metaKey||!c.$element.hasClass("ui-selected");c.$element.removeClass(d?"ui-unselecting":"ui-selected").addClass(d?"ui-selecting":"ui-unselecting");
c.unselecting=!d;c.selecting=d;(c.selected=d)?g._trigger("selecting",e,{selecting:c.element}):g._trigger("unselecting",e,{unselecting:c.element});
return false}})}},_mouseDrag:function(q){var r=this;this.dragged=true;if(!this.options.disabled){var m=this.options,l=this.opos[0],p=this.opos[1],j=q.pageX,a=q.pageY;
if(l>j){var k=j;j=l;l=k}if(p>a){k=a;a=p;p=k}this.helper.css({left:l,top:p,width:j-l,height:a-p});this.selectees.each(function(){var d=b.data(this,"selectable-item");
if(!(!d||d.element==r.element[0])){var c=false;if(m.tolerance=="touch"){c=!(d.left>j||d.right<l||d.top>a||d.bottom<p)}else{if(m.tolerance=="fit"){c=d.left>l&&d.right<j&&d.top>p&&d.bottom<a
}}if(c){if(d.selected){d.$element.removeClass("ui-selected");d.selected=false}if(d.unselecting){d.$element.removeClass("ui-unselecting");
d.unselecting=false}if(!d.selecting){d.$element.addClass("ui-selecting");d.selecting=true;r._trigger("selecting",q,{selecting:d.element})
}}else{if(d.selecting){if(q.metaKey&&d.startselected){d.$element.removeClass("ui-selecting");d.selecting=false;d.$element.addClass("ui-selected");
d.selected=true}else{d.$element.removeClass("ui-selecting");d.selecting=false;if(d.startselected){d.$element.addClass("ui-unselecting");
d.unselecting=true}r._trigger("unselecting",q,{unselecting:d.element})}}if(d.selected){if(!q.metaKey&&!d.startselected){d.$element.removeClass("ui-selected");
d.selected=false;d.$element.addClass("ui-unselecting");d.unselecting=true;r._trigger("unselecting",q,{unselecting:d.element})
}}}}});return false}},_mouseStop:function(a){var e=this;this.dragged=false;b(".ui-unselecting",this.element[0]).each(function(){var c=b.data(this,"selectable-item");
c.$element.removeClass("ui-unselecting");c.unselecting=false;c.startselected=false;e._trigger("unselected",a,{unselected:c.element})
});b(".ui-selecting",this.element[0]).each(function(){var c=b.data(this,"selectable-item");c.$element.removeClass("ui-selecting").addClass("ui-selected");
c.selecting=false;c.selected=true;c.startselected=true;e._trigger("selected",a,{selected:c.element})});this._trigger("stop",a);
this.helper.remove();return false}});b.extend(b.ui.selectable,{version:"1.8.13"})})(jQuery);(function(b){b.widget("ui.sortable",b.ui.mouse,{widgetEventPrefix:"sort",options:{appendTo:"parent",axis:false,connectWith:false,containment:false,cursor:"auto",cursorAt:false,dropOnEmpty:true,forcePlaceholderSize:false,forceHelperSize:false,grid:false,handle:false,helper:"original",items:"> *",opacity:false,placeholder:false,revert:false,scroll:true,scrollSensitivity:20,scrollSpeed:20,scope:"default",tolerance:"intersect",zIndex:1000},_create:function(){var a=this.options;
this.containerCache={};this.element.addClass("ui-sortable");this.refresh();this.floating=this.items.length?a.axis==="x"||/left|right/.test(this.items[0].item.css("float"))||/inline|table-cell/.test(this.items[0].item.css("display")):false;
this.offset=this.element.offset();this._mouseInit()},destroy:function(){this.element.removeClass("ui-sortable ui-sortable-disabled").removeData("sortable").unbind(".sortable");
this._mouseDestroy();for(var a=this.items.length-1;a>=0;a--){this.items[a].item.removeData("sortable-item")}return this},_setOption:function(a,e){if(a==="disabled"){this.options[a]=e;
this.widget()[e?"addClass":"removeClass"]("ui-sortable-disabled")}else{b.Widget.prototype._setOption.apply(this,arguments)
}},_mouseCapture:function(l,m){if(this.reverting){return false}if(this.options.disabled||this.options.type=="static"){return false
}this._refreshItems(l);var j=null,a=this;b(l.target).parents().each(function(){if(b.data(this,"sortable-item")==a){j=b(this);
return false}});if(b.data(l.target,"sortable-item")==a){j=b(l.target)}if(!j){return false}if(this.options.handle&&!m){var k=false;
b(this.options.handle,j).find("*").andSelf().each(function(){if(this==l.target){k=true}});if(!k){return false}}this.currentItem=j;
this._removeCurrentsFromItems();return true},_mouseStart:function(j,k,e){k=this.options;var a=this;this.currentContainer=this;
this.refreshPositions();this.helper=this._createHelper(j);this._cacheHelperProportions();this._cacheMargins();this.scrollParent=this.helper.scrollParent();
this.offset=this.currentItem.offset();this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left};
this.helper.css("position","absolute");this.cssPosition=this.helper.css("position");b.extend(this.offset,{click:{left:j.pageX-this.offset.left,top:j.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()});
this.originalPosition=this._generatePosition(j);this.originalPageX=j.pageX;this.originalPageY=j.pageY;k.cursorAt&&this._adjustOffsetFromHelper(k.cursorAt);
this.domPosition={prev:this.currentItem.prev()[0],parent:this.currentItem.parent()[0]};this.helper[0]!=this.currentItem[0]&&this.currentItem.hide();
this._createPlaceholder();k.containment&&this._setContainment();if(k.cursor){if(b("body").css("cursor")){this._storedCursor=b("body").css("cursor")
}b("body").css("cursor",k.cursor)}if(k.opacity){if(this.helper.css("opacity")){this._storedOpacity=this.helper.css("opacity")
}this.helper.css("opacity",k.opacity)}if(k.zIndex){if(this.helper.css("zIndex")){this._storedZIndex=this.helper.css("zIndex")
}this.helper.css("zIndex",k.zIndex)}if(this.scrollParent[0]!=document&&this.scrollParent[0].tagName!="HTML"){this.overflowOffset=this.scrollParent.offset()
}this._trigger("start",j,this._uiHash());this._preserveHelperProportions||this._cacheHelperProportions();if(!e){for(e=this.containers.length-1;
e>=0;e--){this.containers[e]._trigger("activate",j,a._uiHash(this))}}if(b.ui.ddmanager){b.ui.ddmanager.current=this}b.ui.ddmanager&&!k.dropBehaviour&&b.ui.ddmanager.prepareOffsets(this,j);
this.dragging=true;this.helper.addClass("ui-sortable-helper");this._mouseDrag(j);return true},_mouseDrag:function(l){this.position=this._generatePosition(l);
this.positionAbs=this._convertPositionTo("absolute");if(!this.lastPositionAbs){this.lastPositionAbs=this.positionAbs}if(this.options.scroll){var m=this.options,j=false;
if(this.scrollParent[0]!=document&&this.scrollParent[0].tagName!="HTML"){if(this.overflowOffset.top+this.scrollParent[0].offsetHeight-l.pageY<m.scrollSensitivity){this.scrollParent[0].scrollTop=j=this.scrollParent[0].scrollTop+m.scrollSpeed
}else{if(l.pageY-this.overflowOffset.top<m.scrollSensitivity){this.scrollParent[0].scrollTop=j=this.scrollParent[0].scrollTop-m.scrollSpeed
}}if(this.overflowOffset.left+this.scrollParent[0].offsetWidth-l.pageX<m.scrollSensitivity){this.scrollParent[0].scrollLeft=j=this.scrollParent[0].scrollLeft+m.scrollSpeed
}else{if(l.pageX-this.overflowOffset.left<m.scrollSensitivity){this.scrollParent[0].scrollLeft=j=this.scrollParent[0].scrollLeft-m.scrollSpeed
}}}else{if(l.pageY-b(document).scrollTop()<m.scrollSensitivity){j=b(document).scrollTop(b(document).scrollTop()-m.scrollSpeed)
}else{if(b(window).height()-(l.pageY-b(document).scrollTop())<m.scrollSensitivity){j=b(document).scrollTop(b(document).scrollTop()+m.scrollSpeed)
}}if(l.pageX-b(document).scrollLeft()<m.scrollSensitivity){j=b(document).scrollLeft(b(document).scrollLeft()-m.scrollSpeed)
}else{if(b(window).width()-(l.pageX-b(document).scrollLeft())<m.scrollSensitivity){j=b(document).scrollLeft(b(document).scrollLeft()+m.scrollSpeed)
}}}j!==false&&b.ui.ddmanager&&!m.dropBehaviour&&b.ui.ddmanager.prepareOffsets(this,l)}this.positionAbs=this._convertPositionTo("absolute");
if(!this.options.axis||this.options.axis!="y"){this.helper[0].style.left=this.position.left+"px"}if(!this.options.axis||this.options.axis!="x"){this.helper[0].style.top=this.position.top+"px"
}for(m=this.items.length-1;m>=0;m--){j=this.items[m];var a=j.item[0],k=this._intersectsWithPointer(j);if(k){if(a!=this.currentItem[0]&&this.placeholder[k==1?"next":"prev"]()[0]!=a&&!b.ui.contains(this.placeholder[0],a)&&(this.options.type=="semi-dynamic"?!b.ui.contains(this.element[0],a):true)){this.direction=k==1?"down":"up";
if(this.options.tolerance=="pointer"||this._intersectsWithSides(j)){this._rearrange(l,j)}else{break}this._trigger("change",l,this._uiHash());
break}}}this._contactContainers(l);b.ui.ddmanager&&b.ui.ddmanager.drag(this,l);this._trigger("sort",l,this._uiHash());this.lastPositionAbs=this.positionAbs;
return false},_mouseStop:function(e,g){if(e){b.ui.ddmanager&&!this.options.dropBehaviour&&b.ui.ddmanager.drop(this,e);if(this.options.revert){var a=this;
g=a.placeholder.offset();a.reverting=true;b(this.helper).animate({left:g.left-this.offset.parent.left-a.margins.left+(this.offsetParent[0]==document.body?0:this.offsetParent[0].scrollLeft),top:g.top-this.offset.parent.top-a.margins.top+(this.offsetParent[0]==document.body?0:this.offsetParent[0].scrollTop)},parseInt(this.options.revert,10)||500,function(){a._clear(e)
})}else{this._clear(e,g)}return false}},cancel:function(){var a=this;if(this.dragging){this._mouseUp({target:null});this.options.helper=="original"?this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper"):this.currentItem.show();
for(var e=this.containers.length-1;e>=0;e--){this.containers[e]._trigger("deactivate",null,a._uiHash(this));if(this.containers[e].containerCache.over){this.containers[e]._trigger("out",null,a._uiHash(this));
this.containers[e].containerCache.over=0}}}if(this.placeholder){this.placeholder[0].parentNode&&this.placeholder[0].parentNode.removeChild(this.placeholder[0]);
this.options.helper!="original"&&this.helper&&this.helper[0].parentNode&&this.helper.remove();b.extend(this,{helper:null,dragging:false,reverting:false,_noFinalSort:null});
this.domPosition.prev?b(this.domPosition.prev).after(this.currentItem):b(this.domPosition.parent).prepend(this.currentItem)
}return this},serialize:function(e){var g=this._getItemsAsjQuery(e&&e.connected),a=[];e=e||{};b(g).each(function(){var c=(b(e.item||this).attr(e.attribute||"id")||"").match(e.expression||/(.+)[-=_](.+)/);
if(c){a.push((e.key||c[1]+"[]")+"="+(e.key&&e.expression?c[1]:c[2]))}});!a.length&&e.key&&a.push(e.key+"=");return a.join("&")
},toArray:function(e){var g=this._getItemsAsjQuery(e&&e.connected),a=[];e=e||{};g.each(function(){a.push(b(e.item||this).attr(e.attribute||"id")||"")
});return a},_intersectsWith:function(x){var y=this.positionAbs.left,u=y+this.helperProportions.width,r=this.positionAbs.top,v=r+this.helperProportions.height,p=x.left,z=p+x.width,q=x.top,m=q+x.height,k=this.offset.click.top,a=this.offset.click.left;
k=r+k>q&&r+k<m&&y+a>p&&y+a<z;return this.options.tolerance=="pointer"||this.options.forcePointerForContainers||this.options.tolerance!="pointer"&&this.helperProportions[this.floating?"width":"height"]>x[this.floating?"width":"height"]?k:p<y+this.helperProportions.width/2&&u-this.helperProportions.width/2<z&&q<r+this.helperProportions.height/2&&v-this.helperProportions.height/2<m
},_intersectsWithPointer:function(e){var g=b.ui.isOverAxis(this.positionAbs.top+this.offset.click.top,e.top,e.height);e=b.ui.isOverAxis(this.positionAbs.left+this.offset.click.left,e.left,e.width);
g=g&&e;e=this._getDragVerticalDirection();var a=this._getDragHorizontalDirection();if(!g){return false}return this.floating?a&&a=="right"||e=="down"?2:1:e&&(e=="down"?2:1)
},_intersectsWithSides:function(j){var k=b.ui.isOverAxis(this.positionAbs.top+this.offset.click.top,j.top+j.height/2,j.height);
j=b.ui.isOverAxis(this.positionAbs.left+this.offset.click.left,j.left+j.width/2,j.width);var e=this._getDragVerticalDirection(),a=this._getDragHorizontalDirection();
return this.floating&&a?a=="right"&&j||a=="left"&&!j:e&&(e=="down"&&k||e=="up"&&!k)},_getDragVerticalDirection:function(){var a=this.positionAbs.top-this.lastPositionAbs.top;
return a!=0&&(a>0?"down":"up")},_getDragHorizontalDirection:function(){var a=this.positionAbs.left-this.lastPositionAbs.left;
return a!=0&&(a>0?"right":"left")},refresh:function(a){this._refreshItems(a);this.refreshPositions();return this},_connectWith:function(){var a=this.options;
return a.connectWith.constructor==String?[a.connectWith]:a.connectWith},_getItemsAsjQuery:function(p){var q=[],l=[],k=this._connectWith();
if(k&&p){for(p=k.length-1;p>=0;p--){for(var m=b(k[p]),j=m.length-1;j>=0;j--){var a=b.data(m[j],"sortable");if(a&&a!=this&&!a.options.disabled){l.push([b.isFunction(a.options.items)?a.options.items.call(a.element):b(a.options.items,a.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"),a])
}}}}l.push([b.isFunction(this.options.items)?this.options.items.call(this.element,null,{options:this.options,item:this.currentItem}):b(this.options.items,this.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"),this]);
for(p=l.length-1;p>=0;p--){l[p][0].each(function(){q.push(this)})}return b(q)},_removeCurrentsFromItems:function(){for(var e=this.currentItem.find(":data(sortable-item)"),g=0;
g<this.items.length;g++){for(var a=0;a<e.length;a++){e[a]==this.items[g].item[0]&&this.items.splice(g,1)}}},_refreshItems:function(q){this.items=[];
this.containers=[this];var r=this.items,m=[[b.isFunction(this.options.items)?this.options.items.call(this.element[0],q,{item:this.currentItem}):b(this.options.items,this.element),this]],l=this._connectWith();
if(l){for(var p=l.length-1;p>=0;p--){for(var j=b(l[p]),a=j.length-1;a>=0;a--){var k=b.data(j[a],"sortable");if(k&&k!=this&&!k.options.disabled){m.push([b.isFunction(k.options.items)?k.options.items.call(k.element[0],q,{item:this.currentItem}):b(k.options.items,k.element),k]);
this.containers.push(k)}}}}for(p=m.length-1;p>=0;p--){q=m[p][1];l=m[p][0];a=0;for(j=l.length;a<j;a++){k=b(l[a]);k.data("sortable-item",q);
r.push({item:k,instance:q,width:0,height:0,left:0,top:0})}}},refreshPositions:function(j){if(this.offsetParent&&this.helper){this.offset.parent=this._getParentOffset()
}for(var k=this.items.length-1;k>=0;k--){var e=this.items[k];if(!(e.instance!=this.currentContainer&&this.currentContainer&&e.item[0]!=this.currentItem[0])){var a=this.options.toleranceElement?b(this.options.toleranceElement,e.item):e.item;
if(!j){e.width=a.outerWidth();e.height=a.outerHeight()}a=a.offset();e.left=a.left;e.top=a.top}}if(this.options.custom&&this.options.custom.refreshContainers){this.options.custom.refreshContainers.call(this)
}else{for(k=this.containers.length-1;k>=0;k--){a=this.containers[k].element.offset();this.containers[k].containerCache.left=a.left;
this.containers[k].containerCache.top=a.top;this.containers[k].containerCache.width=this.containers[k].element.outerWidth();
this.containers[k].containerCache.height=this.containers[k].element.outerHeight()}}return this},_createPlaceholder:function(j){var k=j||this,e=k.options;
if(!e.placeholder||e.placeholder.constructor==String){var a=e.placeholder;e.placeholder={element:function(){var c=b(document.createElement(k.currentItem[0].nodeName)).addClass(a||k.currentItem[0].className+" ui-sortable-placeholder").removeClass("ui-sortable-helper")[0];
if(!a){c.style.visibility="hidden"}return c},update:function(d,c){if(!(a&&!e.forcePlaceholderSize)){c.height()||c.height(k.currentItem.innerHeight()-parseInt(k.currentItem.css("paddingTop")||0,10)-parseInt(k.currentItem.css("paddingBottom")||0,10));
c.width()||c.width(k.currentItem.innerWidth()-parseInt(k.currentItem.css("paddingLeft")||0,10)-parseInt(k.currentItem.css("paddingRight")||0,10))
}}}}k.placeholder=b(e.placeholder.element.call(k.element,k.currentItem));k.currentItem.after(k.placeholder);e.placeholder.update(k,k.placeholder)
},_contactContainers:function(p){for(var q=null,l=null,k=this.containers.length-1;k>=0;k--){if(!b.ui.contains(this.currentItem[0],this.containers[k].element[0])){if(this._intersectsWith(this.containers[k].containerCache)){if(!(q&&b.ui.contains(this.containers[k].element[0],q.element[0]))){q=this.containers[k];
l=k}}else{if(this.containers[k].containerCache.over){this.containers[k]._trigger("out",p,this._uiHash(this));this.containers[k].containerCache.over=0
}}}}if(q){if(this.containers.length===1){this.containers[l]._trigger("over",p,this._uiHash(this));this.containers[l].containerCache.over=1
}else{if(this.currentContainer!=this.containers[l]){q=10000;k=null;for(var m=this.positionAbs[this.containers[l].floating?"left":"top"],j=this.items.length-1;
j>=0;j--){if(b.ui.contains(this.containers[l].element[0],this.items[j].item[0])){var a=this.items[j][this.containers[l].floating?"left":"top"];
if(Math.abs(a-m)<q){q=Math.abs(a-m);k=this.items[j]}}}if(k||this.options.dropOnEmpty){this.currentContainer=this.containers[l];
k?this._rearrange(p,k,null,true):this._rearrange(p,null,this.containers[l].element,true);this._trigger("change",p,this._uiHash());
this.containers[l]._trigger("change",p,this._uiHash(this));this.options.placeholder.update(this.currentContainer,this.placeholder);
this.containers[l]._trigger("over",p,this._uiHash(this));this.containers[l].containerCache.over=1}}}}},_createHelper:function(a){var e=this.options;
a=b.isFunction(e.helper)?b(e.helper.apply(this.element[0],[a,this.currentItem])):e.helper=="clone"?this.currentItem.clone():this.currentItem;
a.parents("body").length||b(e.appendTo!="parent"?e.appendTo:this.currentItem[0].parentNode)[0].appendChild(a[0]);if(a[0]==this.currentItem[0]){this._storedCSS={width:this.currentItem[0].style.width,height:this.currentItem[0].style.height,position:this.currentItem.css("position"),top:this.currentItem.css("top"),left:this.currentItem.css("left")}
}if(a[0].style.width==""||e.forceHelperSize){a.width(this.currentItem.width())}if(a[0].style.height==""||e.forceHelperSize){a.height(this.currentItem.height())
}return a},_adjustOffsetFromHelper:function(a){if(typeof a=="string"){a=a.split(" ")}if(b.isArray(a)){a={left:+a[0],top:+a[1]||0}
}if("left" in a){this.offset.click.left=a.left+this.margins.left}if("right" in a){this.offset.click.left=this.helperProportions.width-a.right+this.margins.left
}if("top" in a){this.offset.click.top=a.top+this.margins.top}if("bottom" in a){this.offset.click.top=this.helperProportions.height-a.bottom+this.margins.top
}},_getParentOffset:function(){this.offsetParent=this.helper.offsetParent();var a=this.offsetParent.offset();if(this.cssPosition=="absolute"&&this.scrollParent[0]!=document&&b.ui.contains(this.scrollParent[0],this.offsetParent[0])){a.left+=this.scrollParent.scrollLeft();
a.top+=this.scrollParent.scrollTop()}if(this.offsetParent[0]==document.body||this.offsetParent[0].tagName&&this.offsetParent[0].tagName.toLowerCase()=="html"&&b.browser.msie){a={top:0,left:0}
}return{top:a.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:a.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}
},_getRelativeOffset:function(){if(this.cssPosition=="relative"){var a=this.currentItem.position();return{top:a.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:a.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}
}else{return{top:0,left:0}}},_cacheMargins:function(){this.margins={left:parseInt(this.currentItem.css("marginLeft"),10)||0,top:parseInt(this.currentItem.css("marginTop"),10)||0}
},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}
},_setContainment:function(){var e=this.options;if(e.containment=="parent"){e.containment=this.helper[0].parentNode}if(e.containment=="document"||e.containment=="window"){this.containment=[0-this.offset.relative.left-this.offset.parent.left,0-this.offset.relative.top-this.offset.parent.top,b(e.containment=="document"?document:window).width()-this.helperProportions.width-this.margins.left,(b(e.containment=="document"?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top]
}if(!/^(document|window|parent)$/.test(e.containment)){var g=b(e.containment)[0];e=b(e.containment).offset();var a=b(g).css("overflow")!="hidden";
this.containment=[e.left+(parseInt(b(g).css("borderLeftWidth"),10)||0)+(parseInt(b(g).css("paddingLeft"),10)||0)-this.margins.left,e.top+(parseInt(b(g).css("borderTopWidth"),10)||0)+(parseInt(b(g).css("paddingTop"),10)||0)-this.margins.top,e.left+(a?Math.max(g.scrollWidth,g.offsetWidth):g.offsetWidth)-(parseInt(b(g).css("borderLeftWidth"),10)||0)-(parseInt(b(g).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left,e.top+(a?Math.max(g.scrollHeight,g.offsetHeight):g.offsetHeight)-(parseInt(b(g).css("borderTopWidth"),10)||0)-(parseInt(b(g).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top]
}},_convertPositionTo:function(j,k){if(!k){k=this.position}j=j=="absolute"?1:-1;var e=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&b.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,a=/(html|body)/i.test(e[0].tagName);
return{top:k.top+this.offset.relative.top*j+this.offset.parent.top*j-(b.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():a?0:e.scrollTop())*j),left:k.left+this.offset.relative.left*j+this.offset.parent.left*j-(b.browser.safari&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():a?0:e.scrollLeft())*j)}
},_generatePosition:function(m){var p=this.options,k=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&b.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,j=/(html|body)/i.test(k[0].tagName);
if(this.cssPosition=="relative"&&!(this.scrollParent[0]!=document&&this.scrollParent[0]!=this.offsetParent[0])){this.offset.relative=this._getRelativeOffset()
}var l=m.pageX,a=m.pageY;if(this.originalPosition){if(this.containment){if(m.pageX-this.offset.click.left<this.containment[0]){l=this.containment[0]+this.offset.click.left
}if(m.pageY-this.offset.click.top<this.containment[1]){a=this.containment[1]+this.offset.click.top}if(m.pageX-this.offset.click.left>this.containment[2]){l=this.containment[2]+this.offset.click.left
}if(m.pageY-this.offset.click.top>this.containment[3]){a=this.containment[3]+this.offset.click.top}}if(p.grid){a=this.originalPageY+Math.round((a-this.originalPageY)/p.grid[1])*p.grid[1];
a=this.containment?!(a-this.offset.click.top<this.containment[1]||a-this.offset.click.top>this.containment[3])?a:!(a-this.offset.click.top<this.containment[1])?a-p.grid[1]:a+p.grid[1]:a;
l=this.originalPageX+Math.round((l-this.originalPageX)/p.grid[0])*p.grid[0];l=this.containment?!(l-this.offset.click.left<this.containment[0]||l-this.offset.click.left>this.containment[2])?l:!(l-this.offset.click.left<this.containment[0])?l-p.grid[0]:l+p.grid[0]:l
}}return{top:a-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+(b.browser.safari&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollTop():j?0:k.scrollTop()),left:l-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(b.browser.safari&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():j?0:k.scrollLeft())}
},_rearrange:function(m,p,k,j){k?k[0].appendChild(this.placeholder[0]):p.item[0].parentNode.insertBefore(this.placeholder[0],this.direction=="down"?p.item[0]:p.item[0].nextSibling);
this.counter=this.counter?++this.counter:1;var l=this,a=this.counter;window.setTimeout(function(){a==l.counter&&l.refreshPositions(!j)
},0)},_clear:function(j,k){this.reverting=false;var e=[];!this._noFinalSort&&this.currentItem[0].parentNode&&this.placeholder.before(this.currentItem);
this._noFinalSort=null;if(this.helper[0]==this.currentItem[0]){for(var a in this._storedCSS){if(this._storedCSS[a]=="auto"||this._storedCSS[a]=="static"){this._storedCSS[a]=""
}}this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper")}else{this.currentItem.show()}this.fromOutside&&!k&&e.push(function(c){this._trigger("receive",c,this._uiHash(this.fromOutside))
});if((this.fromOutside||this.domPosition.prev!=this.currentItem.prev().not(".ui-sortable-helper")[0]||this.domPosition.parent!=this.currentItem.parent()[0])&&!k){e.push(function(c){this._trigger("update",c,this._uiHash())
})}if(!b.ui.contains(this.element[0],this.currentItem[0])){k||e.push(function(c){this._trigger("remove",c,this._uiHash())
});for(a=this.containers.length-1;a>=0;a--){if(b.ui.contains(this.containers[a].element[0],this.currentItem[0])&&!k){e.push(function(c){return function(d){c._trigger("receive",d,this._uiHash(this))
}}.call(this,this.containers[a]));e.push(function(c){return function(d){c._trigger("update",d,this._uiHash(this))}}.call(this,this.containers[a]))
}}}for(a=this.containers.length-1;a>=0;a--){k||e.push(function(c){return function(d){c._trigger("deactivate",d,this._uiHash(this))
}}.call(this,this.containers[a]));if(this.containers[a].containerCache.over){e.push(function(c){return function(d){c._trigger("out",d,this._uiHash(this))
}}.call(this,this.containers[a]));this.containers[a].containerCache.over=0}}this._storedCursor&&b("body").css("cursor",this._storedCursor);
this._storedOpacity&&this.helper.css("opacity",this._storedOpacity);if(this._storedZIndex){this.helper.css("zIndex",this._storedZIndex=="auto"?"":this._storedZIndex)
}this.dragging=false;if(this.cancelHelperRemoval){if(!k){this._trigger("beforeStop",j,this._uiHash());for(a=0;a<e.length;
a++){e[a].call(this,j)}this._trigger("stop",j,this._uiHash())}return false}k||this._trigger("beforeStop",j,this._uiHash());
this.placeholder[0].parentNode.removeChild(this.placeholder[0]);this.helper[0]!=this.currentItem[0]&&this.helper.remove();
this.helper=null;if(!k){for(a=0;a<e.length;a++){e[a].call(this,j)}this._trigger("stop",j,this._uiHash())}this.fromOutside=false;
return true},_trigger:function(){b.Widget.prototype._trigger.apply(this,arguments)===false&&this.cancel()},_uiHash:function(a){var e=a||this;
return{helper:e.helper,placeholder:e.placeholder||b([]),position:e.position,originalPosition:e.originalPosition,offset:e.positionAbs,item:e.currentItem,sender:a?a.element:null}
}});b.extend(b.ui.sortable,{version:"1.8.13"})})(jQuery);jQuery.effects||function(B,y){function z(b){var a;if(b&&b.constructor==Array&&b.length==3){return b
}if(a=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(b)){return[parseInt(a[1],10),parseInt(a[2],10),parseInt(a[3],10)]
}if(a=/rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(b)){return[parseFloat(a[1])*2.55,parseFloat(a[2])*2.55,parseFloat(a[3])*2.55]
}if(a=/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(b)){return[parseInt(a[1],16),parseInt(a[2],16),parseInt(a[3],16)]
}if(a=/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(b)){return[parseInt(a[1]+a[1],16),parseInt(a[2]+a[2],16),parseInt(a[3]+a[3],16)]
}if(/rgba\(0, 0, 0, 0\)/.exec(b)){return p.transparent}return p[B.trim(b).toLowerCase()]}function v(c,b){var a;do{a=B.curCSS(c,b);
if(a!=""&&a!="transparent"||B.nodeName(c,"body")){break}b="backgroundColor"}while(c=c.parentNode);return z(a)}function u(){var e=document.defaultView?document.defaultView.getComputedStyle(this,null):this.currentStyle,b={},a,d;
if(e&&e.length&&e[0]&&e[e[0]]){for(var c=e.length;c--;){a=e[c];if(typeof e[a]=="string"){d=a.replace(/\-(\w)/g,function(f,g){return g.toUpperCase()
});b[d]=e[a]}}}else{for(a in e){if(typeof e[a]==="string"){b[a]=e[a]}}}return b}function x(c){var b,a;for(b in c){a=c[b];
if(a==null||B.isFunction(a)||b in k||/scrollbar/.test(b)||!/color/i.test(b)&&isNaN(parseFloat(a))){delete c[b]}}return c}function q(d,b){var a={_:0},c;
for(c in b){if(d[c]!=b[c]){a[c]=b[c]}}return a}function A(d,b,a,c){if(typeof d=="object"){c=b;a=null;b=d;d=b.effect}if(B.isFunction(b)){c=b;
a=null;b={}}if(typeof b=="number"||B.fx.speeds[b]){c=a;a=b;b={}}if(B.isFunction(a)){c=a;a=null}b=b||{};a=a||b.duration;a=B.fx.off?0:typeof a=="number"?a:a in B.fx.speeds?B.fx.speeds[a]:B.fx.speeds._default;
c=c||b.complete;return[d,b,a,c]}function r(a){if(!a||typeof a==="number"||B.fx.speeds[a]){return true}if(typeof a==="string"&&!B.effects[a]){return true
}return false}B.effects={};B.each(["backgroundColor","borderBottomColor","borderLeftColor","borderRightColor","borderTopColor","borderColor","color","outlineColor"],function(b,a){B.fx.step[a]=function(c){if(!c.colorInit){c.start=v(c.elem,a);
c.end=z(c.end);c.colorInit=true}c.elem.style[a]="rgb("+Math.max(Math.min(parseInt(c.pos*(c.end[0]-c.start[0])+c.start[0],10),255),0)+","+Math.max(Math.min(parseInt(c.pos*(c.end[1]-c.start[1])+c.start[1],10),255),0)+","+Math.max(Math.min(parseInt(c.pos*(c.end[2]-c.start[2])+c.start[2],10),255),0)+")"
}});var p={aqua:[0,255,255],azure:[240,255,255],beige:[245,245,220],black:[0,0,0],blue:[0,0,255],brown:[165,42,42],cyan:[0,255,255],darkblue:[0,0,139],darkcyan:[0,139,139],darkgrey:[169,169,169],darkgreen:[0,100,0],darkkhaki:[189,183,107],darkmagenta:[139,0,139],darkolivegreen:[85,107,47],darkorange:[255,140,0],darkorchid:[153,50,204],darkred:[139,0,0],darksalmon:[233,150,122],darkviolet:[148,0,211],fuchsia:[255,0,255],gold:[255,215,0],green:[0,128,0],indigo:[75,0,130],khaki:[240,230,140],lightblue:[173,216,230],lightcyan:[224,255,255],lightgreen:[144,238,144],lightgrey:[211,211,211],lightpink:[255,182,193],lightyellow:[255,255,224],lime:[0,255,0],magenta:[255,0,255],maroon:[128,0,0],navy:[0,0,128],olive:[128,128,0],orange:[255,165,0],pink:[255,192,203],purple:[128,0,128],violet:[128,0,128],red:[255,0,0],silver:[192,192,192],white:[255,255,255],yellow:[255,255,0],transparent:[255,255,255]},m=["add","remove","toggle"],k={border:1,borderBottom:1,borderColor:1,borderLeft:1,borderRight:1,borderTop:1,borderWidth:1,margin:1,padding:1};
B.effects.animateClass=function(d,b,a,c){if(B.isFunction(a)){c=a;a=null}return this.queue(function(){var l=B(this),g=l.attr("style")||" ",j=x(u.call(this)),f,e=l.attr("class");
B.each(m,function(D,C){d[C]&&l[C+"Class"](d[C])});f=x(u.call(this));l.attr("class",e);l.animate(q(j,f),{queue:false,duration:b,easding:a,complete:function(){B.each(m,function(D,C){d[C]&&l[C+"Class"](d[C])
});if(typeof l.attr("style")=="object"){l.attr("style").cssText="";l.attr("style").cssText=g}else{l.attr("style",g)}c&&c.apply(this,arguments);
B.dequeue(this)}})})};B.fn.extend({_addClass:B.fn.addClass,addClass:function(d,b,a,c){return b?B.effects.animateClass.apply(this,[{add:d},b,a,c]):this._addClass(d)
},_removeClass:B.fn.removeClass,removeClass:function(d,b,a,c){return b?B.effects.animateClass.apply(this,[{remove:d},b,a,c]):this._removeClass(d)
},_toggleClass:B.fn.toggleClass,toggleClass:function(e,b,a,d,c){return typeof b=="boolean"||b===y?a?B.effects.animateClass.apply(this,[b?{add:e}:{remove:e},a,d,c]):this._toggleClass(e,b):B.effects.animateClass.apply(this,[{toggle:e},b,a,d])
},switchClass:function(e,b,a,d,c){return B.effects.animateClass.apply(this,[{add:b,remove:e},a,d,c])}});B.extend(B.effects,{version:"1.8.13",save:function(c,b){for(var a=0;
a<b.length;a++){b[a]!==null&&c.data("ec.storage."+b[a],c[0].style[b[a]])}},restore:function(c,b){for(var a=0;a<b.length;a++){b[a]!==null&&c.css(b[a],c.data("ec.storage."+b[a]))
}},setMode:function(b,a){if(a=="toggle"){a=b.is(":hidden")?"show":"hide"}return a},getBaseline:function(c,b){var a;switch(c[0]){case"top":a=0;
break;case"middle":a=0.5;break;case"bottom":a=1;break;default:a=c[0]/b.height}switch(c[1]){case"left":c=0;break;case"center":c=0.5;
break;case"right":c=1;break;default:c=c[1]/b.width}return{x:c,y:a}},createWrapper:function(c){if(c.parent().is(".ui-effects-wrapper")){return c.parent()
}var b={width:c.outerWidth(true),height:c.outerHeight(true),"float":c.css("float")},a=B("<div></div>").addClass("ui-effects-wrapper").css({fontSize:"100%",background:"transparent",border:"none",margin:0,padding:0});
c.wrap(a);a=c.parent();if(c.css("position")=="static"){a.css({position:"relative"});c.css({position:"relative"})}else{B.extend(b,{position:c.css("position"),zIndex:c.css("z-index")});
B.each(["top","left","bottom","right"],function(e,d){b[d]=c.css(d);if(isNaN(parseInt(b[d],10))){b[d]="auto"}});c.css({position:"relative",top:0,left:0,right:"auto",bottom:"auto"})
}return a.css(b).show()},removeWrapper:function(a){if(a.parent().is(".ui-effects-wrapper")){return a.parent().replaceWith(a)
}return a},setTransition:function(d,b,a,c){c=c||{};B.each(b,function(f,e){unit=d.cssUnit(e);if(unit[0]>0){c[e]=unit[0]*a+unit[1]
}});return c}});B.fn.extend({effect:function(d){var b=A.apply(this,arguments),a={options:b[1],duration:b[2],callback:b[3]};
b=a.options.mode;var c=B.effects[d];if(B.fx.off||!c){return b?this[b](a.duration,a.callback):this.each(function(){a.callback&&a.callback.call(this)
})}return c.call(this,a)},_show:B.fn.show,show:function(b){if(r(b)){return this._show.apply(this,arguments)}else{var a=A.apply(this,arguments);
a[1].mode="show";return this.effect.apply(this,a)}},_hide:B.fn.hide,hide:function(b){if(r(b)){return this._hide.apply(this,arguments)
}else{var a=A.apply(this,arguments);a[1].mode="hide";return this.effect.apply(this,a)}},__toggle:B.fn.toggle,toggle:function(b){if(r(b)||typeof b==="boolean"||B.isFunction(b)){return this.__toggle.apply(this,arguments)
}else{var a=A.apply(this,arguments);a[1].mode="toggle";return this.effect.apply(this,a)}},cssUnit:function(c){var b=this.css(c),a=[];
B.each(["em","px","%","pt"],function(e,d){if(b.indexOf(d)>0){a=[parseFloat(b),d]}});return a}});B.easing.jswing=B.easing.swing;
B.extend(B.easing,{def:"easeOutQuad",swing:function(e,b,a,d,c){return B.easing[B.easing.def](e,b,a,d,c)},easeInQuad:function(e,b,a,d,c){return d*(b/=c)*b+a
},easeOutQuad:function(e,b,a,d,c){return -d*(b/=c)*(b-2)+a},easeInOutQuad:function(e,b,a,d,c){if((b/=c/2)<1){return d/2*b*b+a
}return -d/2*(--b*(b-2)-1)+a},easeInCubic:function(e,b,a,d,c){return d*(b/=c)*b*b+a},easeOutCubic:function(e,b,a,d,c){return d*((b=b/c-1)*b*b+1)+a
},easeInOutCubic:function(e,b,a,d,c){if((b/=c/2)<1){return d/2*b*b*b+a}return d/2*((b-=2)*b*b+2)+a},easeInQuart:function(e,b,a,d,c){return d*(b/=c)*b*b*b+a
},easeOutQuart:function(e,b,a,d,c){return -d*((b=b/c-1)*b*b*b-1)+a},easeInOutQuart:function(e,b,a,d,c){if((b/=c/2)<1){return d/2*b*b*b*b+a
}return -d/2*((b-=2)*b*b*b-2)+a},easeInQuint:function(e,b,a,d,c){return d*(b/=c)*b*b*b*b+a},easeOutQuint:function(e,b,a,d,c){return d*((b=b/c-1)*b*b*b*b+1)+a
},easeInOutQuint:function(e,b,a,d,c){if((b/=c/2)<1){return d/2*b*b*b*b*b+a}return d/2*((b-=2)*b*b*b*b+2)+a},easeInSine:function(e,b,a,d,c){return -d*Math.cos(b/c*(Math.PI/2))+d+a
},easeOutSine:function(e,b,a,d,c){return d*Math.sin(b/c*(Math.PI/2))+a},easeInOutSine:function(e,b,a,d,c){return -d/2*(Math.cos(Math.PI*b/c)-1)+a
},easeInExpo:function(e,b,a,d,c){return b==0?a:d*Math.pow(2,10*(b/c-1))+a},easeOutExpo:function(e,b,a,d,c){return b==c?a+d:d*(-Math.pow(2,-10*b/c)+1)+a
},easeInOutExpo:function(e,b,a,d,c){if(b==0){return a}if(b==c){return a+d}if((b/=c/2)<1){return d/2*Math.pow(2,10*(b-1))+a
}return d/2*(-Math.pow(2,-10*--b)+2)+a},easeInCirc:function(e,b,a,d,c){return -d*(Math.sqrt(1-(b/=c)*b)-1)+a},easeOutCirc:function(e,b,a,d,c){return d*Math.sqrt(1-(b=b/c-1)*b)+a
},easeInOutCirc:function(e,b,a,d,c){if((b/=c/2)<1){return -d/2*(Math.sqrt(1-b*b)-1)+a}return d/2*(Math.sqrt(1-(b-=2)*b)+1)+a
},easeInElastic:function(g,b,a,f,e){g=1.70158;var c=0,d=f;if(b==0){return a}if((b/=e)==1){return a+f}c||(c=e*0.3);if(d<Math.abs(f)){d=f;
g=c/4}else{g=c/(2*Math.PI)*Math.asin(f/d)}return -(d*Math.pow(2,10*(b-=1))*Math.sin((b*e-g)*2*Math.PI/c))+a},easeOutElastic:function(g,b,a,f,e){g=1.70158;
var c=0,d=f;if(b==0){return a}if((b/=e)==1){return a+f}c||(c=e*0.3);if(d<Math.abs(f)){d=f;g=c/4}else{g=c/(2*Math.PI)*Math.asin(f/d)
}return d*Math.pow(2,-10*b)*Math.sin((b*e-g)*2*Math.PI/c)+f+a},easeInOutElastic:function(g,b,a,f,e){g=1.70158;var c=0,d=f;
if(b==0){return a}if((b/=e/2)==2){return a+f}c||(c=e*0.3*1.5);if(d<Math.abs(f)){d=f;g=c/4}else{g=c/(2*Math.PI)*Math.asin(f/d)
}if(b<1){return -0.5*d*Math.pow(2,10*(b-=1))*Math.sin((b*e-g)*2*Math.PI/c)+a}return d*Math.pow(2,-10*(b-=1))*Math.sin((b*e-g)*2*Math.PI/c)*0.5+f+a
},easeInBack:function(f,b,a,e,d,c){if(c==y){c=1.70158}return e*(b/=d)*b*((c+1)*b-c)+a},easeOutBack:function(f,b,a,e,d,c){if(c==y){c=1.70158
}return e*((b=b/d-1)*b*((c+1)*b+c)+1)+a},easeInOutBack:function(f,b,a,e,d,c){if(c==y){c=1.70158}if((b/=d/2)<1){return e/2*b*b*(((c*=1.525)+1)*b-c)+a
}return e/2*((b-=2)*b*(((c*=1.525)+1)*b+c)+2)+a},easeInBounce:function(e,b,a,d,c){return d-B.easing.easeOutBounce(e,c-b,0,d,c)+a
},easeOutBounce:function(e,b,a,d,c){return(b/=c)<1/2.75?d*7.5625*b*b+a:b<2/2.75?d*(7.5625*(b-=1.5/2.75)*b+0.75)+a:b<2.5/2.75?d*(7.5625*(b-=2.25/2.75)*b+0.9375)+a:d*(7.5625*(b-=2.625/2.75)*b+0.984375)+a
},easeInOutBounce:function(e,b,a,d,c){if(b<c/2){return B.easing.easeInBounce(e,b*2,0,d,c)*0.5+a}return B.easing.easeOutBounce(e,b*2-c,0,d,c)*0.5+d*0.5+a
}})}(jQuery);(function(b){b.effects.blind=function(a){return this.queue(function(){var q=b(this),m=["position","top","bottom","left","right"],l=b.effects.setMode(q,a.options.mode||"hide"),p=a.options.direction||"vertical";
b.effects.save(q,m);q.show();var j=b.effects.createWrapper(q).css({overflow:"hidden"}),d=p=="vertical"?"height":"width";p=p=="vertical"?j.height():j.width();
l=="show"&&j.css(d,0);var k={};k[d]=l=="show"?p:0;j.animate(k,a.duration,a.options.easing,function(){l=="hide"&&q.hide();
b.effects.restore(q,m);b.effects.removeWrapper(q);a.callback&&a.callback.apply(q[0],arguments);q.dequeue()})})}})(jQuery);
(function(b){b.effects.bounce=function(a){return this.queue(function(){var y=b(this),v=["position","top","bottom","left","right"],u=b.effects.setMode(y,a.options.mode||"effect"),x=a.options.direction||"up",q=a.options.distance||20,z=a.options.times||5,r=a.duration||250;
/show|hide/.test(u)&&v.push("opacity");b.effects.save(y,v);y.show();b.effects.createWrapper(y);var p=x=="up"||x=="down"?"top":"left";
x=x=="up"||x=="left"?"pos":"neg";q=a.options.distance||(p=="top"?y.outerHeight({margin:true})/3:y.outerWidth({margin:true})/3);
if(u=="show"){y.css("opacity",0).css(p,x=="pos"?-q:q)}if(u=="hide"){q/=z*2}u!="hide"&&z--;if(u=="show"){var m={opacity:1};
m[p]=(x=="pos"?"+=":"-=")+q;y.animate(m,r/2,a.options.easing);q/=2;z--}for(m=0;m<z;m++){var d={},k={};d[p]=(x=="pos"?"-=":"+=")+q;
k[p]=(x=="pos"?"+=":"-=")+q;y.animate(d,r/2,a.options.easing).animate(k,r/2,a.options.easing);q=u=="hide"?q*2:q/2}if(u=="hide"){m={opacity:0};
m[p]=(x=="pos"?"-=":"+=")+q;y.animate(m,r/2,a.options.easing,function(){y.hide();b.effects.restore(y,v);b.effects.removeWrapper(y);
a.callback&&a.callback.apply(this,arguments)})}else{d={};k={};d[p]=(x=="pos"?"-=":"+=")+q;k[p]=(x=="pos"?"+=":"-=")+q;y.animate(d,r/2,a.options.easing).animate(k,r/2,a.options.easing,function(){b.effects.restore(y,v);
b.effects.removeWrapper(y);a.callback&&a.callback.apply(this,arguments)})}y.queue("fx",function(){y.dequeue()});y.dequeue()
})}})(jQuery);(function(b){b.effects.clip=function(a){return this.queue(function(){var q=b(this),m=["position","top","bottom","left","right","height","width"],l=b.effects.setMode(q,a.options.mode||"hide"),p=a.options.direction||"vertical";
b.effects.save(q,m);q.show();var j=b.effects.createWrapper(q).css({overflow:"hidden"});j=q[0].tagName=="IMG"?j:q;var d={size:p=="vertical"?"height":"width",position:p=="vertical"?"top":"left"};
p=p=="vertical"?j.height():j.width();if(l=="show"){j.css(d.size,0);j.css(d.position,p/2)}var k={};k[d.size]=l=="show"?p:0;
k[d.position]=l=="show"?0:p/2;j.animate(k,{queue:false,duration:a.duration,easing:a.options.easing,complete:function(){l=="hide"&&q.hide();
b.effects.restore(q,m);b.effects.removeWrapper(q);a.callback&&a.callback.apply(q[0],arguments);q.dequeue()}})})}})(jQuery);
(function(b){b.effects.drop=function(a){return this.queue(function(){var q=b(this),m=["position","top","bottom","left","right","opacity"],l=b.effects.setMode(q,a.options.mode||"hide"),p=a.options.direction||"left";
b.effects.save(q,m);q.show();b.effects.createWrapper(q);var j=p=="up"||p=="down"?"top":"left";p=p=="up"||p=="left"?"pos":"neg";
var d=a.options.distance||(j=="top"?q.outerHeight({margin:true})/2:q.outerWidth({margin:true})/2);if(l=="show"){q.css("opacity",0).css(j,p=="pos"?-d:d)
}var k={opacity:l=="show"?1:0};k[j]=(l=="show"?p=="pos"?"+=":"-=":p=="pos"?"-=":"+=")+d;q.animate(k,{queue:false,duration:a.duration,easing:a.options.easing,complete:function(){l=="hide"&&q.hide();
b.effects.restore(q,m);b.effects.removeWrapper(q);a.callback&&a.callback.apply(this,arguments);q.dequeue()}})})}})(jQuery);
(function(b){b.effects.explode=function(a){return this.queue(function(){var u=a.options.pieces?Math.round(Math.sqrt(a.options.pieces)):3,q=a.options.pieces?Math.round(Math.sqrt(a.options.pieces)):3;
a.options.mode=a.options.mode=="toggle"?b(this).is(":visible")?"hide":"show":a.options.mode;var p=b(this).show().css("visibility","hidden"),r=p.offset();
r.top-=parseInt(p.css("marginTop"),10)||0;r.left-=parseInt(p.css("marginLeft"),10)||0;for(var l=p.outerWidth(true),d=p.outerHeight(true),m=0;
m<u;m++){for(var k=0;k<q;k++){p.clone().appendTo("body").wrap("<div></div>").css({position:"absolute",visibility:"visible",left:-k*(l/q),top:-m*(d/u)}).parent().addClass("ui-effects-explode").css({position:"absolute",overflow:"hidden",width:l/q,height:d/u,left:r.left+k*(l/q)+(a.options.mode=="show"?(k-Math.floor(q/2))*(l/q):0),top:r.top+m*(d/u)+(a.options.mode=="show"?(m-Math.floor(u/2))*(d/u):0),opacity:a.options.mode=="show"?0:1}).animate({left:r.left+k*(l/q)+(a.options.mode=="show"?0:(k-Math.floor(q/2))*(l/q)),top:r.top+m*(d/u)+(a.options.mode=="show"?0:(m-Math.floor(u/2))*(d/u)),opacity:a.options.mode=="show"?1:0},a.duration||500)
}}setTimeout(function(){a.options.mode=="show"?p.css({visibility:"visible"}):p.css({visibility:"visible"}).hide();a.callback&&a.callback.apply(p[0]);
p.dequeue();b("div.ui-effects-explode").remove()},a.duration||500)})}})(jQuery);(function(b){b.effects.fade=function(a){return this.queue(function(){var e=b(this),d=b.effects.setMode(e,a.options.mode||"hide");
e.animate({opacity:d},{queue:false,duration:a.duration,easing:a.options.easing,complete:function(){a.callback&&a.callback.apply(this,arguments);
e.dequeue()}})})}})(jQuery);(function(b){b.effects.fold=function(a){return this.queue(function(){var x=b(this),u=["position","top","bottom","left","right"],r=b.effects.setMode(x,a.options.mode||"hide"),v=a.options.size||15,p=!!a.options.horizFirst,y=a.duration?a.duration/2:b.fx.speeds._default/2;
b.effects.save(x,u);x.show();var q=b.effects.createWrapper(x).css({overflow:"hidden"}),m=r=="show"!=p,k=m?["width","height"]:["height","width"];
m=m?[q.width(),q.height()]:[q.height(),q.width()];var d=/([0-9]+)%/.exec(v);if(d){v=parseInt(d[1],10)/100*m[r=="hide"?0:1]
}if(r=="show"){q.css(p?{height:0,width:v}:{height:v,width:0})}p={};d={};p[k[0]]=r=="show"?m[0]:v;d[k[1]]=r=="show"?m[1]:0;
q.animate(p,y,a.options.easing).animate(d,y,a.options.easing,function(){r=="hide"&&x.hide();b.effects.restore(x,u);b.effects.removeWrapper(x);
a.callback&&a.callback.apply(x[0],arguments);x.dequeue()})})}})(jQuery);(function(b){b.effects.highlight=function(a){return this.queue(function(){var l=b(this),j=["backgroundImage","backgroundColor","opacity"],d=b.effects.setMode(l,a.options.mode||"show"),k={backgroundColor:l.css("backgroundColor")};
if(d=="hide"){k.opacity=0}b.effects.save(l,j);l.show().css({backgroundImage:"none",backgroundColor:a.options.color||"#ffff99"}).animate(k,{queue:false,duration:a.duration,easing:a.options.easing,complete:function(){d=="hide"&&l.hide();
b.effects.restore(l,j);d=="show"&&!b.support.opacity&&this.style.removeAttribute("filter");a.callback&&a.callback.apply(this,arguments);
l.dequeue()}})})}})(jQuery);(function(b){b.effects.pulsate=function(a){return this.queue(function(){var e=b(this),d=b.effects.setMode(e,a.options.mode||"show");
times=(a.options.times||5)*2-1;duration=a.duration?a.duration/2:b.fx.speeds._default/2;isVisible=e.is(":visible");animateTo=0;
if(!isVisible){e.css("opacity",0).show();animateTo=1}if(d=="hide"&&isVisible||d=="show"&&!isVisible){times--}for(d=0;d<times;
d++){e.animate({opacity:animateTo},duration,a.options.easing);animateTo=(animateTo+1)%2}e.animate({opacity:animateTo},duration,a.options.easing,function(){animateTo==0&&e.hide();
a.callback&&a.callback.apply(this,arguments)});e.queue("fx",function(){e.dequeue()}).dequeue()})}})(jQuery);(function(b){b.effects.puff=function(a){return this.queue(function(){var m=b(this),k=b.effects.setMode(m,a.options.mode||"hide"),j=parseInt(a.options.percent,10)||150,l=j/100,d={height:m.height(),width:m.width()};
b.extend(a.options,{fade:true,mode:k,percent:k=="hide"?j:100,from:k=="hide"?d:{height:d.height*l,width:d.width*l}});m.effect("scale",a.options,a.duration,a.callback);
m.dequeue()})};b.effects.scale=function(a){return this.queue(function(){var p=b(this),l=b.extend(true,{},a.options),k=b.effects.setMode(p,a.options.mode||"effect"),m=parseInt(a.options.percent,10)||(parseInt(a.options.percent,10)==0?0:k=="hide"?0:100),j=a.options.direction||"both",d=a.options.origin;
if(k!="effect"){l.origin=d||["middle","center"];l.restore=true}d={height:p.height(),width:p.width()};p.from=a.options.from||(k=="show"?{height:0,width:0}:d);
m={y:j!="horizontal"?m/100:1,x:j!="vertical"?m/100:1};p.to={height:d.height*m.y,width:d.width*m.x};if(a.options.fade){if(k=="show"){p.from.opacity=0;
p.to.opacity=1}if(k=="hide"){p.from.opacity=1;p.to.opacity=0}}l.from=p.from;l.to=p.to;l.mode=k;p.effect("size",l,a.duration,a.callback);
p.dequeue()})};b.effects.size=function(a){return this.queue(function(){var C=b(this),A=["position","top","bottom","left","right","width","height","overflow","opacity"],z=["position","top","bottom","left","right","overflow","opacity"],B=["width","height","overflow"],x=["fontSize"],D=["borderTopWidth","borderBottomWidth","paddingTop","paddingBottom"],y=["borderLeftWidth","borderRightWidth","paddingLeft","paddingRight"],v=b.effects.setMode(C,a.options.mode||"effect"),r=a.options.restore||false,d=a.options.scale||"both",p=a.options.origin,u={height:C.height(),width:C.width()};
C.from=a.options.from||u;C.to=a.options.to||u;if(p){p=b.effects.getBaseline(p,u);C.from.top=(u.height-C.from.height)*p.y;
C.from.left=(u.width-C.from.width)*p.x;C.to.top=(u.height-C.to.height)*p.y;C.to.left=(u.width-C.to.width)*p.x}var q={from:{y:C.from.height/u.height,x:C.from.width/u.width},to:{y:C.to.height/u.height,x:C.to.width/u.width}};
if(d=="box"||d=="both"){if(q.from.y!=q.to.y){A=A.concat(D);C.from=b.effects.setTransition(C,D,q.from.y,C.from);C.to=b.effects.setTransition(C,D,q.to.y,C.to)
}if(q.from.x!=q.to.x){A=A.concat(y);C.from=b.effects.setTransition(C,y,q.from.x,C.from);C.to=b.effects.setTransition(C,y,q.to.x,C.to)
}}if(d=="content"||d=="both"){if(q.from.y!=q.to.y){A=A.concat(x);C.from=b.effects.setTransition(C,x,q.from.y,C.from);C.to=b.effects.setTransition(C,x,q.to.y,C.to)
}}b.effects.save(C,r?A:z);C.show();b.effects.createWrapper(C);C.css("overflow","hidden").css(C.from);if(d=="content"||d=="both"){D=D.concat(["marginTop","marginBottom"]).concat(x);
y=y.concat(["marginLeft","marginRight"]);B=A.concat(D).concat(y);C.find("*[width]").each(function(){child=b(this);r&&b.effects.save(child,B);
var c={height:child.height(),width:child.width()};child.from={height:c.height*q.from.y,width:c.width*q.from.x};child.to={height:c.height*q.to.y,width:c.width*q.to.x};
if(q.from.y!=q.to.y){child.from=b.effects.setTransition(child,D,q.from.y,child.from);child.to=b.effects.setTransition(child,D,q.to.y,child.to)
}if(q.from.x!=q.to.x){child.from=b.effects.setTransition(child,y,q.from.x,child.from);child.to=b.effects.setTransition(child,y,q.to.x,child.to)
}child.css(child.from);child.animate(child.to,a.duration,a.options.easing,function(){r&&b.effects.restore(child,B)})})}C.animate(C.to,{queue:false,duration:a.duration,easing:a.options.easing,complete:function(){C.to.opacity===0&&C.css("opacity",C.from.opacity);
v=="hide"&&C.hide();b.effects.restore(C,r?A:z);b.effects.removeWrapper(C);a.callback&&a.callback.apply(this,arguments);C.dequeue()
}})})}})(jQuery);(function(b){b.effects.shake=function(a){return this.queue(function(){var x=b(this),u=["position","top","bottom","left","right"];
b.effects.setMode(x,a.options.mode||"effect");var r=a.options.direction||"left",v=a.options.distance||20,p=a.options.times||3,y=a.duration||a.options.duration||140;
b.effects.save(x,u);x.show();b.effects.createWrapper(x);var q=r=="up"||r=="down"?"top":"left",m=r=="up"||r=="left"?"pos":"neg";
r={};var k={},d={};r[q]=(m=="pos"?"-=":"+=")+v;k[q]=(m=="pos"?"+=":"-=")+v*2;d[q]=(m=="pos"?"-=":"+=")+v*2;x.animate(r,y,a.options.easing);
for(v=1;v<p;v++){x.animate(k,y,a.options.easing).animate(d,y,a.options.easing)}x.animate(k,y,a.options.easing).animate(r,y/2,a.options.easing,function(){b.effects.restore(x,u);
b.effects.removeWrapper(x);a.callback&&a.callback.apply(this,arguments)});x.queue("fx",function(){x.dequeue()});x.dequeue()
})}})(jQuery);(function(b){b.effects.slide=function(a){return this.queue(function(){var q=b(this),m=["position","top","bottom","left","right"],l=b.effects.setMode(q,a.options.mode||"show"),p=a.options.direction||"left";
b.effects.save(q,m);q.show();b.effects.createWrapper(q).css({overflow:"hidden"});var j=p=="up"||p=="down"?"top":"left";p=p=="up"||p=="left"?"pos":"neg";
var d=a.options.distance||(j=="top"?q.outerHeight({margin:true}):q.outerWidth({margin:true}));if(l=="show"){q.css(j,p=="pos"?isNaN(d)?"-"+d:-d:d)
}var k={};k[j]=(l=="show"?p=="pos"?"+=":"-=":p=="pos"?"-=":"+=")+d;q.animate(k,{queue:false,duration:a.duration,easing:a.options.easing,complete:function(){l=="hide"&&q.hide();
b.effects.restore(q,m);b.effects.removeWrapper(q);a.callback&&a.callback.apply(this,arguments);q.dequeue()}})})}})(jQuery);
(function(b){b.effects.transfer=function(a){return this.queue(function(){var l=b(this),j=b(a.options.to),d=j.offset();j={top:d.top,left:d.left,height:j.innerHeight(),width:j.innerWidth()};
d=l.offset();var k=b('<div class="ui-effects-transfer"></div>').appendTo(document.body).addClass(a.options.className).css({top:d.top,left:d.left,height:l.innerHeight(),width:l.innerWidth(),position:"absolute"}).animate(j,a.duration,a.options.easing,function(){k.remove();
a.callback&&a.callback.apply(l[0],arguments);l.dequeue()})})}})(jQuery);(function(b){b.widget("ui.accordion",{options:{active:0,animated:"slide",autoHeight:true,clearStyle:false,collapsible:false,event:"click",fillSpace:false,header:"> li > :first-child,> :not(li):even",icons:{header:"ui-icon-triangle-1-e",headerSelected:"ui-icon-triangle-1-s"},navigation:false,navigationFilter:function(){return this.href.toLowerCase()===location.href.toLowerCase()
}},_create:function(){var j=this,k=j.options;j.running=0;j.element.addClass("ui-accordion ui-widget ui-helper-reset").children("li").addClass("ui-accordion-li-fix");
j.headers=j.element.find(k.header).addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-all").bind("mouseenter.accordion",function(){k.disabled||b(this).addClass("ui-state-hover")
}).bind("mouseleave.accordion",function(){k.disabled||b(this).removeClass("ui-state-hover")}).bind("focus.accordion",function(){k.disabled||b(this).addClass("ui-state-focus")
}).bind("blur.accordion",function(){k.disabled||b(this).removeClass("ui-state-focus")});j.headers.next().addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom");
if(k.navigation){var e=j.element.find("a").filter(k.navigationFilter).eq(0);if(e.length){var a=e.closest(".ui-accordion-header");
j.active=a.length?a:e.closest(".ui-accordion-content").prev()}}j.active=j._findActive(j.active||k.active).addClass("ui-state-default ui-state-active").toggleClass("ui-corner-all").toggleClass("ui-corner-top");
j.active.next().addClass("ui-accordion-content-active");j._createIcons();j.resize();j.element.attr("role","tablist");j.headers.attr("role","tab").bind("keydown.accordion",function(c){return j._keydown(c)
}).next().attr("role","tabpanel");j.headers.not(j.active||"").attr({"aria-expanded":"false","aria-selected":"false",tabIndex:-1}).next().hide();
j.active.length?j.active.attr({"aria-expanded":"true","aria-selected":"true",tabIndex:0}):j.headers.eq(0).attr("tabIndex",0);
b.browser.safari||j.headers.find("a").attr("tabIndex",-1);k.event&&j.headers.bind(k.event.split(" ").join(".accordion ")+".accordion",function(c){j._clickHandler.call(j,c,this);
c.preventDefault()})},_createIcons:function(){var a=this.options;if(a.icons){b("<span></span>").addClass("ui-icon "+a.icons.header).prependTo(this.headers);
this.active.children(".ui-icon").toggleClass(a.icons.header).toggleClass(a.icons.headerSelected);this.element.addClass("ui-accordion-icons")
}},_destroyIcons:function(){this.headers.children(".ui-icon").remove();this.element.removeClass("ui-accordion-icons")},destroy:function(){var a=this.options;
this.element.removeClass("ui-accordion ui-widget ui-helper-reset").removeAttr("role");this.headers.unbind(".accordion").removeClass("ui-accordion-header ui-accordion-disabled ui-helper-reset ui-state-default ui-corner-all ui-state-active ui-state-disabled ui-corner-top").removeAttr("role").removeAttr("aria-expanded").removeAttr("aria-selected").removeAttr("tabIndex");
this.headers.find("a").removeAttr("tabIndex");this._destroyIcons();var e=this.headers.next().css("display","").removeAttr("role").removeClass("ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content ui-accordion-content-active ui-accordion-disabled ui-state-disabled");
if(a.autoHeight||a.fillHeight){e.css("height","")}return b.Widget.prototype.destroy.call(this)},_setOption:function(a,e){b.Widget.prototype._setOption.apply(this,arguments);
a=="active"&&this.activate(e);if(a=="icons"){this._destroyIcons();e&&this._createIcons()}if(a=="disabled"){this.headers.add(this.headers.next())[e?"addClass":"removeClass"]("ui-accordion-disabled ui-state-disabled")
}},_keydown:function(l){if(!(this.options.disabled||l.altKey||l.ctrlKey)){var m=b.ui.keyCode,j=this.headers.length,a=this.headers.index(l.target),k=false;
switch(l.keyCode){case m.RIGHT:case m.DOWN:k=this.headers[(a+1)%j];break;case m.LEFT:case m.UP:k=this.headers[(a-1+j)%j];
break;case m.SPACE:case m.ENTER:this._clickHandler({target:l.target},l.target);l.preventDefault()}if(k){b(l.target).attr("tabIndex",-1);
b(k).attr("tabIndex",0);k.focus();return false}return true}},resize:function(){var e=this.options,g;if(e.fillSpace){if(b.browser.msie){var a=this.element.parent().css("overflow");
this.element.parent().css("overflow","hidden")}g=this.element.parent().height();b.browser.msie&&this.element.parent().css("overflow",a);
this.headers.each(function(){g-=b(this).outerHeight(true)});this.headers.next().each(function(){b(this).height(Math.max(0,g-b(this).innerHeight()+b(this).height()))
}).css("overflow","auto")}else{if(e.autoHeight){g=0;this.headers.next().each(function(){g=Math.max(g,b(this).height("").height())
}).height(g)}}return this},activate:function(a){this.options.active=a;a=this._findActive(a)[0];this._clickHandler({target:a},a);
return this},_findActive:function(a){return a?typeof a==="number"?this.headers.filter(":eq("+a+")"):this.headers.not(this.headers.not(a)):a===false?b([]):this.headers.filter(":eq(0)")
},_clickHandler:function(q,r){var m=this.options;if(!m.disabled){if(q.target){q=b(q.currentTarget||r);r=q[0]===this.active[0];
m.active=m.collapsible&&r?false:this.headers.index(q);if(!(this.running||!m.collapsible&&r)){var l=this.active;k=q.next();
j=this.active.next();a={options:m,newHeader:r&&m.collapsible?b([]):q,oldHeader:this.active,newContent:r&&m.collapsible?b([]):k,oldContent:j};
var p=this.headers.index(this.active[0])>this.headers.index(q[0]);this.active=r?b([]):q;this._toggle(k,j,a,r,p);l.removeClass("ui-state-active ui-corner-top").addClass("ui-state-default ui-corner-all").children(".ui-icon").removeClass(m.icons.headerSelected).addClass(m.icons.header);
if(!r){q.removeClass("ui-state-default ui-corner-all").addClass("ui-state-active ui-corner-top").children(".ui-icon").removeClass(m.icons.header).addClass(m.icons.headerSelected);
q.next().addClass("ui-accordion-content-active")}}}else{if(m.collapsible){this.active.removeClass("ui-state-active ui-corner-top").addClass("ui-state-default ui-corner-all").children(".ui-icon").removeClass(m.icons.headerSelected).addClass(m.icons.header);
this.active.next().addClass("ui-accordion-content-active");var j=this.active.next(),a={options:m,newHeader:b([]),oldHeader:m.active,newContent:b([]),oldContent:j},k=this.active=b([]);
this._toggle(k,j,a)}}}},_toggle:function(v,x,r,q,u){var m=this,y=m.options;m.toShow=v;m.toHide=x;m.data=r;var p=function(){if(m){return m._completed.apply(m,arguments)
}};m._trigger("changestart",null,m.data);m.running=x.size()===0?v.size():x.size();if(y.animated){r={};r=y.collapsible&&q?{toShow:b([]),toHide:x,complete:p,down:u,autoHeight:y.autoHeight||y.fillSpace}:{toShow:v,toHide:x,complete:p,down:u,autoHeight:y.autoHeight||y.fillSpace};
if(!y.proxied){y.proxied=y.animated}if(!y.proxiedDuration){y.proxiedDuration=y.duration}y.animated=b.isFunction(y.proxied)?y.proxied(r):y.proxied;
y.duration=b.isFunction(y.proxiedDuration)?y.proxiedDuration(r):y.proxiedDuration;q=b.ui.accordion.animations;var k=y.duration,a=y.animated;
if(a&&!q[a]&&!b.easing[a]){a="slide"}q[a]||(q[a]=function(c){this.slide(c,{easing:a,duration:k||700})});q[a](r)}else{if(y.collapsible&&q){v.toggle()
}else{x.hide();v.show()}p(true)}x.prev().attr({"aria-expanded":"false","aria-selected":"false",tabIndex:-1}).blur();v.prev().attr({"aria-expanded":"true","aria-selected":"true",tabIndex:0}).focus()
},_completed:function(a){this.running=a?0:--this.running;if(!this.running){this.options.clearStyle&&this.toShow.add(this.toHide).css({height:"",overflow:""});
this.toHide.removeClass("ui-accordion-content-active");if(this.toHide.length){this.toHide.parent()[0].className=this.toHide.parent()[0].className
}this._trigger("change",null,this.data)}}});b.extend(b.ui.accordion,{version:"1.8.13",animations:{slide:function(p,q){p=b.extend({easing:"swing",duration:300},p,q);
if(p.toHide.size()){if(p.toShow.size()){var l=p.toShow.css("overflow"),k=0,m={},j={},a;q=p.toShow;a=q[0].style.width;q.width(parseInt(q.parent().width(),10)-parseInt(q.css("paddingLeft"),10)-parseInt(q.css("paddingRight"),10)-(parseInt(q.css("borderLeftWidth"),10)||0)-(parseInt(q.css("borderRightWidth"),10)||0));
b.each(["height","paddingTop","paddingBottom"],function(d,c){j[c]="hide";d=(""+b.css(p.toShow[0],c)).match(/^([\d+-.]+)(.*)$/);
m[c]={value:d[1],unit:d[2]||"px"}});p.toShow.css({height:0,overflow:"hidden"}).show();p.toHide.filter(":hidden").each(p.complete).end().filter(":visible").animate(j,{step:function(d,c){if(c.prop=="height"){k=c.end-c.start===0?0:(c.now-c.start)/(c.end-c.start)
}p.toShow[0].style[c.prop]=k*m[c.prop].value+m[c.prop].unit},duration:p.duration,easing:p.easing,complete:function(){p.autoHeight||p.toShow.css("height","");
p.toShow.css({width:a,overflow:l});p.complete()}})}else{p.toHide.animate({height:"hide",paddingTop:"hide",paddingBottom:"hide"},p)
}}else{p.toShow.animate({height:"show",paddingTop:"show",paddingBottom:"show"},p)}},bounceslide:function(a){this.slide(a,{easing:a.down?"easeOutBounce":"swing",duration:a.down?1000:200})
}}})})(jQuery);(function(b){var c=0;b.widget("ui.autocomplete",{options:{appendTo:"body",autoFocus:false,delay:300,minLength:1,position:{my:"left top",at:"left bottom",collision:"none"},source:null},pending:0,_create:function(){var e=this,d=this.element[0].ownerDocument,a;
this.element.addClass("ui-autocomplete-input").attr("autocomplete","off").attr({role:"textbox","aria-autocomplete":"list","aria-haspopup":"true"}).bind("keydown.autocomplete",function(g){if(!(e.options.disabled||e.element.attr("readonly"))){a=false;
var f=b.ui.keyCode;switch(g.keyCode){case f.PAGE_UP:e._move("previousPage",g);break;case f.PAGE_DOWN:e._move("nextPage",g);
break;case f.UP:e._move("previous",g);g.preventDefault();break;case f.DOWN:e._move("next",g);g.preventDefault();break;case f.ENTER:case f.NUMPAD_ENTER:if(e.menu.active){a=true;
g.preventDefault()}case f.TAB:if(!e.menu.active){return}e.menu.select(g);break;case f.ESCAPE:e.element.val(e.term);e.close(g);
break;default:clearTimeout(e.searching);e.searching=setTimeout(function(){if(e.term!=e.element.val()){e.selectedItem=null;
e.search(null,g)}},e.options.delay);break}}}).bind("keypress.autocomplete",function(f){if(a){a=false;f.preventDefault()}}).bind("focus.autocomplete",function(){if(!e.options.disabled){e.selectedItem=null;
e.previous=e.element.val()}}).bind("blur.autocomplete",function(f){if(!e.options.disabled){clearTimeout(e.searching);e.closing=setTimeout(function(){e.close(f);
e._change(f)},150)}});this._initSource();this.response=function(){return e._response.apply(e,arguments)};this.menu=b("<ul></ul>").addClass("ui-autocomplete").appendTo(b(this.options.appendTo||"body",d)[0]).mousedown(function(g){var f=e.menu.element[0];
b(g.target).closest(".ui-menu-item").length||setTimeout(function(){b(document).one("mousedown",function(j){j.target!==e.element[0]&&j.target!==f&&!b.ui.contains(f,j.target)&&e.close()
})},1);setTimeout(function(){clearTimeout(e.closing)},13)}).menu({focus:function(g,f){f=f.item.data("item.autocomplete");
false!==e._trigger("focus",g,{item:f})&&/^key/.test(g.originalEvent.type)&&e.element.val(f.value)},selected:function(k,g){var f=g.item.data("item.autocomplete"),j=e.previous;
if(e.element[0]!==d.activeElement){e.element.focus();e.previous=j;setTimeout(function(){e.previous=j;e.selectedItem=f},1)
}false!==e._trigger("select",k,{item:f})&&e.element.val(f.value);e.term=e.element.val();e.close(k);e.selectedItem=f},blur:function(){e.menu.element.is(":visible")&&e.element.val()!==e.term&&e.element.val(e.term)
}}).zIndex(this.element.zIndex()+1).css({top:0,left:0}).hide().data("menu");b.fn.bgiframe&&this.menu.element.bgiframe()},destroy:function(){this.element.removeClass("ui-autocomplete-input").removeAttr("autocomplete").removeAttr("role").removeAttr("aria-autocomplete").removeAttr("aria-haspopup");
this.menu.element.remove();b.Widget.prototype.destroy.call(this)},_setOption:function(d,a){b.Widget.prototype._setOption.apply(this,arguments);
d==="source"&&this._initSource();if(d==="appendTo"){this.menu.element.appendTo(b(a||"body",this.element[0].ownerDocument)[0])
}d==="disabled"&&a&&this.xhr&&this.xhr.abort()},_initSource:function(){var e=this,d,a;if(b.isArray(this.options.source)){d=this.options.source;
this.source=function(g,f){f(b.ui.autocomplete.filter(d,g.term))}}else{if(typeof this.options.source==="string"){a=this.options.source;
this.source=function(g,f){e.xhr&&e.xhr.abort();e.xhr=b.ajax({url:a,data:g,dataType:"json",autocompleteRequest:++c,success:function(j){this.autocompleteRequest===c&&f(j)
},error:function(){this.autocompleteRequest===c&&f([])}})}}else{this.source=this.options.source}}},search:function(d,a){d=d!=null?d:this.element.val();
this.term=this.element.val();if(d.length<this.options.minLength){return this.close(a)}clearTimeout(this.closing);if(this._trigger("search",a)!==false){return this._search(d)
}},_search:function(a){this.pending++;this.element.addClass("ui-autocomplete-loading");this.source({term:a},this.response)
},_response:function(a){if(!this.options.disabled&&a&&a.length){a=this._normalize(a);this._suggest(a);this._trigger("open")
}else{this.close()}this.pending--;this.pending||this.element.removeClass("ui-autocomplete-loading")},close:function(a){clearTimeout(this.closing);
if(this.menu.element.is(":visible")){this.menu.element.hide();this.menu.deactivate();this._trigger("close",a)}},_change:function(a){this.previous!==this.element.val()&&this._trigger("change",a,{item:this.selectedItem})
},_normalize:function(a){if(a.length&&a[0].label&&a[0].value){return a}return b.map(a,function(d){if(typeof d==="string"){return{label:d,value:d}
}return b.extend({label:d.label||d.value,value:d.value||d.label},d)})},_suggest:function(d){var a=this.menu.element.empty().zIndex(this.element.zIndex()+1);
this._renderMenu(a,d);this.menu.deactivate();this.menu.refresh();a.show();this._resizeMenu();a.position(b.extend({of:this.element},this.options.position));
this.options.autoFocus&&this.menu.next(new b.Event("mouseover"))},_resizeMenu:function(){var a=this.menu.element;a.outerWidth(Math.max(a.width("").outerWidth(),this.element.outerWidth()))
},_renderMenu:function(e,d){var a=this;b.each(d,function(g,f){a._renderItem(e,f)})},_renderItem:function(d,a){return b("<li></li>").data("item.autocomplete",a).append(b("<a></a>").text(a.label)).appendTo(d)
},_move:function(d,a){if(this.menu.element.is(":visible")){if(this.menu.first()&&/^previous/.test(d)||this.menu.last()&&/^next/.test(d)){this.element.val(this.term);
this.menu.deactivate()}else{this.menu[d](a)}}else{this.search(null,a)}},widget:function(){return this.menu.element}});b.extend(b.ui.autocomplete,{escapeRegex:function(a){return a.replace(/[-[\]{}()*+?.,\\^$|#\s]/g,"\\$&")
},filter:function(e,d){var a=new RegExp(b.ui.autocomplete.escapeRegex(d),"i");return b.grep(e,function(f){return a.test(f.label||f.value||f)
})}})})(jQuery);(function(b){b.widget("ui.menu",{_create:function(){var a=this;this.element.addClass("ui-menu ui-widget ui-widget-content ui-corner-all").attr({role:"listbox","aria-activedescendant":"ui-active-menuitem"}).click(function(d){if(b(d.target).closest(".ui-menu-item a").length){d.preventDefault();
a.select(d)}});this.refresh()},refresh:function(){var a=this;this.element.children("li:not(.ui-menu-item):has(a)").addClass("ui-menu-item").attr("role","menuitem").children("a").addClass("ui-corner-all").attr("tabindex",-1).mouseenter(function(d){a.activate(d,b(this).parent())
}).mouseleave(function(){a.deactivate()})},activate:function(l,m){this.deactivate();if(this.hasScroll()){var j=m.offset().top-this.element.offset().top,a=this.element.scrollTop(),k=this.element.height();
if(j<0){this.element.scrollTop(a+j)}else{j>=k&&this.element.scrollTop(a+j-k+m.height())}}this.active=m.eq(0).children("a").addClass("ui-state-hover").attr("id","ui-active-menuitem").end();
this._trigger("focus",l,{item:m})},deactivate:function(){if(this.active){this.active.children("a").removeClass("ui-state-hover").removeAttr("id");
this._trigger("blur");this.active=null}},next:function(a){this.move("next",".ui-menu-item:first",a)},previous:function(a){this.move("prev",".ui-menu-item:last",a)
},first:function(){return this.active&&!this.active.prevAll(".ui-menu-item").length},last:function(){return this.active&&!this.active.nextAll(".ui-menu-item").length
},move:function(e,g,a){if(this.active){e=this.active[e+"All"](".ui-menu-item").eq(0);e.length?this.activate(a,e):this.activate(a,this.element.children(g))
}else{this.activate(a,this.element.children(g))}},nextPage:function(j){if(this.hasScroll()){if(!this.active||this.last()){this.activate(j,this.element.children(".ui-menu-item:first"))
}else{var k=this.active.offset().top,e=this.element.height(),a=this.element.children(".ui-menu-item").filter(function(){var c=b(this).offset().top-k-e+b(this).height();
return c<10&&c>-10});a.length||(a=this.element.children(".ui-menu-item:last"));this.activate(j,a)}}else{this.activate(j,this.element.children(".ui-menu-item").filter(!this.active||this.last()?":first":":last"))
}},previousPage:function(e){if(this.hasScroll()){if(!this.active||this.first()){this.activate(e,this.element.children(".ui-menu-item:last"))
}else{var g=this.active.offset().top,a=this.element.height();result=this.element.children(".ui-menu-item").filter(function(){var c=b(this).offset().top-g+a-b(this).height();
return c<10&&c>-10});result.length||(result=this.element.children(".ui-menu-item:first"));this.activate(e,result)}}else{this.activate(e,this.element.children(".ui-menu-item").filter(!this.active||this.first()?":last":":first"))
}},hasScroll:function(){return this.element.height()<this.element[b.fn.prop?"prop":"attr"]("scrollHeight")},select:function(a){this._trigger("selected",a,{item:this.active})
}})})(jQuery);(function(b){var g,j=function(a){b(":ui-button",a.target.form).each(function(){var c=b(this).data("button");
setTimeout(function(){c.refresh()},1)})},e=function(d){var f=d.name,c=d.form,a=b([]);if(f){a=c?b(c).find("[name='"+f+"']"):b("[name='"+f+"']",d.ownerDocument).filter(function(){return !this.form
})}return a};b.widget("ui.button",{options:{disabled:null,text:true,label:null,icons:{primary:null,secondary:null}},_create:function(){this.element.closest("form").unbind("reset.button").bind("reset.button",j);
if(typeof this.options.disabled!=="boolean"){this.options.disabled=this.element.attr("disabled")}this._determineButtonType();
this.hasTitle=!!this.buttonElement.attr("title");var d=this,f=this.options,c=this.type==="checkbox"||this.type==="radio",a="ui-state-hover"+(!c?" ui-state-active":"");
if(f.label===null){f.label=this.buttonElement.html()}if(this.element.is(":disabled")){f.disabled=true}this.buttonElement.addClass("ui-button ui-widget ui-state-default ui-corner-all").attr("role","button").bind("mouseenter.button",function(){if(!f.disabled){b(this).addClass("ui-state-hover");
this===g&&b(this).addClass("ui-state-active")}}).bind("mouseleave.button",function(){f.disabled||b(this).removeClass(a)}).bind("focus.button",function(){b(this).addClass("ui-state-focus")
}).bind("blur.button",function(){b(this).removeClass("ui-state-focus")}).bind("click.button",function(k){f.disabled&&k.stopImmediatePropagation()
});c&&this.element.bind("change.button",function(){d.refresh()});if(this.type==="checkbox"){this.buttonElement.bind("click.button",function(){if(f.disabled){return false
}b(this).toggleClass("ui-state-active");d.buttonElement.attr("aria-pressed",d.element[0].checked)})}else{if(this.type==="radio"){this.buttonElement.bind("click.button",function(){if(f.disabled){return false
}b(this).addClass("ui-state-active");d.buttonElement.attr("aria-pressed",true);var k=d.element[0];e(k).not(k).map(function(){return b(this).button("widget")[0]
}).removeClass("ui-state-active").attr("aria-pressed",false)})}else{this.buttonElement.bind("mousedown.button",function(){if(f.disabled){return false
}b(this).addClass("ui-state-active");g=this;b(document).one("mouseup",function(){g=null})}).bind("mouseup.button",function(){if(f.disabled){return false
}b(this).removeClass("ui-state-active")}).bind("keydown.button",function(k){if(f.disabled){return false}if(k.keyCode==b.ui.keyCode.SPACE||k.keyCode==b.ui.keyCode.ENTER){b(this).addClass("ui-state-active")
}}).bind("keyup.button",function(){b(this).removeClass("ui-state-active")});this.buttonElement.is("a")&&this.buttonElement.keyup(function(k){k.keyCode===b.ui.keyCode.SPACE&&b(this).click()
})}}this._setOption("disabled",f.disabled)},_determineButtonType:function(){this.type=this.element.is(":checkbox")?"checkbox":this.element.is(":radio")?"radio":this.element.is("input")?"input":"button";
if(this.type==="checkbox"||this.type==="radio"){var a=this.element.parents().filter(":last"),c="label[for="+this.element.attr("id")+"]";
this.buttonElement=a.find(c);if(!this.buttonElement.length){a=a.length?a.siblings():this.element.siblings();this.buttonElement=a.filter(c);
if(!this.buttonElement.length){this.buttonElement=a.find(c)}}this.element.addClass("ui-helper-hidden-accessible");(a=this.element.is(":checked"))&&this.buttonElement.addClass("ui-state-active");
this.buttonElement.attr("aria-pressed",a)}else{this.buttonElement=this.element}},widget:function(){return this.buttonElement
},destroy:function(){this.element.removeClass("ui-helper-hidden-accessible");this.buttonElement.removeClass("ui-button ui-widget ui-state-default ui-corner-all ui-state-hover ui-state-active  ui-button-icons-only ui-button-icon-only ui-button-text-icons ui-button-text-icon-primary ui-button-text-icon-secondary ui-button-text-only").removeAttr("role").removeAttr("aria-pressed").html(this.buttonElement.find(".ui-button-text").html());
this.hasTitle||this.buttonElement.removeAttr("title");b.Widget.prototype.destroy.call(this)},_setOption:function(a,c){b.Widget.prototype._setOption.apply(this,arguments);
if(a==="disabled"){c?this.element.attr("disabled",true):this.element.removeAttr("disabled")}this._resetButton()},refresh:function(){var a=this.element.is(":disabled");
a!==this.options.disabled&&this._setOption("disabled",a);if(this.type==="radio"){e(this.element[0]).each(function(){b(this).is(":checked")?b(this).button("widget").addClass("ui-state-active").attr("aria-pressed",true):b(this).button("widget").removeClass("ui-state-active").attr("aria-pressed",false)
})}else{if(this.type==="checkbox"){this.element.is(":checked")?this.buttonElement.addClass("ui-state-active").attr("aria-pressed",true):this.buttonElement.removeClass("ui-state-active").attr("aria-pressed",false)
}}},_resetButton:function(){if(this.type==="input"){this.options.label&&this.element.val(this.options.label)}else{var f=this.buttonElement.removeClass("ui-button-icons-only ui-button-icon-only ui-button-text-icons ui-button-text-icon-primary ui-button-text-icon-secondary ui-button-text-only"),k=b("<span></span>").addClass("ui-button-text").html(this.options.label).appendTo(f.empty()).text(),c=this.options.icons,a=c.primary&&c.secondary,d=[];
if(c.primary||c.secondary){if(this.options.text){d.push("ui-button-text-icon"+(a?"s":c.primary?"-primary":"-secondary"))}c.primary&&f.prepend("<span class='ui-button-icon-primary ui-icon "+c.primary+"'></span>");
c.secondary&&f.append("<span class='ui-button-icon-secondary ui-icon "+c.secondary+"'></span>");if(!this.options.text){d.push(a?"ui-button-icons-only":"ui-button-icon-only");
this.hasTitle||f.attr("title",k)}}else{d.push("ui-button-text-only")}f.addClass(d.join(" "))}}});b.widget("ui.buttonset",{options:{items:":button, :submit, :reset, :checkbox, :radio, a, :data(button)"},_create:function(){this.element.addClass("ui-buttonset")
},_init:function(){this.refresh()},_setOption:function(a,c){a==="disabled"&&this.buttons.button("option",a,c);b.Widget.prototype._setOption.apply(this,arguments)
},refresh:function(){this.buttons=this.element.find(this.options.items).filter(":ui-button").button("refresh").end().not(":ui-button").button().end().map(function(){return b(this).button("widget")[0]
}).removeClass("ui-corner-all ui-corner-left ui-corner-right").filter(":first").addClass("ui-corner-left").end().filter(":last").addClass("ui-corner-right").end().end()
},destroy:function(){this.element.removeClass("ui-buttonset");this.buttons.map(function(){return b(this).button("widget")[0]
}).removeClass("ui-corner-left ui-corner-right").end().button("destroy");b.Widget.prototype.destroy.call(this)}})})(jQuery);
(function(a,d){function c(){this.debug=false;this._curInst=null;this._keyEvent=false;this._disabledInputs=[];this._inDialog=this._datepickerShowing=false;
this._mainDivId="ui-datepicker-div";this._inlineClass="ui-datepicker-inline";this._appendClass="ui-datepicker-append";this._triggerClass="ui-datepicker-trigger";
this._dialogClass="ui-datepicker-dialog";this._disableClass="ui-datepicker-disabled";this._unselectableClass="ui-datepicker-unselectable";
this._currentClass="ui-datepicker-current-day";this._dayOverClass="ui-datepicker-days-cell-over";this.regional=[];this.regional[""]={closeText:"Done",prevText:"Prev",nextText:"Next",currentText:"Today",monthNames:["January","February","March","April","May","June","July","August","September","October","November","December"],monthNamesShort:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],dayNames:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],dayNamesShort:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],dayNamesMin:["Su","Mo","Tu","We","Th","Fr","Sa"],weekHeader:"Wk",dateFormat:"mm/dd/yy",firstDay:0,isRTL:false,showMonthAfterYear:false,yearSuffix:""};
this._defaults={showOn:"focus",showAnim:"fadeIn",showOptions:{},defaultDate:null,appendText:"",buttonText:"...",buttonImage:"",buttonImageOnly:false,hideIfNoPrevNext:false,navigationAsDateFormat:false,gotoCurrent:false,changeMonth:false,changeYear:false,yearRange:"c-10:c+10",showOtherMonths:false,selectOtherMonths:false,showWeek:false,calculateWeek:this.iso8601Week,shortYearCutoff:"+10",minDate:null,maxDate:null,duration:"fast",beforeShowDay:null,beforeShow:null,onSelect:null,onChangeMonthYear:null,onClose:null,numberOfMonths:1,showCurrentAtPos:0,stepMonths:1,stepBigMonths:12,altField:"",altFormat:"",constrainInput:true,showButtonPanel:false,autoSize:false};
a.extend(this._defaults,this.regional[""]);this.dpDiv=f(a('<div id="'+this._mainDivId+'" class="ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div>'))
}function f(b){return b.delegate("button, .ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-calendar td a","mouseout",function(){a(this).removeClass("ui-state-hover");
this.className.indexOf("ui-datepicker-prev")!=-1&&a(this).removeClass("ui-datepicker-prev-hover");this.className.indexOf("ui-datepicker-next")!=-1&&a(this).removeClass("ui-datepicker-next-hover")
}).delegate("button, .ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-calendar td a","mouseover",function(){if(!a.datepicker._isDisabledDatepicker(i.inline?b.parent()[0]:i.input[0])){a(this).parents(".ui-datepicker-calendar").find("a").removeClass("ui-state-hover");
a(this).addClass("ui-state-hover");this.className.indexOf("ui-datepicker-prev")!=-1&&a(this).addClass("ui-datepicker-prev-hover");
this.className.indexOf("ui-datepicker-next")!=-1&&a(this).addClass("ui-datepicker-next-hover")}})}function g(b,h){a.extend(b,h);
for(var j in h){if(h[j]==null||h[j]==d){b[j]=h[j]}}return b}a.extend(a.ui,{datepicker:{version:"1.8.13"}});var e=(new Date).getTime(),i;
a.extend(c.prototype,{markerClassName:"hasDatepicker",log:function(){this.debug&&console.log.apply("",arguments)},_widgetDatepicker:function(){return this.dpDiv
},setDefaults:function(b){g(this._defaults,b||{});return this},_attachDatepicker:function(b,h){var j=null;for(var l in this._defaults){var o=b.getAttribute("date:"+l);
if(o){j=j||{};try{j[l]=eval(o)}catch(n){j[l]=o}}}l=b.nodeName.toLowerCase();o=l=="div"||l=="span";if(!b.id){this.uuid+=1;
b.id="dp"+this.uuid}var k=this._newInst(a(b),o);k.settings=a.extend({},h||{},j||{});if(l=="input"){this._connectDatepicker(b,k)
}else{o&&this._inlineDatepicker(b,k)}},_newInst:function(b,h){return{id:b[0].id.replace(/([^A-Za-z0-9_-])/g,"\\\\$1"),input:b,selectedDay:0,selectedMonth:0,selectedYear:0,drawMonth:0,drawYear:0,inline:h,dpDiv:!h?this.dpDiv:f(a('<div class="'+this._inlineClass+' ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div>'))}
},_connectDatepicker:function(b,h){var j=a(b);h.append=a([]);h.trigger=a([]);if(!j.hasClass(this.markerClassName)){this._attachments(j,h);
j.addClass(this.markerClassName).keydown(this._doKeyDown).keypress(this._doKeyPress).keyup(this._doKeyUp).bind("setData.datepicker",function(l,o,n){h.settings[o]=n
}).bind("getData.datepicker",function(l,o){return this._get(h,o)});this._autoSize(h);a.data(b,"datepicker",h)}},_attachments:function(b,h){var j=this._get(h,"appendText"),l=this._get(h,"isRTL");
h.append&&h.append.remove();if(j){h.append=a('<span class="'+this._appendClass+'">'+j+"</span>");b[l?"before":"after"](h.append)
}b.unbind("focus",this._showDatepicker);h.trigger&&h.trigger.remove();j=this._get(h,"showOn");if(j=="focus"||j=="both"){b.focus(this._showDatepicker)
}if(j=="button"||j=="both"){j=this._get(h,"buttonText");var o=this._get(h,"buttonImage");h.trigger=a(this._get(h,"buttonImageOnly")?a("<img/>").addClass(this._triggerClass).attr({src:o,alt:j,title:j}):a('<button type="button"></button>').addClass(this._triggerClass).html(o==""?j:a("<img/>").attr({src:o,alt:j,title:j})));
b[l?"before":"after"](h.trigger);h.trigger.click(function(){a.datepicker._datepickerShowing&&a.datepicker._lastInput==b[0]?a.datepicker._hideDatepicker():a.datepicker._showDatepicker(b[0]);
return false})}},_autoSize:function(b){if(this._get(b,"autoSize")&&!b.inline){var h=new Date(2009,11,20),j=this._get(b,"dateFormat");
if(j.match(/[DM]/)){var l=function(o){for(var n=0,k=0,m=0;m<o.length;m++){if(o[m].length>n){n=o[m].length;k=m}}return k};
h.setMonth(l(this._get(b,j.match(/MM/)?"monthNames":"monthNamesShort")));h.setDate(l(this._get(b,j.match(/DD/)?"dayNames":"dayNamesShort"))+20-h.getDay())
}b.input.attr("size",this._formatDate(b,h).length)}},_inlineDatepicker:function(b,h){var j=a(b);if(!j.hasClass(this.markerClassName)){j.addClass(this.markerClassName).append(h.dpDiv).bind("setData.datepicker",function(l,o,n){h.settings[o]=n
}).bind("getData.datepicker",function(l,o){return this._get(h,o)});a.data(b,"datepicker",h);this._setDate(h,this._getDefaultDate(h),true);
this._updateDatepicker(h);this._updateAlternate(h);h.dpDiv.show()}},_dialogDatepicker:function(b,h,j,l,o){b=this._dialogInst;
if(!b){this.uuid+=1;this._dialogInput=a('<input type="text" id="'+("dp"+this.uuid)+'" style="position: absolute; top: -100px; width: 0px; z-index: -10;"/>');
this._dialogInput.keydown(this._doKeyDown);a("body").append(this._dialogInput);b=this._dialogInst=this._newInst(this._dialogInput,false);
b.settings={};a.data(this._dialogInput[0],"datepicker",b)}g(b.settings,l||{});h=h&&h.constructor==Date?this._formatDate(b,h):h;
this._dialogInput.val(h);this._pos=o?o.length?o:[o.pageX,o.pageY]:null;if(!this._pos){this._pos=[document.documentElement.clientWidth/2-100+(document.documentElement.scrollLeft||document.body.scrollLeft),document.documentElement.clientHeight/2-150+(document.documentElement.scrollTop||document.body.scrollTop)]
}this._dialogInput.css("left",this._pos[0]+20+"px").css("top",this._pos[1]+"px");b.settings.onSelect=j;this._inDialog=true;
this.dpDiv.addClass(this._dialogClass);this._showDatepicker(this._dialogInput[0]);a.blockUI&&a.blockUI(this.dpDiv);a.data(this._dialogInput[0],"datepicker",b);
return this},_destroyDatepicker:function(b){var h=a(b),j=a.data(b,"datepicker");if(h.hasClass(this.markerClassName)){var l=b.nodeName.toLowerCase();
a.removeData(b,"datepicker");if(l=="input"){j.append.remove();j.trigger.remove();h.removeClass(this.markerClassName).unbind("focus",this._showDatepicker).unbind("keydown",this._doKeyDown).unbind("keypress",this._doKeyPress).unbind("keyup",this._doKeyUp)
}else{if(l=="div"||l=="span"){h.removeClass(this.markerClassName).empty()}}}},_enableDatepicker:function(b){var h=a(b),j=a.data(b,"datepicker");
if(h.hasClass(this.markerClassName)){var l=b.nodeName.toLowerCase();if(l=="input"){b.disabled=false;j.trigger.filter("button").each(function(){this.disabled=false
}).end().filter("img").css({opacity:"1.0",cursor:""})}else{if(l=="div"||l=="span"){h=h.children("."+this._inlineClass);h.children().removeClass("ui-state-disabled");
h.find("select.ui-datepicker-month, select.ui-datepicker-year").removeAttr("disabled")}}this._disabledInputs=a.map(this._disabledInputs,function(o){return o==b?null:o
})}},_disableDatepicker:function(b){var h=a(b),j=a.data(b,"datepicker");if(h.hasClass(this.markerClassName)){var l=b.nodeName.toLowerCase();
if(l=="input"){b.disabled=true;j.trigger.filter("button").each(function(){this.disabled=true}).end().filter("img").css({opacity:"0.5",cursor:"default"})
}else{if(l=="div"||l=="span"){h=h.children("."+this._inlineClass);h.children().addClass("ui-state-disabled");h.find("select.ui-datepicker-month, select.ui-datepicker-year").attr("disabled","disabled")
}}this._disabledInputs=a.map(this._disabledInputs,function(o){return o==b?null:o});this._disabledInputs[this._disabledInputs.length]=b
}},_isDisabledDatepicker:function(b){if(!b){return false}for(var h=0;h<this._disabledInputs.length;h++){if(this._disabledInputs[h]==b){return true
}}return false},_getInst:function(b){try{return a.data(b,"datepicker")}catch(h){throw"Missing instance data for this datepicker"
}},_optionDatepicker:function(b,h,j){var l=this._getInst(b);if(arguments.length==2&&typeof h=="string"){return h=="defaults"?a.extend({},a.datepicker._defaults):l?h=="all"?a.extend({},l.settings):this._get(l,h):null
}var o=h||{};if(typeof h=="string"){o={};o[h]=j}if(l){this._curInst==l&&this._hideDatepicker();var n=this._getDateDatepicker(b,true),k=this._getMinMaxDate(l,"min"),m=this._getMinMaxDate(l,"max");
g(l.settings,o);if(k!==null&&o.dateFormat!==d&&o.minDate===d){l.settings.minDate=this._formatDate(l,k)}if(m!==null&&o.dateFormat!==d&&o.maxDate===d){l.settings.maxDate=this._formatDate(l,m)
}this._attachments(a(b),l);this._autoSize(l);this._setDate(l,n);this._updateAlternate(l);this._updateDatepicker(l)}},_changeDatepicker:function(b,h,j){this._optionDatepicker(b,h,j)
},_refreshDatepicker:function(b){(b=this._getInst(b))&&this._updateDatepicker(b)},_setDateDatepicker:function(b,h){if(b=this._getInst(b)){this._setDate(b,h);
this._updateDatepicker(b);this._updateAlternate(b)}},_getDateDatepicker:function(b,h){(b=this._getInst(b))&&!b.inline&&this._setDateFromField(b,h);
return b?this._getDate(b):null},_doKeyDown:function(b){var h=a.datepicker._getInst(b.target),j=true,l=h.dpDiv.is(".ui-datepicker-rtl");
h._keyEvent=true;if(a.datepicker._datepickerShowing){switch(b.keyCode){case 9:a.datepicker._hideDatepicker();j=false;break;
case 13:j=a("td."+a.datepicker._dayOverClass+":not(."+a.datepicker._currentClass+")",h.dpDiv);j[0]?a.datepicker._selectDay(b.target,h.selectedMonth,h.selectedYear,j[0]):a.datepicker._hideDatepicker();
return false;case 27:a.datepicker._hideDatepicker();break;case 33:a.datepicker._adjustDate(b.target,b.ctrlKey?-a.datepicker._get(h,"stepBigMonths"):-a.datepicker._get(h,"stepMonths"),"M");
break;case 34:a.datepicker._adjustDate(b.target,b.ctrlKey?+a.datepicker._get(h,"stepBigMonths"):+a.datepicker._get(h,"stepMonths"),"M");
break;case 35:if(b.ctrlKey||b.metaKey){a.datepicker._clearDate(b.target)}j=b.ctrlKey||b.metaKey;break;case 36:if(b.ctrlKey||b.metaKey){a.datepicker._gotoToday(b.target)
}j=b.ctrlKey||b.metaKey;break;case 37:if(b.ctrlKey||b.metaKey){a.datepicker._adjustDate(b.target,l?+1:-1,"D")}j=b.ctrlKey||b.metaKey;
if(b.originalEvent.altKey){a.datepicker._adjustDate(b.target,b.ctrlKey?-a.datepicker._get(h,"stepBigMonths"):-a.datepicker._get(h,"stepMonths"),"M")
}break;case 38:if(b.ctrlKey||b.metaKey){a.datepicker._adjustDate(b.target,-7,"D")}j=b.ctrlKey||b.metaKey;break;case 39:if(b.ctrlKey||b.metaKey){a.datepicker._adjustDate(b.target,l?-1:+1,"D")
}j=b.ctrlKey||b.metaKey;if(b.originalEvent.altKey){a.datepicker._adjustDate(b.target,b.ctrlKey?+a.datepicker._get(h,"stepBigMonths"):+a.datepicker._get(h,"stepMonths"),"M")
}break;case 40:if(b.ctrlKey||b.metaKey){a.datepicker._adjustDate(b.target,+7,"D")}j=b.ctrlKey||b.metaKey;break;default:j=false
}}else{if(b.keyCode==36&&b.ctrlKey){a.datepicker._showDatepicker(this)}else{j=false}}if(j){b.preventDefault();b.stopPropagation()
}},_doKeyPress:function(b){var h=a.datepicker._getInst(b.target);if(a.datepicker._get(h,"constrainInput")){h=a.datepicker._possibleChars(a.datepicker._get(h,"dateFormat"));
var j=String.fromCharCode(b.charCode==d?b.keyCode:b.charCode);return b.ctrlKey||b.metaKey||j<" "||!h||h.indexOf(j)>-1}},_doKeyUp:function(b){b=a.datepicker._getInst(b.target);
if(b.input.val()!=b.lastVal){try{if(a.datepicker.parseDate(a.datepicker._get(b,"dateFormat"),b.input?b.input.val():null,a.datepicker._getFormatConfig(b))){a.datepicker._setDateFromField(b);
a.datepicker._updateAlternate(b);a.datepicker._updateDatepicker(b)}}catch(h){a.datepicker.log(h)}}return true},_showDatepicker:function(b){b=b.target||b;
if(b.nodeName.toLowerCase()!="input"){b=a("input",b.parentNode)[0]}if(!(a.datepicker._isDisabledDatepicker(b)||a.datepicker._lastInput==b)){var h=a.datepicker._getInst(b);
a.datepicker._curInst&&a.datepicker._curInst!=h&&a.datepicker._curInst.dpDiv.stop(true,true);var j=a.datepicker._get(h,"beforeShow");
g(h.settings,j?j.apply(b,[b,h]):{});h.lastVal=null;a.datepicker._lastInput=b;a.datepicker._setDateFromField(h);if(a.datepicker._inDialog){b.value=""
}if(!a.datepicker._pos){a.datepicker._pos=a.datepicker._findPos(b);a.datepicker._pos[1]+=b.offsetHeight}var l=false;a(b).parents().each(function(){l|=a(this).css("position")=="fixed";
return !l});if(l&&a.browser.opera){a.datepicker._pos[0]-=document.documentElement.scrollLeft;a.datepicker._pos[1]-=document.documentElement.scrollTop
}j={left:a.datepicker._pos[0],top:a.datepicker._pos[1]};a.datepicker._pos=null;h.dpDiv.empty();h.dpDiv.css({position:"absolute",display:"block",top:"-1000px"});
a.datepicker._updateDatepicker(h);j=a.datepicker._checkOffset(h,j,l);h.dpDiv.css({position:a.datepicker._inDialog&&a.blockUI?"static":l?"fixed":"absolute",display:"none",left:j.left+"px",top:j.top+"px"});
if(!h.inline){j=a.datepicker._get(h,"showAnim");var o=a.datepicker._get(h,"duration"),n=function(){var k=h.dpDiv.find("iframe.ui-datepicker-cover");
if(k.length){var m=a.datepicker._getBorders(h.dpDiv);k.css({left:-m[0],top:-m[1],width:h.dpDiv.outerWidth(),height:h.dpDiv.outerHeight()})
}};h.dpDiv.zIndex(a(b).zIndex()+1);a.datepicker._datepickerShowing=true;a.effects&&a.effects[j]?h.dpDiv.show(j,a.datepicker._get(h,"showOptions"),o,n):h.dpDiv[j||"show"](j?o:null,n);
if(!j||!o){n()}h.input.is(":visible")&&!h.input.is(":disabled")&&h.input.focus();a.datepicker._curInst=h}}},_updateDatepicker:function(b){var h=a.datepicker._getBorders(b.dpDiv);
i=b;b.dpDiv.empty().append(this._generateHTML(b));var j=b.dpDiv.find("iframe.ui-datepicker-cover");j.length&&j.css({left:-h[0],top:-h[1],width:b.dpDiv.outerWidth(),height:b.dpDiv.outerHeight()});
b.dpDiv.find("."+this._dayOverClass+" a").mouseover();h=this._getNumberOfMonths(b);j=h[1];b.dpDiv.removeClass("ui-datepicker-multi-2 ui-datepicker-multi-3 ui-datepicker-multi-4").width("");
j>1&&b.dpDiv.addClass("ui-datepicker-multi-"+j).css("width",17*j+"em");b.dpDiv[(h[0]!=1||h[1]!=1?"add":"remove")+"Class"]("ui-datepicker-multi");
b.dpDiv[(this._get(b,"isRTL")?"add":"remove")+"Class"]("ui-datepicker-rtl");b==a.datepicker._curInst&&a.datepicker._datepickerShowing&&b.input&&b.input.is(":visible")&&!b.input.is(":disabled")&&b.input[0]!=document.activeElement&&b.input.focus();
if(b.yearshtml){var l=b.yearshtml;setTimeout(function(){l===b.yearshtml&&b.yearshtml&&b.dpDiv.find("select.ui-datepicker-year:first").replaceWith(b.yearshtml);
l=b.yearshtml=null},0)}},_getBorders:function(b){var h=function(j){return{thin:1,medium:2,thick:3}[j]||j};return[parseFloat(h(b.css("border-left-width"))),parseFloat(h(b.css("border-top-width")))]
},_checkOffset:function(b,h,j){var l=b.dpDiv.outerWidth(),o=b.dpDiv.outerHeight(),n=b.input?b.input.outerWidth():0,k=b.input?b.input.outerHeight():0,m=document.documentElement.clientWidth+a(document).scrollLeft(),p=document.documentElement.clientHeight+a(document).scrollTop();
h.left-=this._get(b,"isRTL")?l-n:0;h.left-=j&&h.left==b.input.offset().left?a(document).scrollLeft():0;h.top-=j&&h.top==b.input.offset().top+k?a(document).scrollTop():0;
h.left-=Math.min(h.left,h.left+l>m&&m>l?Math.abs(h.left+l-m):0);h.top-=Math.min(h.top,h.top+o>p&&p>o?Math.abs(o+k):0);return h
},_findPos:function(b){for(var h=this._get(this._getInst(b),"isRTL");b&&(b.type=="hidden"||b.nodeType!=1||a.expr.filters.hidden(b));
){b=b[h?"previousSibling":"nextSibling"]}b=a(b).offset();return[b.left,b.top]},_hideDatepicker:function(b){var h=this._curInst;
if(!(!h||b&&h!=a.data(b,"datepicker"))){if(this._datepickerShowing){b=this._get(h,"showAnim");var j=this._get(h,"duration"),l=function(){a.datepicker._tidyDialog(h);
this._curInst=null};a.effects&&a.effects[b]?h.dpDiv.hide(b,a.datepicker._get(h,"showOptions"),j,l):h.dpDiv[b=="slideDown"?"slideUp":b=="fadeIn"?"fadeOut":"hide"](b?j:null,l);
b||l();if(b=this._get(h,"onClose")){b.apply(h.input?h.input[0]:null,[h.input?h.input.val():"",h])}this._datepickerShowing=false;
this._lastInput=null;if(this._inDialog){this._dialogInput.css({position:"absolute",left:"0",top:"-100px"});if(a.blockUI){a.unblockUI();
a("body").append(this.dpDiv)}}this._inDialog=false}}},_tidyDialog:function(b){b.dpDiv.removeClass(this._dialogClass).unbind(".ui-datepicker-calendar")
},_checkExternalClick:function(b){if(a.datepicker._curInst){b=a(b.target);b[0].id!=a.datepicker._mainDivId&&b.parents("#"+a.datepicker._mainDivId).length==0&&!b.hasClass(a.datepicker.markerClassName)&&!b.hasClass(a.datepicker._triggerClass)&&a.datepicker._datepickerShowing&&!(a.datepicker._inDialog&&a.blockUI)&&a.datepicker._hideDatepicker()
}},_adjustDate:function(b,h,j){b=a(b);var l=this._getInst(b[0]);if(!this._isDisabledDatepicker(b[0])){this._adjustInstDate(l,h+(j=="M"?this._get(l,"showCurrentAtPos"):0),j);
this._updateDatepicker(l)}},_gotoToday:function(b){b=a(b);var h=this._getInst(b[0]);if(this._get(h,"gotoCurrent")&&h.currentDay){h.selectedDay=h.currentDay;
h.drawMonth=h.selectedMonth=h.currentMonth;h.drawYear=h.selectedYear=h.currentYear}else{var j=new Date;h.selectedDay=j.getDate();
h.drawMonth=h.selectedMonth=j.getMonth();h.drawYear=h.selectedYear=j.getFullYear()}this._notifyChange(h);this._adjustDate(b)
},_selectMonthYear:function(b,h,j){b=a(b);var l=this._getInst(b[0]);l._selectingMonthYear=false;l["selected"+(j=="M"?"Month":"Year")]=l["draw"+(j=="M"?"Month":"Year")]=parseInt(h.options[h.selectedIndex].value,10);
this._notifyChange(l);this._adjustDate(b)},_clickMonthYear:function(b){var h=this._getInst(a(b)[0]);h.input&&h._selectingMonthYear&&setTimeout(function(){h.input.focus()
},0);h._selectingMonthYear=!h._selectingMonthYear},_selectDay:function(b,h,j,l){var o=a(b);if(!(a(l).hasClass(this._unselectableClass)||this._isDisabledDatepicker(o[0]))){o=this._getInst(o[0]);
o.selectedDay=o.currentDay=a("a",l).html();o.selectedMonth=o.currentMonth=h;o.selectedYear=o.currentYear=j;this._selectDate(b,this._formatDate(o,o.currentDay,o.currentMonth,o.currentYear))
}},_clearDate:function(b){b=a(b);this._getInst(b[0]);this._selectDate(b,"")},_selectDate:function(b,h){b=this._getInst(a(b)[0]);
h=h!=null?h:this._formatDate(b);b.input&&b.input.val(h);this._updateAlternate(b);var j=this._get(b,"onSelect");if(j){j.apply(b.input?b.input[0]:null,[h,b])
}else{b.input&&b.input.trigger("change")}if(b.inline){this._updateDatepicker(b)}else{this._hideDatepicker();this._lastInput=b.input[0];
typeof b.input[0]!="object"&&b.input.focus();this._lastInput=null}},_updateAlternate:function(b){var h=this._get(b,"altField");
if(h){var j=this._get(b,"altFormat")||this._get(b,"dateFormat"),l=this._getDate(b),o=this.formatDate(j,l,this._getFormatConfig(b));
a(h).each(function(){a(this).val(o)})}},noWeekends:function(b){b=b.getDay();return[b>0&&b<6,""]},iso8601Week:function(b){b=new Date(b.getTime());
b.setDate(b.getDate()+4-(b.getDay()||7));var h=b.getTime();b.setMonth(0);b.setDate(1);return Math.floor(Math.round((h-b)/86400000)/7)+1
},parseDate:function(b,h,j){if(b==null||h==null){throw"Invalid arguments"}h=typeof h=="object"?h.toString():h+"";if(h==""){return null
}var l=(j?j.shortYearCutoff:null)||this._defaults.shortYearCutoff;l=typeof l!="string"?l:(new Date).getFullYear()%100+parseInt(l,10);
for(var o=(j?j.dayNamesShort:null)||this._defaults.dayNamesShort,n=(j?j.dayNames:null)||this._defaults.dayNames,k=(j?j.monthNamesShort:null)||this._defaults.monthNamesShort,m=(j?j.monthNames:null)||this._defaults.monthNames,p=j=-1,q=-1,s=-1,r=false,u=function(y){(y=G+1<b.length&&b.charAt(G+1)==y)&&G++;
return y},v=function(y){var H=u(y);y=new RegExp("^\\d{1,"+(y=="@"?14:y=="!"?20:y=="y"&&H?4:y=="o"?3:2)+"}");y=h.substring(z).match(y);
if(!y){throw"Missing number at position "+z}z+=y[0].length;return parseInt(y[0],10)},w=function(y,H,N){y=a.map(u(y)?N:H,function(D,E){return[[E,D]]
}).sort(function(D,E){return -(D[1].length-E[1].length)});var J=-1;a.each(y,function(D,E){D=E[1];if(h.substr(z,D.length).toLowerCase()==D.toLowerCase()){J=E[0];
z+=D.length;return false}});if(J!=-1){return J+1}else{throw"Unknown name at position "+z}},x=function(){if(h.charAt(z)!=b.charAt(G)){throw"Unexpected literal at position "+z
}z++},z=0,G=0;G<b.length;G++){if(r){if(b.charAt(G)=="'"&&!u("'")){r=false}else{x()}}else{switch(b.charAt(G)){case"d":q=v("d");
break;case"D":w("D",o,n);break;case"o":s=v("o");break;case"m":p=v("m");break;case"M":p=w("M",k,m);break;case"y":j=v("y");
break;case"@":var C=new Date(v("@"));j=C.getFullYear();p=C.getMonth()+1;q=C.getDate();break;case"!":C=new Date((v("!")-this._ticksTo1970)/10000);
j=C.getFullYear();p=C.getMonth()+1;q=C.getDate();break;case"'":if(u("'")){x()}else{r=true}break;default:x()}}}if(j==-1){j=(new Date).getFullYear()
}else{if(j<100){j+=(new Date).getFullYear()-(new Date).getFullYear()%100+(j<=l?0:-100)}}if(s>-1){p=1;q=s;do{l=this._getDaysInMonth(j,p-1);
if(q<=l){break}p++;q-=l}while(1)}C=this._daylightSavingAdjust(new Date(j,p-1,q));if(C.getFullYear()!=j||C.getMonth()+1!=p||C.getDate()!=q){throw"Invalid date"
}return C},ATOM:"yy-mm-dd",COOKIE:"D, dd M yy",ISO_8601:"yy-mm-dd",RFC_822:"D, d M y",RFC_850:"DD, dd-M-y",RFC_1036:"D, d M y",RFC_1123:"D, d M yy",RFC_2822:"D, d M yy",RSS:"D, d M y",TICKS:"!",TIMESTAMP:"@",W3C:"yy-mm-dd",_ticksTo1970:(718685+Math.floor(492.5)-Math.floor(19.7)+Math.floor(4.925))*24*60*60*10000000,formatDate:function(b,h,j){if(!h){return""
}var l=(j?j.dayNamesShort:null)||this._defaults.dayNamesShort,o=(j?j.dayNames:null)||this._defaults.dayNames,n=(j?j.monthNamesShort:null)||this._defaults.monthNamesShort;
j=(j?j.monthNames:null)||this._defaults.monthNames;var k=function(u){(u=r+1<b.length&&b.charAt(r+1)==u)&&r++;return u},m=function(u,v,w){v=""+v;
if(k(u)){for(;v.length<w;){v="0"+v}}return v},p=function(u,v,w,x){return k(u)?x[v]:w[v]},q="",s=false;if(h){for(var r=0;r<b.length;
r++){if(s){if(b.charAt(r)=="'"&&!k("'")){s=false}else{q+=b.charAt(r)}}else{switch(b.charAt(r)){case"d":q+=m("d",h.getDate(),2);
break;case"D":q+=p("D",h.getDay(),l,o);break;case"o":q+=m("o",(h.getTime()-(new Date(h.getFullYear(),0,0)).getTime())/86400000,3);
break;case"m":q+=m("m",h.getMonth()+1,2);break;case"M":q+=p("M",h.getMonth(),n,j);break;case"y":q+=k("y")?h.getFullYear():(h.getYear()%100<10?"0":"")+h.getYear()%100;
break;case"@":q+=h.getTime();break;case"!":q+=h.getTime()*10000+this._ticksTo1970;break;case"'":if(k("'")){q+="'"}else{s=true
}break;default:q+=b.charAt(r)}}}}return q},_possibleChars:function(b){for(var h="",j=false,l=function(n){(n=o+1<b.length&&b.charAt(o+1)==n)&&o++;
return n},o=0;o<b.length;o++){if(j){if(b.charAt(o)=="'"&&!l("'")){j=false}else{h+=b.charAt(o)}}else{switch(b.charAt(o)){case"d":case"m":case"y":case"@":h+="0123456789";
break;case"D":case"M":return null;case"'":if(l("'")){h+="'"}else{j=true}break;default:h+=b.charAt(o)}}}return h},_get:function(b,h){return b.settings[h]!==d?b.settings[h]:this._defaults[h]
},_setDateFromField:function(b,h){if(b.input.val()!=b.lastVal){var j=this._get(b,"dateFormat"),l=b.lastVal=b.input?b.input.val():null,o,n;
o=n=this._getDefaultDate(b);var k=this._getFormatConfig(b);try{o=this.parseDate(j,l,k)||n}catch(m){this.log(m);l=h?"":l}b.selectedDay=o.getDate();
b.drawMonth=b.selectedMonth=o.getMonth();b.drawYear=b.selectedYear=o.getFullYear();b.currentDay=l?o.getDate():0;b.currentMonth=l?o.getMonth():0;
b.currentYear=l?o.getFullYear():0;this._adjustInstDate(b)}},_getDefaultDate:function(b){return this._restrictMinMax(b,this._determineDate(b,this._get(b,"defaultDate"),new Date))
},_determineDate:function(b,h,j){var l=function(n){var k=new Date;k.setDate(k.getDate()+n);return k},o=function(n){try{return a.datepicker.parseDate(a.datepicker._get(b,"dateFormat"),n,a.datepicker._getFormatConfig(b))
}catch(k){}var m=(n.toLowerCase().match(/^c/)?a.datepicker._getDate(b):null)||new Date,p=m.getFullYear(),q=m.getMonth();m=m.getDate();
for(var s=/([+-]?[0-9]+)\s*(d|D|w|W|m|M|y|Y)?/g,r=s.exec(n);r;){switch(r[2]||"d"){case"d":case"D":m+=parseInt(r[1],10);break;
case"w":case"W":m+=parseInt(r[1],10)*7;break;case"m":case"M":q+=parseInt(r[1],10);m=Math.min(m,a.datepicker._getDaysInMonth(p,q));
break;case"y":case"Y":p+=parseInt(r[1],10);m=Math.min(m,a.datepicker._getDaysInMonth(p,q));break}r=s.exec(n)}return new Date(p,q,m)
};if(h=(h=h==null||h===""?j:typeof h=="string"?o(h):typeof h=="number"?isNaN(h)?j:l(h):new Date(h.getTime()))&&h.toString()=="Invalid Date"?j:h){h.setHours(0);
h.setMinutes(0);h.setSeconds(0);h.setMilliseconds(0)}return this._daylightSavingAdjust(h)},_daylightSavingAdjust:function(b){if(!b){return null
}b.setHours(b.getHours()>12?b.getHours()+2:0);return b},_setDate:function(b,h,j){var l=!h,o=b.selectedMonth,n=b.selectedYear;
h=this._restrictMinMax(b,this._determineDate(b,h,new Date));b.selectedDay=b.currentDay=h.getDate();b.drawMonth=b.selectedMonth=b.currentMonth=h.getMonth();
b.drawYear=b.selectedYear=b.currentYear=h.getFullYear();if((o!=b.selectedMonth||n!=b.selectedYear)&&!j){this._notifyChange(b)
}this._adjustInstDate(b);if(b.input){b.input.val(l?"":this._formatDate(b))}},_getDate:function(b){return !b.currentYear||b.input&&b.input.val()==""?null:this._daylightSavingAdjust(new Date(b.currentYear,b.currentMonth,b.currentDay))
},_generateHTML:function(b){var h=new Date;h=this._daylightSavingAdjust(new Date(h.getFullYear(),h.getMonth(),h.getDate()));
var j=this._get(b,"isRTL"),l=this._get(b,"showButtonPanel"),o=this._get(b,"hideIfNoPrevNext"),n=this._get(b,"navigationAsDateFormat"),k=this._getNumberOfMonths(b),m=this._get(b,"showCurrentAtPos"),p=this._get(b,"stepMonths"),q=k[0]!=1||k[1]!=1,s=this._daylightSavingAdjust(!b.currentDay?new Date(9999,9,9):new Date(b.currentYear,b.currentMonth,b.currentDay)),r=this._getMinMaxDate(b,"min"),u=this._getMinMaxDate(b,"max");
m=b.drawMonth-m;var v=b.drawYear;if(m<0){m+=12;v--}if(u){var w=this._daylightSavingAdjust(new Date(u.getFullYear(),u.getMonth()-k[0]*k[1]+1,u.getDate()));
for(w=r&&w<r?r:w;this._daylightSavingAdjust(new Date(v,m,1))>w;){m--;if(m<0){m=11;v--}}}b.drawMonth=m;b.drawYear=v;w=this._get(b,"prevText");
w=!n?w:this.formatDate(w,this._daylightSavingAdjust(new Date(v,m-p,1)),this._getFormatConfig(b));w=this._canAdjustMonth(b,-1,v,m)?'<a class="ui-datepicker-prev ui-corner-all" onclick="DP_jQuery_'+e+".datepicker._adjustDate('#"+b.id+"', -"+p+", 'M');\" title=\""+w+'"><span class="ui-icon ui-icon-circle-triangle-'+(j?"e":"w")+'">'+w+"</span></a>":o?"":'<a class="ui-datepicker-prev ui-corner-all ui-state-disabled" title="'+w+'"><span class="ui-icon ui-icon-circle-triangle-'+(j?"e":"w")+'">'+w+"</span></a>";
var x=this._get(b,"nextText");x=!n?x:this.formatDate(x,this._daylightSavingAdjust(new Date(v,m+p,1)),this._getFormatConfig(b));
o=this._canAdjustMonth(b,+1,v,m)?'<a class="ui-datepicker-next ui-corner-all" onclick="DP_jQuery_'+e+".datepicker._adjustDate('#"+b.id+"', +"+p+", 'M');\" title=\""+x+'"><span class="ui-icon ui-icon-circle-triangle-'+(j?"w":"e")+'">'+x+"</span></a>":o?"":'<a class="ui-datepicker-next ui-corner-all ui-state-disabled" title="'+x+'"><span class="ui-icon ui-icon-circle-triangle-'+(j?"w":"e")+'">'+x+"</span></a>";
p=this._get(b,"currentText");x=this._get(b,"gotoCurrent")&&b.currentDay?s:h;p=!n?p:this.formatDate(p,x,this._getFormatConfig(b));
n=!b.inline?'<button type="button" class="ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all" onclick="DP_jQuery_'+e+'.datepicker._hideDatepicker();">'+this._get(b,"closeText")+"</button>":"";
l=l?'<div class="ui-datepicker-buttonpane ui-widget-content">'+(j?n:"")+(this._isInRange(b,x)?'<button type="button" class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" onclick="DP_jQuery_'+e+".datepicker._gotoToday('#"+b.id+"');\">"+p+"</button>":"")+(j?"":n)+"</div>":"";
n=parseInt(this._get(b,"firstDay"),10);n=isNaN(n)?0:n;p=this._get(b,"showWeek");x=this._get(b,"dayNames");this._get(b,"dayNamesShort");
var z=this._get(b,"dayNamesMin"),G=this._get(b,"monthNames"),C=this._get(b,"monthNamesShort"),y=this._get(b,"beforeShowDay"),H=this._get(b,"showOtherMonths"),N=this._get(b,"selectOtherMonths");
this._get(b,"calculateWeek");for(var J=this._getDefaultDate(b),D="",E=0;E<k[0];E++){for(var P="",L=0;L<k[1];L++){var Q=this._daylightSavingAdjust(new Date(v,m,b.selectedDay)),B=" ui-corner-all",F="";
if(q){F+='<div class="ui-datepicker-group';if(k[1]>1){switch(L){case 0:F+=" ui-datepicker-group-first";B=" ui-corner-"+(j?"right":"left");
break;case k[1]-1:F+=" ui-datepicker-group-last";B=" ui-corner-"+(j?"left":"right");break;default:F+=" ui-datepicker-group-middle";
B="";break}}F+='">'}F+='<div class="ui-datepicker-header ui-widget-header ui-helper-clearfix'+B+'">'+(/all|left/.test(B)&&E==0?j?o:w:"")+(/all|right/.test(B)&&E==0?j?w:o:"")+this._generateMonthYearHeader(b,m,v,r,u,E>0||L>0,G,C)+'</div><table class="ui-datepicker-calendar"><thead><tr>';
var I=p?'<th class="ui-datepicker-week-col">'+this._get(b,"weekHeader")+"</th>":"";for(B=0;B<7;B++){var A=(B+n)%7;I+="<th"+((B+n+6)%7>=5?' class="ui-datepicker-week-end"':"")+'><span title="'+x[A]+'">'+z[A]+"</span></th>"
}F+=I+"</tr></thead><tbody>";I=this._getDaysInMonth(v,m);if(v==b.selectedYear&&m==b.selectedMonth){b.selectedDay=Math.min(b.selectedDay,I)
}B=(this._getFirstDayOfMonth(v,m)-n+7)%7;I=q?6:Math.ceil((B+I)/7);A=this._daylightSavingAdjust(new Date(v,m,1-B));for(var R=0;
R<I;R++){F+="<tr>";var S=!p?"":'<td class="ui-datepicker-week-col">'+this._get(b,"calculateWeek")(A)+"</td>";for(B=0;B<7;
B++){var M=y?y.apply(b.input?b.input[0]:null,[A]):[true,""],K=A.getMonth()!=m,O=K&&!N||!M[0]||r&&A<r||u&&A>u;S+='<td class="'+((B+n+6)%7>=5?" ui-datepicker-week-end":"")+(K?" ui-datepicker-other-month":"")+(A.getTime()==Q.getTime()&&m==b.selectedMonth&&b._keyEvent||J.getTime()==A.getTime()&&J.getTime()==Q.getTime()?" "+this._dayOverClass:"")+(O?" "+this._unselectableClass+" ui-state-disabled":"")+(K&&!H?"":" "+M[1]+(A.getTime()==s.getTime()?" "+this._currentClass:"")+(A.getTime()==h.getTime()?" ui-datepicker-today":""))+'"'+((!K||H)&&M[2]?' title="'+M[2]+'"':"")+(O?"":' onclick="DP_jQuery_'+e+".datepicker._selectDay('#"+b.id+"',"+A.getMonth()+","+A.getFullYear()+', this);return false;"')+">"+(K&&!H?"&#xa0;":O?'<span class="ui-state-default">'+A.getDate()+"</span>":'<a class="ui-state-default'+(A.getTime()==h.getTime()?" ui-state-highlight":"")+(A.getTime()==s.getTime()?" ui-state-active":"")+(K?" ui-priority-secondary":"")+'" href="#">'+A.getDate()+"</a>")+"</td>";
A.setDate(A.getDate()+1);A=this._daylightSavingAdjust(A)}F+=S+"</tr>"}m++;if(m>11){m=0;v++}F+="</tbody></table>"+(q?"</div>"+(k[0]>0&&L==k[1]-1?'<div class="ui-datepicker-row-break"></div>':""):"");
P+=F}D+=P}D+=l+(a.browser.msie&&parseInt(a.browser.version,10)<7&&!b.inline?'<iframe src="javascript:false;" class="ui-datepicker-cover" frameborder="0"></iframe>':"");
b._keyEvent=false;return D},_generateMonthYearHeader:function(b,h,j,l,o,n,k,m){var p=this._get(b,"changeMonth"),q=this._get(b,"changeYear"),s=this._get(b,"showMonthAfterYear"),r='<div class="ui-datepicker-title">',u="";
if(n||!p){u+='<span class="ui-datepicker-month">'+k[h]+"</span>"}else{k=l&&l.getFullYear()==j;var v=o&&o.getFullYear()==j;
u+='<select class="ui-datepicker-month" onchange="DP_jQuery_'+e+".datepicker._selectMonthYear('#"+b.id+"', this, 'M');\" onclick=\"DP_jQuery_"+e+".datepicker._clickMonthYear('#"+b.id+"');\">";
for(var w=0;w<12;w++){if((!k||w>=l.getMonth())&&(!v||w<=o.getMonth())){u+='<option value="'+w+'"'+(w==h?' selected="selected"':"")+">"+m[w]+"</option>"
}}u+="</select>"}s||(r+=u+(n||!(p&&q)?"&#xa0;":""));if(!b.yearshtml){b.yearshtml="";if(n||!q){r+='<span class="ui-datepicker-year">'+j+"</span>"
}else{m=this._get(b,"yearRange").split(":");var x=(new Date).getFullYear();k=function(z){z=z.match(/c[+-].*/)?j+parseInt(z.substring(1),10):z.match(/[+-].*/)?x+parseInt(z,10):parseInt(z,10);
return isNaN(z)?x:z};h=k(m[0]);m=Math.max(h,k(m[1]||""));h=l?Math.max(h,l.getFullYear()):h;m=o?Math.min(m,o.getFullYear()):m;
for(b.yearshtml+='<select class="ui-datepicker-year" onchange="DP_jQuery_'+e+".datepicker._selectMonthYear('#"+b.id+"', this, 'Y');\" onclick=\"DP_jQuery_"+e+".datepicker._clickMonthYear('#"+b.id+"');\">";
h<=m;h++){b.yearshtml+='<option value="'+h+'"'+(h==j?' selected="selected"':"")+">"+h+"</option>"}b.yearshtml+="</select>";
r+=b.yearshtml;b.yearshtml=null}}r+=this._get(b,"yearSuffix");if(s){r+=(n||!(p&&q)?"&#xa0;":"")+u}r+="</div>";return r},_adjustInstDate:function(b,h,j){var l=b.drawYear+(j=="Y"?h:0),o=b.drawMonth+(j=="M"?h:0);
h=Math.min(b.selectedDay,this._getDaysInMonth(l,o))+(j=="D"?h:0);l=this._restrictMinMax(b,this._daylightSavingAdjust(new Date(l,o,h)));
b.selectedDay=l.getDate();b.drawMonth=b.selectedMonth=l.getMonth();b.drawYear=b.selectedYear=l.getFullYear();if(j=="M"||j=="Y"){this._notifyChange(b)
}},_restrictMinMax:function(b,h){var j=this._getMinMaxDate(b,"min");b=this._getMinMaxDate(b,"max");h=j&&h<j?j:h;return h=b&&h>b?b:h
},_notifyChange:function(b){var h=this._get(b,"onChangeMonthYear");if(h){h.apply(b.input?b.input[0]:null,[b.selectedYear,b.selectedMonth+1,b])
}},_getNumberOfMonths:function(b){b=this._get(b,"numberOfMonths");return b==null?[1,1]:typeof b=="number"?[1,b]:b},_getMinMaxDate:function(b,h){return this._determineDate(b,this._get(b,h+"Date"),null)
},_getDaysInMonth:function(b,h){return 32-this._daylightSavingAdjust(new Date(b,h,32)).getDate()},_getFirstDayOfMonth:function(b,h){return(new Date(b,h,1)).getDay()
},_canAdjustMonth:function(b,h,j,l){var o=this._getNumberOfMonths(b);j=this._daylightSavingAdjust(new Date(j,l+(h<0?h:o[0]*o[1]),1));
h<0&&j.setDate(this._getDaysInMonth(j.getFullYear(),j.getMonth()));return this._isInRange(b,j)},_isInRange:function(b,h){var j=this._getMinMaxDate(b,"min");
b=this._getMinMaxDate(b,"max");return(!j||h.getTime()>=j.getTime())&&(!b||h.getTime()<=b.getTime())},_getFormatConfig:function(b){var h=this._get(b,"shortYearCutoff");
h=typeof h!="string"?h:(new Date).getFullYear()%100+parseInt(h,10);return{shortYearCutoff:h,dayNamesShort:this._get(b,"dayNamesShort"),dayNames:this._get(b,"dayNames"),monthNamesShort:this._get(b,"monthNamesShort"),monthNames:this._get(b,"monthNames")}
},_formatDate:function(b,h,j,l){if(!h){b.currentDay=b.selectedDay;b.currentMonth=b.selectedMonth;b.currentYear=b.selectedYear
}h=h?typeof h=="object"?h:this._daylightSavingAdjust(new Date(l,j,h)):this._daylightSavingAdjust(new Date(b.currentYear,b.currentMonth,b.currentDay));
return this.formatDate(this._get(b,"dateFormat"),h,this._getFormatConfig(b))}});a.fn.datepicker=function(b){if(!this.length){return this
}if(!a.datepicker.initialized){a(document).mousedown(a.datepicker._checkExternalClick).find("body").append(a.datepicker.dpDiv);
a.datepicker.initialized=true}var h=Array.prototype.slice.call(arguments,1);if(typeof b=="string"&&(b=="isDisabled"||b=="getDate"||b=="widget")){return a.datepicker["_"+b+"Datepicker"].apply(a.datepicker,[this[0]].concat(h))
}if(b=="option"&&arguments.length==2&&typeof arguments[1]=="string"){return a.datepicker["_"+b+"Datepicker"].apply(a.datepicker,[this[0]].concat(h))
}return this.each(function(){typeof b=="string"?a.datepicker["_"+b+"Datepicker"].apply(a.datepicker,[this].concat(h)):a.datepicker._attachDatepicker(this,b)
})};a.datepicker=new c;a.datepicker.initialized=false;a.datepicker.uuid=(new Date).getTime();a.datepicker.version="1.8.13";
window["DP_jQuery_"+e]=a})(jQuery);(function(b,k){var l={buttons:true,height:true,maxHeight:true,maxWidth:true,minHeight:true,minWidth:true,width:true},j={maxHeight:true,maxWidth:true,minHeight:true,minWidth:true},e=b.attrFn||{val:true,css:true,html:true,text:true,data:true,width:true,height:true,offset:true,click:true};
b.widget("ui.dialog",{options:{autoOpen:true,buttons:{},closeOnEscape:true,closeText:"close",dialogClass:"",draggable:true,hide:null,height:"auto",maxHeight:false,maxWidth:false,minHeight:150,minWidth:150,modal:false,position:{my:"center",at:"center",collision:"fit",using:function(c){var a=b(this).css(c).offset().top;
a<0&&b(this).css("top",c.top-a)}},resizable:true,show:null,stack:true,title:"",width:300,zIndex:1000},_create:function(){this.originalTitle=this.element.attr("title");
if(typeof this.originalTitle!=="string"){this.originalTitle=""}this.options.title=this.options.title||this.originalTitle;
var m=this,f=m.options,a=f.title||"&#160;",g=b.ui.dialog.getTitleId(m.element),d=(m.uiDialog=b("<div></div>")).appendTo(document.body).hide().addClass("ui-dialog ui-widget ui-widget-content ui-corner-all "+f.dialogClass).css({zIndex:f.zIndex}).attr("tabIndex",-1).css("outline",0).keydown(function(q){if(f.closeOnEscape&&q.keyCode&&q.keyCode===b.ui.keyCode.ESCAPE){m.close(q);
q.preventDefault()}}).attr({role:"dialog","aria-labelledby":g}).mousedown(function(q){m.moveToTop(false,q)});m.element.show().removeAttr("title").addClass("ui-dialog-content ui-widget-content").appendTo(d);
var c=(m.uiDialogTitlebar=b("<div></div>")).addClass("ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix").prependTo(d),p=b('<a href="#"></a>').addClass("ui-dialog-titlebar-close ui-corner-all").attr("role","button").hover(function(){p.addClass("ui-state-hover")
},function(){p.removeClass("ui-state-hover")}).focus(function(){p.addClass("ui-state-focus")}).blur(function(){p.removeClass("ui-state-focus")
}).click(function(q){m.close(q);return false}).appendTo(c);(m.uiDialogTitlebarCloseText=b("<span></span>")).addClass("ui-icon ui-icon-closethick").text(f.closeText).appendTo(p);
b("<span></span>").addClass("ui-dialog-title").attr("id",g).html(a).prependTo(c);if(b.isFunction(f.beforeclose)&&!b.isFunction(f.beforeClose)){f.beforeClose=f.beforeclose
}c.find("*").add(c).disableSelection();f.draggable&&b.fn.draggable&&m._makeDraggable();f.resizable&&b.fn.resizable&&m._makeResizable();
m._createButtons(f.buttons);m._isOpen=false;b.fn.bgiframe&&d.bgiframe()},_init:function(){this.options.autoOpen&&this.open()
},destroy:function(){var a=this;a.overlay&&a.overlay.destroy();a.uiDialog.hide();a.element.unbind(".dialog").removeData("dialog").removeClass("ui-dialog-content ui-widget-content").hide().appendTo("body");
a.uiDialog.remove();a.originalTitle&&a.element.attr("title",a.originalTitle);return a},widget:function(){return this.uiDialog
},close:function(f){var c=this,a,d;if(false!==c._trigger("beforeClose",f)){c.overlay&&c.overlay.destroy();c.uiDialog.unbind("keypress.ui-dialog");
c._isOpen=false;if(c.options.hide){c.uiDialog.hide(c.options.hide,function(){c._trigger("close",f)})}else{c.uiDialog.hide();
c._trigger("close",f)}b.ui.dialog.overlay.resize();if(c.options.modal){a=0;b(".ui-dialog").each(function(){if(this!==c.uiDialog[0]){d=b(this).css("z-index");
isNaN(d)||(a=Math.max(a,d))}});b.ui.dialog.maxZ=a}return c}},isOpen:function(){return this._isOpen},moveToTop:function(f,c){var a=this,d=a.options;
if(d.modal&&!f||!d.stack&&!d.modal){return a._trigger("focus",c)}if(d.zIndex>b.ui.dialog.maxZ){b.ui.dialog.maxZ=d.zIndex}if(a.overlay){b.ui.dialog.maxZ+=1;
a.overlay.$el.css("z-index",b.ui.dialog.overlay.maxZ=b.ui.dialog.maxZ)}f={scrollTop:a.element.attr("scrollTop"),scrollLeft:a.element.attr("scrollLeft")};
b.ui.dialog.maxZ+=1;a.uiDialog.css("z-index",b.ui.dialog.maxZ);a.element.attr(f);a._trigger("focus",c);return a},open:function(){if(!this._isOpen){var d=this,c=d.options,a=d.uiDialog;
d.overlay=c.modal?new b.ui.dialog.overlay(d):null;d._size();d._position(c.position);a.show(c.show);d.moveToTop(true);c.modal&&a.bind("keypress.ui-dialog",function(m){if(m.keyCode===b.ui.keyCode.TAB){var g=b(":tabbable",this),f=g.filter(":first");
g=g.filter(":last");if(m.target===g[0]&&!m.shiftKey){f.focus(1);return false}else{if(m.target===f[0]&&m.shiftKey){g.focus(1);
return false}}}});b(d.element.find(":tabbable").get().concat(a.find(".ui-dialog-buttonpane :tabbable").get().concat(a.get()))).eq(0).focus();
d._isOpen=true;d._trigger("open");return d}},_createButtons:function(g){var d=this,a=false,f=b("<div></div>").addClass("ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"),c=b("<div></div>").addClass("ui-dialog-buttonset").appendTo(f);
d.uiDialog.find(".ui-dialog-buttonpane").remove();typeof g==="object"&&g!==null&&b.each(g,function(){return !(a=true)});if(a){b.each(g,function(m,p){p=b.isFunction(p)?{click:p,text:m}:p;
var q=b('<button type="button"></button>').click(function(){p.click.apply(d.element[0],arguments)}).appendTo(c);b.each(p,function(u,r){if(u!=="click"){u in e?q[u](r):q.attr(u,r)
}});b.fn.button&&q.button()});f.appendTo(d.uiDialog)}},_makeDraggable:function(){function g(m){return{position:m.position,offset:m.offset}
}var d=this,a=d.options,f=b(document),c;d.uiDialog.draggable({cancel:".ui-dialog-content, .ui-dialog-titlebar-close",handle:".ui-dialog-titlebar",containment:"document",start:function(m,p){c=a.height==="auto"?"auto":b(this).height();
b(this).height(b(this).height()).addClass("ui-dialog-dragging");d._trigger("dragStart",m,g(p))},drag:function(m,p){d._trigger("drag",m,g(p))
},stop:function(m,p){a.position=[p.position.left-f.scrollLeft(),p.position.top-f.scrollTop()];b(this).removeClass("ui-dialog-dragging").height(c);
d._trigger("dragStop",m,g(p));b.ui.dialog.overlay.resize()}})},_makeResizable:function(g){function d(m){return{originalPosition:m.originalPosition,originalSize:m.originalSize,position:m.position,size:m.size}
}g=g===k?this.options.resizable:g;var a=this,f=a.options,c=a.uiDialog.css("position");g=typeof g==="string"?g:"n,e,s,w,se,sw,ne,nw";
a.uiDialog.resizable({cancel:".ui-dialog-content",containment:"document",alsoResize:a.element,maxWidth:f.maxWidth,maxHeight:f.maxHeight,minWidth:f.minWidth,minHeight:a._minHeight(),handles:g,start:function(m,p){b(this).addClass("ui-dialog-resizing");
a._trigger("resizeStart",m,d(p))},resize:function(m,p){a._trigger("resize",m,d(p))},stop:function(m,p){b(this).removeClass("ui-dialog-resizing");
f.height=b(this).height();f.width=b(this).width();a._trigger("resizeStop",m,d(p));b.ui.dialog.overlay.resize()}}).css("position",c).find(".ui-resizable-se").addClass("ui-icon ui-icon-grip-diagonal-se")
},_minHeight:function(){var a=this.options;return a.height==="auto"?a.minHeight:Math.min(a.minHeight,a.height)},_position:function(f){var c=[],a=[0,0],d;
if(f){if(typeof f==="string"||typeof f==="object"&&"0" in f){c=f.split?f.split(" "):[f[0],f[1]];if(c.length===1){c[1]=c[0]
}b.each(["left","top"],function(m,g){if(+c[m]===c[m]){a[m]=c[m];c[m]=g}});f={my:c.join(" "),at:c.join(" "),offset:a.join(" ")}
}f=b.extend({},b.ui.dialog.prototype.options.position,f)}else{f=b.ui.dialog.prototype.options.position}(d=this.uiDialog.is(":visible"))||this.uiDialog.show();
this.uiDialog.css({top:0,left:0}).position(b.extend({of:window},f));d||this.uiDialog.hide()},_setOptions:function(f){var c=this,a={},d=false;
b.each(f,function(m,g){c._setOption(m,g);if(m in l){d=true}if(m in j){a[m]=g}});d&&this._size();this.uiDialog.is(":data(resizable)")&&this.uiDialog.resizable("option",a)
},_setOption:function(g,d){var a=this,f=a.uiDialog;switch(g){case"beforeclose":g="beforeClose";break;case"buttons":a._createButtons(d);
break;case"closeText":a.uiDialogTitlebarCloseText.text(""+d);break;case"dialogClass":f.removeClass(a.options.dialogClass).addClass("ui-dialog ui-widget ui-widget-content ui-corner-all "+d);
break;case"disabled":d?f.addClass("ui-dialog-disabled"):f.removeClass("ui-dialog-disabled");break;case"draggable":var c=f.is(":data(draggable)");
c&&!d&&f.draggable("destroy");!c&&d&&a._makeDraggable();break;case"position":a._position(d);break;case"resizable":(c=f.is(":data(resizable)"))&&!d&&f.resizable("destroy");
c&&typeof d==="string"&&f.resizable("option","handles",d);!c&&d!==false&&a._makeResizable(d);break;case"title":b(".ui-dialog-title",a.uiDialogTitlebar).html(""+(d||"&#160;"));
break}b.Widget.prototype._setOption.apply(a,arguments)},_size:function(){var f=this.options,c,a,d=this.uiDialog.is(":visible");
this.element.show().css({width:"auto",minHeight:0,height:0});if(f.minWidth>f.width){f.width=f.minWidth}c=this.uiDialog.css({height:"auto",width:f.width}).height();
a=Math.max(0,f.minHeight-c);if(f.height==="auto"){if(b.support.minHeight){this.element.css({minHeight:a,height:"auto"})}else{this.uiDialog.show();
f=this.element.css("height","auto").height();d||this.uiDialog.hide();this.element.height(Math.max(f,a))}}else{this.element.height(Math.max(f.height-c,0))
}this.uiDialog.is(":data(resizable)")&&this.uiDialog.resizable("option","minHeight",this._minHeight())}});b.extend(b.ui.dialog,{version:"1.8.13",uuid:0,maxZ:0,getTitleId:function(a){a=a.attr("id");
if(!a){this.uuid+=1;a=this.uuid}return"ui-dialog-title-"+a},overlay:function(a){this.$el=b.ui.dialog.overlay.create(a)}});
b.extend(b.ui.dialog.overlay,{instances:[],oldInstances:[],maxZ:0,events:b.map("focus,mousedown,mouseup,keydown,keypress,click".split(","),function(a){return a+".dialog-overlay"
}).join(" "),create:function(c){if(this.instances.length===0){setTimeout(function(){b.ui.dialog.overlay.instances.length&&b(document).bind(b.ui.dialog.overlay.events,function(d){if(b(d.target).zIndex()<b.ui.dialog.overlay.maxZ){return false
}})},1);b(document).bind("keydown.dialog-overlay",function(d){if(c.options.closeOnEscape&&d.keyCode&&d.keyCode===b.ui.keyCode.ESCAPE){c.close(d);
d.preventDefault()}});b(window).bind("resize.dialog-overlay",b.ui.dialog.overlay.resize)}var a=(this.oldInstances.pop()||b("<div></div>").addClass("ui-widget-overlay")).appendTo(document.body).css({width:this.width(),height:this.height()});
b.fn.bgiframe&&a.bgiframe();this.instances.push(a);return a},destroy:function(d){var c=b.inArray(d,this.instances);c!=-1&&this.oldInstances.push(this.instances.splice(c,1)[0]);
this.instances.length===0&&b([document,window]).unbind(".dialog-overlay");d.remove();var a=0;b.each(this.instances,function(){a=Math.max(a,this.css("z-index"))
});this.maxZ=a},height:function(){var c,a;if(b.browser.msie&&b.browser.version<7){c=Math.max(document.documentElement.scrollHeight,document.body.scrollHeight);
a=Math.max(document.documentElement.offsetHeight,document.body.offsetHeight);return c<a?b(window).height()+"px":c+"px"}else{return b(document).height()+"px"
}},width:function(){var c,a;if(b.browser.msie&&b.browser.version<7){c=Math.max(document.documentElement.scrollWidth,document.body.scrollWidth);
a=Math.max(document.documentElement.offsetWidth,document.body.offsetWidth);return c<a?b(window).width()+"px":c+"px"}else{return b(document).width()+"px"
}},resize:function(){var a=b([]);b.each(b.ui.dialog.overlay.instances,function(){a=a.add(this)});a.css({width:0,height:0}).css({width:b.ui.dialog.overlay.width(),height:b.ui.dialog.overlay.height()})
}});b.extend(b.ui.dialog.overlay.prototype,{destroy:function(){b.ui.dialog.overlay.destroy(this.$el)}})})(jQuery);(function(b){b.ui=b.ui||{};
var k=/left|center|right/,l=/top|center|bottom/,j=b.fn.position,e=b.fn.offset;b.fn.position=function(m){if(!m||!m.of){return j.apply(this,arguments)
}m=b.extend({},m);var f=b(m.of),a=f[0],g=(m.collision||"flip").split(" "),d=m.offset?m.offset.split(" "):[0,0],c,p,q;if(a.nodeType===9){c=f.width();
p=f.height();q={top:0,left:0}}else{if(a.setTimeout){c=f.width();p=f.height();q={top:f.scrollTop(),left:f.scrollLeft()}}else{if(a.preventDefault){m.at="left top";
c=p=0;q={top:m.of.pageY,left:m.of.pageX}}else{c=f.outerWidth();p=f.outerHeight();q=f.offset()}}}b.each(["my","at"],function(){var r=(m[this]||"").split(" ");
if(r.length===1){r=k.test(r[0])?r.concat(["center"]):l.test(r[0])?["center"].concat(r):["center","center"]}r[0]=k.test(r[0])?r[0]:"center";
r[1]=l.test(r[1])?r[1]:"center";m[this]=r});if(g.length===1){g[1]=g[0]}d[0]=parseInt(d[0],10)||0;if(d.length===1){d[1]=d[0]
}d[1]=parseInt(d[1],10)||0;if(m.at[0]==="right"){q.left+=c}else{if(m.at[0]==="center"){q.left+=c/2}}if(m.at[1]==="bottom"){q.top+=p
}else{if(m.at[1]==="center"){q.top+=p/2}}q.left+=d[0];q.top+=d[1];return this.each(function(){var B=b(this),A=B.outerWidth(),z=B.outerHeight(),y=parseInt(b.curCSS(this,"marginLeft",true))||0,F=parseInt(b.curCSS(this,"marginTop",true))||0,x=A+y+(parseInt(b.curCSS(this,"marginRight",true))||0),E=z+F+(parseInt(b.curCSS(this,"marginBottom",true))||0),D=b.extend({},q),C;
if(m.my[0]==="right"){D.left-=A}else{if(m.my[0]==="center"){D.left-=A/2}}if(m.my[1]==="bottom"){D.top-=z}else{if(m.my[1]==="center"){D.top-=z/2
}}D.left=Math.round(D.left);D.top=Math.round(D.top);C={left:D.left-y,top:D.top-F};b.each(["left","top"],function(r,u){b.ui.position[g[r]]&&b.ui.position[g[r]][u](D,{targetWidth:c,targetHeight:p,elemWidth:A,elemHeight:z,collisionPosition:C,collisionWidth:x,collisionHeight:E,offset:d,my:m.my,at:m.at})
});b.fn.bgiframe&&B.bgiframe();B.offset(b.extend(D,{using:m.using}))})};b.ui.position={fit:{left:function(d,c){var a=b(window);
a=c.collisionPosition.left+c.collisionWidth-a.width()-a.scrollLeft();d.left=a>0?d.left-a:Math.max(d.left-c.collisionPosition.left,d.left)
},top:function(d,c){var a=b(window);a=c.collisionPosition.top+c.collisionHeight-a.height()-a.scrollTop();d.top=a>0?d.top-a:Math.max(d.top-c.collisionPosition.top,d.top)
}},flip:{left:function(m,f){if(f.at[0]!=="center"){var a=b(window);a=f.collisionPosition.left+f.collisionWidth-a.width()-a.scrollLeft();
var g=f.my[0]==="left"?-f.elemWidth:f.my[0]==="right"?f.elemWidth:0,d=f.at[0]==="left"?f.targetWidth:-f.targetWidth,c=-2*f.offset[0];
m.left+=f.collisionPosition.left<0?g+d+c:a>0?g+d+c:0}},top:function(m,f){if(f.at[1]!=="center"){var a=b(window);a=f.collisionPosition.top+f.collisionHeight-a.height()-a.scrollTop();
var g=f.my[1]==="top"?-f.elemHeight:f.my[1]==="bottom"?f.elemHeight:0,d=f.at[1]==="top"?f.targetHeight:-f.targetHeight,c=-2*f.offset[1];
m.top+=f.collisionPosition.top<0?g+d+c:a>0?g+d+c:0}}}};if(!b.offset.setOffset){b.offset.setOffset=function(m,f){if(/static/.test(b.curCSS(m,"position"))){m.style.position="relative"
}var a=b(m),g=a.offset(),d=parseInt(b.curCSS(m,"top",true),10)||0,c=parseInt(b.curCSS(m,"left",true),10)||0;g={top:f.top-g.top+d,left:f.left-g.left+c};
"using" in f?f.using.call(m,g):a.css(g)};b.fn.offset=function(c){var a=this[0];if(!a||!a.ownerDocument){return null}if(c){return this.each(function(){b.offset.setOffset(this,c)
})}return e.call(this)}}})(jQuery);(function(b,c){b.widget("ui.progressbar",{options:{value:0,max:100},min:0,_create:function(){this.element.addClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").attr({role:"progressbar","aria-valuemin":this.min,"aria-valuemax":this.options.max,"aria-valuenow":this._value()});
this.valueDiv=b("<div class='ui-progressbar-value ui-widget-header ui-corner-left'></div>").appendTo(this.element);this.oldValue=this._value();
this._refreshValue()},destroy:function(){this.element.removeClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").removeAttr("role").removeAttr("aria-valuemin").removeAttr("aria-valuemax").removeAttr("aria-valuenow");
this.valueDiv.remove();b.Widget.prototype.destroy.apply(this,arguments)},value:function(a){if(a===c){return this._value()
}this._setOption("value",a);return this},_setOption:function(d,a){if(d==="value"){this.options.value=a;this._refreshValue();
this._value()===this.options.max&&this._trigger("complete")}b.Widget.prototype._setOption.apply(this,arguments)},_value:function(){var a=this.options.value;
if(typeof a!=="number"){a=0}return Math.min(this.options.max,Math.max(this.min,a))},_percentage:function(){return 100*this._value()/this.options.max
},_refreshValue:function(){var d=this.value(),a=this._percentage();if(this.oldValue!==d){this.oldValue=d;this._trigger("change")
}this.valueDiv.toggle(d>this.min).toggleClass("ui-corner-right",d===this.options.max).width(a.toFixed(0)+"%");this.element.attr("aria-valuenow",d)
}});b.extend(b.ui.progressbar,{version:"1.8.13"})})(jQuery);(function(b){b.widget("ui.slider",b.ui.mouse,{widgetEventPrefix:"slide",options:{animate:false,distance:0,max:100,min:0,orientation:"horizontal",range:false,step:1,value:0,values:null},_create:function(){var m=this,p=this.options,k=this.element.find(".ui-slider-handle").addClass("ui-state-default ui-corner-all"),j=p.values&&p.values.length||1,l=[];
this._mouseSliding=this._keySliding=false;this._animateOff=true;this._handleIndex=null;this._detectOrientation();this._mouseInit();
this.element.addClass("ui-slider ui-slider-"+this.orientation+" ui-widget ui-widget-content ui-corner-all"+(p.disabled?" ui-slider-disabled ui-disabled":""));
this.range=b([]);if(p.range){if(p.range===true){if(!p.values){p.values=[this._valueMin(),this._valueMin()]}if(p.values.length&&p.values.length!==2){p.values=[p.values[0],p.values[0]]
}}this.range=b("<div></div>").appendTo(this.element).addClass("ui-slider-range ui-widget-header"+(p.range==="min"||p.range==="max"?" ui-slider-range-"+p.range:""))
}for(var a=k.length;a<j;a+=1){l.push("<a class='ui-slider-handle ui-state-default ui-corner-all' href='#'></a>")}this.handles=k.add(b(l.join("")).appendTo(m.element));
this.handle=this.handles.eq(0);this.handles.add(this.range).filter("a").click(function(c){c.preventDefault()}).hover(function(){p.disabled||b(this).addClass("ui-state-hover")
},function(){b(this).removeClass("ui-state-hover")}).focus(function(){if(p.disabled){b(this).blur()}else{b(".ui-slider .ui-state-focus").removeClass("ui-state-focus");
b(this).addClass("ui-state-focus")}}).blur(function(){b(this).removeClass("ui-state-focus")});this.handles.each(function(c){b(this).data("index.ui-slider-handle",c)
});this.handles.keydown(function(c){var f=true,e=b(this).data("index.ui-slider-handle"),d,g,q;if(!m.options.disabled){switch(c.keyCode){case b.ui.keyCode.HOME:case b.ui.keyCode.END:case b.ui.keyCode.PAGE_UP:case b.ui.keyCode.PAGE_DOWN:case b.ui.keyCode.UP:case b.ui.keyCode.RIGHT:case b.ui.keyCode.DOWN:case b.ui.keyCode.LEFT:f=false;
if(!m._keySliding){m._keySliding=true;b(this).addClass("ui-state-active");d=m._start(c,e);if(d===false){return}}break}q=m.options.step;
d=m.options.values&&m.options.values.length?(g=m.values(e)):(g=m.value());switch(c.keyCode){case b.ui.keyCode.HOME:g=m._valueMin();
break;case b.ui.keyCode.END:g=m._valueMax();break;case b.ui.keyCode.PAGE_UP:g=m._trimAlignValue(d+(m._valueMax()-m._valueMin())/5);
break;case b.ui.keyCode.PAGE_DOWN:g=m._trimAlignValue(d-(m._valueMax()-m._valueMin())/5);break;case b.ui.keyCode.UP:case b.ui.keyCode.RIGHT:if(d===m._valueMax()){return
}g=m._trimAlignValue(d+q);break;case b.ui.keyCode.DOWN:case b.ui.keyCode.LEFT:if(d===m._valueMin()){return}g=m._trimAlignValue(d-q);
break}m._slide(c,e,g);return f}}).keyup(function(c){var d=b(this).data("index.ui-slider-handle");if(m._keySliding){m._keySliding=false;
m._stop(c,d);m._change(c,d);b(this).removeClass("ui-state-active")}});this._refreshValue();this._animateOff=false},destroy:function(){this.handles.remove();
this.range.remove();this.element.removeClass("ui-slider ui-slider-horizontal ui-slider-vertical ui-slider-disabled ui-widget ui-widget-content ui-corner-all").removeData("slider").unbind(".slider");
this._mouseDestroy();return this},_mouseCapture:function(p){var q=this.options,l,k,m,j,a;if(q.disabled){return false}this.elementSize={width:this.element.outerWidth(),height:this.element.outerHeight()};
this.elementOffset=this.element.offset();l=this._normValueFromMouse({x:p.pageX,y:p.pageY});k=this._valueMax()-this._valueMin()+1;
j=this;this.handles.each(function(d){var c=Math.abs(l-j.values(d));if(k>c){k=c;m=b(this);a=d}});if(q.range===true&&this.values(1)===q.min){a+=1;
m=b(this.handles[a])}if(this._start(p,a)===false){return false}this._mouseSliding=true;j._handleIndex=a;m.addClass("ui-state-active").focus();
q=m.offset();this._clickOffset=!b(p.target).parents().andSelf().is(".ui-slider-handle")?{left:0,top:0}:{left:p.pageX-q.left-m.width()/2,top:p.pageY-q.top-m.height()/2-(parseInt(m.css("borderTopWidth"),10)||0)-(parseInt(m.css("borderBottomWidth"),10)||0)+(parseInt(m.css("marginTop"),10)||0)};
this.handles.hasClass("ui-state-hover")||this._slide(p,a,l);return this._animateOff=true},_mouseStart:function(){return true
},_mouseDrag:function(a){var e=this._normValueFromMouse({x:a.pageX,y:a.pageY});this._slide(a,this._handleIndex,e);return false
},_mouseStop:function(a){this.handles.removeClass("ui-state-active");this._mouseSliding=false;this._stop(a,this._handleIndex);
this._change(a,this._handleIndex);this._clickOffset=this._handleIndex=null;return this._animateOff=false},_detectOrientation:function(){this.orientation=this.options.orientation==="vertical"?"vertical":"horizontal"
},_normValueFromMouse:function(a){var e;if(this.orientation==="horizontal"){e=this.elementSize.width;a=a.x-this.elementOffset.left-(this._clickOffset?this._clickOffset.left:0)
}else{e=this.elementSize.height;a=a.y-this.elementOffset.top-(this._clickOffset?this._clickOffset.top:0)}e=a/e;if(e>1){e=1
}if(e<0){e=0}if(this.orientation==="vertical"){e=1-e}a=this._valueMax()-this._valueMin();return this._trimAlignValue(this._valueMin()+e*a)
},_start:function(e,g){var a={handle:this.handles[g],value:this.value()};if(this.options.values&&this.options.values.length){a.value=this.values(g);
a.values=this.values()}return this._trigger("start",e,a)},_slide:function(j,k,e){var a;if(this.options.values&&this.options.values.length){a=this.values(k?0:1);
if(this.options.values.length===2&&this.options.range===true&&(k===0&&e>a||k===1&&e<a)){e=a}if(e!==this.values(k)){a=this.values();
a[k]=e;j=this._trigger("slide",j,{handle:this.handles[k],value:e,values:a});this.values(k?0:1);j!==false&&this.values(k,e,true)
}}else{if(e!==this.value()){j=this._trigger("slide",j,{handle:this.handles[k],value:e});j!==false&&this.value(e)}}},_stop:function(e,g){var a={handle:this.handles[g],value:this.value()};
if(this.options.values&&this.options.values.length){a.value=this.values(g);a.values=this.values()}this._trigger("stop",e,a)
},_change:function(e,g){if(!this._keySliding&&!this._mouseSliding){var a={handle:this.handles[g],value:this.value()};if(this.options.values&&this.options.values.length){a.value=this.values(g);
a.values=this.values()}this._trigger("change",e,a)}},value:function(a){if(arguments.length){this.options.value=this._trimAlignValue(a);
this._refreshValue();this._change(null,0)}else{return this._value()}},values:function(l,m){var j,a,k;if(arguments.length>1){this.options.values[l]=this._trimAlignValue(m);
this._refreshValue();this._change(null,l)}else{if(arguments.length){if(b.isArray(arguments[0])){j=this.options.values;a=arguments[0];
for(k=0;k<j.length;k+=1){j[k]=this._trimAlignValue(a[k]);this._change(null,k)}this._refreshValue()}else{return this.options.values&&this.options.values.length?this._values(l):this.value()
}}else{return this._values()}}},_setOption:function(j,k){var e,a=0;if(b.isArray(this.options.values)){a=this.options.values.length
}b.Widget.prototype._setOption.apply(this,arguments);switch(j){case"disabled":if(k){this.handles.filter(".ui-state-focus").blur();
this.handles.removeClass("ui-state-hover");this.handles.attr("disabled","disabled");this.element.addClass("ui-disabled")}else{this.handles.removeAttr("disabled");
this.element.removeClass("ui-disabled")}break;case"orientation":this._detectOrientation();this.element.removeClass("ui-slider-horizontal ui-slider-vertical").addClass("ui-slider-"+this.orientation);
this._refreshValue();break;case"value":this._animateOff=true;this._refreshValue();this._change(null,0);this._animateOff=false;
break;case"values":this._animateOff=true;this._refreshValue();for(e=0;e<a;e+=1){this._change(null,e)}this._animateOff=false;
break}},_value:function(){var a=this.options.value;return a=this._trimAlignValue(a)},_values:function(e){var g,a;if(arguments.length){g=this.options.values[e];
return g=this._trimAlignValue(g)}else{g=this.options.values.slice();for(a=0;a<g.length;a+=1){g[a]=this._trimAlignValue(g[a])
}return g}},_trimAlignValue:function(e){if(e<=this._valueMin()){return this._valueMin()}if(e>=this._valueMax()){return this._valueMax()
}var g=this.options.step>0?this.options.step:1,a=(e-this._valueMin())%g;alignValue=e-a;if(Math.abs(a)*2>=g){alignValue+=a>0?g:-g
}return parseFloat(alignValue.toFixed(5))},_valueMin:function(){return this.options.min},_valueMax:function(){return this.options.max
},_refreshValue:function(){var v=this.options.range,x=this.options,r=this,q=!this._animateOff?x.animate:false,u,m={},y,p,k,a;
if(this.options.values&&this.options.values.length){this.handles.each(function(c){u=(r.values(c)-r._valueMin())/(r._valueMax()-r._valueMin())*100;
m[r.orientation==="horizontal"?"left":"bottom"]=u+"%";b(this).stop(1,1)[q?"animate":"css"](m,x.animate);if(r.options.range===true){if(r.orientation==="horizontal"){if(c===0){r.range.stop(1,1)[q?"animate":"css"]({left:u+"%"},x.animate)
}if(c===1){r.range[q?"animate":"css"]({width:u-y+"%"},{queue:false,duration:x.animate})}}else{if(c===0){r.range.stop(1,1)[q?"animate":"css"]({bottom:u+"%"},x.animate)
}if(c===1){r.range[q?"animate":"css"]({height:u-y+"%"},{queue:false,duration:x.animate})}}}y=u})}else{p=this.value();k=this._valueMin();
a=this._valueMax();u=a!==k?(p-k)/(a-k)*100:0;m[r.orientation==="horizontal"?"left":"bottom"]=u+"%";this.handle.stop(1,1)[q?"animate":"css"](m,x.animate);
if(v==="min"&&this.orientation==="horizontal"){this.range.stop(1,1)[q?"animate":"css"]({width:u+"%"},x.animate)}if(v==="max"&&this.orientation==="horizontal"){this.range[q?"animate":"css"]({width:100-u+"%"},{queue:false,duration:x.animate})
}if(v==="min"&&this.orientation==="vertical"){this.range.stop(1,1)[q?"animate":"css"]({height:u+"%"},x.animate)}if(v==="max"&&this.orientation==="vertical"){this.range[q?"animate":"css"]({height:100-u+"%"},{queue:false,duration:x.animate})
}}}});b.extend(b.ui.slider,{version:"1.8.13"})})(jQuery);(function(b,m){function p(){return ++j}function k(){return ++l}var j=0,l=0;
b.widget("ui.tabs",{options:{add:null,ajaxOptions:null,cache:false,cookie:null,collapsible:false,disable:null,disabled:[],enable:null,event:"click",fx:null,idPrefix:"ui-tabs-",load:null,panelTemplate:"<div></div>",remove:null,select:null,show:null,spinner:"<em>Loading&#8230;</em>",tabTemplate:"<li><a href='#{href}'><span>#{label}</span></a></li>"},_create:function(){this._tabify(true)
},_setOption:function(c,a){if(c=="selected"){this.options.collapsible&&a==this.options.selected||this.select(a)}else{this.options[c]=a;
this._tabify()}},_tabId:function(a){return a.title&&a.title.replace(/\s/g,"_").replace(/[^\w\u00c0-\uFFFF-]/g,"")||this.options.idPrefix+p()
},_sanitizeSelector:function(a){return a.replace(/:/g,"\\:")},_cookie:function(){var a=this.cookie||(this.cookie=this.options.cookie.name||"ui-tabs-"+k());
return b.cookie.apply(null,[a].concat(b.makeArray(arguments)))},_ui:function(c,a){return{tab:c,panel:a,index:this.anchors.index(c)}
},_cleanup:function(){this.lis.filter(".ui-state-processing").removeClass("ui-state-processing").find("span:data(label.tabs)").each(function(){var a=b(this);
a.html(a.data("label.tabs")).removeData("label.tabs")})},_tabify:function(v){function y(A,q){A.css("display","");!b.support.opacity&&q.opacity&&A[0].style.removeAttribute("filter")
}var x=this,u=this.options,g=/^#.+/;this.list=this.element.find("ol,ul").eq(0);this.lis=b(" > li:has(a[href])",this.list);
this.anchors=this.lis.map(function(){return b("a",this)[0]});this.panels=b([]);this.anchors.each(function(D,C){var B=b(C).attr("href"),A=B.split("#")[0],q;
if(A&&(A===location.toString().split("#")[0]||(q=b("base")[0])&&A===q.href)){B=C.hash;C.href=B}if(g.test(B)){x.panels=x.panels.add(x.element.find(x._sanitizeSelector(B)))
}else{if(B&&B!=="#"){b.data(C,"href.tabs",B);b.data(C,"load.tabs",B.replace(/#.*$/,""));B=x._tabId(C);C.href="#"+B;C=x.element.find("#"+B);
if(!C.length){C=b(u.panelTemplate).attr("id",B).addClass("ui-tabs-panel ui-widget-content ui-corner-bottom").insertAfter(x.panels[D-1]||x.list);
C.data("destroy.tabs",true)}x.panels=x.panels.add(C)}else{u.disabled.push(D)}}});if(v){this.element.addClass("ui-tabs ui-widget ui-widget-content ui-corner-all");
this.list.addClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all");this.lis.addClass("ui-state-default ui-corner-top");
this.panels.addClass("ui-tabs-panel ui-widget-content ui-corner-bottom");if(u.selected===m){location.hash&&this.anchors.each(function(A,q){if(q.hash==location.hash){u.selected=A;
return false}});if(typeof u.selected!=="number"&&u.cookie){u.selected=parseInt(x._cookie(),10)}if(typeof u.selected!=="number"&&this.lis.filter(".ui-tabs-selected").length){u.selected=this.lis.index(this.lis.filter(".ui-tabs-selected"))
}u.selected=u.selected||(this.lis.length?0:-1)}else{if(u.selected===null){u.selected=-1}}u.selected=u.selected>=0&&this.anchors[u.selected]||u.selected<0?u.selected:0;
u.disabled=b.unique(u.disabled.concat(b.map(this.lis.filter(".ui-state-disabled"),function(q){return x.lis.index(q)}))).sort();
b.inArray(u.selected,u.disabled)!=-1&&u.disabled.splice(b.inArray(u.selected,u.disabled),1);this.panels.addClass("ui-tabs-hide");
this.lis.removeClass("ui-tabs-selected ui-state-active");if(u.selected>=0&&this.anchors.length){x.element.find(x._sanitizeSelector(x.anchors[u.selected].hash)).removeClass("ui-tabs-hide");
this.lis.eq(u.selected).addClass("ui-tabs-selected ui-state-active");x.element.queue("tabs",function(){x._trigger("show",null,x._ui(x.anchors[u.selected],x.element.find(x._sanitizeSelector(x.anchors[u.selected].hash))[0]))
});this.load(u.selected)}b(window).bind("unload",function(){x.lis.add(x.anchors).unbind(".tabs");x.lis=x.anchors=x.panels=null
})}else{u.selected=this.lis.index(this.lis.filter(".ui-tabs-selected"))}this.element[u.collapsible?"addClass":"removeClass"]("ui-tabs-collapsible");
u.cookie&&this._cookie(u.selected,u.cookie);v=0;for(var d;d=this.lis[v];v++){b(d)[b.inArray(v,u.disabled)!=-1&&!b(d).hasClass("ui-tabs-selected")?"addClass":"removeClass"]("ui-state-disabled")
}u.cache===false&&this.anchors.removeData("cache.tabs");this.lis.add(this.anchors).unbind(".tabs");if(u.event!=="mouseover"){var e=function(A,q){q.is(":not(.ui-state-disabled)")&&q.addClass("ui-state-"+A)
},r=function(A,q){q.removeClass("ui-state-"+A)};this.lis.bind("mouseover.tabs",function(){e("hover",b(this))});this.lis.bind("mouseout.tabs",function(){r("hover",b(this))
});this.anchors.bind("focus.tabs",function(){e("focus",b(this).closest("li"))});this.anchors.bind("blur.tabs",function(){r("focus",b(this).closest("li"))
})}var f,c;if(u.fx){if(b.isArray(u.fx)){f=u.fx[0];c=u.fx[1]}else{f=c=u.fx}}var a=c?function(A,q){b(A).closest("li").addClass("ui-tabs-selected ui-state-active");
q.hide().removeClass("ui-tabs-hide").animate(c,c.duration||"normal",function(){y(q,c);x._trigger("show",null,x._ui(A,q[0]))
})}:function(A,q){b(A).closest("li").addClass("ui-tabs-selected ui-state-active");q.removeClass("ui-tabs-hide");x._trigger("show",null,x._ui(A,q[0]))
},z=f?function(A,q){q.animate(f,f.duration||"normal",function(){x.lis.removeClass("ui-tabs-selected ui-state-active");q.addClass("ui-tabs-hide");
y(q,f);x.element.dequeue("tabs")})}:function(A,q){x.lis.removeClass("ui-tabs-selected ui-state-active");q.addClass("ui-tabs-hide");
x.element.dequeue("tabs")};this.anchors.bind(u.event+".tabs",function(){var C=this,B=b(C).closest("li"),A=x.panels.filter(":not(.ui-tabs-hide)"),q=x.element.find(x._sanitizeSelector(C.hash));
if(B.hasClass("ui-tabs-selected")&&!u.collapsible||B.hasClass("ui-state-disabled")||B.hasClass("ui-state-processing")||x.panels.filter(":animated").length||x._trigger("select",null,x._ui(this,q[0]))===false){this.blur();
return false}u.selected=x.anchors.index(this);x.abort();if(u.collapsible){if(B.hasClass("ui-tabs-selected")){u.selected=-1;
u.cookie&&x._cookie(u.selected,u.cookie);x.element.queue("tabs",function(){z(C,A)}).dequeue("tabs");this.blur();return false
}else{if(!A.length){u.cookie&&x._cookie(u.selected,u.cookie);x.element.queue("tabs",function(){a(C,q)});x.load(x.anchors.index(this));
this.blur();return false}}}u.cookie&&x._cookie(u.selected,u.cookie);if(q.length){A.length&&x.element.queue("tabs",function(){z(C,A)
});x.element.queue("tabs",function(){a(C,q)});x.load(x.anchors.index(this))}else{throw"jQuery UI Tabs: Mismatching fragment identifier."
}b.browser.msie&&this.blur()});this.anchors.bind("click.tabs",function(){return false})},_getIndex:function(a){if(typeof a=="string"){a=this.anchors.index(this.anchors.filter("[href$="+a+"]"))
}return a},destroy:function(){var a=this.options;this.abort();this.element.unbind(".tabs").removeClass("ui-tabs ui-widget ui-widget-content ui-corner-all ui-tabs-collapsible").removeData("tabs");
this.list.removeClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all");this.anchors.each(function(){var c=b.data(this,"href.tabs");
if(c){this.href=c}var d=b(this).unbind(".tabs");b.each(["href","load","cache"],function(f,e){d.removeData(e+".tabs")})});
this.lis.unbind(".tabs").add(this.panels).each(function(){b.data(this,"destroy.tabs")?b(this).remove():b(this).removeClass("ui-state-default ui-corner-top ui-tabs-selected ui-state-active ui-state-hover ui-state-focus ui-state-disabled ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide")
});a.cookie&&this._cookie(null,a.cookie);return this},add:function(e,a,f){if(f===m){f=this.anchors.length}var d=this,c=this.options;
a=b(c.tabTemplate.replace(/#\{href\}/g,e).replace(/#\{label\}/g,a));e=!e.indexOf("#")?e.replace("#",""):this._tabId(b("a",a)[0]);
a.addClass("ui-state-default ui-corner-top").data("destroy.tabs",true);var g=d.element.find("#"+e);g.length||(g=b(c.panelTemplate).attr("id",e).data("destroy.tabs",true));
g.addClass("ui-tabs-panel ui-widget-content ui-corner-bottom ui-tabs-hide");if(f>=this.lis.length){a.appendTo(this.list);
g.appendTo(this.list[0].parentNode)}else{a.insertBefore(this.lis[f]);g.insertBefore(this.panels[f])}c.disabled=b.map(c.disabled,function(q){return q>=f?++q:q
});this._tabify();if(this.anchors.length==1){c.selected=0;a.addClass("ui-tabs-selected ui-state-active");g.removeClass("ui-tabs-hide");
this.element.queue("tabs",function(){d._trigger("show",null,d._ui(d.anchors[0],d.panels[0]))});this.load(0)}this._trigger("add",null,this._ui(this.anchors[f],this.panels[f]));
return this},remove:function(d){d=this._getIndex(d);var a=this.options,e=this.lis.eq(d).remove(),c=this.panels.eq(d).remove();
if(e.hasClass("ui-tabs-selected")&&this.anchors.length>1){this.select(d+(d+1<this.anchors.length?1:-1))}a.disabled=b.map(b.grep(a.disabled,function(f){return f!=d
}),function(f){return f>=d?--f:f});this._tabify();this._trigger("remove",null,this._ui(e.find("a")[0],c[0]));return this},enable:function(c){c=this._getIndex(c);
var a=this.options;if(b.inArray(c,a.disabled)!=-1){this.lis.eq(c).removeClass("ui-state-disabled");a.disabled=b.grep(a.disabled,function(d){return d!=c
});this._trigger("enable",null,this._ui(this.anchors[c],this.panels[c]));return this}},disable:function(c){c=this._getIndex(c);
var a=this.options;if(c!=a.selected){this.lis.eq(c).addClass("ui-state-disabled");a.disabled.push(c);a.disabled.sort();this._trigger("disable",null,this._ui(this.anchors[c],this.panels[c]))
}return this},select:function(a){a=this._getIndex(a);if(a==-1){if(this.options.collapsible&&this.options.selected!=-1){a=this.options.selected
}else{return this}}this.anchors.eq(a).trigger(this.options.event+".tabs");return this},load:function(e){e=this._getIndex(e);
var a=this,f=this.options,d=this.anchors.eq(e)[0],c=b.data(d,"load.tabs");this.abort();if(!c||this.element.queue("tabs").length!==0&&b.data(d,"cache.tabs")){this.element.dequeue("tabs")
}else{this.lis.eq(e).addClass("ui-state-processing");if(f.spinner){var g=b("span",d);g.data("label.tabs",g.html()).html(f.spinner)
}this.xhr=b.ajax(b.extend({},f.ajaxOptions,{url:c,success:function(u,r){a.element.find(a._sanitizeSelector(d.hash)).html(u);
a._cleanup();f.cache&&b.data(d,"cache.tabs",true);a._trigger("load",null,a._ui(a.anchors[e],a.panels[e]));try{f.ajaxOptions.success(u,r)
}catch(q){}},error:function(u,r){a._cleanup();a._trigger("load",null,a._ui(a.anchors[e],a.panels[e]));try{f.ajaxOptions.error(u,r,e,d)
}catch(q){}}}));a.element.dequeue("tabs");return this}},abort:function(){this.element.queue([]);this.panels.stop(false,true);
this.element.queue("tabs",this.element.queue("tabs").splice(-2,2));if(this.xhr){this.xhr.abort();delete this.xhr}this._cleanup();
return this},url:function(c,a){this.anchors.eq(c).removeData("cache.tabs").data("load.tabs",a);return this},length:function(){return this.anchors.length
}});b.extend(b.ui.tabs,{version:"1.8.13"});b.extend(b.ui.tabs.prototype,{rotation:null,rotate:function(e,a){var f=this,d=this.options,c=f._rotate||(f._rotate=function(g){clearTimeout(f.rotation);
f.rotation=setTimeout(function(){var q=d.selected;f.select(++q<f.anchors.length?q:0)},e);g&&g.stopPropagation()});a=f._unrotate||(f._unrotate=!a?function(g){g.clientX&&f.rotate(null)
}:function(){t=d.selected;c()});if(e){this.element.bind("tabsshow",c);this.anchors.bind(d.event+".tabs",a);c()}else{clearTimeout(f.rotation);
this.element.unbind("tabsshow",c);this.anchors.unbind(d.event+".tabs",a);delete this._rotate;delete this._unrotate}return this
}})})(jQuery);(function(V){var E=V.fn.domManip,S="_tmplitem",F=/^[^<]*(<[\w\W]+>)[^>]*$|\{\{\! /,U={},Q={},R,G={key:0,data:{}},O=0,T=0,K=[];
function P(f,j,b,a){var k={data:a||(j?j.data:{}),_wrap:j?j._wrap:null,tmpl:null,parent:j||null,nodes:[],calls:B,nest:z,wrap:y,html:A,update:C};
f&&V.extend(k,f,{nodes:[],parent:j});if(b){k.tmpl=b;k._ctnt=k._ctnt||k.tmpl(V,k);k.key=++O;(K.length?Q:U)[O]=k}return k}V.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(a,b){V.fn[a]=function(u){var r=[],p=V(u),e,q,c,d,f=this.length===1&&this[0].parentNode;
R=U||{};if(f&&f.nodeType===11&&f.childNodes.length===1&&p.length===1){p[b](this[0]);r=this}else{for(q=0,c=p.length;q<c;q++){T=q;
e=(q>0?this.clone(true):this).get();V.fn[b].apply(V(p[q]),e);r=r.concat(e)}T=0;r=this.pushStack(r,a,p.selector)}d=R;R=null;
V.tmpl.complete(d);return r}});V.fn.extend({tmpl:function(e,f,a){return V.tmpl(this[0],e,f,a)},tmplItem:function(){return V.tmplItem(this[0])
},template:function(a){return V.template(a,this[0])},domManip:function(p,a,b){if(p[0]&&p[0].nodeType){var m=V.makeArray(arguments),k=p.length,c=0,e;
while(c<k&&!(e=V.data(p[c++],"tmplItem"))){}if(k>1){m[0]=[V.makeArray(p)]}if(e&&T){m[2]=function(d){V.tmpl.afterManip(this,d,b)
}}E.apply(this,m)}else{E.apply(this,arguments)}T=0;!R&&V.tmpl.complete(U);return this}});V.extend({tmpl:function(l,f,g,m){var b,a=!m;
if(a){m=G;l=V.template[l]||V.template(null,l);Q={}}else{if(!l){l=m.tmpl;U[m.key]=m;m.nodes=[];m.wrapped&&I(m,m.wrapped);return V(N(m,null,m.tmpl(V,m)))
}}if(!l){return[]}if(typeof f==="function"){f=f.call(m||{})}g&&g.wrapped&&I(g,g.wrapped);b=V.isArray(f)?V.map(f,function(c){return c?P(g,m,l,c):null
}):[P(g,m,l,f)];return a?V(N(m,null,b)):b},tmplItem:function(a){var d;if(a instanceof V){a=a[0]}while(a&&a.nodeType===1&&!(d=V.data(a,"tmplItem"))&&(a=a.parentNode)){}return d||G
},template:function(d,a){if(a){if(typeof a==="string"){a=H(a)}else{if(a instanceof V){a=a[0]||{}}}if(a.nodeType){a=V.data(a,"tmpl")||V.data(a,"tmpl",H(a.innerHTML))
}return typeof d==="string"?(V.template[d]=a):a}return d?typeof d!=="string"?V.template(null,d):V.template[d]||V.template(null,F.test(d)?d:V(d)):null
},encode:function(b){return(""+b).split("<").join("&lt;").split(">").join("&gt;").split('"').join("&#34;").split("'").join("&#39;")
}});V.extend(V.tmpl,{tag:{tmpl:{_default:{$2:"null"},open:"if($notnull_1){_=_.concat($item.nest($1,$2));}"},wrap:{_default:{$2:"null"},open:"$item.calls(_,$1,$2);_=[];",close:"call=$item.calls();_=call._.concat($item.wrap(call,_));"},each:{_default:{$2:"$index, $value"},open:"if($notnull_1){$.each($1a,function($2){with(this){",close:"}});}"},"if":{open:"if(($notnull_1) && $1a){",close:"}"},"else":{_default:{$1:"true"},open:"}else if(($notnull_1) && $1a){"},html:{open:"if($notnull_1){_.push($1a);}"},"=":{_default:{$1:"$data"},open:"if($notnull_1){_.push($.encode($1a));}"},"!":{open:""}},complete:function(){U={}
},afterManip:function(c,a,j){var g=a.nodeType===11?V.makeArray(a.childNodes):a.nodeType===1?[a]:[];j.call(c,a);J(g);T++}});
function N(k,d,j){var a,l=j?V.map(j,function(b){return typeof b==="string"?k.key?b.replace(/(<\w+)(?=[\s>])(?![^>]*_tmplitem)([^>]*)/g,"$1 "+S+'="'+k.key+'" $2'):b:N(b,k,b._ctnt)
}):k;if(d){return l}l=l.join("");l.replace(/^\s*([^<\s][^<]*)?(<[\w\W]+>)([^>]*[^>\s])?\s*$/,function(b,p,g,m){a=V(g).get();
J(a);if(p){a=M(p).concat(a)}if(m){a=a.concat(M(m))}});return a?a:M(l)}function M(d){var a=document.createElement("div");a.innerHTML=d;
return V.makeArray(a.childNodes)}function H(a){return new Function("jQuery","$item","var $=jQuery,call,_=[],$data=$item.data;with($data){_.push('"+V.trim(a).replace(/([\\'])/g,"\\$1").replace(/[\r\t\n]/g," ").replace(/\$\{([^\}]*)\}/g,"{{= $1}}").replace(/\{\{(\/?)(\w+|.)(?:\(((?:[^\}]|\}(?!\}))*?)?\))?(?:\s+(.*?)?)?(\(((?:[^\}]|\}(?!\}))*?)\))?\s*\}\}/g,function(k,p,q,X,Z,Y,W){var r=V.tmpl.tag[q],u,x,v;
if(!r){throw"Template command not found: "+q}u=r._default||[];if(Y&&!/\w$/.test(Z)){Z+=Y;Y=""}if(Z){Z=L(Z);W=W?","+L(W)+")":Y?")":"";
x=Y?Z.indexOf(".")>-1?Z+Y:"("+Z+").call($item"+W:Z;v=Y?x:"(typeof("+Z+")==='function'?("+Z+").call($item):("+Z+"))"}else{v=x=u.$1||"null"
}X=L(X);return"');"+r[p?"close":"open"].split("$notnull_1").join(Z?"typeof("+Z+")!=='undefined' && ("+Z+")!=null":"true").split("$1a").join(v).split("$1").join(x).split("$2").join(X?X.replace(/\s*([^\(]+)\s*(\((.*?)\))?/g,function(g,j,e,f){f=f?","+f+")":e?")":"";
return f?"("+j+").call($item"+f:g}):u.$2||"")+"_.push('"})+"');}return _;")}function I(d,a){d._wrap=N(d,true,V.isArray(a)?a:[F.test(a)?a:V(a).html()]).join("")
}function L(b){return b?b.replace(/\\'/g,"'").replace(/\\\\/g,"\\"):null}function D(c){var d=document.createElement("div");
d.appendChild(c.cloneNode(true));return d.innerHTML}function J(b){var c="_"+T,g,q,f={},u,a,r;for(u=0,a=b.length;u<a;u++){if((g=b[u]).nodeType!==1){continue
}q=g.getElementsByTagName("*");for(r=q.length-1;r>=0;r--){d(q[r])}d(g)}function d(x){var Y,W=x,v,X,l;if(l=x.getAttribute(S)){while(W.parentNode&&(W=W.parentNode).nodeType===1&&!(Y=W.getAttribute(S))){}if(Y!==l){W=W.parentNode?W.nodeType===11?0:W.getAttribute(S)||0:0;
if(!(X=U[l])){X=Q[l];X=P(X,U[W]||Q[W],null,true);X.key=++O;U[O]=X}T&&Z(l)}x.removeAttribute(S)}else{if(T&&(X=V.data(x,"tmplItem"))){Z(X.key);
U[X.key]=X;W=V.data(x.parentNode,"tmplItem");W=W?W.key:0}}if(X){v=X;while(v&&v.key!=W){v.nodes.push(x);v=v.parent}delete X._ctnt;
delete X._wrap;V.data(x,"tmplItem",X)}function Z(e){e=e+c;X=f[e]=f[e]||P(X,U[X.parent.key+c]||X.parent,null,true)}}}function B(f,g,j,e){if(!f){return K.pop()
}K.push({_:f,tmpl:g,item:this,data:j,options:e})}function z(e,f,a){return V.tmpl(V.template(e),f,a,this)}function y(a,e){var f=a.options||{};
f.wrapped=e;return V.tmpl(V.template(a.tmpl),a.data,f,a.item)}function A(e,f){var a=this._wrap;return V.map(V(V.isArray(a)?a.join(""):a).filter(e||"*"),function(b){return f?b.innerText||b.textContent:b.outerHTML||D(b)
})}function C(){var a=this.nodes;V.tmpl(null,null,null,this).insertBefore(a[0]);V(a).remove()}})(jQuery);jQuery.cookie=function(d,e,b){if(arguments.length>1&&(e===null||typeof e!=="object")){b=jQuery.extend({},b);
if(e===null){b.expires=-1}if(typeof b.expires==="number"){var g=b.expires,c=b.expires=new Date();c.setDate(c.getDate()+g)
}return(document.cookie=[encodeURIComponent(d),"=",b.raw?String(e):encodeURIComponent(String(e)),b.expires?"; expires="+b.expires.toUTCString():"",b.path?"; path="+b.path:"",b.domain?"; domain="+b.domain:"",b.secure?"; secure":""].join(""))
}b=e||{};var a,f=b.raw?function(j){return j}:decodeURIComponent;return(a=new RegExp("(?:^|; )"+encodeURIComponent(d)+"=([^;]*)").exec(document.cookie))?f(a[1]):null
};(function(d){var f={put:function(j,g){(g||window).location.hash=this.encoder(j)},get:function(k){var j=((k||window).location.hash).replace(/^#/,"");
try{return d.browser.mozilla?j:decodeURIComponent(j)}catch(g){return j}},encoder:encodeURIComponent};var c={id:"__jQuery_history",init:function(){var g='<iframe id="'+this.id+'" style="display:none" src="javascript:false;" />';
d("body").prepend(g);return this},_document:function(){return d("#"+this.id)[0].contentWindow.document},put:function(j){var g=this._document();
g.open();g.close();f.put(j,g)},get:function(){return f.get(this._document())}};function e(j){j=d.extend({unescape:false},j||{});
f.encoder=k(j.unescape);function k(l){if(l===true){return function(m){return m}}if(typeof l=="string"&&(l=g(l.split("")))||typeof l=="function"){return function(m){return l(encodeURIComponent(m))
}}return encodeURIComponent}function g(m){var l=new RegExp(d.map(m,encodeURIComponent).join("|"),"ig");return function(p){return p.replace(l,decodeURIComponent)
}}}var b={};b.base={callback:undefined,type:undefined,check:function(){},load:function(g){},init:function(j,g){e(g);a.callback=j;
a._options=g;a._init()},_init:function(){},_options:{}};b.timer={_appState:undefined,_init:function(){var g=f.get();a._appState=g;
a.callback(g);setInterval(a.check,100)},check:function(){var g=f.get();if(g!=a._appState){a._appState=g;a.callback(g)}},load:function(g){if(g!=a._appState){f.put(g);
a._appState=g;a.callback(g)}}};b.iframeTimer={_appState:undefined,_init:function(){var g=f.get();a._appState=g;c.init().put(g);
a.callback(g);setInterval(a.check,100)},check:function(){var j=c.get(),g=f.get();if(g!=j){if(g==a._appState){a._appState=j;
f.put(j);a.callback(j)}else{a._appState=g;c.put(g);a.callback(g)}}},load:function(g){if(g!=a._appState){f.put(g);c.put(g);
a._appState=g;a.callback(g)}}};b.hashchangeEvent={_init:function(){a.callback(f.get());d(window).bind("hashchange",a.check)
},check:function(){a.callback(f.get())},load:function(g){f.put(g)}};var a=d.extend({},b.base);if(d.browser.msie&&(d.browser.version<8||document.documentMode<8)){a.type="iframeTimer"
}else{if("onhashchange" in window){a.type="hashchangeEvent"}else{a.type="timer"}}d.extend(a,b[a.type]);d.history=a})(jQuery);
(function(b){b.fn.ajaxSubmit=function(A){if(!this.length){a("ajaxSubmit: skipping submit process - no element selected");
return this}if(typeof A=="function"){A={success:A}}var j=this.attr("action");var d=(typeof j==="string")?b.trim(j):"";if(d){d=(d.match(/^([^#]+)/)||[])[1]
}d=d||window.location.href||"";A=b.extend(true,{url:d,success:b.ajaxSettings.success,type:this[0].getAttribute("method")||"GET",iframeSrc:/^https/i.test(window.location.href||"")?"javascript:false":"about:blank"},A);
var B={};this.trigger("form-pre-serialize",[this,A,B]);if(B.veto){a("ajaxSubmit: submit vetoed via form-pre-serialize trigger");
return this}if(A.beforeSerialize&&A.beforeSerialize(this,A)===false){a("ajaxSubmit: submit aborted via beforeSerialize callback");
return this}var f,x,r=this.formToArray(A.semantic);if(A.data){A.extraData=A.data;for(f in A.data){if(A.data[f] instanceof Array){for(var l in A.data[f]){r.push({name:f,value:A.data[f][l]})
}}else{x=A.data[f];x=b.isFunction(x)?x():x;r.push({name:f,value:x})}}}if(A.beforeSubmit&&A.beforeSubmit(r,this,A)===false){a("ajaxSubmit: submit aborted via beforeSubmit callback");
return this}this.trigger("form-submit-validate",[r,this,A,B]);if(B.veto){a("ajaxSubmit: submit vetoed via form-submit-validate trigger");
return this}var c=b.param(r);if(A.type.toUpperCase()=="GET"){A.url+=(A.url.indexOf("?")>=0?"&":"?")+c;A.data=null}else{A.data=c
}var z=this,p=[];if(A.resetForm){p.push(function(){z.resetForm()})}if(A.clearForm){p.push(function(){z.clearForm()})}if(!A.dataType&&A.target){var y=A.success||function(){};
p.push(function(q){var k=A.replaceTarget?"replaceWith":"html";b(A.target)[k](q).each(y,arguments)})}else{if(A.success){p.push(A.success)
}}A.success=function(D,q,E){var C=A.context||A;for(var v=0,k=p.length;v<k;v++){p[v].apply(C,[D,q,E||z,z])}};var g=b("input:file",this).length>0;
var e="multipart/form-data";var m=(z.attr("enctype")==e||z.attr("encoding")==e);if(A.iframe!==false&&(g||A.iframe||m)){if(A.closeKeepAlive){b.get(A.closeKeepAlive,u)
}else{u()}}else{b.ajax(A)}this.trigger("form-submit-notify",[this,A]);return this;function u(){var v=z[0];if(b(":input[name=submit],:input[id=submit]",v).length){alert('Error: Form elements must not have name or id of "submit".');
return}var J=b.extend(true,{},b.ajaxSettings,A);J.context=J.context||J;var M="jqFormIO"+(new Date().getTime()),G="_"+M;var D=b('<iframe id="'+M+'" name="'+M+'" src="'+J.iframeSrc+'" />');
var H=D[0];D.css({position:"absolute",top:"-1000px",left:"-1000px"});var E={aborted:0,responseText:null,responseXML:null,status:0,statusText:"n/a",getAllResponseHeaders:function(){},getResponseHeader:function(){},setRequestHeader:function(){},abort:function(U){var V=(U==="timeout"?"timeout":"aborted");
a("aborting upload... "+V);this.aborted=1;D.attr("src",J.iframeSrc);E.error=V;J.error&&J.error.call(J.context,E,V,V);Q&&b.event.trigger("ajaxError",[E,J,V]);
J.complete&&J.complete.call(J.context,E,V)}};var Q=J.global;if(Q&&!b.active++){b.event.trigger("ajaxStart")}if(Q){b.event.trigger("ajaxSend",[E,J])
}if(J.beforeSend&&J.beforeSend.call(J.context,E,J)===false){if(J.global){b.active--}return}if(E.aborted){return}var P=0,I;
var F=v.clk;if(F){var N=F.name;if(N&&!F.disabled){J.extraData=J.extraData||{};J.extraData[N]=F.value;if(F.type=="image"){J.extraData[N+".x"]=v.clk_x;
J.extraData[N+".y"]=v.clk_y}}}function O(){var W=z.attr("target"),U=z.attr("action");v.setAttribute("target",M);if(v.getAttribute("method")!="POST"){v.setAttribute("method","POST")
}if(v.getAttribute("action")!=J.url){v.setAttribute("action",J.url)}if(!J.skipEncodingOverride){z.attr({encoding:"multipart/form-data",enctype:"multipart/form-data"})
}if(J.timeout){I=setTimeout(function(){P=true;L(true)},J.timeout)}var V=[];try{if(J.extraData){for(var X in J.extraData){V.push(b('<input type="hidden" name="'+X+'" value="'+J.extraData[X]+'" />').appendTo(v)[0])
}}D.appendTo("body");H.attachEvent?H.attachEvent("onload",L):H.addEventListener("load",L,false);v.submit()}finally{v.setAttribute("action",U);
if(W){v.setAttribute("target",W)}else{z.removeAttr("target")}b(V).remove()}}if(J.forceSync){O()}else{setTimeout(O,10)}var S,T,R=50,C;
function L(aa){if(E.aborted||C){return}if(aa===true&&E){E.abort("timeout");return}var Z=H.contentWindow?H.contentWindow.document:H.contentDocument?H.contentDocument:H.document;
if(!Z||Z.location.href==J.iframeSrc){if(!P){return}}H.detachEvent?H.detachEvent("onload",L):H.removeEventListener("load",L,false);
var W=true;try{if(P){throw"timeout"}var ab=J.dataType=="xml"||Z.XMLDocument||b.isXMLDoc(Z);a("isXml="+ab);if(!ab&&window.opera&&(Z.body==null||Z.body.innerHTML=="")){if(--R){a("requeing onLoad callback, DOM not available");
setTimeout(L,250);return}}E.responseText=Z.body?Z.body.innerHTML:Z.documentElement?Z.documentElement.innerHTML:null;E.responseXML=Z.XMLDocument?Z.XMLDocument:Z;
if(ab){J.dataType="xml"}E.getResponseHeader=function(ad){var ac={"content-type":J.dataType};return ac[ad]};var Y=/(json|script|text)/.test(J.dataType);
if(Y||J.textarea){var V=Z.getElementsByTagName("textarea")[0];if(V){E.responseText=V.value}else{if(Y){var X=Z.getElementsByTagName("pre")[0];
var U=Z.getElementsByTagName("body")[0];if(X){E.responseText=X.textContent}else{if(U){E.responseText=U.innerHTML}}}}}else{if(J.dataType=="xml"&&!E.responseXML&&E.responseText!=null){E.responseXML=K(E.responseText)
}}S=k(E,J.dataType,J)}catch(aa){a("error caught:",aa);W=false;E.error=aa;J.error&&J.error.call(J.context,E,"error",aa);Q&&b.event.trigger("ajaxError",[E,J,aa])
}if(E.aborted){a("upload aborted");W=false}if(W){J.success&&J.success.call(J.context,S,"success",E);Q&&b.event.trigger("ajaxSuccess",[E,J])
}Q&&b.event.trigger("ajaxComplete",[E,J]);if(Q&&!--b.active){b.event.trigger("ajaxStop")}J.complete&&J.complete.call(J.context,E,W?"success":"error");
C=true;if(J.timeout){clearTimeout(I)}setTimeout(function(){D.removeData("form-plugin-onload");D.remove();E.responseXML=null
},100)}var K=b.parseXML||function(U,V){if(window.ActiveXObject){V=new ActiveXObject("Microsoft.XMLDOM");V.async="false";V.loadXML(U)
}else{V=(new DOMParser()).parseFromString(U,"text/xml")}return(V&&V.documentElement&&V.documentElement.nodeName!="parsererror")?V:null
};var q=b.parseJSON||function(U){return window["eval"]("("+U+")")};var k=function(Z,X,W){var V=Z.getResponseHeader("content-type")||"",U=X==="xml"||!X&&V.indexOf("xml")>=0,Y=U?Z.responseXML:Z.responseText;
if(U&&Y.documentElement.nodeName==="parsererror"){b.error&&b.error("parsererror")}if(W&&W.dataFilter){Y=W.dataFilter(Y,X)
}if(typeof Y==="string"){if(X==="json"||!X&&V.indexOf("json")>=0){Y=q(Y)}else{if(X==="script"||!X&&V.indexOf("javascript")>=0){b.globalEval(Y)
}}}return Y}}};b.fn.ajaxForm=function(c){if(this.length===0){var d={s:this.selector,c:this.context};if(!b.isReady&&d.s){a("DOM not ready, queuing ajaxForm");
b(function(){b(d.s,d.c).ajaxForm(c)});return this}a("terminating; zero elements found by selector"+(b.isReady?"":" (DOM not ready)"));
return this}return this.ajaxFormUnbind().bind("submit.form-plugin",function(f){if(!f.isDefaultPrevented()){f.preventDefault();
b(this).ajaxSubmit(c)}}).bind("click.form-plugin",function(l){var k=l.target;var g=b(k);if(!(g.is(":submit,input:image"))){var f=g.closest(":submit");
if(f.length==0){return}k=f[0]}var j=this;j.clk=k;if(k.type=="image"){if(l.offsetX!=undefined){j.clk_x=l.offsetX;j.clk_y=l.offsetY
}else{if(typeof b.fn.offset=="function"){var m=g.offset();j.clk_x=l.pageX-m.left;j.clk_y=l.pageY-m.top}else{j.clk_x=l.pageX-k.offsetLeft;
j.clk_y=l.pageY-k.offsetTop}}}setTimeout(function(){j.clk=j.clk_x=j.clk_y=null},100)})};b.fn.ajaxFormUnbind=function(){return this.unbind("submit.form-plugin click.form-plugin")
};b.fn.formToArray=function(u){var r=[];if(this.length===0){return r}var d=this[0];var g=u?d.getElementsByTagName("*"):d.elements;
if(!g){return r}var l,k,f,x,e,p,c;for(l=0,p=g.length;l<p;l++){e=g[l];f=e.name;if(!f){continue}if(u&&d.clk&&e.type=="image"){if(!e.disabled&&d.clk==e){r.push({name:f,value:b(e).val()});
r.push({name:f+".x",value:d.clk_x},{name:f+".y",value:d.clk_y})}continue}x=b.fieldValue(e,true);if(x&&x.constructor==Array){for(k=0,c=x.length;
k<c;k++){r.push({name:f,value:x[k]})}}else{if(x!==null&&typeof x!="undefined"){r.push({name:f,value:x})}}}if(!u&&d.clk){var m=b(d.clk),q=m[0];
f=q.name;if(f&&!q.disabled&&q.type=="image"){r.push({name:f,value:m.val()});r.push({name:f+".x",value:d.clk_x},{name:f+".y",value:d.clk_y})
}}return r};b.fn.formSerialize=function(c){return b.param(this.formToArray(c))};b.fn.fieldSerialize=function(d){var c=[];
this.each(function(){var j=this.name;if(!j){return}var f=b.fieldValue(this,d);if(f&&f.constructor==Array){for(var g=0,e=f.length;
g<e;g++){c.push({name:j,value:f[g]})}}else{if(f!==null&&typeof f!="undefined"){c.push({name:this.name,value:f})}}});return b.param(c)
};b.fn.fieldValue=function(j){for(var g=[],e=0,c=this.length;e<c;e++){var f=this[e];var d=b.fieldValue(f,j);if(d===null||typeof d=="undefined"||(d.constructor==Array&&!d.length)){continue
}d.constructor==Array?b.merge(g,d):g.push(d)}return g};b.fieldValue=function(c,k){var e=c.name,r=c.type,u=c.tagName.toLowerCase();
if(k===undefined){k=true}if(k&&(!e||c.disabled||r=="reset"||r=="button"||(r=="checkbox"||r=="radio")&&!c.checked||(r=="submit"||r=="image")&&c.form&&c.form.clk!=c||u=="select"&&c.selectedIndex==-1)){return null
}if(u=="select"){var l=c.selectedIndex;if(l<0){return null}var p=[],d=c.options;var g=(r=="select-one");var m=(g?l+1:d.length);
for(var f=(g?l:0);f<m;f++){var j=d[f];if(j.selected){var q=j.value;if(!q){q=(j.attributes&&j.attributes.value&&!(j.attributes.value.specified))?j.text:j.value
}if(g){return q}p.push(q)}}return p}return b(c).val()};b.fn.clearForm=function(){return this.each(function(){b("input,select,textarea",this).clearFields()
})};b.fn.clearFields=b.fn.clearInputs=function(){return this.each(function(){var d=this.type,c=this.tagName.toLowerCase();
if(d=="text"||d=="password"||c=="textarea"){this.value=""}else{if(d=="checkbox"||d=="radio"){this.checked=false}else{if(c=="select"){this.selectedIndex=-1
}}}})};b.fn.resetForm=function(){return this.each(function(){if(typeof this.reset=="function"||(typeof this.reset=="object"&&!this.reset.nodeType)){this.reset()
}})};b.fn.enable=function(c){if(c===undefined){c=true}return this.each(function(){this.disabled=!c})};b.fn.selected=function(c){if(c===undefined){c=true
}return this.each(function(){var d=this.type;if(d=="checkbox"||d=="radio"){this.checked=c}else{if(this.tagName.toLowerCase()=="option"){var e=b(this).parent("select");
if(c&&e[0]&&e[0].type=="select-one"){e.find("option").selected(false)}this.selected=c}}})};function a(){if(b.fn.ajaxSubmit.debug){var c="[jquery.form] "+Array.prototype.join.call(arguments,"");
if(window.console&&window.console.log){window.console.log(c)}else{if(window.opera&&window.opera.postError){window.opera.postError(c)
}}}}})(jQuery);(function(b){b.fn.ajaxSubmit=function(A){if(!this.length){a("ajaxSubmit: skipping submit process - no element selected");
return this}if(typeof A=="function"){A={success:A}}var j=this.attr("action");var d=(typeof j==="string")?b.trim(j):"";if(d){d=(d.match(/^([^#]+)/)||[])[1]
}d=d||window.location.href||"";A=b.extend(true,{url:d,success:b.ajaxSettings.success,type:this[0].getAttribute("method")||"GET",iframeSrc:/^https/i.test(window.location.href||"")?"javascript:false":"about:blank"},A);
var B={};this.trigger("form-pre-serialize",[this,A,B]);if(B.veto){a("ajaxSubmit: submit vetoed via form-pre-serialize trigger");
return this}if(A.beforeSerialize&&A.beforeSerialize(this,A)===false){a("ajaxSubmit: submit aborted via beforeSerialize callback");
return this}var f,x,r=this.formToArray(A.semantic);if(A.data){A.extraData=A.data;for(f in A.data){if(A.data[f] instanceof Array){for(var l in A.data[f]){r.push({name:f,value:A.data[f][l]})
}}else{x=A.data[f];x=b.isFunction(x)?x():x;r.push({name:f,value:x})}}}if(A.beforeSubmit&&A.beforeSubmit(r,this,A)===false){a("ajaxSubmit: submit aborted via beforeSubmit callback");
return this}this.trigger("form-submit-validate",[r,this,A,B]);if(B.veto){a("ajaxSubmit: submit vetoed via form-submit-validate trigger");
return this}var c=b.param(r);if(A.type.toUpperCase()=="GET"){A.url+=(A.url.indexOf("?")>=0?"&":"?")+c;A.data=null}else{A.data=c
}var z=this,p=[];if(A.resetForm){p.push(function(){z.resetForm()})}if(A.clearForm){p.push(function(){z.clearForm()})}if(!A.dataType&&A.target){var y=A.success||function(){};
p.push(function(q){var k=A.replaceTarget?"replaceWith":"html";b(A.target)[k](q).each(y,arguments)})}else{if(A.success){p.push(A.success)
}}A.success=function(D,q,E){var C=A.context||A;for(var v=0,k=p.length;v<k;v++){p[v].apply(C,[D,q,E||z,z])}};var g=b("input:file",this).length>0;
var e="multipart/form-data";var m=(z.attr("enctype")==e||z.attr("encoding")==e);if(A.iframe!==false&&(g||A.iframe||m)){if(A.closeKeepAlive){b.get(A.closeKeepAlive,u)
}else{u()}}else{b.ajax(A)}this.trigger("form-submit-notify",[this,A]);return this;function u(){var v=z[0];if(b(":input[name=submit],:input[id=submit]",v).length){alert('Error: Form elements must not have name or id of "submit".');
return}var J=b.extend(true,{},b.ajaxSettings,A);J.context=J.context||J;var M="jqFormIO"+(new Date().getTime()),G="_"+M;var D=b('<iframe id="'+M+'" name="'+M+'" src="'+J.iframeSrc+'" />');
var H=D[0];D.css({position:"absolute",top:"-1000px",left:"-1000px"});var E={aborted:0,responseText:null,responseXML:null,status:0,statusText:"n/a",getAllResponseHeaders:function(){},getResponseHeader:function(){},setRequestHeader:function(){},abort:function(U){var V=(U==="timeout"?"timeout":"aborted");
a("aborting upload... "+V);this.aborted=1;D.attr("src",J.iframeSrc);E.error=V;J.error&&J.error.call(J.context,E,V,V);Q&&b.event.trigger("ajaxError",[E,J,V]);
J.complete&&J.complete.call(J.context,E,V)}};var Q=J.global;if(Q&&!b.active++){b.event.trigger("ajaxStart")}if(Q){b.event.trigger("ajaxSend",[E,J])
}if(J.beforeSend&&J.beforeSend.call(J.context,E,J)===false){if(J.global){b.active--}return}if(E.aborted){return}var P=0,I;
var F=v.clk;if(F){var N=F.name;if(N&&!F.disabled){J.extraData=J.extraData||{};J.extraData[N]=F.value;if(F.type=="image"){J.extraData[N+".x"]=v.clk_x;
J.extraData[N+".y"]=v.clk_y}}}function O(){var W=z.attr("target"),U=z.attr("action");v.setAttribute("target",M);if(v.getAttribute("method")!="POST"){v.setAttribute("method","POST")
}if(v.getAttribute("action")!=J.url){v.setAttribute("action",J.url)}if(!J.skipEncodingOverride){z.attr({encoding:"multipart/form-data",enctype:"multipart/form-data"})
}if(J.timeout){I=setTimeout(function(){P=true;L(true)},J.timeout)}var V=[];try{if(J.extraData){for(var X in J.extraData){V.push(b('<input type="hidden" name="'+X+'" value="'+J.extraData[X]+'" />').appendTo(v)[0])
}}D.appendTo("body");H.attachEvent?H.attachEvent("onload",L):H.addEventListener("load",L,false);v.submit()}finally{v.setAttribute("action",U);
if(W){v.setAttribute("target",W)}else{z.removeAttr("target")}b(V).remove()}}if(J.forceSync){O()}else{setTimeout(O,10)}var S,T,R=50,C;
function L(aa){if(E.aborted||C){return}if(aa===true&&E){E.abort("timeout");return}var Z=H.contentWindow?H.contentWindow.document:H.contentDocument?H.contentDocument:H.document;
if(!Z||Z.location.href==J.iframeSrc){if(!P){return}}H.detachEvent?H.detachEvent("onload",L):H.removeEventListener("load",L,false);
var W=true;try{if(P){throw"timeout"}var ab=J.dataType=="xml"||Z.XMLDocument||b.isXMLDoc(Z);a("isXml="+ab);if(!ab&&window.opera&&(Z.body==null||Z.body.innerHTML=="")){if(--R){a("requeing onLoad callback, DOM not available");
setTimeout(L,250);return}}E.responseText=Z.body?Z.body.innerHTML:Z.documentElement?Z.documentElement.innerHTML:null;E.responseXML=Z.XMLDocument?Z.XMLDocument:Z;
if(ab){J.dataType="xml"}E.getResponseHeader=function(ad){var ac={"content-type":J.dataType};return ac[ad]};var Y=/(json|script|text)/.test(J.dataType);
if(Y||J.textarea){var V=Z.getElementsByTagName("textarea")[0];if(V){E.responseText=V.value}else{if(Y){var X=Z.getElementsByTagName("pre")[0];
var U=Z.getElementsByTagName("body")[0];if(X){E.responseText=X.textContent}else{if(U){E.responseText=U.innerHTML}}}}}else{if(J.dataType=="xml"&&!E.responseXML&&E.responseText!=null){E.responseXML=K(E.responseText)
}}S=k(E,J.dataType,J)}catch(aa){a("error caught:",aa);W=false;E.error=aa;J.error&&J.error.call(J.context,E,"error",aa);Q&&b.event.trigger("ajaxError",[E,J,aa])
}if(E.aborted){a("upload aborted");W=false}if(W){J.success&&J.success.call(J.context,S,"success",E);Q&&b.event.trigger("ajaxSuccess",[E,J])
}Q&&b.event.trigger("ajaxComplete",[E,J]);if(Q&&!--b.active){b.event.trigger("ajaxStop")}J.complete&&J.complete.call(J.context,E,W?"success":"error");
C=true;if(J.timeout){clearTimeout(I)}setTimeout(function(){D.removeData("form-plugin-onload");D.remove();E.responseXML=null
},100)}var K=b.parseXML||function(U,V){if(window.ActiveXObject){V=new ActiveXObject("Microsoft.XMLDOM");V.async="false";V.loadXML(U)
}else{V=(new DOMParser()).parseFromString(U,"text/xml")}return(V&&V.documentElement&&V.documentElement.nodeName!="parsererror")?V:null
};var q=b.parseJSON||function(U){return window["eval"]("("+U+")")};var k=function(Z,X,W){var V=Z.getResponseHeader("content-type")||"",U=X==="xml"||!X&&V.indexOf("xml")>=0,Y=U?Z.responseXML:Z.responseText;
if(U&&Y.documentElement.nodeName==="parsererror"){b.error&&b.error("parsererror")}if(W&&W.dataFilter){Y=W.dataFilter(Y,X)
}if(typeof Y==="string"){if(X==="json"||!X&&V.indexOf("json")>=0){Y=q(Y)}else{if(X==="script"||!X&&V.indexOf("javascript")>=0){b.globalEval(Y)
}}}return Y}}};b.fn.ajaxForm=function(c){if(this.length===0){var d={s:this.selector,c:this.context};if(!b.isReady&&d.s){a("DOM not ready, queuing ajaxForm");
b(function(){b(d.s,d.c).ajaxForm(c)});return this}a("terminating; zero elements found by selector"+(b.isReady?"":" (DOM not ready)"));
return this}return this.ajaxFormUnbind().bind("submit.form-plugin",function(f){if(!f.isDefaultPrevented()){f.preventDefault();
b(this).ajaxSubmit(c)}}).bind("click.form-plugin",function(l){var k=l.target;var g=b(k);if(!(g.is(":submit,input:image"))){var f=g.closest(":submit");
if(f.length==0){return}k=f[0]}var j=this;j.clk=k;if(k.type=="image"){if(l.offsetX!=undefined){j.clk_x=l.offsetX;j.clk_y=l.offsetY
}else{if(typeof b.fn.offset=="function"){var m=g.offset();j.clk_x=l.pageX-m.left;j.clk_y=l.pageY-m.top}else{j.clk_x=l.pageX-k.offsetLeft;
j.clk_y=l.pageY-k.offsetTop}}}setTimeout(function(){j.clk=j.clk_x=j.clk_y=null},100)})};b.fn.ajaxFormUnbind=function(){return this.unbind("submit.form-plugin click.form-plugin")
};b.fn.formToArray=function(u){var r=[];if(this.length===0){return r}var d=this[0];var g=u?d.getElementsByTagName("*"):d.elements;
if(!g){return r}var l,k,f,x,e,p,c;for(l=0,p=g.length;l<p;l++){e=g[l];f=e.name;if(!f){continue}if(u&&d.clk&&e.type=="image"){if(!e.disabled&&d.clk==e){r.push({name:f,value:b(e).val()});
r.push({name:f+".x",value:d.clk_x},{name:f+".y",value:d.clk_y})}continue}x=b.fieldValue(e,true);if(x&&x.constructor==Array){for(k=0,c=x.length;
k<c;k++){r.push({name:f,value:x[k]})}}else{if(x!==null&&typeof x!="undefined"){r.push({name:f,value:x})}}}if(!u&&d.clk){var m=b(d.clk),q=m[0];
f=q.name;if(f&&!q.disabled&&q.type=="image"){r.push({name:f,value:m.val()});r.push({name:f+".x",value:d.clk_x},{name:f+".y",value:d.clk_y})
}}return r};b.fn.formSerialize=function(c){return b.param(this.formToArray(c))};b.fn.fieldSerialize=function(d){var c=[];
this.each(function(){var j=this.name;if(!j){return}var f=b.fieldValue(this,d);if(f&&f.constructor==Array){for(var g=0,e=f.length;
g<e;g++){c.push({name:j,value:f[g]})}}else{if(f!==null&&typeof f!="undefined"){c.push({name:this.name,value:f})}}});return b.param(c)
};b.fn.fieldValue=function(j){for(var g=[],e=0,c=this.length;e<c;e++){var f=this[e];var d=b.fieldValue(f,j);if(d===null||typeof d=="undefined"||(d.constructor==Array&&!d.length)){continue
}d.constructor==Array?b.merge(g,d):g.push(d)}return g};b.fieldValue=function(c,k){var e=c.name,r=c.type,u=c.tagName.toLowerCase();
if(k===undefined){k=true}if(k&&(!e||c.disabled||r=="reset"||r=="button"||(r=="checkbox"||r=="radio")&&!c.checked||(r=="submit"||r=="image")&&c.form&&c.form.clk!=c||u=="select"&&c.selectedIndex==-1)){return null
}if(u=="select"){var l=c.selectedIndex;if(l<0){return null}var p=[],d=c.options;var g=(r=="select-one");var m=(g?l+1:d.length);
for(var f=(g?l:0);f<m;f++){var j=d[f];if(j.selected){var q=j.value;if(!q){q=(j.attributes&&j.attributes.value&&!(j.attributes.value.specified))?j.text:j.value
}if(g){return q}p.push(q)}}return p}return b(c).val()};b.fn.clearForm=function(){return this.each(function(){b("input,select,textarea",this).clearFields()
})};b.fn.clearFields=b.fn.clearInputs=function(){return this.each(function(){var d=this.type,c=this.tagName.toLowerCase();
if(d=="text"||d=="password"||c=="textarea"){this.value=""}else{if(d=="checkbox"||d=="radio"){this.checked=false}else{if(c=="select"){this.selectedIndex=-1
}}}})};b.fn.resetForm=function(){return this.each(function(){if(typeof this.reset=="function"||(typeof this.reset=="object"&&!this.reset.nodeType)){this.reset()
}})};b.fn.enable=function(c){if(c===undefined){c=true}return this.each(function(){this.disabled=!c})};b.fn.selected=function(c){if(c===undefined){c=true
}return this.each(function(){var d=this.type;if(d=="checkbox"||d=="radio"){this.checked=c}else{if(this.tagName.toLowerCase()=="option"){var e=b(this).parent("select");
if(c&&e[0]&&e[0].type=="select-one"){e.find("option").selected(false)}this.selected=c}}})};function a(){if(b.fn.ajaxSubmit.debug){var c="[jquery.form] "+Array.prototype.join.call(arguments,"");
if(window.console&&window.console.log){window.console.log(c)}else{if(window.opera&&window.opera.postError){window.opera.postError(c)
}}}}})(jQuery);(function($){var $b=$.browser;$.layout={browser:{mozilla:!!$b.mozilla,webkit:!!$b.webkit||!!$b.safari,msie:!!$b.msie,isIE6:!!$b.msie&&$b.version==6,boxModel:false},scrollbarWidth:function(){return window.scrollbarWidth||$.layout.getScrollbarSize("width")
},scrollbarHeight:function(){return window.scrollbarHeight||$.layout.getScrollbarSize("height")},getScrollbarSize:function(dim){var $c=$('<div style="position: absolute; top: -10000px; left: -10000px; width: 100px; height: 100px; overflow: scroll;"></div>').appendTo("body");
var d={width:$c.width()-$c[0].clientWidth,height:$c.height()-$c[0].clientHeight};$c.remove();window.scrollbarWidth=d.width;
window.scrollbarHeight=d.height;return dim.match(/^(width|height)$/i)?d[dim]:d},showInvisibly:function($E,force){if(!$E){return{}
}if(!$E.jquery){$E=$($E)}var CSS={display:$E.css("display"),visibility:$E.css("visibility")};if(force||CSS.display=="none"){$E.css({display:"block",visibility:"hidden"});
return CSS}else{return{}}},getElemDims:function($E){var d={},x=d.css={},i={},b,p,off=$E.offset();d.offsetLeft=off.left;d.offsetTop=off.top;
$.each("Left,Right,Top,Bottom".split(","),function(idx,e){b=x["border"+e]=$.layout.borderWidth($E,e);p=x["padding"+e]=$.layout.cssNum($E,"padding"+e);
i[e]=b+p;d["inset"+e]=p});d.offsetWidth=$E.innerWidth();d.offsetHeight=$E.innerHeight();d.outerWidth=$E.outerWidth();d.outerHeight=$E.outerHeight();
d.innerWidth=d.outerWidth-i.Left-i.Right;d.innerHeight=d.outerHeight-i.Top-i.Bottom;x.width=$E.width();x.height=$E.height();
return d},getElemCSS:function($E,list){var CSS={},style=$E[0].style,props=list.split(","),sides="Top,Bottom,Left,Right".split(","),attrs="Color,Style,Width".split(","),p,s,a,i,j,k;
for(i=0;i<props.length;i++){p=props[i];if(p.match(/(border|padding|margin)$/)){for(j=0;j<4;j++){s=sides[j];if(p=="border"){for(k=0;
k<3;k++){a=attrs[k];CSS[p+s+a]=style[p+s+a]}}else{CSS[p+s]=style[p+s]}}}else{CSS[p]=style[p]}}return CSS},cssWidth:function($E,outerWidth){var b=$.layout.borderWidth,n=$.layout.cssNum;
if(outerWidth<=0){return 0}if(!$.layout.browser.boxModel){return outerWidth}var W=outerWidth-b($E,"Left")-b($E,"Right")-n($E,"paddingLeft")-n($E,"paddingRight");
return W>0?W:0},cssHeight:function($E,outerHeight){var b=$.layout.borderWidth,n=$.layout.cssNum;if(outerHeight<=0){return 0
}if(!$.layout.browser.boxModel){return outerHeight}var H=outerHeight-b($E,"Top")-b($E,"Bottom")-n($E,"paddingTop")-n($E,"paddingBottom");
return H>0?H:0},cssNum:function($E,prop){if(!$E.jquery){$E=$($E)}var CSS=$.layout.showInvisibly($E);var val=parseInt($.curCSS($E[0],prop,true),10)||0;
$E.css(CSS);return val},borderWidth:function(el,side){if(el.jquery){el=el[0]}var b="border"+side.substr(0,1).toUpperCase()+side.substr(1);
return $.curCSS(el,b+"Style",true)=="none"?0:(parseInt($.curCSS(el,b+"Width",true),10)||0)},isMouseOverElem:function(evt,el){var $E=$(el||this),d=$E.offset(),T=d.top,L=d.left,R=L+$E.outerWidth(),B=T+$E.outerHeight(),x=evt.pageX,y=evt.pageY;
return($.layout.browser.msie&&x<0&&y<0)||((x>=L&&x<=R)&&(y>=T&&y<=B))}};$.fn.layout=function(opts){var lang={Pane:"Pane",Open:"Open",Close:"Close",Resize:"Resize",Slide:"Slide Open",Pin:"Pin",Unpin:"Un-Pin",selector:"selector",msgNoRoom:"Not enough room to show this pane.",errContainerMissing:"UI Layout Initialization Error\n\nThe specified layout-container does not exist.",errCenterPaneMissing:"UI Layout Initialization Error\n\nThe center-pane element does not exist.\n\nThe center-pane is a required element.",errContainerHeight:"UI Layout Initialization Warning\n\nThe layout-container \"CONTAINER\" has no height.\n\nTherefore the layout is 0-height and hence 'invisible'!",errButton:"Error Adding Button \n\nInvalid "};
var options={name:"",scrollToBookmarkOnLoad:true,resizeWithWindow:true,resizeWithWindowDelay:200,resizeWithWindowMaxDelay:0,onresizeall_start:null,onresizeall_end:null,onload:null,onunload:null,autoBindCustomButtons:false,zIndex:null,defaults:{applyDemoStyles:false,closable:true,resizable:true,slidable:true,initClosed:false,initHidden:false,contentSelector:".ui-layout-content",contentIgnoreSelector:".ui-layout-ignore",findNestedContent:false,paneClass:"ui-layout-pane",resizerClass:"ui-layout-resizer",togglerClass:"ui-layout-toggler",buttonClass:"ui-layout-button",minSize:0,maxSize:0,spacing_open:6,spacing_closed:6,togglerLength_open:50,togglerLength_closed:50,togglerAlign_open:"center",togglerAlign_closed:"center",togglerTip_open:lang.Close,togglerTip_closed:lang.Open,togglerContent_open:"",togglerContent_closed:"",resizerDblClickToggle:true,autoResize:true,autoReopen:true,resizerDragOpacity:1,maskIframesOnResize:true,resizeNestedLayout:true,resizeWhileDragging:false,resizeContentWhileDragging:false,noRoomToOpenTip:lang.msgNoRoom,resizerTip:lang.Resize,sliderTip:lang.Slide,sliderCursor:"pointer",slideTrigger_open:"click",slideTrigger_close:"mouseleave",hideTogglerOnSlide:false,preventQuickSlideClose:!!($.browser.webkit||$.browser.safari),preventPrematureSlideClose:false,showOverflowOnHover:false,enableCursorHotkey:true,customHotkeyModifier:"SHIFT",fxName:"slide",fxSpeed:null,fxSettings:{},fxOpacityFix:true,triggerEventsOnLoad:false,triggerEventsWhileDragging:true,onshow_start:null,onshow_end:null,onhide_start:null,onhide_end:null,onopen_start:null,onopen_end:null,onclose_start:null,onclose_end:null,onresize_start:null,onresize_end:null,onsizecontent_start:null,onsizecontent_end:null,onswap_start:null,onswap_end:null,ondrag_start:null,ondrag_end:null},north:{paneSelector:".ui-layout-north",size:"auto",resizerCursor:"n-resize",customHotkey:""},south:{paneSelector:".ui-layout-south",size:"auto",resizerCursor:"s-resize",customHotkey:""},east:{paneSelector:".ui-layout-east",size:200,resizerCursor:"e-resize",customHotkey:""},west:{paneSelector:".ui-layout-west",size:200,resizerCursor:"w-resize",customHotkey:""},center:{paneSelector:".ui-layout-center",minWidth:0,minHeight:0},useStateCookie:false,cookie:{name:"",autoSave:true,autoLoad:true,domain:"",path:"",expires:"",secure:false,keys:"north.size,south.size,east.size,west.size,north.isClosed,south.isClosed,east.isClosed,west.isClosed,north.isHidden,south.isHidden,east.isHidden,west.isHidden"}};
var effects={slide:{all:{duration:"fast"},north:{direction:"up"},south:{direction:"down"},east:{direction:"right"},west:{direction:"left"}},drop:{all:{duration:"slow"},north:{direction:"up"},south:{direction:"down"},east:{direction:"right"},west:{direction:"left"}},scale:{all:{duration:"fast"}}};
var state={id:"layout"+new Date().getTime(),initialized:false,container:{},north:{},south:{},east:{},west:{},center:{},cookie:{}};
var _c={allPanes:"north,south,west,east,center",borderPanes:"north,south,west,east",altSide:{north:"south",south:"north",east:"west",west:"east"},hidden:{visibility:"hidden"},visible:{visibility:"visible"},zIndex:{pane_normal:1,resizer_normal:2,iframe_mask:2,pane_sliding:100,pane_animate:1000,resizer_drag:10000},resizers:{cssReq:{position:"absolute",padding:0,margin:0,fontSize:"1px",textAlign:"left",overflow:"hidden"},cssDemo:{background:"#DDD",border:"none"}},togglers:{cssReq:{position:"absolute",display:"block",padding:0,margin:0,overflow:"hidden",textAlign:"center",fontSize:"1px",cursor:"pointer",zIndex:1},cssDemo:{background:"#AAA"}},content:{cssReq:{position:"relative"},cssDemo:{overflow:"auto",padding:"10px"},cssDemoPane:{overflow:"hidden",padding:0}},panes:{cssReq:{position:"absolute",margin:0},cssDemo:{padding:"10px",background:"#FFF",border:"1px solid #BBB",overflow:"auto"}},north:{side:"Top",sizeType:"Height",dir:"horz",cssReq:{top:0,bottom:"auto",left:0,right:0,width:"auto"},pins:[]},south:{side:"Bottom",sizeType:"Height",dir:"horz",cssReq:{top:"auto",bottom:0,left:0,right:0,width:"auto"},pins:[]},east:{side:"Right",sizeType:"Width",dir:"vert",cssReq:{left:"auto",right:0,top:"auto",bottom:"auto",height:"auto"},pins:[]},west:{side:"Left",sizeType:"Width",dir:"vert",cssReq:{left:0,right:"auto",top:"auto",bottom:"auto",height:"auto"},pins:[]},center:{dir:"center",cssReq:{left:"auto",right:"auto",top:"auto",bottom:"auto",height:"auto",width:"auto"}}};
var timer={data:{},set:function(s,fn,ms){timer.clear(s);timer.data[s]=setTimeout(fn,ms)},clear:function(s){var t=timer.data;
if(t[s]){clearTimeout(t[s]);delete t[s]}}};var isStr=function(o){try{return typeof o=="string"||(typeof o=="object"&&o.constructor.toString().match(/string/i)!==null)
}catch(e){return false}};var str=function(o){return isStr(o)?$.trim(o):o==undefined||o==null?"":o};var min=function(x,y){return Math.min(x,y)
};var max=function(x,y){return Math.max(x,y)};var _transformData=function(d){var a,json={cookie:{},defaults:{fxSettings:{}},north:{fxSettings:{}},south:{fxSettings:{}},east:{fxSettings:{}},west:{fxSettings:{}},center:{fxSettings:{}}};
d=d||{};if(d.effects||d.cookie||d.defaults||d.north||d.south||d.west||d.east||d.center){json=$.extend(true,json,d)}else{$.each(d,function(key,val){a=key.split("__");
if(!a[1]||json[a[0]]){json[a[1]?a[0]:"defaults"][a[1]?a[1]:a[0]]=val}})}return json};var _queue=function(action,pane,param){var tried=[];
$.each(_c.borderPanes.split(","),function(i,p){if(_c[p].isMoving){bindCallback(p);return false}});function bindCallback(p){var c=_c[p];
if(!c.doCallback){c.doCallback=true;c.callback=action+","+pane+","+(param?1:0)}else{tried.push(p);var cbPane=c.callback.split(",")[1];
if(cbPane!=pane&&!$.inArray(cbPane,tried)>=0){bindCallback(cbPane)}}}};var _dequeue=function(pane){var c=_c[pane];_c.isLayoutBusy=false;
delete c.isMoving;if(!c.doCallback||!c.callback){return}c.doCallback=false;var cb=c.callback.split(","),param=(cb[2]>0?true:false);
if(cb[0]=="open"){open(cb[1],param)}else{if(cb[0]=="close"){close(cb[1],param)}}if(!c.doCallback){c.callback=null}};var _execCallback=function(pane,v_fn){if(!v_fn){return
}var fn;try{if(typeof v_fn=="function"){fn=v_fn}else{if(!isStr(v_fn)){return}else{if(v_fn.match(/,/)){var args=v_fn.split(",");
fn=eval(args[0]);if(typeof fn=="function"&&args.length>1){return fn(args[1])}}else{fn=eval(v_fn)}}}if(typeof fn=="function"){if(pane&&$Ps[pane]){return fn(pane,$Ps[pane],$.extend({},state[pane]),options[pane],options.name)
}else{return fn(Instance,$.extend({},state),options,options.name)}}}catch(ex){}};var _showInvisibly=function($E,force){if(!$E){return{}
}if(!$E.jquery){$E=$($E)}var CSS={display:$E.css("display"),visibility:$E.css("visibility")};if(force||CSS.display=="none"){$E.css({display:"block",visibility:"hidden"});
return CSS}else{return{}}};var _fixIframe=function(pane){if(state.browser.mozilla){return}var $P=$Ps[pane];if(state[pane].tagName=="IFRAME"){$P.css(_c.hidden).css(_c.visible)
}else{$P.find("IFRAME").css(_c.hidden).css(_c.visible)}};var _cssNum=function($E,prop){if(!$E.jquery){$E=$($E)}var CSS=_showInvisibly($E);
var val=parseInt($.curCSS($E[0],prop,true),10)||0;$E.css(CSS);return val};var _borderWidth=function(E,side){if(E.jquery){E=E[0]
}var b="border"+side.substr(0,1).toUpperCase()+side.substr(1);return $.curCSS(E,b+"Style",true)=="none"?0:(parseInt($.curCSS(E,b+"Width",true),10)||0)
};var cssW=function(el,outerWidth){var str=isStr(el),$E=str?$Ps[el]:$(el);if(isNaN(outerWidth)){outerWidth=str?getPaneSize(el):$E.outerWidth()
}if(outerWidth<=0){return 0}if(!state.browser.boxModel){return outerWidth}var W=outerWidth-_borderWidth($E,"Left")-_borderWidth($E,"Right")-_cssNum($E,"paddingLeft")-_cssNum($E,"paddingRight");
return W>0?W:0};var cssH=function(el,outerHeight){var str=isStr(el),$E=str?$Ps[el]:$(el);if(isNaN(outerHeight)){outerHeight=str?getPaneSize(el):$E.outerHeight()
}if(outerHeight<=0){return 0}if(!state.browser.boxModel){return outerHeight}var H=outerHeight-_borderWidth($E,"Top")-_borderWidth($E,"Bottom")-_cssNum($E,"paddingTop")-_cssNum($E,"paddingBottom");
return H>0?H:0};var cssSize=function(pane,outerSize){if(_c[pane].dir=="horz"){return cssH(pane,outerSize)}else{return cssW(pane,outerSize)
}};var cssMinDims=function(pane){var dir=_c[pane].dir,d={minWidth:1001-cssW(pane,1000),minHeight:1001-cssH(pane,1000)};if(dir=="horz"){d.minSize=d.minHeight
}if(dir=="vert"){d.minSize=d.minWidth}return d};var setOuterWidth=function(el,outerWidth,autoHide){var $E=el,w;if(isStr(el)){$E=$Ps[el]
}else{if(!el.jquery){$E=$(el)}}w=cssW($E,outerWidth);$E.css({width:w});if(w>0){if(autoHide&&$E.data("autoHidden")&&$E.innerHeight()>0){$E.show().data("autoHidden",false);
if(!state.browser.mozilla){$E.css(_c.hidden).css(_c.visible)}}}else{if(autoHide&&!$E.data("autoHidden")){$E.hide().data("autoHidden",true)
}}};var setOuterHeight=function(el,outerHeight,autoHide){var $E=el,h;if(isStr(el)){$E=$Ps[el]}else{if(!el.jquery){$E=$(el)
}}h=cssH($E,outerHeight);$E.css({height:h,visibility:"visible"});if(h>0&&$E.innerWidth()>0){if(autoHide&&$E.data("autoHidden")){$E.show().data("autoHidden",false);
if(!state.browser.mozilla){$E.css(_c.hidden).css(_c.visible)}}}else{if(autoHide&&!$E.data("autoHidden")){$E.hide().data("autoHidden",true)
}}};var setOuterSize=function(el,outerSize,autoHide){if(_c[pane].dir=="horz"){setOuterHeight(el,outerSize,autoHide)}else{setOuterWidth(el,outerSize,autoHide)
}};var _parseSize=function(pane,size,dir){if(!dir){dir=_c[pane].dir}if(isStr(size)&&size.match(/%/)){size=parseInt(size,10)/100
}if(size===0){return 0}else{if(size>=1){return parseInt(size,10)}else{if(size>0){var o=options,avail;if(dir=="horz"){avail=sC.innerHeight-($Ps.north?o.north.spacing_open:0)-($Ps.south?o.south.spacing_open:0)
}else{if(dir=="vert"){avail=sC.innerWidth-($Ps.west?o.west.spacing_open:0)-($Ps.east?o.east.spacing_open:0)}}return Math.floor(avail*size)
}else{if(pane=="center"){return 0}else{var $P=$Ps[pane],dim=(dir=="horz"?"height":"width"),vis=_showInvisibly($P),s=$P.css(dim);
$P.css(dim,"auto");size=(dim=="height")?$P.outerHeight():$P.outerWidth();$P.css(dim,s).css(vis);return size}}}}};var getPaneSize=function(pane,inclSpace){var $P=$Ps[pane],o=options[pane],s=state[pane],oSp=(inclSpace?o.spacing_open:0),cSp=(inclSpace?o.spacing_closed:0);
if(!$P||s.isHidden){return 0}else{if(s.isClosed||(s.isSliding&&inclSpace)){return cSp}else{if(_c[pane].dir=="horz"){return $P.outerHeight()+oSp
}else{return $P.outerWidth()+oSp}}}};var setSizeLimits=function(pane,slide){var o=options[pane],s=state[pane],c=_c[pane],dir=c.dir,side=c.side.toLowerCase(),type=c.sizeType.toLowerCase(),isSliding=(slide!=undefined?slide:s.isSliding),$P=$Ps[pane],paneSpacing=o.spacing_open,altPane=_c.altSide[pane],altS=state[altPane],$altP=$Ps[altPane],altPaneSize=(!$altP||altS.isVisible===false||altS.isSliding?0:(dir=="horz"?$altP.outerHeight():$altP.outerWidth())),altPaneSpacing=((!$altP||altS.isHidden?0:options[altPane][altS.isClosed!==false?"spacing_closed":"spacing_open"])||0),containerSize=(dir=="horz"?sC.innerHeight:sC.innerWidth),minCenterDims=cssMinDims("center"),minCenterSize=dir=="horz"?max(options.center.minHeight,minCenterDims.minHeight):max(options.center.minWidth,minCenterDims.minWidth),limitSize=(containerSize-paneSpacing-(isSliding?0:(_parseSize("center",minCenterSize,dir)+altPaneSize+altPaneSpacing))),minSize=s.minSize=max(_parseSize(pane,o.minSize),cssMinDims(pane).minSize),maxSize=s.maxSize=min((o.maxSize?_parseSize(pane,o.maxSize):100000),limitSize),r=s.resizerPosition={},top=sC.insetTop,left=sC.insetLeft,W=sC.innerWidth,H=sC.innerHeight,rW=o.spacing_open;
switch(pane){case"north":r.min=top+minSize;r.max=top+maxSize;break;case"west":r.min=left+minSize;r.max=left+maxSize;break;
case"south":r.min=top+H-maxSize-rW;r.max=top+H-minSize-rW;break;case"east":r.min=left+W-maxSize-rW;r.max=left+W-minSize-rW;
break}};var calcNewCenterPaneDims=function(){var d={top:getPaneSize("north",true),bottom:getPaneSize("south",true),left:getPaneSize("west",true),right:getPaneSize("east",true),width:0,height:0};
d.width=sC.innerWidth-d.left-d.right;d.height=sC.innerHeight-d.bottom-d.top;d.top+=sC.insetTop;d.bottom+=sC.insetBottom;d.left+=sC.insetLeft;
d.right+=sC.insetRight;return d};var getElemDims=function($E){var d={},x=d.css={},i={},b,p,off=$E.offset();d.offsetLeft=off.left;
d.offsetTop=off.top;$.each("Left,Right,Top,Bottom".split(","),function(idx,e){b=x["border"+e]=_borderWidth($E,e);p=x["padding"+e]=_cssNum($E,"padding"+e);
i[e]=b+p;d["inset"+e]=p});d.offsetWidth=$E.innerWidth();d.offsetHeight=$E.innerHeight();d.outerWidth=$E.outerWidth();d.outerHeight=$E.outerHeight();
d.innerWidth=d.outerWidth-i.Left-i.Right;d.innerHeight=d.outerHeight-i.Top-i.Bottom;x.width=$E.width();x.height=$E.height();
return d};var getElemCSS=function($E,list){var CSS={},style=$E[0].style,props=list.split(","),sides="Top,Bottom,Left,Right".split(","),attrs="Color,Style,Width".split(","),p,s,a,i,j,k;
for(i=0;i<props.length;i++){p=props[i];if(p.match(/(border|padding|margin)$/)){for(j=0;j<4;j++){s=sides[j];if(p=="border"){for(k=0;
k<3;k++){a=attrs[k];CSS[p+s+a]=style[p+s+a]}}else{CSS[p+s]=style[p+s]}}}else{CSS[p]=style[p]}}return CSS};var getHoverClasses=function(el,allStates){var $El=$(el),type=$El.data("layoutRole"),pane=$El.data("layoutEdge"),o=options[pane],root=o[type+"Class"],_pane="-"+pane,_open="-open",_closed="-closed",_slide="-sliding",_hover="-hover ",_state=$El.hasClass(root+_closed)?_closed:_open,_alt=_state==_closed?_open:_closed,classes=(root+_hover)+(root+_pane+_hover)+(root+_state+_hover)+(root+_pane+_state+_hover);
if(allStates){classes+=(root+_alt+_hover)+(root+_pane+_alt+_hover)}if(type=="resizer"&&$El.hasClass(root+_slide)){classes+=(root+_slide+_hover)+(root+_pane+_slide+_hover)
}return $.trim(classes)};var addHover=function(evt,el){var e=el||this;$(e).addClass(getHoverClasses(e))};var removeHover=function(evt,el){var e=el||this;
$(e).removeClass(getHoverClasses(e,true))};var onResizerEnter=function(evt){$("body").disableSelection();addHover(evt,this)
};var onResizerLeave=function(evt,el){var e=el||this,pane=$(e).data("layoutEdge"),name=pane+"ResizerLeave";timer.clear(name);
if(!el){removeHover(evt,this);timer.set(name,function(){onResizerLeave(evt,e)},200)}else{if(!state[pane].isResizing){$("body").enableSelection()
}}};var _create=function(){initOptions();var o=options;if(false===_execCallback(null,o.onload)){return false}if(!getPane("center").length){alert(lang.errCenterPaneMissing);
return null}if(o.useStateCookie&&o.cookie.autoLoad){loadCookie()}state.browser={mozilla:$.browser.mozilla,webkit:$.browser.webkit||$.browser.safari,msie:$.browser.msie,isIE6:$.browser.msie&&$.browser.version==6,boxModel:$.support.boxModel};
initContainer();initPanes();sizeContent();if(o.scrollToBookmarkOnLoad){var l=self.location;if(l.hash){l.replace(l.hash)}}if(o.autoBindCustomButtons){initButtons()
}initHotkeys();if(o.resizeWithWindow&&!$Container.data("layoutRole")){$(window).bind("resize."+sID,windowResize)}$(window).bind("unload."+sID,unload);
state.initialized=true};var windowResize=function(){var delay=Number(options.resizeWithWindowDelay)||100;if(delay>0){timer.clear("winResize");
timer.set("winResize",function(){timer.clear("winResize");timer.clear("winResizeRepeater");resizeAll()},delay);if(!timer.data.winResizeRepeater){setWindowResizeRepeater()
}}};var setWindowResizeRepeater=function(){var delay=Number(options.resizeWithWindowMaxDelay);if(delay>0){timer.set("winResizeRepeater",function(){setWindowResizeRepeater();
resizeAll()},delay)}};var unload=function(){var o=options;state.cookie=getState();if(o.useStateCookie&&o.cookie.autoSave){saveCookie()
}_execCallback(null,o.onunload)};var initContainer=function(){var $C=$Container,tag=sC.tagName=$C.attr("tagName"),fullPage=(tag=="BODY"),props="position,margin,padding,border",CSS={};
sC.selector=$C.selector.split(".slice")[0];sC.ref=tag+"/"+sC.selector;$C.data("layout",Instance).data("layoutContainer",sID);
if(!$C.data("layoutCSS")){if(fullPage){CSS=$.extend(getElemCSS($C,props),{height:$C.css("height"),overflow:$C.css("overflow"),overflowX:$C.css("overflowX"),overflowY:$C.css("overflowY")});
var $H=$("html");$H.data("layoutCSS",{height:"auto",overflow:$H.css("overflow"),overflowX:$H.css("overflowX"),overflowY:$H.css("overflowY")})
}else{CSS=getElemCSS($C,props+",top,bottom,left,right,width,height,overflow,overflowX,overflowY")}$C.data("layoutCSS",CSS)
}try{if(fullPage){$("html").css({height:"100%",overflow:"hidden",overflowX:"hidden",overflowY:"hidden"});$("body").css({position:"relative",height:"100%",overflow:"hidden",overflowX:"hidden",overflowY:"hidden",margin:0,padding:0,border:"none"})
}else{CSS={overflow:"hidden"};var p=$C.css("position"),h=$C.css("height");if(!$C.data("layoutRole")){if(!p||!p.match(/fixed|absolute|relative/)){CSS.position="relative"
}}$C.css(CSS);if($C.is(":visible")&&$C.innerHeight()<2){alert(lang.errContainerHeight.replace(/CONTAINER/,sC.ref))}}}catch(ex){}$.extend(state.container,getElemDims($C))
};var initHotkeys=function(){$.each(_c.borderPanes.split(","),function(i,pane){var o=options[pane];if(o.enableCursorHotkey||o.customHotkey){$(document).bind("keydown."+sID,keyDown);
return false}})};var initOptions=function(){opts=_transformData(opts);var newOpts={applyDefaultStyles:"applyDemoStyles"};
renameOpts(opts.defaults);$.each(_c.allPanes.split(","),function(i,pane){renameOpts(opts[pane])});if(opts.effects){$.extend(effects,opts.effects);
delete opts.effects}$.extend(options.cookie,opts.cookie);var globals="name,zIndex,scrollToBookmarkOnLoad,resizeWithWindow,resizeWithWindowDelay,resizeWithWindowMaxDelay,onresizeall,onresizeall_start,onresizeall_end,onload,onunload,autoBindCustomButtons,useStateCookie";
$.each(globals.split(","),function(i,key){if(opts[key]!==undefined){options[key]=opts[key]}else{if(opts.defaults[key]!==undefined){options[key]=opts.defaults[key];
delete opts.defaults[key]}}});$.each("paneSelector,resizerCursor,customHotkey".split(","),function(i,key){delete opts.defaults[key]
});$.extend(true,options.defaults,opts.defaults);_c.center=$.extend(true,{},_c.panes,_c.center);var z=options.zIndex;if(z===0||z>0){_c.zIndex.pane_normal=z;
_c.zIndex.resizer_normal=z+1;_c.zIndex.iframe_mask=z+1}$.extend(options.center,opts.center);var o_Center=$.extend(true,{},options.defaults,opts.defaults,options.center);
var optionsCenter=("paneClass,contentSelector,applyDemoStyles,triggerEventsOnLoad,showOverflowOnHover,onresize,onresize_start,onresize_end,resizeNestedLayout,resizeContentWhileDragging,onsizecontent,onsizecontent_start,onsizecontent_end").split(",");
$.each(optionsCenter,function(i,key){options.center[key]=o_Center[key]});var o,defs=options.defaults;$.each(_c.borderPanes.split(","),function(i,pane){_c[pane]=$.extend(true,{},_c.panes,_c[pane]);
o=options[pane]=$.extend(true,{},options.defaults,options[pane],opts.defaults,opts[pane]);if(!o.paneClass){o.paneClass="ui-layout-pane"
}if(!o.resizerClass){o.resizerClass="ui-layout-resizer"}if(!o.togglerClass){o.togglerClass="ui-layout-toggler"}$.each(["_open","_close",""],function(i,n){var sName="fxName"+n,sSpeed="fxSpeed"+n,sSettings="fxSettings"+n;
o[sName]=opts[pane][sName]||opts[pane].fxName||opts.defaults[sName]||opts.defaults.fxName||o[sName]||o.fxName||defs[sName]||defs.fxName||"none";
var fxName=o[sName];if(fxName=="none"||!$.effects||!$.effects[fxName]||(!effects[fxName]&&!o[sSettings]&&!o.fxSettings)){fxName=o[sName]="none"
}var fx=effects[fxName]||{},fx_all=fx.all||{},fx_pane=fx[pane]||{};o[sSettings]=$.extend({},fx_all,fx_pane,defs.fxSettings||{},defs[sSettings]||{},o.fxSettings,o[sSettings],opts.defaults.fxSettings,opts.defaults[sSettings]||{},opts[pane].fxSettings,opts[pane][sSettings]||{});
o[sSpeed]=opts[pane][sSpeed]||opts[pane].fxSpeed||opts.defaults[sSpeed]||opts.defaults.fxSpeed||o[sSpeed]||o[sSettings].duration||o.fxSpeed||o.fxSettings.duration||defs.fxSpeed||defs.fxSettings.duration||fx_pane.duration||fx_all.duration||"normal"
})});function renameOpts(O){for(var key in newOpts){if(O[key]!=undefined){O[newOpts[key]]=O[key];delete O[key]}}}};var getPane=function(pane){var sel=options[pane].paneSelector;
if(sel.substr(0,1)==="#"){return $Container.find(sel).eq(0)}else{var $P=$Container.children(sel).eq(0);return $P.length?$P:$Container.children("form:first").children(sel).eq(0)
}};var initPanes=function(){$.each(_c.allPanes.split(","),function(idx,pane){var o=options[pane],s=state[pane],c=_c[pane],fx=s.fx,dir=c.dir,spacing=o.spacing_open||0,isCenter=(pane=="center"),CSS={},$P,$C,size,minSize,maxSize;
$Cs[pane]=false;$P=$Ps[pane]=getPane(pane);if(!$P.length){$Ps[pane]=false;return true}if(!$P.data("layoutCSS")){var props="position,top,left,bottom,right,width,height,overflow,zIndex,display,backgroundColor,padding,margin,border";
$P.data("layoutCSS",getElemCSS($P,props))}$P.data("parentLayout",Instance).data("layoutRole","pane").data("layoutEdge",pane).css(c.cssReq).css("zIndex",_c.zIndex.pane_normal).css(o.applyDemoStyles?c.cssDemo:{}).addClass(o.paneClass+" "+o.paneClass+"-"+pane).bind("mouseenter."+sID,addHover).bind("mouseleave."+sID,removeHover);
initContent(pane,false);if(!isCenter){size=s.size=_parseSize(pane,o.size);minSize=_parseSize(pane,o.minSize)||1;maxSize=_parseSize(pane,o.maxSize)||100000;
if(size>0){size=max(min(size,maxSize),minSize)}s.isClosed=false;s.isSliding=false;s.isResizing=false;s.isHidden=false}s.tagName=$P.attr("tagName");
s.edge=pane;s.noRoom=false;s.isVisible=true;switch(pane){case"north":CSS.top=sC.insetTop;CSS.left=sC.insetLeft;CSS.right=sC.insetRight;
break;case"south":CSS.bottom=sC.insetBottom;CSS.left=sC.insetLeft;CSS.right=sC.insetRight;break;case"west":CSS.left=sC.insetLeft;
break;case"east":CSS.right=sC.insetRight;break;case"center":}if(dir=="horz"){CSS.height=max(1,cssH(pane,size))}else{if(dir=="vert"){CSS.width=max(1,cssW(pane,size))
}}$P.css(CSS);if(dir!="horz"){sizeMidPanes(pane,true)}$P.css({visibility:"visible",display:"block"});if(o.initClosed&&o.closable){close(pane,true,true)
}else{if(o.initHidden||o.initClosed){hide(pane)}}if(o.showOverflowOnHover){$P.hover(allowOverflow,resetOverflow)}});initHandles();
$.each(_c.borderPanes.split(","),function(i,pane){if($Ps[pane]&&state[pane].isVisible){setSizeLimits(pane);makePaneFit(pane)
}});sizeMidPanes("center");$.each(_c.allPanes.split(","),function(i,pane){var o=options[pane];if($Ps[pane]&&o.triggerEventsOnLoad&&state[pane].isVisible){_execCallback(pane,o.onresize_end||o.onresize)
}});if($Container.innerHeight()<2){alert(lang.errContainerHeight.replace(/CONTAINER/,sC.ref))}};var initHandles=function(panes){if(!panes||panes=="all"){panes=_c.borderPanes
}$.each(panes.split(","),function(i,pane){var $P=$Ps[pane];$Rs[pane]=false;$Ts[pane]=false;if(!$P){return}var o=options[pane],s=state[pane],c=_c[pane],rClass=o.resizerClass,tClass=o.togglerClass,side=c.side.toLowerCase(),spacing=(s.isVisible?o.spacing_open:o.spacing_closed),_pane="-"+pane,_state=(s.isVisible?"-open":"-closed"),$R=$Rs[pane]=$("<div></div>"),$T=(o.closable?$Ts[pane]=$("<div></div>"):false);
if(!s.isVisible&&o.slidable){$R.attr("title",o.sliderTip).css("cursor",o.sliderCursor)}$R.attr("id",(o.paneSelector.substr(0,1)=="#"?o.paneSelector.substr(1)+"-resizer":"")).data("parentLayout",Instance).data("layoutRole","resizer").data("layoutEdge",pane).css(_c.resizers.cssReq).css("zIndex",_c.zIndex.resizer_normal).css(o.applyDemoStyles?_c.resizers.cssDemo:{}).addClass(rClass+" "+rClass+_pane).appendTo($Container);
if($T){$T.attr("id",(o.paneSelector.substr(0,1)=="#"?o.paneSelector.substr(1)+"-toggler":"")).data("parentLayout",Instance).data("layoutRole","toggler").data("layoutEdge",pane).css(_c.togglers.cssReq).css(o.applyDemoStyles?_c.togglers.cssDemo:{}).addClass(tClass+" "+tClass+_pane).appendTo($R);
if(o.togglerContent_open){$("<span>"+o.togglerContent_open+"</span>").data("layoutRole","togglerContent").data("layoutEdge",pane).addClass("content content-open").css("display","none").appendTo($T).hover(addHover,removeHover)
}if(o.togglerContent_closed){$("<span>"+o.togglerContent_closed+"</span>").data("layoutRole","togglerContent").data("layoutEdge",pane).addClass("content content-closed").css("display","none").appendTo($T).hover(addHover,removeHover)
}enableClosable(pane)}initResizable(pane);if(s.isVisible){setAsOpen(pane)}else{setAsClosed(pane);bindStartSlidingEvent(pane,true)
}});sizeHandles("all")};var initContent=function(pane,resize){var o=options[pane],sel=o.contentSelector,$P=$Ps[pane],$C;if(sel){$C=$Cs[pane]=(o.findNestedContent)?$P.find(sel).eq(0):$P.children(sel).eq(0)
}if($C&&$C.length){$C.css(_c.content.cssReq);if(o.applyDemoStyles){$C.css(_c.content.cssDemo);$P.css(_c.content.cssDemoPane)
}state[pane].content={};if(resize!==false){sizeContent(pane)}}else{$Cs[pane]=false}};var initButtons=function(){var pre="ui-layout-button-",name;
$.each("toggle,open,close,pin,toggle-slide,open-slide".split(","),function(i,action){$.each(_c.borderPanes.split(","),function(ii,pane){$("."+pre+action+"-"+pane).each(function(){name=$(this).data("layoutName")||$(this).attr("layoutName");
if(name==undefined||name==options.name){bindButton(this,action,pane)}})})})};var initResizable=function(panes){var draggingAvailable=(typeof $.fn.draggable=="function"),$Frames,side;
if(!panes||panes=="all"){panes=_c.borderPanes}$.each(panes.split(","),function(idx,pane){var o=options[pane],s=state[pane],c=_c[pane],side=(c.dir=="horz"?"top":"left"),r,live;
if(!draggingAvailable||!$Ps[pane]||!o.resizable){o.resizable=false;return true}var $P=$Ps[pane],$R=$Rs[pane],base=o.resizerClass,resizerClass=base+"-drag",resizerPaneClass=base+"-"+pane+"-drag",helperClass=base+"-dragging",helperPaneClass=base+"-"+pane+"-dragging",helperLimitClass=base+"-dragging-limit",helperClassesSet=false;
if(!s.isClosed){$R.attr("title",o.resizerTip).css("cursor",o.resizerCursor)}$R.bind("mouseenter."+sID,onResizerEnter).bind("mouseleave."+sID,onResizerLeave);
$R.draggable({containment:$Container[0],axis:(c.dir=="horz"?"y":"x"),delay:0,distance:1,helper:"clone",opacity:o.resizerDragOpacity,addClasses:false,zIndex:_c.zIndex.resizer_drag,start:function(e,ui){o=options[pane];
s=state[pane];live=o.resizeWhileDragging;if(false===_execCallback(pane,o.ondrag_start)){return false}_c.isLayoutBusy=true;
s.isResizing=true;timer.clear(pane+"_closeSlider");setSizeLimits(pane);r=s.resizerPosition;$R.addClass(resizerClass+" "+resizerPaneClass);
helperClassesSet=false;$Frames=$(o.maskIframesOnResize===true?"iframe":o.maskIframesOnResize).filter(":visible");var id,i=0;
$Frames.each(function(){id="ui-layout-mask-"+(++i);$(this).data("layoutMaskID",id);$('<div id="'+id+'" class="ui-layout-mask ui-layout-mask-'+pane+'"/>').css({background:"#fff",opacity:"0.001",zIndex:_c.zIndex.iframe_mask,position:"absolute",width:this.offsetWidth+"px",height:this.offsetHeight+"px"}).css($(this).position()).appendTo(this.parentNode)
});$("body").disableSelection()},drag:function(e,ui){if(!helperClassesSet){ui.helper.addClass(helperClass+" "+helperPaneClass).children().css("visibility","hidden");
helperClassesSet=true;if(s.isSliding){$Ps[pane].css("zIndex",_c.zIndex.pane_sliding)}}var limit=0;if(ui.position[side]<r.min){ui.position[side]=r.min;
limit=-1}else{if(ui.position[side]>r.max){ui.position[side]=r.max;limit=1}}if(limit){ui.helper.addClass(helperLimitClass);
window.defaultStatus="Panel has reached its "+((limit>0&&pane.match(/north|west/))||(limit<0&&pane.match(/south|east/))?"maximum":"minimum")+" size"
}else{ui.helper.removeClass(helperLimitClass);window.defaultStatus=""}if(live){resizePanes(e,ui,pane)}},stop:function(e,ui){$("body").enableSelection();
window.defaultStatus="";$R.removeClass(resizerClass+" "+resizerPaneClass+" "+helperLimitClass);s.isResizing=false;_c.isLayoutBusy=false;
resizePanes(e,ui,pane,true)}});var resizePanes=function(evt,ui,pane,resizingDone){var dragPos=ui.position,c=_c[pane],resizerPos,newSize,i=0;
switch(pane){case"north":resizerPos=dragPos.top;break;case"west":resizerPos=dragPos.left;break;case"south":resizerPos=sC.offsetHeight-dragPos.top-o.spacing_open;
break;case"east":resizerPos=sC.offsetWidth-dragPos.left-o.spacing_open;break}if(resizingDone){$("div.ui-layout-mask").each(function(){this.parentNode.removeChild(this)
});if(false===_execCallback(pane,o.ondrag_end||o.ondrag)){return false}}else{$Frames.each(function(){$("#"+$(this).data("layoutMaskID")).css($(this).position()).css({width:this.offsetWidth+"px",height:this.offsetHeight+"px"})
})}newSize=resizerPos-sC["inset"+c.side];manualSizePane(pane,newSize)}})};var destroy=function(){$(window).unbind("."+sID);
$(document).unbind("."+sID);var fullPage=(sC.tagName=="BODY"),_open="-open",_sliding="-sliding",_closed="-closed",$P,root,pRoot,pClasses;
$.each(_c.allPanes.split(","),function(i,pane){$P=$Ps[pane];if(!$P){return true}if(pane!="center"){if($Ts[pane]){$Ts[pane].remove()
}$Rs[pane].remove()}root=options[pane].paneClass;pRoot=root+"-"+pane;pClasses=[root,root+_open,root+_closed,root+_sliding,pRoot,pRoot+_open,pRoot+_closed,pRoot+_sliding];
$.merge(pClasses,getHoverClasses($P,true));$P.removeClass(pClasses.join(" ")).removeData("layoutRole").removeData("layoutEdge").unbind("."+sID).unbind("mouseenter").unbind("mouseleave");
if(!$P.data("layoutContainer")){$P.css($P.data("layoutCSS"))}});$Container.removeData("layoutContainer");if(!$Container.data("layoutEdge")){$Container.css($Container.data("layoutCSS"))
}if(fullPage){$("html").css($("html").data("layoutCSS"))}unload()};var hide=function(pane,noAnimation){var o=options[pane],s=state[pane],$P=$Ps[pane],$R=$Rs[pane];
if(!$P||s.isHidden){return}if(state.initialized&&false===_execCallback(pane,o.onhide_start)){return}s.isSliding=false;if($R){$R.hide()
}if(!state.initialized||s.isClosed){s.isClosed=true;s.isHidden=true;s.isVisible=false;$P.hide();sizeMidPanes(_c[pane].dir=="horz"?"all":"center");
if(state.initialized||o.triggerEventsOnLoad){_execCallback(pane,o.onhide_end||o.onhide)}}else{s.isHiding=true;close(pane,false,noAnimation)
}};var show=function(pane,openPane,noAnimation,noAlert){var o=options[pane],s=state[pane],$P=$Ps[pane],$R=$Rs[pane];if(!$P||!s.isHidden){return
}if(false===_execCallback(pane,o.onshow_start)){return}s.isSliding=false;s.isShowing=true;if(openPane===false){close(pane,true)
}else{open(pane,false,noAnimation,noAlert)}};var toggle=function(pane,slide){if(!isStr(pane)){pane.stopImmediatePropagation();
pane=$(this).data("layoutEdge")}var s=state[str(pane)];if(s.isHidden){show(pane)}else{if(s.isClosed){open(pane,!!slide)}else{close(pane)
}}};var _closePane=function(pane,setHandles){var $P=$Ps[pane],s=state[pane];$P.hide();s.isClosed=true;s.isVisible=false};
var close=function(pane,force,noAnimation,skipCallback){if(!state.initialized){_closePane(pane);return}var $P=$Ps[pane],$R=$Rs[pane],$T=$Ts[pane],o=options[pane],s=state[pane],doFX=!noAnimation&&!s.isClosed&&(o.fxName_close!="none"),isShowing=s.isShowing,isHiding=s.isHiding,wasSliding=s.isSliding;
delete s.isShowing;delete s.isHiding;if(!$P||(!o.closable&&!isShowing&&!isHiding)){return}else{if(!force&&s.isClosed&&!isShowing){return
}}if(_c.isLayoutBusy){_queue("close",pane,force);return}if(!isShowing&&false===_execCallback(pane,o.onclose_start)){return
}_c[pane].isMoving=true;_c.isLayoutBusy=true;s.isClosed=true;s.isVisible=false;if(isHiding){s.isHidden=true}else{if(isShowing){s.isHidden=false
}}if(s.isSliding){bindStopSlidingEvents(pane,false)}else{sizeMidPanes(_c[pane].dir=="horz"?"all":"center",false)}setAsClosed(pane);
if(doFX){lockPaneForFX(pane,true);$P.hide(o.fxName_close,o.fxSettings_close,o.fxSpeed_close,function(){lockPaneForFX(pane,false);
close_2()})}else{$P.hide();close_2()}function close_2(){if(s.isClosed){bindStartSlidingEvent(pane,true);var altPane=_c.altSide[pane];
if(state[altPane].noRoom){setSizeLimits(altPane);makePaneFit(altPane)}if(!skipCallback&&(state.initialized||o.triggerEventsOnLoad)){if(!isShowing){_execCallback(pane,o.onclose_end||o.onclose)
}if(isShowing){_execCallback(pane,o.onshow_end||o.onshow)}if(isHiding){_execCallback(pane,o.onhide_end||o.onhide)}}}_dequeue(pane)
}};var setAsClosed=function(pane){var $P=$Ps[pane],$R=$Rs[pane],$T=$Ts[pane],o=options[pane],s=state[pane],side=_c[pane].side.toLowerCase(),inset="inset"+_c[pane].side,rClass=o.resizerClass,tClass=o.togglerClass,_pane="-"+pane,_open="-open",_sliding="-sliding",_closed="-closed";
$R.css(side,sC[inset]).removeClass(rClass+_open+" "+rClass+_pane+_open).removeClass(rClass+_sliding+" "+rClass+_pane+_sliding).addClass(rClass+_closed+" "+rClass+_pane+_closed).unbind("dblclick."+sID);
if(o.resizable&&typeof $.fn.draggable=="function"){$R.draggable("disable").removeClass("ui-state-disabled").css("cursor","default").attr("title","")
}if($T){$T.removeClass(tClass+_open+" "+tClass+_pane+_open).addClass(tClass+_closed+" "+tClass+_pane+_closed).attr("title",o.togglerTip_closed);
$T.children(".content-open").hide();$T.children(".content-closed").css("display","block")}syncPinBtns(pane,false);if(state.initialized){sizeHandles("all")
}};var open=function(pane,slide,noAnimation,noAlert){var $P=$Ps[pane],$R=$Rs[pane],$T=$Ts[pane],o=options[pane],s=state[pane],doFX=!noAnimation&&s.isClosed&&(o.fxName_open!="none"),isShowing=s.isShowing;
delete s.isShowing;if(!$P||(!o.resizable&&!o.closable&&!isShowing)){return}else{if(s.isVisible&&!s.isSliding){return}}if(s.isHidden&&!isShowing){show(pane,true);
return}if(_c.isLayoutBusy){_queue("open",pane,slide);return}if(false===_execCallback(pane,o.onopen_start)){return}setSizeLimits(pane,slide);
if(s.minSize>s.maxSize){syncPinBtns(pane,false);if(!noAlert&&o.noRoomToOpenTip){alert(o.noRoomToOpenTip)}return}_c[pane].isMoving=true;
_c.isLayoutBusy=true;if(slide){bindStopSlidingEvents(pane,true)}else{if(s.isSliding){bindStopSlidingEvents(pane,false)}else{if(o.slidable){bindStartSlidingEvent(pane,false)
}}}s.noRoom=false;makePaneFit(pane);s.isVisible=true;s.isClosed=false;if(isShowing){s.isHidden=false}if(doFX){lockPaneForFX(pane,true);
$P.show(o.fxName_open,o.fxSettings_open,o.fxSpeed_open,function(){lockPaneForFX(pane,false);open_2()})}else{$P.show();open_2()
}function open_2(){if(s.isVisible){_fixIframe(pane);if(!s.isSliding){sizeMidPanes(_c[pane].dir=="vert"?"center":"all",false)
}setAsOpen(pane)}_dequeue(pane)}};var setAsOpen=function(pane,skipCallback){var $P=$Ps[pane],$R=$Rs[pane],$T=$Ts[pane],o=options[pane],s=state[pane],side=_c[pane].side.toLowerCase(),inset="inset"+_c[pane].side,rClass=o.resizerClass,tClass=o.togglerClass,_pane="-"+pane,_open="-open",_closed="-closed",_sliding="-sliding";
$R.css(side,sC[inset]+getPaneSize(pane)).removeClass(rClass+_closed+" "+rClass+_pane+_closed).addClass(rClass+_open+" "+rClass+_pane+_open);
if(s.isSliding){$R.addClass(rClass+_sliding+" "+rClass+_pane+_sliding)}else{$R.removeClass(rClass+_sliding+" "+rClass+_pane+_sliding)
}if(o.resizerDblClickToggle){$R.bind("dblclick",toggle)}removeHover(0,$R);if(o.resizable&&typeof $.fn.draggable=="function"){$R.draggable("enable").css("cursor",o.resizerCursor).attr("title",o.resizerTip)
}else{if(!s.isSliding){$R.css("cursor","default")}}if($T){$T.removeClass(tClass+_closed+" "+tClass+_pane+_closed).addClass(tClass+_open+" "+tClass+_pane+_open).attr("title",o.togglerTip_open);
removeHover(0,$T);$T.children(".content-closed").hide();$T.children(".content-open").css("display","block")}syncPinBtns(pane,!s.isSliding);
$.extend(s,getElemDims($P));if(state.initialized){sizeHandles("all");sizeContent(pane,true)}if(!skipCallback&&(state.initialized||o.triggerEventsOnLoad)&&$P.is(":visible")){_execCallback(pane,o.onopen_end||o.onopen);
if(s.isShowing){_execCallback(pane,o.onshow_end||o.onshow)}if(state.initialized){_execCallback(pane,o.onresize_end||o.onresize);
resizeNestedLayout(pane)}}};var slideOpen=function(evt_or_pane){var type=typeof evt_or_pane,pane=(type=="string"?evt_or_pane:$(this).data("layoutEdge"));
if(type=="object"){evt_or_pane.stopImmediatePropagation()}if(state[pane].isClosed){open(pane,true)}else{bindStopSlidingEvents(pane,true)
}};var slideClose=function(evt_or_pane){var evt=isStr(evt_or_pane)?null:evt_or_pane;$E=(evt?$(this):$Ps[evt_or_pane]),pane=$E.data("layoutEdge"),o=options[pane],s=state[pane],$P=$Ps[pane];
if(s.isClosed||s.isResizing){return}else{if(o.slideTrigger_close=="click"){close_NOW()}else{if(o.preventQuickSlideClose&&_c.isLayoutBusy){return
}else{if(o.preventPrematureSlideClose&&evt&&$.layout.isMouseOverElem(evt,$P)){return}else{if(evt){timer.set(pane+"_closeSlider",close_NOW,_c[pane].isMoving?1000:300)
}else{close_NOW()}}}}}function close_NOW(evt){if(s.isClosed){bindStopSlidingEvents(pane,false)}else{if(!_c[pane].isMoving){close(pane)
}}}};var slideToggle=function(pane){toggle(pane,true)};var lockPaneForFX=function(pane,doLock){var $P=$Ps[pane];if(doLock){$P.css({zIndex:_c.zIndex.pane_animate});
if(pane=="south"){$P.css({top:sC.insetTop+sC.innerHeight-$P.outerHeight()})}else{if(pane=="east"){$P.css({left:sC.insetLeft+sC.innerWidth-$P.outerWidth()})
}}}else{$P.css({zIndex:(state[pane].isSliding?_c.zIndex.pane_sliding:_c.zIndex.pane_normal)});if(pane=="south"){$P.css({top:"auto"})
}else{if(pane=="east"){$P.css({left:"auto"})}}var o=options[pane];if(state.browser.msie&&o.fxOpacityFix&&o.fxName_open!="slide"&&$P.css("filter")&&$P.css("opacity")==1){$P[0].style.removeAttribute("filter")
}}};var bindStartSlidingEvent=function(pane,enable){var o=options[pane],$P=$Ps[pane],$R=$Rs[pane],trigger=o.slideTrigger_open;
if(!$R||(enable&&!o.slidable)){return}if(trigger.match(/mouseover/)){trigger=o.slideTrigger_open="mouseenter"}else{if(!trigger.match(/click|dblclick|mouseenter/)){trigger=o.slideTrigger_open="click"
}}$R[enable?"bind":"unbind"](trigger+"."+sID,slideOpen).css("cursor",enable?o.sliderCursor:"default").attr("title",enable?o.sliderTip:"")
};var bindStopSlidingEvents=function(pane,enable){var o=options[pane],s=state[pane],z=_c.zIndex,trigger=o.slideTrigger_close,action=(enable?"bind":"unbind"),$P=$Ps[pane],$R=$Rs[pane];
s.isSliding=enable;timer.clear(pane+"_closeSlider");if(enable){bindStartSlidingEvent(pane,false)}$P.css("zIndex",enable?z.pane_sliding:z.pane_normal);
$R.css("zIndex",enable?z.pane_sliding:z.resizer_normal);if(!trigger.match(/click|mouseleave/)){trigger=o.slideTrigger_close="mouseleave"
}$R[action](trigger,slideClose);if(trigger=="mouseleave"){$P[action]("mouseleave."+sID,slideClose);$R[action]("mouseenter."+sID,cancelMouseOut);
$P[action]("mouseenter."+sID,cancelMouseOut)}if(!enable){timer.clear(pane+"_closeSlider")}else{if(trigger=="click"&&!o.resizable){$R.css("cursor",enable?o.sliderCursor:"default");
$R.attr("title",enable?o.togglerTip_open:"")}}function cancelMouseOut(evt){timer.clear(pane+"_closeSlider");evt.stopPropagation()
}};var makePaneFit=function(pane,isOpening,skipCallback,force){var o=options[pane],s=state[pane],c=_c[pane],$P=$Ps[pane],$R=$Rs[pane],isSidePane=c.dir=="vert",hasRoom=false;
if(pane=="center"||(isSidePane&&s.noVerticalRoom)){hasRoom=s.minHeight<=s.maxHeight&&(isSidePane||s.minWidth<=s.maxWidth);
if(hasRoom&&s.noRoom){$P.show();if($R){$R.show()}s.isVisible=true;s.noRoom=false;if(isSidePane){s.noVerticalRoom=false}_fixIframe(pane)
}else{if(!hasRoom&&!s.noRoom){$P.hide();if($R){$R.hide()}s.isVisible=false;s.noRoom=true}}}if(pane=="center"){}else{if(s.minSize<=s.maxSize){hasRoom=true;
if(s.size>s.maxSize){sizePane(pane,s.maxSize,skipCallback,force)}else{if(s.size<s.minSize){sizePane(pane,s.minSize,skipCallback,force)
}else{if($R&&$P.is(":visible")){var side=c.side.toLowerCase(),pos=s.size+sC["inset"+c.side];if(_cssNum($R,side)!=pos){$R.css(side,pos)
}}}}if(s.noRoom){if(s.wasOpen&&o.closable){if(o.autoReopen){open(pane,false,true,true)}else{s.noRoom=false}}else{show(pane,s.wasOpen,true,true)
}}}else{if(!s.noRoom){s.noRoom=true;s.wasOpen=!s.isClosed&&!s.isSliding;if(s.isClosed){}else{if(o.closable){close(pane,true,true)
}else{hide(pane,true)}}}}}};var manualSizePane=function(pane,size,skipCallback){var o=options[pane],forceResize=o.resizeWhileDragging&&!_c.isLayoutBusy;
o.autoResize=false;sizePane(pane,size,skipCallback,forceResize)};var sizePane=function(pane,size,skipCallback,force){var o=options[pane],s=state[pane],$P=$Ps[pane],$R=$Rs[pane],side=_c[pane].side.toLowerCase(),inset="inset"+_c[pane].side,skipResizeWhileDragging=_c.isLayoutBusy&&!o.triggerEventsWhileDragging,oldSize;
setSizeLimits(pane);oldSize=s.size;size=_parseSize(pane,size);size=max(size,_parseSize(pane,o.minSize));size=min(size,s.maxSize);
if(size<s.minSize){makePaneFit(pane,false,skipCallback);return}if(!force&&size==oldSize){return}if(!skipCallback&&state.initialized&&s.isVisible){_execCallback(pane,o.onresize_start)
}$P.css(_c[pane].sizeType.toLowerCase(),max(1,cssSize(pane,size)));s.size=size;$.extend(s,getElemDims($P));if($R&&$P.is(":visible")){$R.css(side,size+sC[inset])
}sizeContent(pane);if(!skipCallback&&!skipResizeWhileDragging&&state.initialized&&s.isVisible){_execCallback(pane,o.onresize_end||o.onresize);
resizeNestedLayout(pane)}if(!skipCallback){if(!s.isSliding){sizeMidPanes(_c[pane].dir=="horz"?"all":"center",skipResizeWhileDragging,force)
}sizeHandles("all")}var altPane=_c.altSide[pane];if(size<oldSize&&state[altPane].noRoom){setSizeLimits(altPane);makePaneFit(altPane,false,skipCallback)
}};var sizeMidPanes=function(panes,skipCallback,force){if(!panes||panes=="all"){panes="east,west,center"}$.each(panes.split(","),function(i,pane){if(!$Ps[pane]){return
}var o=options[pane],s=state[pane],$P=$Ps[pane],$R=$Rs[pane],isCenter=(pane=="center"),hasRoom=true,CSS={},d=calcNewCenterPaneDims();
$.extend(s,getElemDims($P));if(pane=="center"){if(!force&&s.isVisible&&d.width==s.outerWidth&&d.height==s.outerHeight){return true
}$.extend(s,cssMinDims(pane),{maxWidth:d.width,maxHeight:d.height});CSS=d;CSS.width=cssW(pane,d.width);CSS.height=cssH(pane,d.height);
hasRoom=CSS.width>0&&CSS.height>0;if(!hasRoom&&!state.initialized&&o.minWidth>0){var reqPx=o.minWidth-s.outerWidth,minE=options.east.minSize||0,minW=options.west.minSize||0,sizeE=state.east.size,sizeW=state.west.size,newE=sizeE,newW=sizeW;
if(reqPx>0&&state.east.isVisible&&sizeE>minE){newE=max(sizeE-minE,sizeE-reqPx);reqPx-=sizeE-newE}if(reqPx>0&&state.west.isVisible&&sizeW>minW){newW=max(sizeW-minW,sizeW-reqPx);
reqPx-=sizeW-newW}if(reqPx==0){if(sizeE!=minE){sizePane("east",newE,true)}if(sizeW!=minW){sizePane("west",newW,true)}sizeMidPanes("center",skipCallback,force);
return}}}else{if(s.isVisible&&!s.noVerticalRoom){$.extend(s,getElemDims($P),cssMinDims(pane))}if(!force&&!s.noVerticalRoom&&d.height==s.outerHeight){return true
}CSS.top=d.top;CSS.bottom=d.bottom;CSS.height=cssH(pane,d.height);s.maxHeight=max(0,CSS.height);hasRoom=(s.maxHeight>0);if(!hasRoom){s.noVerticalRoom=true
}}if(hasRoom){if(!skipCallback&&state.initialized){_execCallback(pane,o.onresize_start)}$P.css(CSS);if(s.isVisible){$.extend(s,getElemDims($P));
if(s.noRoom){makePaneFit(pane)}if(state.initialized){sizeContent(pane)}}}else{if(!s.noRoom&&s.isVisible){makePaneFit(pane)
}}if(pane=="center"){var b=state.browser;var fix=b.isIE6||(b.msie&&!b.boxModel);if($Ps.north&&(fix||state.north.tagName=="IFRAME")){$Ps.north.css("width",cssW($Ps.north,sC.innerWidth))
}if($Ps.south&&(fix||state.south.tagName=="IFRAME")){$Ps.south.css("width",cssW($Ps.south,sC.innerWidth))}}if(!skipCallback&&state.initialized&&s.isVisible){_execCallback(pane,o.onresize_end||o.onresize);
resizeNestedLayout(pane)}})};var resizeAll=function(){var oldW=sC.innerWidth,oldH=sC.innerHeight;$.extend(state.container,getElemDims($Container));
if(!sC.outerHeight){return}if(false===_execCallback(null,options.onresizeall_start)){return false}var shrunkH=(sC.innerHeight<oldH),shrunkW=(sC.innerWidth<oldW),$P,o,s,dir;
$.each(["south","north","east","west"],function(i,pane){if(!$Ps[pane]){return}s=state[pane];o=options[pane];dir=_c[pane].dir;
if(o.autoResize&&s.size!=o.size){sizePane(pane,o.size,true,true)}else{setSizeLimits(pane);makePaneFit(pane,false,true,true)
}});sizeMidPanes("all",true,true);sizeHandles("all");o=options;$.each(_c.allPanes.split(","),function(i,pane){$P=$Ps[pane];
if(!$P){return}if(state[pane].isVisible){_execCallback(pane,o[pane].onresize_end||o[pane].onresize)}resizeNestedLayout(pane)
});_execCallback(null,o.onresizeall_end||o.onresizeall)};var resizeNestedLayout=function(pane){var $P=$Ps[pane],$C=$Cs[pane],d="layoutContainer";
if(options[pane].resizeNestedLayout){if($P.data(d)){$P.layout().resizeAll()}else{if($C&&$C.data(d)){$C.layout().resizeAll()
}}}};var sizeContent=function(panes,remeasure){if(!panes||panes=="all"){panes=_c.allPanes}$.each(panes.split(","),function(idx,pane){var $P=$Ps[pane],$C=$Cs[pane],o=options[pane],s=state[pane],m=s.content;
if(!$P||!$C||!$P.is(":visible")){return true}if(false===_execCallback(null,o.onsizecontent_start)){return}if(!_c.isLayoutBusy||m.top==undefined||remeasure||o.resizeContentWhileDragging){_measure();
if(m.hiddenFooters>0&&$P.css("overflow")=="hidden"){$P.css("overflow","visible");_measure();$P.css("overflow","hidden")}}var newH=s.innerHeight-(m.spaceAbove-s.css.paddingTop)-(m.spaceBelow-s.css.paddingBottom);
if(!$C.is(":visible")||m.height!=newH){setOuterHeight($C,newH,true);m.height=newH}if(state.initialized){_execCallback(pane,o.onsizecontent_end||o.onsizecontent);
resizeNestedLayout(pane)}function _below($E){return max(s.css.paddingBottom,(parseInt($E.css("marginBottom"),10)||0))}function _measure(){var ignore=options[pane].contentIgnoreSelector,$Fs=$C.nextAll().not(ignore||":lt(0)"),$Fs_vis=$Fs.filter(":visible"),$F=$Fs_vis.filter(":last");
m={top:$C[0].offsetTop,height:$C.outerHeight(),numFooters:$Fs.length,hiddenFooters:$Fs.length-$Fs_vis.length,spaceBelow:0};
m.spaceAbove=m.top;m.bottom=m.top+m.height;if($F.length){m.spaceBelow=($F[0].offsetTop+$F.outerHeight())-m.bottom+_below($F)
}else{m.spaceBelow=_below($C)}}})};var sizeHandles=function(panes){if(!panes||panes=="all"){panes=_c.borderPanes}$.each(panes.split(","),function(i,pane){var o=options[pane],s=state[pane],$P=$Ps[pane],$R=$Rs[pane],$T=$Ts[pane],$TC;
if(!$P||!$R){return}var dir=_c[pane].dir,_state=(s.isClosed?"_closed":"_open"),spacing=o["spacing"+_state],togAlign=o["togglerAlign"+_state],togLen=o["togglerLength"+_state],paneLen,offset,CSS={};
if(spacing==0){$R.hide();return}else{if(!s.noRoom&&!s.isHidden){$R.show()}}if(dir=="horz"){paneLen=$P.outerWidth();s.resizerLength=paneLen;
$R.css({width:max(1,cssW($R,paneLen)),height:max(0,cssH($R,spacing)),left:_cssNum($P,"left")})}else{paneLen=$P.outerHeight();
s.resizerLength=paneLen;$R.css({height:max(1,cssH($R,paneLen)),width:max(0,cssW($R,spacing)),top:sC.insetTop+getPaneSize("north",true)})
}removeHover(o,$R);if($T){if(togLen==0||(s.isSliding&&o.hideTogglerOnSlide)){$T.hide();return}else{$T.show()}if(!(togLen>0)||togLen=="100%"||togLen>paneLen){togLen=paneLen;
offset=0}else{if(isStr(togAlign)){switch(togAlign){case"top":case"left":offset=0;break;case"bottom":case"right":offset=paneLen-togLen;
break;case"middle":case"center":default:offset=Math.floor((paneLen-togLen)/2)}}else{var x=parseInt(togAlign,10);if(togAlign>=0){offset=x
}else{offset=paneLen-togLen+x}}}if(dir=="horz"){var width=cssW($T,togLen);$T.css({width:max(0,width),height:max(1,cssH($T,spacing)),left:offset,top:0});
$T.children(".content").each(function(){$TC=$(this);$TC.css("marginLeft",Math.floor((width-$TC.outerWidth())/2))})}else{var height=cssH($T,togLen);
$T.css({height:max(0,height),width:max(1,cssW($T,spacing)),top:offset,left:0});$T.children(".content").each(function(){$TC=$(this);
$TC.css("marginTop",Math.floor((height-$TC.outerHeight())/2))})}removeHover(0,$T)}if(!state.initialized&&o.initHidden){$R.hide();
if($T){$T.hide()}}})};var enableClosable=function(pane){var $T=$Ts[pane],o=options[pane];if(!$T){return}o.closable=true;$T.bind("click."+sID,function(evt){toggle(pane);
evt.stopPropagation()}).bind("mouseenter."+sID,addHover).bind("mouseleave."+sID,removeHover).css("visibility","visible").css("cursor","pointer").attr("title",state[pane].isClosed?o.togglerTip_closed:o.togglerTip_open).show()
};var disableClosable=function(pane,hide){var $T=$Ts[pane];if(!$T){return}options[pane].closable=false;if(state[pane].isClosed){open(pane,false,true)
}$T.unbind("."+sID).css("visibility",hide?"hidden":"visible").css("cursor","default").attr("title","")};var enableSlidable=function(pane){var $R=$Rs[pane],o=options[pane];
if(!$R||!$R.data("draggable")){return}options[pane].slidable=true;if(s.isClosed){bindStartSlidingEvent(pane,true)}};var disableSlidable=function(pane){var $R=$Rs[pane];
if(!$R){return}options[pane].slidable=false;if(state[pane].isSliding){close(pane,false,true)}else{bindStartSlidingEvent(pane,false);
$R.css("cursor","default").attr("title","");removeHover(null,$R[0])}};var enableResizable=function(pane){var $R=$Rs[pane],o=options[pane];
if(!$R||!$R.data("draggable")){return}o.resizable=true;$R.draggable("enable").bind("mouseenter."+sID,onResizerEnter).bind("mouseleave."+sID,onResizerLeave);
if(!state[pane].isClosed){$R.css("cursor",o.resizerCursor).attr("title",o.resizerTip)}};var disableResizable=function(pane){var $R=$Rs[pane];
if(!$R||!$R.data("draggable")){return}options[pane].resizable=false;$R.draggable("disable").unbind("."+sID).css("cursor","default").attr("title","");
removeHover(null,$R[0])};var swapPanes=function(pane1,pane2){state[pane1].edge=pane2;state[pane2].edge=pane1;var cancelled=false;
if(false===_execCallback(pane1,options[pane1].onswap_start)){cancelled=true}if(!cancelled&&false===_execCallback(pane2,options[pane2].onswap_start)){cancelled=true
}if(cancelled){state[pane1].edge=pane1;state[pane2].edge=pane2;return}var oPane1=copy(pane1),oPane2=copy(pane2),sizes={};
sizes[pane1]=oPane1?oPane1.state.size:0;sizes[pane2]=oPane2?oPane2.state.size:0;$Ps[pane1]=false;$Ps[pane2]=false;state[pane1]={};
state[pane2]={};if($Ts[pane1]){$Ts[pane1].remove()}if($Ts[pane2]){$Ts[pane2].remove()}if($Rs[pane1]){$Rs[pane1].remove()}if($Rs[pane2]){$Rs[pane2].remove()
}$Rs[pane1]=$Rs[pane2]=$Ts[pane1]=$Ts[pane2]=false;move(oPane1,pane2);move(oPane2,pane1);oPane1=oPane2=sizes=null;if($Ps[pane1]){$Ps[pane1].css(_c.visible)
}if($Ps[pane2]){$Ps[pane2].css(_c.visible)}resizeAll();_execCallback(pane1,options[pane1].onswap_end||options[pane1].onswap);
_execCallback(pane2,options[pane2].onswap_end||options[pane2].onswap);return;function copy(n){var $P=$Ps[n],$C=$Cs[n];return !$P?false:{pane:n,P:$P?$P[0]:false,C:$C?$C[0]:false,state:$.extend({},state[n]),options:$.extend({},options[n])}
}function move(oPane,pane){if(!oPane){return}var P=oPane.P,C=oPane.C,oldPane=oPane.pane,c=_c[pane],side=c.side.toLowerCase(),inset="inset"+c.side,s=$.extend({},state[pane]),o=options[pane],fx={resizerCursor:o.resizerCursor},re,size,pos;
$.each("fxName,fxSpeed,fxSettings".split(","),function(i,k){fx[k]=o[k];fx[k+"_open"]=o[k+"_open"];fx[k+"_close"]=o[k+"_close"]
});$Ps[pane]=$(P).data("layoutEdge",pane).css(_c.hidden).css(c.cssReq);$Cs[pane]=C?$(C):false;options[pane]=$.extend({},oPane.options,fx);
state[pane]=$.extend({},oPane.state);re=new RegExp(o.paneClass+"-"+oldPane,"g");P.className=P.className.replace(re,o.paneClass+"-"+pane);
initHandles(pane);if(c.dir!=_c[oldPane].dir){size=sizes[pane]||0;setSizeLimits(pane);size=max(size,state[pane].minSize);manualSizePane(pane,size,true)
}else{$Rs[pane].css(side,sC[inset]+(state[pane].isVisible?getPaneSize(pane):0))}if(oPane.state.isVisible&&!s.isVisible){setAsOpen(pane,true)
}else{setAsClosed(pane);bindStartSlidingEvent(pane,true)}oPane=null}};function keyDown(evt){if(!evt){return true}var code=evt.keyCode;
if(code<33){return true}var PANE={38:"north",40:"south",37:"west",39:"east"},ALT=evt.altKey,SHIFT=evt.shiftKey,CTRL=evt.ctrlKey,CURSOR=(CTRL&&code>=37&&code<=40),o,k,m,pane;
if(CURSOR&&options[PANE[code]].enableCursorHotkey){pane=PANE[code]}else{if(CTRL||SHIFT){$.each(_c.borderPanes.split(","),function(i,p){o=options[p];
k=o.customHotkey;m=o.customHotkeyModifier;if((SHIFT&&m=="SHIFT")||(CTRL&&m=="CTRL")||(CTRL&&SHIFT)){if(k&&code==(isNaN(k)||k<=9?k.toUpperCase().charCodeAt(0):k)){pane=p;
return false}}})}}if(!pane||!$Ps[pane]||!options[pane].closable||state[pane].isHidden){return true}toggle(pane);evt.stopPropagation();
evt.returnValue=false;return false}function allowOverflow(el){if(this&&this.tagName){el=this}var $P;if(isStr(el)){$P=$Ps[el]
}else{if($(el).data("layoutRole")){$P=$(el)}else{$(el).parents().each(function(){if($(this).data("layoutRole")){$P=$(this);
return false}})}}if(!$P||!$P.length){return}var pane=$P.data("layoutEdge"),s=state[pane];if(s.cssSaved){resetOverflow(pane)
}if(s.isSliding||s.isResizing||s.isClosed){s.cssSaved=false;return}var newCSS={zIndex:(_c.zIndex.pane_normal+2)},curCSS={},of=$P.css("overflow"),ofX=$P.css("overflowX"),ofY=$P.css("overflowY");
if(of!="visible"){curCSS.overflow=of;newCSS.overflow="visible"}if(ofX&&!ofX.match(/visible|auto/)){curCSS.overflowX=ofX;newCSS.overflowX="visible"
}if(ofY&&!ofY.match(/visible|auto/)){curCSS.overflowY=ofX;newCSS.overflowY="visible"}s.cssSaved=curCSS;$P.css(newCSS);$.each(_c.allPanes.split(","),function(i,p){if(p!=pane){resetOverflow(p)
}})}function resetOverflow(el){if(this&&this.tagName){el=this}var $P;if(isStr(el)){$P=$Ps[el]}else{if($(el).data("layoutRole")){$P=$(el)
}else{$(el).parents().each(function(){if($(this).data("layoutRole")){$P=$(this);return false}})}}if(!$P||!$P.length){return
}var pane=$P.data("layoutEdge"),s=state[pane],CSS=s.cssSaved||{};if(!s.isSliding&&!s.isResizing){$P.css("zIndex",_c.zIndex.pane_normal)
}$P.css(CSS);s.cssSaved=false}function getBtn(selector,pane,action){var $E=$(selector);if(!$E.length){alert(lang.errButton+lang.selector+": "+selector)
}else{if(_c.borderPanes.indexOf(pane)==-1){alert(lang.errButton+lang.Pane.toLowerCase()+": "+pane)}else{var btn=options[pane].buttonClass+"-"+action;
$E.addClass(btn+" "+btn+"-"+pane).data("layoutName",options.name);return $E}}return null}function bindButton(selector,action,pane){switch(action.toLowerCase()){case"toggle":addToggleBtn(selector,pane);
break;case"open":addOpenBtn(selector,pane);break;case"close":addCloseBtn(selector,pane);break;case"pin":addPinBtn(selector,pane);
break;case"toggle-slide":addToggleBtn(selector,pane,true);break;case"open-slide":addOpenBtn(selector,pane,true);break}}function addToggleBtn(selector,pane,slide){var $E=getBtn(selector,pane,"toggle");
if($E){$E.click(function(evt){toggle(pane,!!slide);evt.stopPropagation()})}}function addOpenBtn(selector,pane,slide){var $E=getBtn(selector,pane,"open");
if($E){$E.attr("title",lang.Open).click(function(evt){open(pane,!!slide);evt.stopPropagation()})}}function addCloseBtn(selector,pane){var $E=getBtn(selector,pane,"close");
if($E){$E.attr("title",lang.Close).click(function(evt){close(pane);evt.stopPropagation()})}}function addPinBtn(selector,pane){var $E=getBtn(selector,pane,"pin");
if($E){var s=state[pane];$E.click(function(evt){setPinState($(this),pane,(s.isSliding||s.isClosed));if(s.isSliding||s.isClosed){open(pane)
}else{close(pane)}evt.stopPropagation()});setPinState($E,pane,(!s.isClosed&&!s.isSliding));_c[pane].pins.push(selector)}}function syncPinBtns(pane,doPin){$.each(_c[pane].pins,function(i,selector){setPinState($(selector),pane,doPin)
})}function setPinState($Pin,pane,doPin){var updown=$Pin.attr("pin");if(updown&&doPin==(updown=="down")){return}var pin=options[pane].buttonClass+"-pin",side=pin+"-"+pane,UP=pin+"-up "+side+"-up",DN=pin+"-down "+side+"-down";
$Pin.attr("pin",doPin?"down":"up").attr("title",doPin?lang.Unpin:lang.Pin).removeClass(doPin?UP:DN).addClass(doPin?DN:UP)
}function isCookiesEnabled(){return(navigator.cookieEnabled!=0)}function getCookie(opts){var o=$.extend({},options.cookie,opts||{}),name=o.name||options.name||"Layout",c=document.cookie,cs=c?c.split(";"):[],pair;
for(var i=0,n=cs.length;i<n;i++){pair=$.trim(cs[i]).split("=");if(pair[0]==name){return decodeJSON(decodeURIComponent(pair[1]))
}}return""}function saveCookie(keys,opts){var o=$.extend({},options.cookie,opts||{}),name=o.name||options.name||"Layout",params="",date="",clear=false;
if(o.expires.toUTCString){date=o.expires}else{if(typeof o.expires=="number"){date=new Date();if(o.expires>0){date.setDate(date.getDate()+o.expires)
}else{date.setYear(1970);clear=true}}}if(date){params+=";expires="+date.toUTCString()}if(o.path){params+=";path="+o.path}if(o.domain){params+=";domain="+o.domain
}if(o.secure){params+=";secure"}if(clear){state.cookie={};document.cookie=name+"="+params}else{state.cookie=getState(keys||o.keys);
document.cookie=name+"="+encodeURIComponent(encodeJSON(state.cookie))+params}return $.extend({},state.cookie)}function deleteCookie(){saveCookie("",{expires:-1})
}function loadCookie(opts){var o=getCookie(opts);if(o){state.cookie=$.extend({},o);loadState(o)}return o}function loadState(opts,animate){$.extend(true,options,opts);
if(state.initialized){var pane,o,v,a=!animate;$.each(_c.allPanes.split(","),function(idx,pane){o=opts[pane];if(typeof o!="object"){return
}v=o.initHidden;if(v===true){hide(pane,a)}if(v===false){show(pane,0,a)}v=o.size;if(v>0){sizePane(pane,v)}v=o.initClosed;if(v===true){close(pane,0,a)
}if(v===false){open(pane,0,a)}})}}function getState(keys){var data={},alt={isClosed:"initClosed",isHidden:"initHidden"},pair,pane,key,val;
if(!keys){keys=options.cookie.keys}if($.isArray(keys)){keys=keys.join(",")}keys=keys.replace(/__/g,".").split(",");for(var i=0,n=keys.length;
i<n;i++){pair=keys[i].split(".");pane=pair[0];key=pair[1];if(_c.allPanes.indexOf(pane)<0){continue}val=state[pane][key];if(val==undefined){continue
}if(key=="isClosed"&&state[pane]["isSliding"]){val=true}(data[pane]||(data[pane]={}))[alt[key]?alt[key]:key]=val}return data
}function encodeJSON(JSON){return parse(JSON);function parse(h){var D=[],i=0,k,v,t;for(k in h){v=h[k];t=typeof v;if(t=="string"){v='"'+v+'"'
}else{if(t=="object"){v=parse(v)}}D[i++]='"'+k+'":'+v}return"{"+D.join(",")+"}"}}function decodeJSON(str){try{return window["eval"]("("+str+")")||{}
}catch(e){return{}}}var $Container=$(this).eq(0);if(!$Container.length){return null}if($Container.data("layoutContainer")&&$Container.data("layout")){return $Container.data("layout")
}var $Ps={},$Cs={},$Rs={},$Ts={},sC=state.container,sID=state.id;var Instance={options:options,state:state,container:$Container,panes:$Ps,contents:$Cs,resizers:$Rs,togglers:$Ts,toggle:toggle,hide:hide,show:show,open:open,close:close,slideOpen:slideOpen,slideClose:slideClose,slideToggle:slideToggle,initContent:initContent,sizeContent:sizeContent,sizePane:manualSizePane,swapPanes:swapPanes,resizeAll:resizeAll,destroy:destroy,setSizeLimits:setSizeLimits,bindButton:bindButton,addToggleBtn:addToggleBtn,addOpenBtn:addOpenBtn,addCloseBtn:addCloseBtn,addPinBtn:addPinBtn,allowOverflow:allowOverflow,resetOverflow:resetOverflow,encodeJSON:encodeJSON,decodeJSON:decodeJSON,getState:getState,getCookie:getCookie,saveCookie:saveCookie,deleteCookie:deleteCookie,loadCookie:loadCookie,loadState:loadState,cssWidth:cssW,cssHeight:cssH,enableClosable:enableClosable,disableClosable:disableClosable,enableSlidable:enableSlidable,disableSlidable:disableSlidable,enableResizable:enableResizable,disableResizable:disableResizable};
_create();return Instance}})(jQuery);(function(d){var b=location.href.replace(/#.*/,"");var c=d.localScroll=function(e){d("body").localScroll(e)
};c.defaults={duration:1000,axis:"y",event:"click",stop:true};c.hash=function(e){e=d.extend({},c.defaults,e);e.hash=false;
if(location.hash){setTimeout(function(){a(0,location,e)},0)}};d.fn.localScroll=function(f){f=d.extend({},c.defaults,f);return(f.persistent||f.lazy)?this.bind(f.event,function(j){var g=d([j.target,j.target.parentNode]).filter(e)[0];
g&&a(j,g,f)}):this.find("a,area").filter(e).bind(f.event,function(g){a(g,this,f)}).end().end();function e(){return !!this.href&&!!this.hash&&this.href.replace(this.hash,"")==b&&(!f.filter||d(this).is(f.filter))
}};function a(l,k,g){var m=k.hash.slice(1),j=document.getElementById(m)||document.getElementsByName(m)[0];if(j){l&&l.preventDefault();
var f=d(g.target||d.scrollTo.window());if(g.lock&&f.is(":animated")||g.onBefore&&g.onBefore.call(k,l,j,f)===false){return
}if(g.stop){f.queue("fx",[]).stop()}f.scrollTo(j,g).trigger("notify.serialScroll",[j]);if(g.hash){f.queue(function(){location=k.hash;
d(this).dequeue()})}}}})(jQuery);(function(c){var a=["DOMMouseScroll","mousewheel"];c.event.special.mousewheel={setup:function(){if(this.addEventListener){for(var d=a.length;
d;){this.addEventListener(a[--d],b,false)}}else{this.onmousewheel=b}},teardown:function(){if(this.removeEventListener){for(var d=a.length;
d;){this.removeEventListener(a[--d],b,false)}}else{this.onmousewheel=null}}};c.fn.extend({mousewheel:function(d){return d?this.bind("mousewheel",d):this.trigger("mousewheel")
},unmousewheel:function(d){return this.unbind("mousewheel",d)}});function b(k){var g=k||window.event,f=[].slice.call(arguments,1),l=0,j=true,e=0,d=0;
k=c.event.fix(g);k.type="mousewheel";if(k.wheelDelta){l=k.wheelDelta/120}if(k.detail){l=-k.detail/3}d=l;if(g.axis!==undefined&&g.axis===g.HORIZONTAL_AXIS){d=0;
e=-1*l}if(g.wheelDeltaY!==undefined){d=g.wheelDeltaY/120}if(g.wheelDeltaX!==undefined){e=-1*g.wheelDeltaX/120}f.unshift(k,l,e,d);
return c.event.handle.apply(this,f)}})(jQuery);(function(c){var a=c.scrollTo=function(f,e,d){a.window().scrollTo(f,e,d)};
a.defaults={axis:"y",duration:1};a.window=function(){return c(c.browser.safari?"body":"html")};c.fn.scrollTo=function(f,e,d){if(typeof e=="object"){d=e;
e=0}d=c.extend({},a.defaults,d);e=e||d.speed||d.duration;d.queue=d.queue&&d.axis.length>1;if(d.queue){e/=2}d.offset=b(d.offset);
d.over=b(d.over);return this.each(function(){var q=this,m=c(q),p=f,l,j={},r=m.is("html,body");switch(typeof p){case"number":case"string":if(/^([+-]=)?\d+(px)?$/.test(p)){p=b(p);
break}p=c(p,this);case"object":if(p.is||p.style){l=(p=c(p)).offset()}}c.each(d.axis.split(""),function(y,z){var A=z=="x"?"Left":"Top",C=A.toLowerCase(),x="scroll"+A,u=q[x],v=z=="x"?"Width":"Height",B=v.toLowerCase();
if(l){j[x]=l[C]+(r?0:u-m.offset()[C]);if(d.margin){j[x]-=parseInt(p.css("margin"+A))||0;j[x]-=parseInt(p.css("border"+A+"Width"))||0
}j[x]+=d.offset[C]||0;if(d.over[C]){j[x]+=p[B]()*d.over[C]}}else{j[x]=p[C]}if(/^\d+$/.test(j[x])){j[x]=j[x]<=0?0:Math.min(j[x],g(v))
}if(!y&&d.queue){if(u!=j[x]){k(d.onAfterFirst)}delete j[x]}});k(d.onAfter);function k(u){m.animate(j,e,d.easing,u&&function(){u.call(this,f)
})}function g(u){var v=r?c.browser.opera?document.body:document.documentElement:q;return v["scroll"+u]-v["client"+u]}})};
function b(d){return typeof d=="object"?d:{top:d,left:d}}})(jQuery);(function(c){var d=function(a){return parseInt(a,10)||0
};c.each(["min","max"],function(a,b){c.fn[b+"Size"]=function(j){var k,l;if(j){if(j.width!==undefined){this.css(b+"-width",j.width)
}if(j.height!==undefined){this.css(b+"-height",j.height)}return this}else{k=this.css(b+"-width");l=this.css(b+"-height");
return{width:(b==="max"&&(k===undefined||k==="none"||d(k)===-1)&&Number.MAX_VALUE)||d(k),height:(b==="max"&&(l===undefined||l==="none"||d(l)===-1)&&Number.MAX_VALUE)||d(l)}
}}});c.fn.isVisible=function(){return this.is(":visible")};c.each(["border","margin","padding"],function(a,b){c.fn[b]=function(f){if(f){if(f.top!==undefined){this.css(b+"-top"+(b==="border"?"-width":""),f.top)
}if(f.bottom!==undefined){this.css(b+"-bottom"+(b==="border"?"-width":""),f.bottom)}if(f.left!==undefined){this.css(b+"-left"+(b==="border"?"-width":""),f.left)
}if(f.right!==undefined){this.css(b+"-right"+(b==="border"?"-width":""),f.right)}return this}else{return{top:d(this.css(b+"-top"+(b==="border"?"-width":""))),bottom:d(this.css(b+"-bottom"+(b==="border"?"-width":""))),left:d(this.css(b+"-left"+(b==="border"?"-width":""))),right:d(this.css(b+"-right"+(b==="border"?"-width":"")))}
}}})})(jQuery);(function(d){d.timeago=function(g){if(g instanceof Date){return a(g)}else{if(typeof g=="string"){return a(d.timeago.parse(g))
}else{return a(d.timeago.datetime(g))}}};var f=d.timeago;d.extend(d.timeago,{settings:{refreshMillis:60000,allowFuture:false,strings:{prefixAgo:null,prefixFromNow:null,suffixAgo:"ago",suffixFromNow:"from now",seconds:"less than a minute",minute:"about a minute",minutes:"%d minutes",hour:"about an hour",hours:"about %d hours",day:"a day",days:"%d days",month:"about a month",months:"%d months",year:"about a year",years:"%d years",numbers:[]}},inWords:function(p){var q=this.settings.strings;
var k=q.prefixAgo;var x=q.suffixAgo;if(this.settings.allowFuture){if(p<0){k=q.prefixFromNow;x=q.suffixFromNow}p=Math.abs(p)
}var u=p/1000;var g=u/60;var r=g/60;var v=r/24;var l=v/365;function j(y,A){var z=d.isFunction(y)?y(A,p):y;var B=(q.numbers&&q.numbers[A])||A;
return z.replace(/%d/i,B)}var m=u<45&&j(q.seconds,Math.round(u))||u<90&&j(q.minute,1)||g<45&&j(q.minutes,Math.round(g))||g<90&&j(q.hour,1)||r<24&&j(q.hours,Math.round(r))||r<48&&j(q.day,1)||v<30&&j(q.days,Math.floor(v))||v<60&&j(q.month,1)||v<365&&j(q.months,Math.floor(v/30))||l<2&&j(q.year,1)||j(q.years,Math.floor(l));
return d.trim([k,m,x].join(" "))},parse:function(j){var g=d.trim(j);g=g.replace(/\.\d\d\d+/,"");g=g.replace(/-/,"/").replace(/-/,"/");
g=g.replace(/T/," ").replace(/Z/," UTC");g=g.replace(/([\+-]\d\d)\:?(\d\d)/," $1$2");return new Date(g)},datetime:function(j){var k=d(j).get(0).tagName.toLowerCase()=="time";
var g=k?d(j).attr("datetime"):d(j).attr("title");return f.parse(g)}});d.fn.timeago=function(){var j=this;j.each(c);var g=f.settings;
if(g.refreshMillis>0){setInterval(function(){j.each(c)},g.refreshMillis)}return j};function c(){var g=b(this);if(!isNaN(g.datetime)){d(this).text(a(g.datetime))
}return this}function b(g){g=d(g);if(!g.data("timeago")){g.data("timeago",{datetime:f.datetime(g)});var j=d.trim(g.text());
if(j.length>0){g.attr("title",j)}}return g.data("timeago")}function a(g){return f.inWords(e(g))}function e(g){return(new Date().getTime()-g.getTime())
}document.createElement("abbr");document.createElement("time")})(jQuery);(function(a){a.fn.tinyscrollbar=function(e){var m={axis:"y",wheel:40,scroll:true,size:"auto",sizethumb:"auto"};
var e=a.extend(m,e);var y=a(this);if(y.is(".scroll-content")&&!y.parent().is(".scroll-viewport")){var l=y.parent();y.wrap('<div class="scroll-viewport" />');
a('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>').insertBefore(y);
l.addClass("with-scrollbar");return l.tinyscrollbar(e)}var j={obj:a(".scroll-viewport:first",this)};var f={obj:a(".scroll-content:first",this)};
var b={obj:a(".scrollbar:first",this)};var p={obj:a(".track:first",b.obj)};var u={obj:a(".thumb:first",b.obj)};var k=e.axis=="x",q=k?"left":"top",A=k?"Width":"Height";
var v,E={start:0,now:0},r={};var c=null;var D=null;if(this.length>1){this.each(function(){a(this).tinyscrollbar(e)});return this
}this.initialize=function(){this.tinyscrollbar_update();x()};this.tinyscrollbar_update=function(){v=0;j[e.axis]=j.obj[0]["offset"+A];
f[e.axis]=f.obj[0]["scroll"+A];f.ratio=j[e.axis]/f[e.axis];b.obj.toggleClass("disable",f.ratio>=1);p[e.axis]=e.size=="auto"?j[e.axis]:e.size;
u[e.axis]=Math.min(p[e.axis],Math.max(0,(e.sizethumb=="auto"?(p[e.axis]*f.ratio):e.sizethumb)));b.ratio=e.sizethumb=="auto"?(f[e.axis]/p[e.axis]):(f[e.axis]-j[e.axis])/(p[e.axis]-u[e.axis]);
B()};function B(){u.obj.removeAttr("style");r.start=u.obj.offset()[q];var F=A.toLowerCase();b.obj.css(F,p[e.axis]);p.obj.css(F,p[e.axis]);
u.obj.css(F,u[e.axis])}function x(){u.obj.bind("mousedown",g);p.obj.bind("mouseup",z);if(e.scroll&&this.addEventListener){y[0].addEventListener("DOMMouseScroll",C,false);
y[0].addEventListener("mousewheel",C,false)}else{if(e.scroll){y[0].onmousewheel=C}}j.obj.mouseover(function(F){b.obj.addClass("is-scrolling");
F.stopPropagation()});j.obj.mouseout(function(F){b.obj.removeClass("is-scrolling");F.stopPropagation()})}function g(G){r.start=k?G.pageX:G.pageY;
var F=parseInt(u.obj.css(q));E.start=F=="auto"?0:F;a(document).bind("mousemove",z);a(document).bind("mouseup",d);u.obj.bind("mouseup",d);
b.obj.addClass("is-scrolling");return false}function C(G){if(!(f.ratio>=1)){var H=parseInt(f.obj.css(q));G=a.event.fix(G||window.event);
var F=G.wheelDelta?G.wheelDelta/120:-G.detail/3;v-=F*e.wheel;v=Math.min((f[e.axis]-j[e.axis]),Math.max(0,v));u.obj.css(q,v/b.ratio);
f.obj.css(q,-v);G.preventDefault();if(!b.obj.is(".disable")){G.stopPropagation()}if(c){window.clearTimeout(c)}}}function d(F){a(document).unbind("mousemove",z);
a(document).unbind("mouseup",d);u.obj.unbind("mouseup",d);b.obj.removeClass("is-scrolling");return false}function z(F){if(!(f.ratio>=1)){E.now=Math.min((p[e.axis]-u[e.axis]),Math.max(0,(E.start+((k?F.pageX:F.pageY)-r.start))));
v=E.now*b.ratio;f.obj.css(q,-v);u.obj.css(q,E.now)}return false}return this.initialize()}})(jQuery);(function(c){var b={pos:[-260,-260]},d=3,j=document,g=j.documentElement,e=j.body,a,k;
function f(){if(this===b.elem){b.pos=[-260,-260];b.elem=false;d=3}}c.event.special.mwheelIntent={setup:function(){var l=c(this).bind("mousewheel",c.event.special.mwheelIntent.handler);
if(this!==j&&this!==g&&this!==e){l.bind("mouseleave",f)}l=null;return true},teardown:function(){c(this).unbind("mousewheel",c.event.special.mwheelIntent.handler).unbind("mouseleave",f);
return true},handler:function(l,m){var p=[l.clientX,l.clientY];if(this===b.elem||Math.abs(b.pos[0]-p[0])>d||Math.abs(b.pos[1]-p[1])>d){b.elem=this;
b.pos=p;d=250;clearTimeout(k);k=setTimeout(function(){d=10},200);clearTimeout(a);a=setTimeout(function(){d=3},1500);l=c.extend({},l,{type:"mwheelIntent"});
return c.event.handle.apply(this,arguments)}}};c.fn.extend({mwheelIntent:function(l){return l?this.bind("mwheelIntent",l):this.trigger("mwheelIntent")
},unmwheelIntent:function(l){return this.unbind("mwheelIntent",l)}});c(function(){e=j.body;c(j).bind("mwheelIntent.mwheelIntentDefault",c.noop)
})})(jQuery);(function(a9,aK){var aR="none",aq="LoadedContent",a8=false,aP="resize.",aW="y",aU="auto",a6=true,ar="nofollow",aY="x";
function a5(b,d){b=b?' id="'+a2+b+'"':"";d=d?' style="'+d+'"':"";return a9("<div"+b+d+"/>")}function aV(d,c){c=c===aY?aX.width():aX.height();
return typeof d==="string"?Math.round(/%/.test(d)?c/100*parseInt(d,10):parseInt(d,10)):d}function ai(a){return ba.photo||/\.(gif|png|jpg|jpeg|bmp)(?:\?([^#]*))?(?:#(\.*))?$/i.test(a)
}function aI(b){for(var d in b){if(a9.isFunction(b[d])&&d.substring(0,2)!=="on"){b[d]=b[d].call(aZ)}}b.rel=b.rel||aZ.rel||ar;
b.href=b.href||a9(aZ).attr("href");b.title=b.title||aZ.title;return b}function aO(d,b){b&&b.call(aZ);a9.event.trigger(d)}function aH(){var a,j=a2+"Slideshow_",l="click."+a2,g,d;
if(ba.slideshow&&a3[1]){g=function(){ay.text(ba.slideshowStop).unbind(l).bind(ah,function(){if(a4<a3.length-1||ba.loop){a=setTimeout(a7.next,ba.slideshowSpeed)
}}).bind(ag,function(){clearTimeout(a)}).one(l+" "+ap,d);a1.removeClass(j+"off").addClass(j+"on");a=setTimeout(a7.next,ba.slideshowSpeed)
};d=function(){clearTimeout(a);ay.text(ba.slideshowStart).unbind([ah,ag,ap,l].join(" ")).one(l,g);a1.removeClass(j+"on").addClass(j+"off")
};ba.slideshowAuto?g():d()}}function aF(b){if(!ao){aZ=b;ba=aI(a9.extend({},a9.data(aZ,aT)));a3=a9(aZ);a4=0;if(ba.rel!==ar){a3=a9("."+ax).filter(function(){return(a9.data(this,aT).rel||this.rel)===ba.rel
});a4=a3.index(aZ);if(a4===-1){a3=a3.add(aZ);a4=a3.length-1}}if(!aQ){aQ=aA=a6;a1.show();if(ba.returnFocus){try{aZ.blur();
a9(aZ).one(aa,function(){try{this.focus()}catch(c){}})}catch(a){}}aN.css({opacity:+ba.opacity,cursor:ba.overlayClose?"pointer":aU}).show();
ba.w=aV(ba.initialWidth,aY);ba.h=aV(ba.initialHeight,aW);a7.position(0);af&&aX.bind(aP+an+" scroll."+an,function(){aN.css({width:aX.width(),height:aX.height(),top:aX.scrollTop(),left:aX.scrollLeft()})
}).trigger("scroll."+an);aO(aJ,ba.onOpen);ae.add(aw).add(av).add(ay).add(ad).hide();aD.html(ba.close).show()}a7.load(a6)}}var aG={transition:"elastic",speed:300,width:a8,initialWidth:"600",innerWidth:a8,maxWidth:a8,height:a8,initialHeight:"450",innerHeight:a8,maxHeight:a8,scalePhotos:a6,scrolling:a6,inline:a8,html:a8,iframe:a8,photo:a8,href:a8,title:a8,rel:a8,opacity:0.9,preloading:a6,current:"image {current} of {total}",previous:"previous",next:"next",close:"close",open:a8,returnFocus:a6,loop:a6,slideshow:a8,slideshowAuto:a6,slideshowSpeed:2500,slideshowStart:"start slideshow",slideshowStop:"stop slideshow",onOpen:a8,onLoad:a8,onComplete:a8,onCleanup:a8,onClosed:a8,overlayClose:a6,escKey:a6,arrowKey:a6},aT="colorbox",a2="cbox",aJ=a2+"_open",ag=a2+"_load",ah=a2+"_complete",ap=a2+"_cleanup",aa=a2+"_closed",am=a2+"_purge",ac=a2+"_loaded",az=a9.browser.msie&&!a9.support.opacity,af=az&&a9.browser.version<7,an=a2+"_IE6",aN,a1,aE,aS,bc,aj,al,ak,a3,aX,a0,au,at,ad,ae,ay,av,aw,aD,aC,aB,aM,aL,aZ,a4,ba,aQ,aA,ao=a8,a7,ax=a2+"Element";
a7=a9.fn[aT]=a9[aT]=function(j,e){var b=this,g;if(!b[0]&&b.selector){return b}j=j||{};if(e){j.onComplete=e}if(!b[0]||b.selector===undefined){b=a9("<a/>");
j.open=a6}b.each(function(){a9.data(this,aT,a9.extend({},a9.data(this,aT)||aG,j));a9(this).addClass(ax)});g=j.open;if(a9.isFunction(g)){g=g.call(b)
}g&&aF(b[0]);return b};a7.init=function(){var b="hover",a="clear:left";aX=a9(aK);a1=a5().attr({id:aT,"class":az?a2+"IE":""});
aN=a5("Overlay",af?"position:absolute":"").hide();aE=a5("Wrapper");aS=a5("Content").append(a0=a5(aq,"width:0; height:0; overflow:hidden"),at=a5("LoadingOverlay").add(a5("LoadingGraphic")),ad=a5("Title"),ae=a5("Current"),av=a5("Next"),aw=a5("Previous"),ay=a5("Slideshow").bind(aJ,aH),aD=a5("Close"));
aE.append(a5().append(a5("TopLeft"),bc=a5("TopCenter"),a5("TopRight")),a5(a8,a).append(aj=a5("MiddleLeft"),aS,al=a5("MiddleRight")),a5(a8,a).append(a5("BottomLeft"),ak=a5("BottomCenter"),a5("BottomRight"))).children().children().css({"float":"left"});
au=a5(a8,"position:absolute; width:9999px; visibility:hidden; display:none");a9("body").prepend(aN,a1.append(aE,au));aS.children().hover(function(){a9(this).addClass(b)
},function(){a9(this).removeClass(b)}).addClass(b);aC=bc.height()+ak.height()+aS.outerHeight(a6)-aS.height();aB=aj.width()+al.width()+aS.outerWidth(a6)-aS.width();
aM=a0.outerHeight(a6);aL=a0.outerWidth(a6);a1.css({"padding-bottom":aC,"padding-right":aB}).hide();av.click(a7.next);aw.click(a7.prev);
aD.click(a7.close);aS.children().removeClass(b);a9("."+ax).live("click",function(c){if(!(c.button!==0&&typeof c.button!=="undefined"||c.ctrlKey||c.shiftKey||c.altKey)){c.preventDefault();
aF(this)}});aN.click(function(){ba.overlayClose&&a7.close()});a9(document).bind("keydown",function(c){if(aQ&&ba.escKey&&c.keyCode===27){c.preventDefault();
a7.close()}if(aQ&&ba.arrowKey&&!aA&&a3[1]){if(c.keyCode===37&&(a4||ba.loop)){c.preventDefault();aw.click()}else{if(c.keyCode===39&&(a4<a3.length-1||ba.loop)){c.preventDefault();
av.click()}}}})};a7.remove=function(){a1.add(aN).remove();a9("."+ax).die("click").removeData(aT).removeClass(ax)};a7.position=function(k,m){function a(b){bc[0].style.width=ak[0].style.width=aS[0].style.width=b.style.width;
at[0].style.height=at[1].style.height=aS[0].style.height=aj[0].style.height=al[0].style.height=b.style.height}var l,c=Math.max(document.documentElement.clientHeight-ba.h-aM-aC,0)/2+aX.scrollTop(),j=Math.max(aX.width()-ba.w-aL-aB,0)/2+aX.scrollLeft();
l=a1.width()===ba.w+aL&&a1.height()===ba.h+aM?0:k;aE[0].style.width=aE[0].style.height="9999px";a1.dequeue().animate({width:ba.w+aL,height:ba.h+aM,top:c,left:j},{duration:l,complete:function(){a(this);
aA=a8;aE[0].style.width=ba.w+aL+aB+"px";aE[0].style.height=ba.h+aM+aC+"px";m&&m()},step:function(){a(this)}})};a7.resize=function(a){if(aQ){a=a||{};
if(a.width){ba.w=aV(a.width,aY)-aL-aB}if(a.innerWidth){ba.w=aV(a.innerWidth,aY)}a0.css({width:ba.w});if(a.height){ba.h=aV(a.height,aW)-aM-aC
}if(a.innerHeight){ba.h=aV(a.innerHeight,aW)}if(!a.innerHeight&&!a.height){a=a0.wrapInner("<div style='overflow:auto'></div>").children();
ba.h=a.height();a.replaceWith(a.children())}a0.css({height:ba.h});a7.position(ba.transition===aR?0:ba.speed)}};a7.prep=function(a){var g="hidden";
function b(k){var v,u,e,x,j=a3.length,r=ba.loop;a7.position(k,function(){function c(){az&&a1[0].style.removeAttribute("filter")
}if(aQ){az&&f&&a0.fadeIn(100);a0.show();aO(ac);ad.show().html(ba.title);if(j>1){typeof ba.current==="string"&&ae.html(ba.current.replace(/\{current\}/,a4+1).replace(/\{total\}/,j)).show();
av[r||a4<j-1?"show":"hide"]().html(ba.next);aw[r||a4?"show":"hide"]().html(ba.previous);v=a4?a3[a4-1]:a3[j-1];e=a4<j-1?a3[a4+1]:a3[0];
ba.slideshow&&ay.show();if(ba.preloading){x=a9.data(e,aT).href||e.href;u=a9.data(v,aT).href||v.href;x=a9.isFunction(x)?x.call(e):x;
u=a9.isFunction(u)?u.call(v):u;if(ai(x)){a9("<img/>")[0].src=x}if(ai(u)){a9("<img/>")[0].src=u}}}at.hide();ba.transition==="fade"?a1.fadeTo(d,1,function(){c()
}):c();aX.bind(aP+a2,function(){a7.position(0)});aO(ah,ba.onComplete)}})}if(aQ){var f,d=ba.transition===aR?0:ba.speed;aX.unbind(aP+a2);
a0.remove();a0=a5(aq).html(a);a0.hide().appendTo(au.show()).css({width:function(){ba.w=ba.w||a0.width();ba.w=ba.mw&&ba.mw<ba.w?ba.mw:ba.w;
return ba.w}(),overflow:ba.scrolling?aU:g}).css({height:function(){ba.h=ba.h||a0.height();ba.h=ba.mh&&ba.mh<ba.h?ba.mh:ba.h;
return ba.h}()}).prependTo(aS);au.hide();a9("#"+a2+"Photo").css({cssFloat:aR,marginLeft:aU,marginRight:aU});af&&a9("select").not(a1.find("select")).filter(function(){return this.style.visibility!==g
}).css({visibility:g}).one(ap,function(){this.style.visibility="inherit"});ba.transition==="fade"?a1.fadeTo(d,0,function(){b(0)
}):b(d)}};a7.load=function(a){var f,e,b,d=a7.prep;aA=a6;aZ=a3[a4];a||(ba=aI(a9.extend({},a9.data(aZ,aT))));aO(am);aO(ag,ba.onLoad);
ba.h=ba.height?aV(ba.height,aW)-aM-aC:ba.innerHeight&&aV(ba.innerHeight,aW);ba.w=ba.width?aV(ba.width,aY)-aL-aB:ba.innerWidth&&aV(ba.innerWidth,aY);
ba.mw=ba.w;ba.mh=ba.h;if(ba.maxWidth){ba.mw=aV(ba.maxWidth,aY)-aL-aB;ba.mw=ba.w&&ba.w<ba.mw?ba.w:ba.mw}if(ba.maxHeight){ba.mh=aV(ba.maxHeight,aW)-aM-aC;
ba.mh=ba.h&&ba.h<ba.mh?ba.h:ba.mh}f=ba.href;at.show();if(ba.inline){a5().hide().insertBefore(a9(f)[0]).one(am,function(){a9(this).replaceWith(a0.children())
});d(a9(f))}else{if(ba.iframe){a1.one(ac,function(){var g=a9("<iframe frameborder='0' style='width:100%; height:100%; border:0; display:block'/>")[0];
g.name=a2+ +new Date;g.src=ba.href;if(!ba.scrolling){g.scrolling="no"}if(az){g.allowtransparency="true"}a9(g).appendTo(a0).one(am,function(){g.src="//about:blank"
})});d(" ")}else{if(ba.html){d(ba.html)}else{if(ai(f)){e=new Image;e.onload=function(){var c;e.onload=null;e.id=a2+"Photo";
a9(e).css({border:aR,display:"block",cssFloat:"left"});if(ba.scalePhotos){b=function(){e.height-=e.height*c;e.width-=e.width*c
};if(ba.mw&&e.width>ba.mw){c=(e.width-ba.mw)/e.width;b()}if(ba.mh&&e.height>ba.mh){c=(e.height-ba.mh)/e.height;b()}}if(ba.h){e.style.marginTop=Math.max(ba.h-e.height,0)/2+"px"
}a3[1]&&(a4<a3.length-1||ba.loop)&&a9(e).css({cursor:"pointer"}).click(a7.next);if(az){e.style.msInterpolationMode="bicubic"
}setTimeout(function(){d(e)},1)};setTimeout(function(){e.src=f},1)}else{f&&au.load(f,function(j,k,g){d(k==="error"?"Request unsuccessful: "+g.statusText:a9(this).children())
})}}}}};a7.next=function(){if(!aA){a4=a4<a3.length-1?a4+1:0;a7.load()}};a7.prev=function(){if(!aA){a4=a4?a4-1:a3.length-1;
a7.load()}};a7.close=function(){if(aQ&&!ao){ao=a6;aQ=a8;aO(ap,ba.onCleanup);aX.unbind("."+a2+" ."+an);aN.fadeTo("fast",0);
a1.stop().fadeTo("fast",0,function(){aO(am);a0.remove();a1.add(aN).css({opacity:1,cursor:aU}).hide();setTimeout(function(){ao=a8;
aO(aa,ba.onClosed)},1)})}};a7.element=function(){return a9(aZ)};a7.settings=aG;a9(a7.init)})(jQuery,this);(function(c){var a,b;
a=function(D){var A=this,v=(D.is("form")?D:D.find("form").first()),f=v.find("input:file").first(),K={namespace:"file_upload",cssClass:"file_upload",dragDropSupport:true,dropZone:D,url:v.attr("action"),method:v.attr("method"),fieldName:f.attr("name"),multipart:true,multiFileRequest:false,formData:function(){return v.serializeArray()
},withCredentials:false,forceIframeUpload:false},e={},N={},k={},g="undefined",m="function",u="number",x=/^http(s)?:\/\//,z=function(S,Q){var R=0;
this.complete=function(){R+=1;if(R===Q){S()}}},y=function(){return typeof XMLHttpRequest!==g&&typeof File!==g&&(!K.multipart||typeof FormData!==g||typeof FileReader!==g)
},I=function(){if(K.dragDropSupport){if(typeof K.onDocumentDragEnter===m){e["dragenter."+K.namespace]=K.onDocumentDragEnter
}if(typeof K.onDocumentDragLeave===m){e["dragleave."+K.namespace]=K.onDocumentDragLeave}e["dragover."+K.namespace]=A.onDocumentDragOver;
e["drop."+K.namespace]=A.onDocumentDrop;c(document).bind(e);if(typeof K.onDragEnter===m){N["dragenter."+K.namespace]=K.onDragEnter
}if(typeof K.onDragLeave===m){N["dragleave."+K.namespace]=K.onDragLeave}N["dragover."+K.namespace]=A.onDragOver;N["drop."+K.namespace]=A.onDrop;
K.dropZone.bind(N)}k["change."+K.namespace]=A.onChange;f.bind(k)},d=function(){c.each(e,function(Q,R){c(document).unbind(Q,R)
});c.each(N,function(Q,R){K.dropZone.unbind(Q,R)});c.each(k,function(Q,R){f.unbind(Q,R)})},l=function(S,Q,T,R){if(typeof R.onProgress===m){T.upload.onprogress=function(U){R.onProgress(U,S,Q,T,R)
}}if(typeof R.onLoad===m){T.onload=function(U){R.onLoad(U,S,Q,T,R)}}if(typeof R.onAbort===m){T.onabort=function(U){R.onAbort(U,S,Q,T,R)
}}if(typeof R.onError===m){T.onerror=function(U){R.onError(U,S,Q,T,R)}}},H=function(Q){if(typeof Q.formData===m){return Q.formData()
}else{if(c.isArray(Q.formData)){return Q.formData}else{if(Q.formData){var R=[];c.each(Q.formData,function(S,T){R.push({name:S,value:T})
});return R}}}return[]},P=function(T){if(x.test(T)){var U=location.host,S=location.protocol.length+2,R=T.indexOf(U,S),Q=R+U.length;
if((R===S||R===T.indexOf("@",S)+1)&&(T.length===Q||c.inArray(T.charAt(Q),["/","?","#"])!==-1)){return true}return false}return true
},G=function(R,S,Q){if(Q){S.setRequestHeader("X-File-Name",unescape(encodeURIComponent(R.name)))}S.setRequestHeader("Content-Type",R.type);
S.send(R)},q=function(S,U,R){var T=new FormData(),Q;c.each(H(R),function(V,W){T.append(W.name,W.value)});for(Q=0;Q<S.length;
Q+=1){T.append(R.fieldName,S[Q])}U.send(T)},r=function(R,S){var Q=new FileReader();Q.onload=function(T){R.content=T.target.result;
S()};Q.readAsBinaryString(R)},C=function(V,R,Q){var T="--",U="\r\n",S="";c.each(Q,function(W,X){S+=T+V+U+'Content-Disposition: form-data; name="'+unescape(encodeURIComponent(X.name))+'"'+U+U+unescape(encodeURIComponent(X.value))+U
});c.each(R,function(W,X){S+=T+V+U+'Content-Disposition: form-data; name="'+unescape(encodeURIComponent(K.fieldName))+'"; filename="'+unescape(encodeURIComponent(X.name))+'"'+U+"Content-Type: "+X.type+U+U+X.content+U
});S+=T+V+T+U;return S},p=function(T,U,S){var V="----MultiPartFormBoundary"+(new Date()).getTime(),Q,R;U.setRequestHeader("Content-Type","multipart/form-data; boundary="+V);
Q=new z(function(){U.sendAsBinary(C(V,T,H(S)))},T.length);for(R=0;R<T.length;R+=1){r(T[R],Q.complete)}},L=function(U,R,V,S){var Q=P(S.url),T;
l(U,R,V,S);V.open(S.method,S.url,true);if(Q){V.setRequestHeader("X-Requested-With","XMLHttpRequest")}else{if(S.withCredentials){V.withCredentials=true
}}if(!S.multipart){G(U[R],V,Q)}else{if(typeof R===u){T=[U[R]]}else{T=U}if(typeof FormData!==g){q(T,V,S)}else{if(typeof FileReader!==g){p(T,V,S)
}else{c.error("Browser does neither support FormData nor FileReader interface")}}}},M=function(T,S,R){var U=new XMLHttpRequest(),Q=c.extend({},K);
if(typeof K.initUpload===m){K.initUpload(T,S,R,U,Q,function(){L(S,R,U,Q)})}else{L(S,R,U,Q)}},O=function(S,R){var Q;if(K.multiFileRequest){M(S,R)
}else{for(Q=0;Q<R.length;Q+=1){M(S,R,Q)}}},j=function(Q,R){var S=H(R);v.find(":input").not(":disabled").attr("disabled",true).addClass(R.namespace+"_disabled");
c.each(S,function(T,U){v.append(c('<input type="hidden"/>').attr("name",U.name).val(U.value).addClass(R.namespace+"_form_data"))
});Q.insertAfter(f)},E=function(Q,R){Q.remove();v.find("."+R.namespace+"_disabled").removeAttr("disabled").removeClass(R.namespace+"_disabled");
v.find("."+R.namespace+"_form_data").remove()},J=function(Q,S,R){S.unbind("abort").bind("abort",function(T){S.readyState=0;
S.unbind("load").attr("src","javascript".concat(":false;"));if(typeof R.onAbort===m){R.onAbort(T,[{name:Q.val(),type:null,size:null}],0,S,R)
}}).unbind("load").bind("load",function(T){S.readyState=4;if(typeof R.onLoad===m){R.onLoad(T,[{name:Q.val(),type:null,size:null}],0,S,R)
}});v.attr("action",R.url).attr("target",S.attr("name"));j(Q,R);S.readyState=2;v.get(0).submit();E(Q,R)},F=function(T,R){var S=c('<iframe src="javascript:false;" style="display:none" name="iframe_'+K.namespace+"_"+(new Date()).getTime()+'"></iframe>'),Q=c.extend({},K);
S.readyState=0;S.abort=function(){S.trigger("abort")};S.bind("load",function(){S.unbind("load");if(typeof K.initUpload===m){K.initUpload(T,[{name:R.val(),type:null,size:null}],0,S,Q,function(){J(R,S,Q)
})}else{J(R,S,Q)}}).appendTo(v)},B=function(){var Q=f.clone(true);c("<form/>").append(Q).get(0).reset();f.replaceWith(Q);
f=Q};this.onDocumentDragOver=function(Q){if(typeof K.onDocumentDragOver===m&&K.onDocumentDragOver(Q)===false){return false
}Q.preventDefault()};this.onDocumentDrop=function(Q){if(typeof K.onDocumentDrop===m&&K.onDocumentDrop(Q)===false){return false
}Q.preventDefault()};this.onDragOver=function(R){if(typeof K.onDragOver===m&&K.onDragOver(R)===false){return false}var Q=R.originalEvent.dataTransfer;
if(Q){Q.dropEffect=Q.effectAllowed="copy"}R.preventDefault()};this.onDrop=function(R){if(typeof K.onDrop===m&&K.onDrop(R)===false){return false
}var Q=R.originalEvent.dataTransfer;if(Q&&Q.files&&y()){O(R,Q.files)}R.preventDefault()};this.onChange=function(Q){if(typeof K.onChange===m&&K.onChange(Q)===false){return false
}if(!K.forceIframeUpload&&Q.target.files&&y()){O(Q,Q.target.files)}else{F(Q,c(Q.target))}B()};this.init=function(Q){if(Q){c.extend(K,Q)
}if(D.data(K.namespace)){c.error('FileUpload with namespace "'+K.namespace+'" already assigned to this element');return}D.data(K.namespace,A).addClass(K.cssClass);
K.dropZone.addClass(K.cssClass);I()};this.destroy=function(){d();D.removeData(K.namespace).removeClass(K.cssClass);K.dropZone.removeClass(K.cssClass)
}};b={init:function(d){return this.each(function(){(new a(c(this))).init(d)})},destroy:function(d){return this.each(function(){d=d?d:"file_upload";
var e=c(this).data(d);if(e){e.destroy()}else{c.error('No FileUpload with namespace "'+d+'" assigned to this element')}})}};
c.fn.fileUpload=function(d){if(b[d]){return b[d].apply(this,Array.prototype.slice.call(arguments,1))}else{if(typeof d==="object"||!d){return b.init.apply(this,arguments)
}else{c.error("Method "+d+" does not exist on jQuery.fileUpload")}}}}(jQuery));(function(c){var b,a;b=function(e,g){var d=this,k="undefined",l="function",f,j;
this.dropZone=e;this.progressSelector=".file_upload_progress div";this.cancelSelector=".file_upload_cancel div";this.cssClassSmall="file_upload_small";
this.cssClassLarge="file_upload_large";this.cssClassHighlight="file_upload_highlight";this.dropEffect="highlight";this.uploadTable=this.downloadTable=c();
this.buildUploadRow=this.buildDownloadRow=function(){return null};this.addNode=function(m,p,q){if(p){p.hide().appendTo(m).fadeIn(function(){if(typeof q===l){try{q()
}catch(r){c(this).stop();throw r}}})}else{if(typeof q===l){q()}}};this.removeNode=function(m,p){if(m){m.fadeOut(function(){c(this).remove();
if(typeof p===l){try{p()}catch(q){c(this).stop();throw q}}})}else{if(typeof p===l){p()}}};this.onAbort=function(r,q,m,u,p){d.removeNode(p.uploadRow)
};this.cancelUpload=function(u,r,p,v,q){var m=v.readyState;v.abort();if(isNaN(m)||m<2){q.onAbort(u,r,p,v,q)}};this.initProgressBar=function(p,q){if(typeof p.progressbar===l){return p.progressbar({value:q})
}else{var m=c('<progress value="'+q+'" max="100"/>').appendTo(p);m.progressbar=function(r,u){m.attr("value",u)};return m}};
this.initUploadRow=function(r,q,m,x,p,u){var v=p.uploadRow=d.buildUploadRow(q,m);if(v){p.progressbar=d.initProgressBar(v.find(d.progressSelector),(x.upload?0:100));
v.find(d.cancelSelector).click(function(y){d.cancelUpload(y,q,m,x,p)})}d.addNode(d.uploadTable,v,u)};this.initUpload=function(r,q,m,v,p,u){d.initUploadRow(r,q,m,v,p,function(){if(typeof d.beforeSend===l){d.beforeSend(r,q,m,v,p,u)
}else{u()}})};this.onProgress=function(r,q,m,u,p){if(p.progressbar){p.progressbar.progressbar("value",parseInt(r.loaded/r.total*100,10))
}};this.parseResponse=function(m){if(typeof m.responseText!==k){return c.parseJSON(m.responseText)}else{return c.parseJSON(m.contents().text())
}};this.initDownloadRow=function(m,p,u,x,y,q){var z,r;try{z=y.response=d.parseResponse(x);r=y.downloadRow=d.buildDownloadRow(z);
d.addNode(d.downloadTable,r,q)}catch(v){if(typeof d.onError===l){y.originalEvent=m;d.onError(v,p,u,x,y)}else{throw v}}};this.onLoad=function(r,q,m,u,p){d.removeNode(p.uploadRow,function(){d.initDownloadRow(r,q,m,u,p,function(){if(typeof d.onComplete===l){d.onComplete(r,q,m,u,p)
}})})};this.dropZoneEnlarge=function(){if(!j){if(typeof d.dropZone.switchClass===l){d.dropZone.switchClass(d.cssClassSmall,d.cssClassLarge)
}else{d.dropZone.addClass(d.cssClassLarge);d.dropZone.removeClass(d.cssClassSmall)}j=true}};this.dropZoneReduce=function(){if(typeof d.dropZone.switchClass===l){d.dropZone.switchClass(d.cssClassLarge,d.cssClassSmall)
}else{d.dropZone.addClass(d.cssClassSmall);d.dropZone.removeClass(d.cssClassLarge)}j=false};this.onDocumentDragEnter=function(m){d.dropZoneEnlarge()
};this.onDocumentDragOver=function(m){if(f){clearTimeout(f)}f=setTimeout(function(){d.dropZoneReduce()},200)};this.onDragEnter=this.onDragLeave=function(m){d.dropZone.toggleClass(d.cssClassHighlight)
};this.onDrop=function(m){if(f){clearTimeout(f)}if(d.dropEffect&&typeof d.dropZone.effect===l){d.dropZone.effect(d.dropEffect,function(){d.dropZone.removeClass(d.cssClassHighlight);
d.dropZoneReduce()})}else{d.dropZone.removeClass(d.cssClassHighlight);d.dropZoneReduce()}};c.extend(this,g)};a={init:function(d){return this.each(function(){c(this).fileUpload(new b(c(this),d))
})},destroy:function(d){return this.each(function(){c(this).fileUpload("destroy",d)})}};c.fn.fileUploadUI=function(d){if(a[d]){return a[d].apply(this,Array.prototype.slice.call(arguments,1))
}else{if(typeof d==="object"||!d){return a.init.apply(this,arguments)}else{c.error("Method "+d+" does not exist on jQuery.fileUploadUI")
}}}}(jQuery));(function(a){a.Jcrop=function(d,E){var d=d,E=E;if(typeof(d)!=="object"){d=a(d)[0]}if(typeof(E)!=="object"){E={}
}if(!("trackDocument" in E)){E.trackDocument=a.browser.msie?false:true;if(a.browser.msie&&a.browser.version.split(".")[0]=="8"){E.trackDocument=true
}}if(!("keySupport" in E)){E.keySupport=a.browser.msie?false:true}var Y={trackDocument:false,baseClass:"jcrop",addClass:null,bgColor:"black",bgOpacity:0.6,borderOpacity:0.4,handleOpacity:0.5,handlePad:5,handleSize:9,handleOffset:5,edgeMargin:14,aspectRatio:0,keySupport:true,cornerHandles:true,sideHandles:true,drawBorders:true,dragEdges:true,boxWidth:0,boxHeight:0,boundary:8,animationDelay:20,swingSpeed:3,allowSelect:true,allowMove:true,allowResize:true,minSelect:[0,0],maxSize:[0,0],minSize:[0,0],onChange:function(){},onSelect:function(){}};
var L=Y;D(E);var aa=a(d);var ap=aa.clone().removeAttr("id").css({position:"absolute"});ap.width(aa.width());ap.height(aa.height());
aa.after(ap).hide();X(ap,L.boxWidth,L.boxHeight);var U=ap.width(),S=ap.height(),ad=a("<div />").width(U).height(S).addClass(G("holder")).css({position:"relative",backgroundColor:L.bgColor}).insertAfter(aa).append(ap);
if(L.addClass){ad.addClass(L.addClass)}var M=a("<img />").attr("src",ap.attr("src")).css("position","absolute").width(U).height(S);
var k=a("<div />").width(O(100)).height(O(100)).css({zIndex:310,position:"absolute",overflow:"hidden"}).append(M);var P=a("<div />").width(O(100)).height(O(100)).css("zIndex",320);
var C=a("<div />").css({position:"absolute",zIndex:300}).insertBefore(ap).append(k,P);var y=L.boundary;var b=ai().width(U+(y*2)).height(S+(y*2)).css({position:"absolute",top:l(-y),left:l(-y),zIndex:290}).mousedown(ag);
var B,al,r,W;var Q,e,p=true;var ah=H(ap),v,ar,aq,F,af;var ae=function(){var av=0,aG=0,au=0,aF=0,ay,aw;function aA(aJ){var aJ=ax(aJ);
au=av=aJ[0];aF=aG=aJ[1]}function az(aJ){var aJ=ax(aJ);ay=aJ[0]-au;aw=aJ[1]-aF;au=aJ[0];aF=aJ[1]}function aI(){return[ay,aw]
}function at(aL){var aK=aL[0],aJ=aL[1];if(0>av+aK){aK-=aK+av}if(0>aG+aJ){aJ-=aJ+aG}if(S<aF+aJ){aJ+=S-(aF+aJ)}if(U<au+aK){aK+=U-(au+aK)
}av+=aK;au+=aK;aG+=aJ;aF+=aJ}function aB(aJ){var aK=aH();switch(aJ){case"ne":return[aK.x2,aK.y];case"nw":return[aK.x,aK.y];
case"se":return[aK.x2,aK.y2];case"sw":return[aK.x,aK.y2]}}function aH(){if(!L.aspectRatio){return aE()}var aL=L.aspectRatio,aS=L.minSize[0]/Q,aR=L.minSize[1]/e,aK=L.maxSize[0]/Q,aU=L.maxSize[1]/e,aM=au-av,aT=aF-aG,aN=Math.abs(aM),aO=Math.abs(aT),aP=aN/aO,aJ,aQ;
if(aK==0){aK=U*10}if(aU==0){aU=S*10}if(aP<aL){aQ=aF;w=aO*aL;aJ=aM<0?av-w:w+av;if(aJ<0){aJ=0;h=Math.abs((aJ-av)/aL);aQ=aT<0?aG-h:h+aG
}else{if(aJ>U){aJ=U;h=Math.abs((aJ-av)/aL);aQ=aT<0?aG-h:h+aG}}}else{aJ=au;h=aN/aL;aQ=aT<0?aG-h:aG+h;if(aQ<0){aQ=0;w=Math.abs((aQ-aG)*aL);
aJ=aM<0?av-w:w+av}else{if(aQ>S){aQ=S;w=Math.abs(aQ-aG)*aL;aJ=aM<0?av-w:w+av}}}if(aJ>av){if(aJ-av<aS){aJ=av+aS}else{if(aJ-av>aK){aJ=av+aK
}}if(aQ>aG){aQ=aG+(aJ-av)/aL}else{aQ=aG-(aJ-av)/aL}}else{if(aJ<av){if(av-aJ<aS){aJ=av-aS}else{if(av-aJ>aK){aJ=av-aK}}if(aQ>aG){aQ=aG+(av-aJ)/aL
}else{aQ=aG-(av-aJ)/aL}}}if(aJ<0){av-=aJ;aJ=0}else{if(aJ>U){av-=aJ-U;aJ=U}}if(aQ<0){aG-=aQ;aQ=0}else{if(aQ>S){aG-=aQ-S;aQ=S
}}return last=aD(aC(av,aG,aJ,aQ))}function ax(aJ){if(aJ[0]<0){aJ[0]=0}if(aJ[1]<0){aJ[1]=0}if(aJ[0]>U){aJ[0]=U}if(aJ[1]>S){aJ[1]=S
}return[aJ[0],aJ[1]]}function aC(aM,aO,aL,aN){var aQ=aM,aP=aL,aK=aO,aJ=aN;if(aL<aM){aQ=aL;aP=aM}if(aN<aO){aK=aN;aJ=aO}return[Math.round(aQ),Math.round(aK),Math.round(aP),Math.round(aJ)]
}function aE(){var aK=au-av;var aJ=aF-aG;if(B&&(Math.abs(aK)>B)){au=(aK>0)?(av+B):(av-B)}if(al&&(Math.abs(aJ)>al)){aF=(aJ>0)?(aG+al):(aG-al)
}if(W&&(Math.abs(aJ)<W)){aF=(aJ>0)?(aG+W):(aG-W)}if(r&&(Math.abs(aK)<r)){au=(aK>0)?(av+r):(av-r)}if(av<0){au-=av;av-=av}if(aG<0){aF-=aG;
aG-=aG}if(au<0){av-=au;au-=au}if(aF<0){aG-=aF;aF-=aF}if(au>U){var aL=au-U;av-=aL;au-=aL}if(aF>S){var aL=aF-S;aG-=aL;aF-=aL
}if(av>U){var aL=av-S;aF-=aL;aG-=aL}if(aG>S){var aL=aG-S;aF-=aL;aG-=aL}return aD(aC(av,aG,au,aF))}function aD(aJ){return{x:aJ[0],y:aJ[1],x2:aJ[2],y2:aJ[3],w:aJ[2]-aJ[0],h:aJ[3]-aJ[1]}
}return{flipCoords:aC,setPressed:aA,setCurrent:az,getOffset:aI,moveOffset:at,getCorner:aB,getFixed:aH}}();var ab=function(){var aA,aw,aG,aF,aO=370;
var az={};var aS={};var av=false;var aE=L.handleOffset;if(L.drawBorders){az={top:aB("hline").css("top",a.browser.msie?l(-1):l(0)),bottom:aB("hline"),left:aB("vline"),right:aB("vline")}
}if(L.dragEdges){aS.t=aN("n");aS.b=aN("s");aS.r=aN("e");aS.l=aN("w")}L.sideHandles&&aJ(["n","s","e","w"]);L.cornerHandles&&aJ(["sw","nw","ne","se"]);
function aB(aV){var aW=a("<div />").css({position:"absolute",opacity:L.borderOpacity}).addClass(G(aV));k.append(aW);return aW
}function au(aV,aW){var aX=a("<div />").mousedown(c(aV)).css({cursor:aV+"-resize",position:"absolute",zIndex:aW});P.append(aX);
return aX}function aH(aV){return au(aV,aO++).css({top:l(-aE+1),left:l(-aE+1),opacity:L.handleOpacity}).addClass(G("handle"))
}function aN(aX){var a0=L.handleSize,a1=aE,aZ=a0,aW=a0,aY=a1,aV=a1;switch(aX){case"n":case"s":aW=O(100);break;case"e":case"w":aZ=O(100);
break}return au(aX,aO++).width(aW).height(aZ).css({top:l(-aY+1),left:l(-aV+1)})}function aJ(aV){for(i in aV){aS[aV[i]]=aH(aV[i])
}}function aL(a2){var aX=Math.round((a2.h/2)-aE),aW=Math.round((a2.w/2)-aE),a0=west=-aE+1,aZ=a2.w-aE,aY=a2.h-aE,aV,a1;"e" in aS&&aS.e.css({top:l(aX),left:l(aZ)})&&aS.w.css({top:l(aX)})&&aS.s.css({top:l(aY),left:l(aW)})&&aS.n.css({left:l(aW)});
"ne" in aS&&aS.ne.css({left:l(aZ)})&&aS.se.css({top:l(aY),left:l(aZ)})&&aS.sw.css({top:l(aY)});"b" in aS&&aS.b.css({top:l(aY)})&&aS.r.css({left:l(aZ)})
}function aD(aV,aW){M.css({top:l(-aW),left:l(-aV)});C.css({top:l(aW),left:l(aV)})}function aU(aV,aW){C.width(aV).height(aW)
}function ax(){var aV=ae.getFixed();ae.setPressed([aV.x,aV.y]);ae.setCurrent([aV.x2,aV.y2]);aR()}function aR(){if(aF){return aC()
}}function aC(){var aV=ae.getFixed();aU(aV.w,aV.h);aD(aV.x,aV.y);L.drawBorders&&az.right.css({left:l(aV.w-1)})&&az.bottom.css({top:l(aV.h-1)});
av&&aL(aV);aF||aT();L.onChange(ac(aV))}function aT(){C.show();ap.css("opacity",L.bgOpacity);aF=true}function aP(){aQ();C.hide();
ap.css("opacity",1);aF=false}function at(){if(av){aL(ae.getFixed());P.show()}}function aK(){av=true;if(L.allowResize){aL(ae.getFixed());
P.show();return true}}function aQ(){av=false;P.hide()}function aM(aV){(F=aV)?aQ():aK()}function aI(){aM(false);ax()}var ay=ai().mousedown(c("move")).css({cursor:"move",position:"absolute",zIndex:360});
k.append(ay);aQ();return{updateVisible:aR,update:aC,release:aP,refresh:ax,setCursor:function(aV){ay.css("cursor",aV)},enableHandles:aK,enableOnly:function(){av=true
},showHandles:at,disableHandles:aQ,animMode:aM,done:aI}}();var T=function(){var au=function(){},aw=function(){},av=L.trackDocument;
if(!av){b.mousemove(at).mouseup(ax).mouseout(ax)}function aB(){b.css({zIndex:450});if(av){a(document).mousemove(at).mouseup(ax)
}}function aA(){b.css({zIndex:290});if(av){a(document).unbind("mousemove",at).unbind("mouseup",ax)}}function at(aC){au(J(aC))
}function ax(aC){aC.preventDefault();aC.stopPropagation();if(v){v=false;aw(J(aC));L.onSelect(ac(ae.getFixed()));aA();au=function(){};
aw=function(){}}return false}function ay(aD,aC){v=true;au=aD;aw=aC;aB();return false}function az(aC){b.css("cursor",aC)}ap.before(b);
return{activateHandlers:ay,setCursor:az}}();var ao=function(){var aw=a('<input type="radio" />').css({position:"absolute",left:"-30px"}).keypress(at).blur(ax),ay=a("<div />").css({position:"absolute",overflow:"hidden"}).append(aw);
function au(){if(L.keySupport){aw.show();aw.focus()}}function ax(az){aw.hide()}function av(aA,az,aB){if(L.allowMove){ae.moveOffset([az,aB]);
ab.updateVisible()}aA.preventDefault();aA.stopPropagation()}function at(aA){if(aA.ctrlKey){return true}af=aA.shiftKey?true:false;
var az=af?10:1;switch(aA.keyCode){case 37:av(aA,-az,0);break;case 39:av(aA,az,0);break;case 38:av(aA,0,-az);break;case 40:av(aA,0,az);
break;case 27:ab.release();break;case 9:return true}return nothing(aA)}if(L.keySupport){ay.insertBefore(ap)}return{watchKeys:au}
}();function l(at){return""+parseInt(at)+"px"}function O(at){return""+parseInt(at)+"%"}function G(at){return L.baseClass+"-"+at
}function H(at){var au=a(at).offset();return[au.left,au.top]}function J(at){return[(at.pageX-ah[0]),(at.pageY-ah[1])]}function I(at){if(at!=ar){T.setCursor(at);
ar=at}}function f(av,ax){ah=H(ap);T.setCursor(av=="move"?av:av+"-resize");if(av=="move"){return T.activateHandlers(V(ax),q)
}var at=ae.getFixed();var au=u(av);var aw=ae.getCorner(u(au));ae.setPressed(ae.getCorner(au));ae.setCurrent(aw);T.activateHandlers(K(av,at),q)
}function K(au,at){return function(av){if(!L.aspectRatio){switch(au){case"e":av[1]=at.y2;break;case"w":av[1]=at.y2;break;
case"n":av[0]=at.x2;break;case"s":av[0]=at.x2;break}}else{switch(au){case"e":av[1]=at.y+1;break;case"w":av[1]=at.y+1;break;
case"n":av[0]=at.x+1;break;case"s":av[0]=at.x+1;break}}ae.setCurrent(av);ab.update()}}function V(au){var at=au;ao.watchKeys();
return function(av){ae.moveOffset([av[0]-at[0],av[1]-at[1]]);at=av;ab.update()}}function u(at){switch(at){case"n":return"sw";
case"s":return"nw";case"e":return"nw";case"w":return"ne";case"ne":return"sw";case"nw":return"se";case"se":return"nw";case"sw":return"ne"
}}function c(at){return function(au){if(L.disabled){return false}if((at=="move")&&!L.allowMove){return false}v=true;f(at,J(au));
au.stopPropagation();au.preventDefault();return false}}function X(ax,au,aw){var at=ax.width(),av=ax.height();if((at>au)&&au>0){at=au;
av=(au/ax.width())*ax.height()}if((av>aw)&&aw>0){av=aw;at=(aw/ax.height())*ax.width()}Q=ax.width()/at;e=ax.height()/av;ax.width(at).height(av)
}function ac(at){return{x:parseInt(at.x*Q),y:parseInt(at.y*e),x2:parseInt(at.x2*Q),y2:parseInt(at.y2*e),w:parseInt(at.w*Q),h:parseInt(at.h*e)}
}function q(au){var at=ae.getFixed();if(at.w>L.minSelect[0]&&at.h>L.minSelect[1]){ab.enableHandles();ab.done()}else{ab.release()
}T.setCursor(L.allowSelect?"crosshair":"default")}function ag(at){if(L.disabled){return false}if(!L.allowSelect){return false
}v=true;ah=H(ap);ab.disableHandles();I("crosshair");var au=J(at);ae.setPressed(au);T.activateHandlers(an,q);ao.watchKeys();
ab.update();at.stopPropagation();at.preventDefault();return false}function an(at){ae.setCurrent(at);ab.update()}function ai(){var at=a("<div></div>").addClass(G("tracker"));
a.browser.msie&&at.css({opacity:0,backgroundColor:"white"});return at}function x(aK){var aF=aK[0]/Q,au=aK[1]/e,aE=aK[2]/Q,at=aK[3]/e;
if(F){return}var aD=ae.flipCoords(aF,au,aE,at);var aI=ae.getFixed();var aw=initcr=[aI.x,aI.y,aI.x2,aI.y2];var av=L.animationDelay;
var aB=aw[0];var aA=aw[1];var aE=aw[2];var at=aw[3];var aH=aD[0]-initcr[0];var ay=aD[1]-initcr[1];var aG=aD[2]-initcr[2];
var ax=aD[3]-initcr[3];var aC=0;var az=L.swingSpeed;ab.animMode(true);var aJ=function(){return function(){aC+=(100-aC)/az;
aw[0]=aB+((aC/100)*aH);aw[1]=aA+((aC/100)*ay);aw[2]=aE+((aC/100)*aG);aw[3]=at+((aC/100)*ax);if(aC<100){aL()}else{ab.done()
}if(aC>=99.8){aC=100}am(aw)}}();function aL(){window.setTimeout(aJ,av)}aL()}function N(at){am([at[0]/Q,at[1]/e,at[2]/Q,at[3]/e])
}function am(at){ae.setPressed([at[0],at[1]]);ae.setCurrent([at[2],at[3]]);ab.update()}function D(at){if(typeof(at)!="object"){at={}
}L=a.extend(L,at);if(typeof(L.onChange)!=="function"){L.onChange=function(){}}if(typeof(L.onSelect)!=="function"){L.onSelect=function(){}
}}function j(){return ac(ae.getFixed())}function ak(){return ae.getFixed()}function z(at){D(at);R()}function A(){L.disabled=true;
ab.disableHandles();ab.setCursor("default");T.setCursor("default")}function Z(){L.disabled=false;R()}function m(){ab.done();
T.activateHandlers(null,null)}function aj(){ad.remove();aa.show()}function R(at){L.allowResize?at?ab.enableOnly():ab.enableHandles():ab.disableHandles();
T.setCursor(L.allowSelect?"crosshair":"default");ab.setCursor(L.allowMove?"move":"default");ad.css("backgroundColor",L.bgColor);
if("setSelect" in L){N(E.setSelect);ab.done();delete (L.setSelect)}if("trueSize" in L){Q=L.trueSize[0]/U;e=L.trueSize[1]/S
}B=L.maxSize[0]||0;al=L.maxSize[1]||0;r=L.minSize[0]||0;W=L.minSize[1]||0;if("outerImage" in L){ap.attr("src",L.outerImage);
delete (L.outerImage)}ab.refresh()}P.hide();R(true);var g={animateTo:x,setSelect:N,setOptions:z,tellSelect:j,tellScaled:ak,disable:A,enable:Z,cancel:m,focus:ao.watchKeys,getBounds:function(){return[U*Q,S*e]
},getWidgetSize:function(){return[U,S]},release:ab.release,destroy:aj};aa.data("Jcrop",g);return g};a.fn.Jcrop=function(c){function b(f){var e=c.useImg||f.src;
var d=new Image();d.onload=function(){a.Jcrop(f,c)};d.src=e}if(typeof(c)!=="object"){c={}}this.each(function(){if(a(this).data("Jcrop")){if(c=="api"){return a(this).data("Jcrop")
}else{a(this).data("Jcrop").setOptions(c)}}else{b(this)}});return this}})(jQuery);(function(a){a.fn.tagit=function(x){var c=this;
var r=8;var e=13;var m=32;var l=44;var q=9;if(!x.fieldName){x.fieldName="tags"}if(!x.inputFieldHtml){x.inputFieldHtml='<input class="tagit-input" type="text" data-placeholder="add..." value="add..." />'
}if(!x.inputFieldAppendTo){x.inputFieldAppendTo=c}if(x.enableBackspace===undefined){x.enableBackspace=true}if(!x.onchange){x.onchange=function(){}
}var d=true;var j=x.onchange;x.onchange=function(){if(d){return}return j()};c.addClass("tagit");if(x.inputFieldAppendTo==c){var g=a('<li class="tagit-new">'+x.inputFieldHtml+"</li>");
a("input",g).focus(function(){if(a(this).val()==a(this).data("placeholder")){a(this).val("")}a(this).addClass("editting")
}).blur(function(){if(!a(this).val().trim().length){a(this).val(a(this).data("placeholder"));a(this).removeClass("editting")
}});c.append(g)}else{var g=a(x.inputFieldHtml);x.inputFieldAppendTo.append(g)}var k=c;var f=a(".tagit-input",x.inputFieldAppendTo);
c.children("li:not(.tagit-new)").each(function(){if(!a(this).hasClass("tagit-new")){v(a("span",this).eq(0).html());a(this).remove()
}});a(this).click(function(y){if(y.target.tagName=="A"){a(y.target).parent().remove();x.onchange()}else{f.focus()}});f.keydown(function(y){var z=y.keyCode||y.which;
if(z==r&&x.enableBackspace){if(f.val()==""){a(c).children(".tagit-choice:last").remove()}}});f.keypress(function(y){var A=y.keyCode||y.which;
if(A==l||A==e||A==q){y.preventDefault();var z=f.val();z=z.replace(/,+$/,"").trim().replace(/^"/,"").replace(/"$/,"");z=z.trim();
if(z!=""){if(b(z)){v(z)}f.val(f.data("placeholder")).removeClass("editting")}}});f.autocomplete({source:x.availableTags,select:function(y,z){if(b(z.item.name)){v(z.item.name,z.item.value)
}f.val("");return false}});function p(){var y=[];f.parents("ul").children(".tagit-choice").each(function(){y.push(a(this).children("input").val())
});return y}function u(A,z){var y=new Array();for(var B=0;B<A.length;B++){if(z.indexOf(A[B])==-1){y.push(A[B])}}return y}function b(y){var z=true;
f.parents("ul").children(".tagit-choice").each(function(A){n=a(this).children("input").val();if(y==n){z=false}});return z
}function v(A,y){var z="";z='<li class="tagit-choice">\n<span>';z+=y||A+"</span>\n";z+='<a class="close">x</a>\n';z+='<input type="hidden" style="display:none;" value="'+A+'" name="'+x.fieldName+'[]">\n';
z+="</li>\n";if(x.inputFieldAppendTo==k){var B=f.parent();a(z).insertBefore(B)}else{a(z).appendTo(k)}f.val(f.data("placeholder"));
x.onchange()}this.add=function(z,y){v(z,y);return this};this.remove=function(y){f.parents("ul").children(".tagit-choice").each(function(z){n=a(this).children("input").val();
if(y==n){a(this).children("a").click()}});return this};d=false;return this};String.prototype.trim=function(){return this.replace(/^\s+|\s+$/g,"")
}})(jQuery);(function(){var a=window.Bridge;var b={Version:"1.0.5",options:{adapter:"auto",path:false},Framework:{jQuery:{included:!!window.jQuery&&jQuery.fn.jquery,required:"1.5"},Prototype:{included:!!window.Prototype&&Prototype.Version,required:"1.7"}},insertScript:function(f){try{document.write("<script type='text/javascript' src='"+f+"'><\/script>")
}catch(g){var d=document.head||document.getElementsByTagName("head")[0],c=document.createElement("script");c.type="text/javascript";
c.src=f;d.appendChild(c)}},start:function(){var p=/^(\d+(\.?\d+){0,3})([_-]+[A-Za-z0-9]+)?/;function f(B){var E=B.match(p),z=E&&E[1]&&E[1].split(".")||[],C=0;
for(var D=0,A=z.length;D<A;D++){C+=parseInt(z[D]*Math.pow(10,6-D*2))}return E&&E[3]?C-1:C}function j(l,z){return(f(l)>=f(z))
}if(a&&a.options){for(var c in a.options){b.options[c]=a.options[c]}}var m=this.options.adapter=="auto";if(m||(!m&&this.Framework[this.options.adapter]&&!this.Framework[this.options.adapter].included)){this.options.adapter=null;
for(var v in this.Framework){var y=this.Framework[v];if(y.included&&j(y.included,y.required)){this.options.adapter=v}}if(!this.options.adapter||this.options.adapter=="auto"){var d=[];
for(v in this.Framework){d.push(v+" >= "+this.Framework[v].required)}var q=d.join(", "),x=q.lastIndexOf(", ");if(x){q=q.substring(0,x)+" or "+q.substring(x+2)
}alert("BridgeJS requires "+q+" included before bridge.js")}}if(this.options.path&&/^(https?:\/\/|\/)/.test(this.options.path)){this.path=this.options.path
}else{var g=document.getElementsByTagName("script"),u=/bridge([\w\d-_.]+)?\.js(.*)/;for(var k=0,e=g.length;k<e;k++){var r=g[k];
if(r.src.match(u)){this.path=r.src.replace(u,"")}}}this.insertScript(this.path+"adapters/shared.js");this.insertScript(this.path+"adapters/"+this.options.adapter.toLowerCase()+".js")
}};window.Bridge=b;b.start()})();(function(){var d=Array.prototype.slice,c=Object.prototype.toString;Bridge.Shared={"break":{},Array:{_each:function(f,e){for(var j=0,g=f.length;
j<g;j++){e(f[j])}},each:function(g,f,l){var k=0;try{Bridge.Shared.Array._each(g,function(b){f.call(l,b,k++)})}catch(j){if(j!=Bridge.Shared["break"]){throw j
}}}},Function:{bind:function(a,f){var e=d.call(arguments,2);return function(){return a.apply(f,e.concat(d.call(arguments)))
}},bindAsEventListener:function(a,e){return function(b){return a.apply(e,[b||window.event].concat(d.call(arguments)))}},wrap:function(a,f){var e=a;
return function(){var g=[Bridge.Shared.Function.bind(e,this)].concat(d.call(arguments));return f.apply(this,g)}},curry:function(a){if(arguments.length===1){return a
}var e=d.call(arguments,1);return function(){return a.apply(this,e.concat(d.call(arguments)))}},delay:function(a,f){var e=d.call(arguments,2);
return setTimeout(function(){return a.apply(a,e)},f*1000)},defer:function(a){return this.delay.apply(this,[a,0.01].concat(d.call(arguments,1)))
}},Object:{extend:function(f,e){for(var g in e){f[g]=e[g]}return f},clone:function(b){return this.extend({},b)},isBoolean:function(b){return c.call(b)=="[object Boolean]"
}},String:{capitalize:function(b){return b.charAt(0).toUpperCase()+b.substring(1).toLowerCase()},times:function(f,e){return e<1?"":Array(e+1).join(f)
},strip:function(b){return b.replace(/^\s+/,"").replace(/\s+$/,"")}}}})();jQuery.extend(Bridge,function(m){function K(a){a=m.find(a);
var b=[];v.each(a,function(c){b.push(new y(c))});this.source=b}function y(a){this.source=u.isString(a)?m.find("#"+a)[0]:a
}function L(b,c){var a=[b,c];a.left=b;a.top=c;return a}function x(a){if(a.constructor&&a.constructor==y){return a}a=new y(a);
return a.source&&a}function G(){}var M=window.Object.prototype.toString,I=window.Array.prototype.slice,d=function(a,b){b=b||{};
return x(document.createElement(a)).writeAttribute(b)},r=window.Object,u=m.extend(function(){function a(e){var b=[],c;for(c in e){e.hasOwnProperty(c)&&b.push(c)
}return b}return{isArray:m.isArray,isFunction:m.isFunction,isNumber:function(b){return M.call(b)=="[object Number]"},isString:function(b){return M.call(b)=="[object String]"
},isUndefined:function(b){return typeof b=="undefined"},isElement:function(b){return !!b&&b.nodeType==1},keys:r.keys||a}}(),Bridge.Shared.Object),H=m.extend({argumentNames:function(a){a=a.toString().match(/^[\s\(]*function[^(]*\(([^)]*)\)/)[1].replace(/\/\/.*?[\r\n]|\/\*(?:.|[\r\n])*?\*\//g,"").replace(/\s+/g,"").split(",");
return a.length==1&&!a[0]?[]:a}},Bridge.Shared.Function),J={observe:function(b,c,a){b=x(b);m(b.source).bind(c,a)},stopObserving:function(b,c,a){b=x(b);
m(b.source).unbind(c,a)},findElement:function(b,c){var a=b.target;if(!c){return a}return m(a).closest(c)[0]},stop:function(a){a.preventDefault();
a.stopPropagation();a.stopped=true},pointer:function(a){return{x:a.pageX,y:a.pageY}},pointerX:function(a){return a.pageX},pointerY:function(a){return a.pageY
}};u.extend(y.prototype,{addClassName:function(a){m(this.source).addClass(a);return this},removeClassName:function(a){m(this.source).removeClass(a);
return this},getWidth:function(){return m(this.source).outerWidth()},getHeight:function(){return m(this.source).outerHeight()
},getDimensions:function(){return{width:this.getWidth(this.source),height:this.getHeight(this.source)}},cumulativeOffset:function(){var b=m(this.source).offset(),c=this.cumulativeScrollOffset(),a=f.getScrollOffsets();
b.left+=c.left-a.left;b.top+=c.top-a.top;return L(b.left,b.top)},cumulativeScrollOffset:function(){var b=this.source,c=0,a=0;
do{c+=b.scrollTop||0;a+=b.scrollLeft||0;b=b.parentNode}while(b);return L(a,c)},remove:function(){m(this.source).remove()},insert:function(){var a={bottom:"append",top:"prepend",after:"after",before:"before"};
return function(c){if(u.isElement(c.source||c)||u.isString(c)){m(this.source).append(c.source||c)}else{for(var b in c){m(this.source)[a[b]](c[b].source||c[b])
}}return this}}(),update:function(a){m(this.source).html(a.source||a);return this},writeAttribute:function(a){m(this.source).attr(a);
return this},setStyle:function(a){m(this.source).css(a);return this},getStyle:function(a){return m(this.source).css(a)},show:function(){m(this.source).show();
return this},hide:function(){m(this.source).hide();return this},match:function(a){return m(this.source).is(a)},up:function(a){return a?m(this.source).closest(a)[0]:m(this.source).parent()[0]
},down:function(a){return a?m(this.source).find(a).first()[0]:m(this.source).children().first()[0]},store:function(a,b){m.data(this.source,a,b);
return this},retrieve:function(a){return m.data(this.source,a)},eliminate:function(a){this.store(a,null);return this},observe:function(){var a=[this.source].concat(I.call(arguments));
J.observe.apply(null,a);return this},stopObserving:function(){var a=[this.source].concat(I.call(arguments));J.stopObserving.apply(null,a);
return this}});u.extend(K.prototype,{invoke:function(){var a=I.call(arguments),b=a.shift();v._each(this.source,function(c){c[b].apply(c,a)
});return this},each:function(b,c){var a=0;v._each(this.source,function(e){b.call(c,e,a++)});return this}});d=function(a,b){b=b||{};
return x(document.createElement(a)).writeAttribute(b)};var f={getWidth:function(){return m(window).width()},getHeight:function(){return m(window).height()
},getDimensions:function(){return{width:this.getWidth(),height:this.getHeight()}},getScrollOffsets:function(){return{left:m(window).scrollLeft(),top:m(window).scrollTop()}
}},v=m.extend(function(){function b(e,g){return m.inArray(g,e)>-1}function c(j,k,e){var g=[];this.each(j,function(q,l){k.call(e,q,l)&&g.push(q)
});return g}function a(j,k,e){var g;this.each(j,function(q,l){if(k.call(e,q,l)){g=q;throw Bridge.Shared["break"]}});return g
}return{find:a,detect:a,findAll:c,select:c,without:function(e){var g=I.call(arguments,1);return this.select(e,function(j){return !v.include(g,j)
})},include:b,member:b}}(),Bridge.Shared.Array),p={};p.Request=function(a,b){this.options=u.extend({onComplete:G,onSuccess:G,onException:G,method:"post",parameters:""},b||{});
jQuery.ajax({url:a,data:this.options.parameters,type:this.options.method,complete:Bridge.Shared.Function.bind(function(c){u.isFunction(this.options.onComplete)&&this.options.onComplete(c)
},this),success:Bridge.Shared.Function.bind(function(c){u.isFunction(this.options.onSuccess)&&this.options.onSuccess(c)},this),error:Bridge.Shared.Function.bind(function(c,e,g){u.isFunction(this.options.onException)&&this.options.onException(c,g)
},this)})};return{Ajax:p,Array:v,each:v.each,_each:v._each,Element:d,Event:J,Function:H,Object:u,domloaded:function(a){m(document).ready(a)
},$:x,$$:function(a){return new K(a)},emptyFunction:G,K:function(a){return a},String:Bridge.Shared.String,Viewport:f}}(jQuery));
document.createElement("canvas").getContext||function(){function L(){}function O(b){this.type_=b,this.r1_=this.y1_=this.x1_=this.r0_=this.y0_=this.x0_=0,this.colors_=[]
}function Q(e,d,f){!D(d)||(e.m_=d,f&&(e.lineScale_=aa(ab(d[0][0]*d[1][1]-d[0][1]*d[1][0]))))}function D(e){var d=0;for(;d<3;
d++){var f=0;for(;f<2;f++){if(!isFinite(e[d][f])||isNaN(e[d][f])){return !1}}}return !0}function E(f,e,j,g){f.currentPath_.push({type:"bezierCurveTo",cp1x:e.x,cp1y:e.y,cp2x:j.x,cp2y:j.y,x:g.x,y:g.y}),f.currentX_=g.x,f.currentY_=g.y
}function G(d){this.m_=M(),this.mStack_=[],this.aStack_=[],this.currentPath_=[],this.fillStyle=this.strokeStyle="#000",this.lineWidth=1,this.lineJoin="miter",this.lineCap="butt",this.miterLimit=Z*1,this.globalAlpha=1,this.canvas=d;
var c=d.ownerDocument.createElement("div");c.style.width=d.clientWidth+"px",c.style.height=d.clientHeight+"px",c.style.overflow="hidden",c.style.position="absolute",d.appendChild(c),this.element_=c,this.lineScale_=this.arcScaleY_=this.arcScaleX_=1
}function H(b){switch(b){case"butt":return"flat";case"round":return"round";case"square":default:return"square"}}function I(k){var j,r=1;
k=String(k);if(k.substring(0,3)=="rgb"){var q=k.indexOf("(",3),p=k.indexOf(")",q+1),m=k.substring(q+1,p).split(",");j="#";
var l=0;for(;l<3;l++){j+=R[Number(m[l])]}m.length==4&&k.substr(3,1)=="a"&&(r=m[3])}else{j=k}return{color:j,alpha:r}}function J(d,c){c.fillStyle=d.fillStyle,c.lineCap=d.lineCap,c.lineJoin=d.lineJoin,c.lineWidth=d.lineWidth,c.miterLimit=d.miterLimit,c.shadowBlur=d.shadowBlur,c.shadowColor=d.shadowColor,c.shadowOffsetX=d.shadowOffsetX,c.shadowOffsetY=d.shadowOffsetY,c.strokeStyle=d.strokeStyle,c.globalAlpha=d.globalAlpha,c.arcScaleX_=d.arcScaleX_,c.arcScaleY_=d.arcScaleY_,c.lineScale_=d.lineScale_
}function K(k,j){var r=M(),q=0;for(;q<3;q++){var p=0;for(;p<3;p++){var m=0,l=0;for(;l<3;l++){m+=k[q][l]*j[l][p]}r[q][p]=m
}}return r}function M(){return[[1,0,0],[0,1,0],[0,0,1]]}function S(d){var c=d.srcElement;c.firstChild&&(c.firstChild.style.width=c.clientWidth+"px",c.firstChild.style.height=c.clientHeight+"px")
}function T(d){var c=d.srcElement;switch(d.propertyName){case"width":c.style.width=c.attributes.width.nodeValue+"px",c.getContext().clearRect();
break;case"height":c.style.height=c.attributes.height.nodeValue+"px",c.getContext().clearRect()}}function V(e,d){var f=W.call(arguments,2);
return function(){return e.apply(d,f.concat(W.call(arguments)))}}function X(){return this.context_||(this.context_=new G(this))
}var af=Math,ae=af.round,ad=af.sin,ac=af.cos,ab=af.abs,aa=af.sqrt,Z=10,Y=Z/2,W=Array.prototype.slice,U={init:function(d){if(/MSIE/.test(navigator.userAgent)&&!window.opera){var c=d||document;
c.createElement("canvas"),c.attachEvent("onreadystatechange",V(this.init_,this,c))}},init_:function(f){f.namespaces.g_vml_||f.namespaces.add("g_vml_","urn:schemas-microsoft-com:vml","#default#VML"),f.namespaces.g_o_||f.namespaces.add("g_o_","urn:schemas-microsoft-com:office:office","#default#VML");
if(!f.styleSheets.ex_canvas_){var e=f.createStyleSheet();e.owningElement.id="ex_canvas_",e.cssText="canvas{display:inline-block;overflow:hidden;text-align:left;width:300px;height:150px}g_vml_\\:*{behavior:url(#default#VML)}g_o_\\:*{behavior:url(#default#VML)}"
}var j=f.getElementsByTagName("canvas"),g=0;for(;g<j.length;g++){this.initElement(j[g])}},initElement:function(d){if(!d.getContext){d.getContext=X,d.innerHTML="",d.attachEvent("onpropertychange",T),d.attachEvent("onresize",S);
var c=d.attributes;c.width&&c.width.specified?d.style.width=c.width.nodeValue+"px":d.width=d.clientWidth,c.height&&c.height.specified?d.style.height=c.height.nodeValue+"px":d.height=d.clientHeight
}return d}};U.init();var R=[],P=0;for(;P<16;P++){var N=0;for(;N<16;N++){R[P*16+N]=P.toString(16)+N.toString(16)}}var F=G.prototype;
F.clearRect=function(){this.element_.innerHTML=""},F.beginPath=function(){this.currentPath_=[]},F.moveTo=function(e,d){var f=this.getCoords_(e,d);
this.currentPath_.push({type:"moveTo",x:f.x,y:f.y}),this.currentX_=f.x,this.currentY_=f.y},F.lineTo=function(e,d){var f=this.getCoords_(e,d);
this.currentPath_.push({type:"lineTo",x:f.x,y:f.y}),this.currentX_=f.x,this.currentY_=f.y},F.bezierCurveTo=function(v,u,r,q,p,m){var l=this.getCoords_(p,m),k=this.getCoords_(v,u),j=this.getCoords_(r,q);
E(this,k,j,l)},F.quadraticCurveTo=function(k,j,r,q){var p=this.getCoords_(k,j),m=this.getCoords_(r,q),l={x:this.currentX_+0.6666666666666666*(p.x-this.currentX_),y:this.currentY_+0.6666666666666666*(p.y-this.currentY_)};
E(this,l,{x:l.x+(m.x-this.currentX_)/3,y:l.y+(m.y-this.currentY_)/3},m)},F.arc=function(ai,ah,ag,C,B,A){ag*=Z;var z=A?"at":"wa",y=ai+ac(C)*ag-Y,x=ah+ad(C)*ag-Y,v=ai+ac(B)*ag-Y,u=ah+ad(B)*ag-Y;
y==v&&!A&&(y+=0.125);var g=this.getCoords_(ai,ah),d=this.getCoords_(y,x),c=this.getCoords_(v,u);this.currentPath_.push({type:z,x:g.x,y:g.y,radius:ag,xStart:d.x,yStart:d.y,xEnd:c.x,yEnd:c.y})
},F.rect=function(f,e,j,g){this.moveTo(f,e),this.lineTo(f+j,e),this.lineTo(f+j,e+g),this.lineTo(f,e+g),this.closePath()},F.strokeRect=function(g,f,l,k){var j=this.currentPath_;
this.beginPath(),this.moveTo(g,f),this.lineTo(g+l,f),this.lineTo(g+l,f+k),this.lineTo(g,f+k),this.closePath(),this.stroke(),this.currentPath_=j
},F.fillRect=function(g,f,l,k){var j=this.currentPath_;this.beginPath(),this.moveTo(g,f),this.lineTo(g+l,f),this.lineTo(g+l,f+k),this.lineTo(g,f+k),this.closePath(),this.fill(),this.currentPath_=j
},F.createLinearGradient=function(g,f,l,k){var j=new O("gradient");j.x0_=g,j.y0_=f,j.x1_=l,j.y1_=k;return j},F.createRadialGradient=function(k,j,r,q,p,m){var l=new O("gradientradial");
l.x0_=k,l.y0_=j,l.r0_=r,l.x1_=q,l.y1_=p,l.r1_=m;return l},F.drawImage=function(aq){var ap,ao,an,am,al,ak,aj,ai,ah=aq.runtimeStyle.width,ag=aq.runtimeStyle.height;
aq.runtimeStyle.width="auto",aq.runtimeStyle.height="auto";var C=aq.width,B=aq.height;aq.runtimeStyle.width=ah,aq.runtimeStyle.height=ag;
if(arguments.length==3){ap=arguments[1],ao=arguments[2],al=ak=0,aj=an=C,ai=am=B}else{if(arguments.length==5){ap=arguments[1],ao=arguments[2],an=arguments[3],am=arguments[4],al=ak=0,aj=C,ai=B
}else{if(arguments.length==9){al=arguments[1],ak=arguments[2],aj=arguments[3],ai=arguments[4],ap=arguments[5],ao=arguments[6],an=arguments[7],am=arguments[8]
}else{throw Error("Invalid number of arguments")}}}var A=this.getCoords_(ap,ao),z=[];z.push(" <g_vml_:group",' coordsize="',Z*10,",",Z*10,'"',' coordorigin="0,0"',' style="width:',10,"px;height:",10,"px;position:absolute;");
if(this.m_[0][0]!=1||this.m_[0][1]){var y=[];y.push("M11=",this.m_[0][0],",","M12=",this.m_[1][0],",","M21=",this.m_[0][1],",","M22=",this.m_[1][1],",","Dx=",ae(A.x/Z),",","Dy=",ae(A.y/Z),"");
var x=A,g=this.getCoords_(ap+an,ao),b=this.getCoords_(ap,ao+am),a=this.getCoords_(ap+an,ao+am);x.x=af.max(x.x,g.x,b.x,a.x),x.y=af.max(x.y,g.y,b.y,a.y),z.push("padding:0 ",ae(x.x/Z),"px ",ae(x.y/Z),"px 0;filter:progid:DXImageTransform.Microsoft.Matrix(",y.join(""),", sizingmethod='clip');")
}else{z.push("top:",ae(A.y/Z),"px;left:",ae(A.x/Z),"px;")}z.push(' ">','<g_vml_:image src="',aq.src,'"',' style="width:',Z*an,"px;"," height:",Z*am,'px;"',' cropleft="',al/C,'"',' croptop="',ak/B,'"',' cropright="',(C-al-aj)/C,'"',' cropbottom="',(B-ak-ai)/B,'"'," />","</g_vml_:group>"),this.element_.insertAdjacentHTML("BeforeEnd",z.join(""))
},F.stroke=function(aF){var aE=[],aD=I(aF?this.fillStyle:this.strokeStyle),aC=aD.color,aB=aD.alpha*this.globalAlpha;aE.push("<g_vml_:shape",' filled="',!!aF,'"',' style="position:absolute;width:',10,"px;height:",10,'px;"',' coordorigin="0 0" coordsize="',Z*10," ",Z*10,'"',' stroked="',!aF,'"',' path="');
var aA={x:null,y:null},az={x:null,y:null},ay=0;for(;ay<this.currentPath_.length;ay++){var ax=this.currentPath_[ay];switch(ax.type){case"moveTo":aE.push(" m ",ae(ax.x),",",ae(ax.y));
break;case"lineTo":aE.push(" l ",ae(ax.x),",",ae(ax.y));break;case"close":aE.push(" x "),ax=null;break;case"bezierCurveTo":aE.push(" c ",ae(ax.cp1x),",",ae(ax.cp1y),",",ae(ax.cp2x),",",ae(ax.cp2y),",",ae(ax.x),",",ae(ax.y));
break;case"at":case"wa":aE.push(" ",ax.type," ",ae(ax.x-this.arcScaleX_*ax.radius),",",ae(ax.y-this.arcScaleY_*ax.radius)," ",ae(ax.x+this.arcScaleX_*ax.radius),",",ae(ax.y+this.arcScaleY_*ax.radius)," ",ae(ax.xStart),",",ae(ax.yStart)," ",ae(ax.xEnd),",",ae(ax.yEnd))
}if(ax){if(aA.x==null||ax.x<aA.x){aA.x=ax.x}if(az.x==null||ax.x>az.x){az.x=ax.x}if(aA.y==null||ax.y<aA.y){aA.y=ax.y}if(az.y==null||ax.y>az.y){az.y=ax.y
}}}aE.push(' ">');if(aF){if(typeof this.fillStyle=="object"){var aw=this.fillStyle,av=0,au={x:0,y:0},at=0,aq=1;if(aw.type_=="gradient"){var ao=aw.x1_/this.arcScaleX_,am=aw.y1_/this.arcScaleY_,ak=this.getCoords_(aw.x0_/this.arcScaleX_,aw.y0_/this.arcScaleY_),ag=this.getCoords_(ao,am);
av=Math.atan2(ag.x-ak.x,ag.y-ak.y)*180/Math.PI,av<0&&(av+=360),av<0.000001&&(av=0)}else{var ak=this.getCoords_(aw.x0_,aw.y0_),u=az.x-aA.x,b=az.y-aA.y;
au={x:(ak.x-aA.x)/u,y:(ak.y-aA.y)/b},u/=this.arcScaleX_*Z,b/=this.arcScaleY_*Z;var a=af.max(u,b);at=2*aw.r0_/a,aq=2*aw.r1_/a-at
}var ar=aw.colors_;ar.sort(function(d,c){return d.offset-c.offset});var ap=ar.length,an=ar[0].color,al=ar[ap-1].color,aj=ar[0].alpha*this.globalAlpha,ai=ar[ap-1].alpha*this.globalAlpha,ah=[],ay=0;
for(;ay<ap;ay++){var v=ar[ay];ah.push(v.offset*aq+at+" "+v.color)}aE.push('<g_vml_:fill type="',aw.type_,'"',' method="none" focus="100%"',' color="',an,'"',' color2="',al,'"',' colors="',ah.join(","),'"',' opacity="',ai,'"',' g_o_:opacity2="',aj,'"',' angle="',av,'"',' focusposition="',au.x,",",au.y,'" />')
}else{aE.push('<g_vml_:fill color="',aC,'" opacity="',aB,'" />')}}else{var g=this.lineScale_*this.lineWidth;g<1&&(aB*=g),aE.push("<g_vml_:stroke",' opacity="',aB,'"',' joinstyle="',this.lineJoin,'"',' miterlimit="',this.miterLimit,'"',' endcap="',H(this.lineCap),'"',' weight="',g,'px"',' color="',aC,'" />')
}aE.push("</g_vml_:shape>"),this.element_.insertAdjacentHTML("beforeEnd",aE.join(""))},F.fill=function(){this.stroke(!0)},F.closePath=function(){this.currentPath_.push({type:"close"})
},F.getCoords_=function(e,d){var f=this.m_;return{x:Z*(e*f[0][0]+d*f[1][0]+f[2][0])-Y,y:Z*(e*f[0][1]+d*f[1][1]+f[2][1])-Y}
},F.save=function(){var b={};J(this,b),this.aStack_.push(b),this.mStack_.push(this.m_),this.m_=K(M(),this.m_)},F.restore=function(){J(this.aStack_.pop(),this),this.m_=this.mStack_.pop()
},F.translate=function(d,c){Q(this,K([[1,0,0],[0,1,0],[d,c,1]],this.m_),!1)},F.rotate=function(d){var c=ac(d),f=ad(d);Q(this,K([[c,f,0],[-f,c,0],[0,0,1]],this.m_),!1)
},F.scale=function(d,c){this.arcScaleX_*=d,this.arcScaleY_*=c,Q(this,K([[d,0,0],[0,c,0],[0,0,1]],this.m_),!0)},F.transform=function(j,g,p,m,l,k){Q(this,K([[j,g,0],[p,m,0],[l,k,1]],this.m_),!0)
},F.setTransform=function(j,g,p,m,l,k){Q(this,[[j,g,0],[p,m,0],[l,k,1]],!0)},F.clip=function(){},F.arcTo=function(){},F.createPattern=function(){return new L
},O.prototype.addColorStop=function(d,c){c=I(c),this.colors_.push({offset:d,color:c.color,alpha:c.alpha})},G_vmlCanvasManager=U,CanvasRenderingContext2D=G,CanvasGradient=O,CanvasPattern=L
}();var Spinners={Version:"1.2.2"};(function(k){function l(a){a=k.$(a);!a||(this.element=a.source,Spinners.remove(a),Spinners.removeDetached(),this.options=k.Object.extend({radii:[5,10],color:"#000",dashWidth:1.8,dashes:12,opacity:1,padding:3,speed:0.7,build:!0},arguments[1]),this._position=0,this._state="stopped",this.options.build&&this.build(),Spinners.add(this))
}function q(f,e){if(!e){return f}var v=f.slice(0,e),g=f.slice(e,f.length);return g.concat(v)}function r(b){return b*Math.PI/180
}function u(b){return b*180/Math.PI}function j(d,c){return Math.sqrt(d*d+c*c)}k.Object.extend(Spinners,{spinners:[],Required:{Bridge:"1.0.4"},support:{canvas:!!document.createElement("canvas").getContext},insertScript:function(e){try{document.write("<script type='text/javascript' src='"+e+"'><\/script>")
}catch(d){var f=document.head||Bridge.$$("head").source[0];f.appendChild(new Bridge.Element("script",{src:e,type:"text/javascript"}))
}},require:function(d,c){(typeof window[d]=="undefined"||this.convertVersionString(window[d].Version)<this.convertVersionString(this.Required[d]))&&alert("Spinners requires "+(c||d)+" >= "+this.Required[d])
},convertVersionString:function(d){var c=d.replace(/_.*|\./g,"");c=parseInt(c+Bridge.String.times("0",4-c.length));return d.indexOf("_")>-1?c-1:c
},start:function(){this.require("Bridge"),!this.support.canvas&&!window.G_vmlCanvasManager&&alert("Spinners requires ExplorerCanvas (excanvas.js)")
},get:function(a){a=k.$(a).source;if(!!a){var d=null;k._each(this.spinners,function(b){b.element==a&&(d=b)});return d}},add:function(b){this.spinners.push(b)
},remove:function(a){var d=this.get(a);d&&(d.remove(),this.spinners=k.Array.without(this.spinners,d))},removeDetached:function(){function d(e){var c=e;
while(c&&c.parentNode){c=c.parentNode}return c}function a(e){var c=d(e);return !!c&&!!c.body}return function(){k.each(this.spinners,function(b){b.element&&!a(b.element)&&this.remove(b.element)
},this)}}()});var p={drawRoundedRectangle:function(a){var B=k.Object.extend({top:0,left:0,width:0,height:0,radius:0},arguments[1]||{}),A=B,z=A.left,y=A.top,x=A.width,v=A.height,d=A.radius;
a.beginPath(),a.moveTo(z+d,y),a.arc(z+x-d,y+d,d,r(-90),r(0),!1),a.arc(z+x-d,y+v-d,d,r(0),r(90),!1),a.arc(z+d,y+v-d,d,r(90),r(180),!1),a.arc(z+d,y+d,d,r(-180),r(-90),!1),a.closePath(),a.fill()
}},m=function(){function x(d,e){k.Object.isUndefined(e)&&(e=1);return"rgba("+y(d,e).join()+")"}function y(e,d){var f=z(e);
f[3]=d,f.opacity=d;return f}function z(d){var c=[];d.indexOf("#")==0&&(d=d.substring(1)),d=d.toLowerCase();if(d.replace(C,"")!=""){return null
}d.length==3?(c[0]=d.charAt(0)+d.charAt(0),c[1]=d.charAt(1)+d.charAt(1),c[2]=d.charAt(2)+d.charAt(2)):(c[0]=d.substring(0,2),c[1]=d.substring(2,4),c[2]=d.substring(4));
for(var e=0;e<c.length;e++){c[e]=A(c[e])}return B(c)}function A(b){return parseInt(b,16)}function B(d){var c=d;c.red=d[0],c.green=d[1],c.blue=d[2];
return c}var a="0123456789abcdef",C=new RegExp("["+a+"]","g"),v=function(){function c(e,g,f){e=e.toString(f||10);return k.String.times("0",g-e.length)+e
}return function(b,f,e){return"#"+c(b,2,16)+c(f,2,16)+c(e,2,16)}}();return{hex2rgb:z,hex2fill:x,rgb2hex:v}}();k.Object.extend(l.prototype,function(){function C(){if(this._layout){return this._layout
}var L=this.options,K=L.dashes,J=L.radii,I=L.dashWidth,H=Math.min(J[0],J[1]),G=Math.max(J[0],J[1]),F=Math.max(I,G),E=Math.ceil(Math.max(F,j(G,I/2)));
E+=L.padding;var D={workspace:{radius:E,opacities:a(K)},dash:{position:{top:E-G,left:E-I/2},dimensions:{width:I,height:G-H}}};
this._layout=D;return D}function a(E){var D=1/E,G=[];for(var F=0;F<E;F++){G.push((F+1)*D)}return G}function b(){this[this._state=="playing"?"pause":"play"]();
return this}function d(){if(this._state!="stopped"){this._pause(),this._position=0,this.drawPosition(0),this._state="stopped";
return this}}function e(){!this._playTimer||(window.clearTimeout(this._playTimer),this._playTimer=null)}function f(){if(this._state!="paused"){this._pause(),this._state="paused";
return this}}function g(){if(this._state!="playing"){this._state="playing";var c=this.options.speed*1000/this.options.dashes;
this._playTimer=window.setTimeout(k.Function.bind(x,this),c);return this}}function v(){this._position==this.options.dashes-1&&(this._position=-1),this._position++,this.drawPosition(this._position)
}function x(){var c=this.options.speed*1000/this.options.dashes;this.nextPosition(),this._playTimer=window.setTimeout(k.Function.bind(x,this),c)
}function y(E,D){this.ctx.fillStyle=m.hex2fill(D,E);var I=this.getLayout(),H=I.workspace.radius,G=I.dash.position,F=I.dash.dimensions;
p.drawRoundedRectangle(this.ctx,{top:G.top-H,left:G.left-H,width:F.width,height:F.height,radius:Math.min(F.height,F.width)/2})
}function z(L){var K=this.getLayout().workspace,J=K.radius*2,I=-1*K.radius,H=this.options.dashes;this.ctx.clearRect(I,I,J,J);
var G=r(360/H),F=q(K.opacities,L*-1);for(var E=0,D=H;E<D;E++){this.drawDash(F[E],this.options.color),this.ctx.rotate(G)}}function A(){this.remove();
var D=this.getLayout(),F=D.workspace.radius,E=F*2;k.$(this.element).insert(this.canvas=(new k.Element("canvas",{height:E,width:E})).setStyle({zoom:1})),window.G_vmlCanvasManager&&G_vmlCanvasManager.initElement(this.canvas.source),this.ctx=this.canvas.source.getContext("2d"),this.ctx.globalAlpha=this.options.opacity,this.ctx.translate(F,F),this.drawPosition(0);
return this}function B(){!this.canvas||(this.stop(),this.canvas.remove(),this.canvas=null,this.ctx=null)}return{remove:B,build:A,getLayout:C,_nextPosition:x,nextPosition:v,drawPosition:z,drawDash:y,play:g,pause:f,_pause:e,stop:d,toggle:b}
}()),window.Spinner=l,Spinners.start()})(Bridge);eval(function(j,b,l,d,g,f){g=function(a){return(a<b?"":g(parseInt(a/b)))+((a=a%b)>35?String.fromCharCode(a+29):a.toString(36))
};if(!"".replace(/^/,String)){while(l--){f[g(l)]=d[l]||g(l)}d=[function(a){return f[a]}];g=function(){return"\\w+"};l=1}while(l--){if(d[l]){j=j.replace(new RegExp("\\b"+g(l)+"\\b","g"),d[l])
}}return j}('(X(f){X O(a,c,e){19(a=f.$(a)){W.1g=a.1o;u.1D(W.1g);u.39(W);a=f.17.1W(c)||f.17.2W(c)?e||{}:c;W.13=u.5Y(a);W.3o=c;W.2e=W.13.2e||+u.13.4m;W.1q={3a:{11:1,12:1},4W:[],3F:[],2j:{4n:1l,4o:1l,1Q:1l,3G:1l,4p:1l,4X:1l}};c=W.13.1A;W.1A=c=="2X"?"2X":c=="4q"||!c?W.1g:c&&1X.7w(c)&&f.$(W.13.1A)||W.1g;W.5Z()}}X B(a,c){1U(14 e 60 c){c[e]&&c[e].3H&&c[e].3H===17?(a[e]=f.17.1v(a[e])||{},B(a[e],c[e])):a[e]=c[e]}1a a}X P(a,c){W.1g=f.$(a).1o;!W.1g||(W.13=f.17.1x({2Y:5,1R:{x:0,y:0},1E:"#4Y",1w:0.5,2A:1},c||{}),W.2n=W.13.2A,Q.39(W),W.1S(),W.4r())}X R(a,c,e){(W.1g=f.$(a).1o)&&c&&(W.13=f.17.1x({2Y:5,1R:{x:5,y:5},1E:"#61",1w:0.5,2A:1},e||{}),W.2n=W.13.2A,W.1q={},G.39(W),W.1S(),W.4r())}X S(a){19(a){W.1g=f.$(a).1o;C.1D(W.1g);a=W.25();W.13=f.17.1v(a.13);W.2n=1;W.1q={};C.39(W);W.1F=W.13.1k.1t;W.7x=W.13.1h&&W.1F;W.1S()}}X N(a){1a 1/1b.62(a)}X w(a){1a a*1b.2B/26}X z(a){14 c={},e;1U(e 60 a){c[e]=a[e]+"2k"}1a c}14 A=f.17.1x({63:"1.5.0.2",13:{}},1Y.3I),F=X(a){X c(e){1a(e=64(e+"([\\\\d.]+)").7y(a))?7z(e[1]):1H}1a{3Z:!!1Y.7A&&a.3b("4s")===-1&&c("7B "),4s:a.3b("4s")>-1&&c("4s/"),40:a.3b("65/")>-1&&c("65/"),3J:a.3b("3J")>-1&&a.3b("7C")===-1&&c("7D:"),7E:!!a.27(/7F.*7G.*7H/),4Z:a.3b("4Z")>-1&&c("4Z/")}}(7I.7J);f.17.1x(A,X(){X a(g){(53 1Y[g]=="7K"||c(1Y[g].63)<c(e[g]))&&66("3I 67 "+g+" >= "+e[g])}X c(g){14 l=g.4t(/54.*|\\./g,"");l=3K(l+f.68.7L("0",4-l.2l));1a g.3b("54")>-1?l-1:l}14 e={4u:"1.0.5",69:"1.2.2"},b=X(){14 g=1X.6a("3c");1a!!g.3p&&!!g.3p("2d")}(),d;55{d=!!1X.4v("7M")}56(j){d=1l}1a{3L:{3c:b,57:d,4w:X(){14 g=1l;f.1Z(["7N","7O","7P"],X(l){55{1X.4v(l);g=1H}56(h){}});1a g}()},41:X(){a("4u");a("69");!W.3L.3c&&!1Y.2o&&66("3I 67 7Q (7R.4x)");19(W.13&&W.13.2L&&/^(7S?:\\/\\/|\\/)/.58(W.13.2L)){W.2L=W.13.2L||"";W.2L.7T(W.2L.2l-1)!="/"&&(W.2L+="/")}1B{14 g=/20([\\w\\d-54.]+)?\\.4x(.*)/;f.$$("42[43]").1Z(X(l){l=l.1o.43;19(l.27(g)){W.2L=l.4t(g,"")}},W)}4u.1Z("3q-59-4y 3q".3r(" "),X(l){l=W.2L+l+".4x";1U(14 h="",n=0,p=1b.3s(1b.4z()*16);n<p;n++){h+="6b".2Z(1b.3s(1b.4z()*52))}l+="?="+h;55{1X.7U("<42 3t=\'6c/6d\' 43=\'"+l+"\'><\\/42>")}56(s){(1X.5a||f.$$("5a").1o[0]).1o.6e((1y f.1I("42",{43:l,3t:"6c/6d"})).1o)}},W);f.7V(X(){u.6f()})},21:X(g){u.21(g);1a W},1J:X(g){u.1J(g);1a W},3u:X(g){u.3u(g);1a W},1D:X(g){u.1D(g);g&&!f.17.2W(g)&&f.$$(g).1Z(X(l){l.1o.4A("3v-20");l.1o.4A("3v-20-13")});1a W},5b:X(){u.5b();1a W},3d:X(g){u.3d(g);1a W},5c:X(g){u.5c(g);1a W},5d:X(g){u.5d(g);1a W},1Q:X(g){19(f.17.2W(g)){1a u.5e(g)}19(!f.17.5f(g)){f.$$(g);14 l=0;f.28(f.$$(g).1o,X(h){u.5e(h&&h.1o)&&l++});1a l}1a u.3M().2l}}}());A.41();(X(){X a(c,e){14 b=f.17.5g(c)?c:[f.$(c)],d=f.2M.6g(e);f.28(b,X(j){j.1J()});d=1X.7W(d.x,d.y);f.28(b,X(j){j.21()});19(d){b=1X.4v("7X");b.7Y(e.3t,e.7Z,e.80,e.81,e.82,e.83,e.84,e.85,e.86,e.87,e.88,e.89,e.8a,e.8b,e.8c);d.8d(b)}}1a 1X.4v?a:f.K})();14 J={6h:X(a,c){14 e=f.17.1x({2p:1l,6i:1l,Z:0,Y:0,11:0,12:0,1m:0},c||{}),b=e.Y,d=e.Z,j=e.11,g=e.12,l=e.1m;19(e.6i){14 h=2*l;b-=l;d-=l;j+=h;g+=h}l?(a.2f(),a.3N(b+l,d),e.2p=="2N"?a.1c(b+j,d):a.1V(b+j-l,d+l,l,w(-90),w(0),1l),e.2p=="30"?a.1c(b+j,d+g):a.1V(b+j-l,d+g-l,l,w(0),w(90),1l),e.2p=="31"?a.1c(b,d+g):a.1V(b+l,d+g-l,l,w(90),w(26),1l),e.2p=="2C"?a.1c(b,d):a.1V(b+l,d+l,l,w(-26),w(-90),1l),a.2g(),a.3e()):a.6j(d,b,j,g)},8e:X(a,c,e){e=f.17.1x({x:0,y:0,1E:"#4Y"},e||{});1U(14 b=0,d=c.2l;b<d;b++){1U(14 j=0,g=c[b].2l;j<g;j++){14 l=3K(c[b].2Z(j))*(1/9);a.2O=H.2P(e.1E,l);l&&a.6j(e.x+j,e.y+b,1,1)}}},44:X(a,c,e){14 b;19(f.17.1W(c)){b=H.2P(c)}1B{19(f.17.1W(c.1E)){b=H.2P(c.1E,f.17.2v(c.1w)?c.1w:1)}1B{19(f.17.5g(c.1E)){e=f.17.1x({3O:0,3P:0,3Q:0,3R:0},e||{});b=J.6k.6l(a.8f(e.3O,e.3P,e.3Q,e.3R),c.1E,c.1w)}}}1a b}};J.6k={6l:X(a,c,e){e=f.17.2v(e)?e:1;1U(14 b=0,d=c.2l;b<d;b++){14 j=c[b];19(f.17.5f(j.1w)||!f.17.2v(j.1w)){j.1w=1}a.8g(j.15,H.2P(j.1E,j.1w*e))}1a a}};14 y={46:["2C","4B","2N","3f","4C","3w","30","4D","31","3x","4E","32"],47:{6m:/^(Z|Y|29|2a)(Z|Y|29|2a|2q|2h)$/,22:/^(Z|29)/,33:/(2q|2h)/,6n:/^(Z|29|Y|2a)/},6o:X(){14 a={Z:"12",Y:"11",29:"12",2a:"11"};1a X(c){1a a[c]}}(),33:X(a){1a a.34().27(W.47.33)},4F:X(a){1a!W.33(a)},2D:X(a){1a a.34().27(W.47.22)?"22":"2r"},6p:X(a){1a a.34().27(W.47.6n)},3r:X(a){1a a.34().27(W.47.6m)}},M={3y:X(a){1a a.13.1h[y.4F(a.1F)?"6q":"2h"]},48:X(a,c,e){e=f.17.1x({3S:"1K"},e||{});14 b=y.33(a.1F)?"2h":"6q",d=a.13.1h[b];a=d.11;d=d.12;d=W["1r"+f.68.8h(b)+"6r"](a,d,c);e.3S&&(d.11=1b[e.3S](d.11),d.12=1b[e.3S](d.12));1a{11:d.11,12:d.12}},6s:X(a,c,e){14 b=N(w(90-1b.5h(c/a)*26/1b.2B))*e;b=e+a+b;c=a=b*c/a;a-=e;b=a*b/c;1a{11:b,12:a}},6t:X(a,c,e){e=1b.62(w(26-1b.5h(c/a*0.5)*26/1b.2B-90))*e;e=a+e*2;1a{11:e,12:e*c/a}},3T:X(a,c){14 e=W.48(a,c),b=W.3y(a),d=y.33(a.1F),j=1b.1K(e.12+c),g={2w:{1d:{11:1b.1K(e.11),12:1b.1K(j)}},1f:{1d:e,15:{Z:0,Y:0}},1h:{1d:{11:b.11,12:b.12}}};d?g.1h.15={Z:j-b.12,Y:e.11*0.5-b.11*0.5}:g.1h.15={Z:j-b.12,Y:c};1a g},3z:X(a,c,e){14 b=y.2D(c.1F)=="2r";c=y.3r(c.1F);e={12:e.2w.1d[b?"11":"12"],11:e.2w.1d[b?"12":"11"]};b=1b.35(e.11,e.12);14 d=b/2,j=b/2,g=0,l=1,h=1,n=d,p=j;1L(c[1]){1e"Z":c[2]=="2a"&&(n=j-(b-e.11),l=-1);1u;1e"2a":1L(c[2]){1e"29":n=j-(b-e.11);p=d-(b-e.12);l=-1;g=90;1u;1e"Z":1e"2h":1e"2q":g=5i;h=l=-1;n=j-(b-e.11)}1u;1e"29":1L(c[2]){1e"Y":g=26;p=j-(b-e.12);l=-1;1u;1e"2q":1e"2h":1e"2a":p=d-(b-e.12);n=j-(b-e.11);h=l=-1}1u;1e"Y":1L(c[2]){1e"29":g=90;p=j-(b-e.12);l=h=-1;1u;1e"Z":1e"2q":1e"2h":g=5i;l=-1}}a.6u(n,p);a.3z(w(g));a.8i(l,h)},5j:X(a,c,e,b,d){b={Z:0,Y:0};14 j={Z:0,Y:0},g={Z:0,Y:0},l=f.17.1v(c);19(a.13.1h){14 h=y.6p(a.1F);h[1]=="Z"?b.Z=e.12-d:h[1]=="Y"&&(b.Y=e.12-d);14 n=y.3r(a.1F);a=y.2D(a.1F);19(a=="22"){1L(n[2]){1e"2q":1e"2h":j.Y=0.5*l.11-0.5*e.11;g.Y=0.5*l.11;1u;1e"2a":j.Y=l.11-e.11;g.Y=l.11}n[1]=="29"&&(j.Z=l.12-d,g.Z=l.12-d+e.12)}1B{1L(n[2]){1e"2q":1e"2h":j.Z=0.5*l.12-0.5*e.11;g.Z=0.5*l.12;1u;1e"29":j.Z=l.12-e.11;g.Z=l.12}n[1]=="2a"&&(j.Y=l.11-d,g.Y=l.11-d+e.12)}l[y.6o(h[1])]+=e.12-d}1B{n=y.3r(a.1F);a=y.2D(a.1F);19(a=="22"){1L(n[2]){1e"2q":1e"2h":g.Y=0.5*l.11;1u;1e"2a":g.Y=l.11}n[1]=="29"&&(g.Z=l.12)}1B{1L(n[2]){1e"2q":1e"2h":g.Z=0.5*l.12;1u;1e"29":g.Z=l.12}n[1]=="2a"&&(g.Y=l.11)}}1a{1d:l,15:{Z:0,Y:0},1j:{15:b,1d:c},1h:{15:j,1d:e},2m:g}}},H=X(){X a(b){14 d=2E(3);b.3b("#")==0&&(b=b.4G(1));b=b.34();19(b.4t(e,"")!=""){1a 1C}b.2l==3?(d[0]=b.2Z(0)+b.2Z(0),d[1]=b.2Z(1)+b.2Z(1),d[2]=b.2Z(2)+b.2Z(2)):(d[0]=b.4G(0,2),d[1]=b.4G(2,4),d[2]=b.4G(4));1U(b=0;b<d.2l;b++){d[b]=3K(d[b],16)}1a c(d)}X c(b){b.6v=b[0];b.6w=b[1];b.6x=b[2];1a b}14 e=64("[8j]","g");1a{8k:a,2P:X(b,d){f.17.5f(d)&&(d=1);14 j=d,g=a(b);g[3]=j;g.1w=j;1a"8l("+g.8m()+")"},8n:X(b){14 d=a(b);d=c(d);b=d.6v;14 j=d.6w,g=d.6x,l,h,n=b>j?b:j;g>n&&(n=g);14 p=b<j?b:j;g<p&&(p=g);h=n/8o;d=n!=0?(n-p)/n:0;19(d==0){l=0}1B{14 s=(n-b)/(n-p),k=(n-j)/(n-p);g=(n-g)/(n-p);b==n?l=g-k:j==n?l=2+s-g:l=4+k-s;l/=6;l<0&&(l+=1)}l=1b.1M(l*6y);d=1b.1M(d*6z);h=1b.1M(h*6z);b=[];b[0]=l;b[1]=d;b[2]=h;b.8p=l;b.8q=d;b.8r=h;1a"#"+(b[2]>50?"4Y":"61")}}}(),C={3q:[],1r:X(a){19(a=f.$(a).1o){14 c=1C;f.28(W.3q,X(e){e.1g==a&&(c=e)});1a c}},39:X(a){W.3q.2x(a)},1D:X(a){(a=W.1r(a))&&(W.3q=f.2E.4H(W.3q,a),a.1D())}};f.17.1x(S.4I,X(){1a{4J:X(){14 a=W.25();W.3a=a.1q.3a;a=a.13;W.1m=a.1m&&a.1m.2s||0;W.1f=a.1f&&a.1f.2s||0;W.2Q=a.2Q;a=1b.5k(W.3a.12,W.3a.11);W.1m>a/2&&(W.1m=1b.3s(a/2));W.13.1m.15=="1f"&&W.1m>W.1f&&(W.1f=W.1m);W.1q={13:{1m:W.1m,1f:W.1f,2Q:W.2Q}}},6A:X(){W.1q.1k={};14 a=W.1F;f.1Z(y.46,X(c){14 e;W.1q.1k[c]={};W.1F=c;e=W.2t();W.1q.1k[c].2m=e.2m;W.1q.1k[c].1z={1d:e.1z.1d,15:{Z:e.1z.15.Z,Y:e.1z.15.Y}};W.1q.1k[c].1t={1d:e.1O.1d};19(W.1n){e=W.1n.2t();W.1q.1k[c].2m=e.2m;W.1q.1k[c].1z.15.Z+=e.1O.15.Z;W.1q.1k[c].1z.15.Y+=e.1O.15.Y;W.1q.1k[c].1t.1d=e.1t.1d}},W);W.1F=a},1S:X(){W.36();1Y.2o&&1Y.2o.8s(1X);14 a=W.25(),c=W.13;W.1z=1y f.1I("24",{"2b":"8t"});a.4K.1P(W.1z);W.4J();W.6B(a);c.1s&&(W.6C(a),c.1s.1n&&(W.2R?(W.2R.13=c.1s.1n,W.2R.1S()):W.2R=1y P(W.1g,f.17.1x({2A:W.2n},c.1s.1n))));W.4L();c.1n&&(W.1n?(W.1n.13=c.1n,W.1n.1S()):W.1n=1y R(W.1g,W,f.17.1x({2A:W.2n},c.1n)));W.6A()},1D:X(){W.36();W.13.1n&&(G.1D(W.1g),W.13.1s&&W.13.1s.1n&&Q.1D(W.1g));W.1i&&W.1i.1D()},36:X(){!W.1z||(W.1s&&(W.1s.1D(),W.1s=1C,W.5l=1C,W.5m=1C),W.1z.1D(),W.1h=1C,W.1j=1C,W.1z=1C,W.1q={})},25:X(){1a u.1r(W.1g)},3d:X(){14 a=W.25(),c=a.1i.8u(".6D");19(c){c=f.$(c);c.1p({11:"5n",12:"5n"});14 e=3K(a.1i.5o("Z")),b=3K(a.1i.5o("Y")),d=3K(a.1i.5o("11"));a.1i.1p({Y:"-6E",Z:"-6E",11:"8v",12:"5n"});14 j=u.4M.5p(c);a.13.3g&&f.17.2v(a.13.3g)&&j.11>a.13.3g&&(c.1p({11:a.13.3g+"2k"}),j=u.4M.5p(c));a.1q.3a=j;a.1i.1p({Y:b+"2k",Z:e+"2k",11:d+"2k"});W.1S()}},49:X(a){W.1F!=a&&(W.1F=a,W.1S())},6C:X(a){14 c=a.13.1s;c={11:c.3A+2*c.1f,12:c.3A+2*c.1f};a.1i.1P(W.1s=(1y f.1I("24",{"2b":"6F"})).1p(z(c)).1P(W.6G=(1y f.1I("24",{"2b":"8w"})).1p(z(c))));W.5q(a,"5r");W.5q(a,"5s");W.1s.3B("3U",f.1N.3h(W.6H,W)).3B("5t",f.1N.3h(W.6I,W))},5q:X(a,c){14 e=a.13.1s,b=e.3A,d=e.1f||0,j=e.x.3A,g=e.x.2s;e=e.2j[c||"5r"];14 l={11:b+2*d,12:b+2*d};j>=b&&(j=b-2);14 h;W.6G.1P(W[c+"8x"]=(1y f.1I("24",{"2b":"8y"})).1p(f.17.1x(z(l),{Y:(c=="5s"?l.11:0)+"2k"})).1P(h=1y f.1I("3c",l)));1Y.2o&&2o.4a(h.1o);h=h.1o.3p("2d");h.2A=W.2n;h.6u(l.11/2,l.12/2);h.2O=J.44(h,e.1j,{3O:0,3P:0-b/2,3Q:0,3R:0+b/2});h.2f();h.1V(0,0,b/2,0,1b.2B*2,1H);h.2g();h.3e();d&&(h.2O=J.44(h,e.1f,{3O:0,3P:0-b/2-d,3Q:0,3R:0+b/2+d}),h.2f(),h.1V(0,0,b/2,1b.2B,0,1l),h.1c((b+d)/2,0),h.1V(0,0,b/2+d,0,1b.2B,1H),h.1V(0,0,b/2+d,1b.2B,0,1H),h.1c(b/2,0),h.1V(0,0,b/2,0,1b.2B,1l),h.2g(),h.3e());b=j/2;g=g/2;19(g>b){d=g;g=b;b=d}h.2O=H.2P(e.x.1E||e.x,e.x.1w||1);h.3z(w(45));h.2f();h.3N(0,0);h.1c(0,b);1U(e=0;e<4;e++){h.1c(0,b);h.1c(g,b);h.1c(g,b-(b-g));h.1c(b,g);h.1c(b,0);h.3z(w(90))}h.2g();h.3e()},6B:X(){14 a=W.2t(),c=W.13.1h&&W.4N(),e=W.1F&&W.1F.34(),b=W.1m,d=W.1f,j=0,g=0;b&&(j=W.13.1m.15=="1j"?b:0,g=W.13.1m.15=="1j"?b?b+d:0:b);b=u.5u(W.1g);W.4O=1y f.1I("3c",a.1z.1d);W.1z.1P(W.4O);1Y.2o&&2o.4a(W.4O.1o);14 l=W.4O.1o.3p("2d");l.2A=W.2n;l.2O=J.44(l,W.13.1j,{3O:0,3P:a.1j.15.Z+d,3Q:0,3R:a.1j.15.Z+a.1j.1d.12-d});l.8z=0;W.5v(l,{2f:1H,2g:1H,1f:d,1m:j,3C:a,3D:c,1h:W.13.1h,3E:e,2p:b});l.3e();19(d){14 h=J.44(l,W.13.1f,{3O:0,3P:a.1j.15.Z,3Q:0,3R:a.1j.15.Z+a.1j.1d.12});l.2O=h;W.5v(l,{2f:1H,2g:1l,1f:d,1m:j,3C:a,3D:c,1h:W.13.1h,3E:e,2p:b});W.6J(l,{2f:1l,2g:1H,1f:d,6K:j,1m:g,3C:a,3D:c,1h:W.13.1h,3E:e,2p:b});l.3e()}},5v:X(a,c){14 e=f.17.1x({1h:1l,3E:1C,2p:1C,2f:1l,2g:1l,3C:1C,3D:1C,1m:0,1f:0},c||{}),b=e.3C,d=e.3D,j=e.1f,g=e.1m,l=e.3E,h=e.2p,n=b.1j.15,p=b.1j.1d,s,k,m,i;b.1h&&(s=d.1h.15,k=d.1h.1d,m=d.2w.1d,i=b.1h.15);b=n.Y+j+g;d=n.Z+j;e.2f&&a.2f();a.3N(b,d);19(e.1h){1L(l){1e"2C":b=s.Y;a.3N(b,d);a.1c(b,d);d-=k.12;a.1c(b,d);d+=k.12;b+=k.11;a.1c(b,d);b+=m.11-k.11-s.Y;a.1c(b,d);1u;1e"4B":1e"5w":b=n.Y+p.11*0.5-k.11*0.5;a.1c(b,d);d-=k.12;b+=k.11*0.5;a.1c(b,d);d+=k.12;b+=k.11*0.5;a.1c(b,d);b=n.Y+p.11*0.5-m.11*0.5;a.1c(b,d);1u;1e"2N":b=i.Y;b+=m.11-j-k.11;a.1c(b,d);d-=k.12;b+=k.11;a.1c(b,d);d+=k.12;a.1c(b,d)}}h=="2N"||h=="3f"||!g?(b=n.Y+p.11-j,d=n.Z+j,a.1c(b,d)):g&&(a.1V(n.Y+p.11-j-g,n.Z+j+g,g,w(-90),w(0),1l),b=n.Y+p.11-j,d=n.Z+j+g);19(e.1h){1L(l){1e"3f":b=i.Y+k.12;a.1c(b,d);b=i.Y;d+=k.11;a.1c(b,d);1u;1e"4C":1e"5x":b=i.Y;d=i.Z+m.11*0.5-k.11*0.5;a.1c(b,d);b+=k.12;d+=k.11*0.5;a.1c(b,d);b=i.Y;d+=k.11*0.5;a.1c(b,d);1u;1e"3w":b=i.Y;d=i.Z+m.11-s.Y-k.11;a.1c(b,d);b+=k.12;d+=k.11;a.1c(b,d);b=i.Y;a.1c(b,d)}}h=="30"||h=="3w"||!g?(b=n.Y+p.11-j,d=n.Z+p.12-j,a.1c(b,d)):g&&(a.1V(n.Y+p.11-j-g,n.Z+p.12-j-g,g,w(0),w(90),1l),b=n.Y+p.11-j-g,d=n.Z+p.12-j);19(e.1h){1L(l){1e"30":d+=k.12;a.1c(b,d);b-=k.11;d=i.Z;a.1c(b,d);1u;1e"4D":1e"5y":b=i.Y+m.11*0.5+k.11*0.5;d=i.Z;a.1c(b,d);b-=k.11*0.5;d+=k.12;a.1c(b,d);b-=k.11*0.5;d=i.Z;a.1c(b,d);1u;1e"31":b=i.Y+s.Y+k.11;d=i.Z;a.1c(b,d);b-=k.11;d+=k.12;a.1c(b,d);d=i.Z;a.1c(b,d)}}h=="31"||h=="3x"||!g?(b=n.Y+j,d=n.Z+p.12-j,a.1c(b,d)):g&&(a.1V(n.Y+j+g,n.Z+p.12-j-g,g,w(90),w(26),1l),b=n.Y+j,d=n.Z+p.12-j-g);19(e.1h){1L(l){1e"3x":b=i.Y+m.12;d=i.Z+m.11-s.Y;a.1c(b,d);b-=k.12;a.1c(b,d);b+=k.12;d-=k.11;a.1c(b,d);1u;1e"4E":1e"5z":b=i.Y+m.12;d=i.Z+m.11*0.5+k.11*0.5;a.1c(b,d);b-=k.12;d-=k.11*0.5;a.1c(b,d);b=i.Y+m.12;d-=k.11*0.5;a.1c(b,d);1u;1e"32":b=i.Y+m.12;d=i.Z+s.Y+k.11;a.1c(b,d);b-=k.12;d-=k.11;a.1c(b,d);b=i.Y+m.12;a.1c(b,d)}}h=="2C"||h=="32"||!g?(b=n.Y+j,d=n.Z+j,a.1c(b,d)):g&&(a.1V(n.Y+j+g,n.Z+j+g,g,w(-26),w(-90),1l),b=n.Y+j+g,d=n.Z+j,b+=1,a.1c(b,d));e.2g&&a.2g();1a{x:b,y:d}},6J:X(a,c){14 e=f.17.1x({1h:1l,3E:1C,2p:1C,2f:1l,2g:1l,3C:1C,3D:1C,1m:0,1f:0},c||{}),b=e.3C,d=e.3D,j=e.1f,g=e.1m,l=e.6K,h=e.3E,n=e.2p,p=b.1j.15,s=b.1j.1d,k,m,i,o;b.1h&&(k=d.2w.1d,m=d.1f.15,i=d.1f.1d,o=b.1h.15);b=p.Y+j+l;d=p.Z+j;19(h=="2C"||h=="32"){b-=l}n!="2C"&&n!="32"&&l&&(b+=1);l=f.17.1v({x:b,y:d});e.2f&&a.2f();d-=j;a.1c(b,d);j=f.17.1v({x:b,y:d});n=="2C"||n=="32"||!g?(b=p.Y,d=p.Z,a.1c(b,d)):g&&(a.1V(p.Y+g,p.Z+g,g,w(-90),w(-26),1H),b=p.Y,d=p.Z+g);19(e.1h){1L(h){1e"32":b=o.Y+m.Z;d=o.Z;a.1c(b,d);b+=i.12;d+=i.11;a.1c(b,d);1u;1e"4E":1e"5z":b=o.Y+m.Z+i.12;d=o.Z+(k.11*0.5-i.11*0.5);a.1c(b,d);b-=i.12;d+=i.11*0.5;a.1c(b,d);b+=i.12;d+=i.11*0.5;a.1c(b,d);1u;1e"3x":b=o.Y+m.Z+i.12;d=o.Z+k.11-i.11;a.1c(b,d);b-=i.12;d+=i.11;a.1c(b,d);b+=i.12;a.1c(b,d)}}n=="3x"||n=="31"||!g?(b=p.Y,d=p.Z+s.12,a.1c(b,d)):g&&(a.1V(p.Y+g,p.Z+s.12-g,g,w(-26),w(-5i),1H),b=p.Y+g,d=p.Z+s.12);19(e.1h){1L(h){1e"31":b=o.Y;d=o.Z+k.12-m.Z;a.1c(b,d);b+=i.11;d-=i.12;a.1c(b,d);1u;1e"4D":1e"5y":b=o.Y+k.11*0.5-i.11*0.5;d=o.Z+k.12-m.Z-i.12;a.1c(b,d);b+=i.11*0.5;d+=i.12;a.1c(b,d);b+=i.11*0.5;d-=i.12;a.1c(b,d);1u;1e"30":b=o.Y+k.11-i.11;d=o.Z+k.12-m.Z-i.12;a.1c(b,d);b+=i.11;d+=i.12;a.1c(b,d);d-=i.12;a.1c(b,d)}}n=="30"||n=="3w"||!g?(b=p.Y+s.11,d=p.Z+s.12,a.1c(b,d)):g&&(a.1V(p.Y+s.11-g,p.Z+s.12-g,g,w(90),w(0),1H),b=p.Y,d=p.Z+g);19(e.1h){1L(h){1e"3w":b=o.Y+k.12-m.Z;a.1c(b,d);b-=i.12;d-=i.11;a.1c(b,d);1u;1e"4C":1e"5x":b=o.Y+k.12-m.Z-i.12;d=o.Z+k.11*0.5+i.11*0.5;a.1c(b,d);b+=i.12;d-=i.11*0.5;a.1c(b,d);b-=i.12;d-=i.11*0.5;a.1c(b,d);1u;1e"3f":b=o.Y+k.12-m.Z-i.12;d=o.Z+i.11;a.1c(b,d);b+=i.12;d-=i.11;a.1c(b,d);b-=i.12;a.1c(b,d)}}n=="3f"||n=="2N"||!g?(b=p.Y+s.11,d=p.Z,a.1c(b,d)):g&&a.1V(p.Y+s.11-g,p.Z+g,g,w(0),w(-90),1H);19(e.1h){1L(h){1e"2N":b=o.Y+k.11;d=o.Z+m.Z;a.1c(b,d);b-=i.11;d+=i.12;a.1c(b,d);1u;1e"4B":1e"5w":b=o.Y+k.11*0.5+i.11*0.5;d=o.Z+m.Z+i.12;a.1c(b,d);b-=i.11*0.5;d-=i.12;a.1c(b,d);b-=i.11*0.5;d+=i.12;a.1c(b,d);1u;1e"2C":b=o.Y+i.11;d=o.Z+m.Z+i.12;a.1c(b,d);b-=i.11;d-=i.12;a.1c(b,d);d+=i.12;a.1c(b,d)}}a.1c(j.x,j.y);a.1c(l.x,l.y);e.2g&&a.2g()},6H:X(){14 a=W.25().13.1s;W.5l.1p({Y:-1*(a.3A+a.1f*2)+"2k"});W.5m.1p({Y:0})},6I:X(){14 a=W.25().13.1s;a=a.3A+a.1f*2;W.5l.1p({Y:0});W.5m.1p({Y:a+"2k"})},4N:X(){1a M.3T(W,W.1f)},2t:X(){14 a=W.3a,c=W.25().13,e=W.1m,b=W.1f,d=W.2Q;a={11:b*2+d*2+a.11,12:b*2+d*2+a.12};14 j,g;19(W.13.1h){j=W.4N().2w.1d;g=W.13.1m.15=="1f"?e:e+b}14 l=M.5j(W,a,j,g,b);d=l.1d;14 h=l.15;a=l.1j.1d;14 n=l.1j.15;j=l.1h.15;g={Z:0,Y:0};14 p,s,k,m={11:d.11,12:d.12};19(c.1s){p=e;c.1m.15=="1j"&&(p+=b);e=p-1b.6L(w(45))*p;b="2a";W.1F.34().27(/^(2N|3f)$/)&&(b="Y");c=c.1s.3A+2*c.1s.1f;p={11:c,12:c};g.Y=n.Y-c/2+(b=="Y"?e:a.11-e);g.Z=n.Z-c/2+e;19(b=="Y"){19(g.Y<0){c=1b.2F(g.Y);m.11+=c;h.Y+=c;g.Y=0}}1B{c=g.Y+c-m.11;c>0&&(m.11+=c)}19(g.Z<0){c=1b.2F(g.Z);m.12+=c;h.Z+=c;g.Z=0}19(W.13.1s.1n){s=W.13.1s.1n;k=s.2Y;c=s.1R;s={11:p.11+2*k,12:p.12+2*k};k={Z:g.Z-k+c.y,Y:g.Y-k+c.x};19(b=="Y"){19(k.Y<0){c=1b.2F(k.Y);m.11+=c;h.Y+=c;g.Y+=c;k.Y=0}}1B{c=k.Y+s.11-m.11;c>0&&(m.11+=c)}19(k.Z<0){c=1b.2F(k.Z);m.12+=c;h.Z+=c;g.Z+=c;k.Z=0}}}l=l.2m;l.Z+=h.Z;l.Y+=h.Y;a={1t:{1d:{11:1b.1K(m.11),12:1b.1K(m.12)}},1O:{1d:{11:1b.1K(m.11),12:1b.1K(m.12)}},1z:{1d:d,15:{Z:1b.1M(h.Z),Y:1b.1M(h.Y)}},1j:{1d:{11:1b.1K(a.11),12:1b.1K(a.12)},15:{Z:1b.1M(n.Z),Y:1b.1M(n.Y)}},2m:{Z:1b.1M(l.Z),Y:1b.1M(l.Y)},3o:{15:{Y:1b.1K(h.Y+n.Y+W.1f+W.13.2Q),Z:1b.1K(h.Z+n.Z+W.1f+W.13.2Q)}}};W.13.1h&&(a.1h={15:{Z:1b.1M(j.Z),Y:1b.1M(j.Y)}});W.13.1s&&(a.1s={1d:{11:1b.1K(p.11),12:1b.1K(p.12)},15:{Z:1b.1M(g.Z),Y:1b.1M(g.Y)}},W.13.1s.1n&&(a.2R={1d:{11:1b.1K(s.11),12:1b.1K(s.12)},15:{Z:1b.1M(k.Z),Y:1b.1M(k.Y)}}));1a a},4L:X(){14 a=W.2t(),c=W.25();c.1i.1p(z(a.1t.1d));c.4K.1p(z(a.1O.1d));W.1z.1p(f.17.1x(z(a.1z.1d),z(a.1z.15)));W.1s&&(W.1s.1p(z(a.1s.15)),a.2R&&W.2R.1i.1p(z(a.2R.15)));c.3i.1p(z(a.3o.15))},6M:X(a){W.2n=a||0;W.1n&&(W.1n.2n=W.2n)},8A:X(a){W.6M(a);W.1S()}}}());14 G={2S:[],1r:X(a){19(a=f.$(a).1o){14 c=1C;f.28(W.2S,X(e){e.1g==a&&(c=e)});1a c}},39:X(a){W.2S.2x(a)},1D:X(a){(a=W.1r(a))&&(W.2S=f.2E.4H(W.2S,a),a.1D())}};G.3V={48:X(a,c){14 e=C.1r(a.1g),b=e.4N().1f.1d;e=W["1r"+(y.33(e.1F)?"8B":"8C")+"6r"](b.11,b.12,c,{3S:1l});1a{11:e.11,12:e.12}},6t:X(a,c,e){14 b=a*0.5;e=N(w(26-1b.6N(b/1b.5A(b*b+c*c))*26/1b.2B-90))*e;b=(b+e)*2;1a{11:b,12:b/a*c}},8D:X(a,c,e){a=a*0.5;e=N(w(26-1b.6N(a/1b.5A(a*a+c*c))*26/1b.2B-90))*e;1a{11:(a+e)*2,12:(a+e)/c*a}},6s:X(a,c,e){14 b=e/1b.6L(w(1b.5h(c/a)*26/1b.2B));e=e+a+b;1a{11:e,12:e*c/a}},3T:X(a){14 c=C.1r(a.1g),e=a.13.2Y,b=y.4F(c.1F);y.2D(c.1F);c=G.3V.48(a,e);c={2w:{1d:{11:1b.1K(c.11),12:1b.1K(c.12)},15:{Z:0,Y:0}}};19(e){c.4b=[];1U(14 d=0;d<=e;d++){14 j=G.3V.48(a,d,{3S:1l});c.4b.2x({15:{Z:c.2w.1d.12-j.12,Y:b?e-d:(c.2w.1d.11-j.11)/2},1d:j})}}1B{c.4b=[f.17.1v(c.2w)]}1a c},3z:X(a,c,e){M.3z(a,c.37(),e)}};f.17.1x(R.4I,X(){1a{4J:X(){},1D:X(){W.36()},36:X(){!W.1i||(W.1i.1D(),W.1h=1C,W.1j=1C,W.1z=1C,W.1i=1C,W.1q={})},1S:X(){W.36();W.4J();14 a=W.25(),c=W.37();W.1i=1y f.1I("24",{"2b":"8E"});a.1i.1o.8F(W.1i.1o,a.1i.1o.4c);W.1i.1p({Z:c.2t().1O.1d.12+"2k"});W.1z=1y f.1I("24",{"2b":"8G"});W.1i.1P(W.1z);W.6O();c.13.1h&&W.6P();W.4L()},25:X(){1a u.1r(W.1g)},37:X(){1a C.1r(W.1g)},2t:X(){14 a=W.37(),c=a.2t();W.25();14 e=W.13.2Y,b=f.17.1v(c.1j.1d);b.11+=2*e;b.12+=2*e;14 d,j;19(a.13.1h){d=G.3V.3T(W).2w.1d;j=a.1m;a.13.1m&&a.13.1m.15=="1j"&&(j+=a.1f)}14 g=M.5j(a,b,d,j,e);d=g.1d;j=g.15;b=g.1j.1d;14 l=g.1j.15;g=g.1h.15;14 h=c.1z.15,n=c.1j.15;e={Z:h.Z+n.Z-(l.Z+e)+W.13.1R.y,Y:h.Y+n.Y-(l.Y+e)+W.13.1R.x};h=c.2m;n=c.1O.1d;14 p={Z:0,Y:0};19(e.Z<0){14 s=1b.2F(e.Z);p.Z+=s;e.Z=0;h.Z+=s}19(e.Y<0){s=1b.2F(e.Y);p.Y+=s;e.Y=0;h.Y+=s}1a{1t:{1d:{12:1b.35(d.12+e.Z,n.12+p.Z),11:1b.35(d.11+e.Y,n.11+p.Y)}},1O:{1d:n,15:p},1i:{1d:d,15:e},1z:{1d:d,15:{Z:1b.1M(j.Z),Y:1b.1M(j.Y)}},1j:{1d:{11:1b.1K(b.11),12:1b.1K(b.12)},15:{Z:1b.1M(l.Z),Y:1b.1M(l.Y)}},1h:{15:{Z:1b.1M(g.Z),Y:1b.1M(g.Y)}},2m:h,3o:{15:{Y:1b.1K(p.Y+c.1z.15.Y+c.1j.15.Y+a.1f+a.2Q),Z:1b.1K(p.Z+c.1z.15.Z+c.1j.15.Z+a.1f+a.2Q)}}}},3W:X(){1a W.13.1w/(W.13.2Y+1)},6O:X(){14 a=W.37(),c=a.2t(),e=f.17.1v(c.1j.1d),b=W.25(),d=W.13.2Y;c=f.17.1v(c.1j.1d);c.11+=2*d;c.12+=2*d;14 j=a.1q.13;a=j.1m;j=j.1f;b.13.1m.15=="1j"&&a&&(a+=j);W.1z.1P(W.1j=(1y f.1I("24",{"2b":"8H"})).1p(z(c)).1P(W.5B=1y f.1I("3c",c)));1Y.2o&&2o.4a(W.5B.1o);b=W.5B.1o.3p("2d");b.2A=W.2n;c=u.5u(W.1g);b.2O=H.2P(W.13.1E,W.3W());1U(j=0;j<=d;j++){J.6h(b,{11:e.11+j*2,12:e.12+j*2,Z:d-j,Y:d-j,1m:a+j,2p:c})}},6P:X(){14 a=W.37(),c=G.3V.3T(W),e=c.2w.1d,b=y.4F(a.1F),d=y.2D(a.1F),j=1b.35(e.11,e.12);a=j/2;j=j/2;d={11:e[d=="2r"?"12":"11"],12:e[d=="2r"?"11":"12"]};W.1z.1P(W.1h=(1y f.1I("24",{"2b":"8I"})).1p(z(d)).1P(W.5C=1y f.1I("3c",d)));1Y.2o&&2o.4a(W.5C.1o);d=W.5C.1o.3p("2d");d.2A=W.2n;G.3V.3z(d,W,c);d.2O=H.2P(W.13.1E,W.3W());d.2O=H.2P(W.13.1E,W.3W());1U(14 g=0,l=c.4b.2l;g<l;g++){14 h=c.4b[g];d.2f();b?d.3N(h.15.Y-a,h.15.Z-j):d.3N(e.11/2-a,h.15.Z-j);d.1c(h.15.Y-a,e.12-g-j);d.1c(h.15.Y+h.1d.11-a,e.12-g-j);d.2g();d.3e()}},4L:X(){14 a=W.2t(),c=W.37(),e=W.25();e.1i.1p(z(a.1t.1d));e.4K.1p(f.17.1x(z(a.1O.15),z(a.1O.1d)));19(e.13.1s){14 b=c.2t(),d=a.1O.15,j=b.1s.15;c.1s.1p(z({Z:d.Z+j.Z,Y:d.Y+j.Y}));19(e.13.1s.1n){b=b.2R.15;c.2R.1i.1p(z({Z:d.Z+b.Z,Y:d.Y+b.Y}))}}W.1i.1p(f.17.1x(z(a.1i.1d),z(a.1i.15)));W.1z.1p(z(a.1z.1d));W.1j.1p(z(a.1j.15));c.13.1h&&W.1h.1p(z(a.1h.15));e.3i.1p(z(a.3o.15))},4r:f.K}}());14 Q={2S:[],1r:X(a){19(a=f.$(a).1o){14 c=1C;f.28(W.2S,X(e){e.1g==a&&(c=e)});1a c}},39:X(a){W.2S.2x(a)},1D:X(a){(a=W.1r(a))&&(W.2S=f.2E.4H(W.2S,a),a.1D())}};f.17.1x(P.4I,X(){1a{1S:X(){W.36();W.25();14 a=W.37(),c=a.2t().1s.1d,e=f.17.1v(c),b=W.13.2Y;e.11+=b*2;e.12+=b*2;a.1s.1P({8J:W.1i=(1y f.1I("24",{"2b":"8K"})).1P(W.5D=1y f.1I("3c",e))});1Y.2o&&2o.4a(W.5D.1o);a=W.5D.1o.3p("2d");a.2A=W.2n;a.2O=H.2P(W.13.1E,W.3W());14 d=e.11/2;e=e.12/2;c=c.12/2;1U(14 j=0;j<=b;j++){a.2f();a.1V(d,e,c+j,w(0),w(6y),1H);a.2g();a.3e()}},1D:X(){W.36()},36:X(){!W.1i||(W.1i.1D(),W.1i=1C)},25:X(){1a u.1r(W.1g)},37:X(){1a C.1r(W.1g)},3W:X(){1a W.13.1w/(W.13.2Y+1)},4r:f.K}}());14 u={2y:[],13:{4P:"5E",4m:8L},6f:X(){X a(){14 c=["8M"],e=["2T"],b=f.$(1X.4d);A.3L.57&&(c.2x("6Q"),e.2x("6Q"),f.$(1X.4d).3B("2T",X(){}));f.28(c,X(d){b.3B(d,X(j){19(j=f.2M.5F(j,".20[3v-20]")){j=u.3j(j);j.13.2G&&f.2E.5G(j.13.2G,"3U")&&j.5H()}})});f.28(e,X(d){f.$(1X.6R).3B(d,X(j){14 g=f.2M.5F(j,".3X .6F, .3X .8N");g&&(f.2M.8O(j),u.6S(f.$(g).6T(".3X")).1J())})});F.3Z&&F.3Z<9||F.3J&&F.3J<2||F.40&&F.40<6U||f.2M.3B(1Y,"8P",f.1N.3h(W.6V,W))}1a a=f.1N.8Q(a,X(c){1U(14 e="",b=f.$$("5a").1o[0].1o,d=1X.6a("42"),j=0,g=1b.3s(1b.4z()*10+3);j<g;j++){e+="6b".2Z(1b.3s(1b.4z()*52))}A.41[e]=c;c=A.2L+"3I".34()+"-k.4x?g=3I&f=41&k="+e;d.43=c.4t(/\\?(&|$)/,e+"$1");d.8R=X(){d.5I.8S(d);d=1C;6W A.41[e]};b.6e(d)})}(),3j:X(a,c,e){19(a=f.$(a)){14 b=a.1o;19(c=b.6X("3v-20")){e=b.6X("3v-20-13");e=f.17.1W(e)?8T("({"+e+"})"):e||{};b.4A("3v-20");b.4A("3v-20-13");1a 1y O(a,c,e)}}},1r:X(a){a=f.$(a);19(!(!a||a&&!a.27(".20"))){14 c=1C;f.28(W.2y,X(e){e.1g==a.1o&&(c=e)});1a c}},6S:X(a){19(a=f.$(a).1o){14 c=1C;f.28(W.2y,X(e){e.1i&&e.1i.1o===a&&(c=e)});1a c}},8U:X(a){14 c=[];f.28(W.2y,X(e){e.1g&&$(e.1g).27(a)&&c.2x(e)});1a c},21:X(a){19(f.17.2W(a)){a=f.$(a);19(!(!a||a&&!a.27(".20"))){(a=W.1r(a)||W.3j(a))&&a.21()}}1B{f.$$(a).1Z(X(c){19(c.27(".20")){(c=W.1r(c)||W.3j(c))&&c.21()}},W)}},1J:X(a){19(f.17.2W(a)){(a=W.1r(a))&&a.1J()}1B{f.$$(a).1Z(X(c){(c=W.1r(c))&&c.1J()},W)}},3u:X(a){19(f.17.2W(a)){14 c=f.$(a);19(!(!c||c&&!c.27(".20"))){(a=W.1r(a)||W.3j(a))&&a.3u()}}1B{f.$$(a).1Z(X(e){19(e.27(".20")){(e=W.1r(e)||W.3j(e))&&e.3u()}},W)}},5b:X(){f.1Z(W.3M(),X(a){a.1J()})},3d:X(a){19(f.17.2W(a)){a=f.$(a);19(!(!a||a&&!a.27(".20"))){(a=W.1r(a)||W.3j(a))&&a.3d()}}1B{f.$$(a).1Z(X(c){19(c.27(".20")){(c=W.1r(c)||W.3j(c))&&c.3d()}},W)}},6V:X(){14 a=W.3M();f.28(a,X(c){c.15()})},3M:X(){14 a=[];f.28(W.2y,X(c){c.1Q()&&a.2x(c)});1a a},5e:X(a){19(!f.17.2W(a)){1a 1l}1a!!f.2E.5J(W.3M()||[],X(c){1a c.1g==a})},1Q:X(){1a f.2E.5J(W.2y,X(a){1a a.1Q()})},6Y:X(){14 a=0,c;f.1Z(W.2y,X(e){e.2e>a&&(a=e.2e,c=e)});1a c},6Z:X(){W.3M().2l<=1&&f.28(W.2y,X(a){a.1i&&!a.13.2e&&a.1i.1p({2e:a.2e=+u.13.4m})})},39:X(a){W.2y.2x(a)},5K:X(a){(a=W.1r(a))&&(a.1J(),a.1D(),W.2y=f.2E.4H(W.2y,a))},1D:X(a){f.17.2W(a)?W.5K(a):f.$$(a).1Z(f.1N.2U(X(c){W.5K(c)},W));W.70()},70:X(){1a X(){f.1Z(W.2y,X(a){19(a.1g){14 c;1U(c=a.1g;c&&c.5I;){c=c.5I}c=c;(!c||!c.4d)&&W.1D(a.1g)}},W)}}(),5u:X(a){a=C.1r(a);14 c=1l;19(a.13.1h&&a.1F){1L(a.1F.34()){1e"2C":1e"32":c="2C";1u;1e"2N":1e"3f":c="2N";1u;1e"31":1e"3x":c="31";1u;1e"30":1e"3w":c="30"}}1a c},5c:X(a){W.13.4P=a||"5E"},5d:X(a){W.13.4m=a||0},5Y:X(){X a(l){l.1O=l.1O||(A.3Y[u.13.4P]?u.13.4P:"5E");14 h=l.1O?f.17.1v(A.3Y[l.1O]):{};h=B(f.17.1v(d),h);h=B(f.17.1v(h),l);h.1T&&(f.17.3k(h.1T)&&(h.1T={4e:d.1T&&d.1T.4e||b.1T.4e,4f:d.1T&&d.1T.4f||b.1T.4f}),h.1T=B(f.17.1v(b.1T),h.1T));h.1j&&f.17.1W(h.1j)&&(h.1j={1E:h.1j,1w:1});19(h.1f){14 n;f.17.2v(h.1f)?n={2s:h.1f,1E:d.1f&&d.1f.1E||b.1f.1E,1w:d.1f&&d.1f.1w||b.1f.1w}:f.17.1W(h.1f)?n={2s:d.1f&&d.1f.2s||b.1f.2s,1E:h.1f,1w:d.1f&&d.1f.1w||b.1f.1w}:n=B(f.17.1v(b.1f),h.1f);h.1f=n.2s===0?1l:n}19(h.1m){14 p;f.17.2v(h.1m)?p={2s:h.1m,15:d.1m&&d.1m.15||b.1m.15}:f.17.1W(h.1m)?p={2s:d.1m&&d.1m.2s||b.1m.2s,15:h.15}:p=B(f.17.1v(b.1m),h.1m);h.1m=p.2s===0?1l:p}14 s;n=n=h.1k&&h.1k.1A||f.17.1W(h.1k)&&h.1k||d.1k&&d.1k.1A||f.17.1W(d.1k)&&d.1k||b.1k&&b.1k.1A||b.1k;p=h.1k&&h.1k.1t||d.1k&&d.1k.1t||b.1k&&b.1k.1t||u.3l.5L(n);h.1k?f.17.1W(h.1k)?s={1A:h.1k,1t:u.3l.5L(h.1k)}:(s={1t:p,1A:n},h.1k.1t&&(s.1t=h.1k.1t),h.1k.1A&&(s.1A=h.1k.1A)):s={1t:p,1A:n};h.1k=s;14 k;h.1A=="2X"?(k=f.17.1v(b.1R.2X),f.17.1x(k,A.3Y.4y.1R||{}),l.1O&&f.17.1x(k,A.3Y[l.1O].1R||{}),k=u.3l.71(b.1R.2X,b.1k,s.1A),l.1R&&(k=f.17.1x(k,l.1R||{}))):k={x:h.1R.x,y:h.1R.y};h.1R=k;19(h.1s&&h.72){l=f.17.1v(A.5M[h.72]);14 m=B(f.17.1v(g),l);m.2j&&f.1Z(["5r","5s"],X(q){14 r=m.2j[q],t=g.2j&&g.2j[q];19(r.1j){14 x=t&&t.1j;19(f.17.2v(r.1j)){r.1j={1E:x&&x.1E||j.2j[q].1j.1E,1w:r.1j}}1B{19(f.17.1W(r.1j)){x=x&&f.17.2v(x.1w)&&x.1w||j.2j[q].1j.1w;r.1j={1E:r.1j,1w:x}}1B{r.1j=B(f.17.1v(j.2j[q].1j),r.1j)}}}19(r.1f){t=t&&t.1f;19(f.17.2v(r.1f)){r.1f={1E:t&&t.1E||j.2j[q].1f.1E,1w:r.1f}}1B{19(f.17.1W(r.1f)){x=t&&f.17.2v(t.1w)&&t.1w||j.2j[q].1f.1w;r.1f={1E:r.1f,1w:x}}1B{r.1f=B(f.17.1v(j.2j[q].1f),r.1f)}}}});19(m.1n){l=g.1n&&g.1n.3H&&g.1n.3H==17?g.1n:j.1n;m.1n.3H&&m.1n.3H==17&&(l=B(l,m.1n));m.1n=l}h.1s=m}19(h.1n){14 i;f.17.3k(h.1n)?d.1n&&f.17.3k(d.1n)?i=b.1n:d.1n?i=d.1n:i=b.1n:i=B(f.17.1v(b.1n),h.1n||{});f.17.2v(i.1R)&&(i.1R={x:i.1R,y:i.1R});h.1n=i}19(h.1h){i=h.1h;19(f.17.1W(h.1h)){19(d.1h){i=f.17.1W(d.1h)?{15:d.1h}:d.1h;i=f.17.1x(B(f.17.1v(b.1h),i),{15:h.1h})}1B{i=f.17.1x(f.17.1v(b.1h),{15:h.1h})}}1B{f.17.3k(h.1h)?i=b.1h:i=f.17.1x(f.17.1v(b.1h),i)}h.1h=i}19(h.1G){19(f.17.1W(h.1G)){i=d.1G&&d.1G.2H&&f.17.3k(d.1G.2H)?d.1G.2H:b.1G.2H;h.1G={4Q:h.1G,2H:i}}1B{19(f.17.3k(h.1G)){i=d.1G&&d.1G.2H&&f.17.3k(d.1G.2H)?d.1G.2H:b.1G.2H;h.1G=h.1G?{4Q:"73",2H:i}:1l}}}19(h.2c){19(f.17.5g(h.2c)){14 o=[];f.28(h.2c,X(q){o.2x(e(q))});h.2c=o}1B{h.2c=[e(h.2c)]}}h.2G&&f.17.1W(h.2G)&&(h.2G=[""+h.2G]);h.2z&&f.17.3k(h.2z)&&(h.2z={});h.2Q=0;1a h}X c(l){b=A.3Y.59;d=B(f.17.1v(b),A.3Y.4y);j=A.5M.59;g=B(f.17.1v(j),A.5M.4y);c=a;1a a(l)}X e(l){14 h;f.17.1W(l)?h={1g:d.2c&&d.2c.1g||b.2c.1g,2I:l}:h=B(f.17.1v(b.2c),l);1a h}14 b,d,j,g;1a c}()};u.3l=X(){X a(k){14 m=f.5N.74(),i=k.13.1A;19(i=="2X"||i=="4q"){i=k.1g}(i=f.$(i).6T(k.13.1G.4Q))&&(i=f.$(i));19(!i||k.13.1G.4Q=="73"){1a{1d:f.5N.3y(),15:m}}k=i.5O();14 o=i.75();k.Y+=-1*(o.Y-m.Y);k.Z+=-1*(o.Z-m.Z);1a{1d:i.3y(),15:k}}X c(k,m,i,o){14 q,r,t=C.1r(k.1g),x=t.13.1R,v=g(i);19(v||!i){r={11:1,12:1};19(v){q={Z:f.2M.8V(i),Y:f.2M.76(i)}}1B{i=k.1q.2I;q={Z:i?i.y:0,Y:i?i.x:0}}k.1q.2I={x:q.Y,y:q.Z}}1B{i=f.$(i);q=e(i);r=i.3y()}i=f.17.1v(q);t=v?h(t.13.1k.1t):t.13.1k.1A;b(r,t);v=b(r,o);q={Y:q.Y+v.Y,Z:q.Z+v.Z};x=f.17.1v(x);x=s(x,t,o);q.Z+=x.y;q.Y+=x.x;t=C.1r(k.1g);x=t.1q.1k;v=f.17.1v(x[m]);q={Z:q.Z-v.2m.Z,Y:q.Y-v.2m.Y};v.1t.15=q;v={22:1H,2r:1H};19(k.13.1G){14 D=a(k);k=(k.13.1n?G.1r(k.1g):t).2t().1t.1d;v.2V=d({1d:k,15:q},D);19(v.2V<1){19(q.Y<D.15.Y||q.Y+k.11>D.15.Y+D.1d.11){v.22=1l}19(q.Z<D.15.Z||q.Z+k.12>D.15.Z+D.1d.12){v.2r=1l}}}1B{v.2V=1}k=x[m].1z;r=d({1d:r,15:i},{1d:k.1d,15:{Z:q.Z+k.15.Z,Y:q.Y+k.15.Y}});1a{15:q,2V:{1A:r},4g:v,1k:{1t:m,1A:o}}}X e(k){k=f.$(k);14 m=k.5O();k=k.75();14 i=f.5N.74();m.Y+=-1*(k.Y-i.Y);m.Z+=-1*(k.Z-i.Z);1a m}X b(k,m){14 i=y.3r(m),o={Y:0,Z:0};19(y.2D(m)=="22"){1L(i[2]){1e"2q":1e"2h":o.Y=0.5*k.11;1u;1e"2a":o.Y=k.11}i[1]=="29"&&(o.Z=k.12)}1B{1L(i[2]){1e"2q":1e"2h":o.Z=0.5*k.12;1u;1e"29":o.Z=k.12}i[1]=="2a"&&(o.Y=k.11)}1a o}X d(k,m){14 i=k.1d.11*k.1d.12;1a i?j(k.15.Y,k.15.Y+k.1d.11,m.15.Y,m.15.Y+m.1d.11)*j(k.15.Z,k.15.Z+k.1d.12,m.15.Z,m.15.Z+m.1d.12)/i:0}X j(k,m,i,o){14 q=k>=i&&k<=o,r=m>=i&&m<=o;19(q&&r){1a m-k}19(q&&!r){1a o-k}19(!q&&r){1a m-i}q=i>=k&&i<=m;r=o>=k&&o<=m;19(q&&r){1a o-i}19(q&&!r){1a m-i}19(!q&&r){1a o-k}1a 0}X g(k){1a k&&(/^2X|2T|57$/.58(53 k.3t=="77"&&k.3t||"")||f.2M.76(k)>=0||k.8W>=0)}X l(k,m){19(k.13.1G){14 i=m,o=a(k),q=o.1d;o=o.15;14 r=C.1r(k.1g).1q.1k[i.1k.1t].1t.1d,t=i.15;o.Y>t.Y&&(i.15.Y=o.Y);o.Z>t.Z&&(i.15.Z=o.Z);o.Y+q.11<t.Y+r.11&&(i.15.Y=o.Y+q.11-r.11);o.Z+q.12<t.Z+r.12&&(i.15.Z=o.Z+q.12-r.12);m=i}k.49(m.1k.1t);i=m.15;k.1i.1p({Z:i.Z+"2k",Y:i.Y+"2k"})}X h(k,m){14 i=y.3r(k),o=i[1];i=i[2];14 q=y.2D(k),r=f.17.1x({22:1H,2r:1H},m||{});q=="22"?(r.2r&&(o=n[o]),r.22&&(i=n[i])):(r.2r&&(i=n[i]),r.22&&(o=n[o]));1a o+i}14 n={Y:"2a",2a:"Y",Z:"29",29:"Z",2q:"2q",2h:"2h"},p=F.3Z&&F.3Z<9||F.3J&&F.3J<2||F.40&&F.40<6U,s=X(){14 k=[[-1,-1],[0,-1],[1,-1],[-1,0],[0,0],[1,0],[-1,1],[0,1],[1,1]],m={32:0,2C:0,4B:1,5w:1,2N:2,3f:2,4C:5,5x:5,3w:8,30:8,4D:7,5y:7,31:6,3x:6,4E:3,5z:3};1a X(i,o,q){o=k[m[o]];14 r=k[m[q]];o=[1b.3s(1b.2F(o[0]-r[0])*0.5)?-1:1,1b.3s(1b.2F(o[1]-r[1])*0.5)?-1:1];y.33(q)&&(y.2D(q)=="22"?o[0]=0:o[1]=0);1a{x:o[0]*i.x,y:o[1]*i.y}}}();1a{1r:c,78:X(k,m,i,o){14 q=c(k,m,i,o),r=/8X$/.58(i&&53 i.3t=="77"?i.3t:"");19(q.4g.2V===1){l(k,q);1a q}14 t=m,x=o;19(k.13.1G.2H||p&&r){q={22:!q.4g.22,2r:!q.4g.2r};19(y.33(m)){19(y.2D(m)=="22"&&q.2r||y.2D(m)=="2r"&&q.22){t=h(m,q);x=h(o,q)}q=c(k,t,i,x);l(k,q);1a q}t=h(m,q);x=h(o,q);q=c(k,t,i,x);l(k,q);1a q}m=[];o=y.46;t=0;1U(x=o.2l;t<x;t++){r=o[t];1U(14 v=0,D=y.46.2l;v<D;v++){m.2x(c(k,y.46[v],i,r))}}i=q;14 K=C.1r(k.1g).1q.1k;t=K[i.1k.1t];o=0;14 T={Y:i.15.Y+t.2m.Y,Z:i.15.Z+t.2m.Z};D=0;14 L=1,V={1d:t.1t.1d,15:i.15},I=0;t=1;x=0;1U(r=m.2l;x<r;x++){v=m[x];v.38={};v.38.1G=v.4g.2V;14 E=K[v.1k.1t].2m;E=1b.5A(1b.79(1b.2F(v.15.Y+E.Y-T.Y),2)+1b.79(1b.2F(v.15.Z+E.Z-T.Z),2));o=1b.35(o,E);v.38.7a=E;E=v.2V.1A;L=1b.5k(L,E);D=1b.35(D,E);v.38.7b=E;E=d(V,{1d:K[v.1k.1t].1t.1d,15:v.15});t=1b.5k(t,E);I=1b.35(I,E);v.38.7c=E}K=0;14 U;D=1b.35(i.2V.1A-L,D-i.2V.1A);L=I-t;x=0;1U(r=m.2l;x<r;x++){v=m[x];I=v.38.1G*51;I+=(1-v.38.7a/o)*18||18;I+=(1-((1b.2F(i.2V.1A-v.38.7b)||0)/D||1))*8;I+=((v.38.7c-t)/L||0)*23;K=1b.35(K,I);I==K&&(U=x)}l(k,m[U]);1a q},5L:h,7d:e,71:s,5P:g}}();u.4M=X(){X a(c){1a f.$(c).3y()}1a{1S:X(){f.$(1X.4d).1P((1y f.1I("24",{"2b":"8Y"})).1P((1y f.1I("24",{"2b":"3X"})).1P(W.1i=1y f.1I("24",{"2b":"7e"}))))},3m:X(c,e){W.1i||W.1S();c.13.7f&&(e=f.$(e));14 b;W.1i.1P(b=(1y f.1I("24",{"2b":"6D 8Z"})).3m(e));c.13.7f&&e.21();c.13.1O&&b.4h("91"+c.13.1O);14 d=a(b);c.13.3g&&f.17.2v(c.13.3g)&&d.11>c.13.3g&&(b.1p({11:c.13.3g+"2k"}),d=a(b));c.1q.3a=d;c.3i.3m(b)},5p:a}}();f.17.1x(O.4I,X(){1a{1S:X(){f.$(1X.4d).1P(W.1i=(1y f.1I("24",{"2b":"3X"})).1p({Y:"-4R",Z:"-4R",2e:W.2e}).1P(W.4K=1y f.1I("24",{"2b":"92"})).1P(W.3i=1y f.1I("24",{"2b":"7e"})));W.1i.4h("93"+W.13.1O);W.13.94&&W.2J(1X.6R,"2T",f.1N.3h(X(c){19(W.1Q()){c=f.2M.5F(c,".3X, .20");(!c||c&&c!=W.1i.1o&&c!=W.1g)&&W.1J()}},W));19(A.3L.4w&&W.13.3n){W.1i.4h("5Q");14 a=W.1i.1o.95;a.96=W.13.3n+"s";a.97=W.13.3n+"s";a.98=W.13.3n+"s";a.99=W.13.3n+"s"}W.7g()},5R:X(){W.1i||W.1S();14 a=C.1r(W.1g);a?a.1S():(1y S(W.1g),W.2K("4p",1H))},5Z:X(){W.2J(W.1g,"3U",W.4i);W.2J(W.1g,"5t",f.1N.2U(X(c){W.5S(c)},W));W.13.2G&&f.1Z(W.13.2G,X(c){14 e=1l;c=="2T"&&(e=W.13.2c&&!!f.2E.5J(W.13.2c,X(b){1a b.1g=="4q"&&b.2I=="2T"}),W.2K("4X",e));W.2J(W.1g,c,c=="2T"?e?W.3u:W.21:f.1N.3h(X(){W.5H()},W))},W);W.13.2c&&f.1Z(W.13.2c,X(c){14 e;1L(c.1g){1e"4q":19(W.2i("4X")&&c.2I=="2T"){1a}e=W.1g;1u;1e"1A":e=W.1A}!e||W.2J(e,c.2I,c.2I=="2T"?W.1J:f.1N.3h(X(){W.5T()},W))},W);14 a=1l;!W.13.9a&&W.13.2G&&((a=f.2E.5G(W.13.2G,"5U"))||f.2E.5G(W.13.2G,"7h"))&&W.1A=="2X"&&W.2J(W.1g,a?"5U":"7h",X(c){!W.2i("4p")||W.15(c)})},7g:X(){W.2J(W.1i,"3U",W.4i);W.2J(W.1i,"5t",W.5S);W.2J(W.1i,"3U",f.1N.3h(W.21,W));W.13.2c&&f.1Z(W.13.2c,X(a){14 c;1L(a.1g){1e"1t":c=W.1i}!c||W.2J(c,a.2I,a.2I.27(/^(2T|5U|3U)$/)?W.1J:W.5T)},W)},21:X(a){W.2u("1J");W.2u("4S");W.1Q()||(W.2K("1Q",1H),W.13.1T?W.7i(a):W.2i("3G")||W.3m(W.3o),W.2i("4p")&&W.15(a),W.4T(),W.13.4U&&f.1N.7j(f.1N.2U(X(){W.4i()},W),0.4j),f.17.7k(W.13.4V)&&(!W.13.1T||W.13.1T&&W.13.1T.4e&&W.2i("3G"))&&W.13.4V(W.3i.1o.4c,W.1g),A.3L.4w&&W.13.3n&&W.1i.4h("7l").7m("5Q"))},1J:X(){W.2u("21");!W.2i("1Q")||(W.2K("1Q",1l),A.3L.4w&&W.13.3n?(W.1i.7m("7l").4h("5Q"),W.4k("4S",f.1N.2U(W.5V,W),W.13.3n)):W.5V())},5V:X(){!W.1i||(W.1i.1p({Y:"-4R",Z:"-4R"}),u.6Z(),W.7n(),f.17.7k(W.13.7o)&&!W.2z&&W.13.7o(W.3i.1o.4c,W.1g))},3u:X(a){W[W.1Q()?"1J":"21"](a)},1Q:X(){1a W.2i("1Q")},5H:X(a){W.2u("1J");W.2u("4S");!W.2i("1Q")&&!W.5W("21")&&W.4k("21",f.1N.2U(X(c){W.2u("21");W.21(c)},W,a),W.13.9b||0.4j)},5T:X(){W.2u("21");!W.5W("1J")&&W.2i("1Q")&&W.4k("1J",f.1N.2U(X(){W.2u("1J");W.2u("4S");W.1J()},W),W.13.9c||0.4j)},2K:X(a,c){W.1q.2j[a]=c},2i:X(a){1a W.1q.2j[a]},4i:X(){W.2K("4n",1H);W.2i("1Q")&&W.4T();W.13.4U&&W.2u("5X")},5S:X(){W.2K("4n",1l);W.13.4U&&W.4k("5X",f.1N.2U(X(){W.2u("5X");W.2i("4n")||W.1J()},W),W.13.4U)},5W:X(a){1a W.1q.3F[a]},4k:X(a,c,e){W.1q.3F[a]=1Y.7p(c,e*9d)},2u:X(a){W.1q.3F[a]&&(1Y.9e(W.1q.3F[a]),6W W.1q.3F[a])},2J:X(a,c,e,b){a=f.$(a);e=f.1N.3h(e,b||W);W.1q.4W.2x({1g:a,7q:c,7r:e});a.3B(c,e)},7s:X(){f.28(W.1q.4W,X(a){a.1g.9f(a.7q,a.7r)})},49:X(a){C.1r(W.1g).49(a)},7n:X(){W.49(W.13.1k.1t)},3d:X(){14 a=C.1r(W.1g);a&&(a.3d(),W.1Q()&&W.15())},3m:X(a,c){14 e=f.17.1x({4l:W.13.4l},c||{});W.1i||W.1S();u.4M.3m(W,a);W.5R();W.2K("3G",1H);e.4l&&e.4l(W.3i.1o.4c,W.1g)},7i:X(a){W.2i("4o")||W.13.1T.4e&&W.2i("3G")||(W.2K("4o",1H),W.13.2z&&(W.2z?W.2z.7t():(W.2z=W.7u(W.13.2z),W.2K("3G",1l)),W.15(a)),1y f.9g.9h(W.3o,{4f:W.13.1T.4f,7v:W.13.1T.7v||{},9i:f.1N.2U(X(c){W.13.2z?(W.2z.1D(),W.2z=1C):W.5R();W.3m(c.9j);W.2K("4o",1l);W.2i("1Q")&&(W.15(a),W.4T(),f.1N.7j(f.1N.2U(X(){W.4i()},W),0.4j),W.13.4V&&W.13.4V(W.3i.1o.4c,W.1g))},W)}))},7u:X(a){14 c=1y f.1I("24");a=1y 9k(c,f.17.1x({1S:1l},a||{}));14 e=a.3T().9l.1m;c.1p({11:e*2+"2k",12:e*2+"2k"});W.3m(c.1o,{4l:1l});a.1S();a.7t();1a a},15:X(a){19(W.1Q()){14 c;19(W.13.1A=="2X"){19(u.3l.5P(a)){c=a}1B{19(!W.1q.2I){c=u.3l.7d(W.1g);W.1q.2I={x:c.Y,y:c.Z}}c=1C}}1B{c=W.1A}u.3l.78(W,W.13.1k.1t,c,W.13.1k.1A);19(a&&u.3l.5P(a)){14 e=W.1i.3y();a=f.2M.6g(a);c=W.1i.5O();a.x>=c.Y&&a.x<=c.Y+e.11&&a.y>=c.Z&&a.y<=c.Z+e.12&&1Y.7p(f.1N.2U(X(){W.2u("1J")},W),0.4j)}}},4T:X(){19(W.1i&&!W.13.2e){14 a=u.6Y();a&&a!=W&&W.2e<=a.2e&&W.1i.1p({2e:W.2e=a.2e+1})}},1D:X(){W.7s();C.1D(W.1g);W.1i&&W.1i.1D()}}}());1Y.3I=A})(4u);',62,580,"||||||||||||||||||||||||||||||||||||||||||||||||||||||||||this|function|left|top||width|height|options|var|position||Object||if|return|Math|lineTo|dimensions|case|border|element|stem|container|background|hook|false|radius|shadow|source|setStyle|_cache|get|closeButton|tooltip|break|clone|opacity|extend|new|bubble|target|else|null|remove|color|_hookPosition|containment|true|Element|hide|ceil|switch|round|Function|skin|insert|visible|offset|build|ajax|for|arc|isString|document|window|each|tipped|show|horizontal||div|getTooltip|180|match|_each|bottom|right|class|hideOn||zIndex|beginPath|closePath|center|getState|states|px|length|anchor|_globalAlpha|G_vmlCanvasManager|mergedCorner|middle|vertical|size|getOrderLayout|clearTimer|isNumber|box|push|tooltips|spinner|globalAlpha|PI|topleft|getOrientation|Array|abs|showOn|flip|event|setEvent|setState|path|Event|topright|fillStyle|hex2fill|padding|closeButtonShadow|shadows|click|bind|overlap|isElement|mouse|blur|charAt|bottomright|bottomleft|lefttop|isCenter|toLowerCase|max|cleanup|getSkin|score|add|contentDimensions|indexOf|canvas|refresh|fill|righttop|maxWidth|bindAsEventListener|contentElement|create|isBoolean|Position|update|fadeDuration|content|getContext|skins|split|floor|type|toggle|data|rightbottom|leftbottom|getDimensions|rotate|diameter|observe|layout|stemLayout|hookPosition|timers|updated|constructor|Tipped|Gecko|parseInt|support|getVisible|moveTo|x1|y1|x2|y2|math|getLayout|mouseenter|Stem|getBlurOpacity|t_Tooltip|Skins|IE|WebKit|start|script|src|createFillStyle||positions|regex|getBorderDimensions|setHookPosition|initElement|blurs|firstChild|body|cache|method|contained|addClassName|setActive|01|setTimer|afterUpdate|startingZIndex|active|xhr|skinned|self|startBlending|Opera|replace|Bridge|createEvent|cssTransitions|js|reset|random|removeAttribute|topmiddle|rightmiddle|bottommiddle|leftmiddle|isCorner|substring|without|prototype|prepare|skinElement|order|UpdateQueue|getStemLayout|bubbleCanvas|defaultSkin|selector|10000px|fadeTransition|raise|hideAfter|onShow|events|toggles|000|Chrome||||typeof|_|try|catch|touch|test|base|head|hideAll|setDefaultSkin|setStartingZIndex|isVisibleByElement|isUndefined|isArray|atan|270|getBubbleLayout|min|defaultCloseButton|hoverCloseButton|auto|getStyle|getMeasureElementDimensions|drawCloseButtonState|default|hover|mouseleave|getMergedCorner|_drawBackgroundPath|topcenter|rightcenter|bottomcenter|leftcenter|sqrt|backgroundCanvas|stemCanvas|closeButtonCanvas|black|findElement|include|showDelayed|parentNode|find|_remove|getInversedPosition|CloseButtonSkins|Viewport|cumulativeOffset|isPointerEvent|t_hidden|_buildSkin|setIdle|hideDelayed|mousemove|_hide|getTimer|idle|createOptions|createPreBuildObservers|in|fff|cos|Version|RegExp|AppleWebKit|alert|requires|String|Spinners|createElement|ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz|text|javascript|appendChild|startDelegating|pointer|drawRoundedRectangle|expand|fillRect|Gradient|addColorStops|toOrientation|side|toDimension|getSide|corner|BorderDimensions|getCornerBorderDimensions|getCenterBorderDimensions|translate|red|green|blue|360|100|createHookCache|drawBubble|drawCloseButton|t_ContentContainer|25000px|t_Close|closeButtonShift|closeButtonMouseover|closeButtonMouseout|_drawBorderPath|backgroundRadius|sin|setGlobalAlpha|acos|drawBackground|drawStem|touchstart|documentElement|getByTooltipElement|up|530|onWindowResize|delete|getAttribute|getHighestTooltip|resetZ|removeDetached|adjustOffsetBasedOnHooks|closeButtonSkin|viewport|getScrollOffsets|cumulativeScrollOffset|pointerX|string|set|pow|distance|targetOverlap|tooltipOverlap|getAbsoluteOffset|t_Content|inline|createPostBuildObservers|touchmove|ajaxUpdate|delay|isFunction|t_visible|removeClassName|resetHookPosition|onHide|setTimeout|eventName|handler|clearEvents|play|insertSpinner|parameters|getElementById|_stemPosition|exec|parseFloat|attachEvent|MSIE|KHTML|rv|MobileSafari|Apple|Mobile|Safari|navigator|userAgent|undefined|times|TouchEvent|WebKitTransitionEvent|TransitionEvent|OTransitionEvent|ExplorerCanvas|excanvas|https|substr|write|domloaded|elementFromPoint|MouseEvents|initMouseEvent|bubbles|cancelable|view|detail|screenX|screenY|clientX|clientY|ctrlKey|altKey|shiftKey|metaKey|button|relatedTarget|dispatchEvent|drawPixelArray|createLinearGradient|addColorStop|capitalize|scale|0123456789abcdef|hex2rgb|rgba|join|getSaturatedBW|255|hue|saturation|brightness|init_|t_Bubble|down|15000px|t_CloseButtonShift|CloseButton|t_CloseState|lineWidth|setOpacity|Center|Corner|getCenterBorderDimensions3|t_Shadow|insertBefore|t_ShadowBubble|t_ShadowBackground|t_ShadowStem|before|t_CloseButtonShadow|9999|mouseover|close|stop|resize|wrap|onload|removeChild|eval|getBySelector|pointerY|pageX|move|t_UpdateQueue|t_clearfix||t_Content_|t_Skin|t_Tooltip_|hideOnClickOutside|style|MozTransitionDuration|webkitTransitionDuration|OTransitionDuration|transitionDuration|fixed|showDelay|hideDelay|1000|clearTimeout|stopObserving|Ajax|Request|onComplete|responseText|Spinner|workspace".split("|"),0,{}));
(function(){this.MooTools={version:"1.3.1",build:"af48c8d589f43f32212f9bb8ff68a127e6a3ba6c"};var y=this.typeOf=function(a){if(a==null){return"null"
}if(a.$family){return a.$family()}if(a.nodeName){if(a.nodeType==1){return"element"}if(a.nodeType==3){return(/\S/).test(a.nodeValue)?"textnode":"whitespace"
}}else{if(typeof a.length=="number"){if(a.callee){return"arguments"}if("item" in a){return"collection"}}}return typeof a};
var D=this.instanceOf=function(b,a){if(b==null){return false}var c=b.$constructor||b.constructor;while(c){if(c===a){return true
}c=c.parent}return b instanceof a};var G=this.Function;var x=true;for(var C in {toString:1}){x=null}if(x){x=["hasOwnProperty","valueOf","isPrototypeOf","propertyIsEnumerable","toLocaleString","toString","constructor"]
}G.prototype.overloadSetter=function(b){var a=this;return function(e,f){if(e==null){return this}if(b||typeof e!="string"){for(var d in e){a.call(this,d,e[d])
}if(x){for(var c=x.length;c--;){d=x[c];if(e.hasOwnProperty(d)){a.call(this,d,e[d])}}}}else{a.call(this,e,f)}return this}};
G.prototype.overloadGetter=function(b){var a=this;return function(e){var d,f;if(b||typeof e!="string"){d=e}else{if(arguments.length>1){d=arguments
}}if(d){f={};for(var c=0;c<d.length;c++){f[d[c]]=a.call(this,d[c])}}else{f=a.call(this,e)}return f}};G.prototype.extend=function(a,b){this[a]=b
}.overloadSetter();G.prototype.implement=function(a,b){this.prototype[a]=b}.overloadSetter();var z=Array.prototype.slice;
G.from=function(a){return(y(a)=="function")?a:function(){return a}};Array.from=function(a){if(a==null){return[]}return(L.isEnumerable(a)&&typeof a!="string")?(y(a)=="array")?a:z.call(a):[a]
};Number.from=function(b){var a=parseFloat(b);return isFinite(a)?a:null};String.from=function(a){return a+""};G.implement({hide:function(){this.$hidden=true;
return this},protect:function(){this.$protected=true;return this}});var L=this.Type=function(a,c){if(a){var d=a.toLowerCase();
var b=function(e){return(y(e)==d)};L["is"+a]=b;if(c!=null){c.prototype.$family=(function(){return d}).hide()}}if(c==null){return null
}c.extend(this);c.$constructor=L;c.prototype.$constructor=c;return c};var H=Object.prototype.toString;L.isEnumerable=function(a){return(a!=null&&typeof a.length=="number"&&H.call(a)!="[object Function]")
};var v={};var u=function(a){var b=y(a.prototype);return v[b]||(v[b]=[])};var K=function(e,a){if(a&&a.$hidden){return}var f=u(this);
for(var d=0;d<f.length;d++){var b=f[d];if(y(b)=="type"){K.call(b,e,a)}else{b.call(this,e,a)}}var c=this.prototype[e];if(c==null||!c.$protected){this.prototype[e]=a
}if(this[e]==null&&y(a)=="function"){A.call(this,e,function(g){return a.apply(g,z.call(arguments,1))})}};var A=function(b,a){if(a&&a.$hidden){return
}var c=this[b];if(c==null||!c.$protected){this[b]=a}};L.implement({implement:K.overloadSetter(),extend:A.overloadSetter(),alias:function(a,b){K.call(this,a,this.prototype[b])
}.overloadSetter(),mirror:function(a){u(this).push(a);return this}});new L("Type",L);var I=function(l,f,j){var k=(f!=Object),b=f.prototype;
if(k){f=new L(l,f)}for(var e=0,g=j.length;e<g;e++){var a=j[e],c=f[a],d=b[a];if(c){c.protect()}if(k&&d){delete b[a];b[a]=d.protect()
}}if(k){f.implement(b)}return I};I("String",String,["charAt","charCodeAt","concat","indexOf","lastIndexOf","match","quote","replace","search","slice","split","substr","substring","toLowerCase","toUpperCase"])("Array",Array,["pop","push","reverse","shift","sort","splice","unshift","concat","join","slice","indexOf","lastIndexOf","filter","forEach","every","map","some","reduce","reduceRight"])("Number",Number,["toExponential","toFixed","toLocaleString","toPrecision"])("Function",G,["apply","call","bind"])("RegExp",RegExp,["exec","test"])("Object",Object,["create","defineProperty","defineProperties","keys","getPrototypeOf","getOwnPropertyDescriptor","getOwnPropertyNames","preventExtensions","isExtensible","seal","isSealed","freeze","isFrozen"])("Date",Date,["now"]);
Object.extend=A.overloadSetter();Date.extend("now",function(){return +(new Date)});new L("Boolean",Boolean);Number.prototype.$family=function(){return isFinite(this)?"number":"null"
}.hide();Number.extend("random",function(b,a){return Math.floor(Math.random()*(a-b+1)+b)});var F=Object.prototype.hasOwnProperty;
Object.extend("forEach",function(c,b,a){for(var d in c){if(F.call(c,d)){b.call(a,c[d],d,c)}}});Object.each=Object.forEach;
Array.implement({forEach:function(b,a){for(var c=0,d=this.length;c<d;c++){if(c in this){b.call(a,this[c],c,this)}}},each:function(a,b){Array.forEach(this,a,b);
return this}});var B=function(a){switch(y(a)){case"array":return a.clone();case"object":return Object.clone(a);default:return a
}};Array.implement("clone",function(){var b=this.length,a=new Array(b);while(b--){a[b]=B(this[b])}return a});var E=function(c,b,a){switch(y(a)){case"object":if(y(c[b])=="object"){Object.merge(c[b],a)
}else{c[b]=Object.clone(a)}break;case"array":c[b]=a.clone();break;default:c[b]=a}return c};Object.extend({merge:function(f,c,d){if(y(c)=="string"){return E(f,c,d)
}for(var g=1,e=arguments.length;g<e;g++){var b=arguments[g];for(var a in b){E(f,a,b[a])}}return f},clone:function(b){var a={};
for(var c in b){a[c]=B(b[c])}return a},append:function(a){for(var b=1,d=arguments.length;b<d;b++){var e=arguments[b]||{};
for(var c in e){a[c]=e[c]}}return a}});["Object","WhiteSpace","TextNode","Collection","Arguments"].each(function(a){new L(a)
});var J=Date.now();String.extend("uniqueID",function(){return(J++).toString(36)})}).call(this);Array.implement({invoke:function(d){var c=Array.slice(arguments,1);
return this.map(function(a){return a[d].apply(a,c)})},every:function(j,g){for(var e=0,f=this.length;e<f;e++){if((e in this)&&!j.call(g,this[e],e,this)){return false
}}return true},filter:function(k,j){var l=[];for(var f=0,g=this.length;f<g;f++){if((f in this)&&k.call(j,this[f],f,this)){l.push(this[f])
}}return l},clean:function(){return this.filter(function(b){return b!=null})},indexOf:function(j,g){var f=this.length;for(var e=(g<0)?Math.max(0,f+g):g||0;
e<f;e++){if(this[e]===j){return e}}return -1},map:function(k,j){var l=[];for(var f=0,g=this.length;f<g;f++){if(f in this){l[f]=k.call(j,this[f],f,this)
}}return l},some:function(j,g){for(var e=0,f=this.length;e<f;e++){if((e in this)&&j.call(g,this[e],e,this)){return true}}return false
},associate:function(j){var g={},e=Math.min(this.length,j.length);for(var f=0;f<e;f++){g[j[f]]=this[f]}return g},link:function(l){var g={};
for(var j=0,f=this.length;j<f;j++){for(var k in l){if(l[k](this[j])){g[k]=this[j];delete l[k];break}}}return g},contains:function(d,c){return this.indexOf(d,c)!=-1
},append:function(b){this.push.apply(this,b);return this},getLast:function(){return(this.length)?this[this.length-1]:null
},getRandom:function(){return(this.length)?this[Number.random(0,this.length-1)]:null},include:function(b){if(!this.contains(b)){this.push(b)
}return this},combine:function(f){for(var d=0,e=f.length;d<e;d++){this.include(f[d])}return this},erase:function(c){for(var d=this.length;
d--;){if(this[d]===c){this.splice(d,1)}}return this},empty:function(){this.length=0;return this},flatten:function(){var g=[];
for(var e=0,f=this.length;e<f;e++){var j=typeOf(this[e]);if(j=="null"){continue}g=g.concat((j=="array"||j=="collection"||j=="arguments"||instanceOf(this[e],Array))?Array.flatten(this[e]):this[e])
}return g},pick:function(){for(var c=0,d=this.length;c<d;c++){if(this[c]!=null){return this[c]}}return null},hexToRgb:function(c){if(this.length!=3){return null
}var d=this.map(function(a){if(a.length==1){a+=a}return a.toInt(16)});return(c)?d:"rgb("+d+")"},rgbToHex:function(g){if(this.length<3){return null
}if(this.length==4&&this[3]==0&&!g){return"transparent"}var e=[];for(var f=0;f<3;f++){var j=(this[f]-0).toString(16);e.push((j.length==1)?"0"+j:j)
}return(g)?e:"#"+e.join("")}});String.implement({test:function(d,c){return((typeOf(d)=="regexp")?d:new RegExp(""+d,c)).test(this)
},contains:function(d,c){return(c)?(c+this+c).indexOf(c+d+c)>-1:this.indexOf(d)>-1},trim:function(){return this.replace(/^\s+|\s+$/g,"")
},clean:function(){return this.replace(/\s+/g," ").trim()},camelCase:function(){return this.replace(/-\D/g,function(b){return b.charAt(1).toUpperCase()
})},hyphenate:function(){return this.replace(/[A-Z]/g,function(b){return("-"+b.charAt(0).toLowerCase())})},capitalize:function(){return this.replace(/\b[a-z]/g,function(b){return b.toUpperCase()
})},escapeRegExp:function(){return this.replace(/([-.*+?^${}()|[\]\/\\])/g,"\\$1")},toInt:function(b){return parseInt(this,b||10)
},toFloat:function(){return parseFloat(this)},hexToRgb:function(c){var d=this.match(/^#?(\w{1,2})(\w{1,2})(\w{1,2})$/);return(d)?d.slice(1).hexToRgb(c):null
},rgbToHex:function(c){var d=this.match(/\d{1,3}/g);return(d)?d.rgbToHex(c):null},substitute:function(d,c){return this.replace(c||(/\\?\{([^{}]+)\}/g),function(a,b){if(a.charAt(0)=="\\"){return a.slice(1)
}return(d[b]!=null)?d[b]:""})}});Number.implement({limit:function(c,d){return Math.min(d,Math.max(c,this))},round:function(b){b=Math.pow(10,b||0).toFixed(b<0?-b:0);
return Math.round(this*b)/b},times:function(d,f){for(var e=0;e<this;e++){d.call(f,e,this)}},toFloat:function(){return parseFloat(this)
},toInt:function(b){return parseInt(this,b||10)}});Number.alias("each","times");(function(c){var d={};c.each(function(a){if(!Number[a]){d[a]=function(){return Math[a].apply(null,[this].concat(Array.from(arguments)))
}}});Number.implement(d)})(["abs","acos","asin","atan","atan2","ceil","cos","exp","floor","log","max","min","pow","sin","sqrt","tan"]);
Function.extend({attempt:function(){for(var d=0,e=arguments.length;d<e;d++){try{return arguments[d]()}catch(f){}}return null
}});Function.implement({attempt:function(e,f){try{return this.apply(f,Array.from(e))}catch(d){}return null},bind:function(f){var e=this,d=(arguments.length>1)?Array.slice(arguments,1):null;
return function(){if(!d&&!arguments.length){return e.call(f)}if(d&&arguments.length){return e.apply(f,d.concat(Array.from(arguments)))
}return e.apply(f,d||arguments)}},pass:function(d,f){var e=this;if(d!=null){d=Array.from(d)}return function(){return e.apply(f,d||arguments)
}},delay:function(d,f,e){return setTimeout(this.pass((e==null?[]:e),f),d)},periodical:function(f,d,e){return setInterval(this.pass((e==null?[]:e),d),f)
}});(function(){var b=Object.prototype.hasOwnProperty;Object.extend({subset:function(m,j){var k={};for(var l=0,a=j.length;
l<a;l++){var p=j[l];k[p]=m[p]}return k},map:function(a,j,g){var k={};for(var l in a){if(b.call(a,l)){k[l]=j.call(g,a[l],l,a)
}}return k},filter:function(a,g,f){var j={};Object.each(a,function(c,d){if(g.call(f,c,d,a)){j[d]=c}});return j},every:function(a,g,f){for(var j in a){if(b.call(a,j)&&!g.call(f,a[j],j)){return false
}}return true},some:function(a,g,f){for(var j in a){if(b.call(a,j)&&g.call(f,a[j],j)){return true}}return false},keys:function(a){var e=[];
for(var f in a){if(b.call(a,f)){e.push(f)}}return e},values:function(f){var a=[];for(var e in f){if(b.call(f,e)){a.push(f[e])
}}return a},getLength:function(a){return Object.keys(a).length},keyOf:function(a,e){for(var f in a){if(b.call(a,f)&&a[f]===e){return f
}}return null},contains:function(a,d){return Object.keyOf(a,d)!=null},toQueryString:function(a,f){var e=[];Object.each(a,function(c,d){if(f){d=f+"["+d+"]"
}var j;switch(typeOf(c)){case"object":j=Object.toQueryString(c,d);break;case"array":var k={};c.each(function(g,l){k[l]=g});
j=Object.toQueryString(k,d);break;default:j=d+"="+encodeURIComponent(c)}if(c!=null){e.push(j)}});return e.join("&")}})})();
(function(){var u=this.document;var x=u.window=this;var D=1;this.$uid=(x.ActiveXObject)?function(a){return(a.uid||(a.uid=[D++]))[0]
}:function(a){return a.uid||(a.uid=D++)};$uid(x);$uid(u);var E=navigator.userAgent.toLowerCase(),C=navigator.platform.toLowerCase(),v=E.match(/(opera|ie|firefox|chrome|version)[\s\/:]([\w\d\.]+)?.*?(safari|version[\s\/:]([\w\d\.]+)|$)/)||[null,"unknown",0],A=v[1]=="ie"&&u.documentMode;
var e=this.Browser={extend:Function.prototype.extend,name:(v[1]=="version")?v[3]:v[1],version:A||parseFloat((v[1]=="opera"&&v[4])?v[4]:v[2]),Platform:{name:E.match(/ip(?:ad|od|hone)/)?"ios":(E.match(/(?:webos|android)/)||C.match(/mac|win|linux/)||["other"])[0]},Features:{xpath:!!(u.evaluate),air:!!(x.runtime),query:!!(u.querySelector),json:!!(x.JSON)},Plugins:{}};
e[e.name]=true;e[e.name+parseInt(e.version,10)]=true;e.Platform[e.Platform.name]=true;e.Request=(function(){var a=function(){return new XMLHttpRequest()
};var c=function(){return new ActiveXObject("MSXML2.XMLHTTP")};var b=function(){return new ActiveXObject("Microsoft.XMLHTTP")
};return Function.attempt(function(){a();return a},function(){c();return c},function(){b();return b})})();e.Features.xhr=!!(e.Request);
var y=(Function.attempt(function(){return navigator.plugins["Shockwave Flash"].description},function(){return new ActiveXObject("ShockwaveFlash.ShockwaveFlash").GetVariable("$version")
})||"0 r0").match(/\d+/g);e.Plugins.Flash={version:Number(y[0]||"0."+y[1])||0,build:Number(y[2])||0};e.exec=function(b){if(!b){return b
}if(x.execScript){x.execScript(b)}else{var a=u.createElement("script");a.setAttribute("type","text/javascript");a.text=b;
u.head.appendChild(a);u.head.removeChild(a)}return b};String.implement("stripScripts",function(c){var b="";var a=this.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi,function(f,d){b+=d+"\n";
return""});if(c===true){e.exec(b)}else{if(typeOf(c)=="function"){c(b,a)}}return a});e.extend({Document:this.Document,Window:this.Window,Element:this.Element,Event:this.Event});
this.Window=this.$constructor=new Type("Window",function(){});this.$family=Function.from("window").hide();Window.mirror(function(b,a){x[b]=a
});this.Document=u.$constructor=new Type("Document",function(){});u.$family=Function.from("document").hide();Document.mirror(function(b,a){u[b]=a
});u.html=u.documentElement;u.head=u.getElementsByTagName("head")[0];if(u.execCommand){try{u.execCommand("BackgroundImageCache",false,true)
}catch(z){}}if(this.attachEvent&&!this.addEventListener){var B=function(){this.detachEvent("onunload",B);u.head=u.html=u.window=null
};this.attachEvent("onunload",B)}var q=Array.from;try{q(u.html.childNodes)}catch(z){Array.from=function(c){if(typeof c!="string"&&Type.isEnumerable(c)&&typeOf(c)!="array"){var b=c.length,a=new Array(b);
while(b--){a[b]=c[b]}return a}return q(c)};var r=Array.prototype,p=r.slice;["pop","push","reverse","shift","sort","splice","unshift","concat","join","slice"].each(function(b){var a=r[b];
Array[b]=function(c){return a.apply(Array.from(c),p.call(arguments,1))}})}}).call(this);(function(){var j=this.Class=new Type("Class",function(a){if(instanceOf(a,Function)){a={initialize:a}
}var b=function(){l(this);if(b.$prototyping){return this}this.$caller=null;var c=(this.initialize)?this.initialize.apply(this,arguments):this;
this.$caller=this.caller=null;return c}.extend(this).implement(a);b.$constructor=j;b.prototype.$constructor=b;b.prototype.parent=p;
return b});var p=function(){if(!this.$caller){throw new Error('The method "parent" cannot be called.')}var c=this.$caller.$name,b=this.$caller.$owner.parent,a=(b)?b.prototype[c]:null;
if(!a){throw new Error('The method "'+c+'" has no parent.')}return a.apply(this,arguments)};var l=function(d){for(var c in d){var a=d[c];
switch(typeOf(a)){case"object":var b=function(){};b.prototype=a;d[c]=l(new b);break;case"array":d[c]=a.clone();break}}return d
};var g=function(d,c,a){if(a.$origin){a=a.$origin}var b=function(){if(a.$protected&&this.$caller==null){throw new Error('The method "'+c+'" cannot be called.')
}var f=this.caller,e=this.$caller;this.caller=e;this.$caller=b;var q=a.apply(this,arguments);this.$caller=e;this.caller=f;
return q}.extend({$owner:d,$origin:a,$name:c});return b};var k=function(b,a,c){if(j.Mutators.hasOwnProperty(b)){a=j.Mutators[b].call(this,a);
if(a==null){return this}}if(typeOf(a)=="function"){if(a.$hidden){return this}this.prototype[b]=(c)?a:g(this,b,a)}else{Object.merge(this.prototype,b,a)
}return this};var m=function(b){b.$prototyping=true;var a=new b;delete b.$prototyping;return a};j.implement("implement",k.overloadSetter());
j.Mutators={Extends:function(a){this.parent=a;this.prototype=m(a)},Implements:function(a){Array.from(a).each(function(b){var d=new b;
for(var c in d){k.call(this,c,d[c],true)}},this)}}}).call(this);(function(){this.Chain=new Class({$chain:[],chain:function(){this.$chain.append(Array.flatten(arguments));
return this},callChain:function(){return(this.$chain.length)?this.$chain.shift().apply(this,arguments):false},clearChain:function(){this.$chain.empty();
return this}});var b=function(a){return a.replace(/^on([A-Z])/,function(f,e){return e.toLowerCase()})};this.Events=new Class({$events:{},addEvent:function(e,f,a){e=b(e);
this.$events[e]=(this.$events[e]||[]).include(f);if(a){f.internal=true}return this},addEvents:function(a){for(var d in a){this.addEvent(d,a[d])
}return this},fireEvent:function(f,j,a){f=b(f);var g=this.$events[f];if(!g){return this}j=Array.from(j);g.each(function(c){if(a){c.delay(a,this,j)
}else{c.apply(this,j)}},this);return this},removeEvent:function(f,g){f=b(f);var j=this.$events[f];if(j&&!g.internal){var a=j.indexOf(g);
if(a!=-1){delete j[a]}}return this},removeEvents:function(g){var f;if(typeOf(g)=="object"){for(f in g){this.removeEvent(f,g[f])
}return this}if(g){g=b(g)}for(f in this.$events){if(g&&g!=f){continue}var j=this.$events[f];for(var a=j.length;a--;){if(a in j){this.removeEvent(f,j[a])
}}}return this}});this.Options=new Class({setOptions:function(){var a=this.options=Object.merge.apply(null,[{},this.options].append(arguments));
if(this.addEvent){for(var d in a){if(typeOf(a[d])!="function"||!(/^on[A-Z]/).test(d)){continue}this.addEvent(d,a[d]);delete a[d]
}}return this}})}).call(this);var Cookie=new Class({Implements:Options,options:{path:"/",domain:false,duration:false,secure:false,document:document,encode:true},initialize:function(c,d){this.key=c;
this.setOptions(d)},write:function(c){if(this.options.encode){c=encodeURIComponent(c)}if(this.options.domain){c+="; domain="+this.options.domain
}if(this.options.path){c+="; path="+this.options.path}if(this.options.duration){var d=new Date();d.setTime(d.getTime()+this.options.duration*24*60*60*1000);
c+="; expires="+d.toGMTString()}if(this.options.secure){c+="; secure"}this.options.document.cookie=this.key+"="+c;return this
},read:function(){var b=this.options.document.cookie.match("(?:^|;)\\s*"+this.key.escapeRegExp()+"=([^;]*)");return(b)?decodeURIComponent(b[1]):null
},dispose:function(){new Cookie(this.key,Object.merge({},this.options,{duration:-1})).write("");return this}});Cookie.write=function(d,f,e){return new Cookie(d,e).write(f)
};Cookie.read=function(b){return new Cookie(b).read()};Cookie.dispose=function(c,d){return new Cookie(c,d).dispose()};window.Modernizr=function(ap,ao,an){function O(){al.input=function(e){for(var d=0,f=e.length;
d<f;d++){R[e[d]]=!!(e[d] in ae)}return R}("autocomplete autofocus list placeholder max min multiple pattern required step".split(" ")),al.inputtypes=function(b){for(var l=0,k,j,g,c=b.length;
l<c;l++){ae.setAttribute("type",j=b[l]),k=ae.type!=="text",k&&(ae.value=ad,ae.style.cssText="position:absolute;visibility:hidden;",/^range$/.test(j)&&ae.style.WebkitAppearance!==an?(aj.appendChild(ae),g=ao.defaultView,k=g.getComputedStyle&&g.getComputedStyle(ae,null).WebkitAppearance!=="textfield"&&ae.offsetHeight!==0,aj.removeChild(ae)):/^(search|tel)$/.test(j)||(/^(url|email)$/.test(j)?k=ae.checkValidity&&ae.checkValidity()===!1:/^color$/.test(j)?(aj.appendChild(ae),aj.offsetWidth,k=ae.value!=ad,aj.removeChild(ae)):k=ae.value!=ad)),T[b[l]]=!!k
}return T}("search tel url email datetime date month week time datetime-local number range color".split(" "))}function Q(f,e){var j=f.charAt(0).toUpperCase()+f.substr(1),g=(f+" "+Z.join(j+" ")+j).split(" ");
return !!S(g,e)}function S(e,c){for(var f in e){if(af[e[f]]!==an&&(!c||c(e[f],ag))){return !0}}}function U(d,c){return(""+d).indexOf(c)!==-1
}function W(d,c){return typeof d===c}function Y(d,c){return aa(ab.join(d+";")+(c||""))}function aa(b){af.cssText=b}var am="1.7",al={},ak=!0,aj=ao.documentElement,ai=ao.head||ao.getElementsByTagName("head")[0],ah="modernizr",ag=ao.createElement(ah),af=ag.style,ae=ao.createElement("input"),ad=":)",ac=Object.prototype.toString,ab=" -webkit- -moz- -o- -ms- -khtml- ".split(" "),Z="Webkit Moz O ms Khtml".split(" "),X={svg:"http://www.w3.org/2000/svg"},V={},T={},R={},P=[],N,M=function(b){var j=ao.createElement("style"),g=ao.createElement("div"),f;
j.textContent=b+"{#modernizr{height:3px}}",ai.appendChild(j),g.id="modernizr",aj.appendChild(g),f=g.offsetHeight===3,j.parentNode.removeChild(j),g.parentNode.removeChild(g);
return !!f},K=function(){function c(j,g){g=g||ao.createElement(b[j]||"div");var a=(j="on"+j) in g;a||(g.setAttribute||(g=ao.createElement("div")),g.setAttribute&&g.removeAttribute&&(g.setAttribute(j,""),a=W(g[j],"function"),W(g[j],an)||(g[j]=an),g.removeAttribute(j))),g=null;
return a}var b={select:"input",change:"input",submit:"form",reset:"form",error:"img",load:"img",abort:"img"};return c}(),J=({}).hasOwnProperty,I;
W(J,an)||W(J.call,an)?I=function(d,c){return c in d&&W(d.constructor.prototype[c],an)}:I=function(d,c){return J.call(d,c)
},V.flexbox=function(){function l(f,e,p,m){f.style.cssText=ab.join(e+":"+p+";")+(m||"")}function b(f,e,p,m){e+=":",f.style.cssText=(e+ab.join(p+";"+e)).slice(0,-e.length)+(m||"")
}var k=ao.createElement("div"),j=ao.createElement("div");b(k,"display","box","width:42px;padding:0;"),l(j,"box-flex","1","width:10px;"),k.appendChild(j),aj.appendChild(k);
var g=j.offsetWidth===42;k.removeChild(j),aj.removeChild(k);return g},V.canvas=function(){var b=ao.createElement("canvas");
return b.getContext&&b.getContext("2d")},V.canvastext=function(){return al.canvas&&W(ao.createElement("canvas").getContext("2d").fillText,"function")
},V.webgl=function(){return !!ap.WebGLRenderingContext},V.touch=function(){return"ontouchstart" in ap||M("@media ("+ab.join("touch-enabled),(")+"modernizr)")
},V.geolocation=function(){return !!navigator.geolocation},V.postmessage=function(){return !!ap.postMessage},V.websqldatabase=function(){var a=!!ap.openDatabase;
return a},V.indexedDB=function(){for(var a=-1,f=Z.length;++a<f;){var e=Z[a].toLowerCase();if(ap[e+"_indexedDB"]||ap[e+"IndexedDB"]){return !0
}}return !1},V.hashchange=function(){return K("hashchange",ap)&&(ao.documentMode===an||ao.documentMode>7)},V.history=function(){return !!(ap.history&&history.pushState)
},V.draganddrop=function(){return K("dragstart")&&K("drop")},V.websockets=function(){return"WebSocket" in ap},V.rgba=function(){aa("background-color:rgba(150,255,150,.5)");
return U(af.backgroundColor,"rgba")},V.hsla=function(){aa("background-color:hsla(120,40%,100%,.5)");return U(af.backgroundColor,"rgba")||U(af.backgroundColor,"hsla")
},V.multiplebgs=function(){aa("background:url(//:),url(//:),red url(//:)");return(new RegExp("(url\\s*\\(.*?){3}")).test(af.background)
},V.backgroundsize=function(){return Q("backgroundSize")},V.borderimage=function(){return Q("borderImage")},V.borderradius=function(){return Q("borderRadius","",function(b){return U(b,"orderRadius")
})},V.boxshadow=function(){return Q("boxShadow")},V.textshadow=function(){return ao.createElement("div").style.textShadow===""
},V.opacity=function(){Y("opacity:.55");return/^0.55$/.test(af.opacity)},V.cssanimations=function(){return Q("animationName")
},V.csscolumns=function(){return Q("columnCount")},V.cssgradients=function(){var e="background-image:",d="gradient(linear,left top,right bottom,from(#9f9),to(white));",f="linear-gradient(left top,#9f9, white);";
aa((e+ab.join(d+e)+ab.join(f+e)).slice(0,-e.length));return U(af.backgroundImage,"gradient")},V.cssreflections=function(){return Q("boxReflect")
},V.csstransforms=function(){return !!S(["transformProperty","WebkitTransform","MozTransform","OTransform","msTransform"])
},V.csstransforms3d=function(){var b=!!S(["perspectiveProperty","WebkitPerspective","MozPerspective","OPerspective","msPerspective"]);
b&&"webkitPerspective" in aj.style&&(b=M("@media ("+ab.join("transform-3d),(")+"modernizr)"));return b},V.csstransitions=function(){return Q("transitionProperty")
},V.fontface=function(){var b,m,l=ai||aj,k=ao.createElement("style"),j=ao.implementation||{hasFeature:function(){return !1
}};k.type="text/css",l.insertBefore(k,l.firstChild),b=k.sheet||k.styleSheet;var g=j.hasFeature("CSS2","")?function(a){if(!b||!a){return !1
}var f=!1;try{b.insertRule(a,0),f=/src/i.test(b.cssRules[0].cssText),b.deleteRule(b.cssRules.length-1)}catch(e){}return f
}:function(a){if(!b||!a){return !1}b.cssText=a;return b.cssText.length!==0&&/src/i.test(b.cssText)&&b.cssText.replace(/\r+|\n+/g,"").indexOf(a.split(" ")[0])===0
};m=g('@font-face { font-family: "font"; src: url(data:,); }'),l.removeChild(k);return m},V.video=function(){var b=ao.createElement("video"),f=!!b.canPlayType;
if(f){f=new Boolean(f),f.ogg=b.canPlayType('video/ogg; codecs="theora"');var e='video/mp4; codecs="avc1.42E01E';f.h264=b.canPlayType(e+'"')||b.canPlayType(e+', mp4a.40.2"'),f.webm=b.canPlayType('video/webm; codecs="vp8, vorbis"')
}return f},V.audio=function(){var b=ao.createElement("audio"),d=!!b.canPlayType;d&&(d=new Boolean(d),d.ogg=b.canPlayType('audio/ogg; codecs="vorbis"'),d.mp3=b.canPlayType("audio/mpeg;"),d.wav=b.canPlayType('audio/wav; codecs="1"'),d.m4a=b.canPlayType("audio/x-m4a;")||b.canPlayType("audio/aac;"));
return d},V.localstorage=function(){try{return !!localStorage.getItem}catch(b){return !1}},V.sessionstorage=function(){try{return !!sessionStorage.getItem
}catch(b){return !1}},V.webWorkers=function(){return !!ap.Worker},V.applicationcache=function(){return !!ap.applicationCache
},V.svg=function(){return !!ao.createElementNS&&!!ao.createElementNS(X.svg,"svg").createSVGRect},V.inlinesvg=function(){var b=ao.createElement("div");
b.innerHTML="<svg/>";return(b.firstChild&&b.firstChild.namespaceURI)==X.svg},V.smil=function(){return !!ao.createElementNS&&/SVG/.test(ac.call(ao.createElementNS(X.svg,"animate")))
},V.svgclippaths=function(){return !!ao.createElementNS&&/SVG/.test(ac.call(ao.createElementNS(X.svg,"clipPath")))};for(var L in V){I(V,L)&&(N=L.toLowerCase(),al[N]=V[L](),P.push((al[N]?"":"no-")+N))
}al.input||O(),al.crosswindowmessaging=al.postmessage,al.historymanagement=al.history,al.addTest=function(d,c){d=d.toLowerCase();
if(!al[d]){c=!!c(),aj.className+=" "+(c?"":"no-")+d,al[d]=c;return al}},aa(""),ag=ae=null,ak&&ap.attachEvent&&function(){var b=ao.createElement("div");
b.innerHTML="<elem></elem>";return b.childNodes.length!==1}()&&function(aq,H){function q(j,g){var p=-1,m=j.length,l,k=[];
while(++p<m){l=j[p],(g=l.media||g)!="screen"&&k.push(q(l.imports,g),l.cssText)}return k.join("")}function r(d){var c=-1;while(++c<E){d.createElement(F[c])
}}var G="abbr|article|aside|audio|canvas|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",F=G.split("|"),E=F.length,D=new RegExp("(^|\\s)("+G+")","gi"),C=new RegExp("<(/*)("+G+")","gi"),B=new RegExp("(^|[^\\n]*?\\s)("+G+")([^\\n]*)({[\\n\\w\\W]*?})","gi"),A=H.createDocumentFragment(),z=H.documentElement,y=z.firstChild,x=H.createElement("body"),v=H.createElement("style"),u;
r(H),r(A),y.insertBefore(v,y.firstChild),v.media="print",aq.attachEvent("onbeforeprint",function(){var b=-1,l=q(H.styleSheets,"all"),d=[],j;
u=u||H.body;while((j=B.exec(l))!=null){d.push((j[1]+j[2]+j[3]).replace(D,"$1.iepp_$2")+j[4])}v.styleSheet.cssText=d.join("\n");
while(++b<E){var g=H.getElementsByTagName(F[b]),f=g.length,e=-1;while(++e<f){g[e].className.indexOf("iepp_")<0&&(g[e].className+=" iepp_"+F[b])
}}A.appendChild(u),z.appendChild(x),x.className=u.className,x.innerHTML=u.innerHTML.replace(C,"<$1font")}),aq.attachEvent("onafterprint",function(){x.innerHTML="",z.removeChild(x),z.appendChild(u),v.styleSheet.cssText=""
})}(ap,ao),al._enableHTML5=ak,al._version=am,aj.className=aj.className.replace(/\bno-js\b/,"")+" js "+P.join(" ");return al
}(this,this.document);