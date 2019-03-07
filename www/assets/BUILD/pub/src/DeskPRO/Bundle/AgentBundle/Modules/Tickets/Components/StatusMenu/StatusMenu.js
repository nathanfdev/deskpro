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
    ctrl:           PropTypes.object.isRequired,
    ticketStatuses: PropTypes.array.isRequired
  };

  constructor(props) {
    super(props);
    let status = null;
    if (props.ctrl.statusCode) {
      status = props.ticketStatuses.find(s => s.status_code === props.ctrl.statusCode);
    }
    this.state = {
      status,
      ticketStatusId: null
    };
    props.ctrl.onChange(this.handleStatusChange);
    props.ctrl.onSubmit(this.handleStatusSubmit);
  }

  handleStatusChange = (status, ticketStatusId) => {
    this.setState({
      status,
      ticketStatusId
    });
  };

  handleStatusSubmit = () => {
    const { status, ticketStatusId } = this.state;
    if (status) {
      this.props.ctrl.setStatus(status, ticketStatusId);
    }
  };

  handleChange = (status, force = false) => {
    this.setState({
      status
    });
    const ticketStatusId = 0;
    if (force || !status.children || status.children.length === 0) {
      this.props.ctrl.setStatus(status, ticketStatusId);
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
      />
    );
  }
}

export class StatusMenu extends React.Component {
  static propTypes = {
    ticketStatuses: PropTypes.array.isRequired,
    status:         PropTypes.object,
    handleChange:   PropTypes.func
  };

  isStatusSelected = status => this.props.status && this.props.status.status_code === status.status_code;

  renderStatus = (status) => {
    const { handleChange } = this.props;
    const hasChildren = status.children.length > 0;
    return (
      <li
        key={status.status_code}
        className={classNames('on cursor', { 'has-submenu': hasChildren, 'has-no-submenu': !hasChildren })}
        onClick={() => handleChange(status)}
      >
        <div className="status">
          {this.isStatusSelected(status) && <div className="on-icon"><i className="fas fa-check" /></div>}
          {status.title ? status.title : <FormattedMessage id={`agent.tickets.status_${status.status_type}`} />}
        </div>
      </li>
    );
  };

  renderSubStatuses = () => {
    const { status, handleChange, ticketStatuses } = this.props;

    let parentStatusCode = status.status_code;
    if (parentStatusCode.includes('.')) {
      parentStatusCode = parentStatusCode.split('.')[0];
    }
    const parentStatus = ticketStatuses.find(s => s.status_code === parentStatusCode);
    if (!status || parentStatus.children && parentStatus.children.length === 0) {
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
              {this.isStatusSelected(parentStatus) && <div className="on-icon"><i className="fas fa-check" /></div>}
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
