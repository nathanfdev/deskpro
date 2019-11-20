// eslint-disable-next-line
__webpack_public_path__ = (function() {
  var url;
  if (window.DESKPRO_APP_ASSETS_URL) {
    url = window.DESKPRO_APP_ASSETS_URL;
  } else if (window.DESKPRO_ASSETS_URL) {
    url = window.DESKPRO_ASSETS_URL;
  }

  if (url) {
    url = url.replace(/\/$/, '');

    // in some invocations, the pub build path might not be appended
    if (!url.match('pub/build')) {
      url = url + '/pub/build';
    }

    url = url + '/';
    return url;
  }

  console.warn('Unknown path to assets');
  return '';
})();
