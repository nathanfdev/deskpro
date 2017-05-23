import React, { PropTypes } from 'react';
import DeskproAppContainer from './DeskproAppContainer';
import LegacyAppSidebar from './LegacyAppSidebar';
import LegacyAppIcons from './LegacyAppIcons';

class LegacySidebarContainer extends React.Component
{
  static propTypes = {
    widgetsConfigList: PropTypes.array.isRequired
    , dispatchWidgetRequestEvent: PropTypes.func.isRequired

    , context: PropTypes.object.isRequired
    , configuration: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
  }

  componentDidMount() {
    const { configuration } = this.props;
    const iconsContainer = window.document.querySelector(configuration.renderIconsContainer);

    iconsContainer.addEventListener('mouseover', this.onMouseOver);
    iconsContainer.addEventListener('click', this.onMouseClick);

    // add app icons
    const appIcons = LegacyAppIcons.fromSelector(configuration.renderIconsContainer);
    const { widgetsConfigList } = this.props;
    widgetsConfigList.forEach(widgetConfiguration => {
      const { baseUrl, assets } = widgetConfiguration.appConfig;
      appIcons.addAppIcon(assets.getIconUrl(baseUrl));
    });

    //show legacy content if it is available
    if (window.AppPlatform.apps.length) {
      const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
      sidebar.showLegacyContent();
    }

  }

  componentWillUnmount () {
    const { configuration } = this.props;
    const iconsContainer = window.document.querySelector(configuration.renderIconsContainer);

    iconsContainer.removeEventListener('mouseover', this.onMouseOver);
    iconsContainer.removeEventListener('click', this.onMouseClick);
  }

  onMouseOver = (e) => {

    const { target } = e;
    const { configuration } = this.props;

    const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
    if (sidebar.isLocked()) {
      return ;
    }

    const appIcons = LegacyAppIcons.fromSelector(configuration.renderIconsContainer);
    if (appIcons.isAppIconDOM(target)) {
      sidebar.showContent();
    } else if (appIcons.isLegacyAppIconDOM(target)) {
      sidebar.showLegacyContent();
    }
  };

  onMouseClick = (e) =>
  {
    const { target } = e;
    const { configuration } = this.props;

    const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
    if (! sidebar.isLocked()) {
      return ;
    }

    const appIcons = LegacyAppIcons.fromSelector(configuration.renderIconsContainer);
    if (appIcons.isAppIconDOM(target)) {
      sidebar.showContent();
    } else if (appIcons.isLegacyAppIconDOM(target)) {
      sidebar.showLegacyContent();
    }
  };

  /**
   * Renders the container and all the apps
   *
   * @returns {XML}
   */
  render() {
    return (<DeskproAppContainer
      widgetsConfigList = {this.props.widgetsConfigList}
      dispatchWidgetRequestEvent = {this.props.dispatchWidgetRequestEvent}
      context = {this.props.context}
      configuration = {this.props.configuration}
    />);
  }
}

export default LegacySidebarContainer;





