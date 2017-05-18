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

    const appIcons = LegacyAppIcons.fromSelector(configuration.renderIconsContainer);
    appIcons.addAppIcon('/file.php/apps/deskpro_magento/res/magento.png?v=1487683051');
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





