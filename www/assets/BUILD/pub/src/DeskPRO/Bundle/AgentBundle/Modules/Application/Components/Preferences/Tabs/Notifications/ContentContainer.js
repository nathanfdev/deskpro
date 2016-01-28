import React from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';

@connect()
export class ContentContainer extends React.Component {
  render() {
    return (
      <div>
        <Content />
      </div>
    );
  }
}
