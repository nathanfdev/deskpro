import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { makeOutboundCall, searchPerson } from '../../../Actions/clientActions';
import { outboundNumbersSelector } from '../../../Selectors/numbers';
import { outboundNumberSelector } from '../../../Selectors/client';

@connect(state => ({
  numbers:        outboundNumbersSelector(state),
  outboundNumber: outboundNumberSelector(state)
}))
class DialpadContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node
  };

  makeCall = (callFrom, callTo, ticketId) => {
    if (storageAvailable('localStorage')) {
      localStorage.setItem('dpAgent.voice.lastCallFrom', callFrom);
    }

    this.props.dispatch(makeOutboundCall(callFrom, callTo, ticketId));
  };

  searchPerson = searchString => this.props.dispatch(searchPerson(searchString));

  render() {
    const { children } = this.props;

    let lastCallFrom = null;
    if (storageAvailable('localStorage')) {
      lastCallFrom = parseInt(localStorage.getItem('dpAgent.voice.lastCallFrom'), 10);
    }

    // try to get ticket from active tab
    const tabBar = window.DeskPRO_Window.TabBar;
    const activeTab = tabBar.getActiveTab();

    let ticketId;
    let ticketTitle;
    if (activeTab && activeTab.tabType === 'ticket') {
      ticketId = activeTab.page.meta.ticket_id;
      ticketTitle = activeTab.title;
    }

    return React.cloneElement(children, {
      ...this.props,
      ...children.props,
      lastCallFrom,
      ticketId,
      ticketTitle,
      onMakeCall:     this.makeCall,
      onSearchPerson: this.searchPerson,
    });
  }
}

export default DialpadContainer;
