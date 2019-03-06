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

  handleChange = (status) => {
    this.setState({
      status
    });
    const ticketStatusId = 0;
    if (!status.children || status.children.length === 0) {
      this.props.ctrl.setStatus(status, ticketStatusId);
      this.props.ctrl.hideMenu();
    }
  };

  render() {
    const { status } = this.state;
    let statusCode;
    if (status) {
      statusCode = status.status_code;
    }
    return (
      <StatusMenu
        ticketStatuses={this.props.ticketStatuses}
        statusCode={statusCode}
        handleChange={this.handleChange}
      />
    );
  }
}

export class StatusMenu extends React.Component {
  static propTypes = {
    ticketStatuses: PropTypes.array.isRequired,
    statusCode:     PropTypes.string,
    handleChange:   PropTypes.func
  };

  isStatusSelected(statusCode) {
    return this.props.statusCode === statusCode;
  }

  isSubStatusSelected(statusCode) {
    return this.props.statusCode && this.props.statusCode.startsWith(statusCode);
  }

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
          {this.isStatusSelected(status.status_code) && <div className="on-icon"><i className="fas fa-check" /></div>}
          {status.title ? status.title : <FormattedMessage id={`agent.tickets.status_${status.status_type}`} />}
        </div>
        {this.isSubStatusSelected(status.status_code) && status.children.length > 0 &&
        <div className="sub-status">
          <label><FormattedMessage id="agent.general.sub_status" /></label>
          <ul>
            {
              status.children.map(subStatus => (
                <li
                  key={subStatus.status_code}
                  className="on cursor has-no-submenu"
                  onClick={() => handleChange(subStatus)}
                >
                  <div className="status">
                    {this.isStatusSelected(subStatus.status_code) && <div className="on-icon"><i className="fas fa-check" /></div>}
                    {subStatus.title ? subStatus.title : <FormattedMessage id={`agent.tickets.status_${subStatus.status_type}`} />}
                  </div>
                </li>
              ))
            }
          </ul>
        </div>}
      </li>
    );
  };

  render() {
    return (
      <div className="dp-menu-area dp-menu-area-primary">
        <label><FormattedMessage id="agent.general.change_status" /></label>
        <ul>
          {this.props.ticketStatuses.map(status => this.renderStatus(status))}
        </ul>
      </div>
    );
  }
}
