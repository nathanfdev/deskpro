function matches(elem, root, selector) {
  const matched = root.querySelectorAll(selector);

  let i = matched.length;
  let isMatching = false;
  do {
    i -= 1;
    isMatching = elem === matched.item(i);
  } while (i > 0 && !isMatching);

  return  isMatching;
}

/**
 * A bridge to the legacy app icons container
 */
class LegacyAppIcons {
  /**
   * @param {String} selector
   * @return {LegacyAppIcons}
   */
  static fromSelector(selector) {
    const domRoot = window.document.querySelector(selector);
    return new LegacyAppIcons(domRoot);
  }

  constructor(domRoot) {
    this.domRoot = domRoot;
  }

  hasLegacyAppIcons = () => {
    const firstLegacyIcon = this.domRoot.querySelector('li:not([data-deskproapp-marker])');
    return !!firstLegacyIcon;
  };

  isAppIconDOM = domNode => matches(domNode, this.domRoot, 'li[data-deskproapp-marker]')
      || matches(domNode, this.domRoot, 'li[data-deskproapp-marker] *');

  isLegacyAppIconDOM = domNode => matches(domNode, this.domRoot, 'li:not([data-deskproapp-marker])')
      || matches(domNode, this.domRoot, 'li:not([data-deskproapp-marker]) *');

  addAppIcon = (imageUrl) => {
    const markup =
      `<li class="is-enabled" data-deskproapp-marker>
        <img src="${imageUrl}" style="width:48px; height:48px">
      </li>`;
    this.domRoot.insertAdjacentHTML('beforeend', markup);

    return true;
  }
}

export { LegacyAppIcons };
