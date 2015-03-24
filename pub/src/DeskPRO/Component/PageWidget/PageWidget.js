import _ from "lodash";
import $ from "jquery";

export default class PageWidget {
  constructor(container, element = null, parent = null) {
    this.container = container;

    this.initState = 'pre_init';
    this.waitingRender = false;

    this.element     = element ? $(element) : null;
    this.parent      = parent;
    this.widgetDefs  = [];
    this.widgetInsts = [];

    let initVal = this.init();
    if (initVal && initVal.then) {
      initVal.then(() => {
        this.initState = 'done_init';
        this._runDoneInit();
      });
    } else {
      this.initState = 'done_init';
      this._runDoneInit();
    }
  }

  /**
   * @returns {Container}
   */
  getContainer() {
    return this.container;
  }

  /**
   * @returns {PageWidget}
   */
  getParent() {
    return this.parent;
  }

  /**
   * @returns {jQuery}
   */
  getElement() {
    return this.element;
  }

  /**
   * Init is called in the constructor. Use this to do any kind of setup work like
   * using the container.
   *
   * Should return nothing or a promise.
   *
   * @returns {Promise/undefined}
   */
  init() {
    // Add custom init code in here.
  }

  /**
   * Called to a request to render itself.
   */
  render() {
    let pre = this.preRender();

    if (pre && pre.then) {
      pre.then(() => this.renderWidget());
    } else {
      this.renderWidget();
    }
  }

  /**
   * Put any render init code in here. For example, you might need to load
   * other assets or show a loading indicator.
   *
   * Should return nothing or a promise.
   *
   * @returns {Promise/undefined}
   */
  preRender() {
    // Add custom code here
  }

  /**
   * Put logic to actually render the widget here. This is called after preRender().
   */
  renderWidget() {
    // Add custom code here
  }

  /**
   * Called when a component wants to render the widget, but doesn't know if this is ready yet.
   */
  renderWhenReady() {
    if (this.initState === 'done_init') {
      this.render();
    } else {
      this.waitingRender = true;
    }
  }

  /**
   * @private
   */
  _runDoneInit() {
    if (this.waitingRender) {
      this.render();
    }

    this.widgetDefs.forEach(w => {
      this._runWidgetDef(w);
    });
  }

  /**
   * Adds a child widget to this widget.
   *
   * @param {Function} widgetClass
   * @param {String/Function} selector
   */
  addWidgetDef(widgetClass, selector) {
    let desc = [widgetClass, selector];
    this.widgetDefs.push(desc);

    if (this.initState === 'done_init') {
      this._runWidgetDef(desc)
    }
  }

  /**
   * @param {Array} widgetDef
   * @private
   */
  _runWidgetDef(widgetDef) {
    let insts = this._createWidgetInst(widgetDef);

    insts.forEach(i => {
      this.widgetInsts.push(i);
      i.renderWhenReady();
    });
  }

  /**
   * @param {Array} widgetDef
   * @returns {Array}
   * @private
   */
  _createWidgetInst(widgetDef) {
    let widgetClass = widgetDef[0];
    let selector = widgetDef[1];
    let matches;

    if (_.isFunction(selector)) {
      matches = selector(widgetClass, this);
    } else {
      matches = $(selector);
    }

    if (!matches || !matches[0]) {
      return [];
    }

    let insts = [];
    matches.each((_, el) => {
      insts.push(new widgetClass(this.container, el, this));
    });

    return insts;
  }
}