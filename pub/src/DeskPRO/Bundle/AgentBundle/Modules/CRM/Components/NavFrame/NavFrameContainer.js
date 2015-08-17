import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/crmNavActions'
import { NavFrame } from './NavFrame';

@connect(state => ({
  test: 'test'
}))
export class NavFrameContainer extends React.Component {

  constructor(props) {
    super(props);
  }

  render() {
    return (
      <NavFrame />
    );
  }
}
