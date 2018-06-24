function hideAll(root, selector) {
  const matched = root.querySelectorAll(selector);
  for (let i = 0; i < matched.length; i++) {
    matched.item(i).style.display = 'none';
  }
}

function showAll(root, selector) {
  const matched = root.querySelectorAll(selector);
  for (let i = 0; i < matched.length; i++) {
    matched.item(i).style.display = 'block';
  }
}

/**
 * A bridge to the legacy app sidebar
 */
class LegacyAppSidebar {
  /**
   * @param {String} selector
   * @return {LegacyAppSidebar}
   */
  static fromSelector(selector) {
    const domRoot = window.document.querySelector(selector);
    return new LegacyAppSidebar(domRoot);
  }

  constructor(domRoot) {
    this.domRoot = domRoot;
  }

  getContentRoot = () => this.domRoot.querySelector('.dp-app-context[data-deskproapp-marker]');

  isLocked = () => this.domRoot.className.match(/(?:^|\s)sidebar-pinned(?!\S)/);

  showLegacyContent = () => {
    hideAll(this.domRoot, '.dp-app-context');
    showAll(this.domRoot, '.dp-app-context:not([data-deskproapp-marker])');
  };

  showContent = () => {
    hideAll(this.domRoot, '.dp-app-context');
    showAll(this.domRoot, '.dp-app-context[data-deskproapp-marker]');
  };

  togglePined() {
    if (this.domRoot.className.match(/(?:^|\s)sidebar-pinned(?!\S)/)) { // is pinned
      this.domRoot.className = this.domRoot.className.replace(/(?:^|\s)sidebar-pinned(?!\S)/g, '');
    } else {
      this.domRoot.className += ' sidebar-pinned';
    }
  }

}

export { LegacyAppSidebar };
