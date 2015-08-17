import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/crmNavActions'
import { Nav } from './Nav';

@connect(state => ({
  test: 'test'
}))
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
  }

  render() {
    return (
      <Nav />
    );
  }
}
