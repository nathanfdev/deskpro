function getInstInfo(url, instId = 'def') { // eslint-disable-line no-unused-vars
  const helpdeskUrl = url.replace(/\/+$/, '');

  const loadKey = '_dp_instinfoload_';
  const storagePrefix = `dp${instId}loader`;
  const storageAssetUrlKey = `${storagePrefix}_assets_url`;
  const storageTimeKey = `${storagePrefix}_time`;

  const constAssetUrlKey = 'DESKPRO_ASSETS_URL';

  const updateVersionInfo = (assetUrl) => {
    window[constAssetUrlKey] = assetUrl;

    if (window.localStorage) {
      try {
        window.localStorage[storageAssetUrlKey] = assetUrl;
        window.localStorage[storageTimeKey] = (new Date()).getTime();
      } catch (e) {
        // continue
      }
    }
  };

  const getAssetUrl = () => window.DESKPRO_ASSETS_URL || null;

  const getVersionInfo = () => ({
    assetUrl: getAssetUrl(),
    helpdeskUrl,
    instId
  });

  const hasVersionInfo = () => !!getAssetUrl();

  const loaderState = window[loadKey] || {
    cbs:     [],
    hasSent: false,
    handler(instInfo) {
      if (instInfo) {
        const alreadyHas = hasVersionInfo();
        updateVersionInfo(instInfo.assetUrl);

        if (!alreadyHas) {
          for (let i = 0; i < window[loadKey].cbs.length; i += 1) {
            window[loadKey].cbs[i](getVersionInfo(), window, document, instId);
          }
        }
      }
    }
  };

  if (typeof window[loadKey] === 'undefined') {
    window[loadKey] = loaderState;
  }

  const loadVersionInfo = () => {
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
    const lastTime = parseInt(window.localStorage[storageTimeKey], 10) || 0;

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
      then(cb) {
        cb(getVersionInfo(), window, document, instId);
      }
    };
  }

  loadVersionInfo();
  return {
    then(cb) {
      window[loadKey].cbs.push((instInfo) => {
        cb(instInfo, window, document, instId);
      });
    }
  };
}

function onReadyState(loadFn) {
  if (document.readyState && (document.readyState === 'complete' || document.readyState === 'interactive')) {
    loadFn();
  } else if (document.addEventListener) {
    document.addEventListener('DOMContentLoaded', loadFn);
  } else if (window.attachEvent) {
    window.attachEvent('onload', loadFn);
  }
}

let dpV = null;
function getVisitorId() {
  if (dpV) {
    return dpV;
  }

  const formatRe = /^\d{8,9}-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{6}-[A-Z]{3}$/;

  if (window.DP_VISITOR_ID && window.DP_VISITOR_ID.match(formatRe)) {
    dpV = window.DP_VISITOR_ID;
    return dpV;
  }

  let vid = document.cookie.match('(^|;)\\s*dp__v\\s*=\\s*([^;]+)');
  vid = vid ? vid.pop() : null;

  if (vid && !vid.match(formatRe)) {
    vid = null;
  }

  if (!vid) {
    vid = (() => {
      let id = `${Math.floor((new Date()).getTime() / 1000 / 60)}-`;

      const chars1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      const chars2 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

      for (let i = 0; i < 8; i += 1) {
        id += chars1.charAt(Math.floor(Math.random() * chars1.length));
      }
      id += '-';
      for (let i = 0; i < 8; i += 1) {
        id += chars1.charAt(Math.floor(Math.random() * chars1.length));
      }
      id += '-';
      for (let i = 0; i < 6; i += 1) {
        id += chars1.charAt(Math.floor(Math.random() * chars1.length));
      }
      id += '-';
      for (let i = 0; i < 3; i += 1) {
        id += chars2.charAt(Math.floor(Math.random() * chars2.length));
      }

      return id;
    })();

    const date = new Date();
    date.setTime(date.getTime() + 63072000000);

    document.cookie = `dp__v=${vid};expires=${date.toGMTString()};path=/`;
  }

  dpV = vid;
  window.DP_VISITOR_ID = dpV;
  return vid;
}

function getPageHitProperties(extOpts) {
  const opts = extOpts || {};

  const url = opts.url || window.DP_PAGE_URL || window.location.href;
  const pageTitle = opts.title || window.DP_PAGE_TITLE || document.title || null;
  const referrer = opts.referrer || window.DP_PAGE_REFERRER || document.referrer || null;
  const pageType = opts.pageType || window.DP_PAGE_TYPE || 'page';
  const pageId = opts.pageId || window.DP_PAGE_ID || 'page';
  const meta = opts.meta || window.DP_PAGE_META || {};
  const visitorId = getVisitorId();

  if (!meta.pageTitle && pageTitle) {
    meta.pageTitle = pageTitle;
  }

  return {
    url, pageTitle, referrer, pageType, pageId, meta, visitorId
  };
}

function recordPageHit(helpdeskUrl, opts) { // eslint-disable-line no-unused-vars
  const loadFn = () => {
    const props = getPageHitProperties(opts);

    let dataQs = [];
    dataQs.push(`visitor_id=${encodeURIComponent(props.visitorId)}`);
    dataQs.push(`url=${encodeURIComponent(props.url)}`);

    if (props.referrer) {
      dataQs.push(`referrer=${encodeURIComponent(props.referrer)}`);
    }

    const meta = props.meta;
    for (const k of Object.keys(meta)) {
      if (meta[k]) {
        dataQs.push(`meta[${encodeURIComponent(k)}]=${encodeURIComponent(meta[k])}`);
      }
    }

    dataQs = dataQs.join('&');

    const imgSrc = `${helpdeskUrl}/dp/hit/${props.pageType}/${props.pageId}.gif?${dataQs}`;
    const img = document.createElement('img');
    img.setAttribute('src', imgSrc);
    img.setAttribute('role', 'presentation');
    img.setAttribute('width', 1);
    img.setAttribute('height', 1);
    img.setAttribute('style', 'position:absolute;bottom:0;left:0;width:1px;height:1px;overflow:hidden;border:none;margin:0;padding:0;');

    document.body.appendChild(img);
  };

  onReadyState(loadFn);
}
