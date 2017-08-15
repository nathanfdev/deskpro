import isFunction from 'lodash/isFunction';
import $ from 'jquery';

/**
 * A page widget is something that can be attached to a page. It's a self-contained
 * piece of code that handles logic for specific parts of the page, typically
 * by attaching logic to particular elements (i.e., to replace a dumb form element with an enhanced one).
 *
 * == Lifecycle ==
 *
 * 1) A PageWidget is instantied. This is usually done by OTHER PageWidget's by
 *    a matching widgetDef.
 *
 * 2) The constructor is run (you probably should not change it).
 *
 * 3) init() is called. init() either finishes right away or returns a promise.
 *
 * 4) When init() is done, render() is called.
 *
 * 5) render() calls preRender() which finishes right away or returns a promise.
 *
 * 6) When preRender() is done, renderWidget() is called.
 *
 * == Hook methods ==
 *
 * The following methods are designed for you to 'hook' into:
 *
 * - init() should be where you set up dependencies or assign values etc.
 * - preRender() should be where you set up the main UI for you widget.
 *   You could use this to send network requests and show a loading indicator.
 *   You would return the promise.
 * - renderWidget() is where you would handle when everything is loaded and ready
 *   to show your actual widget. If your widget doesnt need to load anything,
 *   you would probably not need preRender().
 *
 * == Child widgets ==
 *
 * You can register child widgets that are automatically instantiated when they match.
 * The use case is: You can have a main PageWidget that acts at a page level,
 * and have it define all of the widgets that should operate on the page by adding
 * widget defs within its init() method. For example:
 *
 *     init() {
 *         this.addWidgetDef(myWidgetClass, ".my-widget-block");
 *     }
 *
 * Whenever there's an element with class "my-widget-block", a new instance of myWidgetClass will be
 * instantiated on it.
 */
export class PageWidget {

  constructor(element = null, parent = null, options = {}) {
    this.initState = 'pre_init';
    this.waitingRender = false;

    this.$element = element ? $(element) : null;
    this.parent = parent;
    this.options = options;

    this.widgetDefs = [];
    this.widgetInsts = [];

    const initVal = this.init();
    if (initVal && initVal.then) {
      initVal.then(() => {
        this.initState = 'done_init';
        this.runDoneInit();
      });
    } else {
      this.initState = 'done_init';
      this.runDoneInit();
    }
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
    return this.$element;
  }

  /**
   * Init is called in the constructor. Use this to do any kind of setup work like
   * using the container.
   *
   * Should return nothing or a promise.
   *
   * @returns {Promise/undefined}
   */
  init() { // eslint-disable-line class-methods-use-this
    // Add custom init code in sub-classes.
  }

  /**
   * Called to a request to render itself.
   */
  render() {
    let pre;
    try {
      pre = this.preRender();
    } catch (e) {
      console.log('Error in preRender()');
      console.error(e);
      pre = null;
    }

    if (pre && pre.then) {
      pre.then(() => {
        try {
          this.renderWidget();
        } catch (e) {
          console.log('Error in renderWidget()');
          console.error(e);
        }

        this.runWidgets();
      });
    } else {
      try {
        this.renderWidget();
      } catch (e) {
        console.log('Error in renderWidget()');
        console.error(e);
      }

      this.runWidgets();
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
  preRender() { // eslint-disable-line class-methods-use-this
    // Add custom code here
  }

  /**
   * Put logic to actually render the widget here. This is called after preRender().
   */
  renderWidget() {
    // Add custom code here
  }

  /**
   * Refresh all widgets.
   */
  refresh($el) {
    this.widgetInsts.forEach(i => i.refresh($el));
    this.runWidgets($el);
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
  runDoneInit() {
    if (this.waitingRender) {
      const p = this.render();

      if (p && p.then) {
        p.then(() => {
          this.runWidgets();
        });
      } else {
        this.runWidgets();
      }
    }
  }

  /**
   * Adds a child widget to this widget.
   *
   * @param {Function} widgetClass
   * @param {String/Function} selector
   * @param {Object} options
   */
  addWidgetDef(widgetClass, selector, options = null) {
    const desc = { widgetClass, selector, options: options || this.options };
    this.widgetDefs.push(desc);

    if (this.initState === 'done_init') {
      this.runWidgetDef(desc, null); // todo this.$element as second arg?
    }
  }

  /**
   * Runs all widget definitions. You can run this multiple times, the system
   * will ensure that an element is not instantiated multiple times.
   *
   * @param {HTMLElement/jQuery} $el Optionally scope the run to this element
   */
  runWidgets($el = null) {
    let el = $el;
    if (el) {
      el = $(el);
    }

    this.widgetDefs.forEach((w) => {
      this.runWidgetDef(w, el);
    });
  }

  /**
   * @param {Array} widgetDef
   * @param {Object} $el
   * @private
   */
  runWidgetDef(widgetDef, $el) {
    const insts = this.createWidgetInst(widgetDef, $el);

    insts.forEach((i) => {
      this.widgetInsts.push(i);
      i.renderWhenReady();
    });
  }

  /**
   * @param {Array} widgetDef
   * @param {HTMLElement/jQuery} $parentElement Optionally scope the run to this element
   * @returns {Array}
   * @private
   */
  createWidgetInst(widgetDef, $parentElement) {
    const { widgetClass, selector, options = {} } = widgetDef;

    let matches;
    let $context;

    if ($parentElement) {
      $context = $($parentElement);
    } else if (this.$element) {
      $context = $(this.$element);
    } else {
      $context = $(document);
    }

    if (isFunction(selector)) {
      matches = selector(widgetClass, $context, this);
    } else {
      matches = $context.find(selector);
    }
    if (!matches || !matches[0]) {
      return [];
    }

    const insts = [];
    matches.each((x, el) => {
      const $el = $(el);
      if (!$el.data('dpWidgetInsts')) {
        $el.data('dpWidgetInsts', new WeakMap());
      }

      const elInsts = $el.data('dpWidgetInsts');
      if (!elInsts.has(widgetClass) && widgetClass) {
        const i = new widgetClass($el, this, options);
        elInsts.set(widgetClass, i);
        insts.push(i);
      }
    });

    return insts;
  }
}
