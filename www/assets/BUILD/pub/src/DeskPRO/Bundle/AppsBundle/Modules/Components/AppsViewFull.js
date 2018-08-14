import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { DeskproAppContainer } from './DeskproAppContainer';
import { SidebarControlBtn } from './SidebarControlBtn';
import { NavigationGroups } from './NavigationGroups';
import { getWidgetBadgeTotalCount } from '../Services/appsState';


export class AppsViewFull extends React.PureComponent {
  static propTypes = {
    appsState:    PropTypes.object.isRequired,
    sidebarState: PropTypes.string.isRequired,
    togglePin:    PropTypes.func.isRequired,
    pin:          PropTypes.func.isRequired,

    widgetsConfigList: PropTypes.array.isRequired,
    context:           PropTypes.object.isRequired,
    receiveMessage:    PropTypes.func.isRequired
  };

  static defaultProps = {
    active: false
  };


  /**
   * @param {SyntheticEvent} e
   */
  onClick = (e) =>  { // eslint-disable-line no-unused-vars
    this.props.pin();
  };

  render()  {
    return (
      <div className={'layout-sidebar__apps'} onClick={this.onClick}>
        <div className={'dp-Root'}>
          <div className={'dp-AppPanel'} >
            <div className={'dp-AppTabs is-horizontal'}>
              <NavigationGroups
                className={'dp-ButtonTabs--wrap'}
                notificationsCount={getWidgetBadgeTotalCount(this.props.appsState)}
              />

              <SidebarControlBtn sidebarState={this.props.sidebarState} onActivate={this.props.togglePin} />
            </div>

            <DeskproAppContainer
              context={this.props.context}
              receiveMessage={this.props.receiveMessage}
              widgetsConfigList={this.props.widgetsConfigList}
            />
          </div>

        </div>
      </div>
    );
  }

}
