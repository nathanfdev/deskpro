import $ from 'jquery';
import factory from 'iframe-resizer';

const runEmbed = (helpdeskUrl, options, containerEl) => {
  const { language = 'en' } = options;

  const node = document.createElement('iframe');
  node.frameborder = 0;
  node.framespacing = 0;
  node.marginheight = 0;
  node.marginwidth = 0;
  node.allowtransparency = 'true';

  (node.frameElement || node).style.cssText = 'border: none; margin: 0; padding: 0;';

  const langSeg = language && `${language}` !== '0' ? `${language}/` : '';

  node.src = helpdeskUrl + (`/frame-embed/${langSeg}`).replace(/\/$/, '');

  const calculatedWidth = () => {
    if (options.width && parseInt(options.width, 10) !== 0 && !isNaN(parseInt(options.width, 10))) {
      return options.width;
    }

    return $(containerEl).width() || 500;
  };

  const updateWidth = () => {
    const w = calculatedWidth();
    node.width = w;
    node.style.width = `${w}px`;
  };

  if (!(options.width && options.width !== 0)) {
    window.setInterval(() => {
      updateWidth();
    }, 5000);
    $(window).on('load resize', () => {
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
  options.helpdeskUrl,
  options,
  document.getElementById(options.containerId)
);
