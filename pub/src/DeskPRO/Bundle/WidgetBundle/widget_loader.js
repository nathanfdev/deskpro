(function(appSrc) {
  // Create the iframe loader
  const node = document.createElement('iframe');
  node.src = 'javascript:false';
  node.title = '';
  node.role = 'presentation';
  (node.frameElement || node).style.cssText = "display: none";

  // Insert it into the DOM
  const allScripts = document.getElementsByTagName("script");
  const lastScript = allScripts[allScripts.length - 1];
  lastScript.parentNode.insertBefore(node, lastScript);

  // This try/catch process is requried for proper crossdomain functioning
  // http://calendar.perfplanet.com/2012/the-non-blocking-script-loader-pattern/#crossdomain_issues
  const frameWin = node.contentWindow;
  const frameDoc = frameWin.document;

  let doc, docDomain;
  try {
    doc = frameDoc;
  } catch (c) {
    docDomain = document.domain;
    node.src = 'javascript:var d=document.open();d.domain="' + docDomain + '";void(0);';
    doc = frameDoc;
  }

  // After onload, we load the script source for real
  doc.open()._load = function() {
    const appNode = this.createElement('script');
    if (docDomain) {
      this.domain = docDomain;
    }

    appNode.id = 'dp_loader_iframe';
    appNode.src = appSrc;

    this.body.appendChild(appNode);
  };

  doc.write('<body onload="document._load();"><div id="dp_loader_element"></div>');
  doc.close();
})(__DP_APP_SRC__);
