import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Simple } from 'DeskPRO/Component/Positioned/Simple';
import { Preferences } from './Preferences';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class PreferencesContainer extends React.Component {

  static propTypes = {
    dpWindow:       PropTypes.object.isRequired,
    dispatch:       PropTypes.func.isRequired,
    positionTarget: PropTypes.any.isRequired
  };

  render() {
    const { dpWindow, dispatch, positionTarget } = this.props;

    return (
      <Simple
        isOpen={dpWindow.get('isPreferencesOpen')}
        positionTarget={positionTarget}
        positionAt="center center"
        postionMy="center center"
      >

        <Preferences dispatch={dispatch} dpWindow={dpWindow} />
      </Simple>
    );
  }
}
