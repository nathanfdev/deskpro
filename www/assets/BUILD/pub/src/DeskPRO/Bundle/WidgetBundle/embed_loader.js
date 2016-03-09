((helpdeskUrl, options = {}, window, document) => {
  const { language = 'en', dimensions = {}, department = 0 } = options;
  const { width = 500, height = 500 } = dimensions;
  const node = document.createElement('iframe');

  node.src = helpdeskUrl + `focus-win/${language}/new-ticket`;
  if (department) {
    node.src += `ticket[department_id]=${department}`;
  }

  node.width = width;
  node.height = height;
  (node.frameElement || node).style.cssText = 'border: 0';

  window.onload = () => {
    document.body.appendChild(node);
  };
})(__DP_URL__, __DP_OPTIONS__, window, document);
