import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import HTML5Backend from 'react-dnd-html5-backend';
import { DragDropContext } from 'react-dnd';
import debounce from 'lodash/function/debounce';
import { Header } from './Header';
import { AppSwitcher } from './AppSwitcher';
import { TabBodyPane } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/panes';
import { TabFrame } from './TabFrame';
import { NotificationsContainer } from './Notifications/notifications';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { workspaceDimsSelector } from '../Selectors/workspace';
import { setActiveApp, togglePreferences, toggleWorkspace, windowResize } from '../Actions/appActions';
import $ from 'jquery';

@connect(state => ({
  user: meSelector(state),
  dpWindow: state.Application.dpWindow,
  workspaceDims: workspaceDimsSelector(state)
}))
@DragDropContext(HTML5Backend)
export class DpApp extends React.Component {

  static propTypes = {
    user: PropTypes.object.isRequired,
    children: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired,
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
  }

  componentWillUnmount() {
    $(window).off('resize', this.onResize);
  }

  _resetWinSize() {
    this.props.dispatch(windowResize($(window).width(), $(window).height()));
  }

  switchApp = appId => {
    this.props.dispatch(setActiveApp(appId));
  };

  toggleWorkspace = event => {
    event.preventDefault();
    this.props.dispatch(toggleWorkspace());
  };

  togglePreferences = event => {
    event.preventDefault();
    this.props.dispatch(togglePreferences());
  };

  render() {
    const { user, dpWindow, children } = this.props;

    return (
      <div className="dp-window">
        <Header user={user} toggleWorkspace={this.toggleWorkspace} togglePreferences={this.togglePreferences}/>
        <AppSwitcher switchApp={this.switchApp} currentApp={dpWindow.get('activeAppId')}/>

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
