import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';

@connect(() => ({
  elements: Immutable.fromJS([{id: 1}, {id: 2}, {id: 3}, {id: 4}, {id: 5}])
}))
export class ListCardViewContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    elements: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>ListCardViewContainer</div>
    );
  }

}