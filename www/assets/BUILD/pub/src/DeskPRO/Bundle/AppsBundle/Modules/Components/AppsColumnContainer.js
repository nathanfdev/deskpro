import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { Scrollbars } from 'react-custom-scrollbars';
import { AppsViewFull } from './AppsViewFull';
import { AppsViewIcons } from './AppsViewIcons';
import { receiveMessage } from '../WidgetMessage';
import { ContainerEvents } from './ContainerEvents';
import { setWidgetState } from '../Services/appsState';
import { WidgetConfiguration }  from '../Domain';

/**
 * @param {Array<WidgetConfiguration>} widgetList
 */
function mapWidgetsToGroups(widgetList) {
  const defaultGroup = [];

  /**
   * @param {Object} acc
   * @param {WidgetConfiguration} config
   */
  function reducer(acc, config) {
    const { settings, id } = config;

    if (!settings || !settings.showInGroup || settings.showInGroup === 'default') {
      defaultGroup.push(config);
    } else if (settings.showInGroup === 'isolated') {
      acc[id] = [config];
    }

    return acc;
  }

  const namedGroups = widgetList.reduce(reducer, {});
  if (defaultGroup.length === 0) {
    return Object.keys(namedGroups).map(key => namedGroups[key]);
  }

  return [defaultGroup].concat(Object.keys(namedGroups).map(key => namedGroups[key]));
}

class AppsColumnContainer extends React.Component {

  static propTypes = {
    widgetsConfigList: PropTypes.arrayOf(PropTypes.instanceOf(WidgetConfiguration)).isRequired,
    context:           PropTypes.object.isRequired,

    // own properties
    getSidebarState:                PropTypes.func.isRequired,
    sendMessageLegacyMessageBroker: PropTypes.func.isRequired
  };

  state = {
    appsState:          {},
    sidebarState:       null,
    widgetGroupVisible: 0
  };

  showWidgetGroup = (groupId) => {
    this.setState({ widgetGroupVisible: groupId });
  };

  /**
   * @param {SyntheticEvent} e
   */
  expand = (e) => { // eslint-disable-line no-unused-vars
    if (this.props.getSidebarState() !== 'pinned') {
      const { pageId } = this.props.context;
      this.props.sendMessageLegacyMessageBroker(`apps-column.expand.${pageId}`);
      this.setState({
        sidebarState: this.props.getSidebarState()
      });
    }
  };

  collapse = () => {
    if (this.props.getSidebarState() !== 'pinned') {
      const { pageId } = this.props.context;
      this.props.sendMessageLegacyMessageBroker(`apps-column.collapse.${pageId}`);
      this.setState({
        sidebarState: this.props.getSidebarState()
      });
    }
  };

  pin = () => {
    if (this.props.getSidebarState() !== 'pinned') {
      const { pageId } = this.props.context;
      this.props.sendMessageLegacyMessageBroker(`apps-column.togglePin.${pageId}`);
      this.setState({
        sidebarState: this.props.getSidebarState()
      });
    }
  };

  togglePin = (e) =>  { // eslint-disable-line no-unused-vars
    const { pageId } = this.props.context;
    this.props.sendMessageLegacyMessageBroker(`apps-column.togglePin.${pageId}`);
    this.setState({
      sidebarState: this.props.getSidebarState()
    });
  };

  /**
   * We're intercepting widget communications to capture ui changes from the apps and update the container accordingly
   *
   * @param {Widget} widget
   * @param {Object} event
   */
  receiveMessage = (widget, event) =>  {
    const { eventName } = event.data;
    // intercept the EVENT_UI_CHANGED event and update the apps state
    if (eventName === ContainerEvents.EVENT_UI_CHANGED) {
      const { widgetId, body } = event.data;
      this.setState({
        appsState: setWidgetState(widgetId, body, this.state.appsState)
      });
    } else {
      receiveMessage(widget, event);
    }
  };

  render()  {
    const sidebarState = this.state.sidebarState || this.props.getSidebarState();

    return (
      <Scrollbars autoHide>
        <div className={'layout-sidebar__views'} onMouseLeave={this.collapse} onMouseOver={this.expand} onClick={this.pin}>

          <AppsViewIcons
            appsState={this.state.appsState}
            sidebarState={sidebarState}

            togglePin={this.togglePin}
            widgetsConfigList={this.props.widgetsConfigList}
          />

          <AppsViewFull
            appsState={this.state.appsState}
            sidebarState={sidebarState}
            showWidgetGroup={this.showWidgetGroup}
            widgetGroupVisible={this.state.widgetGroupVisible}

            togglePin={this.togglePin}
            pin={this.pin}
            widgetGroups={mapWidgetsToGroups(this.props.widgetsConfigList)}

            context={this.props.context}
            receiveMessage={this.receiveMessage}
          />
        </div>
      </Scrollbars>
    );
  }
}

export { AppsColumnContainer };

