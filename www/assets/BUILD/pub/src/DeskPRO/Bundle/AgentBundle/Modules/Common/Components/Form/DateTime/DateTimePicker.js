import PropTypes from 'prop-types';
import React from 'react';
import { AbstractDateTimePicker } from './AbstractDateTimePicker';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { HiddenDateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/HiddenDateTimePicker';
import Moment from 'moment';

export class DateTimePicker extends React.Component {

  static propTypes = {
    label:                  PropTypes.string.isRequired,
    value:                  PropTypes.string,
    className:              PropTypes.string.isRequired,
    onChange:               PropTypes.func,
    stopPropagationOnClose: PropTypes.bool,
    onSetEditing:           PropTypes.func
  };

  componentWillMount() {
    this.setState({
      isOpen: false,
      value:  this.props.value
    });
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      value: nextProps.value
    });
  }

  onChange = (val) => {
    this.setState({ value: val });
    if (this.props.onChange) {
      this.props.onChange(val);
    }
  };

  onOpen = () => {
    if (this.state.isOpen) return;
    this.setState({ isOpen: true });
    if (this.props.onSetEditing) {
      this.props.onSetEditing(true);
    }
  };

  onClose = event => {
    if (!this.state.isOpen) return;
    if (this.props.stopPropagationOnClose) {
      event.stopImmediatePropagation();
    }
    this.setState({ isOpen: false });
    if (this.props.onSetEditing) {
      this.props.onSetEditing(false);
    }
  };

  render() {
    const { className, label } = this.props;
    const { value } = this.state;
    const valueString = value ? Moment(value).format('MMMM D, YYYY, hh:mm') : '';

    return (
      <div className={className}>
        <label>{label}</label>
        <input type="text" ref="input" onFocus={this.onOpen} value={valueString} readOnly />
        <Detached
          isOpen={this.state.isOpen}
          positionTarget={this}
          positionAt="left bottom"
          collision="fit"
          zIndex={1002}
        >
          <ClickOut onClickOut={this.onClose} ignoreNodes={[this.refs.input]}>
            <HiddenDateTimePicker value={value} onDone={this.onChange} />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
