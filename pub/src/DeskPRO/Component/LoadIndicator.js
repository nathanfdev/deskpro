import React, { PropTypes, Component } from 'react';
import Loader from 'react-loader';
import { connect } from 'react-redux';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

@connect(state => {
  return ({
    activeAppId: state.Application.dpWindow.get('activeAppId')
  });
})
export class LoadIndicator extends Component {
  static propTypes = {
    activeAppId: PropTypes.string,
    top: PropTypes.number,
    left: PropTypes.number
  };

  render() {
    const { activeAppId } = this.props;
    const top  = this.props.top  || '50%';
    const left = this.props.left || '50%';

    return (
      <Loader color={constants.APP_COLOURS[activeAppId]}
              width={3}
              left={left}
              top={top}
        {...this.props} />
    );
  }
}