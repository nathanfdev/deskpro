import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import HTML5Backend from 'react-dnd-html5-backend';
import { DragDropContext } from 'react-dnd';
import { Header } from './Header';
import { AppSwitcher } from './AppSwitcher';
import { TabFrame } from './TabFrame';
import { NotificationsContainer } from './Notifications/notifications';
import { meSelector } from '../RecordStores/Selectors/meSelectors';

@connect(state => ({
  ...state,
  user: meSelector(state),
  dpWindow: state.Application.dpWindow
}))
@DragDropContext(HTML5Backend)
export class DpApp extends React.Component {

  static propTypes = {
    user: PropTypes.object.isRequired,
    children: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  render() {
    const { user, dpWindow, dispatch, children } = this.props;

    return (
      <div className="dp-window">
        <Header user={user} dispatch={dispatch} />
        <AppSwitcher dpWindow={dpWindow} dispatch={dispatch} />

        {children}

        <TabFrame dpWindow={dpWindow} />
        <NotificationsContainer />
      </div>
    );
  }
}
