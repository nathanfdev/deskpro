import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { MassActionCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar';
import { toggleMassAction } from '../../../Actions/listActions';
import { selectedCountSelector } from '../../../Selectors/list';

@connect(state => ({
  count: selectedCountSelector(state)
}))
export class MassActionCheckboxContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    count: PropTypes.number.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      enabled: false
    };
  }

  handleClick = (e) => {
    e.preventDefault();
    this.props.dispatch(toggleMassAction(!this.state.enabled));
    this.setState({enabled: !this.state.enabled});
  };

  render() {
    return (
      <MassActionCheckbox
        count={this.props.count}
        massAction={this.state.enabled}
        onClick={this.handleClick}
      />
    );
  }
}