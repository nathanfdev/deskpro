import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import '@deskpro/apps-components-style'; // eslint-disable-line import/extensions
import { DeskproAppContainer } from './DeskproAppContainer';
import { SidebarControlBtn } from './SidebarControlBtn';
import { WidgetGroupControlBtn } from './WidgetGroupControlBtn';
import { getWidgetBadgeCount } from '../Services/appsState';
import { WidgetConfiguration }  from '../Domain';
import { WidgetContainerListEmpty } from './WidgetContainerListEmpty';
import { WidgetContainerList } from './WidgetContainerList';


export class AppsViewFull extends React.PureComponent {
  static propTypes = {
    appsState:    PropTypes.object.isRequired,
    sidebarState: PropTypes.string.isRequired,
    togglePin:    PropTypes.func.isRequired,
    pin:          PropTypes.func.isRequired,

    showWidgetGroup:    PropTypes.func.isRequired,
    widgetGroupVisible: PropTypes.string,
    widgetGroups:       PropTypes.arrayOf(
      PropTypes.arrayOf(
        PropTypes.instanceOf(WidgetConfiguration)
      )
    ).isRequired,

    context:        PropTypes.object.isRequired,
    receiveMessage: PropTypes.func.isRequired
  };

  static defaultProps = {
    widgetGroupVisible: 0,
    active:             false
  };


  /**
   * @param {SyntheticEvent} e
   */
  onClick = (e) =>  { // eslint-disable-line no-unused-vars
    this.props.pin();
  };

  /**
   * @param {Array<WidgetConfiguration>} widgetList
   * @param groupId
   */
  renderGroupWidgets(widgetList, groupId)  {
    const { widgetGroupVisible } = this.props;
    function render({ getEvent, getEventProviders, unregister })    {
      if (widgetList && widgetList.length > 0) {
        return (
          <WidgetContainerList
            isVisible={groupId === widgetGroupVisible}
            widgets={widgetList}
            getEvent={getEvent}
            getEventProviders={getEventProviders}
            unregister={unregister}
          />
        );
      }
      return (<WidgetContainerListEmpty />);
    }

    return (<DeskproAppContainer
      context={this.props.context}
      receiveMessage={this.props.receiveMessage}
      widgetsConfigList={widgetList}
    >
      {render}
    </DeskproAppContainer>);
  }

  /**
   * @param {Array<WidgetConfiguration>} widgetList
   * @param groupId
   * @return {*}
   */
  renderGroupTab(widgetList, groupId)  {
    /**
     * @type {WidgetConfiguration}
     */
    const firstWidget = widgetList[0];
    const { appsState, showWidgetGroup } = this.props;
    /**
     * @param {Number} acc
     * @param {WidgetConfiguration} widgetConfig
     * @return {*}
     */
    function computeNotificationsCount(acc, widgetConfig) {
      return acc + getWidgetBadgeCount(widgetConfig.id, appsState);
    }

    const isMainGroup = groupId === 0;

    const notificationsCount = widgetList.reduce(computeNotificationsCount, 0);
    const icon = !isMainGroup && widgetList.length === 1 ? firstWidget.appConfig.assets.getIconUrl(firstWidget.appConfig.baseUrl) : null;
    const label = !isMainGroup && widgetList.length === 1 ? firstWidget.appConfig.applicationTitle : null;

    return (<WidgetGroupControlBtn
      groupId={groupId}
      onClick={showWidgetGroup}
      notificationsCount={notificationsCount}
      icon={icon}
      label={label}
    />);
  }

  render()  {
    return (
      <div className={'layout-sidebar__apps'} onClick={this.onClick}>
        <div className={'dp-Root'}>
          <div className={'dp-AppPanel'} >
            <div className={'dp-AppTabs is-horizontal'}>

              <div className={'dp-ButtonTabs--wrap'} >
                { this.props.widgetGroups.map((widgetList, index) => this.renderGroupTab(widgetList, index)) }
              </div>

              <SidebarControlBtn sidebarState={this.props.sidebarState} onActivate={this.props.togglePin} />
            </div>

            { this.props.widgetGroups.map((widgetList, index) => this.renderGroupWidgets(widgetList, index)) }

          </div>
        </div>
      </div>
    );
  }
}
