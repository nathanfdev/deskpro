import React, { PropTypes } from 'react';

export class BaseForm extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      quickFilter: '',
      agent: null,
      team: null,
      department: null,
      errors: {},
      submit: false
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
      agent: selected
    });
  };

  onUnassignAll = (event) => {
    event.preventDefault();
    this.setState({
      agent: null,
      team: null,
      department: null
    });
  };

  onChange(prop, value) {
    this.setState({
      [prop]: value
    });
  }

}
