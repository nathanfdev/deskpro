import React, { PropTypes } from 'react';
import { AbstractDateTimePicker } from './AbstractDateTimePicker';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { HiddenDateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/HiddenDateTimePicker';
import Moment from 'moment';

export class DateTimePicker extends React.Component {

  static propTypes = {
    label: PropTypes.string.isRequired,
    value: PropTypes.string,
    className: PropTypes.string.isRequired,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      isOpen: false,
      value: props.value
    };
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

  render() {
    const { className, label } = this.props;
    const { value } = this.state;
    const valueString = value ? Moment(value).format('MMMM D, YYYY, hh:mm') : '';

    return (
      <div className={className}>
        <label>{label}</label>
        <input type="text" ref="input" onFocus={this.onOpen} value={valueString} readOnly={true} />
        <Detached isOpen={this.state.isOpen}
                  positionTarget={this}
                  positionAt="left bottom"
                  collision="fit"
                  zIndex={1002}>

          <ClickOut onClickOut={this.onClose} ignoreNodes={[this.refs.input]}>
            <HiddenDateTimePicker value={value} onChange={this.onChange} />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
