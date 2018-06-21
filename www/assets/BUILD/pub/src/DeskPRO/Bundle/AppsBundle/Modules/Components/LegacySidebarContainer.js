import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { DeskproAppContainer } from './DeskproAppContainer';
import { LegacyAppSidebar } from './LegacyAppSidebar';
import { LegacyAppIcons } from './LegacyAppIcons';

const addEventListener = (dom, event, handler) => {
  dom.addEventListener(event, handler);
  return () => dom.removeEventListener(event, handler);
};

class LegacySidebarContainer extends React.Component {
  static propTypes = {
    widgetsConfigList:             PropTypes.array.isRequired,
    context:                       PropTypes.object.isRequired,
    dispatchIncomingWidgetMessage: PropTypes.func.isRequired,
    addWidgetEventListener:        PropTypes.func.isRequired,
    parseIncomingWidgetMessageJS:  PropTypes.func.isRequired,
    // own properties
    configuration:                 PropTypes.object.isRequired
  };

  componentDidMount()  {
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
    for (const cb of this.removeEventListeners) {
      cb();
    }
  }

  onIconsMouseOut() { this.toggleIcons = true; }

  onIconsMouseOver(e) {
    if (!this.toggleIcons) { return; }
    this.toggleIcons = false;

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

  toggleIcons = true;

  removeEventListeners = [];

  render()  {
    return (<DeskproAppContainer
      context={this.props.context}
      addWidgetEventListener={this.props.addWidgetEventListener}
      dispatchIncomingWidgetMessage={this.props.dispatchIncomingWidgetMessage}
      parseIncomingWidgetMessageJS={this.props.parseIncomingWidgetMessageJS}
      widgetsConfigList={this.props.widgetsConfigList}
    />);
  }
}

export { LegacySidebarContainer };

