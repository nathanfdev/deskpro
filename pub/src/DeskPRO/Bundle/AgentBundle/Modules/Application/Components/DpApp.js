import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import HTML5Backend from 'react-dnd-html5-backend';
import { DragDropContext } from 'react-dnd';
import { Header } from './Header';
import { AppSwitcher } from './AppSwitcher';
import { TabBodyPane } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { TabFrame } from './TabFrame';
import { NotificationsContainer } from './Notifications/notifications';
import { meSelector } from '../RecordStores/Selectors/meSelectors';
import { workspaceDimsSelector } from '../Selectors/workspace';
import * as appActions from '../Actions/appActions';
import {pollActionAlerts} from '../Actions/notificationActions';
import debounce from 'lodash/function/debounce';
import $ from 'jquery';

@connect(state => ({
  user: meSelector(state),
  dpWindow: state.Application.dpWindow,
  workspaceDims: workspaceDimsSelector(state),
  actionAlerts: state.Application.notifications.get('actionAlerts')
}))
@DragDropContext(HTML5Backend)
export class DpApp extends React.Component {

  static propTypes = {
    user: PropTypes.object.isRequired,
    children: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired,
    workspace: PropTypes.object.isRequired,
    actionAlerts: PropTypes.string.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this._resetWinSize();
    this.onResize = debounce(() => {
      this._resetWinSize();
    }, 350);
  }

  componentDidMount() {
    $(window).on('resize', this.onResize);
    setInterval(() => this.props.dispatch(pollActionAlerts()), 5000);
  }

  componentWillUnmount() {
    $(window).off('resize', this.onResize);
  }

  _resetWinSize() {
    this.props.dispatch(appActions.windowResize($(window).width(), $(window).height()));
  }

  render() {
    const { user, dpWindow, dispatch, children } = this.props;

    return (
      <div className="dp-window">
        <Header user={user} dispatch={dispatch}/>
        <AppSwitcher dispatch={dispatch} currentApp={dpWindow.get('activeAppId')}/>

        <div className="dp-panes-middle">
          {children}

          <TabBodyPane>
            <TabFrame dpWindow={dpWindow}/>
          </TabBodyPane>
        </div>
        <NotificationsContainer />
      </div>
    );
  }
}
