import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { SidebarControlBtn } from './SidebarControlBtn';
import Icon from './Icon';
import { WidgetConfiguration } from '../Domain';
import { getWidgetBadgeCount, getWidgetBadgeStyle } from '../Services/appsState';

export class AppsViewIcons extends React.PureComponent {
  static propTypes = {
    appsState:    PropTypes.object.isRequired,
    sidebarState: PropTypes.string.isRequired,
    togglePin:    PropTypes.func.isRequired,
    widgetGroups: PropTypes.arrayOf(
      PropTypes.arrayOf(
        PropTypes.instanceOf(WidgetConfiguration)
      )
    ).isRequired,
    onIconClick: PropTypes.func.isRequired,
  };

  static defaultProps = {
    active: false
  };

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {Number} groupId
   */
  onIconClick = ({ widgetConfiguration, groupId }) => {
    this.props.onIconClick(widgetConfiguration, groupId);
  };

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   * @param {boolean} [withSeparator]
   * @param {Number} groupId
   */
  renderIcon = (widgetConfiguration, withSeparator, groupId) =>  {
    const { baseUrl, assets } = widgetConfiguration.appConfig;
    const iconUrl = assets.getIconUrl(baseUrl);
    const appTitle = widgetConfiguration.appConfig.applicationTitle;
    const notification = getWidgetBadgeCount(widgetConfiguration.id, this.props.appsState);
    const notificationStyle = getWidgetBadgeStyle(widgetConfiguration.id, this.props.appsState);

    return (<Icon
      iconUrl={iconUrl}
      appTitle={appTitle}
      notification={notification}
      notificationStyle={notificationStyle}
      showSeparator={withSeparator}
      onClick={this.onIconClick}

      widgetConfiguration={widgetConfiguration}
      groupId={groupId}
    />);
  };

  /**
   * @param {Array<WidgetConfiguration>} widgetList
   * @param {Number} groupId
   * @param {Boolean} isLastGroup
   */
  renderGroupIcons = (widgetList, groupId, isLastGroup) =>  {
    const lastIndex = widgetList.length - 1;
    return widgetList.map((widgetConfiguration, index) => {
      const hasSeparator = index === lastIndex && !isLastGroup;
      return this.renderIcon(widgetConfiguration, hasSeparator, groupId);
    });
  };

  /**
   * @param {Array<WidgetConfiguration>} widgetList
   */
  renderIconList = widgetList => widgetList.map(widgetConfiguration => this.renderIcon(widgetConfiguration, false, 0));

  render()  {
    const nrGroups = this.props.widgetGroups.length;
    const renderIcon = nrGroups === 1 ? this.renderIconList : this.renderGroupIcons;
    const lastIndex = nrGroups - 1;

    return (
      <div className={'dp-AppPanel'}>
        <div className={'dp-AppTabs is-vertical layout-sidebar__icons'} >
          <SidebarControlBtn sidebarState={this.props.sidebarState} onActivate={this.props.togglePin} />

          <div className={'dp-ButtonTabs--wrap'}>
            {this.props.widgetGroups.map((widgetList, index) => renderIcon(widgetList, index, lastIndex === index))}
          </div>

        </div>
      </div>
    );
  }
}
