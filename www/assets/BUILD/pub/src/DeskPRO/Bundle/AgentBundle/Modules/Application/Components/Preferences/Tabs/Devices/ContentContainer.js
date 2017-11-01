import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import { loadQrCode } from '../../../../Actions/preferencesActions';
import { setupTokenSelector } from '../../../../Selectors/preferences';

@connect(state => ({
  token: setupTokenSelector(state)
}))
export class ContentContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    token:    PropTypes.string.isRequired
  };

  componentDidMount() {
    this.props.dispatch(loadQrCode());
  }

  render() {
    return (
      <div>
        <Content token={this.props.token} />
      </div>
    );
  }
}
