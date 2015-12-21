((helpdeskUrl, window, document) => {
  const url       = window.location.href;
  const pageTitle = document.title || null;
  const referrer  = document.referrer || null;

  const pageType = typeof DP_PAGE_TYPE != 'undefined' && DP_PAGE_TYPE ? DP_PAGE_TYPE : 'misc';
  const pageId   = typeof DP_PAGE_ID != 'undefined'   && DP_PAGE_ID   ? DP_PAGE_ID   : 'page';
  const meta     = typeof DP_PAGE_META != 'undefined' && DP_PAGE_META ? DP_PAGE_META : {};

  if (!meta.pageTitle && pageTitle) {
    meta.pageTitle = pageTitle;
  }

  var dataQs = [];
  dataQs.push('url=' + encodeURIComponent(url));

  if (referrer) {
    dataQs.push('referrer=' + encodeURIComponent(referrer));
  }

  for (var k in meta) {
    if (meta.hasOwnProperty(k)) {
      if (meta[k]) {
        dataQs.push('meta[' + encodeURIComponent(k) + ']=' + encodeURIComponent(meta[k]));
      }
    }
  }

  dataQs = dataQs.join('&');

  const imgSrc = `${helpdeskUrl}/dp/hit/${pageType}/${pageId}.gif?${dataQs}`;
  const img = document.createElement('img');
  img.src = imgSrc;
  img.role = "presentation";
  img.width = 1;
  img.height = 1;
  img.style = "position:absolute;bottom:0;left:0;visibility:hidden;";
  document.body.appendChild(img);
})(__DP_URL__, window, document);
