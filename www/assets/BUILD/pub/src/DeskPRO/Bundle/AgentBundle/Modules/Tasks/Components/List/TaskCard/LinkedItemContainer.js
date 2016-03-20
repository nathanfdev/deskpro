import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { LinkedItem } from './LinkedItem';


@connect()

export class LinkedItemContainer extends React.Component {

  render() {
    return (
      <LinkedItem {...this.props} />
    );
  }
}
