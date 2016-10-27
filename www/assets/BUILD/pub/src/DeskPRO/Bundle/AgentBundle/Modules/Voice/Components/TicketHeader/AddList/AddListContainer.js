import React from 'react';
import { connect } from 'react-redux';
import AddList from './AddList';

@connect()
class AddListContainer extends React.Component {

  render() {
    return <AddList {...this.props} />;
  }
}

export default AddListContainer;
