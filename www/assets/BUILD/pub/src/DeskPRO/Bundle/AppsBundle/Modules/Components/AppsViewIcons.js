import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { BtnPin } from './BtnPin';
import { WidgetConfiguration } from '../Domain';

export class AppsViewIcons extends React.PureComponent {
  static propTypes = {
    expand:            PropTypes.func.isRequired,
    togglePin:         PropTypes.func.isRequired,
    widgetsConfigList: PropTypes.arrayOf(WidgetConfiguration).isRequired,
  };

  static defaultProps = {
    active: false
  };

  /**
   * @param {SyntheticEvent} e
   */
  onMouseOver = (e) =>  { // eslint-disable-line no-unused-vars
    this.props.expand();
  };

  /**
   * @param {SyntheticEvent} ev
   */
  cancelMouseOver = (ev) =>  {
    ev.stopPropagation();
  };

  /**
   * @param {WidgetConfiguration} widgetConfiguration
   */
  renderIcon = (widgetConfiguration) =>  {
    const { baseUrl, assets } = widgetConfiguration.appConfig;
    return (
      <div className={'layout-sidebar__icon-view layout-sidebar__icon-view--group-start layout-sidebar__icon-view--group-end'}>
        <img src={assets.getIconUrl(baseUrl)} alt={widgetConfiguration.appConfig.applicationTitle} />
      </div>
    );
  };

  render()  {
    return (
      <div className={'layout-sidebar__icons'} onMouseOver={this.onMouseOver}>
        <div className={'layout-sidebar__header'} onMouseOver={this.cancelMouseOver}>
          <BtnPin toggle={this.props.togglePin} />
        </div>

        {this.props.widgetsConfigList.map(this.renderIcon)}

      </div>
    );
  }
}
