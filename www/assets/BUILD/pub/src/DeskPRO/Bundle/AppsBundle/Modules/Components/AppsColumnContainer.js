import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars
import { AppsViewFull } from './AppsViewFull';
import { AppsViewIcons } from './AppsViewIcons';

import { LegacyAppSidebar } from './LegacyAppSidebar';

class AppsColumnContainer extends React.Component {

  static propTypes = {
    widgetsConfigList:              PropTypes.array.isRequired,
    context:                        PropTypes.object.isRequired,
    dispatchIncomingWidgetMessage:  PropTypes.func.isRequired,
    addWidgetEventListener:         PropTypes.func.isRequired,
    parseIncomingWidgetMessageJS:   PropTypes.func.isRequired,
    // own properties
    configuration:                  PropTypes.object.isRequired,
    sendMessageLegacyMessageBroker: PropTypes.func.isRequired
  };

  state = {
    visibility: 'collapsed'
  };

  /**
   * @param {SyntheticEvent} e
   */
  expand = (e) => { // eslint-disable-line no-unused-vars
    const { configuration } = this.props;
    const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
    if (sidebar.isLocked()) {
      return;
    }

    this.props.sendMessageLegacyMessageBroker('apps.column.expand');
  };

  collapse = () => {
    const { configuration } = this.props;
    const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
    if (sidebar.isLocked()) {
      return;
    }

    this.props.sendMessageLegacyMessageBroker('apps.column.collapse');
  };

  pin = () => {
    const { configuration } = this.props;
    const sidebar = LegacyAppSidebar.fromSelector(configuration.renderSidebarContainer);
    if (sidebar.isLocked()) {
      return;
    }

    this.props.sendMessageLegacyMessageBroker('apps.column.toggle');
  };

  togglePin = (e) =>  { // eslint-disable-line no-unused-vars
    this.props.sendMessageLegacyMessageBroker('apps.column.toggle');
  };

  timeout = null;

  render()  {
    return [

      <AppsViewIcons
        togglePin={this.togglePin}
        expand={this.expand}

        widgetsConfigList={this.props.widgetsConfigList}
      />,

      <AppsViewFull
        togglePin={this.togglePin}
        pin={this.pin}
        collapse={this.collapse}
        widgetsConfigList={this.props.widgetsConfigList}

        context={this.props.context}
        addWidgetEventListener={this.props.addWidgetEventListener}
        dispatchIncomingWidgetMessage={this.props.dispatchIncomingWidgetMessage}
        parseIncomingWidgetMessageJS={this.props.parseIncomingWidgetMessageJS}

      />
    ];
  }
}

export { AppsColumnContainer };

