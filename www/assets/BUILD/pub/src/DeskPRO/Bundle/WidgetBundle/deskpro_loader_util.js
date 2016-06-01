function getInstInfo(helpdeskUrl, window, document, instId = 'def') {

  helpdeskUrl = helpdeskUrl.replace(/\/+$/, '');

  const loadKey = '_dp_instinfoload_';
  const storagePrefix = `dp${instId}loader`;
  const storageAssetUrlKey = `${storagePrefix}_assets_url`;
  const storageTimeKey = `${storagePrefix}_time`;

  const constAssetUrlKey = 'DESKPRO_ASSETS_URL';

  const loaderState = window[loadKey] || {

      cbs: [],
      hasSent: false,
      handler: function (instInfo) {
        if (instInfo) {
          const alreadyHas = hasVersionInfo();
          updateVersionInfo(instInfo.assetUrl);

          if (!alreadyHas) {
            for (let i = 0; i < window[loadKey]['cbs'].length; i++) {
              window[loadKey]['cbs'][i](getVersionInfo(), window, document, instId);
            }
          }
        }
      }
    };

  if (typeof window[loadKey] == 'undefined') {
    window[loadKey] = loaderState;
  }

  const updateVersionInfo = function (assetUrl) {
    window[constAssetUrlKey] = assetUrl;

    if (window.localStorage) {
      window.localStorage[storageAssetUrlKey] = assetUrl;
      window.localStorage[storageTimeKey] = (new Date()).getTime();
    }
  };

  const getAssetUrl = function () {
    return window.DESKPRO_ASSETS_URL || null;
  };

  const getVersionInfo = function () {
    return {
      assetUrl: getAssetUrl(),
      helpdeskUrl: helpdeskUrl,
      instId: instId
    }
  };

  const hasVersionInfo = function () {
    return !!getAssetUrl();
  };

  const loadVersionInfo = function () {
    if (loaderState.hasSent) {
      return;
    }
    loaderState.hasSent = true;

    const jsonpScript = document.createElement('script');
    jsonpScript.type = 'application/javascript';
    jsonpScript.async = true;
    jsonpScript.src = `${helpdeskUrl}/dyn-assets/inst_info.js?callback=${loadKey}.handler`;
    (document.getElementsByTagName('head')[0] || document.body).appendChild(jsonpScript);
  };

  // See if we have version info in localstorage cache
  // which will speed up the initial paint
  if (
    !hasVersionInfo()
    && window.localStorage
    && window.localStorage[storageAssetUrlKey]
    && window.localStorage[storageTimeKey]
  ) {
    const lastTime = parseInt(window.localStorage[storageTimeKey]) || 0;

    // If the version info we have cached is <24 hours, then lets use it
    if (((new Date()).getTime() - 86400) > lastTime) {
      window[constAssetUrlKey] = window.localStorage[storageAssetUrlKey];

      // But refresh in the bg if its >3 mins old
      if (((new Date()).getTime() - 180) > lastTime) {
        loadVersionInfo();
      }
    }
  }

  if (hasVersionInfo()) {
    return {
      then: function (cb) {
        cb(getVersionInfo(), window, document, instId);
      }
    }
  } else {
    loadVersionInfo();
    return {
      then: function (cb) {
        window[loadKey]['cbs'].push(function(instInfo) {
          cb(instInfo, window, document, instId);
        });
      }
    }
  }
};

function onReadyState(loadFn, window, document) {
  if (document.readyState && (document.readyState === 'complete' || document.readyState === 'interactive')) {
    loadFn();
  } else {
    if (document.addEventListener) {
      document.addEventListener('DOMContentLoaded', loadFn);
    } else if (window.attachEvent) {
      window.attachEvent('onload', loadFn);
    }
  }
}
