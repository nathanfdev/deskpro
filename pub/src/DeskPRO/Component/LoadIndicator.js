import React, { PropTypes, Component } from 'react';
import Loader from 'react-loader';
import { connect } from 'react-redux';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

@connect(state => {
  return ({
    activeAppId: state.Application.dpWindow.get('activeAppId')
  });
})
export class LoadIndicator extends Component {
  static propTypes = {
    activeAppId: PropTypes.string
  };

  render() {
    const {activeAppId} = this.props;

    return (
      <Loader color={constants.APP_COLOURS[activeAppId]}
              width={3}
              left="50%"
              top="50%"
        {...this.props} />
    );
  }
}