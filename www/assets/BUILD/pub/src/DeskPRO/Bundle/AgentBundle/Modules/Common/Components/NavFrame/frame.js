import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

export class NavFrame extends React.Component {

  static propTypes = {
    children: PropTypes.any.isRequired
  };

  render() {
    const { children } = this.props;
    let outer = '';
    let inner = children;

    if (children instanceof Array && children.length) {
      children.forEach(child => {
        if (child.props.part === 'outer') {
          outer = child;
        } else if (child.props.part === 'inner') {
          inner = child;
        }
      });
    }

    return (
      <div>
        {outer}
        <section className="task-nav-frame dp-nav-frame">
          <div className="sidebar-wrapper">
            <aside className="sidebar has-tabs">
              {inner}
            </aside>
          </div>
        </section>
      </div>
    );
  }
}

@connect(state => ({
  currentApp: state.Application.dpWindow.get('activeAppId')
}))
export class NavFrameHeaderContainer extends React.Component {
  static propTypes = {
    currentApp: PropTypes.string.isRequired
  };

  render() {
    return <NavFrameHeader {...this.props} />;
  }
}

export class NavFrameHeader extends React.Component {

  static propTypes = {
    children:   PropTypes.any.isRequired,
    icon:       PropTypes.string.isRequired,
    currentApp: PropTypes.string.isRequired
  };

  render() {
    const { children, icon, currentApp } = this.props;
    const iconClass = 'icon ' + icon;

    return (
      <div>
        <div className="dpw-sidebar-main-title">
          <h1 style={{ borderBottomColor: constants.APP_COLOURS[currentApp] }}>{children}</h1>

          <div className="dpw-sidebar-main-title-active-app-icon">
            <div className={iconClass}></div>
          </div>
        </div>
        <span className="dpw-sidebar-main-title-footer"></span>
      </div>
    );
  }
}

export class NavFrameBody extends React.Component {
  static propTypes = {
    children: PropTypes.any.isRequired,
    isLoaded: PropTypes.bool.isRequired
  };

  render() {
    const isLoaded = this.props.isLoaded === !!this.props.isLoaded
                   ? this.props.isLoaded
                   : true;

    return (
      <LoadIndicator loaded={isLoaded} top="100px">
        <div className="dp-nav-frame-body">
          <Scrollable vertical>
            {this.props.children}
          </Scrollable>
        </div>
      </LoadIndicator>
    );
  }
}
