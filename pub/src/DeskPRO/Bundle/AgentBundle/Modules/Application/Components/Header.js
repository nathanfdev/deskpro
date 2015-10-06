import React, { PropTypes } from 'react';
import { HeaderWidget } from '../../IM/Components/HeaderWidget';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';
import { Workspace } from './Workspace/Workspace';

export class Header extends React.Component {
  static propTypes = {
    user: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      isWorkspaceOpen: false,
    };
  }

  toggleWorkspace() {
    this.setState({
      isWorkspaceOpen: !this.state.isWorkspaceOpen
    });
  }

  closeWorkspace() {
    this.setState({
      isWorkspaceOpen: false
    });
  }

  render() {
    const { user, dpWindow } = this.props;

    return (<header className="dp-window-header top-bar">
      <a href="https://www.deskpro.com/" className="logo"></a>
      <HeaderWidget/>

      <div className="user-options">
        <a href="#" className="notification-button" ref="workspace" onClick={this.toggleWorkspace.bind(this)}>
          <span className="title" ><i className="fa fa-columns"></i><i className="fa fa-angle-down"></i></span>
        </a>

        <a href="#" className="notification-button">
          <span className="notification-count">23</span>
          <span className="title"><i className="fa fa-cog"></i> Admin <i className="fa fa-angle-down"></i></span>
        </a>

        <a href="#" className="user-options-button">
          <span className="user-photo" style={{backgroundImage: 'url(' + user.get('picture_url') + ')'}}></span>
          <span className="title">Settings <i className="fa fa-angle-down"></i></span>
        </a>
      </div>

      <Positioned isOpen={this.state.isWorkspaceOpen} positionTarget={this.refs.workspace}>
        <Workspace dpWindow={dpWindow} closeFn={this.closeWorkspace.bind(this)} />
      </Positioned>

    </header>);
  }
}
