import React from 'react';
import * as TestActions from '../Actions/TestActions';
import { connect } from 'react-redux';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

const testAppUserSel = createPeopleRequestSelectors('testApp');

console.log(testAppUserSel);

@connect(state => ({
  test: state.Test.test,
  userStatus: testAppUserSel.statusSel(state),
  users: testAppUserSel.recordsSel(state)
}))
export default class TestNavFrame extends React.Component {
  getNum() {
    return 5;
  }

  inc = () => {
    this.props.dispatch(TestActions.setCount(this.props.test.get('count') + this.getNum()));
  }

  loadUser = () => {
    this.props.dispatch(TestActions.loadUser());
  }

  loadBatch1 = () => {
    this.props.dispatch(TestActions.loadUserBatch([1,2]));
  }

  loadBatch2 = () => {
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
