import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { Scrollbars } from 'react-custom-scrollbars';
import { AppsViewFull } from './AppsViewFull';
import { AppsViewIcons } from './AppsViewIcons';
import { receiveMessage } from '../WidgetMessage';
import { ContainerEvents } from './ContainerEvents';
import { setWidgetState } from '../Services/appsState';


class AppsColumnContainer extends React.Component {

  static propTypes = {
    widgetsConfigList: PropTypes.array.isRequired,
    context:           PropTypes.object.isRequired,


    // own properties
    getSidebarState:                PropTypes.func.isRequired,
    sendMessageLegacyMessageBroker: PropTypes.func.isRequired
  };

  state = {
    appsState:    {},
    sidebarState: null
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

            togglePin={this.togglePin}
            pin={this.pin}

            widgetsConfigList={this.props.widgetsConfigList}
            context={this.props.context}
            receiveMessage={this.receiveMessage}
          />
        </div>
      </Scrollbars>
    );
  }
}

export { AppsColumnContainer };

