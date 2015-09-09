import React from "react";
import * as TestActions from "../Actions/TestActions";
import { connect } from "react-redux";
import * as userSels from "DeskPRO/Bundle/AgentBundle/Modules/Common/Selectors/userSelector";

@connect(state => ({
  test:       state.Test.test,
  userStatus: userSels.requestStatus("testApp")(state),
  users:      userSels.requestRecords("testApp")(state)
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

  loadBatch1 = (e) => {
    this.props.dispatch(TestActions.loadUserBatch([1,2]));
  }

  loadBatch2 = (e) => {
    this.props.dispatch(TestActions.loadUserBatch([2,3,4,5]));
  }

  render() {
    const { test, users, userStatus } = this.props;

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

        <hr />

        {userStatus.get('isLoading') ? "Loading..." : (<div><button onClick={this.loadBatch1}>Load 1</button><button onClick={this.loadBatch2}>Load 2</button></div>)}

        {users.map(u => {
          return (
            <li>{u.get('name')}</li>
          )
        })}
      </section>
    )
  }
}
