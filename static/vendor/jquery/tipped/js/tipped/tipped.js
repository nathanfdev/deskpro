/*  Tipped 2.4.0.1 - 02-10-2011
 *  (c) 2010-2011 Nick Stakenburg - http://www.nickstakenburg.com
 *
 *  Tipped is licensed under the terms of the Tipped License:
 *  http://projects.nickstakenburg.com/tipped/license
 *
 *  More information on this project:
 *  http://projects.nickstakenburg.com/tipped
 */

var Tipped = { version: '2.4.0.1' };

Tipped.Skins = {
  // base skin, don't modify! (create custom skins in a seperate file)
  'base': {
    afterUpdate: false,
    ajax: {
      cache: true,
      type: 'get'
    },
    background: {
      color: '#f2f2f2',
      opacity: 1
    },
    border: {
      size: 1,
      color: '#000',
      opacity: 1
    },
    closeButtonSkin: 'default',
    containment: {
      selector: 'viewport'
    },
    fadeIn: 180,
    fadeOut: 220,
    showDelay: 75,
    hideDelay: 25,
    radius: {
      size: 3,
      position: 'background'
    },
    hideAfter: false,
    hideOn: {
      element: 'self',
      event: 'mouseleave'
    },
    hideOthers: false,
    hook: 'topleft',
    inline: false,
    offset: {
      x: 0, y: 0,
      mouse: { x: -12, y: -12 } // only defined in the base class
    },
    onHide: false,
    onShow: false,
    shadow: {
      blur: 2,
      color: '#000',
      offset: { x: 0, y: 0 },
      opacity: .15
    },
    showOn: 'mousemove',
    spinner: true,
    stem: {
      height: 6,
      width: 11,
      offset: { x: 5, y: 5 },
      spacing: 2
    },
    target: 'self'
  },
  
  // Every other skin inherits from this one
  'reset': {
    ajax: false,
    closeButton: false,
    hideOn: [{
      element: 'self',
      event: 'mouseleave'
    }, {
      element: 'tooltip',
      event: 'mouseleave'
    }],
    hook: 'topmiddle',
    stem: true
  },

  // Custom skins start here
  'black': {
     background: { color: '#232323', opacity: .9 },
     border: { size: 1, color: "#232323" },
     spinner: { color: '#fff' }
  },

  'cloud': {
    border: {
      size: 1,
      color: [
        { position: 0, color: '#bec6d5'},
        { position: 1, color: '#b1c2e3' }
      ]
    },
    closeButtonSkin: 'light',
    background: {
      color: [
        { position: 0, color: '#f6fbfd'},
        { position: 0.1, color: '#fff' },
        { position: .48, color: '#fff'},
        { position: .5, color: '#fefffe'},
        { position: .52, color: '#f7fbf9'},
        { position: .8, color: '#edeff0' },
        { position: 1, color: '#e2edf4' }
      ]
    },
    shadow: { opacity: .1 }
  },

  'dark': {
    border: { size: 1, color: '#1f1f1f', opacity: .95 },
    background: {
      color: [
        { position: .0, color: '#686766' },
        { position: .48, color: '#3a3939' },
        { position: .52, color: '#2e2d2d' },
        { position: .54, color: '#2c2b2b' },
        { position: 0.95, color: '#222' },
        { position: 1, color: '#202020' }
      ],
      opacity: .9
    },
    radius: { size: 4 },
    shadow: { offset: { x: 0, y: 1 } },
    spinner: { color: '#ffffff' }
  },

  'facebook': {
    background: { color: '#282828' },
    border: 0,
    fadeIn: 0,
    fadeOut: 0,
    radius: 0,
    stem: {
      width: 7,
      height: 4,
      offset: { x: 6, y: 6 }
    },
    shadow: false
  },

  'lavender': {
    background: {
      color: [
        { position: .0, color: '#b2b6c5' },
        { position: .5, color: '#9da2b4' },
        { position: 1, color: '#7f85a0' }
      ]
    },
    border: {
      color: [
        { position: 0, color: '#a2a9be' },
        { position: 1, color: '#6b7290' }
      ],
      size: 1
    },
    radius: 1,
    shadow: { opacity: .1 }
  },

  'light': {
    border: { size: 0, color: '#afafaf' },
    background: {
      color: [
        { position: 0, color: '#fefefe' },
        { position: 1, color: '#f7f7f7' }
      ]
    },
    closeButtonSkin: 'light',
    radius: 1,
    stem: {
      height: 7,
      width: 13,
      offset: { x: 7, y: 7 }
    },
    shadow: { opacity: .32, blur: 2 }
  },

  'lime': {
    border: {
      size: 1,
      color: [
        { position: 0,   color: '#5a785f' },
        { position: .05, color: '#0c7908' },
        { position: 1, color: '#587d3c' }
      ]
    },
    background: {
      color: [
        { position: 0,   color: '#a5e07f' },
        { position: .02, color: '#cef8be' },
        { position: .09, color: '#7bc83f' },
        { position: .35, color: '#77d228' },
        { position: .65, color: '#85d219' },
        { position: .8,  color: '#abe041' },
        { position: 1,   color: '#c4f087' }
      ]
    }
  },

  'liquid' : {
    border: {
      size: 1,
      color: [
        { position: 0, color: '#454545' },
        { position: 1, color: '#101010' }
      ]
    },
    background: {
      color: [
        { position: 0, color: '#515562'},
        { position: .3, color: '#252e43'},
        { position: .48, color: '#111c34'},
        { position: .52, color: '#161e32'},
        { position: .54, color: '#0c162e'},
        { position: 1, color: '#010c28'}
      ],
      opacity: .8
    },
    radius: { size: 4 },
    shadow: { offset: { x: 0, y: 1 } },
    spinner: { color: '#ffffff' }
  },

  'blue': {
    border: {
      color: [
        { position: 0, color: '#113d71'},
        { position: 1, color: '#1e5290' }
      ]
    },
    background: {
      color: [
        { position: 0, color: '#3a7ab8'},
        { position: .48, color: '#346daa'},
        { position: .52, color: '#326aa6'},
        { position: 1, color: '#2d609b' }
      ]
    },
    spinner: { color: '#f2f6f9' },
    shadow: { opacity: .2 }
  },

  'salmon' : {
    background: {
      color: [
        { position: 0, color: '#fbd0b7' },
        { position: .5, color: '#fab993' },
        { position: 1, color: '#f8b38b' }
      ]
    },
    border: {
      color: [
        { position: 0, color: '#eda67b' },
        { position: 1, color: '#df946f' }
      ],
      size: 1
    },
    radius: 1,
    shadow: { opacity: .1 }
  },

  'yellow': {
    border: { size: 1, color: '#f7c735' },
    background: '#ffffaa',
    radius: 1,
    shadow: { opacity: .1 }
  }
};

Tipped.Skins.CloseButtons = {
  'base': {
    diameter: 17,
    border: 2,
    x: { diameter: 10, size: 2, opacity: 1 },
    states: {
      'default': {
        background: {
          color: [
            { position: 0, color: '#1a1a1a' },
            { position: 0.46, color: '#171717' },
            { position: 0.53, color: '#121212' },
            { position: 0.54, color: '#101010' },
            { position: 1, color: '#000' }
          ],
          opacity: 1
        },
        x: { color: '#fafafa', opacity: 1 },
        border: { color: '#fff', opacity: 1 }
      },
      'hover': {
        background: {
          color: '#333',
          opacity: 1
        },
        x: { color: '#e6e6e6', opacity: 1 },
        border: { color: '#fff', opacity: 1 }
      }
    },
    shadow: {
      blur: 2,
      color: '#000',
      offset: { x: 0, y: 0 },
      opacity: .3
    }
  },

  'reset': {},

  'default': {},

  'light': {
    diameter: 17,
    border: 2,
    x: { diameter: 10, size: 2, opacity: 1 },
    states: {
      'default': {
        background: {
          color: [
            { position: 0, color: '#797979' },
            { position: 0.48, color: '#717171' },
            { position: 0.52, color: '#666' },
            { position: 1, color: '#666' }
          ],
          opacity: 1
        },
        x: { color: '#fff', opacity: .95 },
        border: { color: '#676767', opacity: 1 }
      },
      'hover': {
        background: {
          color: [
            { position: 0, color: '#868686' },
            { position: 0.48, color: '#7f7f7f' },
            { position: 0.52, color: '#757575' },
            { position: 1, color: '#757575' }
          ],
          opacity: 1
        },
        x: { color: '#fff', opacity: 1 },
        border: { color: '#767676', opacity: 1 }
      }
    }
  }
};

eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(D(a){D b(a,b){L c=[a,b];M c.H=a,c.J=b,c}D c(a){C.S=a}D d(a){L b={},c;1E(c 5M a)b[c]=a[c]+"28";M b}D e(a){M a*2h/N.2A}D f(a){M a*N.2A/2h}D g(b){b&&(C.S=b,t.1f(b),b=C.1Q(),C.I=a.Y({},b.I),C.1Z=1,C.Z={},t.2J(C),C.1z=C.I.11.1e,C.7p=C.I.W&&C.1z,C.1s())}D h(b,c,d){(C.S=b)&&c&&(C.I=a.Y({2B:3,1h:{x:0,y:0},1q:"#3W",1o:.5,2i:1},d||{}),C.1Z=C.I.2i,C.Z={},u.2J(C),C.1s())}D i(b,c){T(C.S=b)C.I=a.Y({2B:5,1h:{x:0,y:0},1q:"#3W",1o:.5,2i:1},c||{}),C.1Z=C.I.2i,v.2J(C),C.1s()}D j(b,c){1E(L d 5M c)c[d]&&c[d].38&&c[d].38===4L?(b[d]=a.Y({},b[d])||{},j(b[d],c[d])):b[d]=c[d];M b}D k(b,c,d){T(C.S=b){w.1f(C.S),w.2J(C),a.13(c)=="7q"&&!m.20(c)?(d=c,c=1g):d=d||{},C.I=w.5N(d),d=b.5O("4M");T(!c){L e=b.5O("2p-7r");e?c=e:d&&(c=d)}d&&(a(b).2p("4N",d),b.7s("4M","")),C.21=c,C.1S=C.I.1S||+w.I.3X,C.Z={2K:{G:1,K:1},4O:[],2L:[],22:{3Y:!1,29:!1,1k:!1,2U:!1,1s:!1,3Z:!1,4P:!1,39:!1},4Q:""},b=C.I.1l,C.1l=b=="2q"?"2q":b=="40"||!b?C.S:b&&1b.7t(b)||C.S,C.5P(),C.5Q()}}L l=5R.3a.7u,m={7v:D(b,c){M D(){L d=[a.1d(b,C)].5S(l.2V(41));M c.4R(C,d)}},"1a":{},5T:D(a,b){1E(L c=0,d=a.1x;c<d;c++)b(a[c])},19:D(a,b,c){L d=0;4S{C.5T(a,D(a){b.2V(c,a,d++)})}4T(e){T(e!=m["1a"])7w e}},42:D(a,b,c){L d=!1;M m.19(a||[],D(a,e){T(d|=b.2V(c,a,e))M m["1a"]}),!!d},5U:D(a,b){L c=!1;M m.42(a||[],D(a){T(c=a===b)M!0}),c},4U:D(a,b,c){L d=[];M m.19(a||[],D(a,e){b.2V(c,a,e)&&(d[d.1x]=a)}),d},3t:D(a){L b=l.2V(41,1);M m.4U(a,D(a){M!m.5U(b,a)})},20:D(a){M a&&a.7x==1},4V:D(a,b){L c=l.2V(41,2);M 7y(D(){M a.4R(a,c)},b)},4W:D(a){M m.4V.4R(C,[a,1].5S(l.2V(41,1)))},43:D(a){M{x:a.5V,y:a.7z}},4X:D(b,c){L d=b.1l;M c?a(d).4Y(c)[0]:d},S:{44:D(a){L c=0,d=0;7A c+=a.46||0,d+=a.47||0,a=a.48;7B(a);M b(d,c)},49:D(c){L d=a(c).1h(),c=m.S.44(c),e=a(1F).46(),f=a(1F).47();M d.H+=c.H-f,d.J+=c.J-e,b(d.H,d.J)},4Z:D(){M D(a){1E(;a&&a.48;)a=a.48;M!!a&&!!a.4a}}()}},n=D(a){D b(b){M(b=5W(b+"([\\\\d.]+)").7C(a))?5X(b[1]):!0}M{52:!!1F.7D&&a.2W("53")===-1&&b("7E "),53:a.2W("53")>-1&&(!!1F.54&&54.5Y&&5X(54.5Y())||7.55),7F:a.2W("5Z/")>-1&&b("5Z/"),60:a.2W("60")>-1&&a.2W("7G")===-1&&b("7H:"),7I:!!a.2M(/7J.*7K.*7L/),56:a.2W("56")>-1&&b("56/")}}(7M.7N),o={2C:{2X:{4b:"2.7O",4c:1F.2X&&2X.7P},3u:{4b:"1.6",4c:1F.3u&&3u.7Q.7R}},57:D(){D a(a){1E(L c=(a=a.2M(b))&&a[1]&&a[1].2r(".")||[],d=0,e=0,f=c.1x;e<f;e++)d+=2s(c[e]*N.4d(10,6-e*2));M a&&a[3]?d-1:d}L b=/^(\\d+(\\.?\\d+){0,3})([A-61-7S-]+[A-61-7T-9]+)?/;M D(b){!C.2C[b].62&&(C.2C[b].62=!0,!C.2C[b].4c||a(C.2C[b].4c)<a(C.2C[b].4b)&&!C.2C[b].63)&&(C.2C[b].63=!0,64("1A 65 "+b+" >= "+C.2C[b].4b))}}()};a.Y(1A,D(){L b=D(){L a=1b.1w("2D");M!!a.2Y&&!!a.2Y("2d")}(),d;4S{d=!!1b.66("7U")}4T(e){d=!1}M{2N:{2D:b,58:d,3v:D(){L b=!1;M a.19(["7V","7W","7X"],D(a,c){4S{1b.66(c),b=!0}4T(d){}}),b}()},2O:D(){T(!C.2N.2D&&!1F.3w)T(n.52)64("1A 65 7Y (7Z.80)");1K M;o.57("3u"),a(1b).67(D(){w.68()})},4e:D(a,b,d){M c.4e(a,b,d),C.17(a)},17:D(a){M 2Z c(a)},1t:D(a){M C.17(a).1t(),C},1m:D(a){M C.17(a).1m(),C},2E:D(a){M C.17(a).2E(),C},2t:D(a){M C.17(a).2t(),C},1f:D(a){M C.17(a).1f(),C},4f:D(){M w.4f(),C},59:D(a){M w.59(a),C},5a:D(a){M w.5a(a),C},1k:D(b){T(m.20(b))M w.5b(b);T(a.13(b)!="5c"){L b=a(b),c=0;M a.19(b,D(a,b){w.5b(b)&&c++}),c}M w.3x().1x}}}()),a.Y(c,{4e:D(b,c,d){T(b){L e=d||{},f=[];M m.20(b)?f.1T(2Z k(b,c,e)):a(b).19(D(a,b){f.1T(2Z k(b,c,e))}),f}}}),a.Y(c.3a,{3y:D(){M w.2a.4g={x:0,y:0},w.17(C.S)},1t:D(){M a.19(C.3y(),D(a,b){b.1t()}),C},1m:D(){M a.19(C.3y(),D(a,b){b.1m()}),C},2E:D(){M a.19(C.3y(),D(a,b){b.2E()}),C},2t:D(){M a.19(C.3y(),D(a,b){b.2t()}),C},1f:D(){M w.1f(C.S),C}});L p={2O:D(){M 1F.3w&&!1A.2N.2D&&n.52?D(a){3w.81(a)}:D(){}}(),69:D(b,c){L d=a.Y({J:0,H:0,G:0,K:0,12:0},c||{}),e=d.H,g=d.J,h=d.G,i=d.K;(d=d.12)?(b.1L(),b.2P(e+d,g),b.1J(e+h-d,g+d,d,f(-90),f(0),!1),b.1J(e+h-d,g+i-d,d,f(0),f(90),!1),b.1J(e+d,g+i-d,d,f(90),f(2h),!1),b.1J(e+d,g+d,d,f(-2h),f(-90),!1),b.1M(),b.2u()):b.6a(e,g,h,i)},82:D(b,c,d){1E(L d=a.Y({x:0,y:0,1q:"#3W"},d||{}),e=0,f=c.1x;e<f;e++)1E(L g=0,h=c[e].1x;g<h;g++){L i=2s(c[e].30(g))*(1/9);b.2j=s.2k(d.1q,i),i&&b.6a(d.x+g,d.y+e,1,1)}},3z:D(b,c,d){L e;M a.13(c)=="1V"?e=s.2k(c):a.13(c.1q)=="1V"?e=s.2k(c.1q,a.13(c.1o)=="2b"?c.1o:1):a.6b(c.1q)&&(d=a.Y({3b:0,3c:0,3d:0,3e:0},d||{}),e=p.6c.6d(b.83(d.3b,d.3c,d.3d,d.3e),c.1q,c.1o)),e},6c:{6d:D(b,c,d){1E(L d=a.13(d)=="2b"?d:1,e=0,f=c.1x;e<f;e++){L g=c[e];T(a.13(g.1o)=="5c"||a.13(g.1o)!="2b")g.1o=1;b.84(g.P,s.2k(g.1q,g.1o*d))}M b}}},q={3A:"3f,3B,3g,3h,3C,3D,3E,3F,3G,3H,3I,3i".2r(","),3J:{6e:/^(J|H|1B|1C)(J|H|1B|1C|2v|2w)$/,1y:/^(J|1B)/,2Q:/(2v|2w)/,6f:/^(J|1B|H|1C)/},6g:D(){L a={J:"K",H:"G",1B:"K",1C:"G"};M D(b){M a[b]}}(),2Q:D(a){M!!a.31().2M(C.3J.2Q)},5d:D(a){M!C.2Q(a)},2l:D(a){M a.31().2M(C.3J.1y)?"1y":"24"},5e:D(a){L b=1g;M(a=a.31().2M(C.3J.6f))&&a[1]&&(b=a[1]),b},2r:D(a){M a.31().2M(C.3J.6e)}},r={5f:D(a){M a=a.I.W,{G:a.G,K:a.K}},3K:D(b,c,d){M d=a.Y({3j:"1n"},d||{}),b=b.I.W,c=C.4h(b.G,b.K,c),d.3j&&(c.G=N[d.3j](c.G),c.K=N[d.3j](c.K)),{G:c.G,K:c.K}},4h:D(a,b,c){L d=2h-e(N.6h(b/a*.5));M c*=N.4i(f(d-90)),c=a+c*2,{G:c,K:c*b/a}},32:D(a,b){L c=C.3K(a,b),d=C.5f(a);q.2Q(a.1z);L e=N.1n(c.K+b);M{2F:{Q:{G:N.1n(c.G),K:N.1n(e)}},U:{Q:c},W:{Q:{G:d.G,K:d.K}}}},5g:D(b,c,d){L e={J:0,H:0},f={J:0,H:0},g=a.Y({},c),h=b.U,i=i||C.32(b,b.U),j=i.2F.Q;d&&(j.K=d,h=0);T(b.I.W){L k=q.5e(b.1z);k=="J"?e.J=j.K-h:k=="H"&&(e.H=j.K-h);L d=q.2r(b.1z),l=q.2l(b.1z);T(l=="1y"){1u(d[2]){R"2v":R"2w":f.H=.5*g.G;1a;R"1C":f.H=g.G}d[1]=="1B"&&(f.J=g.K-h+j.K)}1K{1u(d[2]){R"2v":R"2w":f.J=.5*g.K;1a;R"1B":f.J=g.K}d[1]=="1C"&&(f.H=g.G-h+j.K)}g[q.6g(k)]+=j.K-h}1K T(d=q.2r(b.1z),l=q.2l(b.1z),l=="1y"){1u(d[2]){R"2v":R"2w":f.H=.5*g.G;1a;R"1C":f.H=g.G}d[1]=="1B"&&(f.J=g.K)}1K{1u(d[2]){R"2v":R"2w":f.J=.5*g.K;1a;R"1B":f.J=g.K}d[1]=="1C"&&(f.H=g.G)}L m=b.I.12&&b.I.12.2c||0,h=b.I.U&&b.I.U.2c||0;T(b.I.W){L n=b.I.W&&b.I.W.1h||{x:0,y:0},k=m&&b.I.12.P=="X"?m:0,m=m&&b.I.12.P=="U"?m:m+h,o=h+k+.5*i.W.Q.G-.5*i.U.Q.G,i=N.1n(h+k+.5*i.W.Q.G+(m>o?m-o:0));T(l=="1y")1u(d[2]){R"H":f.H+=i;1a;R"1C":f.H-=i}1K 1u(d[2]){R"J":f.J+=i;1a;R"1B":f.J-=i}}T(b.I.W&&(n=b.I.W.1h))T(l=="1y")1u(d[2]){R"H":f.H+=n.x;1a;R"1C":f.H-=n.x}1K 1u(d[2]){R"J":f.J+=n.y;1a;R"1B":f.J-=n.y}L p;T(b.I.W&&(p=b.I.W.85))T(l=="1y")1u(d[1]){R"J":f.J-=p;1a;R"1B":f.J+=p}1K 1u(d[1]){R"H":f.H-=p;1a;R"1C":f.H+=p}M{Q:g,P:{J:0,H:0},X:{P:e,Q:c},W:{Q:j},1W:f}}},s=D(){D b(a){M a.6i=a[0],a.6j=a[1],a.6k=a[2],a}D c(a){L c=5R(3);a.2W("#")==0&&(a=a.4j(1)),a=a.31();T(a.86(d,"")!="")M 1g;a.1x==3?(c[0]=a.30(0)+a.30(0),c[1]=a.30(1)+a.30(1),c[2]=a.30(2)+a.30(2)):(c[0]=a.4j(0,2),c[1]=a.4j(2,4),c[2]=a.4j(4));1E(a=0;a<c.1x;a++)c[a]=2s(c[a],16);M b(c)}L d=5W("[87]","g");M{88:c,2k:D(b,d){a.13(d)=="5c"&&(d=1);L e=d,f=c(b);M f[3]=e,f.1o=e,"89("+f.8a()+")"},8b:D(a){L a=c(a),a=b(a),d=a.6i,e=a.6j,f=a.6k,g,h=d>e?d:e;f>h&&(h=f);L i=d<e?d:e;f<i&&(i=f),g=h/8c,a=h!=0?(h-i)/h:0;T(a==0)d=0;1K{L j=(h-d)/(h-i),k=(h-e)/(h-i),f=(h-f)/(h-i),d=d==h?f-k:e==h?2+j-f:4+k-j;d/=6,d<0&&(d+=1)}M d=N.1N(d*6l),a=N.1N(a*5h),g=N.1N(g*5h),e=[],e[0]=d,e[1]=a,e[2]=g,e.8d=d,e.8e=a,e.8f=g,"#"+(e[2]>50?"3W":"8g")}}}(),t={3L:[],17:D(b){T(!b)M 1g;L c=1g;M a.19(C.3L,D(a,d){d.S==b&&(c=d)}),c},2J:D(a){C.3L.1T(a)},1f:D(a){T(a=C.17(a))C.3L=m.3t(C.3L,a),a.1f()}};a.Y(g.3a,D(){M{4k:D(){L a=C.1Q();C.2K=a.Z.2K,a=a.I,C.12=a.12&&a.12.2c||0,C.U=a.U&&a.U.2c||0,C.1X=a.1X,a=N.5i(C.2K.K,C.2K.G),C.12>a/2&&(C.12=N.5j(a/2)),C.I.12.P=="U"&&C.12>C.U&&(C.U=C.12),C.Z={I:{12:C.12,U:C.U,1X:C.1X}}},6m:D(){C.Z.11={};L b=C.1z;a.19(q.3A,a.1d(D(a,b){L c;C.Z.11[b]={},C.1z=b,c=C.1Y(),C.Z.11[b].1W=c.1W,C.Z.11[b].1i={Q:c.1i.Q,P:{J:c.1i.P.J,H:c.1i.P.H}},C.Z.11[b].1e={Q:c.1G.Q},C.15&&(c=C.15.1Y(),C.Z.11[b].1W=c.1W,C.Z.11[b].1i.P.J+=c.1G.P.J,C.Z.11[b].1i.P.H+=c.1G.P.H,C.Z.11[b].1e.Q=c.1e.Q)},C)),C.1z=b},1s:D(){C.2G(),1F.3w&&1F.3w.8h(1b);L b=C.1Q(),c=C.I;a(C.1i=1b.1w("1O")).1v({"1U":"8i"}),a(b.4l).1H(C.1i),C.4k(),C.6n(b),c.1c&&(C.6o(b),c.1c.15)&&(C.2x?(C.2x.I=c.1c.15,C.2x.1s()):C.2x=2Z i(C.S,a.Y({2i:C.1Z},c.1c.15))),C.4m(),c.15&&(C.15?(C.15.I=c.15,C.15.1s()):C.15=2Z h(C.S,C,a.Y({2i:C.1Z},c.15))),C.6m()},1f:D(){C.2G(),C.I.15&&(u.1f(C.S),C.I.1c&&C.I.1c.15&&v.1f(C.S)),C.V&&(a(C.V).1f(),C.V=1g)},2G:D(){C.1i&&(C.1c&&(a(C.1c).1f(),C.5k=C.5l=C.1c=1g),a(C.1i).1f(),C.1i=C.X=C.W=1g,C.Z={})},1Q:D(){M w.17(C.S)[0]},2t:D(){L b=C.1Q(),c=a(b.V),d=a(b.V).5m(".6p").6q()[0];T(d){a(d).14({G:"5n",K:"5n"});L e=2s(c.14("J")),f=2s(c.14("H")),g=2s(c.14("G"));c.14({H:"-6r",J:"-6r",G:"8j",K:"5n"}),b.1j("1k")||a(b.V).1t();L h=w.4n.5o(d);b.I.2R&&a.13(b.I.2R)=="2b"&&h.G>b.I.2R&&(a(d).14({G:b.I.2R+"28"}),h=w.4n.5o(d)),b.1j("1k")||a(b.V).1m(),b.Z.2K=h,c.14({H:f+"28",J:e+"28",G:g+"28"}),C.1s()}},3M:D(a){C.1z!=a&&(C.1z=a,C.1s())},6o:D(b){L c=b.I.1c,c={G:c.33+2*c.U,K:c.33+2*c.U};a(b.V).1H(a(C.1c=1b.1w("1O")).1v({"1U":"6s"}).14(d(c)).1H(a(C.6t=1b.1w("1O")).1v({"1U":"8k"}).14(d(c)))),C.5p(b,"5q"),C.5p(b,"5r"),a(C.1c).3k("3N",a.1d(C.6u,C)).3k("4o",a.1d(C.6v,C))},5p:D(b,c){L e=b.I.1c,g=e.33,h=e.U||0,i=e.x.33,j=e.x.2c,e=e.22[c||"5q"],k={G:g+2*h,K:g+2*h};i>=g&&(i=g-2);L l;a(C.6t).1H(a(C[c+"8l"]=1b.1w("1O")).1v({"1U":"8m"}).14(a.Y(d(k),{H:(c=="5r"?k.G:0)+"28"})).1H(a(l=1b.1w("2D")).1v(k))),p.2O(l),l=l.2Y("2d"),l.2i=C.1Z,l.8n(k.G/2,k.K/2),l.2j=p.3z(l,e.X,{3b:0,3c:0-g/2,3d:0,3e:0+g/2}),l.1L(),l.1J(0,0,g/2,0,N.2A*2,!0),l.1M(),l.2u(),h&&(l.2j=p.3z(l,e.U,{3b:0,3c:0-g/2-h,3d:0,3e:0+g/2+h}),l.1L(),l.1J(0,0,g/2,N.2A,0,!1),l.O((g+h)/2,0),l.1J(0,0,g/2+h,0,N.2A,!0),l.1J(0,0,g/2+h,N.2A,0,!0),l.O(g/2,0),l.1J(0,0,g/2,0,N.2A,!1),l.1M(),l.2u()),g=i/2,j/=2,j>g&&(h=j,j=g,g=h),l.2j=s.2k(e.x.1q||e.x,e.x.1o||1),l.4p(f(45)),l.1L(),l.2P(0,0),l.O(0,g);1E(e=0;e<4;e++)l.O(0,g),l.O(j,g),l.O(j,g-(g-j)),l.O(g,j),l.O(g,0),l.4p(f(90));l.1M(),l.2u()},6n:D(b){L c=C.1Y(),d=C.I.W&&C.3O(),e=C.1z&&C.1z.31(),f=C.12,g=C.U,h=b.I.W&&b.I.W.1h||{x:0,y:0},i=0,j=0;f&&(i=C.I.12.P=="X"?f:0,j=C.I.12.P=="U"?f:i+g),C.2S=1b.1w("2D"),a(C.2S).1v(c.1i.Q),a(C.1i).1H(C.2S),a(b.V).1t(),p.2O(C.2S),b.1j("1k")||a(b.V).1m(),b=C.2S.2Y("2d"),b.2i=C.1Z,b.2j=p.3z(b,C.I.X,{3b:0,3c:c.X.P.J+g,3d:0,3e:c.X.P.J+c.X.Q.K-g}),b.8o=0,C.5s(b,{1L:!0,1M:!0,U:g,12:i,4q:j,34:c,35:d,W:C.I.W,36:e,37:h}),b.2u(),g&&(f=p.3z(b,C.I.U,{3b:0,3c:c.X.P.J,3d:0,3e:c.X.P.J+c.X.Q.K}),b.2j=f,C.5s(b,{1L:!0,1M:!1,U:g,12:i,4q:j,34:c,35:d,W:C.I.W,36:e,37:h}),C.6w(b,{1L:!1,1M:!0,U:g,6x:i,12:{2c:j,P:C.I.12.P},34:c,35:d,W:C.I.W,36:e,37:h}),b.2u())},5s:D(b,c){L d=a.Y({W:!1,36:1g,1L:!1,1M:!1,34:1g,35:1g,12:0,U:0,4q:0,37:{x:0,y:0}},c||{}),e=d.34,g=d.35,h=d.37,i=d.U,j=d.12,k=d.36,l=e.X.P,e=e.X.Q,m,n,o;g&&(m=g.W.Q,n=g.2F.Q,o=d.4q,g=i+j+.5*m.G-.5*g.U.Q.G,o=N.1n(o>g?o-g:0));L p,g=j?l.H+i+j:l.H+i;p=l.J+i,h&&h.x&&/^(3f|3i)$/.4r(k)&&(g+=h.x),d.1L&&b.1L(),b.2P(g,p);T(d.W)1u(k){R"3f":g=l.H+i,j&&(g+=j),g+=N.1r(o,h.x||0),b.O(g,p),p-=m.K,g+=m.G*.5,b.O(g,p),p+=m.K,g+=m.G*.5,b.O(g,p);1a;R"3B":R"4s":g=l.H+e.G*.5-m.G*.5,b.O(g,p),p-=m.K,g+=m.G*.5,b.O(g,p),p+=m.K,g+=m.G*.5,b.O(g,p),g=l.H+e.G*.5-n.G*.5,b.O(g,p);1a;R"3g":g=l.H+e.G-i-m.G,j&&(g-=j),g-=N.1r(o,h.x||0),b.O(g,p),p-=m.K,g+=m.G*.5,b.O(g,p),p+=m.K,g+=m.G*.5,b.O(g,p)}j?j&&(b.1J(l.H+e.G-i-j,l.J+i+j,j,f(-90),f(0),!1),g=l.H+e.G-i,p=l.J+i+j):(g=l.H+e.G-i,p=l.J+i,b.O(g,p));T(d.W)1u(k){R"3h":p=l.J+i,j&&(p+=j),p+=N.1r(o,h.y||0),b.O(g,p),g+=m.K,p+=m.G*.5,b.O(g,p),g-=m.K,p+=m.G*.5,b.O(g,p);1a;R"3C":R"4t":p=l.J+e.K*.5-m.G*.5,b.O(g,p),g+=m.K,p+=m.G*.5,b.O(g,p),g-=m.K,p+=m.G*.5,b.O(g,p);1a;R"3D":p=l.J+e.K-i,j&&(p-=j),p-=m.G,p-=N.1r(o,h.y||0),b.O(g,p),g+=m.K,p+=m.G*.5,b.O(g,p),g-=m.K,p+=m.G*.5,b.O(g,p)}j?j&&(b.1J(l.H+e.G-i-j,l.J+e.K-i-j,j,f(0),f(90),!1),g=l.H+e.G-i-j,p=l.J+e.K-i):(g=l.H+e.G-i,p=l.J+e.K-i,b.O(g,p));T(d.W)1u(k){R"3E":g=l.H+e.G-i,j&&(g-=j),g-=N.1r(o,h.x||0),b.O(g,p),g-=m.G*.5,p+=m.K,b.O(g,p),g-=m.G*.5,p-=m.K,b.O(g,p);1a;R"3F":R"4u":g=l.H+e.G*.5+m.G*.5,b.O(g,p),g-=m.G*.5,p+=m.K,b.O(g,p),g-=m.G*.5,p-=m.K,b.O(g,p);1a;R"3G":g=l.H+i+m.G,j&&(g+=j),g+=N.1r(o,h.x||0),b.O(g,p),g-=m.G*.5,p+=m.K,b.O(g,p),g-=m.G*.5,p-=m.K,b.O(g,p)}j?j&&(b.1J(l.H+i+j,l.J+e.K-i-j,j,f(90),f(2h),!1),g=l.H+i,p=l.J+e.K-i-j):(g=l.H+i,p=l.J+e.K-i,b.O(g,p));T(d.W)1u(k){R"3H":p=l.J+e.K-i,j&&(p-=j),p-=N.1r(o,h.y||0),b.O(g,p),g-=m.K,p-=m.G*.5,b.O(g,p),g+=m.K,p-=m.G*.5,b.O(g,p);1a;R"3I":R"4v":p=l.J+e.K*.5+m.G*.5,b.O(g,p),g-=m.K,p-=m.G*.5,b.O(g,p),g+=m.K,p-=m.G*.5,b.O(g,p);1a;R"3i":p=l.J+i+m.G,j&&(p+=j),p+=N.1r(o,h.y||0),b.O(g,p),g-=m.K,p-=m.G*.5,b.O(g,p),g+=m.K,p-=m.G*.5,b.O(g,p)}M j?j&&(b.1J(l.H+i+j,l.J+i+j,j,f(-2h),f(-90),!1),g=l.H+i+j,p=l.J+i,g+=1,b.O(g,p)):(g=l.H+i,p=l.J+i,b.O(g,p)),d.1M&&b.1M(),{x:g,y:p}},6w:D(b,c){L d=a.Y({W:!1,36:1g,1L:!1,1M:!1,34:1g,35:1g,12:0,U:0,8p:0,37:{x:0,y:0}},c||{}),e=d.34,g=d.35,h=d.37,i=d.U,j=d.12&&d.12.2c||0,k=d.6x,l=d.36,m=e.X.P,e=e.X.Q,n,o,p;g&&(n=g.W.Q,o=g.U.Q,p=i+k+.5*n.G-.5*o.G,p=N.1n(j>p?j-p:0));L g=m.H+i+k,q=m.J+i;k&&(g+=1),a.Y({},{x:g,y:q}),d.1L&&b.1L();L r=a.Y({},{x:g,y:q});q-=i,b.O(g,q),j?j&&(b.1J(m.H+j,m.J+j,j,f(-90),f(-2h),!0),g=m.H,q=m.J+j):(g=m.H,q=m.J,b.O(g,q));T(d.W)1u(l){R"3i":q=m.J+i,k&&(q+=k),q-=o.G*.5,q+=n.G*.5,q+=N.1r(p,h.y||0),b.O(g,q),g-=o.K,q+=o.G*.5,b.O(g,q),g+=o.K,q+=o.G*.5,b.O(g,q);1a;R"3I":R"4v":q=m.J+e.K*.5-o.G*.5,b.O(g,q),g-=o.K,q+=o.G*.5,b.O(g,q),g+=o.K,q+=o.G*.5,b.O(g,q);1a;R"3H":q=m.J+e.K-i-o.G,k&&(q-=k),q+=o.G*.5,q-=n.G*.5,q-=N.1r(p,h.y||0),b.O(g,q),g-=o.K,q+=o.G*.5,b.O(g,q),g+=o.K,q+=o.G*.5,b.O(g,q)}j?j&&(b.1J(m.H+j,m.J+e.K-j,j,f(-2h),f(-8q),!0),g=m.H+j,q=m.J+e.K):(g=m.H,q=m.J+e.K,b.O(g,q));T(d.W)1u(l){R"3G":g=m.H+i,k&&(g+=k),g-=o.G*.5,g+=n.G*.5,g+=N.1r(p,h.x||0),b.O(g,q),q+=o.K,g+=o.G*.5,b.O(g,q),q-=o.K,g+=o.G*.5,b.O(g,q);1a;R"3F":R"4u":g=m.H+e.G*.5-o.G*.5,b.O(g,q),q+=o.K,g+=o.G*.5,b.O(g,q),q-=o.K,g+=o.G*.5,b.O(g,q),g=m.H+e.G*.5+o.G,b.O(g,q);1a;R"3E":g=m.H+e.G-i-o.G,k&&(g-=k),g+=o.G*.5,g-=n.G*.5,g-=N.1r(p,h.x||0),b.O(g,q),q+=o.K,g+=o.G*.5,b.O(g,q),q-=o.K,g+=o.G*.5,b.O(g,q)}j?j&&(b.1J(m.H+e.G-j,m.J+e.K-j,j,f(90),f(0),!0),g=m.H+e.G,q=m.J+e.G+j):(g=m.H+e.G,q=m.J+e.K,b.O(g,q));T(d.W)1u(l){R"3D":q=m.J+e.K-i,q+=o.G*.5,q-=n.G*.5,k&&(q-=k),q-=N.1r(p,h.y||0),b.O(g,q),g+=o.K,q-=o.G*.5,b.O(g,q),g-=o.K,q-=o.G*.5,b.O(g,q);1a;R"3C":R"4t":q=m.J+e.K*.5+o.G*.5,b.O(g,q),g+=o.K,q-=o.G*.5,b.O(g,q),g-=o.K,q-=o.G*.5,b.O(g,q);1a;R"3h":q=m.J+i,k&&(q+=k),q+=o.G,q-=o.G*.5-n.G*.5,q+=N.1r(p,h.y||0),b.O(g,q),g+=o.K,q-=o.G*.5,b.O(g,q),g-=o.K,q-=o.G*.5,b.O(g,q)}j?j&&(b.1J(m.H+e.G-j,m.J+j,j,f(0),f(-90),!0),q=m.J):(g=m.H+e.G,q=m.J,b.O(g,q));T(d.W)1u(l){R"3g":g=m.H+e.G-i,g+=o.G*.5-n.G*.5,k&&(g-=k),g-=N.1r(p,h.x||0),b.O(g,q),q-=o.K,g-=o.G*.5,b.O(g,q),q+=o.K,g-=o.G*.5,b.O(g,q);1a;R"3B":R"4s":g=m.H+e.G*.5+o.G*.5,b.O(g,q),q-=o.K,g-=o.G*.5,b.O(g,q),q+=o.K,g-=o.G*.5,b.O(g,q),g=m.H+e.G*.5-o.G,b.O(g,q),b.O(g,q);1a;R"3f":g=m.H+i+o.G,g-=o.G*.5,g+=n.G*.5,k&&(g+=k),g+=N.1r(p,h.x||0),b.O(g,q),q-=o.K,g-=o.G*.5,b.O(g,q),q+=o.K,g-=o.G*.5,b.O(g,q)}b.O(r.x,r.y-i),b.O(r.x,r.y),d.1M&&b.1M()},6u:D(){L b=C.1Q().I.1c,b=b.33+b.U*2;a(C.5l).14({H:-1*b+"28"}),a(C.5k).14({H:0})},6v:D(){L b=C.1Q().I.1c,b=b.33+b.U*2;a(C.5l).14({H:0}),a(C.5k).14({H:b+"28"})},3O:D(){M r.32(C,C.U)},1Y:D(){L a,b,c,d,e,g,h=C.2K,i=C.1Q().I,j=C.12,k=C.U,l=C.1X,h={G:k*2+l*2+h.G,K:k*2+l*2+h.K};C.I.W&&C.3O();L m=r.5g(C,h),l=m.Q,n=m.P,h=m.X.Q,o=m.X.P,p=0,q=0,s=l.G,t=l.K;M i.1c&&(e=j,i.12.P=="X"&&(e+=k),p=e-N.8r(f(45))*e,k="1C",C.1z.31().2M(/^(3g|3h)$/)&&(k="H"),i=i.1c.33+2*i.1c.U,e=i,g=i,q=o.H-i/2+(k=="H"?p:h.G-p),p=o.J-i/2+p,k=="H"?q<0&&(i=N.2m(q),s+=i,n.H+=i,q=0):(i=q+i-s,i>0&&(s+=i)),p<0&&(i=N.2m(p),t+=i,n.J+=i,p=0),C.I.1c.15)&&(a=C.I.1c.15,b=a.2B,i=a.1h,c=e+2*b,d=g+2*b,a=p-b+i.y,b=q-b+i.x,k=="H"?b<0&&(i=N.2m(b),s+=i,n.H+=i,q+=i,b=0):(i=b+c-s,i>0&&(s+=i)),a<0&&(i=N.2m(a),t+=i,n.J+=i,p+=i,a=0)),m=m.1W,m.J+=n.J,m.H+=n.H,k={H:N.1n(n.H+o.H+C.U+C.I.1X),J:N.1n(n.J+o.J+C.U+C.I.1X)},h={1e:{Q:{G:N.1n(s),K:N.1n(t)}},1G:{Q:{G:N.1n(s),K:N.1n(t)}},1i:{Q:l,P:{J:N.1N(n.J),H:N.1N(n.H)}},X:{Q:{G:N.1n(h.G),K:N.1n(h.K)},P:{J:N.1N(o.J),H:N.1N(o.H)}},1W:{J:N.1N(m.J),H:N.1N(m.H)},21:{P:k}},C.I.1c&&(h.1c={Q:{G:N.1n(e),K:N.1n(g)},P:{J:N.1N(p),H:N.1N(q)}},C.I.1c.15)&&(h.2x={Q:{G:N.1n(c),K:N.1n(d)},P:{J:N.1N(a),H:N.1N(b)}}),h},4m:D(){L b=C.1Y(),c=C.1Q();a(c.V).14(d(b.1e.Q)),a(c.4l).14(d(b.1G.Q)),a(C.1i).14(a.Y(d(b.1i.Q),d(b.1i.P))),C.1c&&(a(C.1c).14(d(b.1c.P)),b.2x&&a(C.2x.V).14(d(b.2x.P))),a(c.2T).14(d(b.21.P))},6y:D(a){C.1Z=a||0,C.15&&(C.15.1Z=C.1Z)},8s:D(a){C.6y(a),C.1s()}}}());L u={2y:[],17:D(b){T(!b)M 1g;L c=1g;M a.19(C.2y,D(a,d){d.S==b&&(c=d)}),c},2J:D(a){C.2y.1T(a)},1f:D(a){T(a=C.17(a))C.2y=m.3t(C.2y,a),a.1f()},3P:D(a){M N.2A/2-N.4d(a,N.4i(a)*N.2A)},3l:{3K:D(a,b){L c=t.17(a.S).3O().U.Q,c=C.4h(c.G,c.K,b,{3j:!1});M{G:c.G,K:c.K}},8t:D(a,b,c){L d=a*.5,g=2h-e(N.8u(d/N.6z(d*d+b*b)))-90,g=f(g);M c*=1/N.4i(g),d=(d+c)*2,{G:d,K:d/a*b}},4h:D(a,b,c){L d=2h-e(N.6h(b/a*.5));M c*=N.4i(f(d-90)),c=a+c*2,{G:c,K:c*b/a}},32:D(b){L c=t.17(b.S),d=b.I.2B,e=q.5d(c.1z);q.2l(c.1z),c=u.3l.3K(b,d),c={2F:{Q:{G:N.1n(c.G),K:N.1n(c.K)},P:{J:0,H:0}}};T(d){c.2e=[];1E(L f=0;f<=d;f++){L g=u.3l.3K(b,f,{3j:!1});c.2e.1T({P:{J:c.2F.Q.K-g.K,H:e?d-f:(c.2F.Q.G-g.G)/2},Q:g})}}1K c.2e=[a.Y({},c.2F)];M c},4p:D(a,b,c){r.4p(a,b.2H(),c)}}};a.Y(h.3a,D(){M{4k:D(){},1f:D(){C.2G()},2G:D(){C.V&&(a(C.V).1f(),C.V=C.1i=C.X=C.W=1g,C.Z={})},1s:D(){C.2G(),C.4k();L b=C.1Q(),c=C.2H();C.V=1b.1w("1O"),a(C.V).1v({"1U":"8v"}),a(b.V).8w(C.V),c.1Y(),a(C.V).14({J:0,H:0}),C.6A(),C.4m()},1Q:D(){M w.17(C.S)[0]},2H:D(){M t.17(C.S)},1Y:D(){L b=C.2H(),c=b.1Y();C.1Q();L d=C.I.2B,e=a.Y({},c.X.Q);e.G+=2*d,e.K+=2*d;L f;b.I.W&&(f=u.3l.32(C).2F.Q,f=f.K);L g=r.5g(b,e,f);f=g.Q;L h=g.P,e=g.X.Q,g=g.X.P,i=c.1i.P,j=c.X.P,d={J:i.J+j.J-(g.J+d)+C.I.1h.y,H:i.H+j.H-(g.H+d)+C.I.1h.x},i=c.1W,j=c.1G.Q,k={J:0,H:0};T(d.J<0){L l=N.2m(d.J);k.J+=l,d.J=0,i.J+=l}M d.H<0&&(l=N.2m(d.H),k.H+=l,d.H=0,i.H+=l),l={K:N.1r(f.K+d.J,j.K+k.J),G:N.1r(f.G+d.H,j.G+k.H)},b={H:N.1n(k.H+c.1i.P.H+c.X.P.H+b.U+b.1X),J:N.1n(k.J+c.1i.P.J+c.X.P.J+b.U+b.1X)},{1e:{Q:l},1G:{Q:j,P:k},V:{Q:f,P:d},1i:{Q:f,P:{J:N.1N(h.J),H:N.1N(h.H)}},X:{Q:{G:N.1n(e.G),K:N.1n(e.K)},P:{J:N.1N(g.J),H:N.1N(g.H)}},1W:i,21:{P:b}}},5t:D(){M C.I.1o/(C.I.2B+1)},6A:D(){L b=C.2H(),c=b.1Y(),e=C.1Q(),f=C.1Y(),g=C.I.2B,h=u.3l.32(C),i=b.1z,j=q.5e(i),k=g,l=g;T(e.I.W){L m=h.2e[h.2e.1x-1];j=="H"&&(l+=N.1n(m.Q.K)),j=="J"&&(k+=N.1n(m.Q.K))}L n=b.Z.I,m=n.12,n=n.U;e.I.12.P=="X"&&m&&(m+=n),a(C.V).1H(a(C.1i=1b.1w("1O")).1v({"1U":"8x"}).14(d(f.1i.Q)).1H(a(C.2S=1b.1w("2D")).1v(f.1i.Q))).14(d(f.1i.Q)),p.2O(C.2S),e=C.2S.2Y("2d"),e.2i=C.1Z;1E(L f=g+1,o=0;o<=g;o++)e.2j=s.2k(C.I.1q,u.3P(o*(1/f))*(C.I.1o/f)),p.69(e,{G:c.X.Q.G+o*2,K:c.X.Q.K+o*2,J:k-o,H:l-o,12:m+o});T(b.I.W){L o=h.2e[0].Q,r=b.I.W,g=n;g+=r.G*.5;L t=b.I.12&&b.I.12.P=="X"?b.I.12.2c||0:0;t&&(g+=t),n=n+t+.5*r.G-.5*o.G,m=N.1n(m>n?m-n:0),g+=N.1r(m,b.I.W.1h&&b.I.W.1h[j&&/^(H|1C)$/.4r(j)?"y":"x"]||0);T(j=="J"||j=="1B"){1u(i){R"3f":R"3G":l+=g;1a;R"3B":R"4s":R"3F":R"4u":l+=c.X.Q.G*.5;1a;R"3g":R"3E":l+=c.X.Q.G-g}j=="1B"&&(k+=c.X.Q.K),o=0;1E(b=h.2e.1x;o<b;o++)e.2j=s.2k(C.I.1q,u.3P(o*(1/f))*(C.I.1o/f)),g=h.2e[o],e.1L(),j=="J"?(e.2P(l,k-o),e.O(l-g.Q.G*.5,k-o),e.O(l,k-o-g.Q.K),e.O(l+g.Q.G*.5,k-o)):(e.2P(l,k+o),e.O(l-g.Q.G*.5,k+o),e.O(l,k+o+g.Q.K),e.O(l+g.Q.G*.5,k+o)),e.1M(),e.2u()}1K{1u(i){R"3i":R"3h":k+=g;1a;R"3I":R"4v":R"3C":R"4t":k+=c.X.Q.K*.5;1a;R"3H":R"3D":k+=c.X.Q.K-g}j=="1C"&&(l+=c.X.Q.G),o=0;1E(b=h.2e.1x;o<b;o++)e.2j=s.2k(C.I.1q,u.3P(o*(1/f))*(C.I.1o/f)),g=h.2e[o],e.1L(),j=="H"?(e.2P(l-o,k),e.O(l-o,k-g.Q.G*.5),e.O(l-o-g.Q.K,k),e.O(l-o,k+g.Q.G*.5)):(e.2P(l+o,k),e.O(l+o,k-g.Q.G*.5),e.O(l+o+g.Q.K,k),e.O(l+o,k+g.Q.G*.5)),e.1M(),e.2u()}}},8y:D(){L b=C.2H(),c=u.3l.32(C),e=c.2F.Q;q.5d(b.1z);L f=q.2l(b.1z),g=N.1r(e.G,e.K),b=g/2;g/=2,f={G:e[f=="24"?"K":"G"],K:e[f=="24"?"G":"K"]},a(C.1i).1H(a(C.W=1b.1w("1O")).1v({"1U":"8z"}).14(d(f)).1H(a(C.5u=1b.1w("2D")).1v(f))),p.2O(C.5u),f=C.5u.2Y("2d"),f.2i=C.1Z,f.2j=s.2k(C.I.1q,C.5t());1E(L h=0,i=c.2e.1x;h<i;h++){L j=c.2e[h];f.1L(),f.2P(e.G/2-b,j.P.J-g),f.O(j.P.H-b,e.K-h-g),f.O(j.P.H+j.Q.G-b,e.K-h-g),f.1M(),f.2u()}},4m:D(){L b=C.1Y(),c=C.2H(),e=C.1Q();a(e.V).14(d(b.1e.Q)),a(e.4l).14(a.Y(d(b.1G.P),d(b.1G.Q)));T(e.I.1c){L f=c.1Y(),g=b.1G.P,h=f.1c.P;a(c.1c).14(d({J:g.J+h.J,H:g.H+h.H})),e.I.1c.15&&(f=f.2x.P,a(c.2x.V).14(d({J:g.J+f.J,H:g.H+f.H})))}a(C.V).14(a.Y(d(b.V.Q),d(b.V.P))),a(C.1i).14(d(b.1i.Q)),a(e.2T).14(d(b.21.P))}}}());L v={2y:[],17:D(b){T(!b)M 1g;L c=1g;M a.19(C.2y,D(a,d){d.S==b&&(c=d)}),c},2J:D(a){C.2y.1T(a)},1f:D(a){T(a=C.17(a))C.2y=m.3t(C.2y,a),a.1f()}};a.Y(i.3a,D(){M{1s:D(){C.2G(),C.1Q();L b=C.2H(),c=b.1Y().1c.Q,d=a.Y({},c),e=C.I.2B;d.G+=e*2,d.K+=e*2,a(b.1c).5v(a(C.V=1b.1w("1O")).1v({"1U":"8A"}).1H(a(C.5w=1b.1w("2D")).1v(d))),p.2O(C.5w),b=C.5w.2Y("2d"),b.2i=C.1Z;1E(L g=d.G/2,d=d.K/2,c=c.K/2,h=e+1,i=0;i<=e;i++)b.2j=s.2k(C.I.1q,u.3P(i*(1/h))*(C.I.1o/h)),b.1L(),b.1J(g,d,c+i,f(0),f(6l),!0),b.1M(),b.2u()},1f:D(){C.2G()},2G:D(){C.V&&(a(C.V).1f(),C.V=1g)},1Q:D(){M w.17(C.S)[0]},2H:D(){M t.17(C.S)},5t:D(){M C.I.1o/(C.I.2B+1)}}}());L w={25:[],I:{3m:"5x",3X:8B},68:D(){M D(){L b=["2f"];1A.2N.58&&(b.1T("8C"),a(1b.4a).3k("2f",D(){})),a.19(b,D(b,c){a(1b.6B).3k(c,D(b){L c=m.4X(b,".3n .6s, .3n .8D");c&&(b.8E(),b.8F(),w.6C(a(c).4Y(".3n")[0]).1m())})})}}(),17:D(b){L c=[];M m.20(b)?a.19(C.25,D(a,d){d.S==b&&c.1T(d)}):a.19(C.25,D(d,e){e.S&&a(e.S).6D(b)&&c.1T(e)}),c},6C:D(b){T(!b)M 1g;L c=1g;M a.19(C.25,D(a,d){d.1j("1s")&&d.V===b&&(c=d)}),c},8G:D(b){L c=[];M a.19(C.25,D(d,e){e.S&&a(e.S).6D(b)&&c.1T(e)}),c},1t:D(b){m.20(b)?(b=C.17(b)[0])&&b.1t():a(b).19(a.1d(D(a,b){L c=C.17(b)[0];c&&c.1t()},C))},1m:D(b){m.20(b)?(b=C.17(b)[0])&&b.1m():a(b).19(a.1d(D(a,b){L c=C.17(b)[0];c&&c.1m()},C))},2E:D(b){m.20(b)?(b=C.17(b)[0])&&b.2E():a(b).19(a.1d(D(a,b){L c=C.17(b)[0];c&&c.2E()},C))},4f:D(){a.19(C.3x(),D(a,b){b.1m()})},2t:D(b){m.20(b)?(b=C.17(b)[0])&&b.2t():a(b).19(a.1d(D(a,b){L c=C.17(b)[0];c&&c.2t()},C))},3x:D(){L b=[];M a.19(C.25,D(a,c){c.1k()&&b.1T(c)}),b},5b:D(a){M m.20(a)?m.42(C.3x()||[],D(b){M b.S==a}):!1},1k:D(){M m.4U(C.25,D(a){M a.1k()})},6E:D(){L b=0,c;M a.19(C.25,D(a,d){d.1S>b&&(b=d.1S,c=d)}),c},6F:D(){C.3x().1x<=1&&a.19(C.25,D(b,c){c.1j("1s")&&!c.I.1S&&a(c.V).14({1S:c.1S=+w.I.3X})})},2J:D(a){C.25.1T(a)},5y:D(a){T(a=C.17(a)[0])a.1m(),a.1f(),C.25=m.3t(C.25,a)},1f:D(b){m.20(b)?C.5y(b):a(b).19(a.1d(D(a,b){C.5y(b)},C)),C.6G()},6G:D(){a.19(C.25,a.1d(D(a,b){b.S&&!m.S.4Z(b.S)&&C.1f(b.S)},C))},59:D(a){C.I.3m=a||"5x"},5a:D(a){C.I.3X=a||0},5N:D(){D b(b){M a.13(b)=="1V"?{S:f.1I&&f.1I.S||e.1I.S,26:b}:j(a.Y({},e.1I),b)}D c(b){M e=1A.2n.6H,f=j(a.Y({},e),1A.2n.5z),g=1A.2n.5A.6H,h=j(a.Y({},g),1A.2n.5A.5z),c=d,d(b)}D d(c){c.1G=c.1G||(1A.2n[w.I.3m]?w.I.3m:"5x");L d=c.1G?a.Y({},1A.2n[c.1G]||1A.2n[w.I.3m]):{},d=j(a.Y({},f),d),d=j(a.Y({},d),c);d.1D&&(a.13(d.1D)=="3Q"&&(d.1D={3R:f.1D&&f.1D.3R||e.1D.3R,13:f.1D&&f.1D.13||e.1D.13}),d.1D=j(a.Y({},e.1D),d.1D)),d.X&&a.13(d.X)=="1V"&&(d.X={1q:d.X,1o:1});T(d.U){L i;i=a.13(d.U)=="2b"?{2c:d.U,1q:f.U&&f.U.1q||e.U.1q,1o:f.U&&f.U.1o||e.U.1o}:j(a.Y({},e.U),d.U),d.U=i.2c===0?!1:i}d.12&&(i=a.13(d.12)=="2b"?{2c:d.12,P:f.12&&f.12.P||e.12.P}:j(a.Y({},e.12),d.12),d.12=i.2c===0?!1:i),i=i=d.11&&d.11.1l||a.13(d.11)=="1V"&&d.11||f.11&&f.11.1l||a.13(f.11)=="1V"&&f.11||e.11&&e.11.1l||e.11;L k=d.11&&d.11.1e||f.11&&f.11.1e||e.11&&e.11.1e||w.2a.6I(i);d.11?a.13(d.11)=="1V"?i={1l:d.11,1e:w.2a.6J(d.11)}:(i={1e:k,1l:i},d.11.1e&&(i.1e=d.11.1e),d.11.1l&&(i.1l=d.11.1l)):i={1e:k,1l:i},d.11=i,d.1l=="2q"?(k=a.Y({},e.1h.2q),a.Y(k,1A.2n.5z.1h||{}),c.1G&&a.Y(k,(1A.2n[c.1G]||1A.2n[w.I.3m]).1h||{}),k=w.2a.6K(e.1h.2q,e.11,i.1l),c.1h&&(k=a.Y(k,c.1h||{})),d.3o=0):k={x:d.1h.x,y:d.1h.y},d.1h=k;T(d.1c&&d.6L){L c=a.Y({},1A.2n.5A[d.6L]),l=j(a.Y({},h),c);l.22&&a.19(["5q","5r"],D(b,c){L d=l.22[c],e=h.22&&h.22[c];T(d.X){L f=e&&e.X;a.13(d.X)=="2b"?d.X={1q:f&&f.1q||g.22[c].X.1q,1o:d.X}:a.13(d.X)=="1V"?(f=f&&a.13(f.1o)=="2b"&&f.1o||g.22[c].X.1o,d.X={1q:d.X,1o:f}):d.X=j(a.Y({},g.22[c].X),d.X)}d.U&&(e=e&&e.U,d.U=a.13(d.U)=="2b"?{1q:e&&e.1q||g.22[c].U.1q,1o:d.U}:j(a.Y({},g.22[c].U),d.U))}),l.15&&(c=h.15&&h.15.38&&h.15.38==4L?h.15:g.15,l.15.38&&l.15.38==4L&&(c=j(c,l.15)),l.15=c),d.1c=l}d.15&&(c=a.13(d.15)=="3Q"?f.15&&a.13(f.15)=="3Q"?e.15:f.15?f.15:e.15:j(a.Y({},e.15),d.15||{}),a.13(c.1h)=="2b"&&(c.1h={x:c.1h,y:c.1h}),d.15=c),d.W&&(c={},c=a.13(d.W)=="3Q"?j({},e.W):j(j({},e.W),a.Y({},d.W)),a.13(c.1h)=="2b"&&(c.1h={x:c.1h,y:c.1h}),d.W=c),d.27&&(a.13(d.27)=="1V"?d.27={4w:d.27,6M:!0}:a.13(d.27)=="3Q"&&(d.27=d.27?{4w:"6N",6M:!0}:!1)),d.1I&&d.1I=="2f-8H"&&(d.6O=!0,d.1I=!1);T(d.1I)T(a.6b(d.1I)){L m=[];a.19(d.1I,D(a,c){m.1T(b(c))}),d.1I=m}1K d.1I=[b(d.1I)];M d.2o&&a.13(d.2o)=="1V"&&(d.2o=[""+d.2o]),d.1X=0,d.1p&&(1F.2X?o.57("2X"):d.1p=!1),d}L e,f,g,h;M c}()};w.2a=D(){D b(b,c){L d=q.2r(b),e=d[1],d=d[2],f=q.2l(b),g=a.Y({1y:!0,24:!0},c||{});M f=="1y"?(g.24&&(e=k[e]),g.1y&&(d=k[d])):(g.24&&(d=k[d]),g.1y&&(e=k[e])),e+d}D c(b,c){T(b.I.27){L d=c,e=j(b),f=e.Q,e=e.P,g=t.17(b.S).Z.11[d.11.1e].1e.Q,h=d.P;e.H>h.H&&(d.P.H=e.H),e.J>h.J&&(d.P.J=e.J),e.H+f.G<h.H+g.G&&(d.P.H=e.H+f.G-g.G),e.J+f.K<h.J+g.K&&(d.P.J=e.J+f.K-g.K),c=d}b.3M(c.11.1e),d=c.P,a(b.V).14({J:d.J+"28",H:d.H+"28"})}D d(a){M a&&(/^2q|2f|58$/.4r(6P a.13=="1V"&&a.13||"")||a.5V>=0)}D e(a,b,c,d){L e=a>=c&&a<=d,f=b>=c&&b<=d;M e&&f?b-a:e&&!f?d-a:!e&&f?b-c:(e=c>=a&&c<=b,f=d>=a&&d<=b,e&&f?d-c:e&&!f?b-c:!e&&f?d-a:0)}D f(a,b){L c=a.Q.G*a.Q.K;M c?e(a.P.H,a.P.H+a.Q.G,b.P.H,b.P.H+b.Q.G)*e(a.P.J,a.P.J+a.Q.K,b.P.J,b.P.J+b.Q.K)/c:0}D g(a,b){L c=q.2r(b),d={H:0,J:0};T(q.2l(b)=="1y"){1u(c[2]){R"2v":R"2w":d.H=.5*a.G;1a;R"1C":d.H=a.G}c[1]=="1B"&&(d.J=a.K)}1K{1u(c[2]){R"2v":R"2w":d.J=.5*a.K;1a;R"1B":d.J=a.K}c[1]=="1C"&&(d.H=a.G)}M d}D h(b){L c=m.S.49(b),b=m.S.44(b),d=a(1F).46(),e=a(1F).47();M c.H+=-1*(b.H-e),c.J+=-1*(b.J-d),c}D i(c,e,i,k){L n,o,p=t.17(c.S),r=p.I.1h,s=d(i);s||!i?(o={G:1,K:1},s?(n=m.43(i),n={J:n.y,H:n.x}):(n=c.Z.26,n={J:n?n.y:0,H:n?n.x:0}),c.Z.26={x:n.H,y:n.J}):(n=h(i),o={G:a(i).6Q(),K:a(i).6R()});T(p.I.W&&p.I.1l!="2q"){L i=q.2r(k),v=q.2r(e),w=q.2l(k),z=p.Z.I,B=p.3O().U.Q,E=z.12,z=z.U,F=E&&p.I.12.P=="X"?E:0,E=E&&p.I.12.P=="U"?E:E+z,B=z+F+.5*p.I.W.G-.5*B.G,B=N.1n(z+F+.5*p.I.W.G+(E>B?E-B:0)+p.I.W.1h[w=="1y"?"x":"y"]);T(w=="1y"&&i[2]=="H"&&v[2]=="H"||i[2]=="1C"&&v[2]=="1C")o.G-=B*2,n.H+=B;1K T(w=="24"&&i[2]=="J"&&v[2]=="J"||i[2]=="1B"&&v[2]=="1B")o.K-=B*2,n.J+=B}i=a.Y({},n),p=s?b(p.I.11.1e):p.I.11.1l,g(o,p),s=g(o,k),n={H:n.H+s.H,J:n.J+s.J},r=a.Y({},r),r=l(r,p,k),n.J+=r.y,n.H+=r.x,p=t.17(c.S),r=p.Z.11,s=a.Y({},r[e]),n={J:n.J-s.1W.J,H:n.H-s.1W.H},s.1e.P=n,s={1y:!0,24:!0};T(c.I.27){T(v=j(c),c=(c.I.15?u.17(c.S):p).1Y().1e.Q,s.2z=f({Q:c,P:n},v),s.2z<1){T(n.H<v.P.H||n.H+c.G>v.P.H+v.Q.G)s.1y=!1;T(n.J<v.P.J||n.J+c.K>v.P.J+v.Q.K)s.24=!1}}1K s.2z=1;M c=r[e].1i,o=f({Q:o,P:i},{Q:c.Q,P:{J:n.J+c.P.J,H:n.H+c.P.H}}),{P:n,2z:{1l:o},3S:s,11:{1e:e,1l:k}}}D j(b){L c={J:a(1F).46(),H:a(1F).47()},d=b.I.1l;T(d=="2q"||d=="40")d=b.S;d=a(d).4Y(b.I.27.4w).6q()[0];T(!d||b.I.27.4w=="6N")M{Q:{G:a(1F).G(),K:a(1F).K()},P:c};L b=m.S.49(d),e=m.S.44(d);M b.H+=-1*(e.H-c.H),b.J+=-1*(e.J-c.J),{Q:{G:a(d).6S(),K:a(d).6T()},P:b}}L k={H:"1C",1C:"H",J:"1B",1B:"J",2v:"2v",2w:"2w"},l=D(){L a=[[-1,-1],[0,-1],[1,-1],[-1,0],[0,0],[1,0],[-1,1],[0,1],[1,1]],b={3i:0,3f:0,3B:1,4s:1,3g:2,3h:2,3C:5,4t:5,3D:8,3E:8,3F:7,4u:7,3G:6,3H:6,3I:3,4v:3};M D(c,d,e){L f=a[b[d]],g=a[b[e]],f=[N.5j(N.2m(f[0]-g[0])*.5)?-1:1,N.5j(N.2m(f[1]-g[1])*.5)?-1:1];M!q.2Q(d)&&q.2Q(e)&&(q.2l(e)=="1y"?f[0]=0:f[1]=0),{x:f[0]*c.x,y:f[1]*c.y}}}();M{17:i,6U:D(a,d,e,g){L h=i(a,d,e,g);/8I$/.4r(e&&6P e.13=="1V"?e.13:"");T(h.3S.2z===1)c(a,h);1K{L j=d,k=g,k={1y:!h.3S.1y,24:!h.3S.24};T(!q.2Q(d))M j=b(d,k),k=b(g,k),h=i(a,j,e,k),c(a,h),h;T(q.2l(d)=="1y"&&k.24||q.2l(d)=="24"&&k.1y)M j=b(d,k),k=b(g,k),h=i(a,j,e,k),c(a,h),h;d=[],g=q.3A,j=0;1E(k=g.1x;j<k;j++)1E(L l=g[j],m=0,n=q.3A.1x;m<n;m++)d.1T(i(a,q.3A[m],e,l));1E(L e=h,o=t.17(a.S).Z.11,j=o[e.11.1e],g=0,p=e.P.H+j.1W.H,r=e.P.J+j.1W.J,n=0,s=1,u={Q:j.1e.Q,P:e.P},v=0,j=1,k=0,l=d.1x;k<l;k++){m=d[k],m.2I={},m.2I.27=m.3S.2z;L w=o[m.11.1e].1W,w=N.6z(N.4d(N.2m(m.P.H+w.H-p),2)+N.4d(N.2m(m.P.J+w.J-r),2)),g=N.1r(g,w);m.2I.6V=w,w=m.2z.1l,s=N.5i(s,w),n=N.1r(n,w),m.2I.6W=w,w=f(u,{Q:o[m.11.1e].1e.Q,P:m.P}),j=N.5i(j,w),v=N.1r(v,w),m.2I.6X=w}1E(L o=0,y,n=N.1r(e.2z.1l-s,n-e.2z.1l),s=v-j,k=0,l=d.1x;k<l;k++)m=d[k],v=m.2I.27*51,v+=(1-m.2I.6V/g)*18||18,p=N.2m(e.2z.1l-m.2I.6W)||0,v+=(1-(p/n||1))*8,v+=((m.2I.6X-j)/s||0)*23,o=N.1r(o,v),v==o&&(y=k);c(a,d[y])}M h},6I:b,6J:D(a){M a=q.2r(a),b(a[1]+k[a[2]])},6Y:h,6K:l,5B:d}}(),w.2a.4g={x:0,y:0},a(1b).67(D(){a(1b).3k("4x",D(a){w.2a.4g=m.43(a)})}),w.4n=D(){D b(b){M{G:a(b).6S(),K:a(b).6T()}}D c(c){L d=b(c),e=c.48;M e&&a(e).14({G:d.G+"28"})&&b(c).K>d.K&&d.G++,a(e).14({G:"5h%"}),d}M{1s:D(){a(1b.4a).1H(a(1b.1w("1O")).1v({"1U":"8J"}).1H(a(1b.1w("1O")).1v({"1U":"3n"}).1H(a(C.V=1b.1w("1O")).1v({"1U":"6Z"}))))},3p:D(b,c,d,e){C.V||C.1s(),e=a.Y({1p:!1},e||{}),(b.I.70||m.20(c))&&!a(c).2p("71")&&(b.I.70&&a.13(c)=="1V"&&(c=a("#"+c)[0]),!b.3q&&c&&m.S.4Z(c))&&(a(c).2p("72",a(c).14("73")),b.3q=1b.1w("1O"),a(c).5v(a(b.3q).1m()));L f=1b.1w("1O");a(C.V).1H(a(f).1v({"1U":"6p 8K"}).1H(c)),m.20(c)&&a(c).1t(),b.I.1G&&a(f).3r("8L"+b.I.1G);L g=a(f).5m("74[4y]").8M(D(){M!a(C).1v("K")||!a(C).1v("G")});T(g.1x>0&&!b.1j("39")){b.1P("39",!0),b.I.1p&&(!e.1p&&!b.1p&&(b.1p=b.5C(b.I.1p)),b.1j("1k")&&(b.P(),a(b.V).1t()),b.1p.5D());L h=0,c=N.1r(8N,(g.1x||0)*8O);b.1R("39"),b.3s("39",a.1d(D(){g.19(D(){C.5E=D(){}}),h>=g.1x||(C.4z(b,f),d&&d())},C),c),a.19(g,a.1d(D(c,e){L i=2Z 8P;i.5E=a.1d(D(){i.5E=D(){},a(e).1v({G:i.G,K:i.K}),h++,h==g.1x&&(b.1R("39"),b.1p&&(b.1p.1f(),b.1p=1g),b.1j("1k")&&a(b.V).1m(),C.4z(b,f),d&&d())},C),i.4y=e.4y},C))}1K C.4z(b,f),d&&d()},4z:D(b,d){L e=c(d),f=e.G-(2s(a(d).14("1X-H"))||0)-(2s(a(d).14("1X-1C"))||0);2s(a(d).14("1X-J")),2s(a(d).14("1X-1B")),b.I.2R&&a.13(b.I.2R)=="2b"&&f>b.I.2R&&(a(d).14({G:b.I.2R+"28"}),e=c(d)),b.Z.2K=e,a(b.2T).75(d)},5o:c}}(),a.Y(k.3a,D(){M{1s:D(){C.1j("1s")||(a(1b.4a).1H(a(C.V).14({H:"-4A",J:"-4A",1S:C.1S}).1H(a(C.4l=1b.1w("1O")).1v({"1U":"8Q"})).1H(a(C.2T=1b.1w("1O")).1v({"1U":"6Z"}))),a(C.V).3r("8R"+C.I.1G),C.I.6O&&(a(C.S).3r("76"),C.2g(1b.6B,"2f",a.1d(D(a){C.1k()&&(a=m.4X(a,".3n, .76"),(!a||a&&a!=C.V&&a!=C.S)&&C.1m())},C))),1A.2N.3v&&(C.I.3T||C.I.3o)&&(C.4B(C.I.3T),a(C.V).3r("5F")),C.77(),C.1P("1s",!0))},5P:D(){a(C.V=1b.1w("1O")).1v({"1U":"3n"})},78:D(){C.1s();L a=t.17(C.S);a?a.1s():(2Z g(C.S),C.1P("3Z",!0))},5Q:D(){C.2g(C.S,"3N",C.4C),C.2g(C.S,"4o",a.1d(D(a){C.5G(a)},C)),C.I.2o&&a.19(C.I.2o,a.1d(D(b,c){L d=!1;c=="2f"&&(d=C.I.1I&&m.42(C.I.1I,D(a){M a.S=="40"&&a.26=="2f"}),C.1P("4P",d)),C.2g(C.S,c,c=="2f"?d?C.2E:C.1t:a.1d(D(){C.79()},C))},C)),C.I.1I?a.19(C.I.1I,a.1d(D(b,c){L d;1u(c.S){R"40":T(C.1j("4P")&&c.26=="2f")M;d=C.S;1a;R"1l":d=C.1l}d&&C.2g(d,c.26,c.26=="2f"?C.1m:a.1d(D(){C.5H()},C))},C)):C.I.7a&&C.I.2o&&!a.5I(C.I.2o,"2f")>-1&&C.2g(C.S,"4o",a.1d(D(){C.1R("1t")},C));L b=!1;!C.I.8S&&C.I.2o&&((b=a.5I("4x",C.I.2o)>-1)||a.5I("7b",C.I.2o)>-1)&&C.1l=="2q"&&C.2g(C.S,b?"4x":"7b",D(a){C.1j("3Z")&&C.P(a)})},77:D(){C.2g(C.V,"3N",C.4C),C.2g(C.V,"4o",C.5G),C.2g(C.V,"3N",a.1d(D(){C.4D("3U")||C.1t()},C)),C.I.1I&&a.19(C.I.1I,a.1d(D(b,c){L d;1u(c.S){R"1e":d=C.V}d&&C.2g(d,c.26,c.26.2M(/^(2f|4x|3N)$/)?C.1m:a.1d(D(){C.5H()},C))},C))},1t:D(b){C.1R("1m"),C.1R("3U");T(!C.1k()){T(a.13(C.21)=="D"||a.13(C.Z.4E)=="D"){a.13(C.Z.4E)!="D"&&(C.Z.4E=C.21);L c=C.Z.4E(C.S)||!1;c!=C.Z.4Q&&(C.Z.4Q=c,C.1P("2U",!1),C.5J()),C.21=c;T(!c)M}C.I.8T&&w.4f(),C.1P("1k",!0),C.I.1D?C.7c(b):C.1j("2U")||C.3p(C.21),C.1j("3Z")&&C.P(b),C.4F(),C.I.4G&&m.4W(a.1d(D(){C.4C()},C)),a.13(C.I.4H)=="D"&&(!C.I.1D||C.I.1D&&C.I.1D.3R&&C.1j("2U"))&&C.I.4H(C.2T.4I,C.S),1A.2N.3v&&(C.I.3T||C.I.3o)&&(C.4B(C.I.3T),a(C.V).3r("7d").7e("5F")),a(C.V).1t()}},1m:D(){C.1R("1t"),C.1j("1k")&&(C.1P("1k",!1),1A.2N.3v&&(C.I.3T||C.I.3o)?(C.4B(C.I.3o),a(C.V).7e("7d").3r("5F"),C.3s("3U",a.1d(C.5K,C),C.I.3o)):C.5K(),C.Z.29)&&(C.Z.29.7f(),C.Z.29=1g,C.1P("29",!1))},5K:D(){C.1j("1s")&&(a(C.V).14({H:"-4A",J:"-4A"}),w.6F(),C.7g(),a.13(C.I.7h)=="D"&&!C.1p)&&C.I.7h(C.2T.4I,C.S)},2E:D(a){C[C.1k()?"1m":"1t"](a)},1k:D(){M C.1j("1k")},79:D(b){C.1R("1m"),C.1R("3U"),!C.1j("1k")&&!C.4D("1t")&&C.3s("1t",a.1d(D(a){C.1R("1t"),C.1t(a)},C,b),C.I.7a||1)},5H:D(){C.1R("1t"),!C.4D("1m")&&C.1j("1k")&&C.3s("1m",a.1d(D(){C.1R("1m"),C.1R("3U"),C.1m()},C),C.I.8U||1)},4B:D(a){T(1A.2N.3v){L a=a||0,b=C.V.8V;b.8W=a+"4J",b.8X=a+"4J",b.8Y=a+"4J",b.8Z=a+"4J"}},1P:D(a,b){C.Z.22[a]=b},1j:D(a){M C.Z.22[a]},4C:D(){C.1P("3Y",!0),C.1j("1k")&&C.4F(),C.I.4G&&C.1R("5L")},5G:D(){C.1P("3Y",!1),C.I.4G&&C.3s("5L",a.1d(D(){C.1R("5L"),C.1j("3Y")||C.1m()},C),C.I.4G)},4D:D(a){M C.Z.2L[a]},3s:D(a,b,c){C.Z.2L[a]=m.4V(b,c)},1R:D(a){C.Z.2L[a]&&(1F.7i(C.Z.2L[a]),91 C.Z.2L[a])},7j:D(){a.19(C.Z.2L,D(a,b){1F.7i(b)}),C.Z.2L=[]},2g:D(b,c,d,e){d=a.1d(d,e||C),C.Z.4O.1T({S:b,7k:c,7l:d}),a(b).3k(c,d)},7m:D(){a.19(C.Z.4O,D(b,c){a(c.S).7n(c.7k,c.7l)})},3M:D(a){L b=t.17(C.S);b&&b.3M(a)},7g:D(){C.3M(C.I.11.1e)},2t:D(){L a=t.17(C.S);a&&(a.2t(),C.1k()&&C.P())},3p:D(b,c){L d=a.Y({3V:C.I.3V,1p:!1},c||{});C.1s(),C.1j("1k")&&a(C.V).1m(),w.4n.3p(C,b,a.1d(D(){L b=C.1j("1k");b||C.1P("1k",!0),C.78(),b||C.1P("1k",!1),C.1j("1k")&&(a(C.V).1m(),C.P(),C.4F(),a(C.V).1t()),C.1P("2U",!0),d.3V&&d.3V(C.2T.4I,C.S),d.4K&&d.4K()},C),{1p:d.1p})},7c:D(b){C.1j("29")||C.I.1D.3R&&C.1j("2U")||(C.1P("29",!0),C.I.1p&&(C.1p?C.1p.5D():(C.1p=C.5C(C.I.1p),C.1P("2U",!1)),C.P(b)),C.Z.29&&(C.Z.29.7f(),C.Z.29=1g),C.Z.29=a.1D({92:C.21,13:C.I.1D.13,2p:C.I.1D.2p||{},7o:C.I.1D.7o||"75",93:a.1d(D(b){b.94!==0&&C.3p(b.95,{1p:C.I.1p&&C.1p,4K:a.1d(D(){C.1P("29",!1),C.1j("1k")&&C.I.4H&&C.I.4H(C.2T.4I,C.S),C.1p&&(C.1p.1f(),C.1p=1g)},C)})},C)}))},5C:D(b){L c=1b.1w("1O");a(c).2p("71",!0);L e=2X.4e(c,a.Y({},b||{})),b=2X.5f(c);M a(c).14(d(b)),C.3p(c,{3V:!1,4K:D(){e.5D()}}),e},P:D(b){T(C.1k()){L c;T(C.I.1l=="2q"){c=w.2a.5B(b);L d=w.2a.4g;c?d.x||d.y?(C.Z.26={x:d.x,y:d.y},c=1g):c=b:(d.x||d.y?C.Z.26={x:d.x,y:d.y}:C.Z.26||(c=w.2a.6Y(C.S),C.Z.26={x:c.H,y:c.J}),c=1g)}1K c=C.1l;w.2a.6U(C,C.I.11.1e,c,C.I.11.1l);T(b&&w.2a.5B(b)){L d=a(C.V).6Q(),e=a(C.V).6R(),b=m.43(b);c=m.S.49(C.V),b.x>=c.H&&b.x<=c.H+d&&b.y>=c.J&&b.y<=c.J+e&&m.4W(a.1d(D(){C.1R("1m")},C))}}},4F:D(){T(C.1j("1s")&&!C.I.1S){L b=w.6E();b&&b!=C&&C.1S<=b.1S&&a(C.V).14({1S:C.1S=b.1S+1})}},5J:D(){L b=C.21,c;C.3q&&(a.13(C.21)=="1V"&&(b=a("#"+C.21)[0]),(c=a(b).2p("72"))&&a(b).14({73:c}),a(C.3q).5v(b).1f(),C.3q=1g)},1f:D(){C.7m(),C.7j(),C.5J(),a(C.V).5m("74[4y]").7n("96"),t.1f(C.S),C.1j("1s")&&C.V&&(a(C.V).1f(),C.V=1g);L b=a(C.S).2p("4N");b&&(a(C.S).1v("4M",b),a(C.S).2p("4N",1g))}}}()),1A.2O()})(3u)',62,565,'||||||||||||||||||||||||||||||||||||||this|function|||width|left|options|top|height|var|return|Math|lineTo|position|dimensions|case|element|if|border|container|stem|background|extend|_cache||hook|radius|type|css|shadow||get||each|break|document|closeButton|proxy|tooltip|remove|null|offset|bubble|getState|visible|target|hide|ceil|opacity|spinner|color|max|build|show|switch|attr|createElement|length|horizontal|_hookPosition|Tipped|bottom|right|ajax|for|window|skin|append|hideOn|arc|else|beginPath|closePath|round|div|setState|getTooltip|clearTimer|zIndex|push|class|string|anchor|padding|getOrderLayout|_globalAlpha|isElement|content|states||vertical|tooltips|event|containment|px|xhr|Position|number|size||blurs|click|setEvent|180|globalAlpha|fillStyle|hex2fill|getOrientation|abs|Skins|showOn|data|mouse|split|parseInt|refresh|fill|middle|center|closeButtonShadow|shadows|overlap|PI|blur|scripts|canvas|toggle|box|cleanup|getSkin|score|add|contentDimensions|timers|match|support|init|moveTo|isCenter|maxWidth|bubbleCanvas|contentElement|updated|call|indexOf|Spinners|getContext|new|charAt|toLowerCase|getLayout|diameter|layout|stemLayout|hookPosition|cornerOffset|constructor|preloading_images|prototype|x1|y1|x2|y2|topleft|topright|righttop|lefttop|math|bind|Stem|defaultSkin|t_Tooltip|fadeOut|update|inlineMarker|addClass|setTimer|without|jQuery|cssTransitions|G_vmlCanvasManager|getVisible|items|createFillStyle|positions|topmiddle|rightmiddle|rightbottom|bottomright|bottommiddle|bottomleft|leftbottom|leftmiddle|regex|getBorderDimensions|skins|setHookPosition|mouseenter|getStemLayout|transition|boolean|cache|contained|fadeIn|fadeTransition|afterUpdate|000|startingZIndex|active|skinned|self|arguments|any|pointer|cumulativeScrollOffset||scrollTop|scrollLeft|parentNode|cumulativeOffset|body|required|available|pow|create|hideAll|mouseBuffer|getCenterBorderDimensions|cos|substring|prepare|skinElement|order|UpdateQueue|mouseleave|rotate|borderRadius|test|topcenter|rightcenter|bottomcenter|leftcenter|selector|mousemove|src|_updateTooltip|10000px|setFadeDuration|setActive|getTimer|contentFunction|raise|hideAfter|onShow|firstChild|ms|callback|Object|title|tipped_restore_title|events|toggles|fnCallContent|apply|try|catch|select|delay|defer|findElement|closest|isAttached|||IE|Opera|opera||Chrome|check|touch|setDefaultSkin|setStartingZIndex|isVisibleByElement|undefined|isCorner|getSide|getDimensions|getBubbleLayout|100|min|floor|hoverCloseButton|defaultCloseButton|find|auto|getMeasureElementDimensions|drawCloseButtonState|default|hover|_drawBackgroundPath|getBlurOpacity|stemCanvas|before|closeButtonCanvas|black|_remove|reset|CloseButtons|isPointerEvent|insertSpinner|play|onload|t_hidden|setIdle|hideDelayed|inArray|_restoreInlineContent|_hide|idle|in|createOptions|getAttribute|_preBuild|createPreBuildObservers|Array|concat|_each|member|pageX|RegExp|parseFloat|version|AppleWebKit|Gecko|Za|checked|notified|alert|requires|createEvent|ready|startDelegating|drawRoundedRectangle|fillRect|isArray|Gradient|addColorStops|toOrientation|side|toDimension|atan|red|green|blue|360|createHookCache|drawBubble|drawCloseButton|t_ContentContainer|first|25000px|t_Close|closeButtonShift|closeButtonMouseover|closeButtonMouseout|_drawBorderPath|backgroundRadius|setGlobalAlpha|sqrt|drawBackground|documentElement|getByTooltipElement|is|getHighestTooltip|resetZ|removeDetached|base|getInversedPosition|getTooltipPositionFromTarget|adjustOffsetBasedOnHooks|closeButtonSkin|flip|viewport|hideOnClickOutside|typeof|outerWidth|outerHeight|innerWidth|innerHeight|set|distance|targetOverlap|tooltipOverlap|getAbsoluteOffset|t_Content|inline|isSpinner|tipped_restore_inline_display|display|img|html|t_hideOnClickOutside|createPostBuildObservers|_buildSkin|showDelayed|showDelay|touchmove|ajaxUpdate|t_visible|removeClass|abort|resetHookPosition|onHide|clearTimeout|clearTimers|eventName|handler|clearEvents|unbind|dataType|_stemPosition|object|tipped|setAttribute|getElementById|slice|wrap|throw|nodeType|setTimeout|pageY|do|while|exec|attachEvent|MSIE|WebKit|KHTML|rv|MobileSafari|Apple|Mobile|Safari|navigator|userAgent|0_b1|Version|fn|jquery|z_|z0|TouchEvent|WebKitTransitionEvent|TransitionEvent|OTransitionEvent|ExplorerCanvas|excanvas|js|initElement|drawPixelArray|createLinearGradient|addColorStop|spacing|replace|0123456789abcdef|hex2rgb|rgba|join|getSaturatedBW|255|hue|saturation|brightness|fff|init_|t_Bubble|15000px|t_CloseButtonShift|CloseButton|t_CloseState|translate|lineWidth|stemOffset|270|sin|setOpacity|getCenterBorderDimensions2|acos|t_Shadow|prepend|t_ShadowBubble|drawStem|t_ShadowStem|t_CloseButtonShadow|9999|touchstart|close|preventDefault|stopPropagation|getBySelector|outside|move|t_UpdateQueue|t_clearfix|t_Content_|filter|8e3|750|Image|t_Skin|t_Tooltip_|fixed|hideOthers|hideDelay|style|MozTransitionDuration|webkitTransitionDuration|OTransitionDuration|transitionDuration||delete|url|complete|status|responseText|load'.split('|'),0,{}));