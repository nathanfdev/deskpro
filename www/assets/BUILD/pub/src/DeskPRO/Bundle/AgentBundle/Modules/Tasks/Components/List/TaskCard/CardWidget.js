import PropTypes from 'prop-types';
import React from 'react';

export class CardWidget extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      isOpen: props.isOpen,
      value:  props.value
    };
  }

  shouldComponentUpdate(props, state) {
    return this.state.isOpen !== state.isOpen || this.state.value !== state.value;
  }

  componentWillReceiveProps(props) {
    let state = {
      value: props.value
    };
    if (undefined !== props.isOpen) {
      state.isOpen = props.isOpen;
    }
    this.setState(state);
  }

  onChange = (val) => {
    this.setState({ value: val });
    this.props.onChange && this.props.onChange(val);
  };

  onOpen = () => {
    if (this.state.isOpen) return;
    this.setState({ isOpen: true });
    this.props.onSetEditing && this.props.onSetEditing(true);
  };

  onClose = event => {
    if (!this.state.isOpen) return;
    this.setState({ isOpen: false });
    this.props.onSetEditing && this.props.onSetEditing(false);
  };
}
