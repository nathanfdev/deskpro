import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';

@connect()
export class ContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  render() {
    return (
      <div>
        <Content dispatch={this.props.dispatch} />
      </div>
    );
  }
}
