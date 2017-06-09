import React, { PropTypes } from 'react';
import DeskproAppContainer from './DeskproAppContainer';
import LegacyAppSidebar from './LegacyAppSidebar';
import LegacyAppIcons from './LegacyAppIcons';

class LegacySidebarContainer extends React.Component
{
  static propTypes = {
    widgetsConfigList: PropTypes.array.isRequired
    , dispatchIncomingWidgetMessage: PropTypes.func.isRequired

    , context: PropTypes.object.isRequired
    , configuration: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.iconsDOMListeners = { mouseout: [], mouseover: [], click: [] }
  }

  componentDidMount() {
    const { configuration } = this.props;
    const iconsContainer = window.document.querySelector(configuration.renderIconsContainer);

    // TODO: refactor obviously
    let onMouseOverToggle = true;
    const onMouseOverListener = e => {
      if (onMouseOverToggle) {
        onMouseOverToggle = false;
        this.onMouseOver(e);
      }
    };
    iconsContainer.addEventListener('mouseover', onMouseOverListener);
    this.iconsDOMListeners.mouseover.push(onMouseOverListener);

    const onMouseOutListener = e => onMouseOverToggle = true;
    iconsContainer.addEventListener('mouseout', onMouseOutListener);
    this.iconsDOMListeners.mouseout.push(onMouseOverListener);

    iconsContainer.addEventListener('click', this.onMouseClick);
    this.iconsDOMListeners.click.push(this.onMouseClick);

    // add app icons
    const appIcons = LegacyAppIcons.fromSelector(configuration.renderIconsContainer);
    const { widgetsConfigList } = this.props;
    widgetsConfigList.forEach(widgetConfiguration => {
      const { baseUrl, assets } = widgetConfiguration.appConfig;
      appIcons.addAppIcon(assets.getIconUrl(baseUrl));
    });

    //show legacy content if it is available
    if (appIcons.hasLegacyAppIcons()) {
      const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
      sidebar.showLegacyContent();
    }
  }

  componentWillUnmount () {
    const { configuration } = this.props;
    const iconsContainer = window.document.querySelector(configuration.renderIconsContainer);

    for (const event of ['click', 'mouseover', 'mouseover']) {
      for (const listener of this.iconsDOMListeners[event]) {
        iconsContainer.removeEventListener(event, listener);
      }
    }
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
      dispatchIncomingWidgetMessage = {this.props.dispatchIncomingWidgetMessage}
      context = {this.props.context}
      configuration = {this.props.configuration}
    />);
  }
}

export default LegacySidebarContainer;





