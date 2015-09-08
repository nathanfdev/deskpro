import React from "react";
import * as TestActions from "../Actions/TestActions";
import { connect } from "react-redux";

@connect(state => ({
  test:   state.Test.test
}))
export default class TasksListFrame extends React.Component {
  inc = (e) => {
    this.props.dispatch(TestActions.setCount(this.props.test.get('count') + this.getNum()));
  }

  getNum() {
    return 5;
  }

  loadUser = (e) => {
    this.props.dispatch(TestActions.loadUser());
  }

  render() {
    const test = this.props.test;
    const count  = test.get('count');
    const user   = test.get('user');
    const status = test.get('status');

    return (
      <section className="test-nav-frame dp-nav-frame">
        Count: {count}<br/>
        <button onClick={this.inc}>Inc</button>

        <hr />

        {status.get('userIsLoading') ? 'LOADING' : ''}

        {!status.get('userIsLoading') && user.has('id') ? user.get('name') : ''}

        <br/>
        <button onClick={this.loadUser}>Load User</button>
      </section>
    )
  }
}
