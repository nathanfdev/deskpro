import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { setRingingVolume } from '../../../Actions/clientActions';
import { ringingVolumeSelector } from '../../../Selectors/client';
import Volume from './Volume';

@connect(state => ({
  value: ringingVolumeSelector(state)
}))
class VolumeContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onChange = (value) => {
    this.props.dispatch(setRingingVolume(value));
  };

  render() {
    return (
      <Volume
        {...this.props}
        onChange={this.onChange}
      />
    );
  }
}

export default VolumeContainer;
