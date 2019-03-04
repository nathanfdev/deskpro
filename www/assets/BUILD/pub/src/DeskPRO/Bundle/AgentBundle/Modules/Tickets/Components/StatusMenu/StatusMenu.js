import React from 'react';
import PropTypes from 'prop-types';
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
    this.state = {
      status:         null,
      ticketStatusId: null
    };
    props.ctrl.onChange(this.handleStatusChange);
  }

  handleStatusChange = (status, ticketStatusId) => {
    this.setState({
      status,
      ticketStatusId
    });
  };

  render() {
    return (
      <StatusMenu ticketStatuses={this.props.ticketStatuses} />
    );
  }
}

export class StatusMenu extends React.Component {
  static propTypes = {
    ticketStatuses: PropTypes.array.isRequired,
    statusCode:     PropTypes.string
  };

  isStatusSelected(statusCode) {
    return this.props.statusCode === statusCode;
  }

  render() {
    return (
      <div className="dp-menu-area dp-menu-area-primary">
        <label>Reply and set a status</label>
        <ul>
          {this.props.ticketStatuses.map(status => (
            <li key={status.status_code} className="on has-no-submenu cursor">
              {this.isStatusSelected(status) && <div className="on-icon"><i className="fas fa-check" /></div>}
              {status.title ? status.title : <FormattedMessage id={`agent.tickets.status_${status.status_type}`} />}
            </li>
          ))}
        </ul>
      </div>
    );
  }
}
