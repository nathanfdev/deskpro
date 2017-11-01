import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { MassActionBarContainer }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBarContainer';
import { initialLoad } from '../../Actions/navActions';
import { loadIndicator } from '../../Actions/listActions';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { massActionsSelector } from '../../Selectors/massActions';
import { connect } from 'react-redux';

@connect(state => ({
  selected: selectedSelector(state),
  actions:  massActionsSelector(state)
}))
export class MassActionContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    selected: PropTypes.object.isRequired,
    actions:  PropTypes.array.isRequired
  };

  componentWillMount() {
    this.setState({
      expanded: false
    });
  }

  render() {
    const { actions } = this.props;

    const config = {
      actions,

      content:             'tickets',
      loadIndicatorAction: loadIndicator,
      reloadNavAction:     initialLoad
    };

    return (
      <MassActionBarContainer {...config} />
    );
  }
}
