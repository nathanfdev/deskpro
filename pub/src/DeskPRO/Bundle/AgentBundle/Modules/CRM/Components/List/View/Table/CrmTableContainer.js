import React, {Component, PropTypes} from 'react';
import { CrmTable } from './CrmTable';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    people: state.CRM.list.get('people')
  });
})

export class CrmTableContainer extends Component {
  static propTypes = {
    people: PropTypes.object.isRequired
  };

  render() {
    const { people } = this.props;
    return (
      <CrmTable elements={people}/>
    );
  }
}