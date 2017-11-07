import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { DeskproAppContainer } from './DeskproAppContainer';
import { LegacyAppSidebar } from './LegacyAppSidebar';
import { LegacyAppIcons } from './LegacyAppIcons';

const addEventListener = (dom, event, handler) => {
  dom.addEventListener(event, handler);
  return () => dom.removeEventListener(event, handler);
};

class LegacySidebarContainer extends DeskproAppContainer {
  static propTypes = {
    widgetsConfigList:             PropTypes.array.isRequired,
    context:                       PropTypes.object.isRequired,
    dispatchIncomingWidgetMessage: PropTypes.func.isRequired,
    addWidgetEventListener:        PropTypes.func.isRequired,
    parseIncomingWidgetMessageJS:  PropTypes.func.isRequired,
    // own properties
    configuration:                 PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.removeListeners = [];
    this.onIconsToggle = true;
  }

  componentDidMount() {
    super.componentDidMount();

    const { configuration } = this.props;
    const iconsContainer = window.document.querySelector(configuration.renderIconsContainer);

    this.removeEventListeners = [
      addEventListener(iconsContainer, 'mouseover', this.onIconsMouseOver.bind(this)),
      addEventListener(iconsContainer, 'click', this.onIconsMouseClick.bind(this)),
      addEventListener(iconsContainer, 'mouseout', this.onIconsMouseOut.bind(this))
    ];

    // add app icons
    const appIcons = LegacyAppIcons.fromSelector(configuration.renderIconsContainer);
    const { widgetsConfigList } = this.props;
    widgetsConfigList.forEach((widgetConfiguration) => {
      const { baseUrl, assets } = widgetConfiguration.appConfig;
      appIcons.addAppIcon(assets.getIconUrl(baseUrl));
    });

    // show legacy content if it is available
    if (appIcons.hasLegacyAppIcons()) {
      const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
      sidebar.showLegacyContent();
    }
  }

  componentWillUnmount() {
    super.componentWillUnmount();
    for (const cb of this.removeEventListeners) {
      cb();
    }
  }

  /**
   * @param {Widget} widget
   * @param {{ type:string }} e
   */
  /**
   * @param {Widget} widget
   * @param {String} eventName
   * @param {WidgetRequest} widgetMessage
   */
  onWidgetMouseEventMessage(widget, eventName, widgetMessage) {
    const { body : e } = widgetMessage;

    if (e.type === 'mousedown') {
      const { configuration } = this.props;
      LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer).togglePined();
    }
  }

  onIconsMouseOut() { this.onIconsToggle = true; }

  onIconsMouseOver(e) {
    if (!this.onIconsToggle) { return; }
    this.onIconsToggle = false;

    const { target } = e;
    const { configuration } = this.props;

    const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
    if (sidebar.isLocked()) {
      return;
    }

    const appIcons = LegacyAppIcons.fromSelector(configuration.renderIconsContainer);
    if (appIcons.isAppIconDOM(target)) {
      sidebar.showContent();
    } else if (appIcons.isLegacyAppIconDOM(target)) {
      sidebar.showLegacyContent();
    }
  }

  onIconsMouseClick(e) {
    const { target } = e;
    const { configuration } = this.props;

    const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
    if (!sidebar.isLocked()) {
      return;
    }

    const appIcons = LegacyAppIcons.fromSelector(configuration.renderIconsContainer);
    if (appIcons.isAppIconDOM(target)) {
      sidebar.showContent();
    } else if (appIcons.isLegacyAppIconDOM(target)) {
      sidebar.showLegacyContent();
    }
  }
}

export { LegacySidebarContainer };

