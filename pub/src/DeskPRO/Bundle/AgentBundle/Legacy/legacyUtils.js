import $ from 'jquery';

/**
 * Gets the template contents from a legacy 'template' script tag.
 *
 * @param {String} id
 * @returns {String}
 */
export function getTemplateHtml(id) {
  const el = document.getElementById('dplegacy.' + id);

  if (!el) {
    throw new Error("Invalid id: " + id);
  }

  return el.innerHTML;
}
