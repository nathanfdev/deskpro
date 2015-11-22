import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import moment from 'moment';

@connect()
export class TaskCardNewContainer extends React.Component {

  static propTypes = {
    updateData: PropTypes.object
  };

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

  getDateDue() {
    const { updateData } = this.props;
    const date = updateData.date_due ? updateData.date_due : moment().endOf('day');

    return date.format();
  }

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,
      ...props,

      title: this.state.title,
      onChangeTitle: this.onChangeTitle,
      dateDue: this.getDateDue()
    });
  }
}
