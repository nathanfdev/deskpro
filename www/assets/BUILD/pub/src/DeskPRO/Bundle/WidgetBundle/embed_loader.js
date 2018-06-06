(function (window, document) {
  // #include deskpro_loader_util.js

  const options = window.DESKPRO_EMBED_OPTIONS;

  getInstInfo(options.helpdeskUrl, options.instId || 'default').then((instInfo) => {
    const script = options.type === 'form' ? 'DeskPRO_EmbedFormBundle.js' : 'DeskPRO_EmbedHelpdeskBundle.js';
    const appSrc = `${instInfo.assetUrl}/pub/build/${script}`;

    const loadFn = () => {
      const appNode = document.createElement('script');
      appNode.charset = 'UTF8';
      appNode.type = 'application/javascript';
      appNode.src = appSrc;
      (document.getElementsByTagName('head')[0] || document.body).appendChild(appNode);
    };

    onReadyState(loadFn);
  });
})(window, document);
