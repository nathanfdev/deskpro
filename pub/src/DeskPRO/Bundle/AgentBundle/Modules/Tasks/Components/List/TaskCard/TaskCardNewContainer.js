import React from 'react';
import { connect } from 'react-redux';

@connect()
export class TaskCardNewContainer extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      title: ''
    };
  }

  onChangeTitle = value => {
    this.setState({
      title: value
    });
  };

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      ...props,

      title: this.state.title,
      onChangeTitle: this.onChangeTitle
    });
  }
}
