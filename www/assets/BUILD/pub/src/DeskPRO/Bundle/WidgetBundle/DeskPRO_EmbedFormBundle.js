import 'babel-polyfill';
import $ from 'jquery';
import factory from 'iframe-resizer';

const runEmbed = function (helpdeskUrl, options, containerEl) {
  const { language = 'en', department = 0, hide_department = 0 } = options;

  const node = document.createElement('iframe');
  node.frameborder = 0;
  node.framespacing = 0;
  node.marginheight = 0;
  node.marginwidth = 0;
  node.allowtransparency = 'true';

  (node.frameElement || node).style.cssText = 'border: none; margin: 0; padding: 0;';

  const langSeg = language && language != '0' ? `/${language}` : '';

  node.src = (function () {
    let src = helpdeskUrl + `/focus-win${langSeg}/new-ticket`;
    if (department) {
      src += `?department_id=${department}`;
      if (hide_department) {
        src += '&hide_department=1';
      }
    }
    return src;
  })();

  const calculatedWidth = function () {
    if (options.width && parseInt(options.width) !== 0 && !isNaN(parseInt(options.width))) {
      return options.width;
    }

    return $(containerEl).width() || 500;
  };

  const updateWidth = function () {
    const w = calculatedWidth();
    node.width = w;
    node.style.width = w + 'px';
  };

  if (!(options.width && options.width !== 0)) {
    window.setInterval(function () {
      updateWidth();
    }, 5000);
    $(window).on('load resize', function () {
      updateWidth();
    });
  }

  updateWidth();
  node.style.minHeight = '300px';

  containerEl.appendChild(node);
  factory.iframeResizer({
    log:         true,
    checkOrigin: false,
    sizeHeight:  true
  }, node);
};

const options = window.DESKPRO_EMBED_OPTIONS;

runEmbed(
  options['helpdeskUrl'],
  options,
  document.getElementById(options['containerId'])
);
