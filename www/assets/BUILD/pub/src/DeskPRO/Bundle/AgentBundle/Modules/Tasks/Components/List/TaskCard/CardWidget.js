import React, { PropTypes } from 'react';

export class CardWidget extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      isOpen: false,
      value: props.value
    };
  }

  componentWillReceiveProps(newProps) {
    this.setState({value: newProps.value});
  }

  componentWillUnmount() {
    this.isUnmounted = true;
  }

  reset() {
    this.setState({value: null});
  }

  onChange = (val) => {
    this.setState({value: val});
    this.props.onChange && this.props.onChange(val);
  };

  onOpen = () => {
    if (this.state.isOpen) return;
    this.setState({isOpen: true});
    this.props.onSetEditing && this.props.onSetEditing(true);
  };

  onClose = event => {
    if (!this.state.isOpen) return;
    this.setState({isOpen: false});
    this.props.onSetEditing && this.props.onSetEditing(false);
  };
}
