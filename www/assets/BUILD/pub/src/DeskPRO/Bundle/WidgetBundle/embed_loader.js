import 'babel-polyfill';
import factory from 'iframe-resizer';

((helpdeskUrl, options = {}, window, document) => {
  const { language = 'en', width = 500, department = 0 } = options;
  const node = document.createElement('iframe');

  // embed type (full portal or new-ticket)
  node.src = helpdeskUrl + `focus-win/${language}/new-ticket`;
  if (department) {
    node.src += `ticket[department_id]=${department}`;
  }

  // iframe styles
  node.width = width;
  (node.frameElement || node).style.cssText = 'border: 0';

  window.onload = () => {
    document.body.appendChild(node);
    factory.iframeResizer({
      log: true,
      checkOrigin: false,
      sizeHeight: true
    }, node);
  };
})(__DP_URL__, __DP_OPTIONS__, window, document);
