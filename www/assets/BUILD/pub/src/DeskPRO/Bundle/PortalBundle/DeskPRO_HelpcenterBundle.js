import 'react-hot-loader/patch';
import $ from 'jquery';
import 'bootstrap';
import 'popper.js';

window.replaceSvgImages = function (img) {
  const imgID = img.id;
  const imgClass = img.className;
  const imgURL = img.src;

  fetch(imgURL).then(response => response.text()).then((text) => {
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(text, 'text/xml');

    // Get the SVG tag, ignore the rest
    const svg = xmlDoc.getElementsByTagName('svg')[0];

    // Add replaced image's ID to the new SVG
    if (typeof imgID !== 'undefined') {
      svg.setAttribute('id', imgID);
    }
    // Add replaced image's classes to the new SVG
    if (typeof imgClass !== 'undefined') {
      svg.setAttribute('class', `${imgClass} replaced-svg`);
    }

    // Remove any invalid XML tags as per http://validator.w3.org
    svg.removeAttribute('xmlns:a');

    // Check if the viewport is set, if the viewport is not set the SVG wont't scale.
    if (!svg.getAttribute('viewBox') && svg.getAttribute('height') && svg.getAttribute('width')) {
      svg.setAttribute('viewBox', `0 0 ${svg.getAttribute('height')} ${svg.getAttribute('width')}`);
    }

    // Replace image with new SVG
    img.parentNode.replaceChild(svg, img);
  });
};

const toolOptions = {
  template: '<div class="tooltip dp-po-tip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
};

$('[data-toggle="tooltip"]').tooltip(toolOptions);
$('[data-toggle="popover"]').popover();
$('.dropdown-toggle').dropdown();
