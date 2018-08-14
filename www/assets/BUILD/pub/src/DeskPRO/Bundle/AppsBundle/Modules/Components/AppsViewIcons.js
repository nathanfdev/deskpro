import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { SidebarControlBtn } from './SidebarControlBtn';
import { WidgetConfiguration } from '../Domain';
import { getWidgetBadgeCount } from '../Services/appsState';

export class AppsViewIcons extends React.PureComponent {
  static propTypes = {
    appsState:         PropTypes.object.isRequired,
    sidebarState:      PropTypes.string.isRequired,
    togglePin:         PropTypes.func.isRequired,
    widgetsConfigList: PropTypes.arrayOf(WidgetConfiguration).isRequired,
  };

  static defaultProps = {
    active: false
  };

  /**
   * @param {SyntheticEvent} ev
   */
  cancelMouseOver = (ev) =>  {
    ev.preventDefault();
    ev.stopPropagation();
  };

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   */
  renderIcon = (widgetConfiguration) =>  {
    const { baseUrl, assets } = widgetConfiguration.appConfig;
    const notification = getWidgetBadgeCount(widgetConfiguration.id, this.props.appsState);

    return (
      <button className="dp-ButtonTabs dp---is-pulse" onMouseOver={this.cancelMouseOver}>
        <img src={assets.getIconUrl(baseUrl)} alt={widgetConfiguration.appConfig.applicationTitle} className={'dp-ButtonsImg'} />
        { !!notification && <span className="dp-IconBadge">{notification}</span> }
      </button>
    );
  };

  render()  {
    return (
      <div className={'dp-AppPanel'}>
        <div className={'dp-AppTabs is-vertical layout-sidebar__icons'} >
          <SidebarControlBtn sidebarState={this.props.sidebarState} onActivate={this.props.togglePin} />

          <div className={'dp-ButtonTabs--wrap'}>
            {this.props.widgetsConfigList.map(this.renderIcon)}
          </div>

        </div>
      </div>
    );
  }
}
