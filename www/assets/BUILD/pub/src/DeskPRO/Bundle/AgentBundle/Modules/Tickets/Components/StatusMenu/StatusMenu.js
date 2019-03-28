import React from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';

@connect(state => ({
  ticketStatuses: state.Agent.info.getIn(['tickets', 'ticket_statuses']).toJS()
}))
export class StatusMenuContainer extends React.Component {
  static propTypes = {
    ctrl:                 PropTypes.object.isRequired,
    ticketStatuses:       PropTypes.array.isRequired,
    disablePendingStatus: PropTypes.bool
  };

  static findStatus(statusCode, ticketStatuses) {
    let status = null;
    if (statusCode.includes('.')) {
      const parentStatusCode = statusCode.split('.')[0];

      const parentStatus = ticketStatuses.find(s => s.status_code === parentStatusCode);
      status = parentStatus.children.find(ss => ss.status_code === statusCode);
    } else {
      status = ticketStatuses.find(s => s.status_code === statusCode);
    }
    return status;
  }

  constructor(props) {
    super(props);
    let status = null;
    if (props.ctrl.statusCode) {
      status = StatusMenuContainer.findStatus(props.ctrl.statusCode, props.ticketStatuses);
    }
    this.state = {
      status,
    };
    props.ctrl.onChange(this.handleStatusChange);
    props.ctrl.onSubmit(this.handleStatusSubmit);
  }

  handleStatusChange = (statusCode) => {
    const { ticketStatuses } = this.props;

    const status = StatusMenuContainer.findStatus(statusCode, ticketStatuses);
    if (status) {
      this.setState({
        status,
      });
    }
  };

  handleStatusSubmit = () => {
    const { status } = this.state;
    if (status) {
      this.props.ctrl.setStatus(status);
    }
  };

  handleChange = (status, force = false) => {
    this.setState({
      status
    });
    if (force || !status.children || status.children.length === 0) {
      this.props.ctrl.setStatus(status);
      this.props.ctrl.hideMenu();
    }
  };

  render() {
    const { status } = this.state;

    return (
      <StatusMenu
        ticketStatuses={this.props.ticketStatuses}
        status={status}
        handleChange={this.handleChange}
        disablePendingStatus={this.props.disablePendingStatus}
      />
    );
  }
}

export class StatusMenu extends React.Component {
  static propTypes = {
    ticketStatuses:       PropTypes.array.isRequired,
    status:               PropTypes.object,
    handleChange:         PropTypes.func,
    disablePendingStatus: PropTypes.bool
  };

  isStatusSelected = (status, strict = false) => {
    if (!this.props.status || !this.props.status.status_code) {
      return false;
    }
    if (strict) {
      return this.props.status.status_code === status.status_code;
    }
    return this.props.status.status_code.startsWith(status.status_code);
  };

  renderStatus = (status) => {
    const { handleChange, disablePendingStatus } = this.props;
    const hasChildren = status.children.length > 0;
    const isSelected = this.isStatusSelected(status);

    if (!isSelected && disablePendingStatus && status.status_type === 'pending') {
      return null;
    }

    return (
      <li
        key={status.status_code}
        className={classNames('on cursor', { 'has-submenu': hasChildren, 'has-no-submenu': !hasChildren })}
        onClick={() => handleChange(status)}
      >
        <div className="status">
          {isSelected && <div className="on-icon"><i className="fas fa-check" /></div>}
          {status.title ? status.title : <FormattedMessage id={`agent.tickets.status_${status.status_type}`} />}
        </div>
      </li>
    );
  };

  renderSubStatuses = () => {
    const { status, handleChange, ticketStatuses } = this.props;

    if (!status) {
      return null;
    }
    let parentStatusCode = status.status_code;
    if (parentStatusCode.includes('.')) {
      parentStatusCode = parentStatusCode.split('.')[0];
    }
    const parentStatus = ticketStatuses.find(s => s.status_code === parentStatusCode);
    if (parentStatus.children && parentStatus.children.length === 0) {
      return null;
    }
    return (
      <div className="sub-status">
        <label><FormattedMessage id="agent.general.sub_status" /></label>
        <ul>
          <li
            key="none"
            className="on cursor has-no-submenu"
            onClick={() => handleChange(parentStatus, true)}
          >
            <div className="status">
              {this.isStatusSelected(parentStatus, true) && <div className="on-icon"><i className="fas fa-check" /></div>}
              <FormattedMessage id="agent.general.none" />
            </div>
          </li>
          {
            parentStatus.children.map(subStatus => (
              <li
                key={subStatus.status_code}
                className="on cursor has-no-submenu"
                onClick={() => handleChange(subStatus)}
              >
                <div className="status">
                  {this.isStatusSelected(subStatus) && <div className="on-icon"><i className="fas fa-check" /></div>}
                  {subStatus.title ? subStatus.title : <FormattedMessage id={`agent.tickets.status_${subStatus.status_type}`} />}
                </div>
              </li>
            ))
          }
        </ul>
      </div>
    );
  };

  render() {
    return (
      <div className="dp-menu-area dp-menu-area-primary">
        <label><FormattedMessage id="agent.general.change_status" /></label>
        <ul>
          {
            this.props.ticketStatuses
            .filter(status => status.status_code !== 'hidden' && status.status_code !== 'archived')
            .map(status => this.renderStatus(status))
          }
        </ul>
        {this.renderSubStatuses()}
      </div>
    );
  }
}
