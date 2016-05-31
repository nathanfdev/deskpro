(function (window, document) {
  // #include deskpro_loader_utils.js

  const options = window.DESKPRO_EMBED_OPTIONS;

  getInstInfo(options.helpdeskUrl, window, document, options.instId || 'default').then(function (instInfo, window, document) {

    const appSrc = instInfo.assetUrl + '/pub/build/' + (options.type === 'form' ? 'DeskPRO_EmbedFormBundle.js' : 'DeskPRO_EmbedHelpdeskBundle.js');

    const loadFn = function () {

      const appNode = document.createElement('script');
      appNode.charset = 'UTF8';
      appNode.type = 'application/javascript';
      appNode.src = appSrc;
      (document.getElementsByTagName('head')[0] || document.body).appendChild(appNode);
    };

    onReadyState(loadFn, window, document);
  });

})(window, document);

