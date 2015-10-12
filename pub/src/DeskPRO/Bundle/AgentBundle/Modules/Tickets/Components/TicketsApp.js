import React from 'react';
import { connect } from 'react-redux';
import { AppContainer } from 'DeskPRO/Component/AppContainer';

import TicketsSidebarHoverFrame from './TicketsSidebarHoverFrame';
import TicketsNavFrame from './TicketsNavFrame';
import TicketsListFrame from './TicketsListFrame';
import { meSelector } from '../RecordStores/Selectors/meSelectors';

@connect(state => ({
  user: meSelector(state),
  dpWindow: state.Application.dpWindow
}))
export default class TicketsApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="tickets" {...this.props}>
        <TicketsSidebarHoverFrame />
        <TicketsNavFrame {...this.props} />
        <TicketsListFrame />
      </AppContainer>
    );
  }
}
