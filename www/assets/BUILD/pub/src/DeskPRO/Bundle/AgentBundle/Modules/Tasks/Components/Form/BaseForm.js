import React, { PropTypes } from 'react';
import Immutable from 'immutable';

export class BaseForm extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      quickFilter: '',
      assign:      {},
      errors:      {},
      submit:      false
    };
  }

  onChangeQuickFilter = value => {
    this.setState({
      quickFilter: value
    });
  };

  onAssignSelf = () => {
    const id = this.props.me.get('id');
    const selected = this.state.agent;
    if (id && selected.indexOf(id) === -1) {
      selected.push(id);
    }

    this.setState({
      assign: { agent: selected }
    });
  };

  onUnassignAll = (event) => {
    event.preventDefault();
    const set = Immutable.Set([]);
    this.setState({
      agents:      set,
      teams:       set,
      departments: set
    });
  };

  onChange = (prop, value) => {
    this.setState({ [prop]: value });
  };

}
