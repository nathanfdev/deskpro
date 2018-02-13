import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Loader from '@deskpro/react-loader';
import { connect } from 'react-redux';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

@connect((state, props) => {
  const loaded = props.selector
    ? !props.selector(state)
    : props.loaded;
  return {
    activeAppId: state.Application.dpWindow.get('activeAppId'),
    loaded
  };
})
export class LoadIndicator extends Component {

  static propTypes = {
    activeAppId:    PropTypes.string,
    top:            PropTypes.string,
    left:           PropTypes.string,
    loaded:         PropTypes.boolean,
    onStartLoading: PropTypes.func,
    onStopLoading:  PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      loaded: props.loaded
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      loaded: props.loaded
    });
  }

  componentDidUpdate(prevProps, prevState) {
    !this.state.loaded && this.props.onStartLoading && this.props.onStartLoading();
    this.state.loaded && this.props.onStopLoading && this.props.onStopLoading();
  }

  render() {
    const { activeAppId } = this.props;
    const top  = this.props.top || '50%';
    const left = this.props.left || '50%';

    return (
      <Loader
        color={constants.APP_COLOURS[activeAppId]}
        width={3}
        left={left}
        top={top}
        {...this.props}
      />
    );
  }
}
